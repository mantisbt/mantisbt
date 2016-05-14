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
 * Verify Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

# don't auto-login when trying to verify new user
$g_login_anonymous = false;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );

# check if at least one way to get here is enabled
if( OFF == config_get( 'allow_signup' ) &&
	OFF == config_get( 'lost_password_feature' ) &&
	OFF == config_get( 'send_reset_password' ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
}

$f_user_id = gpc_get_string( 'id' );
$f_confirm_hash = gpc_get_string( 'confirm_hash' );

# force logout on the current user if already authenticated
if( auth_is_user_authenticated() ) {
	auth_logout();

	# reload the page after logout
	print_header_redirect( 'verify.php?id=' . $f_user_id . '&confirm_hash=' . $f_confirm_hash );
}

$t_token_confirm_hash = token_get_value( TOKEN_ACCOUNT_ACTIVATION, $f_user_id );

if( $f_confirm_hash != $t_token_confirm_hash ) {
	trigger_error( ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID, ERROR );
}

user_reset_failed_login_count_to_zero( $f_user_id );
user_reset_lost_password_in_progress_count_to_zero( $f_user_id );

# fake login so the user can set their password
auth_attempt_script_login( user_get_field( $f_user_id, 'username' ) );

user_increment_login_count( $f_user_id );


# extracts the user information
# and prefixes it with u_
$t_row = user_get_row( $f_user_id );

extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_can_change_password = helper_call_custom_function( 'auth_can_change_password', array() );

html_page_top1();
html_page_top2a();

?>

<div id="reset-passwd-msg" class="important-msg">
	<ul>
		<?php
		if( $t_can_change_password ) {
			echo '<li>' . lang_get( 'verify_warning' ) . '</li>';
			echo '<li>' . lang_get( 'verify_change_password' ) . '</li>';
		} else {
			echo '<li>' . lang_get( 'no_password_change' ) . '</li>';
		}
		?>
	</ul>
</div>

<?php
if( $t_can_change_password ) {
?>

<div id="verify-div" class="form-container">
	<form id="account-update-form" method="post" action="account_update.php">
		<fieldset class="required">
			<legend><span><?php echo lang_get( 'edit_account_title' ); ?></span></legend>
			<div class="field-container">
				<span class="display-label"><span><?php echo lang_get( 'username' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo string_display_line( $u_username ) ?></span></span>
				<span class="label-style"></span>
			</div>
			<input type="hidden" name="verify_user_id" value="<?php echo $u_id ?>">
			<?php
			echo form_security_field( 'account_update' );
			# When verifying account, set a token and don't display current password
			token_set( TOKEN_ACCOUNT_VERIFY, true, TOKEN_EXPIRY_AUTHENTICATED, $u_id );
			?>
			<div class="field-container">
				<label for="realname"><span><?php echo lang_get( 'realname' ) ?></span></label>
				<span class="input">
					<input id="realname" type="text" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME ?>" name="realname" value="<?php echo string_attribute( $u_realname ) ?>" />
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="password" class="required"><span><?php echo lang_get( 'new_password' ) ?></span></label>
				<span class="input"><input id="password" type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="password-confirm" class="required"><span><?php echo lang_get( 'confirm_password' ) ?></span></label>
				<span class="input"><input id="password-confirm" type="password" name="password_confirm" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
}

html_page_bottom1a( __FILE__ );