<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	if ( $f_action="update" ) {
		if ( $f_save_prefs=="on" ) {
			setcookie( $g_hide_resolved_cookie, $f_hide_resolved, $g_time_length );
			setcookie( $g_view_limit_cookie, $f_view_limit, $g_time_length );
		}
		else {
			setcookie( $g_hide_resolved_cookie, $f_hide_resolved );
			setcookie( $g_view_limit_cookie, $f_view_limit );
		}
	}
	else if ( $f_action="reset" ) {
		setcookie( $g_hide_resolved_cookie );
		setcookie( $g_view_limit_cookie );
	}
	else {
		echo "ERROR: invalid action";
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	print_meta_redirect( $g_account_prefs_page, $g_wait_time );
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
Preferences have been successfully updated...
<p>
<a href="<? echo $g_account_prefs_page ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>