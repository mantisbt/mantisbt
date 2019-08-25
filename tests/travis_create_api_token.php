#!/usr/bin/php -q
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
 * Travis CI unit tests for REST API.
 * This script will generate an API token to execute the REST API test suite.
 * @package Tests
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

global $g_bypass_headers;
$g_bypass_headers = true;

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_api( 'api_token_api.php' );

# Make sure this script doesn't run via the webserver
if( php_sapi_name() != 'cli' ) {
	echo basename( __FILE__  ) . " is not allowed to run through the webserver.\n";
	exit( 1 );
}

# Default administrator account
$t_user_id = 1;

$t_token_name = 'Travis_PHPUnit';
if( api_token_name_is_unique( $t_token_name, $t_user_id ) ) {
	$t_token = api_token_create( $t_token_name, 1 );
} else {
	echo "ERROR: token $t_token_name already exists.\n";
	exit( 1 );
}

echo $t_token;
