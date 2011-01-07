<?php
# MantisBT - a php based bugtracking system

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

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

@set_time_limit( 0 );

$g_skip_open_db = true;  # don't open the database in database_api.php
/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
$g_error_send_page_header = false; # suppress page headers in the error handler

$g_failed = false;

/* This script is probably meant to be executed from PHP CLI and hence should
 * not be interpreted as text/html. However saying that, we do call gpc_
 * functions that only make sense in PHP CGI mode. Given this mismatch we'll
 * just assume for now that this script is meant to be used from PHP CGI and
 * the output is meant to be text/plain. We also need to prevent Internet
 * Explorer from ignoring our MIME type and using it's own MIME sniffing.
 */
header( 'Content-Type: text/plain' );
header( 'X-Content-Type-Options: nosniff' );

/**
 * Print the result of an upgrade step.
 * 
 * @param integer $result       GOOD or BAD.
 * @param bool    $p_hard_fail  If result is BAD, sets the global failure flag.
 * @param string  $p_message    The message describing the upgrade step.
 * @access private
 */
function print_test_result( $p_result, $p_hard_fail = true, $p_message = '' ) {
	global $g_failed;
	if( BAD == $p_result ) {
		if( $p_hard_fail ) {
			$g_failed = true;
			echo " - ERROR: ";
		} else {
			echo " - WARNING: ";
		}
		if( '' != $p_message ) {
			echo $p_message;
		}
	}

	if( GOOD == $p_result ) {
		echo " - GOOD";
	}
	echo "\n";
}

$result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ),
	config_get_global( 'db_username' ), config_get_global( 'db_password' ),
	config_get_global( 'database_name' ) );

if( false == $result ) {
	echo "Opening connection to database " .
		config_get_global( 'database_name' ) .
		" on host " . config_get_global( 'hostname' ) .
		" with username " . config_get_global( 'db_username' ) .
		" failed: " . db_error_msg() . "\n";
	exit( 1 );
}

# check to see if the new installer was used
if ( -1 == config_get( 'database_version', -1 ) ) {
	echo "Upgrade from the current installed MantisBT version is no longer supported.  If you are using MantisBT version older than 1.0.0, then upgrade to v1.0.0 first.";
	exit( 1 );
}

# read control variables with defaults
$f_hostname = gpc_get( 'hostname', config_get( 'hostname', 'localhost' ) );
$f_db_type = gpc_get( 'db_type', config_get( 'db_type', '' ) );
$f_database_name = gpc_get( 'database_name', config_get( 'database_name', 'bugtrack' ) );
$f_db_username = gpc_get( 'db_username', config_get( 'db_username', '' ) );
$f_db_password = gpc_get( 'db_password', config_get( 'db_password', '' ) );
$f_db_exists = gpc_get_bool( 'db_exists', false );

# install the tables
if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $f_db_type ) ||
     !file_exists( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR . 'adodb-' . $f_db_type . '.inc.php' ) ) {
	echo 'Invalid db type ' . htmlspecialchars( $f_db_type ) . '.';
	exit;
}

$GLOBALS['g_db_type'] = $f_db_type; # database_api references this
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'schema.php' );
$g_db = ADONewConnection( $f_db_type );

echo "\nPost 1.0 schema changes\n";
echo "Connecting to database... ";
$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );

if( false == $t_result ) {
	echo "failed\n";
	exit( 1 );
}

echo "OK\n";

$g_db_connected = true; # fake out database access routines used by config_get
$t_last_update = config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS );
$lastid = count( $upgrade ) - 1;
$i = $t_last_update + 1;

while(( $i <= $lastid ) && !$g_failed ) {
	echo 'Create Schema ( ' . $upgrade[$i][0] . ' on ' . $upgrade[$i][1][0] . ' )';
	$dict = NewDataDictionary( $g_db );

	if( $upgrade[$i][0] == 'InsertData' ) {
		$sqlarray = call_user_func_array( $upgrade[$i][0], $upgrade[$i][1] );
	} else {
		$sqlarray = call_user_func_array( Array( $dict, $upgrade[$i][0] ), $upgrade[$i][1] );
	}

	$ret = $dict->ExecuteSQLArray( $sqlarray );
	if( $ret == 2 ) {
		print_test_result( GOOD );
		config_set( 'database_version', $i );
	} else {
		print_test_result( BAD, true, $sqlarray[0] . '<br />' . $g_db->ErrorMsg() );
	}

	$i++;
}

if( false == $g_failed ) {
	exit( 0 );
}

exit( 1 );

# vim: noexpandtab tabstop=4 softtabstop=0:
