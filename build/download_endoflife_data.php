#!/usr/bin/env php -q
<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
# See the README and LICENSE files for details

/**
 * Script to generate offline data for End of life checks.
 *
 * @package   scripts
 * @copyright Copyright 2025  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link      https://mantisbt.org
 */

# Not using namespace otherwise PHP throws error due to the shebang
use Mantis\admin\check\EndOfLifeCheck;

# Make sure the script doesn't run via the webserver
if( php_sapi_name() != 'cli' ) {
	echo basename( __FILE__
			) . " is not allowed to run through the webserver.\n";
	exit( 1 );
}

require_once dirname( __DIR__ ) . '/core.php';

echo "Retrieving end-of-life data from '" . EndOfLifeCheck::URL . "'...";

/** @noinspection PhpUnhandledExceptionInspection */
try {
	$t_products = EndOfLifeCheck::dumpProductInfo();
} catch( Exception $e ) {
	echo PHP_EOL;
	echo $e->getMessage() . PHP_EOL;
	echo $e->getPrevious()->getMessage() . PHP_EOL;
	exit( 1 );
}

echo "\nProducts processed:\n  ", implode( ', ', $t_products ), PHP_EOL;
echo "JSON files saved in '"
		. EndOfLifeCheck::getDataDir()
		. "'\n";
echo "Done\n";
