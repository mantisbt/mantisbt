<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# Bug delete confirmation page
	# Page contiues to bug_delete.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( DEVELOPER );
	check_bug_exists( $f_id );
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<p>
<div align="center">
	<? print_hr( $g_hr_size, $g_hr_width ) ?>
	<? echo $s_delete_bug_sure_msg ?>

	<form method="post" action="<? echo $g_bug_delete ?>">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="f_bug_text_id" value="<? echo $f_bug_text_id ?>">
		<input type="submit" value="<? echo $s_delete_bug_button ?>">
	</form>

	<? print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<? print_page_bot1( __FILE__ ) ?>