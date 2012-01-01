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
 * Login page POSTs results to login.php
 * Check to see if the user is already logged in
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

/**
 * MantisBT Core API's
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

$f_error		 = gpc_get_bool( 'error' );
$f_cookie_error		 = gpc_get_bool( 'cookie_error' );
$f_return		 = string_sanitize_url( gpc_get_string( 'return', '' ) );
$f_username		 = gpc_get_string( 'username', '' );
$f_perm_login		 = gpc_get_bool( 'perm_login', false );
$f_secure_session	 = gpc_get_bool( 'secure_session', false );
$f_secure_session_cookie = gpc_get_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', null );

$t_session_validation = ( ON == config_get_global( 'session_validation' ) );

// If user is already authenticated and not anonymous
if( auth_is_user_authenticated() && !current_user_is_anonymous() ) {
	// If return URL is specified redirect to it; otherwise use default page
	if( !is_blank( $f_return ) ) {
		print_header_redirect( $f_return, false, false, true );
	}
	else {
		print_header_redirect( config_get( 'default_home_page' ) );
	}
}

# Check for automatic logon methods where we want the logon to just be handled by login.php
if ( auth_automatic_logon_bypass_form() ) {
	$t_uri = "login.php";

	if ( ON == config_get( 'allow_anonymous_login' ) ) {
		$t_uri = "login_anon.php";
	}

	if ( !is_blank( $f_return ) ) {
		$t_uri .= "?return=" . string_url( $f_return );
	}

	print_header_redirect( $t_uri );
	exit;
}

# Determine if secure_session should default on or off?
# - If no errors, and no cookies set, default to on.
# - If no errors, but cookie is set, use the cookie value.
# - If errors, use the value passed in.
if ( $t_session_validation ) {
	if ( !$f_error && !$f_cookie_error ) {
		$t_default_secure_session = ( is_null( $f_secure_session_cookie ) ? true : $f_secure_session_cookie );
	} else {
		$t_default_secure_session = $f_secure_session;
	}
}

# Determine whether the username or password field should receive automatic focus.
$t_username_field_autofocus = 'autofocus';
$t_password_field_autofocus = '';
if ( $f_username ) {
	$t_username_field_autofocus = '';
	$t_password_field_autofocus = 'autofocus';
}

# Login page shouldn't be indexed by search engines
html_robots_noindex();

html_page_top1();
html_page_top2a();

if( $f_error || $f_cookie_error ) {
	echo '<div class="important-msg">';
	echo '<ul>';

	# Display short greeting message
	# echo lang_get( 'login_page_info' ) . '<br />';

	# Only echo error message if error variable is set
	if ( $f_error ) {
		echo '<li>' . lang_get( 'login_error' ) . '</li>';
	}
	if ( $f_cookie_error ) {
		echo '<li>' . lang_get( 'login_cookies_disabled' ) . '</li>';
	}
	echo '</ul>';
	echo '</div>';
}
?>

<!-- Login Form BEGIN -->
<div id="login-div" class="form-container">
	<form id="login-form" method="post" action="login.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'login_title' ) ?></span></legend>
			<?php
			if ( !is_blank( $f_return ) ) {
				echo '<input type="hidden" name="return" value="', string_html_specialchars( $f_return ), '" />';
			}
			# CSRF protection not required here - form does not result in modifications
			echo '<ul id="login-links">';

			if ( ON == config_get( 'allow_anonymous_login' ) ) {
				echo '<li><a href="login_anon.php?return=' . string_url( $f_return ) . '">' . lang_get( 'login_anonymously' ) . '</a></li>';
			}

			if ( ( ON == config_get_global( 'allow_signup' ) ) &&
				( LDAP != config_get_global( 'login_method' ) ) &&
				( ON == config_get( 'enable_email_notification' ) )
			) {
				echo '<li><a href="signup_page.php">', lang_get( 'signup_link' ), '</a></li>';
			}
			# lost password feature disabled or reset password via email disabled -> stop here!
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
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" value="<?php echo string_attribute( $f_username ); ?>" class="<?php echo $t_username_field_autofocus ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="password"><span><?php echo lang_get( 'password' ) ?></span></label>
				<span class="input"><input id="password" type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" class="<?php echo $t_password_field_autofocus ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="remember-login"><span><?php echo lang_get( 'save_login' ) ?></span></label>
				<span class="input"><input id="remember-login" type="checkbox" name="perm_login" <?php echo ( $f_perm_login ? 'checked="checked" ' : '' ) ?>/></span>
				<span class="label-style"></span>
			</div>
			<?php if ( $t_session_validation ) { ?>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label id="secure-session-label" for="secure-session"><span><?php echo lang_get( 'secure_session' ) ?></span></label>
				<span class="input">
					<input id="secure-session" type="checkbox" name="secure_session" <?php echo ( $t_default_secure_session ? 'checked="checked" ' : '' ) ?>/>
					<span id="session-msg"><?php echo lang_get( 'secure_session_long' ); ?></span>
				</span>
				<span class="label-style"></span>
			</div>
			<?php } ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'login_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
#
# Do some checks to warn administrators of possible security holes.
# Since this is considered part of the admin-checks, the strings are not translated.
#

if ( config_get_global( 'admin_checks' ) == ON ) {
	$t_warnings = array();

	# Generate a warning if administrator/root is valid.
	$t_admin_user_id = user_get_id_by_name( 'administrator' );
	if ( $t_admin_user_id !== false ) {
		if ( user_is_enabled( $t_admin_user_id ) && auth_does_password_match( $t_admin_user_id, 'root' ) ) {
			$t_warnings[] = lang_get( 'warning_default_administrator_account_present' );
		}
	}

	# Check if the admin directory is available and is readable.
	$t_admin_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
	if ( is_dir( $t_admin_dir ) ) {
		$t_warnings[] = lang_get( 'warning_admin_directory_present' );
	}
	if ( is_dir( $t_admin_dir ) && is_readable( $t_admin_dir ) && is_executable( $t_admin_dir ) && @file_exists( "$t_admin_dir/." ) ) {
		# since admin directory and db_upgrade lists are available check for missing db upgrades
		# Check for db upgrade for versions < 1.0.0 using old upgrader
		$t_db_version = config_get( 'database_version' , 0 );
		# if db version is 0, we haven't moved to new installer.
		if ( $t_db_version == 0 ) {
			$t_upgrade_count = 0;
			if ( db_table_exists( db_get_table( 'upgrade' ) ) ) {
				$query = "SELECT COUNT(*) from " . db_get_table( 'upgrade' ) . ";";
				$result = db_query_bound( $query );
				if ( db_num_rows( $result ) > 0 ) {
					$t_upgrade_count = (int)db_result( $result );
				}
			}

			if ( $t_upgrade_count > 0 ) { # table exists, check for number of updates

				# new config table database version is 0.
				# old upgrade tables exist.
				# assume user is upgrading from <1.0 and therefore needs to update to 1.x before upgrading to 1.2
				$t_warnings[] = lang_get( 'error_database_version_out_of_date_1' );
			} else {
				# old upgrade tables do not exist, yet config database_version is 0
				$t_warnings[] = lang_get( 'error_database_no_schema_version' );
			}
		}

		# Check for db upgrade for versions > 1.0.0 using new installer and schema
		require_once( 'admin' . DIRECTORY_SEPARATOR . 'schema.php' );
		$t_upgrades_reqd = count( $upgrade ) - 1;

		if ( ( 0 < $t_db_version ) &&
				( $t_db_version != $t_upgrades_reqd ) ) {

			if ( $t_db_version < $t_upgrades_reqd ) {
				$t_warnings[] = lang_get( 'error_database_version_out_of_date_2' );
			} else {
				$t_warnings[] = lang_get( 'error_code_version_out_of_date' );
			}
		}
	}
	if( count( $t_warnings ) > 0 ) {
		echo '<div class="important-msg">';
		echo '<ul>';
		foreach( $t_warnings AS $t_warning ) {
			echo '<li>' . $t_warning . '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
} # if 'admin_checks'

html_page_bottom1a( __FILE__ );
