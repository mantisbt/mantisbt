#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT Travis-CI test script execution
# -----------------------------------------------------------------------------


# -----------------------------------------------------------------------------
# Install toolchain and build docbooks if they have been modified
#
function build_docbook() {

	# Get the list of modified docbooks from the commit range
	UPDATED_DOCBOOKS=$(
		git diff --name-only $TRAVIS_COMMIT_RANGE -- docbook/ |
		grep -i '\.xml$' |
		cut -d '/' -f 2 |
		sort -u
	)

	# Build the docbook if any XML files have been updated
	if [[ -n $UPDATED_DOCBOOKS ]]
	then
		# Install DocBook toolchain
		echo "Installing Publican..."
		sudo apt-get install publican

		# Build the books
		for BOOK in $UPDATED_DOCBOOKS
		do
			echo
			echo "Building '$BOOK'..."
			cd $TRAVIS_BUILD_DIR/docbook/$BOOK
			make
		done
	else
		echo "No documentation changes in $TRAVIS_COMMIT_RANGE"
	fi
}


# -----------------------------------------------------------------------------
# Main block
#
if [[ -z $DOCBOOK ]]
then
	echo "Executing MantisBT test scripts..."
	phpunit --bootstrap ./tests/bootstrap.php ./tests/AllTests.php
else
	echo "Building DocBook..."
	build_docbook
fi
