<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: user_api.php,v 1.9 2002-08-26 22:35:05 jfitzell Exp $
	# --------------------------------------------------------

	# --------------------
	# Create a user.
	# If $g_use_ldap_email then tries to find email using ldap
	# $p_email may be empty, but the user wont get any emails.
	# returns false if error, the generated cookie string if ok
	function signup_user( $p_username, $p_email=false ) {
		global $g_use_ldap_email,
		$g_mantis_user_table,
		$g_default_new_account_access_level,
		$g_mantis_user_pref_table,
		$g_default_advanced_report,
		$g_default_advanced_view, $g_default_advanced_update,
		$g_default_refresh_delay, $g_default_redirect_delay,
		$g_default_email_on_new, $g_default_email_on_assigned,
		$g_default_email_on_feedback, $g_default_email_on_resolved,
		$g_default_email_on_closed, $g_default_email_on_reopened,
		$g_default_email_on_bugnote, $g_default_email_on_status,
		$g_default_email_on_priority, $g_default_language;

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
		$c_username			= addslashes($p_username);
		$c_email			= addslashes($p_email);

		$query = "INSERT INTO $g_mantis_user_table
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
		$query = "INSERT INTO $g_mantis_user_pref_table
				(id, user_id, advanced_report, advanced_view, advanced_update,
				refresh_delay, redirect_delay,
				email_on_new, email_on_assigned,
				email_on_feedback, email_on_resolved,
				email_on_closed, email_on_reopened,
				email_on_bugnote, email_on_status,
				email_on_priority, language)
				VALUES
				(null, '$t_user_id', '$g_default_advanced_report',
				'$g_default_advanced_view', '$g_default_advanced_update',
				'$g_default_refresh_delay', '$g_default_redirect_delay',
				'$g_default_email_on_new', '$g_default_email_on_assigned',
				'$g_default_email_on_feedback', '$g_default_email_on_resolved',
				'$g_default_email_on_closed', '$g_default_email_on_reopened',
				'$g_default_email_on_bugnote', '$g_default_email_on_status',
				'$g_default_email_on_priority', '$g_default_language')";
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
	function delete_user( $p_user_id ) {
		global $g_mantis_user_table, $g_mantis_user_profile_table,
		       $g_mantis_user_pref_table, $g_mantis_project_user_list_table;

		$c_user_id = (integer)$p_user_id;

	    if ( !user_get_field( $p_user_id, 'protected' ) ) {
		    # Remove account
	    	$query = "DELETE
    				FROM $g_mantis_user_table
    				WHERE id='$c_user_id'";
	    	$result = db_query( $query );
			$success = db_affected_rows();

		    # Remove associated profiles
		    $query = "DELETE
	    			FROM $g_mantis_user_profile_table
	    			WHERE user_id='$c_user_id'";
		    $result = db_query( $query );
	
			# Remove associated preferences
    		$query = "DELETE
    				FROM $g_mantis_user_pref_table
    				WHERE user_id='$c_user_id'";
    		$result = db_query( $query );

	    	$query = "DELETE
    				FROM $g_mantis_project_user_list_table
	    			WHERE user_id='$c_user_id'";
		    $result = db_query( $query );

			drop_user_info_cache();
			
			return $success;
		} else {
			return 0;
		}
    }

	# --------------------
	# Flush user information cache.  Should be called when the user information
	# is changed.
	function drop_user_info_cache( ) {
		global $g_current_user_info;
		unset ( $g_current_user_info );
	}
	# --------------------
	###########################################################################
	# User Information API
	###########################################################################
	# --------------------
	# Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_field( $p_field_name ) {
		global 	$g_string_cookie_val, $g_mantis_user_table, $g_current_user_info;

		# if logged in
		if ( isset( $g_string_cookie_val ) ) {
			if ( !isset ( $g_current_user_info[ $p_field_name ] ) ) {
				# get user info
				$query = "SELECT * ".
						"FROM $g_mantis_user_table ".
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
		global 	$g_string_cookie_val, $g_mantis_user_pref_table, $g_cache_user_pref;

		# if logged in
		if ( isset( $g_string_cookie_val ) ) {
			$t_id = get_current_user_field( 'id' );

			if ( !isset( $g_cache_user_pref[$t_id] ) ) {
				# get user info
				$query = "SELECT *
						FROM $g_mantis_user_pref_table
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
	# --------------------
	# return all data associated with a particular user id
	function get_user_info_by_id_arr( $p_user_id ) {
		global $g_mantis_user_table;

		$c_user_id = (integer)$p_user_id;

		$query = "SELECT * " .
				"FROM $g_mantis_user_table " .
				"WHERE id='$c_user_id'";
		$result = db_query( $query );
	    return db_fetch_array( $result );
	}
	# --------------------
	# return all data associated with a particular user name
	function get_user_info_by_name_arr( $p_username ) {
		global $g_mantis_user_table;

		$c_username = addslashes($p_username);

	    $query = "SELECT *
	    		FROM $g_mantis_user_table
	    		WHERE username='$c_username'";
	    $result =  db_query( $query );
	    return db_fetch_array( $result );
	}
	# --------------------
	# return the specified preference field for the user id
	function get_user_pref_info( $p_user_id, $p_field ) {
		global $g_mantis_user_pref_table;

		$c_user_id = (integer)$p_user_id;

	    $query = "SELECT $p_field
	    		FROM $g_mantis_user_pref_table
	    		WHERE user_id='$c_user_id'";
	    $result =  db_query( $query );
	    if ( $result ) {
	    	return db_result( $result, 0, 0 );
	    } else {
	    	return 0;
	    }
	}
	# --------------------
	# return the specified user field for the user id
	# exception for LDAP email
	function get_user_info( $p_user_id, $p_field ) {
		global $g_mantis_user_table,$g_use_ldap_email,$g_login_method;
		if ( ( ON == $g_use_ldap_email ) && ( 'email' == $p_field  ) ) {
		    # Find out what username belongs to the p_user_id and ask ldap
		    return ldap_emailaddy( $p_user_id );
		}
		$c_user_id = (integer)$p_user_id;

		$query = "SELECT $p_field
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";
		$result =  db_query( $query );
		return db_result( $result, 0, 0 );

	}
	# --------------------
	# return whether user is monitoring bug for the user id and bug id
	function check_bug_monitoring( $p_user_id, $p_bug_id ) {
		global $g_mantis_bug_monitor_table;

		$c_user_id	= (integer)$p_user_id;
		$c_bug_id	= (integer)$p_bug_id;

		$query = "SELECT user_id
				FROM $g_mantis_bug_monitor_table
				WHERE user_id='$c_user_id' AND bug_id='$c_bug_id'";

		$result =  db_query( $query );
		return db_result( $result, 0, 0 );

	}
	# --------------------
	# return the specified user field for the user id
	# exception for LDAP email
	function user_get_field( $p_user_id, $p_field ) {
		global $g_mantis_user_table,$g_use_ldap_email,$g_login_method;
		if ( ( ON == $g_use_ldap_email ) && ( 'email' == $p_field  ) ) {
		    # Find out what username belongs to the p_user_id and ask ldap
		    return ldap_emailaddy( $p_user_id );
		}
		$c_user_id = (integer)$p_user_id;

		$query = "SELECT $p_field
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";

		$result =  db_query( $query );
		return db_result( $result, 0, 0 );

	}
	# --------------------
	# returns username
	function user_get_name( $p_user_id ) {
		global $g_mantis_user_table, $s_user_no_longer_exists;

		$c_user_id = db_prepare_int( $p_user_id );

		if ( 0 == $p_user_id ) {
			return '';
		}

		$query = "SELECT username
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			return db_result( $result );
		} else {
			return $s_user_no_longer_exists;
		}
	}
	# --------------------
	###########################################################################
	# Miscellaneous User API
	###########################################################################
	# --------------------
	# Update the last_visited field to be NOW()
	function login_update_last_visit( $p_string_cookie_val ) {
		global $g_mantis_user_table;
		
		drop_user_info_cache();

		$c_string_cookie_val = addslashes($p_string_cookie_val);

		$query = "UPDATE $g_mantis_user_table
				SET last_visit=NOW()
				WHERE cookie_string='$c_string_cookie_val'";
		$result = db_query( $query );
	}
	# --------------------
	function check_user_pref_exists( $p_project_id ) {
		global $g_mantis_user_pref_table;

		$c_project_id = (integer)$p_project_id;

		$t_user_id = get_current_user_field( 'id' );
	    $query = "SELECT COUNT(*)
	    		FROM $g_mantis_user_pref_table
	    		WHERE user_id='$t_user_id' AND project_id='$c_project_id'";
	    $result = db_query($query);
		$t_count =  db_result( $result, 0, 0 );
		if ( $t_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	function create_project_user_prefs( $p_project_id ) {
		global $g_mantis_user_pref_table;

		$c_project_id = (integer)$p_project_id;

		$t_user_id = get_current_user_field( 'id' );
	    $query = "INSERT
	    		INTO $g_mantis_user_pref_table
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
		global $g_mantis_bug_table, $g_mantis_user_table, $g_project_cookie_val;

		$c_project_id	= (integer)$p_project_id;
		$c_cookie_str	= addslashes($p_cookie_str);

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
				"FROM $g_mantis_bug_table ".
				"WHERE $t_where_prj ".
				"status<>'$t_res' AND status<>'$t_clo' AND ".
				"handler_id='$t_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# retrieve the number of open reported bugs by a user in a project
	function get_reported_open_bug_count( $p_project_id, $p_cookie_str ) {
		global $g_mantis_bug_table, $g_mantis_user_table, $g_project_cookie_val;

		$c_project_id	= (integer)$p_project_id;
		$c_cookie_str	= addslashes($p_cookie_str);

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
				"FROM $g_mantis_bug_table ".
				"WHERE $t_where_prj ".
				"status<>'$t_res' AND status<>'$t_clo' AND ".
				"reporter_id='$t_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# returns true if the username is unique, false if there is already a user
	#  with that username
	function user_is_name_unique( $p_username ) {
		global $g_mantis_user_table;

		$c_username = addslashes($p_username);

		$query = "SELECT username
			FROM $g_mantis_user_table
			WHERE username='$c_username'";

	    $result = db_query( $query );

	    if ( db_num_rows( $result ) > 0 ) {
			return false;
		} else {
			return true;
		}
	}
?>
