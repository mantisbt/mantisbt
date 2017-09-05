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
 * Lost Password Functionality
 *
 * @package MantisBT
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_css( 'login.css' );

# lost password feature disabled or reset password via email disabled -> stop here!
if( LDAP == config_get_global( 'login_method' ) ||
	OFF == config_get( 'lost_password_feature' ) ||
	OFF == config_get( 'send_reset_password' )  ||
	OFF == config_get( 'enable_email_notification' ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
}

$f_username = gpc_get_string( 'username', '' );
$t_username = auth_prepare_username( $f_username );

# Determine whether the username or password field should receive automatic focus.
$t_username_field_autofocus = 'autofocus';
$t_email_field_autofocus = '';
if( $t_username ) {
	$t_username_field_autofocus = '';
	$t_email_field_autofocus = 'autofocus';
}

# don't index lost password page
html_robots_noindex();

layout_login_page_begin();
?>

<div class="col-md-offset-3 col-md-6 col-sm-10 col-sm-offset-1">
<div id="lost-password-div" class="login-container">
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
			<i class="ace-icon fa fa-key"></i>
			<?php echo lang_get( 'lost_password_title' ) ?>
		</h4>
		<div class="space-10"></div>
	<form id="lost-password-form" method="post" action="lost_pwd.php">
		<fieldset>
			<?php
			echo form_security_field( 'lost_pwd' );

			$t_allow_passwd = auth_can_set_password();
			if( $t_allow_passwd ) { ?>
				<label for="username" class="block clearfix">
				<span class="block input-icon input-icon-right">
					<input id="username" name="username" type="text"
						placeholder="<?php echo lang_get( 'username' ) ?>"
                        value="<?php echo string_html_specialchars( $t_username ) ?>"
                        size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" class="form-control <?php echo $t_username_field_autofocus ?>">
					<i class="ace-icon fa fa-user"></i>
				</span>
				</label>
				<label for="email-field" class="block clearfix">
				<span class="block input-icon input-icon-right">
					<input id="email-field" name="email" type="text"
						   placeholder="<?php echo lang_get( 'email' ) ?>"
						   size="32" maxlength="64" class="form-control <?php echo $t_email_field_autofocus; ?>">
					<i class="ace-icon fa fa-envelope"></i>
				</span>
				</label>
				<div class="space-10"></div>
				<?php echo lang_get( 'lost_password_info' ); ?>
				<div class="space-10"></div>
				<input type="submit" class="width-40 pull-right btn btn-success btn-inverse bigger-110"  value="<?php echo lang_get( 'submit_button' ) ?>" />
			<?php
			} else {
				echo '<div class="space-10"></div>';
				echo '<div class="alert alert-danger">';
				echo auth_password_managed_elsewhere_message();
				echo '</div>';
			} ?>
		</fieldset>
	</form>
	</div>
		<div class="toolbar center">
			<a class="back-to-login-link pull-left" href="<?php echo AUTH_PAGE_USERNAME ?>"><?php echo lang_get( 'login_link' ); ?></a>
			<?php if( auth_signup_enabled() ) { ?>
			<a class="back-to-login-link pull-right" href="signup_page.php"><?php echo lang_get( 'signup_link' ); ?></a>
			<?php } ?>
			<div class="clearfix"></div>
		</div>
	</div>
	</div>
	</div>
</div>

<?php
layout_login_page_end();
