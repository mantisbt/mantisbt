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
    print('''Usage: {} /path/to/mantisbt/docbook /path/to/install \
[<lang> ...]
    Options:  -h | --help           Print this usage message
              -d | --delete         Delete install directory before building
                   --epub           Build EPUB manual
                   --html           Build HTML manual
                   --pdf            Build PDF manual
                   --txt            Build TXT manual
                   --release        Build single file types used for
                                    release tarballs
              -a | --all            Build all manual types
'''.format(path.basename(__file__)))
# end usage()


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError as err:
        print(err)
        usage()
        sys.exit(2)

    if len(args) < 2:
        usage()
        sys.exit(1)

    delete = False
    types = {MAKE: "html pdf", PUBLICAN: "html,pdf"}

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-d", "--delete"):
            delete = True

        elif opt in ("-a", "--all"):
            types[MAKE] = "html html_onefile html.tar.gz text pdf"
            types[PUBLICAN] = "html,html-desktop,txt,pdf,epub"

        elif opt == "--epub":
            types[MAKE] = "epub"
            types[PUBLICAN] = "epub"

        elif opt == "--html":
            types[MAKE] = "html html_onefile html.tar.gz"
            types[PUBLICAN] = "html,html-desktop"

        elif opt == "--pdf":
            types[MAKE] = "pdf"
            types[PUBLICAN] = types[MAKE]

        elif opt == "--txt":
            types[MAKE] = "text"
            types[PUBLICAN] = "txt"

        elif opt == "--release":
            types[MAKE] = "html_onefile pdf text"
            types[PUBLICAN] = "html-desktop,pdf,txt"

    docroot = path.abspath(args[0])
    installroot = args[1]
    languages = []

    if len(sys.argv) > 2:
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
        if len(languages) > 0:
            langs = languages
        else:
            langs = next(os.walk(builddir))[1]
            if langs.count('.svn'):
                langs.remove('.svn')
            if langs.count('tmp'):
                langs.remove('tmp')

        if path.exists('publican.cfg'):
            # Build docbook with PUBLICAN

            print("Building manual in '{}'\n".format(builddir))
            os.system('publican clean')
            os.system('publican build --formats={} --langs={}'.format(
                      types[PUBLICAN], ','.join(langs)))

            print("\nCopying generated manuals to '{}'".format(installroot))
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
                if len(glob.glob(source)) > 0:
                    rsync = "rsync -a --delete {} {}".format(
                        source, installdir
                    )
                    print(rsync)
                    ret = subprocess.call(rsync, shell=True)
                    if ret != 0:
                        print('ERROR: rsync call failed with exit code '
                              .format(ret))

                # Copy single file manuals (PDF, TXT and EPUB)
                for filetype in ['epub', 'pdf', 'txt']:
                    if filetype == 'epub':
                        source = path.join(builddir, '*.epub')
                    else:
                        source = path.join(builddir, filetype, '*' + filetype)
                    dest = path.join(installdir, directory + '.' + filetype)
                    for sourcefile in glob.glob(source):
                        print("Copying '{}' to '{}'".format(sourcefile, dest))
                        shutil.copy2(sourcefile, dest)

            os.system('publican clean')
            print("\nBuild complete\n")
            buildcount += len(langs)
        else:
            # Build docbook with MAKE

            for lang in langs:
                if not path.isdir(path.join(builddir, lang)):
                    print("WARNING: Unknown language '{}' in '{}'"
                          .format(lang, builddir))
                    continue

                builddir = path.join(builddir, lang)
                installdir = path.join(installroot, lang)
                os.chdir(builddir)

                if not path.exists('Makefile'):
                    continue

                print("Building manual in '%s'\n".format(builddir))
                os.system(
                    'make clean {} 2>&1 && '
                    'make INSTALL_DIR={} install 2>&1'
                    .format(types[MAKE], installdir)
                )
                os.system('make clean 2>&1')
                print("\nBuild complete\n")
                buildcount += 1

    # end docbook build loop

    print("Done - {} docbooks built.\n".format(buildcount))

# end main()


if __name__ == '__main__':
    main()
