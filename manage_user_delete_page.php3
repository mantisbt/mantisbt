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

	check_access( ADMINISTRATOR );
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<p>
<div align="center">
	<? print_hr( $g_hr_size, $g_hr_width ) ?>
	<? echo $s_delete_account_sure_msg ?>

	<form method="post" action="<? echo $g_manage_user_delete ?>">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="f_protected" value="<? echo $f_protected ?>">
		<input type="submit" value="<? echo $s_delete_account_button ?>">
	</form>

	<? print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<? print_page_bot1( __FILE__ ) ?>