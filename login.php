<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Check login then redirect to main_page.php3 or to login_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php
	if (isset($f_cookietest)) {
		if (!isset($$g_string_cookie)) {
			print_meta_redirect( 'login_page.php?f_cookie_error=1', 0 );
		} else {
			print_meta_redirect( $f_return );
		}
		exit;
	}
		

	if ( BASIC_AUTH == $g_login_method ) {
		check_varset( $f_username, $REMOTE_USER );
		$f_password = $PHP_AUTH_PW;
 	}

   	# get user info
	$row = user_get_row_by_name( $f_username );

	$login_result = 1;
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, 'u' );
	} else {
		# invalid login
		$login_result = 0;

		# Login failed, create user if basic authentication
		if ( BASIC_AUTH == $g_login_method ) {
			if ( $t_cookie_string = user_signup( $f_username ) ) {
				$row = user_get_row_by_name( $f_username );
				$login_result = 1;
				extract( $row, EXTR_PREFIX_ALL, 'u' );
			}
		}
	}

	if (( $g_anonymous_account == $f_username ) && ( ON == $g_allow_anonymous_login )) {
		$f_password = '';
	}

	$t_project_id = 0;

	if (( 1 == $login_result )&&
		( ON == $u_enabled )&&
		is_password_match( $f_username, $f_password, $u_password )) {

		# increment login count
		user_increment_login_count( $u_id );

		$t_project_id = user_get_pref( $u_id, 'default_project' );

		if ( ( isset( $f_perm_login ) )&&( 'on' == $f_perm_login ) ) {
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
	# goto $f_return, main_page or back to login_page
	if ( $t_project_id > -1 ) {
		if ( !empty($f_return) ) {
			$t_redirect_url = $f_return;
		} else {
			$t_redirect_url = 'main_page.php';
		}
	} else if ( $login_result ) {
		if ( isset($f_project_id) ) {
			$t_redirect_url = 'set_project.php?f_project_id='.$f_project_id;
		} else {
			$t_redirect_url = 'login_select_proj_page.php';
		}
	} else {
		$t_redirect_url = 'login_page.php?f_error=1';
	}
	
	if (!isset($f_cookietest)) {
		$t_redirect_url = 'login.php?f_cookietest=true&f_return=' . urlencode($t_redirect_url);
	}

	if ( $login_result ) {
		print_header_redirect( $t_redirect_url );
	}
?>
<?php print_page_top1() ?>
<?php
	# goto main_page or back to login_page
	if ( $t_project_id > 0 ) {
		if ( !empty($f_return) ) {
			$t_redirect_url = $f_return;
		} else {
			$t_redirect_url = 'main_page.php';
		}
		print_meta_redirect( $t_redirect_url, 0 );
	} else if ( $login_result ) {
		if ( isset($f_project_id) ) {
			print_meta_redirect( 'set_project.php?f_project_id='.$f_project_id, 0 );
		} else {
			print_meta_redirect( 'login_select_proj_page.php', 0 );
		}
	} else {
		print_meta_redirect( 'login_page.php?f_error=1', 0 );
	}
?>
<?php print_page_top2a() ?>

<p>
<div align="center">
<?php
	if ( $t_project_id > 0 ) {							# SUCCESS
		print_bracket_link( 'main_page.php', $s_proceed );
	} else if ( $login_result ) {						# SUCCESS
		print_bracket_link( 'login_select_proj_page.php', $s_proceed );
	} else {											# FAILURE
		print $MANTIS_ERROR[ERROR_LOGIN].'<p>';

		print_bracket_link( 'login_page.php?f_error=1', $s_proceed );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
