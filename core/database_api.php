<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: database_api.php,v 1.9 2002-10-15 23:46:46 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Database
	###########################################################################

	# This in the general interface for all database calls.
	# The actual SQL queries are found in the pages.
	# Use this as a starting point to port to other databases

	# An array in which all executed queries are stored.  This is used for profiling
	$g_queries_array = array();

	# --------------------
	# connect to database and select database
	function db_connect($p_hostname, $p_username, $p_password, $p_database,	$p_port ) {

		$t_result = mysql_connect(  $p_hostname.':'.$p_port, $p_username, $p_password );

		if ( !$t_result ) {
			echo 'ERROR: FAILED CONNECTION TO DATABASE: ';
			echo db_error();
			exit;
		}

		$t_result = db_select_db( $p_database );

		if ( !$t_result ) {
			echo 'ERROR: FAILED DATABASE SELECTION: ';
			echo db_error();
			exit;
		}
	}
	# --------------------
	# persistent connect to database and select database
	function db_pconnect($p_hostname, $p_username, $p_password, $p_database, $p_port ) {

		$t_result = mysql_pconnect(  $p_hostname.':'.$p_port, $p_username, $p_password );

		if ( !$t_result ) {
			echo 'ERROR: FAILED CONNECTION TO DATABASE: ';
			echo db_error();
			exit;
		}

		$t_result = db_select_db( $p_database );

		if ( !$t_result ) {
			echo 'ERROR: FAILED DATABASE SELECTION: ';
			echo db_error();
			exit;
		}
	}
	# --------------------
	# execute query, requires connection to be opened
	# If $p_error_on_failure is true (default) an error will be triggered
	#  if there is a problem executing the query.
	# If $p_error_on_failure is false, false will be returned if there is a
	#  problem.  This should be used very infrequently.  It was added to allow
	#  the admin script to check whether a table exists.
	function db_query( $p_query, $p_error_on_failure=true ) {
		global $g_queries_array;

		array_push ( $g_queries_array, $p_query );

		$t_result = mysql_query( $p_query );

		if ( !$t_result && $p_error_on_failure ) {
			echo 'ERROR: FAILED QUERY: '.$p_query.' : ';
			echo db_error();
			exit;
		} else {
			return $t_result;
		}
	}
	# --------------------
	function db_select_db( $p_db_name ) {
		return mysql_select_db( $p_db_name );
	}
	# --------------------
	function db_num_rows( $p_result ) {
		return mysql_num_rows( $p_result );
	}
	# --------------------
	function db_affected_rows() {
		return mysql_affected_rows();
	}
	# --------------------
	function db_fetch_array( $p_result ) {
		return mysql_fetch_array( $p_result );
	}
	# --------------------
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {
		if ( $p_result && ( db_num_rows( $p_result ) > 0 ) ) {
			return mysql_result( $p_result, $p_index1, $p_index2 );
		} else {
			return false;
		}
	}
	# --------------------
	# return the last inserted id
	# For MS SQL use: SELECT @@IDENTITY AS 'id'
	function db_insert_id() {
		$query = 'SELECT LAST_INSERT_ID()';
		$t_result = db_query( $query );
		return db_result( $t_result, 0, 0 );
	}
	# --------------------
	function db_field_exists( $p_field_name, $p_table_name, $p_db_name = '') {
		global $g_database_name;

		if ($p_db_name == '') {
			$p_db_name = $g_database_name;
		}

		$fields = mysql_list_fields($p_db_name, $p_table_name);
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
		  if ( mysql_field_name( $fields, $i ) == $p_field_name ) {
		  	return true;
		  }
		}

		return false;
	}
	# --------------------
	function db_error_num() {
		return mysql_errno();
	}
	# --------------------
	function db_error_msg() {
		return mysql_error();
	}
	# --------------------
	# display both the error num and error msg
	function db_error() {
		return '<br />'.db_error_num().': '.db_error_msg().'<br />';
	}
	# --------------------
	# close the connection.
	# Not really necessary most of the time since a connection is
	# automatically closed when a page finishes loading.
	function db_close() {
		$t_result = mysql_close();
	}

	# --------------------
	# prepare a string before DB insertion
	function db_prepare_string( $p_string ) {
		return mysql_escape_string( $p_string );
	}
	# --------------------
	# prepare an integer before DB insertion
	function db_prepare_int( $p_int ) {
		return (integer)$p_int;
	}
	# --------------------
	# prepare a boolean before DB insertion
	function db_prepare_bool( $p_bool ) {
		return (int)(bool)$p_bool;
	}

	# --------------------
	# generic unprepare if type is unknown
	function db_unprepare( $p_string ) {
		return stripslashes( $p_string );
	}
	# --------------------
	# unprepare a string after taking it out of the DB
	function db_unprepare_string( $p_string ) {
		return db_unprepare( $p_string );
	}
	# --------------------
	# unprepare an integer after taking it out of the DB
	function db_unprepare_int( $p_int ) {
		return (integer)db_unprepare( $p_int );
	}
	# --------------------
	# unprepare a boolean after taking it out of the DB
	function db_unprepare_bool( $p_bool ) {
		return (bool)db_unprepare( $p_bool );
	}
	# --------------------
	# calls db_unprepare() on every item in a row
	function db_unprepare_row( $p_row ) {
		if ( false == $p_row ) {
			return false;
		}

		$t_new_row = array();

		while ( list( $t_key, $t_val ) = each( $p_row ) ) {
			$t_new_row[$t_key] = db_unprepare( $t_val );
		}

		return $t_new_row;
	}

	if ( !isset( $f_skip_open_db ) ) {
		if ( OFF == $g_use_persistent_connections ) {
			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name, $g_port );
		} else {
			db_pconnect( $g_hostname, $g_db_username, $g_db_password, $g_database_name, $g_port );
		}
	}
?>
