<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: project_api.php,v 1.37 2003-02-19 01:37:22 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'category_api.php' );
	require_once( $t_core_dir . 'version_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );

	###########################################################################
	# Project API
	###########################################################################

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_project = array();
	$g_cache_project_missing = array();
	$g_cache_project_all = false;

	# --------------------
	# Cache a project row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the project can't be found.  If the second parameter is
	#  false, return false if the project can't be found.
	function project_cache_row( $p_project_id, $p_trigger_errors=true ) {
		global $g_cache_project, $g_cache_project_missing;

		$c_project_id = db_prepare_int( $p_project_id );

		if ( isset ( $g_cache_project[(int)$p_project_id] ) ) {
			return $g_cache_project[(int)$p_project_id];
		} else if ( isset( $g_cache_project_missing[(int)$p_project_id] ) ) {
			return false;
		}

		$t_project_table = config_get( 'mantis_project_table' );

		$query = "SELECT * 
				  FROM $t_project_table 
				  WHERE id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			$g_cache_project_missing[(int)$p_project_id] = true;

			if ( $p_trigger_errors ) {
				trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_project[(int)$p_project_id] = $row;

		return $row;
	}

	# --------------------
	# Cache all project rows and return an array of them
	function project_cache_all() {
		global $g_cache_project, $g_cache_project_all;
		
		if ( ! $g_cache_project_all ) {
			$t_project_table = config_get( 'mantis_project_table' );

			$query = "SELECT * 
					  FROM $t_project_table";
			$result = db_query( $query );

			$count = db_num_rows( $result );

			for ( $i = 0 ; $i < $count ; $i++ ) {
				$row = db_fetch_array( $result );

				$g_cache_project[(int)$row['id']] = $row;
			}

			$g_cache_project_all = true;
		}

		return $g_cache_project;
	}

	# --------------------
	# Clear the project cache (or just the given id if specified)
	function project_clear_cache( $p_project_id = null ) {
		global $g_cache_project, $g_cache_project_missing, $g_cache_project_all;
		
		if ( null === $p_project_id ) {
			$g_cache_project = array();
			$g_cache_project_missing = array();
			$g_cache_project_all = false;
		} else {
			unset( $g_cache_project[(int)$p_project_id] );
			unset( $g_cache_project_missing[(int)$p_project_id] );
			$g_cache_project_all = false;
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
		$c_name = db_prepare_string( $p_name );

		$t_project_table = config_get( 'mantis_project_table' );

		$query ="SELECT COUNT(*) 
				 FROM $t_project_table 
				 WHERE name='$c_name'";
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
		$p_file_path = terminate_directory_path( $p_file_path );

		$c_name 		= db_prepare_string( $p_name );
		$c_description 	= db_prepare_string( $p_description );
		$c_status		= db_prepare_int( $p_status );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_file_path	= db_prepare_string( $p_file_path );
		$c_enabled		= db_prepare_bool( $p_enabled );

		if ( is_blank( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
		}

		project_ensure_name_unique( $p_name );

		file_ensure_valid_upload_path( $p_file_path );

		$t_project_table = config_get( 'mantis_project_table' );

		$query = "INSERT
				  INTO $t_project_table
					( id, name, status, enabled, view_state, file_path, description )
				  VALUES
					( null, '$c_name', '$c_status', '$c_enabled', '$c_view_state', '$c_file_path', '$c_description' )";

		db_query( $query );

		# return the id of the new project
		return db_insert_id();
	}

	# --------------------
	# Delete a project
	function project_delete( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_table = config_get( 'mantis_project_table' );

		# Delete the bugs
		bug_delete_all( $p_project_id );

		# Delete associations with custom field definitions.
		custom_field_unlink_all( $p_project_id );

		# Delete the project categories
		category_remove_all( $p_project_id );

		# Delete the project versions
		version_remove_all( $p_project_id );

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
		$p_file_path = terminate_directory_path( $p_file_path );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_name 		= db_prepare_string( $p_name );
		$c_description 	= db_prepare_string( $p_description );
		$c_status		= db_prepare_int( $p_status );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_file_path	= db_prepare_string( $p_file_path );
		$c_enabled		= db_prepare_bool( $p_enabled );

		if ( is_blank( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
		}

		$t_old_name = project_get_field( $p_project_id, 'name' );

		if ( $p_name != $t_old_name ) {
			project_ensure_name_unique( $p_name );
		}

		file_ensure_valid_upload_path( $p_file_path );

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
	# Return the row describing the given project
	function project_get_row( $p_project_id ) {
		return project_cache_row( $p_project_id );
	}

	# --------------------
	# Return all rows describing all projects
	function project_get_all_rows() {
		return project_cache_all();
	}

	# --------------------
	# Return the specified field of the specified project
	function project_get_field( $p_project_id, $p_field_name ) {
		$row = project_get_row( $p_project_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# Return the name of the project
	# Handles project 0 by returning the internationalized string for All Projects
	function project_get_name( $p_project_id ) {
		if ( 0 == $p_project_id ) {
			return lang_get( 'all_projects' );
		} else {
			return project_get_field( $p_project_id, 'name' );
		}
	}

	# --------------------
	# Return the user's local (overridden) access level on the project or false
	#  if the user is not listed on the project
	function project_get_local_user_access_level( $p_project_id, $p_user_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );

		if ( 0 == $c_project_id ) {
			return false;
		}

		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$query = "SELECT access_level
				  FROM $t_project_user_list_table
				  WHERE user_id='$c_user_id' AND project_id='$c_project_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			return db_result( $result );
		} else {
			return false;
		}
	}

	# --------------------
	# return the descriptor holding all the info from the project user list
	# for the specified project
	function project_get_local_user_rows( $p_project_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$query = "SELECT *
				FROM $t_project_user_list_table
				WHERE project_id='$c_project_id'";

		$result = db_query( $query );

		$t_user_rows = array();
		$t_row_count = db_num_rows( $result );

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			array_push( $t_user_rows, db_fetch_array( $result ) );
		}

		return $t_user_rows;
	}

	# --------------------
	# Return an array of info about users who have access to the the given project
	# For each user we have 'id', 'username', and 'access_level' (overall access level)
	function project_get_all_user_rows( $p_project_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_user_table = config_get( 'mantis_user_table' );
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$t_on = ON;

		if ( PUBLIC == project_get_field( $p_project_id, 'view_state' ) ) {
			$t_access_clause = '';
		} else {
			# @@@ we need to get this logic out somewhere else.
			#   I was getting access_min from the project but apparently we got
			#   rid of that in 0.17.2.  The user docs claim developers and higher
			#   get into private projects but the code seems to only allow administrators in
			$t_access_clause = 'AND access_level >= ' . ADMINISTRATOR;
		}

		$t_users = array();

		$query = "SELECT id, username, access_level
					FROM $t_user_table
					WHERE enabled = $t_on
					  $t_access_clause
					ORDER BY username";

		$result = db_query( $query );
		$t_row_count = db_num_rows( $result );
		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_users[$row['id']] = $row;
		}

		// Get the project overrides
		$query = "SELECT u.id, u.username, l.access_level
					FROM $t_project_user_list_table l, $t_user_table u
					WHERE l.user_id = u.id
					  AND u.enabled = $t_on
					  AND l.project_id = $c_project_id
					ORDER BY u.username";

		$result = db_query( $query );
		$t_row_count = db_num_rows( $result );
		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_users[$row['id']] = $row;
		}

		return multi_sort( array_values($t_users), 'username' );
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
	# update or add the entry as appropriate
	#  This function involves one more db query than project_update_user_acces() 
	#  or project_add_user()
	function project_set_user_access( $p_project_id, $p_user_id, $p_access_level ) {
		if ( project_includes_user( $p_project_id, $p_user_id ) ) {
			return project_update_user_access( $p_project_id, $p_user_id, $p_access_level );
		} else {
			return project_add_user( $p_project_id, $p_user_id, $p_access_level );
		}
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
		$rows = project_get_local_user_rows( $p_source_id );

		for ( $i = 0 ; $i < sizeof( $rows ) ; $i++ ) {
			extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

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