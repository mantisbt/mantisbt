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
	check_access( ADMINISTRATOR );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( $g_manage_create_user_page ) ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<form method="post" action="<?php echo $g_manage_create_new_user ?>">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_create_new_account_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_username ?>
	</td>
	<td width="75%">
		<input type="text" name="f_username" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email ?>
	</td>
	<td>
		<input type="text" name="f_email" size="32" maxlength="64">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_password ?>
	</td>
	<td>
		<input type="password" name="f_password" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_verify_password ?>
	</td>
	<td>
		<input type="password" name="f_password_verify" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_access_level ?>
	</td>
	<td>
		<select name="f_access_level">
			<?php print_enum_string_option_list( "access_levels", REPORTER ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_enabled ?>
	</td>
	<td>
		<input type="checkbox" name="f_enabled" CHECKED>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_protected ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_protected">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_create_user_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>