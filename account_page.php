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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

require_once( 'core.php' );
require_api( 'api_token_api.php' );
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

$t_account_verification = defined( 'ACCOUNT_VERIFICATION_INC' );

#============ Permissions ============
auth_ensure_user_authenticated();

if( !$t_account_verification ) {
	auth_reauthenticate();
}

current_user_ensure_unprotected();

layout_page_header( lang_get( 'account_link' ) );

layout_page_begin();

# extracts the user information for the currently logged in user
# and prefixes it with u_
$t_row = user_get_row( auth_get_current_user_id() );

extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_ldap = ( LDAP == config_get( 'login_method' ) );

# In case we're using LDAP to get the email address... this will pull out
#  that version instead of the one in the DB
$u_email = user_get_email( $u_id );

# If the password is the default password, then prompt user to change it.
$t_reset_password = $u_username == 'administrator' && auth_does_password_match( $u_id, 'root' );

$t_can_change_password = auth_can_set_password();
$t_force_pw_reset = false;

# Only show the update button if there is something to update.
$t_show_update_button = false;

if( $t_reset_password && $t_can_change_password ) {
	?>
	<div class="alert alert-danger">
		<ul>
			<li><?php echo lang_get( 'warning_default_administrator_account_present' ) ?></li>
		</ul>
	</div>
	<?php
	$t_force_pw_reset = true;
}

print_account_menu( 'account_page.php' );

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

<div id="account-update-div" class="form-container">
	<form id="account-update-form" method="post" action="account_update.php">

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-user"></i>
			<?php echo lang_get( 'edit_account_title' ) ?>
		</h4>
	</div>
	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed table-striped">

		<fieldset>
			<?php echo form_security_field( 'account_update' );

			if( !$t_can_change_password ) {
				# With LDAP -->
			?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'username' ) ?>
				</td>
				<td>
					<?php echo string_display_line( $u_username ) ?>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'password' ) ?>
				</td>
				<td>
					<?php echo auth_password_managed_elsewhere_message() ?>
				</td>
			</tr><?php
			} else {
				# Without LDAP
				$t_show_update_button = true;
			?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'username' ) ?>
				</td>
				<td>
					<?php echo string_display_line( $u_username ) ?>
				</td>
			</tr><?php
			# When verifying account, set a token and don't display current password
			if( $t_account_verification ) {
				token_set( TOKEN_ACCOUNT_VERIFY, true, TOKEN_EXPIRY_AUTHENTICATED, $u_id );
			} else {
			?>
			<tr>
				<td class="category">
					<span class="required"><?php if( $t_force_pw_reset ) { ?> * <?php } ?></span> <?php echo lang_get( 'current_password' ) ?>
				</td>
				<td>
					<input class="input-sm" id="password-current" type="password" name="password_current" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
				</td>
			</tr>
			<?php
			} ?>
			<tr>
				<td class="category">
					<span class="required"><?php if( $t_force_pw_reset ) { ?> * <?php } ?></span> <?php echo lang_get( 'new_password' ) ?>
				</td>
				<td>
					<input class="input-sm" id="password" type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required"><?php if( $t_force_pw_reset ) { ?> * <?php } ?></span> <?php echo lang_get( 'confirm_password' ) ?>
				</td>
				<td>
					<input class="input-sm" id="password-confirm" type="password" name="password_confirm" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
				</td>
			</tr>
			<?php
			} ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'email' ) ?>
				</td>
				<td>
				<?php
				if( $t_ldap && ON == config_get( 'use_ldap_email' ) ) {
					# With LDAP
					echo string_display_line( $u_email );
				} else {
					# Without LDAP
					$t_show_update_button = true;
					print_email_input( 'email', $u_email );
				} ?>
				</td>
			</tr>
			<tr><?php
				if( $t_ldap && ON == config_get( 'use_ldap_realname' ) ) {
					# With LDAP
					echo '<td class="category">' . lang_get( 'realname' ) . '</td>';
					echo '<td>';
					echo string_display_line( ldap_realname_from_username( $u_username ) );
					echo '</td>';
				} else {
					# Without LDAP
					$t_show_update_button = true;
					echo '<td class="category">' . lang_get( 'realname' ) . '</td>';
					echo '<td>';
					echo '<input class="input-sm" id="realname" type="text" size="32" maxlength="' . DB_FIELD_SIZE_REALNAME . '" name="realname" value="' . string_attribute( $u_realname ) . '" />';
					echo '</td>';
				} ?>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'access_level' ) ?>
				</td>
				<td>
					<?php echo get_enum_element( 'access_levels', $u_access_level ); ?>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'access_level_project' ) ?>
				</td>
				<td>
					<?php echo get_enum_element( 'access_levels', current_user_get_access_level() ); ?>
				</td>
			</tr>
				</fieldset>
			</table>
		</div>
	</div>
	<?php if( $t_show_update_button ) { ?>
		<div class="widget-toolbox padding-8 clearfix">
			<?php if ($t_force_pw_reset) { ?>
				<span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
			<?php } ?>
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</div>
	<?php } ?>
	</div>
</div>

<?php
$t_projects = user_get_assigned_projects( auth_get_current_user_id() );
if( !empty( $t_projects ) ) {
?>
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-puzzle-piece"></i>
				<?php echo lang_get( 'assigned_projects' ) ?>
			</h4>
		</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th><?php echo lang_get( 'name' ) ?></th>
								<th><?php echo lang_get( 'access_level' ) ?></th>
								<th><?php echo lang_get( 'view_status' ) ?></th>
								<th><?php echo lang_get( 'description' ) ?></th>
							</tr>
						</thead>
						<?php
						foreach( $t_projects as $t_project_id => $t_project ) {
							$t_project_name = string_attribute( $t_project['name'] );
							$t_access_level = get_enum_element( 'access_levels', $t_project['access_level'] );
							$t_view_state = get_enum_element( 'project_view_state', $t_project['view_state'] );
							$t_description = string_display_links( project_get_field( $t_project_id, 'description' ) );
							echo '<tr>';
							echo '<td>' . $t_project_name . '</td>';
							echo '<td>' . $t_access_level . '</td>';
							echo '<td>' . $t_view_state . '</td>';
							echo '<td>' . $t_description . '</td>';
							echo '</tr>';
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

	</form>
</div>

<?php # check if users can't delete their own accounts
if( ON == config_get( 'allow_account_delete' ) ) { ?>

<!-- Delete Button -->
<div class="form-container">
	<form method="post" action="account_delete.php">
		<fieldset>
			<?php echo form_security_field( 'account_delete' ) ?>
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
		</fieldset>
	</form>
</div>
<?php
}
echo '</div>';
layout_page_end();
