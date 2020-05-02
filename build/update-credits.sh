#!/bin/bash
#------------------------------------------------------------------------------
#
# update-credits.sh
#
# This script will update the CREDITS file's Contributors section with the list
# of all people who authored commits in the current branch of the GIT repository
# (using the shortlog).
#
# The repository's .mailmap file should be updated as appropriate (see the
# git shortlog man page for details) prior to running this script, so that
# commit made by the same person under different names and/or e-mail addresses
# are properly aggregated.
#
#------------------------------------------------------------------------------


#------------------------------------------------------------------------------
# Parameters (edit variables as appropriate)
#

FILE_CREDITS=doc/CREDITS

PATTERN_BEG="^Contributors$"
PATTERN_END="^Other Contributors$"


#------------------------------------------------------------------------------
# Initialization
#

# Abort if Credits file not found
if [ ! -w $FILE_CREDITS ]
then
	echo "ERROR: file '$FILE_CREDITS' not found or not writable"
	exit 1
fi

# Determine the beginning and end of 'Contributors' section

# The list begins after the 1st empty line following PATTERN_BEG
contrib_beg=$(awk "/$PATTERN_BEG/ {
		while(getline line && line !~ \"^$\");
		print NR
	}" $FILE_CREDITS)

# End assumes 2 empty lines before PATTERN_END
contrib_end=$(awk "/$PATTERN_END/ { print NR - 2 }" $FILE_CREDITS)

# Abort if patterns not found (wrong format in CREDITS file
if [[ $contrib_beg -eq 0 || $contrib_end -eq 0 ]]
then
	echo "ERROR: Contributors section not found in '$FILE_CREDITS'"
	exit 1
fi


#------------------------------------------------------------------------------
# Main
#

# Generate new Contributors list
echo "Generating the Contributors list from git shortlog"
FILE_TMP=$(mktemp)
git shortlog -s -n |cut -f2 >$FILE_TMP

# Replace old Contributors list
echo "Updating Credits file '$FILE_CREDITS'"
sed -n -i.bak "
		1,${contrib_beg} p;
		${contrib_beg} r$FILE_TMP
		${contrib_end},$ p
	" $FILE_CREDITS

# Cleanup
rm $FILE_TMP
rm $FILE_CREDITS.bak

echo "Done"
