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

	$f_id = gpc_get_int( 'f_id' );

	# grab user data and prefix with u_
	$row = user_get_row( $f_id );
	extract( $row, EXTR_PREFIX_ALL, 'u' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu() ?>

<br />
<div align="center">
<form method="post" action="manage_user_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>" />
		<?php echo lang_get( 'edit_user_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="70%">
		<input type="text" size="16" maxlength="32" name="f_username" value="<?php echo $u_username ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php print_email_input( 'f_email', $u_email ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<select name="f_access_level">
			<?php print_enum_string_option_list( 'access_levels', $u_access_level ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="f_enabled" <?php check_checked( $u_enabled, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'protected' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="f_protected" <?php check_checked( $u_protected, ON ); ?> />
	</td>
</tr>
<tr>
	<td colspan="2" class="center">
		<input type="submit" value="<?php echo lang_get( 'update_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">

<!-- Reset Button -->
	<form method="post" action="manage_user_reset.php">
	<input type="hidden" name="f_id" value="<?php echo $u_id ?>" />
	<input type="hidden" name="f_email" value="<?php echo $u_email ?>" />
	<input type="submit" value="<?php echo lang_get( 'reset_password_button' ) ?>" />
	</form>


<!-- Delete Button -->
	<form method="post" action="manage_user_delete_page.php">
	<input type="hidden" name="f_id" value="<?php echo $u_id ?>" />
	<input type="submit" value="<?php echo lang_get( 'delete_user_button' ) ?>" />
	</form>
</div>

<br />
<div align="center">
<?php
	if ( ON == $g_send_reset_password ) {
		echo lang_get( 'reset_password_msg' );
	} else {
		echo lang_get( 'reset_password_msg2' );
	}
?>
</div>

<?php ### BEGIN User to Project Add Form ?>
<br />
<div align="center">
<form method="post" action="manage_user_proj_add.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="f_user_id" value="<?php echo $u_id ?>" />
		<?php echo lang_get( 'add_user_title' ) ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category" width="30%">
		<?php echo lang_get( 'assigned_projects' ) ?>:
	</td>
	<td width="70%">
		<?php print_project_user_list( $u_id ) ?>
	</td>
</tr>
<tr class="row-2" valign="top">
	<td class="category">
		<?php echo lang_get( 'unassigned_projects' ) ?>:
	</td>
	<td>
		<select name="f_project_id[]" multiple size="5">
			<?php print_project_user_list_option_list2( $u_id ) ?>
		</select>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
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
		<input type="submit" value="<?php echo lang_get( 'add_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php ### END User to Project Add Form ?>

<?php
	if ( access_level_check_greater_or_equal ( ADMINISTRATOR ) ) {
		include ( 'account_prefs_inc.php' );
		edit_account_prefs( $u_id, false, false, 'manage_page.php' );
	}
?>


<?php print_page_bot1( __FILE__ ) ?>
