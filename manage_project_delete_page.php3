<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater_or_equal( "administrator" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<hr size=1 width=50%>

<? echo $s_project_delete_msg ?>

<form method=post action="<? echo $g_manage_project_delete ?>">
	<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
	<input type=submit value="<? echo $s_project_delete_button ?>">
</form>

<hr size=1 width=50%>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>