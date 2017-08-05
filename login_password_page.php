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
 * Login credential page asks user for password then posts to login.php page.
 * If an authentication plugin is installed and has its own credential page,
 * this page will re-direct to it.
 *
 * This page also offers features like remember me, secure session, and forgot password.
 *
 * @package MantisBT
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_css( 'login.css' );

$f_error                 = gpc_get_bool( 'error' );
$f_cookie_error          = gpc_get_bool( 'cookie_error' );
$f_return                = string_sanitize_url( gpc_get_string( 'return', '' ) );
$f_username              = gpc_get_string( 'username', '' );
$f_reauthenticate        = gpc_get_bool( 'reauthenticate', false );
$f_perm_login            = gpc_get_bool( 'perm_login', false );
$f_secure_session        = gpc_get_bool( 'secure_session', false );
$f_secure_session_cookie = gpc_get_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', null );

# Set username to blank if invalid to prevent possible XSS exploits
$t_username = auth_prepare_username( $f_username );

if( is_blank( $t_username ) ) {
	$t_query_args = array(
		'error' => 1,
		'return' => $f_return,
	);

	$t_query_text = http_build_query( $t_query_args, '', '&' );

	$t_redirect_url = auth_login_page( $t_query_text );
	print_header_redirect( $t_redirect_url );
}

# Get the user id and based on the user decide whether to continue with native password credential
# page or one provided by a plugin.
$t_user_id = auth_get_user_id_from_login_name( $t_username );
if( $t_user_id !== false && auth_credential_page( '', $t_user_id ) != AUTH_PAGE_CREDENTIAL ) {
	$t_query_args = array(
		'username' => $t_username,
        'cookie_error' => $f_cookie_error,
        'reauthenticate' => $f_reauthenticate,
	);

	if( !is_blank( $f_error ) ) {
		$t_query_args['error'] = $f_error;
    }

    if( !is_blank( $f_cookie_error ) ) {
		$t_query_args['cookie_error'] = $f_cookie_error;
    }

	$t_query_text = http_build_query( $t_query_args, '', '&' );

	$t_redirect_url = auth_credential_page( $t_query_text, $t_user_id );
	print_header_redirect( $t_redirect_url );
}

$t_session_validation = !$f_reauthenticate && ( ON == config_get_global( 'session_validation' ) );

$t_show_reset_password = !$f_reauthenticate &&
	( LDAP != config_get_global( 'login_method' ) ) &&
	( ON == config_get( 'lost_password_feature' ) ) &&
	( ON == config_get( 'send_reset_password' ) ) &&
	( ON == config_get( 'enable_email_notification' ) );

$t_show_remember_me = !$f_reauthenticate && auth_allow_perm_login( $t_user_id, $t_username );

$t_form_title = $f_reauthenticate ? lang_get( 'reauthenticate_title' ) : lang_get( 'login_title' );

# If user is already authenticated and not anonymous
if( auth_is_user_authenticated() && !current_user_is_anonymous() && !$f_reauthenticate) {
	# If return URL is specified redirect to it; otherwise use default page
	if( !is_blank( $f_return ) ) {
		print_header_redirect( $f_return, false, false, true );
	} else {
		print_header_redirect( config_get( 'default_home_page' ) );
	}
}

# Determine if secure_session should default on or off?
# - If no errors, and no cookies set, default to on.
# - If no errors, but cookie is set, use the cookie value.
# - If errors, use the value passed in.
if( $t_session_validation ) {
	if( !$f_error && !$f_cookie_error ) {
		$t_default_secure_session = is_null( $f_secure_session_cookie ) ? true : $f_secure_session_cookie;
	} else {
		$t_default_secure_session = $f_secure_session;
	}
}

# Login page shouldn't be indexed by search engines
html_robots_noindex();

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
<?php
if( $f_error || $f_cookie_error || $f_reauthenticate ) {
	echo '<div class="alert alert-danger">';

	if( $f_reauthenticate ) {
		echo '<p>' . lang_get( 'reauthenticate_message' ) . '</p>';
	}

	# Only echo error message if error variable is set
	if( $f_error ) {
		echo '<p>' . lang_get( 'login_error' ) . '</p>';
	}

	if( $f_cookie_error ) {
		echo '<p>' . lang_get( 'login_cookies_disabled' ) . '</p>';
	}

	echo '</div>';
}

