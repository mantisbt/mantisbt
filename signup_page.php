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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

/**
 * MantisBT Core API's
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

# Check for invalid access to signup page
if ( OFF == config_get_global( 'allow_signup' ) || LDAP == config_get_global( 'login_method' ) ) {
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
			if ( ( LDAP != config_get_global( 'login_method' ) ) &&
				( ON == config_get( 'lost_password_feature' ) ) &&
				( ON == config_get( 'send_reset_password' ) ) &&
				( ON == config_get( 'enable_email_notification' ) ) ) {
				echo '<li><a href="lost_pwd_page.php">', lang_get( 'lost_password_link' ), '</a></li>';
			}
			?>
			</ul>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="username"><span><?php echo lang_get( 'username' ) ?></span></label>
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" class="autofocus" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="email-field"><span><?php echo lang_get( 'email_label' ) ?></span></label>
				<span class="input"><?php print_email_input( 'email', '' ) ?></span>
				<span class="label-style"></span>
			</div>

			<?php
			$t_allow_passwd = helper_call_custom_function( 'auth_can_change_password', array() );
			if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 && ( true == $t_allow_passwd ) ) {
				# captcha image requires GD library and related option to ON

				echo '<div class="field-container ', helper_alternate_class_no_attribute(), '">';
				echo '<label for="captcha-field"><span>' . lang_get( 'signup_captcha_request_label' ) . '</span></label>';
				echo '<span id="captcha-input" class="input">';
				print_captcha_input( 'captcha', '' );
				echo '<span class="captcha-image"><img src="make_captcha_img.php?public_key=' . $t_public_key . '" alt="visual captcha" /></span>';
				echo '</span>';
				echo '<input type="hidden" name="public_key" value="' . $t_public_key . '" />';
				echo '<span class="label-style"></span>';
				echo '</div>';
			}
			if( false == $t_allow_passwd ) {
				echo '<span id="no-password-msg">';
				echo lang_get( 'no_password_request' );
				echo '</span>';
			}
			?>
			<span id="signup-info"><?php echo lang_get( 'signup_info' ); ?></span>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'signup_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php html_page_bottom1a( __FILE__ );
