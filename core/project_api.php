<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: project_api.php,v 1.3 2002-08-26 00:40:23 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Project API
	###########################################################################

	# --------------------
	# Cache a project row if necessary and return the cached copy
	#  The cached row should be db_unprepare()'d
	function project_cache_row( $p_project_id ) {
		global $g_cache_project;

		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_table = config_get( 'mantis_project_table' );

		if ( ! isset( $g_cache_project ) ) {
			$g_cache_project = array();
		}

		if ( isset ( $g_cache_project[$c_project_id] ) ) {
			return $g_cache_project[$c_project_id];
		}

		$query = "SELECT * ".
				 "FROM $t_project_table ".
				 "WHERE id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
		}

		$row = db_fetch_array( $result );

		$g_cache_project[$c_project_id] = $row;

		return $row;
	}

	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function project_ensure_exists( $p_project_id ) {
		global $g_mantis_project_table;

		$c_project_id = (integer)$p_project_id;

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_project_table ".
				"WHERE id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
		}
	}
	# --------------------
	# check to see if project exists by name
	function project_is_name_unique( $p_name ) {
		global $g_mantis_project_table;

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_project_table ".
				"WHERE name='$p_name'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# Returns the specified field of the specified project
	function project_get_field( $p_project_id, $p_field_name ) {
		$row = project_cache_row( $p_project_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, NOTICE );
		}
	}
	# --------------------
	# Returns the name of the project
?>