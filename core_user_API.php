<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Cookie API
	###########################################################################
	# --------------------
	# checks to see that a user is logged in
	# if the user is and the account is enabled then let them pass
	# otherwise redirect them to the login page
	# if $p_redirect_url is specifed then redirect them to that page
	function login_cookie_check( $p_redirect_url='', $p_return_page='' ) {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$REQUEST_URI;

		# if logged in
		if ( !empty( $g_string_cookie_val ) ) {
			# get user info
			$t_enabled = get_current_user_field( 'enabled' );
			# check for access enabled
			if ( OFF == $t_enabled ) {
				print_header_redirect( 'logout_page.php' );
			}

			# update last_visit date
			login_update_last_visit( $g_string_cookie_val );

			# if no project is selected then go to the project selection page
			if ( empty( $g_project_cookie_val ) ) {
				print_header_redirect( 'login_select_proj_page.php' );
			}

			# go to redirect if set
			if ( !empty( $p_redirect_url ) ) {
				print_header_redirect( $p_redirect_url );
			} else {			# continue with current page
				return;
			}
		} else {				# not logged in
			if ( empty ( $p_return_page ) ) {
				$p_return_page = $REQUEST_URI;
			}
			$p_return_page = htmlentities(urlencode($p_return_page));
			print_header_redirect( 'login_page.php?f_return='.$p_return_page );
		}
	}
	# --------------------
	# checks to see if a returning user is valid
	# also sets the last time they visited
	# otherwise redirects to the login page
	function index_login_cookie_check( $p_redirect_url='' ) {
		global 	$g_string_cookie_val, $g_project_cookie_val;

		# if logged in
		if ( !empty( $g_string_cookie_val ) ) {
			if ( empty( $g_project_cookie_val ) ) {
				print_header_redirect( 'login_select_proj_page.php' );
			}

			# set last visit cookie

			# get user info
			$t_enabled = get_current_user_field( 'enabled' );

			# check for acess enabled
			if ( OFF == $t_enabled ) {
				print_header_redirect( 'login_page.php' );
			}

			# update last_visit date
			login_update_last_visit( $g_string_cookie_val );

			# go to redirect
			if ( !empty( $p_redirect_url ) ) {
				print_header_redirect( $p_redirect_url );
			} else {			# continue with current page
				return;
			}
		} else {				# not logged in
			print_header_redirect( 'login_page.php' );
		}
	}
	# --------------------
	# Only check to see if the user is logged in
	# redirect to logout_page if fail
	function login_user_check_only() {
		global 	$g_string_cookie_val;

		# if logged in
		if ( !empty( $g_string_cookie_val ) ) {
			# get user info
			$t_enabled = get_current_user_field( 'enabled' );
			# check for acess enabled
			if ( OFF == $t_enabled ) {
				print_header_redirect( 'logout_page.php' );
			}
		} else {				# not logged in
			print_header_redirect( 'login_page.php' );
		}
	}
	# --------------------
	###########################################################################
	# Authentication API
	###########################################################################
	# --------------------
	# Checks for password match using the globally specified login method
	function is_password_match( $f_username, $p_test_password, $p_password ) {
		global $g_login_method, $g_allow_anonymous_login, $g_anonymous_account;
		global $PHP_AUTH_PW;


		# allow anonymous logins
		if ( $g_anonymous_account == $f_username ) {
			if ( ON == $g_allow_anonymous_login ) {
				return true;
			}
		}

		switch ( $g_login_method ) {
			case CRYPT:	$salt = substr( $p_password, 0, 2 );
						if ( crypt( $p_test_password, $salt ) == $p_password ) {
							return true;
						} else {
							return false;
						}
			case CRYPT_FULL_SALT:
						$salt = $p_password;
						if ( crypt( $p_test_password, $salt ) == $p_password ) {
							return true;
						} else {
							return false;
						}
			case PLAIN:	if ( $p_test_password == $p_password ) {
							return true;
						} else {
							return false;
						}
			case MD5:	if ( md5( $p_test_password ) == $p_password ) {
							return true;
						} else {
							return false;
						}
			case LDAP:	if ( ldap_uid_pass( $f_username, $p_test_password ) ) {
							return true;
						} else {
							return false;
						}
			case BASIC_AUTH:
					return ( isset( $PHP_AUTH_PW ) && ( $p_test_password == $PHP_AUTH_PW ) );

		}
		return false;
	}
	# --------------------
	# This function is only called from the login.php3 script
	function increment_login_count( $p_id ) {
		global $g_mantis_user_table;

		drop_user_info_cache();

		$c_id = (integer)$p_id;

		$query = "UPDATE $g_mantis_user_table
				SET login_count=login_count+1
				WHERE id='$c_id'";
		$result = db_query( $query );
	}
	# --------------------
	#
	function process_plain_password( $p_password ) {
		global $g_login_method;

		$t_processed_password = $p_password;
		switch ( $g_login_method ) {
			case CRYPT:	$salt = substr( $p_password, 0, 2 );
						$t_processed_password= crypt( $p_password, $salt );
						break;
			case CRYPT_FULL_SALT:
						$salt = $p_password;
						$t_processed_password = crypt( $p_password, $salt );
						break;
			case PLAIN:	$t_processed_password = $p_password;
						break;
			case MD5:	$t_processed_password = md5( $p_password );
						break;
			default:	$t_processed_password = $p_password;
						break;
		}
		# cut this off to 32 cahracters which the largest possible string in the database
		return substr( $t_processed_password, 0, 32 );
	}
	# --------------------
	###########################################################################
	# User Management API
	###########################################################################
	# --------------------
	# creates a random 12 character password
	# p_email is unused
	function create_random_password( $p_email ) {
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val );

		return substr( $t_val, 0, 12 );
	}
	# --------------------
	# This string is used to use as the login identified for the web cookie
	# It is not guarranteed to be unique and should be checked
	# The string returned should be 64 characters in length
	function generate_cookie_string() {
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val ).md5( time() );
		return substr( $t_val, 0, 64 );
	}
	# --------------------
	# The string returned should be 64 characters in length
	function create_cookie_string() {
		$t_cookie_string = generate_cookie_string();
		while ( check_cookie_string_duplicate( $t_cookie_string ) ) {
			$t_cookie_string = generate_cookie_string();
		}
		return $t_cookie_string;
	}
	# --------------------
	# Check to see that the unique identifier is really unique
	function check_cookie_string_duplicate( $p_cookie_string ) {
		global $g_mantis_user_table;

		$c_cookie_string = addslashes($p_cookie_string);

		$query = "SELECT COUNT(*)
				FROM $g_mantis_user_table
				WHERE cookie_string='$c_cookie_string'";
		$result = db_query( $query );
		$t_count = db_result( $result, 0, 0 );
		if ( $t_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
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
	###########################################################################
	# Access Control API
	###########################################################################
	# --------------------
	# check to see if the access level is strictly equal
	function access_level_check_equal( $p_access_level, $p_project_id=0 ) {
		global $g_string_cookie_val;

		if ( !isset( $g_string_cookie_val ) ) {
			return false;
		}

		$t_access_level = get_current_user_field( 'access_level' );
		$t_access_level2 = get_project_access_level( $p_project_id );

		if ( $t_access_level2 == $p_access_level ) {
			return true;
		} else if ( ( $t_access_level == $p_access_level ) &&
					( -1 == $t_access_level2 ) ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if the access level is equal or greater
	# this checks to see if the user has a higher access level for the current project
	function access_level_check_greater_or_equal( $p_access_level, $p_project_id=0 ) {
		global $g_string_cookie_val;

		# user isn't logged in
		if (( !isset( $g_string_cookie_val ) )||( empty( $g_string_cookie_val ) )) {
			return false;
		}

		# Administrators ALWAYS pass.
		if ( get_current_user_field( 'access_level' ) >= ADMINISTRATOR ) {
			return true;
		}

		$t_access_level = get_current_user_field( 'access_level' );
		$t_access_level2 = get_project_access_level( $p_project_id );

		# use the project level access level instead of the global access level
		# if the project level is not specified then use the global access level
		if ( -1 != $t_access_level2 ) {
			$t_access_level = $t_access_level2;
		}

		if ( $t_access_level >= $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
    # Checks if the access level is greater than or equal the specified access level
	# The return will be true for administrators, will be the project-specific access
	# right if found, or the default if project is PUBLIC and no specific access right
	# found, otherwise, (private/not found) will return false
	function access_level_ge_no_default_for_private ( $p_access_level, $p_project_id ) {
		global $g_string_cookie_val;

		# user isn't logged in
		if (( !isset( $g_string_cookie_val ) )||( empty( $g_string_cookie_val ) )) {
			return false;
		}

		# Administrators ALWAYS pass.
		if ( get_current_user_field( 'access_level' ) >= ADMINISTRATOR ) {
			return true;
		}

		$t_access_level = get_project_access_level( $p_project_id );
		$t_project_view_state = get_project_field( $p_project_id, 'view_state' );

		# use the project level access level instead of the global access level
		# if the project level is not specified then use the global access level
		if ( ( -1 == $t_access_level ) && ( PUBLIC == $t_project_view_state ) ) {
			$t_access_level = get_current_user_field( 'access_level' );
		}

		return ( $t_access_level >= $p_access_level );
	}
	# --------------------
	# check to see if the access level is strictly equal
	function absolute_access_level_check_equal( $p_access_level ) {
		global $g_string_cookie_val;

		if ( !isset( $g_string_cookie_val ) ) {
			return false;
		}

		$t_access_level = get_current_user_field( 'access_level' );
		if ( $t_access_level == $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if the access level is equal or greater
	# this checks to see if the user has a higher access level for the current project
	function absolute_access_level_check_greater_or_equal( $p_access_level ) {
		global $g_string_cookie_val;

		# user isn't logged in
		if (( !isset( $g_string_cookie_val ) ) ||
			( empty( $g_string_cookie_val ) )) {
			return false;
		}

		$t_access_level = get_current_user_field( 'access_level' );

		if ( $t_access_level >= $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# Checks to see if the user should be here.  If not then log the user out.
	function check_access( $p_access_level ) {
		# Administrators ALWAYS pass.
		if ( get_current_user_field( 'access_level' ) >= ADMINISTRATOR ) {
			return;
		}
		if ( !access_level_check_greater_or_equal( $p_access_level ) ) {
			# need to replace with access error page
			print_header_redirect( 'logout_page.php' );
		}
	}
	# --------------------
	# Checks to see if the user has access to this project
	# If not then log the user out
	# If not logged into the project it attempts to log you into that project
	function project_access_check( $p_bug_id, $p_project_id='0' ) {
		global	$g_mantis_project_user_list_table,
				$g_mantis_project_table, $g_mantis_bug_table,
				$g_project_cookie_val;

		project_check( $p_bug_id );

		# Administrators ALWAYS pass.
		if ( get_current_user_field( 'access_level' ) >= ADMINISTRATOR ) {
			return;
		}

		# access_level check
		$t_project_id = get_bug_field( $p_bug_id, 'project_id' );
		$t_project_view_state = get_project_field( $t_project_id, 'view_state' );

		# public project accept all users
		if ( PUBLIC == $t_project_view_state ) {
			return;
		} else {
			# private projects require users to be assigned
			$t_project_access_level = get_project_access_level( $t_project_id );
			if ( -1 == $t_project_access_level ) {
				print_header_redirect( 'login_select_proj_page.php' );
			} else {
				return;
			}
		}
	}
	# --------------------
	# Check to see if the currently logged in project and bug project id match
	# If there is no match then the project cookie will be set to the bug project id
	# No access check is done.  It is expected to be checked afterwards.
	function project_check( $p_bug_id ) {
		global	$g_project_cookie, $g_project_cookie_val, $g_view_all_cookie,
				$g_cookie_time_length, $g_cookie_path;

		$t_project_id = get_bug_field( $p_bug_id, 'project_id' );
		if ( $t_project_id != $g_project_cookie_val ) {
			setcookie( $g_project_cookie, $t_project_id, time()+$g_cookie_time_length, $g_cookie_path );
			setcookie( $g_view_all_cookie );

			$t_redirect_url = get_view_redirect_url( $p_bug_id, 1 );
			print_header_redirect( $t_redirect_url );
		}
	}
	# --------------------
	# return the project access level for the current user/project key pair.
	# use the project_id if supplied.
	function get_project_access_level( $p_project_id=0 ) {
		global	$g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$c_project_id = (integer)$p_project_id;

		$t_user_id = get_current_user_field( 'id' );
		if ( 0 == $p_project_id ) {
			if ( (integer)$g_project_cookie_val == 0 ) {
				return -1;
			}
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$g_project_cookie_val'";
		} else {
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$c_project_id'";
		}
		$result = db_query( $query );
		if ( db_num_rows( $result ) > 0 ) {
			return db_result( $result, 0, 0 );
		} else {
			return -1;
		}
	}
	# --------------------
	# Return the project user list access level for the current user/project key pair if it exists.
	# Otherwise return the default user access level.
	function get_effective_access_level( $p_user_id=0, $p_project_id=-1 ) {
		global	$g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$c_project_id = (integer)$p_project_id;

		# use the current user unless otherwise specified
		if ( 0 == $p_user_id ) {
			$t_user_id = get_current_user_field( 'id' );
		} else {
			$t_user_id = (integer)$p_user_id;
		}

		# all projects
		if ( -1 == $p_project_id ) {
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$g_project_cookie_val'";
		} else if ( 0 == $p_project_id ) {
			$g_project_cookie_val = p_project_id;
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id'";
		} else {
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$c_project_id'";
		}

		$result = db_query( $query );
		$count = db_num_rows( $result, 0, 0 );
		if ( $count>0 ) {
			return db_result( $result, 0, 0 );
		} else {
			return get_user_field( $t_user_id, 'access_level' );
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
		global 	$g_string_cookie_val, $g_mantis_user_pref_table;

		# if logged in
		if ( isset( $g_string_cookie_val ) ) {

			$t_id = get_current_user_field( 'id' );
			# get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_pref_table
					WHERE user_id='$t_id'";
			$result = db_query( $query );
			return db_result( $result, 0 );
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
	function get_user_field( $p_user_id, $p_field ) {
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
?>
