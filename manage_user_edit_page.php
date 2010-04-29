<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_username = gpc_get_string( 'username', '' );

	if ( is_blank( $f_username ) ) {
		$f_user_id = gpc_get_int( 'user_id' );
		$t_user_id = $f_user_id;
	} else {
		$t_user_id = user_get_id_by_name( $f_username );
		if ( $t_user_id === false ) {
			error_parameters( $f_username );
			trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, ERROR );
		}
	}

	$t_user = user_get_row( $t_user_id );

	# Ensure that the account to be updated is of equal or lower access to the
	# current user.
	access_ensure_global_level( $t_user['access_level'] );

	$t_ldap = ( LDAP == config_get( 'login_method' ) );

	html_page_top();

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
		<input type="text" size="16" maxlength="<?php echo USERLEN;?>" name="username" value="<?php echo $t_user['username'] ?>" />
	</td>
</tr>

<!-- Realname -->
<tr <?php echo helper_alternate_class( 1 ) ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'realname' ) ?>:
	</td>
	<td width="70%">
		<?php
			if ( !$t_ldap || config_get( 'use_ldap_realname' ) == OFF ) {
		?>
				<input type="text" size="16" maxlength="<?php echo REALLEN;?>" name="realname" value="<?php echo string_attribute( $t_user['realname'] ) ?>" />
		<?php
			} else {
				echo string_display( user_get_realname( $f_user_id ) );
			}
		?>
	</td>
</tr>

<!-- Email -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php
			if ( !$t_ldap || config_get( 'use_ldap_email' ) == OFF ) {
				print_email_input( 'email', $t_user['email'] );
			} else {
				echo string_display( user_get_email( $f_user_id ) );
			}
		?>
	</td>
</tr>
 
<!-- Access Level -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<select name="access_level">
			<?php
				$t_access_level = $t_user['access_level'];
				if ( !MantisEnum::hasValue( config_get( 'access_levels_enum_string' ), $t_access_level ) ) {
					$t_access_level = config_get( 'default_new_account_access_level' );
				}
				print_project_access_levels_option_list( $t_access_level )
			?>
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
	<?php if ( config_get( 'enable_email_notification' ) == ON ) {
		echo lang_get( 'notify_user' ); ?>
		<input type="checkbox" name="send_email_notification" checked />
	<?php } ?>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<!-- RESET AND DELETE -->
<div class="border center">
<!-- Reset Button -->
	<form method="post" action="manage_user_reset.php">
<?php echo form_security_field( 'manage_user_reset' ) ?>
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'reset_password_button' ) ?>" />
	</form>

<!-- Delete Button -->
<?php if ( !( ( user_is_administrator( $t_user_id ) && ( user_count_level( config_get_global( 'admin_site_threshold' ) ) <= 1 ) ) ) ) { ?>
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
    !user_is_administrator( $t_user_id ) ) {
?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
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

<form method="post" action="manage_user_proj_add.php">
<?php echo form_security_field( 'manage_user_proj_add' ) ?>
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
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
			<?php print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) ) ?>
		</select>
	</td>
</tr>

<!-- Submit Buttom -->
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_user_button' ) ?>" />
	</td>
</tr>
</form>
</table>
</div>
<?php
	} # End of PROJECT ACCESS conditional section

	include ( 'account_prefs_inc.php' );
	edit_account_prefs( $t_user['id'], false, false, 'manage_user_edit_page.php?user_id=' . $t_user_id );

	html_page_bottom();
