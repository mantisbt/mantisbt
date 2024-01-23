#!/usr/bin/python3 -u

import getopt
import hashlib
import os
from os import path
import re
import shutil
import subprocess
import sys
import tempfile

# Script options
options = "hcdv:s:"
long_options = ["help", "clean", "docbook", "version=", "suffix="]

# Absolute path to docbook-manual.py
manualscript = path.dirname(path.abspath(__file__)) + '/docbook-manual.py'

# List of files and dirs to exclude from the release tarball
exclude_list = (
    # System / build files
    ".git*",
    ".mailmap",
    ".imgbotconfig",
    ".travis.yml",
    "build/",
    "composer.json",
    "composer.lock",
    # User custom files
    "config_inc.php",
    "custom_constant*_inc.php",
    "custom_functions_inc.php",
    "custom_strings_inc.php",
    "custom_relationships_inc.php",
    "mantis_offline.php",
    "mc_config_inc.php",
    # Directories
    "docbook/",
    "tests/"
    )

# Checksum types to includ in digest filese
checksum_types = ['sha512', 'sha256', 'sha1', 'md5']


def usage():
    print('''Builds a release (zip/tarball)

Usage: {0} [options] /path/for/tarballs [/path/to/mantisbt]

Options:
    -h | --help               Show this usage message

    -c | --clean              Remove build directory when completed
    -d | --docbook            Build and include the docbook manuals
    -v | --version <version>  Override version name detection
    -s | --suffix <suffix>    Include version suffix in config file
'''.format(path.basename(__file__)))
# end usage()


def gpg_sign_tarball(filename):
    """
    Sign the file using GPG

    The private key's passphrase is read from a file named 'gpg-passphrase' in
    the user's home directory. If that file is not present, gpg will fall back
    to using gpg-agent, which may request the passphrase interactively.
    """

    gpgsign = [
        'gpg',
        '--detach-sign',
        '--armor',
        '--batch',
        '--yes',
        path.abspath(path.join(os.curdir, filename)),
    ]

    # Insert passphrase option if file exists
    passphrase = path.expanduser('~/gpg-passphrase')
    if path.isfile(passphrase):
        pos = len(gpgsign) - 1
        gpgsign[pos:pos] = ['--pinentry=loopback',
                            '--passphrase-file=' + passphrase]

    try:
        subprocess.check_call(gpgsign)
    except subprocess.CalledProcessError:
        # Remove batch-specific options for warning display
        gpgsign[3:len(gpgsign) - 1] = []
        print("WARNING: GPG signature failed; to sign manually, run")
        print("         " + " ".join(gpgsign))


def generate_checksum(filename):
    """
    Generate digest file with checksums for the given filename
    """
    # Initialize hash objects for each checksum type
    checksums = dict()
    for method in checksum_types:
        checksums[method] = hashlib.new(method)

    # Read the file and calculate checksums
    with open(filename, 'rb') as file:
        for chunk in file:
            for method in checksum_types:
                checksums[method].update(chunk)

    # Print results and generate digests file
    f = open(filename + ".digests", 'w')
    for method in checksum_types:
        checksum = checksums[method].hexdigest()
        f.write("{hash} *{file}\n".format(file=filename, hash=checksum))
    f.close()


def remove_build_dir(release_dir):
    print("Removing build directory...")
    shutil.rmtree(release_dir)


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError as err:
        print(err)
        usage()
        sys.exit(2)

    build_docbook = False
    clean_build = False
    mantis_version = ""
    version_suffix = ""

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-c", "--clean"):
            clean_build = True

        elif opt in ("-d", "--docbook"):
            build_docbook = True

        elif opt in ("-v", "--version"):
            mantis_version = val

        elif opt in ("-s", "--suffix"):
            version_suffix = val

    if len(args) < 1:
        usage()
        sys.exit(1)

    release_path = args[0]
    mantis_path = "."

    if len(args) > 1:
        mantis_path = args[1]

    # 'Standard' umask
    old_umask = os.umask(0o002)

    # Check paths
    if not path.isdir(release_path):
        print("Creating release path...")
        os.mkdir(release_path)

    if not path.isdir(mantis_path):
        print("Error: mantis path is not a directory or does not exist.")
        sys.exit(3)

    if (
        not path.isfile(path.join(mantis_path, "core.php")) or
        not path.isdir(path.join(mantis_path, "core")) or
        not path.isfile(path.join(mantis_path, "core", "constant_inc.php"))
    ):
        print("Error: '{}' does not appear to be a valid Mantis directory."
              .format(mantis_path))
        sys.exit(3)

    # Find Mantis version
    if not mantis_version:
        f = open(path.join(mantis_path, "core", "constant_inc.php"))
        content = f.read()
        f.close()

        mantis_version = re.search(r"'MANTIS_VERSION'[,\s]+'([^']+)'",
                                   content).group(1)

    # Generate release name
    release_name = 'mantisbt-' + mantis_version
    if version_suffix:
        release_name += '-' + version_suffix

    # Copy to release path, excluding unwanted files
    release_dir = path.abspath(path.join(release_path, release_name))

    print("\nBuilding release '{}' in path '{}'".format(
        release_name,
        release_dir
    ))
    print("  Source repository: '{}'\n".format(mantis_path))

    if path.exists(release_dir):
        print("Error: release path already contains {}.".format(release_name))
        sys.exit(3)

    # Generate temp file with list of exclusions
    fp = tempfile.NamedTemporaryFile(mode='wt', delete=False)
    print("  Excluded files and directories:")
    for name in exclude_list:
        print("    " + name)
        fp.write(name + "\n")
    fp.close()

    # Copy the files from the source repo, then delete temp file
    rsync = "rsync -rltD --exclude-from={exclude} {source}/ {target}".format(
        exclude=fp.name,
        source=mantis_path,
        target=release_dir
    )
    subprocess.check_call(rsync, shell=True)

    os.unlink(fp.name)
    print("  Copy complete.\n")

    # Apply version suffix
    if version_suffix:
        print("Applying version suffix...")
        sed_cmd = r"s/({}\s*=\s*)'.*'/\\1'{}'/".format(
            'g_version_suffix',
            version_suffix
        )
        subprocess.call(
            'sed -r -i.bak "{}" {}'.format(
                sed_cmd,
                path.join(release_dir, "config_defaults_inc.php")
            ),
            shell=True
        )

    # Build documentation for release
    if build_docbook:
        print("Building docbook manuals...\n")
        subprocess.call(
            manualscript + " --release {} {}".format(
                path.join(mantis_path, "docbook"),
                path.join(release_dir, "doc")
            ),
            shell=True
        )

    # Create tarballs and sign them
    print("Creating release tarballs...")
    os.chdir(release_path)
    tarball_ext = ("tar.gz", "zip")

    for ext in tarball_ext:
        tarball = "{}.{}".format(release_name, ext)
        print("  " + tarball)

        if ext == "tar.gz":
            tar_cmd = "tar -czf"
        elif ext == "zip":
            tar_cmd = "zip -rq"
        else:
            tar_cmd = ""
        tar_cmd += " {} {}"

        subprocess.call(tar_cmd.format(tarball, release_name), shell=True)

        print("    Signing the tarball")
        gpg_sign_tarball(tarball)

        print("    Generating checksums ({types})".format(types=', '.join(checksum_types)))
        generate_checksum(tarball)

    # Cleanup
    if clean_build:
        print()
        remove_build_dir(release_dir)

    # Restore previous umask
    os.umask(old_umask)

    print("Done!\n")

# end main()


if __name__ == "__main__":
    main()
