#!/bin/bash
#------------------------------------------------------------------------------
#
# update-copyright-year.sh
#
# This script will bump the copyright year in all of the MantisBT source files
# including docbook
#
#------------------------------------------------------------------------------


#------------------------------------------------------------------------------
# Initialization
#

# Reference file to retrieve current copyright year
if [ -z "$REF_FILE" ]
then
   REF_FILE=core.php
fi
if [ ! -r "$REF_FILE" ]
then
	echo "ERROR: Reference file '$REF_FILE' not found in '$PWD'"
	echo "The script must be executed in the MantisBT root directory"
	exit 1
fi


# Regular expressions
REGEX_MANTISBT="Copyright .*[0-9]{4} +MantisBT Team"
REGEX_SOAP_API="Copyright .*[0-9]{4} +Victor Boctor"
REGEX_DOCS='<year>[0-9]{4}<\/year>|<!ENTITY YEAR "[0-9]{4}">'

# Determine copyright year based on reference file
COPYRIGHT_YEAR_OLD=$(sed -rn "/$REGEX_MANTISBT/ s/^.*- *([0-4]{4}) .*$/\1/p" $REF_FILE)
COPYRIGHT_YEAR_NEW=$(( $COPYRIGHT_YEAR_OLD + 1 ))


#------------------------------------------------------------------------------
# Replace function
#
function bump_copyright()
{
	git grep -E -l "$1" |
		xargs sed -i.bak -r -e "/$1/ s/$COPYRIGHT_YEAR_OLD/$COPYRIGHT_YEAR_NEW/"
}

#------------------------------------------------------------------------------
# Main
#

echo "Ready to update MantisBT copyright year from $COPYRIGHT_YEAR_OLD to $COPYRIGHT_YEAR_NEW"
echo "Press enter to proceed"
read

if [ -n "$REPLY" ]
then
	echo "Aborting !"
	exit
fi

# MantisBT files
echo "Updading MantisBT core files"
bump_copyright "$REGEX_MANTISBT"

# SOAP API files
echo "Updading SOAP API files"
bump_copyright "$REGEX_SOAP_API"

# Documentation
echo "Updading Documentation"
bump_copyright "$REGEX_DOCS"


# Cleanup
echo
echo "Updates complete"

echo "Removing backup files"
find . -name "*.bak" -delete
