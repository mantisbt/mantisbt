<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( 'manage_user_create_page.php' ) ?>

<br />
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<form method="post" action="manage_user_create.php">
		<?php echo $s_create_new_account_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_username ?>
	</td>
	<td width="75%">
		<input type="text" name="f_username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email ?>
	</td>
	<td>
		<input type="text" name="f_email" size="32" maxlength="64" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_password ?>
	</td>
	<td>
		<input type="password" name="f_password" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_verify_password ?>
	</td>
	<td>
		<input type="password" name="f_password_verify" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_access_level ?>
	</td>
	<td>
		<select name="f_access_level">
			<?php print_enum_string_option_list( 'access_levels', REPORTER ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_enabled ?>
	</td>
	<td>
		<input type="checkbox" name="f_enabled" checked="checked" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_protected ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_protected" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_create_user_button ?>" />
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
