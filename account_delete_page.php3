<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
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

<p>
<div align=center>
<hr size=1 width=50%>

Are you sure you wish to delete your account?

<form method=post action="<? echo $g_account_update ?>">
	<input type=hidden name=f_id value="<? echo $f_id ?>">
	<input type=hidden name=f_action value="delete">
	<input type=submit value=" Delete Account ">
</form>

<hr size=1 width=50%>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>