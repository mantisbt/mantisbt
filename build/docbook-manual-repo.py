#!/usr/bin/python -u

# Integrates with docbook-manual.py to build manuals for all tagged
# versions and development branches in the Git repo


import getopt
import os
from os import path
import re
import sys
import time

# Absolute path to docbook-manual.py
manualscript = path.dirname(path.abspath(__file__)) + '/docbook-manual.py'

# Regular expressions of refs to ignore
ignorelist = map(re.compile, [
    'HEAD',
    '->',
    '-1\.0\.[\w\d]+',
    '-1\.1\.[\w\d]+'
])

# Script options
options = "hr:cfda"
long_options = ["help", "ref=", "current", "force", "delete",
                "all", "pdf", "html", "release"]


def usage():
    print '''Usage: docbook-manual-repo /path/to/mantisbt/repo \
/path/to/install [<lang> ...]

    Options:  -h | --help           Print this usage message
              -r | --ref            Select what refs to build
              -c | --current        Build for current branch (no checkout)
              -f | --force          Ignore timestamps and force building
              -d | --delete         Delete install directories before building
                   --html           Build HTML manual
                   --pdf            Build PDF manual
                   --release        Build single file types used for
                                    release tarballs
              -a | --all            Build all manual types'''
# end usage()


def ignore(ref):
    '''Decide which refs to ignore based on regexen listed in 'ignorelist'.
    '''

    ignore = False
    for regex in ignorelist:
        if len(regex.findall(ref)) > 0:
            ignore = True
    return ignore
# end ignore()


def git_current_branch():
    ''' Returns the current git branch's name or the current commit SHA
        if we are in detached HEAD state
    '''
    gitcmd = 'git symbolic-ref --quiet --short HEAD || git rev-parse HEAD'
    return os.popen(gitcmd).read().rstrip()


def git_checkout(branch):
    os.system('git checkout -f %s >/dev/null' % branch)


def main():
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError, err:
        print str(err)
        usage()
        sys.exit(2)

    refs = None
    current = False
    force = False
    pass_opts = ""

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
            pass_opts += " -d"

        elif opt in ("-a", "--all"):
            pass_opts += " -a"

        elif opt == "--html":
            pass_opts += " --html"

        elif opt == "--pdf":
            pass_opts += " --pdf"

        elif opt == "--release":
            pass_opts += " --release"

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
        print "Updating repository in '%s' from default remote" % repo
        os.chdir(repo)
        os.system('git fetch')
        os.system('git remote prune origin')

    if not current and refs is None:
        # List refs from remote branches and tags
        branches = os.popen('git branch -r').read().split()
        tags = os.popen('git tag -l').read().split()

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
        print "\nGenerating documentation for '%s'" % ref

        manualpath = path.join(installroot, refnameregex.search(ref).group(1))

        if not current:
            git_checkout(ref)

        # Get timestamp of last change to docbook sources from git
        lastchange = os.popen('git log --pretty="format:%ct" -n1 -- docbook'
                              ).read()

        buildfile = path.join(manualpath, '.build')
        lastbuild = 0
        if path.exists(buildfile):
            f = open(buildfile, 'r')
            lastbuild = f.read()
            f.close()

        if lastchange > lastbuild or force or current:
            buildcommand = '%s %s %s %s %s' % (
                manualscript,
                pass_opts,
                path.abspath('docbook'),
                manualpath, ' '.join(languages)
            )
            print "Calling: " + buildcommand
            if(os.system(buildcommand)):
                print 'here'

            f = open(buildfile, 'w')
            f.write(lastchange)
            f.close()
        else:
            # Get last build's timestamp from buildfile's modified time
            mtime = float(os.path.getmtime(buildfile))
            print("Docbook source unchanged since last build (%s)" %
                  time.strftime("%a %Y-%m-%d %H:%M:%S", time.localtime(mtime))
                  )
            # 'touch' the flag file to bump the modified time
            os.utime(buildfile, None)

    # Reset repository to originally checked-out branch
    if curbranch != git_current_branch():
        print "\nRestoring originally checked-out branch"
        git_checkout(curbranch)

# end main()

if __name__ == '__main__':
    main()
