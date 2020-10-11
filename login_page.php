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
 * Login page accepts username and posts results to login_password_page.php,
 * which may take the users credential or redirect to a plugin specific page.
 *
 * This page also offers features like anonymous login and signup.
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
$f_secure_session        = gpc_get_bool( 'secure_session', false );
$f_secure_session_cookie = gpc_get_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', null );

# Set username to blank if invalid to prevent possible XSS exploits
$t_username = auth_prepare_username( $f_username );

if( config_get_global( 'email_login_enabled' ) ) {
	$t_username_label = lang_get( 'username_or_email' );
} else {
	$t_username_label = lang_get( 'username' );
}

$t_show_signup =
	( auth_signup_enabled() ) &&
	( LDAP != config_get_global( 'login_method' ) ) &&
	( ON == config_get( 'enable_email_notification' ) );

$t_show_anonymous_login = auth_anonymous_enabled();

$t_form_title = lang_get( 'login_title' );

# If user is already authenticated and not anonymous
if( auth_is_user_authenticated() && !current_user_is_anonymous() ) {
	# If return URL is specified redirect to it; otherwise use default page
	if( !is_blank( $f_return ) ) {
		print_header_redirect( $f_return, false, false, true );
	} else {
		print_header_redirect( config_get_global( 'default_home_page' ) );
	}
}

# Check for automatic logon methods where we want the logon to just be handled by login.php
if( auth_automatic_logon_bypass_form() ) {
	$t_uri = 'login.php';

	if( auth_anonymous_enabled() ) {
		$t_uri = 'login_anon.php';
	}

	if( !is_blank( $f_return ) ) {
		$t_uri .= '?return=' . string_url( $f_return );
	}

	print_header_redirect( $t_uri );
	exit;
}

# Login page shouldn't be indexed by search engines
html_robots_noindex();

layout_login_page_begin();
?>

<div class="col-md-offset-3 col-md-6 col-sm-10 col-sm-offset-1">
	<div class="login-container">
		<div class="space-12 hidden-480"></div>
		<?php layout_login_page_logo() ?>
		<div class="space-24 hidden-480"></div>
<?php
if( $f_error || $f_cookie_error ) {
	echo '<div class="alert alert-danger">';

	# Only echo error message if error variable is set
	if( $f_error ) {
		echo '<p>' . lang_get( 'login_error' ) . '</p>';
	}

	if( $f_cookie_error ) {
		echo '<p>' . lang_get( 'login_cookies_disabled' ) . '</p>';
	}

	echo '</div>';
}

$t_warnings = array();
$t_upgrade_required = false;

if( config_get_global( 'admin_checks' ) == ON ) {
	# Check if the admin directory is accessible
	$t_admin_dir = dirname( __FILE__ ) . '/admin';
	$t_admin_dir_is_accessible = @file_exists( $t_admin_dir . '/.' );
	if( $t_admin_dir_is_accessible ) {
		$t_warnings[] = lang_get( 'warning_admin_directory_present' );
	}

	# Generate a warning if default user administrator/root is valid.
	$t_admin_user_id = user_get_id_by_name( 'administrator' );
	if( $t_admin_user_id !== false ) {
		if( user_is_enabled( $t_admin_user_id ) && auth_does_password_match( $t_admin_user_id, 'root' ) ) {
			$t_warnings[] = lang_get( 'warning_default_administrator_account_present' );
		}
	}

	/**
	 * Display Warnings for enabled debugging / developer settings
	 * @param string $p_type    Message Type.
	 * @param string $p_setting Setting.
	 * @param string $p_value   Value.
	 * @return string
	 */
	function debug_setting_message ( $p_type, $p_setting, $p_value ) {
		return sprintf( lang_get( 'warning_change_setting' ), $p_setting, $p_value )
			. sprintf( lang_get( 'word_separator' ) )
			. sprintf( lang_get( "warning_${p_type}_hazard" ) );
	}

	$t_config = 'show_detailed_errors';
	if( config_get_global( $t_config ) != OFF ) {
		$t_warnings[] = debug_setting_message( 'security', $t_config, 'OFF' );
	}

	# since admin directory and db_upgrade lists are available check for missing db upgrades
	# if db version is 0, we do not have a valid database.
	$t_db_version = config_get( 'database_version', 0, ALL_USERS, ALL_PROJECTS );
	if( $t_db_version == 0 ) {
		$t_warnings[] = lang_get( 'error_database_no_schema_version' );
	}

	# Check for db upgrade for versions > 1.0.0 using new installer and schema
	if( $t_admin_dir_is_accessible ) {
		require_once( 'admin/schema.php' );
		$t_upgrades_reqd = count( $g_upgrade ) - 1;

		if( ( 0 < $t_db_version ) &&
			( $t_db_version != $t_upgrades_reqd ) ) {

			if( $t_db_version < $t_upgrades_reqd ) {
				$t_warnings[] = lang_get( 'error_database_version_out_of_date_2'
				);
				$t_upgrade_required = true;
			}
			else {
				$t_warnings[] = lang_get( 'error_code_version_out_of_date' );
			}
		}
	}
}
?>

<div class="position-relative">
	<div class="signup-box visible widget-box no-border" id="login-box">
		<div class="widget-body">
			<div class="widget-main">
				<h4 class="header lighter bigger">
					<?php print_icon( 'fa-sign-in', 'ace-icon' ); ?>
					<?php echo $t_form_title ?>
				</h4>
				<div class="space-10"></div>
	<form id="login-form" method="post" action="<?php echo AUTH_PAGE_CREDENTIAL ?>">
		<fieldset>

			<?php
			if( !is_blank( $f_return ) ) {
				echo '<input type="hidden" name="return" value="', string_html_specialchars( $f_return ), '" />';
			}

			if( $t_upgrade_required ) {
				echo '<input type="hidden" name="install" value="true" />';
			}

			# CSRF protection not required here - form does not result in modifications
			?>

			<label for="username" class="block clearfix">
				<span class="block input-icon input-icon-right">
					<input id="username" name="username" type="text" placeholder="<?php echo $t_username_label ?>"
						   size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" value="<?php echo string_attribute( $t_username ); ?>"
						   class="form-control autofocus">
					<?php print_icon( 'fa-user', 'ace-icon' ); ?>
				</span>
			</label>

			<div class="space-10"></div>

			<input type="submit" class="width-40 pull-right btn btn-success btn-inverse bigger-110" value="<?php echo lang_get( 'login' ) ?>" />
		</fieldset>
	</form>

<?php
#
# Do some checks to warn administrators of possible security holes.
#

if( count( $t_warnings ) > 0 ) {
	echo '<div class="space-10"></div>';
	echo '<div class="alert alert-warning">';
	foreach( $t_warnings AS $t_warning ) {
		echo '<p>' . $t_warning . '</p>';
	}
	echo '</div>';
}
?>
</div>

<?php
if( $t_show_anonymous_login || $t_show_signup ) {
	echo '<div class="toolbar center">';

	if( $t_show_anonymous_login ) {
		echo '<a class="back-to-login-link pull-right" href="login_anon.php?return=' . string_url( $f_return ) . '">' . lang_get( 'login_anonymously' ) . '</a>';
	}

	if( $t_show_signup ) {
		echo '<a class="back-to-login-link pull-left" href="signup_page.php">', lang_get( 'signup_link' ), '</a>';
	}

	echo '<div class="clearfix"></div>';
	echo '</div>';
}
?>

		</div>
</div>
</div>
</div>
</div>

<?php
layout_login_page_end();
