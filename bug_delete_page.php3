<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<hr size=1 width=50%>
<p>
<div align=center>
Are you sure you wish to delete this bug?
<p>
<form method=post action="<? echo $g_bug_delete ?>">
	<input type=hidden name=f_id value="<? echo $f_id ?>">
	<input type=submit value=" Delete Bug ">
</form>
</div>
<hr size=1 width=50%>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>