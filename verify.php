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
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_css( 'login.css' );


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

layout_login_page_begin();

?>

<div class="col-md-offset-4 col-md-4 col-sm-8 col-sm-offset-1">
	<div class="login-container">
		<div class="space-12 hidden-480"></div>
		<a href="<?php echo config_get( 'logo_url' ) ?>">
			<h1 class="center white">
				<img src="<?php echo helper_mantis_url( config_get( 'logo_image' ) ); ?>">
			</h1>
		</a>
		<div class="space-24 hidden-480"></div>

		<?php
			if( $t_can_change_password ) {
				echo '<div id="reset-passwd-msg" class="alert alert-sm alert-warning ">';
				echo lang_get( 'verify_warning' ) . '<br />';
				echo lang_get( 'verify_change_password' );
				echo '</div>';
			} else {
				echo '<div id="reset-passwd-msg" class="alert alert-sm alert-warning">';
				echo lang_get( 'no_password_change' );
				echo '</div>';
			}
		?>


		<?php
		if( $t_can_change_password ) {
		?>

			<div class="position-relative">
			<div class="signup-box visible widget-box no-border" id="login-box">
			<div class="widget-body">
				<div class="widget-main">

					<!-- Login Form BEGIN -->

		<div id="verify-div" class="form-container">
			<form id="account-update-form" method="post" action="account_update.php">
				<fieldset>
					<legend><span><?php echo lang_get( 'edit_account_title' ) . ' - ' . string_display_line( $u_username ) ?></span></legend>
					<div class="space-10"></div>
					<input type="hidden" name="verify_user_id" value="<?php echo $u_id ?>">
					<?php
					echo form_security_field( 'account_update' );
					# When verifying account, set a token and don't display current password
					token_set( TOKEN_ACCOUNT_VERIFY, true, TOKEN_EXPIRY_AUTHENTICATED, $u_id );
					?>
					<div class="field-container">
						<label class="block clearfix">
							<span class="block input-icon input-icon-right">
								<input id="realname" class="form-control" placeholder="<?php echo lang_get( 'realname' ) ?>" type="text" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME ?>" name="realname" value="<?php echo string_attribute( $u_realname ) ?>" />
								<i class="ace-icon fa fa-user"></i>
							</span>
						</label>
						<span class="label-style"></span>
					</div>

					<div class="field-container">
						<label class="block clearfix">
							<span class="block input-icon input-icon-right">
								<input id="password" class="form-control" placeholder="<?php echo lang_get( 'password' ) ?>" type="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" name="password"/>
								<i class="ace-icon fa fa-lock"></i>
							</span>
						</label>
						<span class="label-style"></span>
					</div>
					
					<div class="field-container">
						<label class="block clearfix">
							<span class="block input-icon input-icon-right">
								<input id="password-confirm" class="form-control" placeholder="<?php echo lang_get( 'confirm_password' ) ?>" type="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" name="password_confirm"/>
								<i class="ace-icon fa fa-lock"></i>
							</span>
						</label>
						<span class="label-style"></span>
					</div>
					<div class="space-18"></div>
					<span class="submit-button">
						<button type="submit" class="width-100 width-40 pull-right btn btn-success btn-inverse bigger-110">
							<span class="bigger-110"><?php echo lang_get( 'update_user_button' ) ?></span>
						</button>
					</span>

				</fieldset>
			</form>
		</div>
	</div>
</div>

			</div>
			</div>
			</div>
			</div>

<?php
}

layout_login_page_end();