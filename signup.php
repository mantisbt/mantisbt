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
 * Sign Up
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'crypto_api.php' );
require_api( 'email_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'signup' );

$f_username		= strip_tags( gpc_get_string( 'username' ) );
$f_email		= strip_tags( gpc_get_string( 'email' ) );
$f_captcha		= gpc_get_string( 'captcha', '' );

$f_username = trim( $f_username );
$f_email = trim( $f_email );
$f_captcha = utf8_strtolower( trim( $f_captcha ) );

# force logout on the current user if already authenticated
if( auth_is_user_authenticated() ) {
	auth_logout();
}

# Check to see if signup is allowed
if( OFF == config_get_global( 'allow_signup' ) ) {
	print_header_redirect( 'login_page.php' );
	exit;
}

if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 	&&
	helper_call_custom_function( 'auth_can_change_password', array() ) ) {
	# captcha image requires GD library and related option to ON
	require_lib( 'securimage/securimage.php' );

	$t_securimage = new Securimage();
	if( $t_securimage->check( $f_captcha ) == false ) {
		trigger_error( ERROR_SIGNUP_NOT_MATCHING_CAPTCHA, ERROR );
	}
}

# notify the selected group a new user has signed-up
if( user_signup( $f_username, $f_email ) ) {
	email_notify_new_account( $f_username, $f_email );
}

form_security_purge( 'signup' );

layout_login_page_begin();
?>

	<div class="col-md-offset-3 col-md-6 col-sm-10 col-sm-offset-1">
		<div class="login-container">
			<div class="space-12 hidden-480"></div>
			<a href="<?php echo config_get( 'logo_url' ) ?>">
				<h1 class="center white">
					<img src="<?php echo helper_mantis_url( config_get( 'logo_image' ) ); ?>">
				</h1>
			</a>
			<div class="space-24 hidden-480"></div>

			<div class="position-relative">

				<div class="signup-box visible widget-box no-border" id="login-box">
					<div class="widget-body">
						<div class="widget-main">
							<h4 class="header lighter bigger">
								<i class="ace-icon fa fa-pencil"></i>
								<?php echo lang_get( 'signup_title' ) ?>
							</h4>
							<div class="space-10"></div>

							<div class="center">
								<strong><?php echo lang_get( 'signup_done_title' ) ?></strong><br />
								<?php echo '[' . $f_username . ' - ' . $f_email . '] ' ?>
							</div>

							<div>
								<br />
								<?php echo lang_get( 'password_emailed_msg' ) ?>
								<br /><br />
								<?php echo lang_get( 'no_reponse_msg' ) ?>
								<br /><br />
							</div>

							<br />
							<div class="center">
								<a class="width-40 btn btn-inverse bigger-110 btn-success" href="login_page.php">
									<?php echo lang_get( 'proceed' ) ?>
								</a>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


<?php
layout_login_page_end();
