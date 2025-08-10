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
 * Verify Email Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses user_api.php
 *
 * Unhandled exceptions will be caught by the default error handler
 * @noinspection PhpUnhandledExceptionInspection
 */

# don't auto-login when trying to verify new user
$g_login_anonymous = false;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'tokens_api.php' );
require_api( 'utility_api.php' );
require_css( 'login.css' );


$f_user_id = gpc_get_string( 'id' );
$f_confirm_hash = gpc_get_string( 'confirm_hash' );

# Force logout on the current user if already authenticated
if( auth_is_user_authenticated() ) {
	auth_logout();

	# reload the page after logout
	print_header_redirect( 'verify_email.php?id=' . $f_user_id . '&confirm_hash=' . $f_confirm_hash );
}

# Make sure the hash is valid and we're actually verifying an e-mail address
$t_token_confirm_hash = token_get_value( TOKEN_ACCOUNT_ACTIVATION, $f_user_id );
$t_token_change_email = token_get_value( TOKEN_ACCOUNT_CHANGE_EMAIL, $f_user_id );
if( $t_token_confirm_hash === null
	|| $t_token_change_email === null
	|| $f_confirm_hash !== $t_token_confirm_hash
) {
	trigger_error( ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID, ERROR );
}

# Login again as the user
auth_attempt_script_login( user_get_username( $f_user_id ) );
user_increment_login_count( $f_user_id );
$t_row = user_get_row( $f_user_id );
extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_form_title = lang_get( 'verify_email_title' );

layout_login_page_begin( $t_form_title );
?>

<div class="col-md-offset-4 col-md-4 col-sm-8 col-sm-offset-1">
	<div class="login-container">
		<div class="space-12 hidden-480"></div>
		<?php layout_login_page_logo() ?>
		<div class="space-24 hidden-480"></div>

		<div class="position-relative">
		<div class="signup-box visible widget-box no-border" id="login-box">
		<div class="widget-body">
		<div class="widget-main">

		<div id="verify-div" class="form-container">
			<form id="account-update-form" method="post" action="account_update.php">
				<legend>
					<span><?php echo $t_form_title . ' - ' . string_display_line( $u_username ) ?></span>
				</legend>

				<div id="reset-passwd-msg" class="alert alert-sm alert-warning ">
					<?php printf( lang_get( 'verify_email_warning' ), $t_token_change_email ); ?>
				</div>

				<input type="hidden" name="verify_user_id" value="<?php echo $u_id ?>">
				<input type="hidden" name="verify_email" value="1">
				<input type="hidden" name="confirm_hash" value="<?php echo string_html_specialchars( $f_confirm_hash ) ?>">
				<?php echo form_security_field( 'account_update' ); ?>
				<div class="space-10"></div>

				<button type="submit" class="width-100 width-40 btn btn-success btn-inverse bigger-110">
					<span class="bigger-110"><?php echo lang_get( 'update_user_button' ) ?></span>
				</button>
			</form>
		</div>

		</div>
		</div>
		</div>
		</div>
	</div>
</div>

<?php
layout_login_page_end();
