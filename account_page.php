<?php
# MantisBT - A PHP based bugtracking system

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
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses ldap_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'ldap_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

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

if ( $t_force_pw_reset ) {
	echo '<div id="reset-passwd-msg" class="important-msg">';
	echo '<ul>';
	echo '<li>' . lang_get( 'verify_warning' ) . '</li>';
	if ( helper_call_custom_function( 'auth_can_change_password', array() ) ) {
		echo '<li>' . lang_get( 'verify_change_password' ) . '</li>';
	}
	echo '</ul>';
	echo '</div>';
}
?>

<div id="account-update-div" class="form-container">
	<form id="account-update-form" method="post" action="account_update.php">
		<fieldset <?php if ( $t_force_pw_reset ) { ?> class="has-required"<?php } ?>>
			<legend><span><?php echo lang_get( 'edit_account_title' ); ?></span></legend>
			<?php echo form_security_field( 'account_update' );
			print_account_menu( 'account_page.php' );

		if ( !helper_call_custom_function( 'auth_can_change_password', array() ) ) {
			# With LDAP --> ?>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<span class="display-label"><span><?php echo lang_get( 'username' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo string_display_line( $u_username ) ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<span class="display-label"><span><?php echo lang_get( 'password' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo lang_get( 'no_password_change' ) ?></span></span>
				<span class="label-style"></span>
			</div><?php
		} else {
			# Without LDAP
			$t_show_update_button = true;
			?>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<span class="display-label"><span><?php echo lang_get( 'username' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo string_display_line( $u_username ) ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="password" <?php if ( $t_force_pw_reset ) { ?> class="required" <?php } ?>><span><?php echo lang_get( 'password' ) ?></span></label>
				<span class="input"><input id="password" type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="password-confirm" <?php if ( $t_force_pw_reset ) { ?> class="required" <?php } ?>><span><?php echo lang_get( 'confirm_password' ) ?></span></label>
				<span class="input"><input id="password-confirm" type="password" name="password_confirm" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div><?php
		} ?>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<span class="display-label"><span><?php echo lang_get( 'email' ) ?></span></span>
				<span class="input"><?php
				if ( $t_ldap && ON == config_get( 'use_ldap_email' ) ) {
					// With LDAP
					echo '<span class="field-value">' . string_display_line( $u_email ) . '</span>';
				} else {
					// Without LDAP
					$t_show_update_button = true;
					print_email_input( 'email', $u_email );
				} ?>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>"><?php
				if ( $t_ldap && ON == config_get( 'use_ldap_realname' ) ) {
					# With LDAP
					echo '<span class="display-label"><span>' . lang_get( 'realname' ) . '</span></span>';
					echo '<span class="input">';
					echo '<span class="field-value">';
					echo string_display_line( ldap_realname_from_username( $u_username ) );
					echo '</span>';
					echo '</span>';
				} else {
					# Without LDAP
					$t_show_update_button = true;
					echo '<label for="realname"><span>' . lang_get( 'realname' ) . '</span></label>';
					echo '<span class="input">';
					echo '<input id="realname" type="text" size="32" maxlength="' . DB_FIELD_SIZE_REALNAME . '" name="realname" value="' . string_attribute( $u_realname ) . '" />';
					echo '</span>';
				} ?>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<span class="display-label"><span><?php echo lang_get( 'access_level' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo get_enum_element( 'access_levels', $u_access_level ); ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<span class="display-label"><span><?php echo lang_get( 'access_level_project' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo get_enum_element( 'access_levels', current_user_get_access_level() ); ?></span></span>
				<span class="label-style"></span>
			</div>
			<?php
			$t_projects = user_get_assigned_projects( auth_get_current_user_id() );
			if( count( $t_projects ) > 0 ) {
				echo '<div class="field-container ' . helper_alternate_class_no_attribute() . '">';
				echo '<span class="display-label"><span>' . lang_get( 'assigned_projects' ) . '</span></span>';
				echo '<div class="input">';
				echo '<ul class="project-list">';
				foreach( $t_projects AS $t_project_id=>$t_project ) {
					$t_project_name = string_attribute( $t_project['name'] );
					$t_view_state = $t_project['view_state'];
					$t_access_level = $t_project['access_level'];
					$t_access_level = get_enum_element( 'access_levels', $t_access_level );
					$t_view_state = get_enum_element( 'project_view_state', $t_view_state );

					echo '<li><span class="project-name">' . $t_project_name . '</span> <span class="access-level">' . $t_access_level . '</span> <span class="view-state">' . $t_view_state . '</span></li>'; 
				}
				echo '</ul>';
				echo '</div>';
				echo '<span class="label-style"></span>';
				echo '</div>';
			}
			?>
	<?php if ( $t_show_update_button ) { ?>
		<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" /></span>
	<?php } ?>
		</fieldset>
	</form>
</div>
<?php # check if users can't delete their own accounts
if ( ON == config_get( 'allow_account_delete' ) ) { ?>

<!-- Delete Button -->
<div class="form-container">
	<form method="post" action="account_delete.php">
		<fieldset>
			<?php echo form_security_field( 'account_delete' ) ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'delete_account_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
}
html_page_bottom();
