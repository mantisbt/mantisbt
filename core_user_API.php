<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Cookie API
	###########################################################################
	### --------------------
	### checks to see that a user is logged in
	### if the user is and the account is enabled then let them pass
	### otherwise redirect them to the login page
	### if $p_redirect_url is specifed then redirect them to that page
	function login_cookie_check( $p_redirect_url="" ) {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$g_login_page, $g_logout_page, $g_login_select_proj_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( !empty( $g_string_cookie_val ) ) {
			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$t_enabled = get_current_user_field( "enabled" );
			### check for acess enabled
			if ( $t_enabled==0 ) {
				print_header_redirect( $g_logout_page );
			}

			### update last_visit date
			login_update_last_visit( $g_string_cookie_val );
			db_close();

			### if no project is selected then go to the project selection page
			if ( empty( $g_project_cookie_val ) ) {
				print_header_redirect( $g_login_select_proj_page );
				exit;
			}

			### go to redirect if set
			if ( !empty( $p_redirect_url ) ) {
				print_header_redirect( $p_redirect_url );
				exit;
			} else {			### continue with current page
				return;
			}
		} else {				### not logged in
			print_header_redirect( $g_login_page );
			exit;
		}
	}
	### --------------------
	### checks to see if a returning user is valid
	### also sets the last time they visited
	### otherwise redirects to the login page
	function index_login_cookie_check( $p_redirect_url="" ) {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$g_login_page, $g_logout_page, $g_login_select_proj_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( !empty( $g_string_cookie_val ) ) {
			if ( empty( $g_project_cookie_val ) ) {
				print_header_redirect( $g_login_select_proj_page );
				exit;
			}

			### set last visit cookie

			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$t_enabled = get_current_user_field( "enabled" );

			### check for acess enabled
			if ( $t_enabled==0 ) {
				print_header_redirect( $g_login_page );
			}

			### update last_visit date
			login_update_last_visit( $g_string_cookie_val );
			db_close();

			### go to redirect
			if ( !empty( $p_redirect_url ) ) {
				print_header_redirect( $p_redirect_url );
				exit;
			} else {			### continue with current page
				return;
			}
		} else {				### not logged in
			print_header_redirect( $g_login_page );
			exit;
		}
	}
	### --------------------
	# Only check to see if the user is logged in
	# redirect to logout_page if fail
	function login_user_check_only() {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$g_login_page, $g_logout_page, $g_login_select_proj_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( !empty( $g_string_cookie_val ) ) {
			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$t_enabled = get_current_user_field( "enabled" );
			### check for acess enabled
			if ( $t_enabled==0 ) {
				print_header_redirect( $g_logout_page );
			}
			db_close();
		} else {				### not logged in
			print_header_redirect( $g_login_page );
			exit;
		}
	}
	### --------------------
	###########################################################################
	# Authentication API
	###########################################################################
	### --------------------
	# Checks for password match using the globally specified login method
	function is_password_match( $p_test_password, $p_password ) {
		global $g_login_method;

		switch ( $g_login_method ) {
			case CRYPT:
						$salt = substr( $p_password, 0, 2 );
						if ( crypt( $p_test_password, $salt ) == $p_password ) {
							return true;
						} else {
							return false;
						}

			case PLAIN:

						if ( $p_test_password == $p_password ) {
							return true;
						} else {
							return false;
						}

			case MD5:
						if ( md5( $p_test_password ) == $p_password ) {
							return true;
						} else {
							return false;
						}
		}
		return false;
	}
	### --------------------
	# This function is only called from the login.php3 script
	function increment_login_count( $p_id ) {
		global $g_mantis_user_table;

		$query = "UPDATE $g_mantis_user_table
				SET login_count=login_count+1
				WHERE id='$p_id'";
		$result = db_query( $query );
	}
	### --------------------
	#
	function process_plain_password( $p_password ) {
		global $g_login_method;

		switch ( $g_login_method ) {
			case CRYPT:	return crypt( $p_password );
			case PLAIN:	return $p_password;
			case MD5:	return md5( $p_password );
		}
	}
	### --------------------
	###########################################################################
	# User Management API
	###########################################################################
	### --------------------
	# creates a random 12 character password
	function create_random_password( $p_email ) {
		mt_srand( microtime() );
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val );

		return substr( $t_val, 0, 12 );
	}
	### --------------------
	# This string is used to use as the login identified for the web cookie
	# It is not guarranteed to be unique but should be good enough
	# The string returned should be 64 characters in length
	function create_cookie_string( $p_email ) {
		mt_srand( microtime() );
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val ).md5( time() );
		return substr( $t_val, 0, 64 );
	}
	### --------------------
	###########################################################################
	# Access Control API
	###########################################################################
	### --------------------
	### check to see if the access level is strictly equal
	function access_level_check_equal( $p_access_level ) {
		global $g_string_cookie_val;

		if ( !isset($g_string_cookie_val) ) {
			return false;
		}

		$t_access_level = get_current_user_field( "access_level" );
		$t_access_level2 = get_project_access_level();

		if (( $t_access_level == $p_access_level )||( $t_access_level2 == $p_access_level )) {
			return true;
		} else {
			return false;
		}
	}
	### --------------------
	# check to see if the access level is equal or greater
	# this checks to see if the user has a higher access level for the current project
	function access_level_check_greater_or_equal( $p_access_level ) {
		global $g_string_cookie_val;

		if (( !isset( $g_string_cookie_val ) )||( empty( $g_string_cookie_val ) )) {
			return false;
		}

		$t_access_level = get_current_user_field( "access_level" );
		$t_access_level2 = get_project_access_level();

		# use the project level access level instead of the global access level
		# if the project level is not specified then use the global access level
		if ( $t_access_level2 == -1 ) {
			# do nothing
		} else if ( $t_access_level2 > $t_access_level ) {
			$t_access_level = $t_access_level2;
		}

		if ( $t_access_level >= $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
	### --------------------
	# @@@ UNUSED
	function is_project_manager( $p_project_id ) {
		global $g_mantis_project_table, $g_mantis_project_user_list_table;

		$t_user_id = get_current_user_field( "id" );
		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_user_list_table
				WHERE project_id='$p_project_id' AND user_id='$t_user_id' AND
					access_level='manager'";
		$result = db_query( $query );
		$t_count = db_result( $result );
		if ($t_count > 1) {
			return true;
		} else {
			return false;
		}
	}
	### --------------------
	# Checks to see if the user should be here.  If not then log the user out.
	function check_access( $p_access_level ) {
		global $g_logout_page;

		if ( !access_level_check_greater_or_equal( $p_access_level ) ) {
			### need to replace with access error page
			print_header_redirect( $g_logout_page );
			exit;
		}
	}
	### --------------------
	# translate the access level number to a name
	# @@@ UNUSED
	function trans_access_level( $p_num ) {
	}
	### --------------------
	# return the project access level for the current user/project key pair
	function get_project_access_level() {
		global	$g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$t_user_id = get_current_user_field( "id" );
		$query = "SELECT access_level
				FROM $g_mantis_project_user_list_table
				WHERE user_id='$t_user_id' AND project_id='$g_project_cookie_val'";
		$result = db_query( $query );
		if ( db_num_rows( $result )>0 ) {
			return db_result( $result, 0, 0 );
		} else {
			return -1;
		}
	}
	### --------------------
	###########################################################################
	# User Information API
	###########################################################################
	### --------------------
	### Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_field( $p_field_name ) {
		global 	$g_string_cookie_val, $g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {
			### get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = db_query( $query );
			return db_result( $result, 0 );
		} else {
			return 0;
		}
	}
	### --------------------
	### Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_pref_field( $p_field_name ) {
		global 	$g_string_cookie_val, $g_mantis_user_pref_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {

			$t_id = get_current_user_field( "id" );
			### get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_pref_table
					WHERE user_id='$t_id'";
			$result = db_query( $query );
			return db_result( $result, 0 );
		} else {
			return 0;
		}
	}
	### --------------------
	# return all data associated with a particular user id
	function get_user_info_by_id_arr( $p_user_id ) {
		global $g_mantis_user_table;

	    $query = "SELECT *
	    		FROM $g_mantis_user_table
	    		WHERE id='$p_user_id'";
	    $result =  db_query( $query );
	    return db_fetch_array( $result );
	}
	### --------------------
	# return all data associated with a particular user name
	function get_user_info_by_name_arr( $p_username ) {
		global $g_mantis_user_table;

	    $query = "SELECT *
	    		FROM $g_mantis_user_table
	    		WHERE username='$p_username'";
	    $result =  db_query( $query );
	    return db_fetch_array( $result );
	}
	### --------------------
	# return the specified preference field for the user id
	function get_user_pref_info( $p_user_id, $p_field ) {
		global $g_mantis_user_pref_table;

	    $query = "SELECT $p_field
	    		FROM $g_mantis_user_pref_table
	    		WHERE user_id='$p_user_id'";
	    $result =  db_query( $query );
	    if ( $result ) {
	    	return db_result( $result, 0, 0 );
	    } else {
	    	return 0;
	    }
	}
	### --------------------
	# return the specified user field for the user id
	function get_user_info( $p_user_id, $p_field ) {
		global $g_mantis_user_table;

	    $query = "SELECT $p_field
	    		FROM $g_mantis_user_table
	    		WHERE id='$p_user_id'";
	    $result =  db_query( $query );
	    return db_result( $result, 0, 0 );
	}
	###########################################################################
	# Miscellaneous User API
	###########################################################################
	### --------------------
	# Update the last_visited field to be NOW()
	function login_update_last_visit( $p_string_cookie_val ) {
		global $g_mantis_user_table;

		$query = "UPDATE $g_mantis_user_table
				SET last_visit=NOW()
				WHERE cookie_string='$p_string_cookie_val'";
		$result = db_query( $query );
	}
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>