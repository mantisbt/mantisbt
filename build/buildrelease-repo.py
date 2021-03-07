#!/usr/bin/python3 -u

import getopt
import re
import os
from os import path
import shutil
import subprocess
import sys
import tempfile

# clone URL for MantisBT repository
clone_url = 'https://github.com/mantisbt/mantisbt.git'

build_script_name = 'buildrelease.py'

# List of refs to ignore (regular expressions)
# - HEAD (generally the same as master)
# - dependabot branches
# - 1.x refs
ignorelist = [
              r'^origin\/HEAD$',
              r'-1\.[\d]+\.[\w\d]+',
              r'^origin\/dependabot\/',
             ]

# Script options
options = "hfr:bacds:"
long_options = ["help", "fresh", "ref=", "branches", "auto-suffix", "clean",
                "docbook", "suffix="]


def usage():
    print('''Builds one or more releases (zip/tarball) for the specified
references or for all branches.

Usage: {0} [options] /path/for/tarballs [/path/to/repo]

Options:
    -h | --help                  Show this usage message

    -f | --fresh                 Create a fresh clone in repository path,
                                 or temporary path
    -r | --ref <ref>[,<ref>...]  Build a release for named refs <ref>
    -b | --branches              Build a release for all branches
    -a | --auto-suffix           Automatically append the HEAD commit's sha1
                                 to the version suffix

The following options are passed on to '{1}':
    -c | --clean                 Remove build directories when completed
    -d | --docbook               Build the docbook manuals
    -s | --suffix <suffix>       Include version suffix in config files
'''.format(path.basename(__file__), build_script_name))
# end usage()


def ignore(ref):
    """
    Decide which refs to ignore based on regexen listed in 'ignorelist'.
    """
    for regex in [re.compile(r) for r in ignorelist]:
        if len(regex.findall(ref)) > 0:
            return True
    return False
# end ignore()


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError as err:
        print(err)
        usage()
        sys.exit(2)

    pass_opts = ""
    refs = []
    all_branches = False
    version_suffix = ""
    auto_suffix = False
    fresh_clone = False
    delete_clone = False

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-f", "--fresh"):
            fresh_clone = True

        elif opt in ("-r", "--ref"):
            refs.extend(val.split(","))

        elif opt in ("-b", "--branches"):
            all_branches = True

        elif opt in ("-c", "--clean"):
            pass_opts += " -c"

        elif opt in ("-d", "--docbook"):
            pass_opts += " -d"

        elif opt in ("-a", "--auto-suffix"):
            auto_suffix = True

        elif opt in ("-s", "--suffix"):
            version_suffix = val

    if len(args) < 1:
        usage()
        sys.exit(1)

    release_path = path.abspath(args[0])
    repo_path = "."

    if len(args) > 1:
        repo_path = path.abspath(args[1])

    # Create a new repo clone
    if fresh_clone:
        print("Origin MantisBT repository:", clone_url)
        if repo_path == ".":
            repo_path = tempfile.mkdtemp(prefix="mantisbt-", suffix=".git")
            delete_clone = True
        ret = subprocess.call('git clone {} {}'.format(clone_url, repo_path),
                              shell=True)
        if ret != 0:
            print("ERROR: clone failed")
            sys.exit(1)

    # Change to the repo path
    script_dir = path.dirname(path.abspath(__file__))
    os.chdir(repo_path)

    # Update the repository
    if not fresh_clone:
        os.system('git fetch')

    # Consolidate refs/branches
    if all_branches:
        os.system('git remote prune origin')
        cmd = 'git for-each-ref --format="%(refname:short)" refs/remotes'
        refs.extend(os.popen(cmd).read().split())

    if len(refs) < 1:
        refs.append(os.popen('git log --pretty="format:%h" -n1').read())

    refs = [ref for ref in refs if not ignore(ref)]

    # Info
    print("\nWill build the following releases:")
    for ref in refs:
        print("  " + ref)

    # Regex to strip 'origin/' from ref names
    refnameregex = re.compile('(?:[a-zA-Z0-9-.]+/)?(.*)')

    for ref in refs:
        print("\nChecking out '{}'".format(ref))
        os.system("git checkout -f -q {}".format(ref))
        os.system("git log -n1 --pretty='HEAD is now at %h... %s'")
        print()

        # Composer
        if path.isfile('composer.json'):
            print("Installing Composer packages")
            if subprocess.call(
                    'composer install --no-plugins --no-scripts --no-dev',
                    shell=True):
                continue
            print()

        # Update and reset submodules
        print("Updating submodules")
        subprocess.call('git submodule update --init', shell=True)
        subprocess.call('git submodule foreach git checkout -- .', shell=True)

        # Handle suffix/auto-suffix generation
        commit_hash = os.popen('git log --pretty="format:%h" -n1').read()
        print(commit_hash)
        if commit_hash != ref:
            ref = refnameregex.search(ref).group(1)
            commit_hash = "{}-{}".format(ref, commit_hash)

        if auto_suffix and version_suffix:
            suffix = "--suffix {}-{}".format(version_suffix, commit_hash)
        elif auto_suffix:
            suffix = "--suffix " + commit_hash
        elif version_suffix:
            suffix = "--suffix " + version_suffix
        else:
            suffix = ""

        # Absolute path to buildrelease.py in the target repository, with a
        # fallback to the directory of the currently executing script
        buildscript = path.join(repo_path, 'build', build_script_name)
        if not path.exists(buildscript):
            buildscript = path.join(script_dir, build_script_name)
            print("Build script ({file}) not found in {ref}"
                  .format(file=build_script_name, ref=ref))
            print("Using '{}' instead.".format(buildscript))

        # Start building
        os.system("{} {} {} {} {}".format(buildscript, pass_opts, suffix,
                                          release_path, repo_path))

    # Cleanup temporary repo if needed
    if delete_clone:
        print("\nRemoving temporary clone.")
        shutil.rmtree(repo_path)

    # Done
    print("\nAll builds completed.")

# end main()


if __name__ == "__main__":
    main()
