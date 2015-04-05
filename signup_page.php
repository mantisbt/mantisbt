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
 * Sign Up Page
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'crypto_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

require_css( 'login.css' );

require_js( 'login.js' );

# Check for invalid access to signup page
if( OFF == config_get_global( 'allow_signup' ) || LDAP == config_get_global( 'login_method' ) ) {
	print_header_redirect( 'login_page.php' );
}

# signup page shouldn't be indexed by search engines
html_robots_noindex();

html_page_top1();
html_page_top2a();

$t_public_key = crypto_generate_uri_safe_nonce( 64 );
?>

<div id="signup-div" class="form-container">
	<form id="signup-form" method="post" action="signup.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'signup_title' ) ?></span></legend>
			<?php echo form_security_field( 'signup' ); ?>

			<ul id="login-links">
				<li><a href="login_page.php"><?php echo lang_get( 'login_link' ); ?></a></li>
<?php
	# lost password feature disabled or reset password via email disabled
	if( ( LDAP != config_get_global( 'login_method' ) ) &&
		( ON == config_get( 'lost_password_feature' ) ) &&
		( ON == config_get( 'send_reset_password' ) ) &&
		( ON == config_get( 'enable_email_notification' ) ) ) {
?>
				<li><a href="lost_pwd_page.php"><?php echo lang_get( 'lost_password_link' ); ?></a></li>
<?php
	}
?>
			</ul>

			<div class="field-container">
				<label for="username"><span><?php echo lang_get( 'username' ) ?></span></label>
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" class="autofocus" /></span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label for="email-field"><span><?php echo lang_get( 'email_label' ) ?></span></label>
				<span class="input"><?php print_email_input( 'email', '' ) ?></span>
				<span class="label-style"></span>
			</div>

<?php
	$t_allow_passwd_change = helper_call_custom_function( 'auth_can_change_password', array() );

	# captcha image requires GD library and related option to ON
	if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 && $t_allow_passwd_change ) {
		$t_securimage_path = 'library/securimage';
		$t_securimage_show = $t_securimage_path . '/securimage_show.php';
		$t_securimage_play = $t_securimage_path . '/securimage_play.swf?'
			. http_build_query( array(
				'audio_file' => $t_securimage_path . '/securimage_play.php',
				'bgColor1=' => '#fff',
				'bgColor2=' => '#fff',
				'iconColor=' => '#777',
				'borderWidth=' => 1,
				'borderColor=' => '#000',
			) );
?>
			<div class="field-container">
				<label for="captcha-field">
					<span><?php echo lang_get( 'signup_captcha_request_label' ); ?></span>
				</label>
				<span id="captcha-input" class="input">
					<?php print_captcha_input( 'captcha' ); ?>

					<span id="captcha-image" class="captcha-image" style="padding-right:3px;">
						<img src="<?php echo $t_securimage_show; ?>" alt="visual captcha" />
						<ul id="captcha-refresh"><li><a href="#"><?php
							echo lang_get( 'signup_captcha_refresh' );
						?></a></li></ul>
					</span>

					<object type="application/x-shockwave-flash" width="19" height="19"
						data="<?php echo $t_securimage_play; ?>">
						<param name="movie" value="<?php echo $t_securimage_play; ?>" />
					</object>
				</span>

				<span class="label-style"></span>
			</div>
<?php
	}

	if( !$t_allow_passwd_change ) {
?>
			<span class="info-text"><?php echo lang_get( 'no_password_request' ); ?></span>
<?php
	}
?>
			<span class="info-text"><?php echo lang_get( 'signup_info' ); ?></span>

			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'signup_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
html_page_bottom1a( __FILE__ );
