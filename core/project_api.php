<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: project_api.php,v 1.7 2002-08-27 06:47:43 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Project API
	###########################################################################

	#===================================
	# Caching
	#===================================

	# --------------------
	# Cache a project row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the project can't be found.  If the second parameter is
	#  false, return false if the project can't be found.
	function project_cache_row( $p_project_id, $p_trigger_errors=true) {
		global $g_cache_project;

		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_table = config_get( 'mantis_project_table' );

		if ( ! isset( $g_cache_project ) ) {
			$g_cache_project = array();
		}

		if ( isset ( $g_cache_project[$c_project_id] ) ) {
			return $g_cache_project[$c_project_id];
		}

		$query = "SELECT * 
				  FROM $t_project_table 
				  WHERE id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_project[$c_project_id] = $row;

		return $row;
	}
	# --------------------
	# Clear the project cache (or just the given id if specified)
	function project_clear_cache( $p_project_id = null ) {
		global $g_cache_project;
		
		if ( $p_project_id === null ) {
			$g_cache_project = array();
		} else {
			$c_project_id = db_prepare_int( $p_project_id );
			unset( $g_cache_project[$c_project_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# check to see if project exists by id
	# return true if it does, false otherwise
	function project_exists( $p_project_id ) {
		# we're making use of the caching function here.  If we
		#  succeed in caching the project then it exists and is
		#  now cached for use by later function calls.  If we can't
		#  cache it we return false.
		if ( false == project_cache_row( $p_project_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}
	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function project_ensure_exists( $p_project_id ) {
		if ( ! project_exists( $p_project_id ) ) {
			trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
		}
	}
	# --------------------
	# check to see if project exists by name
	function project_is_name_unique( $p_name ) {
		$t_project_table = config_get( 'mantis_project_table' );

		$query ="SELECT COUNT(*) 
				 FROM $t_project_table 
				 WHERE name='$p_name'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function project_ensure_name_unique( $p_name ) {
		if ( ! project_is_name_unique( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_NOT_UNIQUE, ERROR );
		}
	}
	# --------------------
	# check to see if the user/project combo already exists
	# returns true is duplicate is found, otherwise false
	function project_includes_user( $p_project_id, $p_user_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );

		$query = "SELECT COUNT(*)
				  FROM $t_project_user_list_table
				  WHERE project_id='$c_project_id' AND
						user_id='$c_user_id'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return false;
		} else {
			return true;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Create a new project
	function project_create( $p_name, $p_description, $p_status, $p_view_state=PUBLIC, $p_file_path='', $p_enabled=true ) {
		# Make sure file path has trailing slash
		$p_file_path = helper_terminate_directory_path( $p_file_path );

		$c_name 		= db_prepare_string( $p_name );
		$c_description 	= db_prepare_string( $p_description );
		$c_status		= db_prepare_int( $p_status );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_file_path	= db_prepare_string( $p_file_path );
		$c_enabled		= db_prepare_bool( $p_enabled );

		if ( empty( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
		}

		project_ensure_name_unique( $p_name );

		$t_project_table = config_get( 'mantis_project_table' );

		$query = "INSERT
				  INTO $t_project_table
					( id, name, status, enabled, view_state, file_path, description )
				  VALUES
					( null, '$c_name', '$c_status', '$c_enabled', '$c_view_state', '$c_file_path', '$c_description' )";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}
	# --------------------
	# Delete a project
	function project_delete( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_table = config_get( 'mantis_project_table' );

		# Delete the bugs
		bug_delete_all( $p_project_id );

		# Delete the project categories
		category_delete_all( $p_project_id );

		# Delete the project versions
		version_delete_all( $p_project_id );

		# Delete the project custom attributes
		attribute_delete_all( $p_project_id );

		# Delete the project files
		project_delete_all_files( $p_project_id );

		# Delete the records assigning users to this project
		project_remove_all_users( $p_project_id );

		# Delete the project entry
		$query = "DELETE
				FROM $t_project_table
				WHERE id='$c_project_id'";
		
		db_query( $query );

		project_clear_cache( $p_project_id );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Update a project
	function project_update( $p_project_id, $p_name, $p_description, $p_status, $p_view_state, $p_file_path, $p_enabled ) {
		# Make sure file path has trailing slash
		$p_file_path = helper_terminate_directory_path( $p_file_path );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_name 		= db_prepare_string( $p_name );
		$c_description 	= db_prepare_string( $p_description );
		$c_status		= db_prepare_int( $p_status );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_file_path	= db_prepare_string( $p_file_path );
		$c_enabled		= db_prepare_bool( $p_enabled );

		if ( empty( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
		}

		$t_old_name = project_get_field( $p_project_id, 'name' );

		if ( $p_name != $t_old_name ) {
			project_ensure_name_unique( $p_name );
		}

		$t_project_table = config_get( 'mantis_project_table' );

		$query = "UPDATE $t_project_table
				  SET name='$c_name',
					status='$c_status',
					enabled='$c_enabled',
					view_state='$c_view_state',
					file_path='$c_file_path',
					description='$c_description'
				  WHERE id='$c_project_id'";

		db_query( $query );

		project_clear_cache( $p_project_id );

		# db_query errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return the specified field of the specified project
	function project_get_field( $p_project_id, $p_field_name ) {
		$row = project_cache_row( $p_project_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, NOTICE );
		}
	}
	# --------------------
	# return the descriptor holding all the info from the project user list
	# for the specified project
	function project_get_all_user_rows( $p_project_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );

		$query = "SELECT *
				FROM $t_project_user_list_table
				WHERE project_id='$c_project_id'";

		return db_query( $query );
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# add user with the specified access level to a project
	function project_add_user( $p_project_id, $p_user_id, $p_access_level ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_access_level	= db_prepare_int( $p_access_level );

		$query = "INSERT
				  INTO $t_project_user_list_table
				    ( project_id, user_id, access_level )
				  VALUES
				    ( '$c_project_id', '$c_user_id', '$c_access_level')";
		
		db_query( $query );

		# db_query errors on failure so:
		return true;
	}
	# --------------------
	# update entry
	# must make sure entry exists beforehand
	function project_update_user_access( $p_project_id, $p_user_id, $p_access_level ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_access_level	= db_prepare_int( $p_access_level );

		$query = "UPDATE $t_project_user_list_table
				  SET access_level='$c_access_level'
				  WHERE	project_id='$c_project_id' AND
						user_id='$c_user_id'";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}
	# --------------------
	# remove user from project
	function project_remove_user( $p_project_id, $p_user_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );

		$query = "DELETE FROM $t_project_user_list_table
				  WHERE project_id='$c_project_id' AND
						user_id='$c_user_id'";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}
	# --------------------
	# delete all users from the project user list for a given project
	# this is useful when deleting or closing a project
	function project_remove_all_users( $p_project_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );

		$query = "DELETE FROM $t_project_user_list_table
				WHERE project_id='$c_project_id'";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}
	# --------------------
	# Copy all users and their permissions from the source project to the
	#  destination project
	function project_copy_users( $p_destination_id, $p_source_id ) {
		# Copy all users from current project over to another project
		$result = project_get_all_user_rows( $p_source_id );

		$user_count = db_num_rows( $result );
		for ( $i = 0 ; $i < $user_count ; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			# if there is no duplicate then add a new entry
			# otherwise just update the access level for the existing entry
			if ( project_includes_user( $p_destination_id, $v_user_id ) ) {
				project_update_user_access( $p_destination_id, $v_user_id, $v_access_level );
			} else {
				project_add_user( $p_destination_id, $v_user_id, $v_access_level );
			}
		}
	}
	# --------------------
	# Delete all files associated with a project
	function project_delete_all_files( $p_project_id ) {
		# @@@ to be written
	}
?>