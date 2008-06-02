<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: manage_user_edit_page.php,v 1.18.2.1 2007-10-13 22:33:54 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_user_id = gpc_get_int( 'user_id' );
	$c_user_id = $f_user_id;

	$t_user = user_get_row( $f_user_id );

	html_page_top1();
	html_page_top2();

	print_manage_menu();
?>

<br />


<!-- USER INFO -->
<div align="center">
<form method="post" action="manage_user_update.php">
<?php echo form_security_field( 'manage_user_update' ) ?>
<table class="width75" cellspacing="1">
<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<?php echo lang_get( 'edit_user_title' ) ?>
	</td>
</tr>

<!-- Username -->
<tr <?php echo helper_alternate_class( 1 ) ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="70%">
		<input type="text" size="16" maxlength="32" name="username" value="<?php echo $t_user['username'] ?>" />
	</td>
</tr>

<!-- Realname -->
<tr <?php echo helper_alternate_class( 1 ) ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'realname' ) ?>:
	</td>
	<td width="70%">
		<input type="text" size="16" maxlength="100" name="realname" value="<?php echo string_attribute( $t_user['realname'] ) ?>" />
	</td>
</tr>

<!-- Email -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php print_email_input( 'email', $t_user['email'] ) ?>
	</td>
</tr>

<!-- Access Level -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<select name="access_level">
			<?php print_enum_string_option_list( 'access_levels', $t_user['access_level'] ) ?>
		</select>
	</td>
</tr>

<!-- Enabled Checkbox -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="enabled" <?php check_checked( $t_user['enabled'], ON ); ?> />
	</td>
</tr>

<!-- Protected Checkbox -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'protected' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="protected" <?php check_checked( $t_user['protected'], ON ); ?> />
	</td>
</tr>

<!-- Submit Button -->
<tr>
	<td colspan="2" class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<!-- RESET AND DELETE -->
<div class="border-center">
<!-- Reset Button -->
	<form method="post" action="manage_user_reset.php">
<?php echo form_security_field( 'manage_user_reset' ) ?>
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'reset_password_button' ) ?>" />
	</form>

<!-- Delete Button -->
<?php if ( !( ( ADMINISTRATOR <= $t_user['access_level'] ) && ( 1 >= user_count_level( ADMINISTRATOR ) ) ) ) { ?>
	<form method="post" action="manage_user_delete.php">
<?php echo form_security_field( 'manage_user_delete' ) ?>

		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'delete_user_button' ) ?>" />
	</form>
<?php } ?>
</div>
<br />
<div align="center">
<?php
	if ( ( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {
		echo lang_get( 'reset_password_msg' );
	} else {
		echo lang_get( 'reset_password_msg2' );
	}
?>
</div>


<!-- PROJECT ACCESS (if permissions allow) and user is not ADMINISTRATOR -->
<?php if ( access_has_global_level( config_get( 'manage_user_threshold' ) ) &&
    !access_has_global_level( ADMINISTRATOR, $t_user['id'] ) ){
?>
<br />
<div align="center">
<form method="post" action="manage_user_proj_add.php">
<?php echo form_security_field( 'manage_user_proj_add' ) ?>
<table class="width75" cellspacing="1">
<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<?php echo lang_get( 'add_user_title' ) ?>
	</td>
</tr>

<!-- Assigned Projects -->
<tr <?php echo helper_alternate_class( 1 ) ?> valign="top">
	<td class="category" width="30%">
		<?php echo lang_get( 'assigned_projects' ) ?>:
	</td>
	<td width="70%">
		<?php print_project_user_list( $t_user['id'] ) ?>
	</td>
</tr>

<!-- Unassigend Project Selection -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td class="category">
		<?php echo lang_get( 'unassigned_projects' ) ?>:
	</td>
	<td>
		<select name="project_id[]" multiple="multiple" size="5">
			<?php print_project_user_list_option_list2( $t_user['id'] ) ?>
		</select>
	</td>
</tr>

<!-- New Access Level -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<select name="access_level">
		<?php
			# No administrator choice
			print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) )
		?>
		</select>
	</td>
</tr>

<!-- Submit Buttom -->
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php
	} # End of PROJECT ACCESS conditional section
?>



<!-- ACCOUNT PREFERENCES -->
<?php
	include ( 'account_prefs_inc.php' );
	edit_account_prefs( $t_user['id'], false, false, 'manage_user_edit_page.php?user_id=' . $c_user_id );
?>

<?php html_page_bottom1( __FILE__ ) ?>
