<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: user_api.php,v 1.71 2004-05-18 03:33:14 int2str Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'email_api.php' );
	require_once( $t_core_dir . 'ldap_api.php' );

	### User API ###

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on

	$g_cache_user = array();

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

		if ( null === $p_user_id ) {
			$g_cache_user = array();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
			unset( $g_cache_user[$c_user_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# check to see if user exists by id
	# return true if it does, false otherwise
	#
	# Use user_cache_row() to benefit from caching if called multiple times
	#  and because if the user does exist the data may well be wanted
	function user_exists( $p_user_id ) {
		$row = user_cache_row( $p_user_id, false );

		if ( false === $row ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function user_ensure_exists( $p_user_id ) {
		if ( !user_exists( $p_user_id ) ) {
			trigger_error( ERROR_USER_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# return true if the username is unique, false if there is already a user
	#  with that username
	function user_is_name_unique( $p_username ) {
		$c_username = db_prepare_string( $p_username );

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "SELECT username
				FROM $t_user_table
				WHERE username='$c_username'";
		$result = db_query( $query, 1 );

		if ( db_num_rows( $result ) > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Check if the username is unique and trigger an ERROR if it isn't
	function user_ensure_name_unique( $p_username ) {
		if ( !user_is_name_unique( $p_username ) ) {
			trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
		}
	}

	# --------------------
	# Check if the username is a valid username (does not account for uniqueness)
	# Return true if it is, false otherwise
	function user_is_name_valid( $p_username ) {
		# The DB field is only 32 characters
		if ( strlen( $p_username ) > 32 ) {
			return false;
		}

		# Only allow a basic set of characters
		if ( 0 == preg_match( config_get( 'user_login_valid_regex' ), $p_username ) ) {
			return false;
		}

		# We have a valid username
		return true;
	}

	# --------------------
	# Check if the username is a valid username (does not account for uniqueness)
	# Trigger an error if the username is not valid
	function user_ensure_name_valid( $p_username ) {
		if ( !user_is_name_valid( $p_username ) ) {
			trigger_error( ERROR_USER_NAME_INVALID, ERROR );
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

		if ( 0 == db_result( $result ) ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# return true if the user has access of ADMINISTRATOR or higher, false otherwise
	function user_is_administrator( $p_user_id ) {
		$t_access_level = user_get_field( $p_user_id, 'access_level' );

		if ( $t_access_level >= ADMINISTRATOR ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# return true is the user account is protected, false otherwise
	function user_is_protected( $p_user_id ) {
		if ( ON == user_get_field( $p_user_id, 'protected' ) ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Trigger an ERROR if the user account is protected
	function user_ensure_unprotected( $p_user_id ) {
		if ( user_is_protected( $p_user_id ) ) {
			trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
		}
	}

	# --------------------
	# return true is the user account is enabled, false otherwise
	function user_is_enabled( $p_user_id ) {
		if ( ON == user_get_field( $p_user_id, 'enabled' ) ) {
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

		$t_password = auth_process_plain_password( $p_password );

		$c_username		= db_prepare_string( $p_username );
		$c_password		= db_prepare_string( $t_password );
		$c_email		= db_prepare_string( $p_email );
		$c_access_level	= db_prepare_int( $p_access_level );
		$c_protected	= db_prepare_bool( $p_protected );
		$c_enabled		= db_prepare_bool( $p_enabled );

		user_ensure_name_valid( $p_username );
		user_ensure_name_unique( $p_username );
		email_ensure_valid( $p_email );

		$t_seed				= $p_email . $p_username;
		$t_cookie_string	= auth_generate_unique_cookie_string( $t_seed );
		$t_user_table 		= config_get( 'mantis_user_table' );

		$query = "INSERT INTO $t_user_table
				    ( username, email, password, date_created, last_visit,
				     enabled, access_level, login_count, cookie_string )
				  VALUES
				    ( '$c_username', '$c_email', '$c_password', " . db_now() . "," . db_now() . ",
				     $c_enabled, $c_access_level, 0, '$t_cookie_string')";
		db_query( $query );

		# Create preferences for the user
		$t_user_id = db_insert_id( $t_user_table );
		user_pref_set_default( $t_user_id );

		# Users are added with protected set to FALSE in order to be able to update
		# preferences.  Now set the real value of protected.
		if ( $c_protected ) {
			user_set_field( $t_user_id, 'protected', 1 );
		}

		# Send notification email
		if ( !is_blank( $p_email ) ) {
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
		if ( null === $p_email ) {
			$p_email = '';

			# @@@ I think the ldap_email stuff is a bit borked
			#  Where is it being set?  When is it being used?
			#  Shouldn't we override an email that is passed in here?
			#  If the user doesn't exist in ldap, is the account created?
			#  If so, there password won't get set anywhere...  (etc)
			#  RJF: I was going to check for the existence of an LDAP email.
			#  however, since we can't create an LDAP account at the moment,
			#  and we don't know the user password in advance, we may not be able
			#  to retrieve it anyway.
			#  I'll re-enable this once a plan has been properly formulated for LDAP
			#  account management and creation.

/*			$t_email = '';
			if ( ON == config_get( 'use_ldap_email' ) ) {
				$t_email = ldap_email_from_username( $p_username );
			}

			if ( !is_blank( $t_email ) ) {
				$p_email = $t_email;
			}
*/
		}

		$p_email = trim( $p_email );

		$t_seed = $p_email . $p_username;
		# Create random password
		$t_password	= auth_generate_random_password( $t_seed );

		return user_create( $p_username, $t_password, $p_email );
	}

	# --------------------
	# delete project-specific user access levels.
	# returns true when successfully deleted
	function user_delete_project_specific_access_levels( $p_user_id ) {
		$c_user_id = db_prepare_int($p_user_id);

		user_ensure_unprotected( $p_user_id );

		$t_project_user_list_table 	= config_get('mantis_project_user_list_table');

		$query = "DELETE FROM $t_project_user_list_table
				  WHERE user_id='$c_user_id'";
		db_query( $query );

		user_clear_cache( $p_user_id );

		return true;
	}

	# --------------------
	# delete profiles for the specified user
	# returns true when successfully deleted
	function user_delete_profiles( $p_user_id ) {
		$c_user_id = db_prepare_int($p_user_id);

		user_ensure_unprotected( $p_user_id );

		$t_user_profile_table = config_get('mantis_user_profile_table');

		# Remove associated profiles
		$query = "DELETE FROM $t_user_profile_table
				  WHERE user_id='$c_user_id'";
		db_query( $query );

		user_clear_cache( $p_user_id );

		return true;
	}

	# --------------------
	# delete a user account (account, profiles, preferences, project-specific access levels)
	# returns true when the account was successfully deleted
	function user_delete( $p_user_id ) {
		$c_user_id 					= db_prepare_int($p_user_id);

		user_ensure_unprotected( $p_user_id );

		# Remove associated profiles
		user_delete_profiles( $p_user_id );

		# Remove associated preferences
		user_pref_delete_all( $p_user_id );

		# Remove project specific access levels
		user_delete_project_specific_access_levels( $p_user_id );

		user_clear_cache( $p_user_id );

		$t_user_table = config_get('mantis_user_table');

		# Remove account
		$query = "DELETE FROM $t_user_table
				  WHERE id='$c_user_id'";
		db_query( $query );

		return true;
	}

	#===================================
	# Data Access
	#===================================

	# --------------------
	# get a user id from a username
	#  return false if the username does not exist
	function user_get_id_by_name( $p_username ) {
		$c_username		= db_prepare_string( $p_username );
		$t_user_table	= config_get( 'mantis_user_table' );

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

		if ( false === $t_user_id ) {
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
	# return the specified user field for the user id
	function user_get_field( $p_user_id, $p_field_name ) {
		if ( NO_USER == $p_user_id ) {
			trigger_error( 'user_get_field() for NO_USER', WARNING );
			return "@null@";
		}

		$row = user_get_row( $p_user_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			error_parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# lookup the user's email in LDAP or the db as appropriate
	function user_get_email( $p_user_id ) {
		$t_email = '';
		if ( ON == config_get( 'use_ldap_email' ) ) {
		    $t_email = ldap_email( $p_user_id );
		}
		if ( is_blank( $t_email ) ) {
			$t_email =  user_get_field( $p_user_id, 'email' );
		}
		return $t_email;
	}

	# --------------------
	# return the username or a string "user<id>" if the user does not exist
	function user_get_name( $p_user_id ) {
		$row = user_cache_row( $p_user_id, false );

		if ( false == $row ) {
			return lang_get( 'prefix_for_deleted_users' ) . (int)$p_user_id;
		} else {
			return $row['username'];
		}
	}

	# --------------------
	# return the user's access level
	#  account for private project and the project user lists
	function user_get_access_level( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_access_level  = user_get_field( $p_user_id, 'access_level' );

		if ( $t_access_level >= ADMINISTRATOR ) {
			return $t_access_level;
		}

		$t_project_access_level = project_get_local_user_access_level( $p_project_id, $p_user_id );

		if ( false === $t_project_access_level ) {
			return $t_access_level;
		} else {
			return $t_project_access_level;
		}
	}

	# --------------------
	# retun an array of project IDs to which the user has access
	function user_get_accessible_projects( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		$t_project_table			= config_get( 'mantis_project_table' );
		$t_project_user_list_table	= config_get( 'mantis_project_user_list_table' );

		$t_public	= VS_PUBLIC;
		$t_private	= VS_PRIVATE;

		if ( user_is_administrator( $p_user_id ) ) {
			$query = "SELECT DISTINCT( id ), name
					  FROM $t_project_table
					  WHERE enabled=1
					  ORDER BY name";
		} else {
			$query = "SELECT DISTINCT( p.id ), p.name
					  FROM $t_project_table p
					  LEFT JOIN $t_project_user_list_table u
					    ON p.id=u.project_id
					  WHERE ( p.enabled = 1 ) AND
						( p.view_state='$t_public'
						    OR (p.view_state='$t_private'
							    AND
						        u.user_id='$c_user_id' )
						)
					  ORDER BY p.name";
		}

		$result = db_query( $query );
		$row_count = db_num_rows( $result );

		$t_projects = array();

		for ( $i=0 ; $i < $row_count ; $i++ ) {
			$row = db_fetch_array( $result );

			array_push( $t_projects, $row['id'] );
		}

		return $t_projects;
	}

	# --------------------
	# return the number of open assigned bugs to a user in a project
	function user_get_assigned_open_bug_count( $p_user_id, $p_project_id=ALL_PROJECTS ) {
		$c_user_id		= db_prepare_int($p_user_id);
		$c_project_id	= db_prepare_int($p_project_id);

		$t_bug_table	= config_get('mantis_bug_table');

		if ( ALL_PROJECTS == $p_project_id ) {
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
	function user_get_reported_open_bug_count( $p_user_id, $p_project_id=ALL_PROJECTS ) {
		$c_user_id		= db_prepare_int($p_user_id);
		$c_project_id	= db_prepare_int($p_project_id);

		$t_bug_table	= config_get('mantis_bug_table');

		if ( ALL_PROJECTS == $p_project_id ) {
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

	# --------------------
	# return a profile row
	function user_get_profile_row( $p_user_id, $p_profile_id ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_profile_id	= db_prepare_int( $p_profile_id );

		$t_user_profile_table = config_get( 'mantis_user_profile_table' );

		$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE id='$c_profile_id' AND
				  		user_id='$c_user_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			trigger_error( ERROR_USER_PROFILE_NOT_FOUND, ERROR );
		}

		$row = db_fetch_array( $result );

		return $row;
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Update the last_visited field to be now
	function user_update_last_visit( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "UPDATE $t_user_table
				  SET last_visit= " . db_now() . "
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
	# Set a user field
	function user_set_field( $p_user_id, $p_field_name, $p_field_value ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_field_name	= db_prepare_string( $p_field_name );
		$c_field_value	= db_prepare_string( $p_field_value );

		if ( $p_field_name != "protected" ) {
			user_ensure_unprotected( $p_user_id );
		}

		$t_user_table = config_get( 'mantis_user_table' );

		$query = "UPDATE $t_user_table
				  SET $c_field_name='$c_field_value'
				  WHERE id='$c_user_id'";

		db_query( $query );

		user_clear_cache( $p_user_id );

		#db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Set the user's default project
	function user_set_default_project( $p_user_id, $p_project_id ) {
		return user_pref_set_pref( $p_user_id, 'default_project', (int)$p_project_id );
	}

	# --------------------
	# Set the user's password to the given string, encoded as appropriate
	function user_set_password( $p_user_id, $p_password, $p_allow_protected=false ) {
		$c_user_id = db_prepare_int( $p_user_id );

		if ( !$p_allow_protected ) {
			user_ensure_unprotected( $p_user_id );
		}

		$t_password		= auth_process_plain_password( $p_password );
		$t_user_table	= config_get( 'mantis_user_table' );
		$query = "UPDATE $t_user_table
				  SET password='$t_password'
				  WHERE id='$c_user_id'";
		db_query( $query );

		#db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Set the user's email to the given string after checking that it is a valid email
	function user_set_email( $p_user_id, $p_email ) {
		email_ensure_valid( $p_email );

		return user_set_field( $p_user_id, 'email', $p_email );
	}

	# --------------------
	# Set the user's username to the given string after checking that it is valid
	function user_set_name( $p_user_id, $p_username ) {
		user_ensure_name_valid( $p_username );
		user_ensure_name_unique( $p_username );

		return user_set_field( $p_user_id, 'username', $p_username );
	}

	# --------------------
	# Reset the user's password
	#  Take into account the 'send_reset_password' setting
	#   - if it is ON, generate a random password and send an email
	#      (unless the second parameter is false)
	#   - if it is OFF, set the password to blank
	#  Return false if the user is protected, true if the password was
	#   successfully reset
	function user_reset_password( $p_user_id, $p_send_email=true ) {
		$t_protected = user_get_field( $p_user_id, 'protected' );

		# Go with random password and email it to the user
		if ( ON == $t_protected ) {
			return false;
		}

		# @@@ do we want to force blank password instead of random if
		#      email notifications are turned off?
		#     How would we indicate that we had done this with a return value?
		#     Should we just have two functions? (user_reset_password_random()
		#     and user_reset_password() )?

		if ( ON == config_get( 'send_reset_password' ) ) {
			# Create random password
			$t_email		= user_get_field( $p_user_id, 'email' );
			$t_password		= auth_generate_random_password( $t_email );
			$t_password2	= auth_process_plain_password( $t_password );

			user_set_field( $p_user_id, 'password', $t_password2 );

			# Send notification email
			if ( $p_send_email ) {
				email_reset( $p_user_id, $t_password );
			}
		} else { # use blank password, no emailing
			$t_password = auth_process_plain_password( '' );

			user_set_field( $p_user_id, 'password', $t_password );
		}

		return true;
	}
?>
