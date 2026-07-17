#!/usr/bin/python3 -u

import errno
import getopt
import glob
import os
from os import path
import shutil
import subprocess
import sys


# Constants
MAKE = 'make'
PUBLICAN = 'publican'

# Script options
options = "hda"
long_options = [
    "help",
    "delete",
    "epub",
    "html",
    "pdf",
    "txt",
    "release",
    "all"
    ]


def usage():
    print(f'''Usage: {path.basename(__file__)} \
/path/to/mantisbt/docbook /path/to/install [<lang> ...]
    Options:  -h | --help           Print this usage message
              -d | --delete         Delete install directory before building
                   --epub           Build EPUB manual
                   --html           Build HTML manual
                   --pdf            Build PDF manual
                   --txt            Build TXT manual
                   --release        Build single file types used for
                                    release tarballs
              -a | --all            Build all manual types
''')
# end usage()


def run_publican(args):
    """
    Run Publican with the given arguments.
    Prints an error message and exits with return code 1 if execution failed.

    :param args: Publican Command and arguments (as a list)
    :return:
    """
    publican = shutil.which(PUBLICAN)
    if publican is None:
        print(f"ERROR: {PUBLICAN} executable not found.")
        sys.exit(1)
    cmd = [publican]
    if type(args) is list:
        cmd.extend(args)
    else:
        cmd.append(args)

    try:
        print("Running", " ".join(cmd))
        ret = subprocess.run(cmd, text=True, capture_output=True, check=True)
        print(ret.stdout.strip())
        print()
    except subprocess.CalledProcessError as e:
        print(e.stderr.strip() if e.stderr else e.stdout.strip())
        print("ERROR:", e)
        sys.exit(1)


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError as err:
        print(err)
        usage()
        sys.exit(2)

    # noinspection PyUnboundLocalVariable
    if len(args) < 2:
        usage()
        sys.exit(1)

    delete = False
    types = "html,pdf"

    # noinspection PyUnboundLocalVariable
    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-d", "--delete"):
            delete = True

        # Output formats
        elif opt in ("-a", "--all"):
            types = "html,html-desktop,txt,pdf,epub"
        elif opt == "--epub":
            types = "epub"
        elif opt == "--html":
            types = "html,html-desktop"
        elif opt == "--pdf":
            types = "pdf"
        elif opt == "--txt":
            types = "txt"
        elif opt == "--release":
            types = "html-desktop,pdf,txt"

    docroot = path.abspath(args[0])
    installroot = args[1]
    languages = []

    if len(args) > 2:
        languages = args[2:]

    os.chdir(docroot)

    if delete and installroot != "/" and path.isdir(installroot):
        print("Deleting install directory " + installroot)
        for root, dirs, files in os.walk(installroot, topdown=False):
            for name in files:
                os.remove(path.join(root, name))
            for name in dirs:
                os.rmdir(path.join(root, name))

    buildcount = 0

    # Process all existing manuals
    for directory in next(os.walk(docroot))[1]:
        if directory == '.svn' or directory == 'template' or directory == 'erd':
            continue

        builddir = path.join(docroot, directory)
        os.chdir(builddir)

        # Languages to process
        if languages:
            langs = languages
        else:
            langs = next(os.walk(builddir))[1]
            if langs.count('.svn'):
                langs.remove('.svn')
            if langs.count('tmp'):
                langs.remove('tmp')

        # Build docbook with PUBLICAN
        if not path.exists('publican.cfg'):
            print("ERROR: publican.cfg not found")
            exit(1)

        print(f"Building manual in '{builddir}'\n")
        run_publican('clean')
        cmd = ['build', '--formats=' + types]
        if langs:
            cmd.append('--langs=' + ','.join(langs))
        run_publican(cmd)

        print(f"Copying generated manuals to '{installroot}'")
        for lang in langs:
            builddir = path.join('tmp', lang)
            installdir = path.join(installroot, lang, directory)

            # Create target directory tree
            try:
                os.makedirs(installdir)
            except OSError as e:
                # Ignore file exists error
                if e.errno != errno.EEXIST:
                    raise

            # Copy HTML manuals with rsync
            source = path.join(builddir, 'html*')
            glob_source = glob.glob(source)
            if glob_source:
                try:
                    rsync = ['rsync', '-a', '--delete'] + glob_source + [installdir]
                    print(" ".join(rsync))
                    subprocess.run(rsync, check=True)
                except subprocess.CalledProcessError as e:
                    print("ERROR:", e)
                    sys.exit(1)

            # Copy single file manuals (PDF, TXT and EPUB)
            for filetype in ['epub', 'pdf', 'txt']:
                if filetype == 'epub':
                    source = path.join(builddir, '*.epub')
                else:
                    source = path.join(builddir, filetype, '*' + filetype)
                dest = path.join(installdir, directory + '.' + filetype)
                for sourcefile in glob.glob(source):
                    print(f"Copying '{sourcefile}' to '{dest}'")
                    shutil.copy2(sourcefile, dest)
            print()

            run_publican('clean')
            print(f"{directory} Build complete\n")
            buildcount += len(langs)

    # end docbook build loop

    print(f"Done - {buildcount} docbooks built.\n")

# end main()


if __name__ == '__main__':
    main()
