<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Project User API
	###########################################################################
	# --------------------
	# checks to see if the user/project combo already exists
	# returns true is duplicate is found, otherwise false
	function proj_user_is_duplicate( $p_project_id, $p_user_id ) {
		global $g_mantis_project_user_list_table;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_user_list_table
				WHERE 	project_id='$p_project_id' AND
						user_id='$p_user_id'";
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

		$query = "INSERT
				INTO $g_mantis_project_user_list_table
				( project_id, user_id, access_level )
				VALUES
				( '$p_project_id', '$p_user_id', '$p_access_level')";
		return db_query( $query );
	}
	# --------------------
	# remove user from project
	function proj_user_delete( $p_project_id, $p_user_id ) {
		global $g_mantis_project_user_list_table;

		$query = "DELETE FROM $g_mantis_project_user_list_table
				WHERE	project_id='$p_project_id' AND
						user_id='$p_user_id'";
		return db_query( $query );
	}
	# --------------------
	# delete all users from the project user list for a given project
	# this is useful when deleting or closing a project
	function proj_user_delete_all_users( $p_project_id ) {
		global $g_mantis_project_user_list_table;

		$query = "DELETE FROM $g_mantis_project_user_list_table
				WHERE project_id='$p_project_id'";
		return db_query( $query );
	}
	# --------------------
	# returns the descriptor holding all the info from the project user list
	# for the specified project
	function proj_user_get_all_users( $p_project_id ) {
		global $g_mantis_project_user_list_table;

		$query = "SELECT *
				FROM $g_mantis_project_user_list_table
				WHERE project_id='$p_project_id'";
		return db_query( $query );
	}
	# --------------------
?>