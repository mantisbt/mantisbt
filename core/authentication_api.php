<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: authentication_api.php,v 1.3 2002-08-29 02:56:23 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Authentication API
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
			$t_user_id = auth_get_current_user_id();
			# get user info
			$t_enabled = current_user_get_field( 'enabled' );
			# check for access enabled
			if ( OFF == $t_enabled ) {
				print_header_redirect( 'logout_page.php' );
			}

			# update last_visit date
			user_update_last_visit( $t_user_id );

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

			$t_user_id = auth_get_current_user_id();

			# set last visit cookie

			# get user info
			$t_enabled = current_user_get_field( 'enabled' );

			# check for acess enabled
			if ( OFF == $t_enabled ) {
				print_header_redirect( 'login_page.php' );
			}

			# update last_visit date
			user_update_last_visit( $g_string_cookie_val );

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
			$t_enabled = current_user_get_field( 'enabled' );
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

	function auth_get_current_user_cookie() {
		$t_cookie_name = config_get( 'string_cookie' );

		$t_cookie = gpc_get_string( $t_cookie_name, '' );

		return $t_cookie;
	}

	function auth_get_current_user_id() {
# @@@ caching the current user id is a major security hole until we
#     turn off register_globals so it's commented out
/* (caching commented out)
		global $g_cache_current_user_id;

		if ( isset( $g_cache_current_user_id ) {
			return $g_cache_current_user_id;
		}
*/
		$t_user_table = config_get( 'mantis_user_table' );

		$t_cookie_string = auth_get_current_user_cookie();
		
		# @@@ error with an error saying they aren't logged in?
		#     Or redirect to the login page maybe?

		$c_cookie_string = db_prepare_string( $t_cookie_string );

		$query = "SELECT id
				  FROM $t_user_table
				  WHERE cookie_string='$c_cookie_string'";

		$result = db_query( $query );

		if ( db_num_rows( $result ) < 1 ) {
			trigger_error( ERROR_AUTH_INVALID_COOKIE, WARNING );
			return false;
		}

		$t_user_id = db_result( $result );

/* (caching commented out)
		$g_cache_current_user_id = $t_user_id;
*/

		return $t_user_id;
	}
?>
