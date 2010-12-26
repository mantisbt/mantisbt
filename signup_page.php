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
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

<br />
<div>
<form name="signup_form" method="post" action="signup.php">
<?php echo form_security_field( 'signup' ); ?>
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'signup_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<th class="category" width="30%">
		<?php echo lang_get( 'username_label' ) ?>
	</th>
	<td width="70%" colspan="2">
		<input type="text" name="username" size="32" maxlength="<?php echo USERLEN;?>" class="autofocus" />
	</td>
</tr>
<tr class="row-2">
	<th class="category">
		<?php echo lang_get( 'email_label' ) ?>
	</th>
	<td colspan="2">
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<?php
	$t_allow_passwd = helper_call_custom_function( 'auth_can_change_password', array() );
	if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 && ( true == $t_allow_passwd ) ) {
		# captcha image requires GD library and related option to ON
?>
<tr class="row-1">
	<th class="category">
		<?php echo lang_get( 'signup_captcha_request_label' ) ?>
	</th>
	<td>
		<?php print_captcha_input( 'captcha', '' ) ?>
	</td>
	<td>
		<img src="make_captcha_img.php?public_key=<?php echo $t_public_key ?>" alt="visual captcha" />
		<input type="hidden" name="public_key" value="<?php echo $t_public_key ?>" />
	</td>
</tr>
<?php
	}
	if( false == $t_allow_passwd ) {
?>
<tr class="row-1">
	<td class="category">
	</td>
	<td colspan="2">
		<?php echo lang_get( 'no_password_request' ) ?>
	</td>
</tr>
<?php
	}
?>
<tr>
	<td colspan="3">
		<br/>
		<?php echo lang_get( 'signup_info' ) ?>
		<br/><br/>
	</td>
</tr>
<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'signup_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
echo '<br /><div>';
print_login_link();
echo '&#160;';
print_lost_password_link();
echo '</div>';

html_page_bottom1a( __FILE__ );
