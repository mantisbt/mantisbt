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
	check_access( MANAGER );
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<? print_manage_menu( $g_manage_project_version_edit_page ) ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_edit_project_version_title ?>
	</td>
</tr>
<tr class="row-1">
	<form method="post" action="<? echo $g_manage_project_version_update ?>">
	<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
	<input type="hidden" name="f_orig_version" value="<? echo $f_version ?>">
	<td class="category">
		<? # @@@ localize ?>
		Version
	</td>
	<td>
		<input type="text" name="f_version" size="32" maxlength="64" value="<? echo urldecode( $f_version ) ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? # @@@ localize ?>
		Date Order
	</td>
	<td>
		<input type="text" name="f_date_order" size="32" value="<? echo urldecode( $f_date_order ) ?>">
	</td>
</tr>
<tr>
	<td class="left" width="50%">
		<input type="submit" value="<? echo $s_update_version_button ?>">
	</td>
	</form>
	<form method="post" action="<? echo $g_manage_project_version_delete_page ?>">
	<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
	<input type="hidden" name="f_version" value="<? echo $f_version ?>">
	<td class="right" width="50%">
		<input type="submit" value="<? echo $s_delete_version_button ?>">
	</td>
	</form>
</tr>
</table>
</div>

<? print_page_bot1( __FILE__ ) ?>