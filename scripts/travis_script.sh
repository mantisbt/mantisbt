#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT Travis-CI test script execution
# -----------------------------------------------------------------------------


# -----------------------------------------------------------------------------
# Install toolchain and build docbooks if they have been modified
#
function build_docbook() {

	DOCBOOKS="Admin_Guide Developers_Guide"

	for BOOK in $DOCBOOKS
	do
		echo
		echo "Building '$BOOK'..."
		cd $TRAVIS_BUILD_DIR/docbook/$BOOK
		make
	done
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
