<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: user_api.php,v 1.11 2002-08-27 21:51:35 jlatour Exp $
	# --------------------------------------------------------

	###########################################################################
	# User API
	###########################################################################

	#===================================
	# Caching
	#===================================

	# --------------------
	# Cache a user row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the user can't be found.  If the second parameter is
	#  false, return false if the user can't be found.
	function user_cache_row( $p_user_id, $p_trigger_errors=true) {
		global $g_cache_user;

		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_table = config_get( 'mantis_user_table' );

		if ( ! isset( $g_cache_user ) ) {
			$g_cache_user = array();
		}

		if ( isset ( $g_cache_user[$c_user_id] ) ) {
			return $g_cache_user[$c_user_id];
		}

		$query = "SELECT * 
				  FROM $t_user_table 
				  WHERE id='$c_user_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_USER_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_user[$c_user_id] = $row;

		return $row;
	}
	# --------------------
	# Clear the user cache (or just the given id if specified)
	function user_clear_cache( $p_user_id = null ) {
		global $g_cache_user;
		
		if ( $p_user_id === null ) {
			$g_cache_user = array();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
			unset( $g_cache_user[$c_user_id] );
		}

		return true;
	}
	# --------------------
	# Cache a user preferences row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the preferences can't be found.  If the second parameter is
	#  false, return false if the preferences can't be found.
	#
	# @@@ needs extension for per-project prefs
	function user_pref_cache_row( $p_user_id, $p_trigger_errors=true) {
		global $g_cache_user_pref;

		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		if ( ! isset( $g_cache_user_pref ) ) {
			$g_cache_user_pref = array();
		}

		if ( isset ( $g_cache_user_pref[$c_user_id] ) ) {
			return $g_cache_user_pref[$c_user_id];
		}

		$query = "SELECT * 
				  FROM $t_user_pref_table 
				  WHERE id='$c_user_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_user_pref[$c_user_id] = $row;

		return $row;
	}
	# --------------------
	# Clear the user preferences cache (or just the given id if specified)
	#
	# @@@ needs extension for per-project prefs
	function user_pref_clear_cache( $p_user_id = null ) {
		global $g_cache_user_pref;
		
		if ( $p_user_id === null ) {
			$g_cache_user_pref = array();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
			unset( $g_cache_user_pref[$c_user_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# returns true if the username is unique, false if there is already a user
	#  with that username
	function user_is_name_unique( $p_username ) {
		$t_user_table = config_get( 'g_mantis_user_table' );

		$c_username = db_prepare_string( $p_username );

		$query = "COUNT(*)
				  FROM $g_mantis_user_table
				  WHERE username='$c_username'";

	    $result = db_query( $query );

	    if ( db_result( $result ) > 0 ) {
			return false;
		} else {
			return true;
		}
	}
	# --------------------
	# return whether user is monitoring bug for the user id and bug id
	function user_is_monitoring_bug( $p_user_id, $p_bug_id ) {
		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );

		$c_user_id	= db_prepare_int( $p_user_id );
		$c_bug_id	= db_prepare_int( $p_bug_id );

		$query = "SELECT COUNT(*)
				  FROM $t_bug_monitor_table
				  WHERE user_id='$c_user_id' AND bug_id='$c_bug_id'";

		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			return false;
		} else {
			return true;
		}
	}
	# --------------------
	# @@@ unused
	function user_has_project_prefs( $p_user_id, $p_project_id ) {
		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$c_project_id = db_prepare_int( $p_project_id );
		$c_user_id = db_prepare_int( $p_user_id );

	    $query = "SELECT COUNT(*)
	    		  FROM $t_user_pref_table
	    		  WHERE user_id='$c_user_id' AND project_id='$c_project_id'";
	    $result = db_query($query);
		$t_count =  db_result( $result );
		if ( $t_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Create a user.
	# If $g_use_ldap_email then tries to find email using ldap
	# $p_email may be empty, but the user wont get any emails.
	# returns false if error, the generated cookie string if ok
	function user_signup( $p_username, $p_email=false ) {
		$t_use_ldap_email 					= config_get('use_ldap_email');
		$t_default_new_account_access_level = config_get('default_new_account_access_level');
		$t_default_advanced_report			= config_get('default_advanced_report');
		$t_default_advanced_view			= config_get('default_advanced_view');
		$t_default_advanced_update			= config_get('default_advanced_update');
		$t_default_refresh_delay			= config_get('default_refresh_delay');
		$t_default_redirect_delay			= config_get('default_redirect_delay');
		$t_default_email_on_new				= config_get('default_email_on_new');
		$t_default_email_on_assigned		= config_get('default_email_on_assigned');
		$t_default_email_on_feedback		= config_get('default_email_on_feedback');
		$t_default_email_on_resolved		= config_get('default_email_on_resolved');
		$t_default_email_on_closed			= config_get('default_email_on_closed');
		$t_default_email_on_reopened		= config_get('default_email_on_reopened');
		$t_default_email_on_bugnote			= config_get('default_email_on_bugnote');
		$t_default_email_on_status			= config_get('default_email_on_status');
		$t_default_email_on_priority		= config_get('default_email_on_priority');
		$t_default_language					= config_get('default_language');

		$t_user_table 						= config_get('mantis_user_table');
		$t_user_pref_table 					= config_get('mantis_user_pref_table');

		if ( ( false == $p_email ) && ( ON == $g_use_ldap_email ) ) {
			$p_email = get_user_info( $p_username,'email' );
		}

		$t_seed = $p_email ? $p_email : $p_username;
		# Create random password
		$t_password			= create_random_password( $t_seed );
		# Use a default access level
		# create the almost unique string for each user then insert into the table
		$t_cookie_string	= create_cookie_string( $t_seed );
		$t_password2		= process_plain_password( $t_password );
		$c_username			= db_prepare_string($p_username);
		$c_email			= db_prepare_string($p_email);

		$query = "INSERT INTO $t_user_table
				( id, username, email, password, date_created, last_visit,
				enabled, protected, access_level, login_count, cookie_string )
				VALUES
				( null, '$c_username', '$c_email', '$t_password2', NOW(), NOW(),
				1, 0, $g_default_new_account_access_level, 0, '$t_cookie_string')";
		$result = db_query( $query );

		if ( !$result ) {
			return false;
		}

		# Create preferences for the user
		$t_user_id = db_insert_id();
		$query = "INSERT INTO $t_user_pref_table
				(id, user_id, advanced_report, advanced_view, advanced_update,
				refresh_delay, redirect_delay,
				email_on_new, email_on_assigned,
				email_on_feedback, email_on_resolved,
				email_on_closed, email_on_reopened,
				email_on_bugnote, email_on_status,
				email_on_priority, language)
				VALUES
				(null, '$t_user_id', '$t_default_advanced_report',
				'$t_default_advanced_view', '$t_default_advanced_update',
				'$t_default_refresh_delay', '$t_default_redirect_delay',
				'$t_default_email_on_new', '$t_default_email_on_assigned',
				'$t_default_email_on_feedback', '$t_default_email_on_resolved',
				'$t_default_email_on_closed', '$t_default_email_on_reopened',
				'$t_default_email_on_bugnote', '$t_default_email_on_status',
				'$t_default_email_on_priority', '$t_default_language')";
		$result = db_query($query);

		if ( !$result ) {
			return false;
		}

		# Send notification email
		if ( $p_email ) {
			email_signup( $t_user_id, $t_password );
		}

		return $t_cookie_string;
	}
	# --------------------
	# delete an account
	# returns true when the account was successfully deleted
	function user_delete( $p_user_id ) {
		$c_user_id 					= db_prepare_int($p_user_id);
		
		$t_user_table 				= config_get('mantis_user_table');
		$t_user_profile_table 		= config_get('mantis_user_profile_table');
		$t_user_pref_table 			= config_get('mantis_user_pref_table');
		$t_project_user_list_table 	= config_get('mantis_project_user_list_table');

	    if ( !user_get_field( $p_user_id, 'protected' ) ) {
		    # Remove account
	    	$query = "DELETE
    				FROM $t_user_table
    				WHERE id='$c_user_id'";
	    	$result = db_query( $query );
			$success = db_affected_rows();

		    # Remove associated profiles
		    $query = "DELETE
	    			FROM $t_user_profile_table
	    			WHERE user_id='$c_user_id'";
		    $result = db_query( $query );
	
			# Remove associated preferences
    		$query = "DELETE
    				FROM $t_user_pref_table
    				WHERE user_id='$c_user_id'";
    		$result = db_query( $query );

	    	$query = "DELETE
    				FROM $t_project_user_list_table
	    			WHERE user_id='$c_user_id'";
		    $result = db_query( $query );

			drop_user_info_cache();
			
			return $success;
		} else {
			return 0;
		}
    }

	#===================================
	# Data Access
	#===================================
	# --------------------
	# get a user id from a username
	#  return false if the username does not exist
	function user_get_id_by_name( $p_username ) {
		$t_user_table = config_get( 'mantis_user_table' );
		
		$c_username = db_prepare_string( $p_username );

		$query = "SELECT id
				  FROM $t_user_table
				  WHERE username='$c_username'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			return false;
		} else {
			return db_result( $result );
		}
	}
	# --------------------
	# return all data associated with a particular user name
	#  return false if the username does not exist
	function user_get_row_by_name( $p_username ) {
		$t_user_id = user_get_id_by_name( $p_username );

		if ( false == $t_user_id ) {
			return false;
		}

		$row = user_cache_row( $t_user_id );

		return $row;
	}
	# --------------------
	# return the specified preference field for the user id
	function user_get_pref( $p_user_id, $p_field_name ) {
		$row = user_pref_cache_row( $p_user_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	# --------------------
	# return the specified user field for the user id
	function user_get_field( $p_user_id, $p_field_name ) {
		$row = user_cache_row( $p_user_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	# --------------------
	# lookup the user's email in LDAP or the db as appropriate
	function user_get_email( $p_user_id ) {
		if ( ON == config_get( 'use_ldap_email' ) ) {
		    return ldap_email( $p_user_id );
		} else {
			return user_get_field( $p_user_id, 'email' );
		}
	}
	# --------------------
	# returns username
	function user_get_name( $p_user_id ) {
		$t_string = lang_get( 'user_no_longer_exists' );

		$row = user_cache_row( $p_user_id, false );

		if ( false == $row ) {
			return $t_string;
		} else {
			return $row['username'];
		}
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Update the last_visited field to be NOW()
	function user_update_last_visit( $p_user_id ) {
		$t_user_table = config_get( 'mantis_user_table' );
		
		# @@@ remove this once old user caching is gotten rid of
		drop_user_info_cache();

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "UPDATE $t_user_table
				  SET last_visit=NOW()
				  WHERE id='$c_user_id'";
		
		db_query( $query );

		user_clear_cache( $p_user_id );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# This function is only called from the login.php3 script
	function user_increment_login_count( $p_user_id ) {
		$t_user_table = config_get( 'mantis_user_table' );

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "UPDATE $t_user_table
				SET login_count=login_count+1
				WHERE id='$c_user_id'";

		db_query( $query );

		#db_query() errors on failure so:
		return true;
	}



#########################################
#
# Current user functions
#
# These functions operate on the current user
#
# They should be refactored into functions that take a user
#  with the current functions calling the new ones with the
#  result of auth_get_current_user_id() as the first parameter
#
# The naming of these and whether they should be in another api
#  file is also an issue.  The best I can think of is:
#
#   current_user_*() but I'm tempted to leave them in this file
#
# maybe they need to be user_current_*() or user_*_current() ??
#
##################################

	# --------------------
	# Flush user information cache.  Should be called when the user information
	# is changed.
	function drop_user_info_cache( ) {
		global $g_current_user_info;
		unset ( $g_current_user_info );
	}
	# --------------------

	# --------------------
	# @@@ unused
	function user_create_project_prefs( $p_project_id ) {
		$c_project_id 		= db_prepare_int($p_project_id);
		
		$t_user_pref_table 	= config_get('mantis_user_pref_table');

		$t_user_id = get_current_user_field( 'id' );
	    $query = "INSERT
	    		INTO $t_user_pref_table
	    		(id, user_id, project_id,
	    		advanced_report, advanced_view, advanced_update,
	    		refresh_delay, redirect_delay,
	    		email_on_new, email_on_assigned,
	    		email_on_feedback, email_on_resolved,
	    		email_on_closed, email_on_reopened,
	    		email_on_bugnote, email_on_status,
	    		email_on_priority, language)
	    		VALUES
	    		(null, '$t_user_id', '$c_project_id',
	    		'$g_default_advanced_report', '$g_default_advanced_view', '$g_default_advanced_update',
	    		'$g_default_refresh_delay', '$g_default_redirect_delay',
	    		'$g_default_email_on_new', '$g_default_email_on_assigned',
	    		'$g_default_email_on_feedback', '$g_default_email_on_resolved',
	    		'$g_default_email_on_closed', '$g_default_email_on_reopened',
	    		'$g_default_email_on_bugnote', '$g_default_email_on_status',
	    		'$g_default_email_on_priority', '$g_default_language')";
	    $result = db_query($query);
	}
	# --------------------
	# grabs the access level of the current user
	# this function accounts for private project and the project user lists
	function get_current_user_access_level() {
		global $g_string_cookie_val;

		$t_access_level = get_current_user_field( 'access_level' );
		$t_access_level2 = get_project_access_level();

		if ( $t_access_level >= ADMINISTRATOR ) {
			return $t_access_level;
		}

		if ( -1 == $t_access_level2 ) {
			return $t_access_level;
		} else {
			return $t_access_level2;
		}
	}
	# --------------------
	# retrieve the number of open assigned bugs to a user in a project
	function get_assigned_open_bug_count( $p_project_id, $p_cookie_str ) {
		$c_project_id	= db_prepare_int($p_project_id);
		$c_cookie_str	= db_prepare_string($p_cookie_str);
		
		$t_bug_table	= config_get('mantis_bug_table');
		$t_user_table	= config_get('mantis_user_table');

		$query ="SELECT id ".
				"FROM $t_user_table ".
				"WHERE cookie_string='$c_cookie_str'";
		$result = db_query( $query );
		$t_id = db_result( $result );

		if ( '0000000' == $g_project_cookie_val ) {
			$t_where_prj = '';
		} else {
			$t_where_prj = "project_id='$c_project_id' AND";
		}
		$t_res = RESOLVED;
		$t_clo = CLOSED;
		$query ="SELECT COUNT(*) ".
				"FROM $t_bug_table ".
				"WHERE $t_where_prj ".
				"status<>'$t_res' AND status<>'$t_clo' AND ".
				"handler_id='$t_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# retrieve the number of open reported bugs by a user in a project
	function get_reported_open_bug_count( $p_project_id, $p_cookie_str ) {
		$c_project_id	= db_prepare_int($p_project_id);
		$c_cookie_str	= db_prepare_string($p_cookie_str);
		
		$t_bug_table	= config_get('mantis_bug_table');
		$t_user_table	= config_get('mantis_user_table');

		$query ="SELECT id ".
				"FROM $g_mantis_user_table ".
				"WHERE cookie_string='$c_cookie_str'";
		$result = db_query( $query );
		$t_id = db_result( $result );

		if ( '0000000' == $g_project_cookie_val ) {
			$t_where_prj = '';
		} else {
			$t_where_prj = "project_id='$c_project_id' AND";
		}
		$t_res = RESOLVED;
		$t_clo = CLOSED;
		$query ="SELECT COUNT(*) ".
				"FROM $t_bug_table ".
				"WHERE $t_where_prj ".
				"status<>'$t_res' AND status<>'$t_clo' AND ".
				"reporter_id='$t_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_field( $p_field_name ) {
		global 	$g_string_cookie_val, $g_current_user_info;
		
		$t_user_table	= config_get('mantis_user_table');

		# if logged in
		if ( isset( $g_string_cookie_val ) ) {
			if ( !isset ( $g_current_user_info[ $p_field_name ] ) ) {
				# get user info
				$query = "SELECT * ".
						"FROM $t_user_table ".
						"WHERE cookie_string='$g_string_cookie_val' ".
						"LIMIT 1";
				$result = db_query( $query );
				$g_current_user_info = db_fetch_array ( $result );
			}
			return $g_current_user_info [ $p_field_name ];
		} else {
			return 0;
		}
	}
	# --------------------
	# Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_pref_field( $p_field_name ) {
		global 	$g_string_cookie_val, $g_cache_user_pref;
		
		$t_user_pref_table	= config_get('mantis_user_pref_table');

		# if logged in
		if ( isset( $g_string_cookie_val ) ) {
			$t_id = get_current_user_field( 'id' );

			if ( !isset( $g_cache_user_pref[$t_id] ) ) {
				# get user info
				$query = "SELECT *
						FROM $t_user_pref_table
						WHERE user_id='$t_id'";
				$result = db_query( $query );
				$row = db_fetch_array( $result );
				if ( false === $row ) {
					return 0;
				}
				$g_cache_user_pref[$t_id] = $row;
			}
			return ( $g_cache_user_pref[$t_id][$p_field_name] );
		} else {
			return 0;
		}
	}
?>
