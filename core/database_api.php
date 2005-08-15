<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: database_api.php,v 1.46 2005-08-15 22:13:52 thraxisp Exp $
	# --------------------------------------------------------

	### Database ###

	# This is the general interface for all database calls.
	# Use this as a starting point to port to other databases

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'adodb/adodb.inc.php' );

	# An array in which all executed queries are stored.  This is used for profiling
	$g_queries_array = array();

	# Stores whether a database connection was succesfully opened.
	$g_db_connected = false;

	# set adodb fetch mode
	# most drivers don't implement this, but for mysql there is a small internal php performance gain for using it
	if( $g_db_type == 'mysql' ) {	
		$ADODB_FETCH_MODE = ADODB_FETCH_BOTH; 
	}
	
	# --------------------
	# Make a connection to the database
	function db_connect( $p_dsn, $p_hostname = null, $p_username = null, $p_password = null, $p_database_name = null ) {
		global $g_db_connected, $g_db;

		if(  $p_dsn === false ) {
			$g_db = ADONewConnection( config_get_global( 'db_type' ) );
			$t_result = $g_db->Connect($p_hostname, $p_username, $p_password, $p_database_name );
		} else {
			$g_db = ADONewConnection( $p_dsn );
			$t_result = $g_db->IsConnected();
		}
		
		if ( !$t_result ) {
			db_error();
			trigger_error( ERROR_DB_CONNECT_FAILED, ERROR );
			return false;
		}

		$g_db_connected = true;

		return true;
	}

	# --------------------
	# Make a persistent connection to the database
	function db_pconnect( $p_dsn, $p_hostname = null, $p_username = null, $p_password = null, $p_database_name = null ) {
		global $g_db_connected, $g_db;

		if(  $p_dsn === false ) {
			$g_db = ADONewConnection( config_get_global( 'db_type' ) );
			$t_result = $g_db->PConnect($p_hostname, $p_username, $p_password, $p_database_name );
		} else {
			$g_db = ADONewConnection( $p_dsn );
			$t_result = $g_db->IsConnected();
		}

		if ( !$t_result ) {
			db_error();
			trigger_error( ERROR_DB_CONNECT_FAILED, ERROR );
			return false;
		}
		$g_db_connected = true;
		return true;
	}

	# --------------------
	# Returns whether a connection to the database exists
	function db_is_connected() {
		global $g_db_connected;

		return $g_db_connected;
	}

	# --------------------
	# Check is the database is PostgreSQL
	function db_is_pgsql() {
		$t_db_type = config_get( 'db_type' );

		switch( $t_db_type ) {
			case 'postgres':
			case 'postgres64':
			case 'postgres7':
			case 'pgsql':
				return true;
		}

		return false;
	}

	# --------------------
	# timer analysis
	function microtime_float() {
		list( $usec, $sec ) = explode( " ", microtime() );
		return ( (float)$usec + (float)$sec );
	}

	# --------------------
	# execute query, requires connection to be opened
	# If $p_error_on_failure is true (default) an error will be triggered
	#  if there is a problem executing the query.
	function db_query( $p_query, $p_limit = -1, $p_offset = -1 ) {
		global $g_queries_array, $g_db;

		$t_start = microtime_float();
		if ( ( $p_limit != -1 ) || ( $p_offset != -1 ) ) {
			$t_result = $g_db->SelectLimit( $p_query, $p_limit, $p_offset );
		} else {
			$t_result = $g_db->Execute( $p_query );
		}
		$t_elapsed = number_format( microtime_float() - $t_start, 4);
		array_push ( $g_queries_array, array( $p_query, $t_elapsed ) );

		if ( !$t_result ) {
			db_error($p_query);
			trigger_error( ERROR_DB_QUERY_FAILED, ERROR );
			return false;
		} else {
			return $t_result;
		}
	}

	# --------------------
	function db_num_rows( $p_result ) {
		global $g_db;

		return $p_result->RecordCount( );
	}

	# --------------------
	function db_affected_rows() {
		global $g_db;

		return $g_db->Affected_Rows( );
	}

	# --------------------
	function db_fetch_array( & $p_result ) {
		global $g_db, $g_db_type;

		if ( $p_result->EOF ) {
			return false;
		}		

		# mysql obeys FETCH_MODE_BOTH, hence ->fields works, other drivers do not support this
		if( $g_db_type == 'mysql' ) {	
			$t_array = $p_result->fields;
 			$p_result->MoveNext();
			return $t_array;
		} else { 
			$test = $p_result->GetRowAssoc(false);
			$p_result->MoveNext();
			return $test;
		}
	}

	# --------------------
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {
		global $g_db;

		if ( $p_result && ( db_num_rows( $p_result ) > 0 ) ) {
			$p_result->Move($p_index1);
			$t_result = $p_result->GetArray();
			return $t_result[0][$p_index2];
		} else {
			return false;
		}
	}

	# --------------------
	# return the last inserted id
	function db_insert_id($p_table = null) {
		global $g_db;

		if ( isset($p_table) && db_is_pgsql() ) {
			$query = "SELECT currval('".$p_table."_id_seq')";
			$result = db_query( $query );
			return db_result($result);
		}
		return $g_db->Insert_ID( );
	}

	# --------------------
	function db_table_exists( $p_table_name ) {
		global $g_db;

		return in_array ( $p_table_name , $g_db->MetaTables( "TABLE" ) ) ;
	}

	# --------------------
	function db_field_exists( $p_field_name, $p_table_name ) {
		global $g_db;
		return in_array ( $p_field_name , $g_db->MetaColumnNames( $p_table_name ) ) ;
	}

	# --------------------
	# Check if there is an index defined on the specified table/field and with
	# the specified type.
	#
	# @@@ thraxisp - this only works with MySQL
	#
	# $p_table: Name of table to check
	# $p_field: Name of field to check
	# $p_key: key type to check for (eg: PRI, MUL, ...etc)
	function db_key_exists_on_field( $p_table, $p_field, $p_key ) {
		$c_table = db_prepare_string( $p_table );
		$c_field = db_prepare_string( $p_field );
		$c_key   = db_prepare_string( $p_key );

		$query = "DESCRIBE $c_table";
		$result = db_query( $query );
		$count = db_num_rows( $result );
		for ( $i=0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );

			if ( $row['Field'] == $c_field ) {
				return ( $row['Key'] == $c_key );
			}
		}
		return false;
	}

	# --------------------
	function db_error_num() {
		global $g_db;

		return $g_db->ErrorNo();
	}

	# --------------------
	function db_error_msg() {
		global $g_db;

		return $g_db->ErrorMsg();
	}

	# --------------------
	# display both the error num and error msg
	function db_error( $p_query=null ) {
		if ( null !== $p_query ) {
			error_parameters( db_error_num(), db_error_msg(), $p_query );
		} else {
			error_parameters( db_error_num(), db_error_msg() );
		}
	}

	# --------------------
	# close the connection.
	# Not really necessary most of the time since a connection is
	# automatically closed when a page finishes loading.
	function db_close() {
		global $g_db;

		$t_result = $g_db->Close();
	}

	# --------------------
	# prepare a string before DB insertion
	# @@@ should default be return addslashes( $p_string ); or generate an error
	function db_prepare_string( $p_string ) {
		global $g_db;
		$t_db_type = config_get( 'db_type' );

		switch( $t_db_type ) {
			case 'mssql':
			case 'odbc_mssql':
				if( ini_get( 'magic_quotes_sybase' ) ) {
					return addslashes( $p_string );
				} else {
					ini_set( 'magic_quotes_sybase', true );
					$t_string = addslashes( $p_string );
					ini_set( 'magic_quotes_sybase', false );
					return $t_string;
				}

			case 'mysql':
				return mysql_escape_string( $p_string );

			# For some reason mysqli_escape_string( $p_string ) always returns an empty
			# string.  This is happening with PHP v5.0.2.
			# @@@ Consider using ADODB escaping for all databases.
			case 'mysqli':
				$t_escaped = $g_db->qstr( $p_string, false );
				return substr( $t_escaped, 1, strlen( $t_escaped ) - 2 );

			case 'postgres':
			case 'postgres64':
			case 'postgres7':
			case 'pgsql':
				return pg_escape_string( $p_string );

			default:
				error_parameters( 'db_type', $t_db_type );
				trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
		}
	}

	# --------------------
	# prepare an integer before DB insertion
	function db_prepare_int( $p_int ) {
		return (int)$p_int;
	}

	# --------------------
	# prepare a boolean before DB insertion
	function db_prepare_bool( $p_bool ) {
		return (int)(bool)$p_bool;
	}

	# --------------------
	# return current timestamp for DB
	function db_now() {
		global $g_db;

		return $g_db->DBTimeStamp(time());
	}

	# --------------------
	# generate a unixtimestamp of a date
	# > SELECT UNIX_TIMESTAMP();
	#	-> 882226357
	# > SELECT UNIX_TIMESTAMP('1997-10-04 22:23:00');
	#	-> 875996580
	function db_timestamp( $p_date=null ) {
		global $g_db;

		if ( null !== $p_date ) {
			$p_timestamp = $g_db->UnixTimeStamp($p_date);
		} else {
			$p_timestamp = time();
		}
		return $g_db->DBTimeStamp($p_timestamp) ;
	}

	function db_unixtimestamp( $p_date=null ) {
		global $g_db;

		if ( null !== $p_date ) {
			$p_timestamp = $g_db->UnixTimeStamp($p_date);
		} else {
			$p_timestamp = time();
		}
		return $p_timestamp ;
	}

	# --------------------
	# helper function to compare two dates against a certain number of days
	# limitstring can be '> 1' '<= 2 ' etc
	# @@@ Check if there is a way to do that using ADODB rather than implementing it here.
	function db_helper_compare_days($p_date1, $p_date2, $p_limitstring) {
		$t_db_type = config_get( 'db_type' );

		switch( $t_db_type ) {
			case 'mssql':
			case 'odbc_mssql':
				return "(DATEDIFF(day, $p_date2, $p_date1) ". $p_limitstring . ")";

			case 'mysql':
			case 'mysqli':
				return "(TO_DAYS($p_date1) - TO_DAYS($p_date2) ". $p_limitstring . ")";

			case 'postgres':
			case 'postgres64':
			case 'postgres7':
			case 'pgsql':
				return "(date_mi($p_date1::date, $p_date2::date) ". $p_limitstring . ")";

			default:
				error_parameters( 'db_type', $t_db_type );
				trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
		}
	}

	# --------------------
	# count queries
	function db_count_queries () {
		global $g_queries_array;

		return count( $g_queries_array );
		}

	# --------------------
	# count unique queries
	function db_count_unique_queries () {
		global $g_queries_array;

		$t_unique_queries = 0;
		$t_shown_queries = array();
		foreach ($g_queries_array as $t_val_array) {
			if ( ! in_array( $t_val_array[0], $t_shown_queries ) ) {
				$t_unique_queries++;
				array_push( $t_shown_queries, $t_val_array[0] );
			}
		}
		return $t_unique_queries;
		}

	# --------------------
	# get total time for queries
	function db_time_queries () {
		global $g_queries_array;
		$t_count = count( $g_queries_array );
		$t_total = 0;
		for ( $i = 0; $i < $t_count; $i++ ) {
			$t_total += $g_queries_array[$i][1];
		}
		return $t_total;
	}


	# --------------------

	if ( !isset( $g_skip_open_db ) ) {
		if ( OFF == $g_use_persistent_connections ) {
			db_connect( config_get_global( 'dsn', false ), $g_hostname, $g_db_username, $g_db_password, $g_database_name );
		} else {
			db_pconnect( config_get_global( 'dsn', false ), $g_hostname, $g_db_username, $g_db_password, $g_database_name );
		}
	}
?>
