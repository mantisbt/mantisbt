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

/**
 * This file handles unattended upgrades of Mantis
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

@set_time_limit( 0 );

# Load the MantisDB core in maintenance mode. This mode will assume that
# config_inc.php hasn't been specified. Thus the database will not be opened
# and plugins will not be loaded.
define( 'MANTIS_MAINTENANCE_MODE', true );

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_api( 'crypto_api.php' );
$g_error_send_page_header = false; # suppress page headers in the error handler

$g_failed = false;

# This script is probably meant to be executed from PHP CLI and hence should
# not be interpreted as text/html. However saying that, we do call gpc_
# functions that only make sense in PHP CGI mode. Given this mismatch we'll
# just assume for now that this script is meant to be used from PHP CGI and
# the output is meant to be text/plain. We also need to prevent Internet
# Explorer from ignoring our MIME type and using it's own MIME sniffing.
header( 'Content-Type: text/plain' );
header( 'X-Content-Type-Options: nosniff' );

/**
 * Print the result of an upgrade step.
 *
 * @param integer $p_result    GOOD or BAD.
 * @param boolean $p_hard_fail If result is BAD, sets the global failure flag.
 * @param string  $p_message   The message describing the upgrade step.
 * @access private
 * @return void
 */
function print_test_result( $p_result, $p_hard_fail = true, $p_message = '' ) {
	global $g_failed;
	if( BAD == $p_result ) {
		if( $p_hard_fail ) {
			$g_failed = true;
			echo '- ERROR: ';
		} else {
			echo '- WARNING: ';
		}
		if( '' != $p_message ) {
			echo $p_message;
		}
	}

	if( GOOD == $p_result ) {
		echo '- GOOD';
	}
	echo "\n";
}

$t_result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ),
	config_get_global( 'db_username' ), config_get_global( 'db_password' ),
	config_get_global( 'database_name' ) );

if( false == $t_result ) {
	echo 'Opening connection to database ' .
		config_get_global( 'database_name' ) .
		' on host ' . config_get_global( 'hostname' ) .
		' with username ' . config_get_global( 'db_username' ) .
		' failed: ' . db_error_msg() . "\n";
	exit( 1 );
}

# TODO: Enhance this check to support the mode where this script is called on an empty database.
# check to see if the new installer was used
if( -1 == config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS ) ) {
		echo 'Upgrade from the current installed MantisBT version is no longer supported.  If you are using MantisBT version older than 1.0.0, then upgrade to v1.0.0 first.';
		exit( 1 );
}

# read control variables with defaults
$t_db_type = config_get_global( 'db_type' );

# install the tables
if( !preg_match( '/^[a-zA-Z0-9_]+$/', $t_db_type ) ||
	!file_exists( dirname( dirname( __FILE__ ) ) . '/vendor/adodb/adodb-php/drivers/adodb-' . $t_db_type . '.inc.php' ) ) {
	echo 'Invalid db type ' . htmlspecialchars( $t_db_type ) . '.';
	exit;
}

$GLOBALS['g_db_type'] = $t_db_type; # database_api references this
require_once( dirname( __FILE__ ) . '/schema.php' );
$g_db = ADONewConnection( $t_db_type );

echo "\nPost 1.0 schema changes\n";
echo 'Connecting to database... ';
$t_result = @$g_db->Connect( config_get_global( 'hostname' ), config_get_global( 'db_username' ), config_get_global( 'db_password' ), config_get_global( 'database_name' ) );

if( false == $t_result ) {
	echo 'Failed.' . "\n";
	exit( 1 );
}

echo 'OK' . "\n";

$g_db_connected = true; # fake out database access routines used by config_get
$t_last_update = config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS );
$t_last_id = count( $g_upgrade ) - 1;
$i = $t_last_update + 1;
$t_count_done = 0;

while( ( $i <= $t_last_id ) && !$g_failed ) {
	if ( $g_upgrade[$i] === null ) {
		$i++;
		$t_count_done++;
		continue;
	}

	$t_dict = NewDataDictionary( $g_db );
	$t_sql = true;
	$t_target = $g_upgrade[$i][1][0];

	if( $g_upgrade[$i][0] == 'InsertData' ) {
		$t_sqlarray = call_user_func_array( $g_upgrade[$i][0], $g_upgrade[$i][1] );
	} else if( $g_upgrade[$i][0] == 'UpdateSQL' ) {
		$t_sqlarray = array(
			$g_upgrade[$i][1],
		);

		$t_target = $g_upgrade[$i][1];
	} else if( $g_upgrade[$i][0] == 'UpdateFunction' ) {
		$t_sqlarray = array(
			$g_upgrade[$i][1],
		);

		if( isset( $g_upgrade[$i][2] ) ) {
			$t_sqlarray[] = $g_upgrade[$i][2];
		}

		$t_sql = false;
		$t_target = $g_upgrade[$i][1];
	} else {
		# 0: function to call, 1: function params, 2: function to evaluate before calling upgrade, if false, skip upgrade.
		if( isset( $g_upgrade[$i][2] ) ) {
			if( call_user_func_array( $g_upgrade[$i][2][0], $g_upgrade[$i][2][1] ) ) {
				$t_sqlarray = call_user_func_array( array( $t_dict, $g_upgrade[$i][0] ), $g_upgrade[$i][1] );
			} else {
				$t_sqlarray = array();
			}
		} else {
			$t_sqlarray = call_user_func_array( array( $t_dict, $g_upgrade[$i][0] ), $g_upgrade[$i][1] );
		}
	}

	echo 'Schema step ' . $i . ': ' . $g_upgrade[$i][0] . ' ( ' . $t_target . ' ) ';
	if( $t_sql ) {
		$t_ret = $t_dict->ExecuteSQLArray( $t_sqlarray, false );
	} else {
		if( isset( $t_sqlarray[1] ) ) {
			$t_ret = call_user_func( 'install_' . $t_sqlarray[0], $t_sqlarray[1] );
		} else {
			$t_ret = call_user_func( 'install_' . $t_sqlarray[0] );
		}
	}

	if( $t_ret == 2 ) {
		print_test_result( GOOD );
		config_set( 'database_version', $i, ALL_USERS, ALL_PROJECTS );
	} else {
		$t_msg = $t_sqlarray[0];
		if( !is_blank( $g_db->ErrorMsg() ) ) {
			$t_msg .= "\n" . $g_db->ErrorMsg();
		}
		print_test_result( BAD, true, $t_msg );
	}

	$i++;
	$t_count_done++;
}

echo $t_count_done . ' schema upgrades executed.' . "\n";

if( false == $g_failed ) {
	echo 'Done.' . "\n";
	exit( 0 );
}

echo "Failed.\n";
exit( 1 );
