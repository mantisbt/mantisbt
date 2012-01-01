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
	 * CALLERS
	 * This page is called from:
	 * - print_menu()
	 * - print_account_menu()
	 * - header redirects from account_*.php
	 * - included by verify.php to allow user to change their password
	 *
	 * EXPECTED BEHAVIOUR
	 * - Display the user's current settings
	 * - Allow the user to edit their settings
	 * - Allow the user to save their changes
	 * - Allow the user to delete their account if account deletion is enabled
	 *
	 * CALLS
	 * This page calls the following pages:
	 * - account_update.php  (to save changes)
	 * - account_delete.php  (to delete the user's account)
	 *
	 * RESTRICTIONS & PERMISSIONS
	 * - User must be authenticated
	 * - The user's account must not be protected
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
	$row = user_get_row( auth_get_current_user_id() );
	extract( $row, EXTR_PREFIX_ALL, 'u' );

	$t_ldap = ( LDAP == config_get( 'login_method' ) );

	# In case we're using LDAP to get the email address... this will pull out
	#  that version instead of the one in the DB
	$u_email = user_get_email( $u_id, $u_username );

	# note if we are being included by a script of a different name, if so,
	#  this is a mandatory password change request
	$t_force_pw_reset = is_page_name( 'verify.php' );

	# Only show the update button if there is something to update.
	$t_show_update_button = false;

	html_page_top( lang_get( 'account_link' ) );
?>

<!-- # Edit Account Form BEGIN -->
<br />
<?php if ( $t_force_pw_reset ) { ?>
<center><div style="color:red; width:75%">
		<?php
			echo lang_get( 'verify_warning' );
			if ( helper_call_custom_function( 'auth_can_change_password', array() ) ) {
				echo '<br />' . lang_get( 'verify_change_password' );
			}
		?>
</div></center>
<br />
<?php } ?>
<div align="center">
<form method="post" action="account_update.php">
<?php echo form_security_field( 'account_update' ); ?>
<table class="width75" cellspacing="1">

	<!-- Headings -->
	<tr>
		<td class="form-title">
			<?php echo lang_get( 'edit_account_title' ) ?>
		</td>
		<td class="right">
			<?php print_account_menu( 'account_page.php' ) ?>
		</td>
	</tr>

<?php if ( !helper_call_custom_function( 'auth_can_change_password', array() ) ) { ?> <!-- With LDAP -->

	<!-- Username -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo string_display_line( $u_username ) ?>
		</td>
	</tr>

	<!-- Password -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get( 'password' ) ?>
		</td>
		<td>
			<?php echo lang_get( 'no_password_change' ) ?>
		</td>
	</tr>

	<!-- Without LDAP -->
<?php } else {
	$t_show_update_button = true;
?>

	<!-- Username -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo string_display_line( $u_username ) ?>
		</td>
	</tr>

	<!-- Password -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php
				echo lang_get( 'password' );
				if ( $t_force_pw_reset ) {
			?>
			<span class="required">*</span>
			<?php } ?>
		</td>
		<td>
			<input type="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" name="password" />
		</td>
	</tr>

	<!-- Password confirmation -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php
				echo lang_get( 'confirm_password' );
				if ( $t_force_pw_reset ) {
			?>
			<span class="required">*</span>
			<?php } ?>
		</td>
		<td>
			<input type="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" name="password_confirm" />
		</td>
	</tr>

<?php } ?>
	<!-- End LDAP conditional -->

	<!-- Email -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
		<?php
			// With LDAP
			if ( $t_ldap && ON == config_get( 'use_ldap_email' ) ) {
				echo string_display_line( $u_email );
			}
			// Without LDAP
			else {
				$t_show_update_button = true;
				print_email_input( 'email', $u_email );
			}
		?>
		</td>
	</tr>

	<!-- Realname -->
	<tr <?php echo helper_alternate_class() ?> valign="top">
		<td class="category">
			<?php echo lang_get( 'realname' ) ?>
		</td>
		<td>
		<?php
			// With LDAP
			if ( $t_ldap && ON == config_get( 'use_ldap_realname' ) ) {
				echo string_display_line( ldap_realname_from_username( $u_username ) );
			}
			// Without LDAP
			else {
				$t_show_update_button = true;
		?>
			<input type="text" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" name="realname" value="<?php echo string_attribute( $u_realname ) ?>" />
		<?php
			}
		?>
		</td>
	</tr>

	<!-- Access level -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get( 'access_level' ) ?>
		</td>
		<td>
			<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
		</td>
	</tr>

	<!-- Project access level -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get( 'access_level_project' ) ?>
		</td>
		<td>
			<?php echo get_enum_element( 'access_levels', current_user_get_access_level() ) ?>
		</td>
	</tr>

	<!-- Assigned project list -->
	<tr <?php echo helper_alternate_class() ?> valign="top">
		<td class="category">
			<?php echo lang_get( 'assigned_projects' ) ?>
		</td>
		<td>
			<?php print_project_user_list( auth_get_current_user_id(), false ) ?>
		</td>
	</tr>

	<?php if ( $t_show_update_button ) { ?>
	<!-- BUTTONS -->
	<tr>
		<td class="left">
			<?php if ( $t_force_pw_reset ) { ?>
			<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
			<?php } ?>
		</td>
		<!-- Update Button -->
		<td>
			<input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</td>
	</tr>
	<?php } ?>
</table>
</form>
</div>

<br />
<?php # Delete Account Form BEGIN ?>
<?php
	# check if users can't delete their own accounts
	if ( ON == config_get( 'allow_account_delete' ) ) {
?>

<!-- Delete Button -->
<div class="border center">
	<form method="post" action="account_delete.php">
	<?php echo form_security_field( 'account_delete' ) ?>
	<input type="submit" class="button" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
	</form>
</div>

<?php
}
# Delete Account Form END

html_page_bottom();
