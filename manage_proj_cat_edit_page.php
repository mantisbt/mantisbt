<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( $g_manage_project_category_edit_page ) ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_edit_project_category_title ?>
	</td>
</tr>
<tr class="row-1">
	<form method="post" action="<?php echo $g_manage_project_category_update ?>">
	<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
	<input type="hidden" name="f_orig_category" value="<?php echo $f_category ?>">
	<td class="center" colspan="2">
		<input type="text" name="f_category" size="32" maxlength="64" value="<?php echo urldecode( $f_category ) ?>">
	</td>
</tr>
<tr>
	<td class="left" width="50%">
		<input type="submit" value="<?php echo $s_update_category_button ?>">
	</td>
	</form>
	<form method="post" action="<?php echo $g_manage_project_category_delete_page ?>">
	<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
	<input type="hidden" name="f_category" value="<?php echo $f_category ?>">
	<td class="right" width="50%">
		<input type="submit" value="<?php echo $s_delete_category_button ?>">
	</td>
	</form>
</tr>
</form>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>