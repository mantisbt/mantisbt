<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: user_api.php,v 1.23 2002-09-06 06:11:52 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# User API
	###########################################################################

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_user = array();
	$g_cache_user_pref = array();

	# --------------------
	# Cache a user row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the user can't be found.  If the second parameter is
	#  false, return false if the user can't be found.
	function user_cache_row( $p_user_id, $p_trigger_errors=true) {
		global $g_cache_user;

		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_table = config_get( 'mantis_user_table' );

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

		if ( isset ( $g_cache_user_pref[$c_user_id] ) ) {
			return $g_cache_user_pref[$c_user_id];
		}

		$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id'";
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
	# check to see if user exists by id
	# return true if it does, false otherwise
	function user_exists( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "SELECT COUNT(*)
				  FROM $t_user_table
				  WHERE id='$c_user_id'";
		$result = db_query( $query );

		if ( db_result( $result ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function user_ensure_exists( $p_user_id ) {
		if ( ! user_exists( $p_user_id ) ) {
			trigger_error( ERROR_USER_NOT_FOUND, ERROR );
		}
	}
	# --------------------
	# return true if the username is unique, false if there is already a user
	#  with that username
	function user_is_name_unique( $p_username ) {
		$c_username = db_prepare_string( $p_username );

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "SELECT COUNT(*)
				  FROM $t_user_table
				  WHERE username='$c_username'";

	    $result = db_query( $query );

	    if ( db_result( $result ) > 0 ) {
			return false;
		} else {
			return true;
		}
	}
	# --------------------
	# Check if the username is unique
	#  return true if it is, trigger an ERROR if it isn't
	function user_ensure_name_unique( $p_username ) {
		if ( ! user_is_name_unique( $p_username ) ) {
			trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
		}
	}
	# --------------------
	# return whether user is monitoring bug for the user id and bug id
	function user_is_monitoring_bug( $p_user_id, $p_bug_id ) {
		$c_user_id	= db_prepare_int( $p_user_id );
		$c_bug_id	= db_prepare_int( $p_bug_id );

		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );

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
		$c_project_id = db_prepare_int( $p_project_id );
		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

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
	# returns false if error, the generated cookie string if ok
	function user_create( $p_username, $p_password, $p_email='', $p_access_level=null, $p_protected=false, $p_enabled=true ) {
		if ( null === $p_access_level ) {
			$p_access_level = config_get( 'default_new_account_access_level');
		}

		$t_password = process_plain_password( $p_password );

		$c_username		= db_prepare_string( $p_username );
		$c_password		= db_prepare_string( $t_password );
		$c_email		= db_prepare_string( $p_email );
		$c_access_level	= db_prepare_int( $p_access_level );
		$c_protected	= db_prepare_bool( $p_protected );
		$c_enabled		= db_prepare_bool( $p_enabled );

		user_ensure_name_unique( $p_username );
		email_ensure_valid( $p_email );

		$t_seed = $p_email.$p_username;
		$t_cookie_string	= create_cookie_string( $t_seed );

		$t_default_advanced_report			= config_get( 'default_advanced_report');
		$t_default_advanced_view			= config_get( 'default_advanced_view');
		$t_default_advanced_update			= config_get( 'default_advanced_update');
		$t_default_refresh_delay			= config_get( 'default_refresh_delay');
		$t_default_redirect_delay			= config_get( 'default_redirect_delay');
		$t_default_email_on_new				= config_get( 'default_email_on_new');
		$t_default_email_on_assigned		= config_get( 'default_email_on_assigned');
		$t_default_email_on_feedback		= config_get( 'default_email_on_feedback');
		$t_default_email_on_resolved		= config_get( 'default_email_on_resolved');
		$t_default_email_on_closed			= config_get( 'default_email_on_closed');
		$t_default_email_on_reopened		= config_get( 'default_email_on_reopened');
		$t_default_email_on_bugnote			= config_get( 'default_email_on_bugnote');
		$t_default_email_on_status			= config_get( 'default_email_on_status');
		$t_default_email_on_priority		= config_get( 'default_email_on_priority');
		$t_default_language					= config_get( 'default_language');

		$t_user_table 						= config_get( 'mantis_user_table' );
		$t_user_pref_table 					= config_get( 'mantis_user_pref_table' );

		$query = "INSERT INTO $t_user_table
				    ( id, username, email, password, date_created, last_visit,
				     enabled, protected, access_level, login_count, cookie_string )
				  VALUES
				    ( null, '$c_username', '$c_email', '$c_password', NOW(), NOW(),
				     $c_enabled, $c_protected, $c_access_level, 0, '$t_cookie_string')";
		db_query( $query );

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
		db_query($query);

		# Send notification email
		if ( $p_email ) {
			email_signup( $t_user_id, $p_password );
		}

		return $t_cookie_string;
	}
	# --------------------
	# Signup a user.
	# If the use_ldap_email config option is on then tries to find email using
	# ldap. $p_email may be empty, but the user wont get any emails.
	# returns false if error, the generated cookie string if ok
	function user_signup( $p_username, $p_email=null ) {
		if ( ( null === $p_email ) && ( ON == config_get( 'use_ldap_email' ) ) ) {
			$p_email = ldap_email( $p_username );
		}

		$t_seed = $p_email.$p_username;
		# Create random password
		$t_password	= create_random_password( $t_seed );

		return user_create( $p_username, $t_password, $p_email );
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
	    	db_query( $query );

			# Remove associated profiles
			$query = "DELETE
	    			  FROM $t_user_profile_table
	    			  WHERE user_id='$c_user_id'";
			db_query( $query );

			# Remove associated preferences
			$query = "DELETE
    				  FROM $t_user_pref_table
    				  WHERE user_id='$c_user_id'";
    		db_query( $query );

			$query = "DELETE
    				  FROM $t_project_user_list_table
	    			  WHERE user_id='$c_user_id'";
			db_query( $query );

			user_clear_cache( $p_user_id );

			return true;
		} else {
			return false;
		}
    }
	# --------------------
	# @@@ unused
	function user_create_project_prefs( $p_user_id, $p_project_id ) {
		$c_user_id 		= db_prepare_int( $p_user_id );
		$c_project_id 	= db_prepare_int( $p_project_id );

		$t_user_pref_table 	= config_get( 'mantis_user_pref_table' );

		$t_default_advanced_report			= config_get( 'default_advanced_report');
		$t_default_advanced_view			= config_get( 'default_advanced_view');
		$t_default_advanced_update			= config_get( 'default_advanced_update');
		$t_default_refresh_delay			= config_get( 'default_refresh_delay');
		$t_default_redirect_delay			= config_get( 'default_redirect_delay');
		$t_default_email_on_new				= config_get( 'default_email_on_new');
		$t_default_email_on_assigned		= config_get( 'default_email_on_assigned');
		$t_default_email_on_feedback		= config_get( 'default_email_on_feedback');
		$t_default_email_on_resolved		= config_get( 'default_email_on_resolved');
		$t_default_email_on_closed			= config_get( 'default_email_on_closed');
		$t_default_email_on_reopened		= config_get( 'default_email_on_reopened');
		$t_default_email_on_bugnote			= config_get( 'default_email_on_bugnote');
		$t_default_email_on_status			= config_get( 'default_email_on_status');
		$t_default_email_on_priority		= config_get( 'default_email_on_priority');
		$t_default_language					= config_get( 'default_language');

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
				    (null, '$c_user_id', '$c_project_id',
				    '$t_default_advanced_report', '$t_default_advanced_view', '$t_default_advanced_update',
				    '$t_default_refresh_delay', '$t_default_redirect_delay',
				    '$t_default_email_on_new', '$t_default_email_on_assigned',
				    '$t_default_email_on_feedback', '$t_default_email_on_resolved',
				    '$t_default_email_on_closed', '$t_default_email_on_reopened',
				    '$t_default_email_on_bugnote', '$t_default_email_on_status',
				    '$t_default_email_on_priority', '$t_default_language')";
		db_query($query);

		# db_query() errors on failure so:
		return true;
	}

	#===================================
	# Data Access
	#===================================
	# --------------------
	# get a user id from a username
	#  return false if the username does not exist
	function user_get_id_by_name( $p_username ) {
		$c_username = db_prepare_string( $p_username );

		$t_user_table = config_get( 'mantis_user_table' );

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

		$row = user_get_row( $t_user_id );

		return $row;
	}
	# --------------------
	# return a user row
	function user_get_row( $p_user_id ) {
		return user_cache_row( $p_user_id );
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
		if ( 0 == $p_user_id ) {
		    return "@null@";
		}

		$row = user_get_row( $p_user_id );

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
		    return ldap_email( user_get_name( $p_user_id ) );
		} else {
			return user_get_field( $p_user_id, 'email' );
		}
	}
	# --------------------
	# return the username or a string saying "user no longer exists"
	#  if the user does not exist
	function user_get_name( $p_user_id ) {
		$t_string = lang_get( 'user_no_longer_exists' );

		$row = user_get_row( $p_user_id, false );

		if ( false == $row ) {
			return $t_string;
		} else {
			return $row['username'];
		}
	}
	# --------------------
	# return the user's access level
	#  account for private project and the project user lists
	function user_get_access_level( $p_user_id, $p_project_id ) {
		$t_access_level  = user_get_field( $p_user_id, 'access_level' );

		if ( $t_access_level >= ADMINISTRATOR ) {
			return $t_access_level;
		}

		$t_project_access_level = project_get_user_access_level( $p_project_id, $p_user_id );

		if ( false === $t_project_access_level ) {
			return $t_access_level;
		} else {
			return $t_project_access_level;
		}
	}
	# --------------------
	# return the number of open assigned bugs to a user in a project
	function user_get_assigned_open_bug_count( $p_user_id, $p_project_id=0 ) {
		$c_user_id		= db_prepare_int($p_user_id);
		$c_project_id	= db_prepare_int($p_project_id);

		$t_bug_table	= config_get('mantis_bug_table');

		if ( 0 == $p_project_id ) {
			$t_where_prj = '';
		} else {
			$t_where_prj = "project_id='$c_project_id' AND";
		}

		$t_resolved	= RESOLVED;
		$t_closed	= CLOSED;

		$query = "SELECT COUNT(*)
				  FROM $t_bug_table
				  WHERE $t_where_prj
				  status<>'$t_resolved' AND status<>'$t_closed' AND
				  handler_id='$c_user_id'";
		$result = db_query( $query );

		return db_result( $result );
	}
	# --------------------
	# return the number of open reported bugs by a user in a project
	function user_get_reported_open_bug_count( $p_user_id, $p_project_id=0 ) {
		$c_user_id		= db_prepare_int($p_user_id);
		$c_project_id	= db_prepare_int($p_project_id);

		$t_bug_table	= config_get('mantis_bug_table');

		if ( 0 == $p_project_id ) {
			$t_where_prj = '';
		} else {
			$t_where_prj = "project_id='$c_project_id' AND";
		}

		$t_resolved	= RESOLVED;
		$t_closed	= CLOSED;

		$query = "SELECT COUNT(*)
				  FROM $t_bug_table
				  WHERE $t_where_prj
				  status<>'$t_resolved' AND status<>'$t_closed' AND
				  reporter_id='$c_user_id'";
		$result = db_query( $query );

		return db_result( $result );
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Update the last_visited field to be NOW()
	function user_update_last_visit( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "UPDATE $t_user_table
				  SET last_visit=NOW()
				  WHERE id='$c_user_id'";

		db_query( $query );

		user_clear_cache( $p_user_id );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Increment the number of times the user has logegd in
	# This function is only called from the login.php script
	function user_increment_login_count( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "UPDATE $t_user_table
				SET login_count=login_count+1
				WHERE id='$c_user_id'";

		db_query( $query );

		user_clear_cache( $p_user_id );

		#db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Set a user preference
	function user_set_pref( $p_user_id, $p_pref_name, $p_pref_value ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_pref_name	= db_prepare_string( $p_pref_name );
		$c_pref_value	= db_prepare_string( $p_pref_value );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "UPDATE $t_user_pref_table
				SET $c_pref_name='$c_pref_value'
				WHERE user_id='$c_user_id'";

		db_query( $query );

		user_pref_clear_cache( $p_user_id );

		#db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Set the user's default project
	function user_set_default_project( $p_user_id, $p_project_id ) {
		return user_set_pref( $p_user_id, 'default_project', (int)$p_project_id );
	}
	
?>
