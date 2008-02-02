<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: upgrade_unattended.php,v 1.1.2.1 2007-10-13 22:34:59 giallu Exp $
	# --------------------------------------------------------

	@set_time_limit ( 0 ) ;

	$g_skip_open_db = true;  # don't open the database in database_api.php
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
	$g_error_send_page_header = false; # suppress page headers in the error handler

	define( 'BAD', 0 );
	define( 'GOOD', 1 );

	$g_failed = false;

	# -------
	# print test result
	function print_test_result( $p_result, $p_hard_fail=true, $p_message='' ) {
		global $g_failed;
		if ( BAD == $p_result ) {
			if ( $p_hard_fail ) {
				$g_failed = true;
				echo " - ERROR: ";
			} else {
				echo " - WARNING: ";
			}
			if ( '' != $p_message ) {
				echo $p_message;
			}
		}

		if ( GOOD == $p_result ) {
			echo " - GOOD";
		}
		echo "\n";
	}

	# @@@ upgrade list moved to the bottom of upgrade_inc.php
	$result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ), 
			config_get_global( 'db_username' ), config_get_global( 'db_password' ), 
			config_get_global( 'database_name' ) );

	if ( false == $result ) {
		echo "Opening connection to database " . 
			config_get_global( 'database_name' ) .
			" on host " . config_get_global( 'hostname' ) . 
			" with username " . config_get_global( 'db_username' ) .
			" failed: " . db_error_msg() . "\n";
		exit(1);
	}

	# check to see if the new installer was used
	if ( -1 == config_get( 'database_version', -1 ) ) {
		# Old database detected: run the old style upgrade set
		if ( ! db_table_exists( config_get( 'mantis_upgrade_table' ) ) ) {
			# Create the upgrade table if it does not exist
			$query = "CREATE TABLE " . config_get( 'mantis_upgrade_table' ) .
				"(upgrade_id char(20) NOT NULL,
				description char(255) NOT NULL,
				PRIMARY KEY (upgrade_id))";

			$result = db_query( $query );
		}

		# link the data structures and upgrade list
		require_once ( 'upgrade_inc.php' );
		$error = $upgrade_set->run_all_unattended();

		if ( true == $error ) {
			exit (1);
		}
	}

	# read control variables with defaults
	$f_hostname = gpc_get( 'hostname', config_get( 'hostname', 'localhost' ) );
	$f_db_type = gpc_get( 'db_type', config_get( 'db_type', '' ) );
	$f_database_name = gpc_get( 'database_name', config_get( 'database_name', 'bugtrack') );
	$f_db_username = gpc_get( 'db_username', config_get( 'db_username', '' ) );
	$f_db_password = gpc_get( 'db_password', config_get( 'db_password', '' ) );
	$f_db_exists = gpc_get_bool( 'db_exists', false );

	# install the tables
	$GLOBALS['g_db_type'] = $f_db_type; # database_api references this
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'schema.php' );
	$g_db = ADONewConnection( $f_db_type );

	echo "\nPost 1.0 schema changes\n";
	echo "Connecting to database... ";
	$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );

	if (false == $t_result ) {
		echo "failed\n";
		exit (1);
	}

	echo "OK\n";

	$g_db_connected = true; # fake out database access routines used by config_get
	$t_last_update = config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS );
	$lastid = sizeof( $upgrade ) - 1;
	$i = $t_last_update + 1;

	while ( ( $i <= $lastid ) && ! $g_failed ) {
		echo 'Create Schema ( ' . $upgrade[$i][0] . ' on ' . $upgrade[$i][1][0] . ' )';
		$dict = NewDataDictionary($g_db);

		if ( $upgrade[$i][0] == 'InsertData' ) {
			$sqlarray = call_user_func_array( $upgrade[$i][0], $upgrade[$i][1] );
		} else {
			$sqlarray = call_user_func_array(Array($dict,$upgrade[$i][0]),$upgrade[$i][1]);
		}

		$ret = $dict->ExecuteSQLArray($sqlarray);
		if ( $ret == 2 ) {
			print_test_result( GOOD );
			config_set( 'database_version', $i );
		} else {
			print_test_result( BAD, true, $sqlarray[0] . '<br />' . $g_db->ErrorMsg() );
		}

		$i++;
	}

	if ( false == $g_failed ) {
		exit (0);
	}

	exit (1)
# vim: noexpandtab tabstop=4 softtabstop=0:
?>
