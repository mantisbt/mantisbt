#!/usr/bin/python3 -u

# Integrates with docbook-manual.py to build manuals for all tagged
# versions and development branches in the Git repo

import getopt
import os
from os import path
import re
import subprocess
import sys
import time

# Absolute path to docbook-manual.py
manualscript = path.dirname(path.abspath(__file__)) + '/docbook-manual.py'

# List of refs to ignore (regular expressions)
# - HEAD (generally the same as master)
# - dependabot branches
# - 1.x refs except 1.3.x
ignorelist = [
              r'^origin\/HEAD$',
              r'-1\.[012]+\.[\w\d]+',
              r'^origin\/dependabot\/',
             ]

# Script options
options = "hr:cfda"
long_options = ["help", "ref=", "current", "force", "delete",
                "all", "pdf", "html", "release"]


def usage():
    print(f'''Usage: {path.basename(__file__)} /path/to/mantisbt/repo /path/to/install [<lang> ...]

    Options:  -h | --help           Print this usage message
              -r | --ref            Select what refs to build
              -c | --current        Build for current branch (no checkout)
              -f | --force          Ignore timestamps and force building
              -d | --delete         Delete install directories before building
                   --html           Build HTML manual
                   --pdf            Build PDF manual
                   --release        Build single file types used for
                                    release tarballs
              -a | --all            Build all manual types
''')
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


def git_current_branch():
    """
    Returns the current git branch's name or the current commit SHA
    if we are in detached HEAD state
    """
    try:
        # Try to get branch name
        result = subprocess.run(['git', 'symbolic-ref', '--quiet', '--short', 'HEAD'],
                                capture_output=True, text=True, check=True)
    except subprocess.CalledProcessError:
        # Detached HEAD - fall back to the commit hash
        result = subprocess.run(['git', 'rev-parse', 'HEAD'],
                                capture_output=True, text=True, check=True)

    return result.stdout.rstrip()


def git_checkout(branch):
    subprocess.run(['git', 'checkout', '-f', branch], stdout=subprocess.DEVNULL, check=True)


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError as err:
        print(err)
        usage()
        sys.exit(2)

    refs = None
    current = False
    force = False
    pass_opts = []

    # noinspection PyUnboundLocalVariable
    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-r", "--ref"):
            refs = val.split(",")

        elif opt in ("-c", "--current"):
            current = True

        elif opt in ("-f", "--force"):
            force = True

        elif opt in ("-d", "--delete"):
            pass_opts.append("-d")

        elif opt in ("-a", "--all"):
            pass_opts.append("-a")
        elif opt == "--html":
            pass_opts.append("--html")
        elif opt == "--pdf":
            pass_opts.append("--pdf")
        elif opt == "--release":
            pass_opts.append("--release")

    # noinspection PyUnboundLocalVariable
    if len(args) < 2:
        usage()
        sys.exit(1)

    repo = args[0]
    installroot = args[1]
    languages = []

    if len(sys.argv) > 2:
        languages = args[2:]

    if not current:
        # Update repo from default remote
        print(f"Updating repository in '{repo}' from default remote")
        os.chdir(repo)
        subprocess.run(['git', 'fetch'], check=True)
        subprocess.run(['git', 'remote', 'prune', 'origin'], check=True)

    if not current and refs is None:
        # List remote branches
        result = subprocess.run(['git', 'for-each-ref', '--format=%(refname:short)', 'refs/remotes'],
                                capture_output=True, text=True, check=True)
        branches = result.stdout.split()

        # List tags
        result = subprocess.run(['git', 'tag', '-l'], capture_output=True, text=True, check=True)
        tags = result.stdout.split()

        # Filter refs using ignore()
        refs = [ref for ref in branches + tags if not ignore(ref)]

    # Regex to strip 'origin/' from ref names
    refnameregex = re.compile('(?:[a-zA-Z0-9-.]+/)?(.*)')

    curbranch = git_current_branch()
    if current:
        refs = [curbranch]

    # For each ref, checkout (unless working on current branch) and call
    # docbook-manual.py, tracking last build timestamp to prevent
    # building a manual if there have been no commits since last build
    for ref in refs:
        print(f"\nGenerating documentation for '{ref}'")

        manualpath = path.join(installroot, refnameregex.search(ref).group(1))

        if not current:
            git_checkout(ref)

        # Get timestamp of last change to docbook sources from git
        result = subprocess.run(['git', 'log', '--pretty=format:%ct', '-n1', '--', 'docbook'],
                                capture_output=True, text=True, check=True)
        lastchange = int(result.stdout)

        buildfile = path.join(manualpath, '.build')
        lastbuild = 0
        if path.exists(buildfile):
            f = open(buildfile, 'r')
            lastbuild = int(f.read())
            f.close()

        if lastchange > lastbuild or force or current:
            buildcommand = [manualscript] + pass_opts + [path.abspath('docbook'), manualpath] + languages
            print("Calling: " + " ".join(buildcommand))
            try:
                subprocess.run(buildcommand, check=True)

                f = open(buildfile, 'w')
                f.write(str(lastchange))
                f.close()
            except subprocess.CalledProcessError as e:
                print(f"ERROR: failed to build the documentation for '{ref}'")

        else:
            # Get last build's timestamp from buildfile's modified time
            mtime = float(os.path.getmtime(buildfile))
            formatted = time.strftime("%a %Y-%m-%d %H:%M:%S", time.localtime(mtime))
            print(f"Docbook source unchanged since last build ({formatted})")
            # 'touch' the flag file to bump the modified time
            os.utime(buildfile, None)

    # Reset repository to originally checked-out branch
    if curbranch != git_current_branch():
        print("\nRestoring originally checked-out branch")
        git_checkout(curbranch)

# end main()


if __name__ == '__main__':
    main()
