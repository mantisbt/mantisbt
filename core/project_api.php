<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: project_api.php,v 1.4 2002-08-26 22:49:50 jfitzell Exp $
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
	# checks to see if the user/project combo already exists
	# returns true is duplicate is found, otherwise false
	function proj_user_is_duplicate( $p_project_id, $p_user_id ) {
		global $g_mantis_project_user_list_table;

		$c_project_id	= (integer)$p_project_id;
		$c_user_id		= (integer)$p_user_id;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_user_list_table
				WHERE 	project_id='$c_project_id' AND
						user_id='$c_user_id'";
		$result = db_query( $query );
		$count = db_result( $result, 0, 0 );
		if ( 0 == $count ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# add user with the specified access level to a project
	function proj_user_add( $p_project_id, $p_user_id, $p_access_level ) {
		global $g_mantis_project_user_list_table;

		$c_project_id	= (integer)$p_project_id;
		$c_user_id		= (integer)$p_user_id;
		$c_access_level	= (integer)$p_access_level;

		$query = "INSERT
				INTO $g_mantis_project_user_list_table
				( project_id, user_id, access_level )
				VALUES
				( '$c_project_id', '$c_user_id', '$c_access_level')";
		return db_query( $query );
	}
	# --------------------
	# update entry
	# must make sure entry exists beforehand
	function proj_user_update( $p_project_id, $p_user_id, $p_access_level ) {
		global $g_mantis_project_user_list_table;

		$c_project_id	= (integer)$p_project_id;
		$c_user_id		= (integer)$p_user_id;
		$c_access_level	= (integer)$p_access_level;

		$query = "UPDATE $g_mantis_project_user_list_table
				SET		access_level='$c_access_level'
				WHERE	project_id='$c_project_id' AND
						user_id='$c_user_id'";
		return db_query( $query );
	}
	# --------------------
	# remove user from project
	function proj_user_delete( $p_project_id, $p_user_id ) {
		global $g_mantis_project_user_list_table;

		$c_project_id	= (integer)$p_project_id;
		$c_user_id		= (integer)$p_user_id;

		$query = "DELETE FROM $g_mantis_project_user_list_table
				WHERE	project_id='$c_project_id' AND
						user_id='$c_user_id'";
		return db_query( $query );
	}
	# --------------------
	# delete all users from the project user list for a given project
	# this is useful when deleting or closing a project
	function proj_user_delete_all_users( $p_project_id ) {
		global $g_mantis_project_user_list_table;

		$c_project_id = (integer)$p_project_id;

		$query = "DELETE FROM $g_mantis_project_user_list_table
				WHERE project_id='$c_project_id'";
		return db_query( $query );
	}
	# --------------------
	# returns the descriptor holding all the info from the project user list
	# for the specified project
	function proj_user_get_all_users( $p_project_id ) {
		global $g_mantis_project_user_list_table;

		$c_project_id = (integer)$p_project_id;

		$query = "SELECT *
				FROM $g_mantis_project_user_list_table
				WHERE project_id='$c_project_id'";
		return db_query( $query );
	}
	# --------------------
	# returns true if the user exists in the project user list.
	function is_removable_proj_user( $p_user_id ) {
		global $g_mantis_project_user_list_table, $g_project_cookie_val;

		$c_user_id = (integer)$p_user_id;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_user_list_table p
				WHERE	p.project_id='$g_project_cookie_val' AND
						p.user_id='$c_user_id'";
		$result = db_query( $query );
		$count = db_result( $result, 0, 0 );
		if ( $count>0 ) {
			return true;
		} else {
			return false;
		}
	}

?>