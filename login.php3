<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Check login then redirect to main_page.php3 or to login_page.php3
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

   	### get user info
	$row = get_user_info_by_name_arr( $f_username );

	$login_result = 1;
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
	} else {
		### invalid login
		$login_result = 0;
	}

	$t_project_id = 0;
	if (( $login_result==1 )&&
		( $u_enabled==1 )&&
		is_password_match( $f_password, $u_password )) {

		### increment login count
		increment_login_count( $u_id );

		$t_project_id = get_default_project( $u_id );

		if (( isset( $f_perm_login ) )&&( $f_perm_login=="on")) {
			### set permanent cookie (1 year)
			setcookie( $g_string_cookie, $u_cookie_string, time()+$g_cookie_time_length );

			if ( $t_project_id > 0 ) {
				setcookie( $g_project_cookie, $t_project_id, time()+$g_cookie_time_length );
			}
		} else {
			### set temp cookie, cookie dies after browser closes
			setcookie( $g_string_cookie, $u_cookie_string );
			if ( $t_project_id > 0 ) {
				setcookie( $g_project_cookie, $t_project_id, time()+$g_cookie_time_length+$g_cookie_time_length );
			}
		}

		### login good
		$login_result = 1;
	} else {
		### invalid login
		$login_result = 0;
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	### goto main_page or back to login_page
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
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<p>
<div align="center">
<?
	if ( $t_project_id > 0 ) {							### SUCCESS
		print_bracket_link( $g_main_page, $s_proceed );
	} else if ( $login_result ) {						### SUCCESS
		print_bracket_link( $g_login_select_proj_page, $s_proceed );
	} else {											### FAILURE
		echo $s_login_error_msg;

		print_bracket_link( $g_login_page."?f_error=1", $s_proceed );
	}
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>