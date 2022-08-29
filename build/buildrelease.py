#!/usr/bin/python -u

import getopt
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
    ".travis.yml",
    "build.xml",
    "composer.json",
    "composer.lock",
    "test_langs.php",
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
    "build-scripts/"
    "javascript/dev/",
    "packages/",
    "phing/",
    "tests/"
    )


def usage():
    print '''Builds a release (zip/tarball)

Usage: %s [options] /path/for/tarballs [/path/to/mantisbt]

Options:
    -h | --help               Show this usage message

    -c | --clean              Remove build directory when completed
    -d | --docbook            Build and include the docbook manuals
    -v | --version <version>  Override version name detection
    -s | --suffix <suffix>    Include version suffix in config file
''' % path.basename(__file__)
# end usage()


def gpg_sign_tarball(filename):
    ''' Sign the file using GPG '''
    gpgsign = "gpg -b -a %s" + path.abspath(path.join(os.curdir, filename))
    try:
        subprocess.check_call(gpgsign % '--batch --yes ', shell=True)
    except subprocess.CalledProcessError:
        print "WARNING: GPG signature failed; to sign manually, run\n" \
            "         %s" % (gpgsign % '')


def generate_checksum(filename):
    ''' Generate MD5 and SHA1 checksums for the file '''
    f = open("%s.digests" % filename, 'w')
    for method in ("md5", "sha1"):
        checksum_cmd = "%ssum --binary " % method
        checksum = os.popen(checksum_cmd + filename).read()
        f.write(checksum)
        print "      %s: %s" % (method, checksum.rstrip())
    f.close()


def remove_build_dir(release_dir):
    print "Removing build directory..."
    shutil.rmtree(release_dir)


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError, err:
        print str(err)
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
    old_umask = os.umask(0002)

    # Check paths
    if not path.isdir(release_path):
        print "Creating release path..."
        os.mkdir(release_path)

    if not path.isdir(mantis_path):
        print "Error: mantis path is not a directory or does not exist."
        sys.exit(3)

    if (
        not path.isfile(path.join(mantis_path, "core.php")) or
        not path.isdir(path.join(mantis_path, "core")) or
        not path.isfile(path.join(mantis_path, "core", "constant_inc.php"))
    ):
        print "Error: '%s' does not appear to be a valid Mantis directory." % \
            mantis_path
        sys.exit(3)

    # Find Mantis version
    if not mantis_version:
        f = open(path.join(mantis_path, "core", "constant_inc.php"))
        content = f.read()
        f.close

        mantis_version = re.search("'MANTIS_VERSION'[,\s]+'([^']+)'",
                                   content).group(1)

    # Generate release name
    release_name = 'mantisbt-' + mantis_version
    if version_suffix:
        release_name += '-' + version_suffix

    # Copy to release path, excluding unwanted files
    release_dir = path.abspath(path.join(release_path, release_name))

    print "\nBuilding release '%s' in path '%s'" % (release_name, release_dir)
    print "  Source repository: '%s'\n" % mantis_path

    if path.exists(release_dir):
        print "Error: release path already contains %s." % (release_name)
        sys.exit(3)

    # Generate temp file with list of exclusions
    fp = tempfile.NamedTemporaryFile(delete=False)
    print "  Excluded files and directories:"
    for name in exclude_list:
        print "    " + name
        fp.write(name + "\n")
    fp.close()

    # Copy the files from the source repo, then delete temp file
    rsync = "rsync -rltD --exclude-from=%s %s/ %s" % (
        fp.name,
        mantis_path,
        release_dir
    )
    subprocess.check_call(rsync, shell=True)

    os.unlink(fp.name)
    print "  Copy complete.\n"

    # Apply version suffix
    if version_suffix:
        print "Applying version suffix..."
        sed_cmd = "s/(%s\s*=\s*)'.*'/\\1'%s'/" % (
            'g_version_suffix',
            version_suffix
        )
        subprocess.call(
            'sed -r -i.bak "%s" %s' % (
                sed_cmd,
                path.join(release_dir, "config_defaults_inc.php")
            ),
            shell=True
        )

    # Build documentation for release
    if build_docbook:
        print "Building docbook manuals...\n"
        subprocess.call(
            manualscript + " --release %s %s" % (
                path.join(mantis_path, "docbook"),
                path.join(release_dir, "doc")
            ),
            shell=True
        )

    # Create tarballs and sign them
    print "Creating release tarballs..."
    os.chdir(release_path)
    tarball_ext = ("tar.gz", "zip")

    for ext in tarball_ext:
        tarball = "%s.%s" % (release_name, ext)
        print "  " + tarball

        if ext == "tar.gz":
            tar_cmd = "tar -czf"
        elif ext == "zip":
            tar_cmd = "zip -rq"
        tar_cmd += " %s %s"

        subprocess.call(tar_cmd % (tarball, release_name), shell=True)

        print "    Signing the tarball"
        gpg_sign_tarball(tarball)

        print "    Generating checksums..."
        generate_checksum(tarball)

    # Cleanup
    if clean_build:
        print
        remove_build_dir(release_dir)

    # Restore previous umask
    os.umask(old_umask)

    print "Done!\n"

# end main()

if __name__ == "__main__":
    main()
