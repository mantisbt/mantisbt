<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Revision: 1.14 $
	# $Author: vboctor $
	# $Date: 2002-07-02 12:48:52 $
	#
	# $Id: core_database_API.php,v 1.14 2002-07-02 12:48:52 vboctor Exp $
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
	# execute query, requires connection to be opened,
	function db_query( $p_query ) {
		global $g_queries_array;

		array_push ( $g_queries_array, $p_query );

		$t_result = mysql_query( $p_query );

		if ( !$t_result ) {
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
		return '<p>'.db_error_num().': '.db_error_msg().'<p>';
	}
	# --------------------
	# close the connection.
	# Not really necessary most of the time since a connection is
	# automatically closed when a page finishes loading.
	function db_close() {
		$t_result = mysql_close();
	}
	# --------------------

	if ( !isset( $f_skip_open_db ) ) {
		if ( OFF == $g_use_persistent_connections ) {
			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name, $g_port );
		} else {
			db_pconnect( $g_hostname, $g_db_username, $g_db_password, $g_database_name, $g_port );
		}
	}
?>