$t_upgrade_required = false;
if( config_get_global( 'admin_checks' ) == ON && file_exists( dirname( __FILE__ ) .'/admin' ) ) {
	# since admin directory and db_upgrade lists are available check for missing db upgrades
	# if db version is 0, we do not have a valid database.
	$t_db_version = config_get( 'database_version', 0 );
	if( $t_db_version == 0 ) {
		$t_warnings[] = lang_get( 'error_database_no_schema_version' );
	}

	# Check for db upgrade for versions > 1.0.0 using new installer and schema
	require_once( 'admin' . DIRECTORY_SEPARATOR . 'schema.php' );
	$t_upgrades_reqd = count( $g_upgrade ) - 1;

	if( ( 0 < $t_db_version ) &&
			( $t_db_version != $t_upgrades_reqd ) ) {

		if( $t_db_version < $t_upgrades_reqd ) {
			$t_upgrade_required = true;
		}
	}
}
?>

<div class="position-relative">
	<div class="signup-box visible widget-box no-border" id="login-box">
		<div class="widget-body">
			<div class="widget-main">
				<h4 class="header lighter bigger">
					<i class="ace-icon fa fa-sign-in"></i>
					<?php echo $t_form_title ?>
				</h4>
				<div class="space-10"></div>
	<form id="login-form" method="post" action="login.php">
		<fieldset>

			<?php
			if( !is_blank( $f_return ) ) {
				echo '<input type="hidden" name="return" value="', string_html_specialchars( $f_return ), '" />';
			}

			if( $t_upgrade_required ) {
				echo '<input type="hidden" name="install" value="true" />';
			}

			echo '<input type="hidden" name="username" value="', string_html_specialchars( $t_username ), '" />';

			echo sprintf( lang_get( 'enter_password' ), string_html_specialchars( $t_username ) );

			# CSRF protection not required here - form does not result in modifications
			?>

			<label for="password" class="block clearfix">
				<span class="block input-icon input-icon-right">
					<input id="password" name="password" type="password" placeholder="<?php echo lang_get( 'password' ) ?>"
						   size="32" maxlength="<?php echo auth_get_password_max_size(); ?>"
						   class="form-control autofocus">
					<i class="ace-icon fa fa-lock"></i>
				</span>
			</label>

			<?php if( $t_show_remember_me ) { ?>
				<div class="clearfix">
					<label for="remember-login" class="inline">
						<input id="remember-login" type="checkbox" name="perm_login" class="ace" <?php echo ( $f_perm_login ? 'checked="checked" ' : '' ) ?> />
						<span class="lbl"> <?php echo lang_get( 'save_login' ) ?></span>
					</label>
				</div>
			<?php } ?>
			<?php if( $t_session_validation ) { ?>
				<div class="clearfix">
					<label for="secure-session" class="inline">
						<input id="secure-session" type="checkbox" name="secure_session" class="ace" <?php echo ( $t_default_secure_session ? 'checked="checked" ' : '' ) ?> />
						<span class="lbl"> <?php echo lang_get( 'secure_session_long' ) ?></span>
					</label>
				</div>
			<?php } ?>

			<?php if( $f_reauthenticate ) {
				echo '<input id="reauthenticate" type="hidden" name="reauthenticate" value="1" />';
			} ?>

			<div class="space-10"></div>

			<input type="submit" class="width-40 pull-right btn btn-success btn-inverse bigger-110" value="<?php echo lang_get( 'login_button' ) ?>" />
			<div class="clearfix"></div>
			<?php
			# lost password feature disabled or reset password via email disabled -> stop here!
			if( $t_show_reset_password ) {
				echo '<a class="pull-right" href="lost_pwd_page.php?username=', urlencode( $t_username ), '">', lang_get( 'lost_password_link' ), '</a>';
			}
			?>
		</fieldset>
	</form>
</div>
</div>
</div>
</div>
</div>
</div>

<?php
layout_login_page_end();
