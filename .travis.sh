#!/bin/bash
#-----------------------------------------------------------
#
# Purpose: Run phing in a travis environment
#
# Target system: travis-ci
#-----------------------------------------------------------

installPearTask ()
{
    sudo apt-get update -qq
    
    echo -e "\n=> Auto-discover pear channels and upgrade ..."
    echo -e "\n=> Set auto_discover ..."
	pear config-set auto_discover 1
    echo -e "\n=> channel-update ..."
    pear -qq channel-update pear.php.net
    echo -e "\n=> upgrade ..."
    pear list-upgrades
    echo -e "\n=> channel-discover ..."
    pear -qq channel-discover pear.phing.info
    echo " ... OK"

    echo -e "\n=> Installing / upgrading phpcpd ... "
    which phpcpd >/dev/null                      &&
        pear upgrade pear.phpunit.de/phpcpd ||
        pear install pear.phpunit.de/phpcpd
    phpenv rehash

    echo -e "\n=> Installing / upgrading phploc ... "
    which phploc >/dev/null                      &&
        pear upgrade pear.phpunit.de/phploc ||
        pear install pear.phpunit.de/phploc
    phpenv rehash

    echo -e "\n=> Installing / upgrading phpdepend ... "
    if [[ $TRAVIS_PHP_VERSION < 5.3 ]]; then
        which pdepend >/dev/null                      &&
            pear upgrade pear.pdepend.org/PHP_Depend-1.1.0 ||
            pear install pear.pdepend.org/PHP_Depend-1.1.0
    else
        which pdepend >/dev/null                      &&
            pear upgrade pear.pdepend.org/PHP_Depend-beta ||
            pear install pear.pdepend.org/PHP_Depend-beta
    fi
    phpenv rehash

    echo -e "\n=> Installing / upgrading phpcs ... "
    which phpcs >/dev/null                             &&
        pear upgrade pear.php.net/PHP_CodeSniffer ||
        pear install pear.php.net/PHP_CodeSniffer
    phpenv rehash
    # re-test for phpcs:
    phpcs --version 2>&1 >/dev/null   &&
        echo "... OK"           ||
        return 1

    sudo apt-get install python-docutils
    pear install VersionControl_Git-alpha
    pear install VersionControl_SVN-alpha
    pear install pear/XML_Serializer-beta
    pear install --alldeps PEAR_PackageFileManager
    pear install --alldeps PEAR_PackageFileManager2
    pear install Net_Growl
    pear install HTTP_Request2

    # update paths
    phpenv rehash
}


#-----------------------------------------------------------

    installPearTask &&
        echo -e "\nSUCCESS - PHP ENVIRONMENT READY." ||
        ( echo "=== FAILED."; exit 1 )

    if [[ $TRAVIS_PHP_VERSION < 5.3 ]]; then
    	pear install -f phpunit/File_Iterator-1.3.2
    	pear install -f phpunit/PHP_TokenStream-1.1.4
    	pear install -f phpunit/PHP_Timer-1.0.3
    	pear install -f phpunit/Text_Template-1.1.1
        pear upgrade pecl.php.net/Phar ||
            pear install pecl.php.net/Phar
        phpenv rehash
    else
    	composer selfupdate --quiet
        composer install
    fi

    phpenv config-add .travis.php.ini

    echo "=== SETTING GIT IDENTITY ==="
    git config --global user.email ""
    git config --global user.name "MantisBT"

    echo "=== TESTING PHING ==="
    phing
