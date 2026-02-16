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
 * CI unit tests for REST API.
 *
 * This script will generate an project, versions, tags and anonymous
 * user to execute the REST API test suite.
 *
 * @package Tests
 * @copyright Copyright 2025  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

global $g_bypass_headers;
$g_bypass_headers = true;

require_once( dirname( __DIR__ ) . '/core.php' );

# Make sure this script doesn't run via the webserver
if( php_sapi_name() != 'cli' ) {
	echo basename( __FILE__  ) . " is not allowed to run through the webserver.\n";
	exit( 1 );
}

$t_password = md5( '123456' );
$t_cookie_string = crypto_generate_uri_safe_nonce( 64 );
$t_timestamp = db_now();

db_query( "INSERT INTO " . db_get_table( 'project' ) . "(name, inherit_global, description) VALUES ('Test Project', true, 'Travis-CI Test Project')" );

db_query( "INSERT INTO " . db_get_table( 'project_version' ) . "(project_id, version, description, released, obsolete, date_order) VALUES (1, '1.0.0', 'Obsolete version', true, true, " . ( $t_timestamp - 120 ) . "),(1, '1.1.0', 'Released version', true, false, " . ( $t_timestamp - 60 ) . "),(1, '2.0.0', 'Future version', false, false, $t_timestamp)" );

db_query( "INSERT INTO " . db_get_table( 'tag' ) . "(user_id, name, description, date_created, date_updated) VALUES (1, 'modern-ui', '', $t_timestamp, $t_timestamp),(1, 'patch', '', $t_timestamp, $t_timestamp)" );

db_query( "INSERT INTO " . db_get_table( 'user' ) . "(username, realname, email, password, cookie_string,enabled, protected, access_level, last_visit, date_created) VALUES ('anonymous', 'Anonymous User', 'anonymous@localhost', '$t_password', '$t_cookie_string', '1', '1', 10, $t_timestamp, $t_timestamp)" );
