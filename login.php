<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Check login then redirect to main_page.php3 or to login_page.php3
?>
<?php include( "core_API.php" ) ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( BASIC_AUTH == $g_login_method ) {
		$f_username = isset( $PHP_AUTH_USER ) ? $PHP_AUTH_USER : $REMOTE_USER;
		$f_password = $PHP_AUTH_PW;
 	}

   	# get user info
	$row = get_user_info_by_name_arr( $f_username );

	$login_result = 1;
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
	} else {
		# invalid login
		$login_result = 0;

		# Login failed, create user if basic authentication
		if ( BASIC_AUTH == $g_login_method ) {
			if ( $t_cookie_string = signup_user( $f_username ) ) {
				$row = get_user_info_by_name_arr( $f_username );
				$login_result = 1;
				extract( $row, EXTR_PREFIX_ALL, "u" );
			}
		}

	}

	if (( $g_anonymous_account == $f_username ) && ( ON == $g_allow_anonymous_login )) {
		$f_password = "";
	}

	$t_project_id = 0;

	if (( 1 == $login_result )&&
		( ON == $u_enabled )&&
		is_password_match( $f_username, $f_password, $u_password )) {

		# increment login count
		increment_login_count( $u_id );

		$t_project_id = get_default_project( $u_id );

		if ( ( isset( $f_perm_login ) )&&( "on" == $f_perm_login ) ) {
			# set permanent cookie (1 year)
			setcookie( $g_string_cookie, $u_cookie_string, time()+$g_cookie_time_length, $g_cookie_path );
			if ( $t_project_id > -1 ) {
				setcookie( $g_project_cookie, $t_project_id, time()+$g_cookie_time_length, $g_cookie_path );
			}
		} else {
			# set temp cookie, cookie dies after browser closes
			setcookie( $g_string_cookie, $u_cookie_string, 0, $g_cookie_path );
			if ( $t_project_id > -1 ) {
				setcookie( $g_project_cookie, $t_project_id, time()+$g_cookie_time_length+$g_cookie_time_length, $g_cookie_path );
			}
		}

		# login good
		$login_result = 1;
	} else {
		# invalid login
		$login_result = 0;
	}
	# goto main_page or back to login_page
	if ( $t_project_id > -1 ) {
		$t_redirect_url = $g_main_page;
	} else if ( $login_result ) {
		if ( isset($f_project_id) ) {
			$t_redirect_url = $g_set_project."?f_project_id=".$f_project_id;
		} else {
			$t_redirect_url = $g_login_select_proj_page;
		}
	} else {
		$t_redirect_url = $g_login_page."?f_error=1";
	}

	if ( $login_result ) {
		print_header_redirect( $t_redirect_url );
	}
?>
<?php print_page_top1() ?>
<?php
	# goto main_page or back to login_page
	if ( $t_project_id > 0 ) {
		print_meta_redirect( $g_main_page, 0 );
	} else if ( $login_result ) {
		if ( isset($f_project_id) ) {
			print_meta_redirect( $g_set_project."?f_project_id=".$f_project_id, 0 );
		} else {
			print_meta_redirect( $g_login_select_proj_page, 0 );
		}
	} else {
		print_meta_redirect( $g_login_page."?f_error=1", 0 );
	}
?>
<?php print_page_top2a() ?>

<p>
<div align="center">
<?php
	if ( $t_project_id > 0 ) {							# SUCCESS
		print_bracket_link( $g_main_page, $s_proceed );
	} else if ( $login_result ) {						# SUCCESS
		print_bracket_link( $g_login_select_proj_page, $s_proceed );
	} else {											# FAILURE
		echo $MANTIS_ERROR[ERROR_LOGIN]."<p>";

		print_bracket_link( $g_login_page."?f_error=1", $s_proceed );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>