<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );

	# grab user data and prefix with u_
	$row = get_user_info_by_id_arr( $f_id );
	extract( $row, EXTR_PREFIX_ALL, 'u' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu() ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<form method="post" action="manage_user_update.php">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
		<?php echo $s_edit_user_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo $s_username ?>:
	</td>
	<td colspan="2" width="70%">
		<input type="text" size="16" maxlength="32" name="f_username" value="<?php echo $u_username ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email ?>:
	</td>
	<td colspan="2">
		<input type="text" size="32" maxlength="64" name="f_email" value="<?php echo $u_email ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_access_level ?>:
	</td>
	<td colspan="2">
		<select name="f_access_level">
			<?php print_enum_string_option_list( 'access_levels', $u_access_level ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_enabled ?>:
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_enabled" <?php if ( ON == $u_enabled ) echo 'CHECKED' ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_protected ?>:
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_protected" <?php if ( ON == $u_protected ) echo 'CHECKED' ?>>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo $s_update_user_button ?>">
		</form>
	</td>
	<td class="center">
		<form method="post" action="manage_user_reset.php">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
		<input type="hidden" name="f_email" value="<?php echo $u_email ?>">
		<input type="hidden" name="f_protected" value="<?php echo $u_protected ?>">
		<input type="submit" value="<?php echo $s_reset_password_button ?>">
		</form>
	</td>
	<td class="center">
		<form method="post" action="manage_user_delete_page.php">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
		<input type="hidden" name="f_protected" value="<?php echo $u_protected ?>">
		<input type="submit" value="<?php echo $s_delete_user_button ?>">
		</form>
	</td>
</tr>
</table>
</div>

<p>
<div align="center">
<?php
	if ( ON == $g_send_reset_password ) {
		PRINT "$s_reset_password_msg";
	} else {
		PRINT "$s_reset_password_msg2";
	}
?>
</div>

<?php ### BEGIN User to Project Add Form ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<form method="post" action="manage_user_proj_add.php">
		<input type="hidden" name="f_user_id" value="<?php echo $u_id ?>">
		<?php echo $s_add_user_title ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category" width="30%">
		<?php echo $s_assigned_projects ?>:
	</td>
	<td width="70%">
		<?php print_project_user_list( $u_id ) ?>
	</td>
</tr>
<tr class="row-2" valign="top">
	<td class="category">
		<?php echo $s_unassigned_projects ?>:
	</td>
	<td>
		<select name="f_project_id[]" multiple size="5">
			<?php print_project_user_list_option_list2( $u_id ) ?>
		</select>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo $s_access_level ?>:
	</td>
	<td>
		<select name="f_access_level">
			<?php # No administrator choice ?>
			<?php print_project_user_option_list( REPORTER ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_add_user_button ?>">
		</form>
	</td>
</tr>
</table>
</div>
<?php ### END User to Project Add Form ?>

<?php
	if ( access_level_check_greater_or_equal ( ADMINISTRATOR ) ) {
		include ( 'account_prefs_inc.php' );
		edit_account_prefs( $u_id, false, false, 'manage_page.php' );
	}
?>

<?php print_page_bot1( __FILE__ ) ?>
