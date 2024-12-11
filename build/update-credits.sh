#!/usr/bin/env bash
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
# Check if .mailmap needs to be updated
#

# Get list of author names or e-mails
# Params
# - 1: Git log format string
# - 2: Optional awk FS delimiter
get_list() {
	local OPT=${2:+-F"$2"}
	# shellcheck disable=SC2086
	git log --format="$1" | sort -u | awk $OPT '{print $1}' | uniq -c | grep -v '^ *1 '| cut -c9-
}

# Author names having more than one e-mail address not already aliased
readarray -t AUTHORS < <(get_list "%aN <%aE>" "<")

# Author e-mails linked to more than one name, and not already aliased
readarray -t EMAILS < <(get_list "%aE %aN")

# If we have any matches, display them and abort
if [[ ${#AUTHORS[@]} -gt 0 || ${#EMAILS[@]} -gt 0 ]]
then
	echo "The following commit Authors/e-mails should be defined in .mailmap."
	echo "See https://git-scm.com/docs/gitmailmap for details."
	echo

	for author in "${AUTHORS[@]}" "${EMAILS[@]}"
	do
		git log --author="$author" --format="%aN <%aE>" | sort -u
	done

	echo
	echo "Please fix and run this script again."
	exit 1
else
	echo "No duplicate authors found in Git history"
fi

#------------------------------------------------------------------------------
# Main
#

# Generate new Contributors list
echo "Generating the Contributors list from git shortlog"
FILE_TMP=$(mktemp)
git shortlog -s -n |cut -f2 >"$FILE_TMP"

# Replace old Contributors list
echo "Updating Credits file '$FILE_CREDITS'"
sed -n -i.bak "
		1,${contrib_beg} p;
		${contrib_beg} r$FILE_TMP
		${contrib_end},$ p
	" $FILE_CREDITS

# Cleanup
rm "$FILE_TMP"
rm "$FILE_CREDITS.bak"

echo "Done"
