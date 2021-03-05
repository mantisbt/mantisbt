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
 * Authentication API
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses ldap_api.php
 * @uses print_api.php
 * @uses session_api.php
 * @uses string_api.php
 * @uses tokens_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

use Mantis\Exceptions\ClientException;

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'crypto_api.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'ldap_api.php' );
require_api( 'print_api.php' );
require_api( 'session_api.php' );
require_api( 'string_api.php' );
require_api( 'tokens_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

# @global array $g_script_login_cookie
$g_script_login_cookie = null;

# @global array $g_cache_anonymous_user_cookie_string
$g_cache_anonymous_user_cookie_string = null;

# @global array $g_cache_cookie_valid
$g_cache_cookie_valid = null;

# @global int $g_cache_current_user_id
$g_cache_current_user_id = NO_USER;

/**
 * Gets set of flags for authentication for the specified user.
 * @param int|null|bool $p_user_id  The user id or null for logged in user or
 *                                  NO_USER/false for user that doesn't exist
 *                                  in the system, that may be auto-provisioned.
 * @param string        $p_username The username or email
 * @return AuthFlags The auth flags object to use.
 * @throws ClientException
 */
function auth_flags( $p_user_id = null, $p_username = '' ) {
	if( !$p_user_id ) {
		# If user id is not provided and user is not authenticated return default flags.
		# Otherwise, we can get into a loop as in #22740
		if( !auth_is_user_authenticated() ) {
			return new AuthFlags();
		}

		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = (int)$p_user_id;
	}

	if( !$t_user_id && is_blank( $p_username ) ) {
		# If user is not in db, must supply the name.
		trigger_error( ERROR_GENERIC, ERROR );
	}

	if( $t_user_id ) {
		$t_username = user_get_username( $t_user_id );
		$t_email = user_get_email( $t_user_id );
	} else {
		$t_username = $p_username;

		# If the plugin cares about email, then it can check if user typed the email address
		# as the username.
		$t_email = '';
	}

	$t_event_arguments = array(
		'user_id' => $t_user_id,
		'username' => $t_username,
		'email' => $t_email,
	);

	static $s_flags_cache = array();
	if( !isset( $s_flags_cache[$t_user_id] ) ) {
		$t_flags = event_signal( 'EVENT_AUTH_USER_FLAGS', array( $t_event_arguments ) );

		# Don't cache in case of user not in db.
		if( $t_user_id ) {
			$s_flags_cache[$t_user_id] = $t_flags;
		}
	} else {
		$t_flags = $s_flags_cache[$t_user_id];
	}

	if( is_null( $t_flags ) ) {
		$t_flags = new AuthFlags();
	}

	return $t_flags;
}

/**
 * The message to show to indicate to user that password is managed elsewhere.
 * @return string The message.
 * @throws ClientException
 */
function auth_password_managed_elsewhere_message() {
	$t_auth_flags = auth_flags();
	return $t_auth_flags->getPasswordManagedExternallyMessage();
}

/**
 * Check if permanent login is enabled.
 * @param int|bool $p_user_id  The user id, or NO_USER/false for unknown user.
 * @param string   $p_username The username user typed in sign-in form.
 * @return boolean true: yes, false: otherwise.
 * @throws ClientException
 */
function auth_allow_perm_login( $p_user_id, $p_username ) {
	$t_auth_flags = auth_flags( $p_user_id, $p_username );
	return $t_auth_flags->getPermSessionEnabled();
}

/**
 * Check if signup is enabled.
 * @return bool true: enabled, false otherwise.
 */
function auth_signup_enabled() {
	return config_get_global( 'allow_signup' );
}

/**
 * Get the access level for users that signup.
 * @return integer The access level to use.
 */
function auth_signup_access_level() {
	return config_get( 'default_new_account_access_level' );
}

/**
 * Anonymous login enabled.
 * @return bool true: enabled; false: otherwise.
 */
function auth_anonymous_enabled() {
	return config_get_global( 'allow_anonymous_login' ) && auth_anonymous_account();
}

/**
 * Get the anonymous account username.
 * @return string Anonymous account username.
 */
function auth_anonymous_account() {
	return config_get_global( 'anonymous_account' );
}

/**
 * Get the auth cookie expiry time.
 * @param integer $p_user_id    The user id to get session expiry for.
 * @param boolean $p_perm_login Use permanent login.
 * @return integer cookie lifetime or 0 for browser session.
 * @throws ClientException
 */
function auth_session_expiry( $p_user_id, $p_perm_login ) {
	$t_auth_flags = auth_flags( $p_user_id );
	$t_perm_login = $p_perm_login;
	if( !$t_auth_flags->getPermSessionEnabled() ) {
		$t_perm_login = false;
	}

	if( $t_perm_login ) {
		$t_lifetime = $t_auth_flags->getPermSessionLifetime();
	} else {
		$t_lifetime = $t_auth_flags->getSessionLifetime();
	}

	return $t_lifetime == 0 ? 0 : time() + $t_lifetime;
}

/**
 * Gets the login page to redirect to when login is needed, it will return a
 * relative url.
 * @param string $p_query_string query string parameters.
 * @return string login page (e.g. 'login_page.php' )
 * @throws ClientException
 */
function auth_login_page( $p_query_string = '' ) {
	$t_auth_flags = auth_flags();
	$t_login_page = $t_auth_flags->getLoginPage();

	return helper_url_combine( $t_login_page, $p_query_string );
}

/**
 * Gets the page that asks the user for credentials based on the user's
 * authentication model.
 *
 * @param string   $p_query_string The query string, can be empty.
 * @param int|null $p_user_id      The user id or null for current logged in user.
 * @param string   $p_username     The username
 * @return string The credentials page with query string.
 * @throws ClientException
 */
function auth_credential_page( $p_query_string, $p_user_id = null, $p_username = '' ) {
	$t_auth_flags = auth_flags( $p_user_id, $p_username );
	return $t_auth_flags->getCredentialsPage( $p_query_string );
}

/**
 * Gets the logout page to redirect to for logging out the user, it will return
 * a relative url
 * @return string logout page (e.g. 'logout_page.php' )
 * @throws ClientException
 */
function auth_logout_page() {
	$t_auth_flags = auth_flags();
	return $t_auth_flags->getLogoutPage();
}

/**
 * Gets the page to redirect to after logout.
 * @return string the logout redirect page.
 * @throws ClientException
 */
function auth_logout_redirect_page() {
	$t_auth_flags = auth_flags();
	return $t_auth_flags->getLogoutRedirectPage();
}

/**
 * Checks if specified user can set their own password.
 * @param integer|null $p_user_id The user id or null for logged in user or 0 for signup scenarios.
 * @return bool true: can set password, false: otherwise.
 * @throws ClientException
 */
function auth_can_set_password( $p_user_id = null ) {
	$t_auth_flags = auth_flags( $p_user_id );

	if( !$t_auth_flags->getCanUseStandardLogin() ) {
		return false;
	}

	return helper_call_custom_function( 'auth_can_change_password', array() );
}

/**
 * Checks if specified user can use standard login (e.g. username and password).
 * @param integer|null $p_user_id The user id or null for logged in user.
 * @return bool true: can login using username and password, false otherwise.
 * @throws ClientException
 */
function auth_can_use_standard_login( $p_user_id = null ) {
	$t_auth_flags = auth_flags( $p_user_id );
	return $t_auth_flags->getCanUseStandardLogin();
}

/**
 * Check that there is a user logged-in and authenticated
 * If the user's account is disabled they will be logged out
 * If there is no user logged in, redirect to the login page
 * If parameter is given it is used as a URL to redirect to following
 * successful login.  If none is given, the URL of the current page is used
 * @param string $p_return_page Page to redirect to following successful logon, defaults to current page.
 * @access public
 * @return void
 * @throws ClientException
 */
function auth_ensure_user_authenticated( $p_return_page = '' ) {
	# if logged in
	if( auth_is_user_authenticated() ) {
		# check for access enabled
		#  This also makes sure the cookie is valid
		if( OFF == current_user_get_field( 'enabled' ) ) {
			print_header_redirect( auth_logout_page() );
		}
	} else {
		# not logged in
		if( is_blank( $p_return_page ) ) {
			if( !isset( $_SERVER['REQUEST_URI'] ) ) {
				$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
			}
			$p_return_page = $_SERVER['REQUEST_URI'];
		}
		$p_return_page = string_url( $p_return_page );
		print_header_redirect( auth_login_page( 'return=' . $p_return_page ) );
	}
}

/**
 * Return true if there is a currently logged in and authenticated user, false otherwise
 *
 * @return boolean
 * @access public
 * @throws ClientException
 */
function auth_is_user_authenticated() {
	global $g_cache_cookie_valid, $g_login_anonymous;
	if( $g_cache_cookie_valid == true ) {
		return $g_cache_cookie_valid;
	}
	$g_cache_cookie_valid = auth_is_cookie_valid( auth_get_current_user_cookie( $g_login_anonymous ) );
	return $g_cache_cookie_valid;
}

/**
 * prepare/override the username provided from logon form (if necessary)
 * @todo when we rewrite authentication api for plugins, this should be merged with prepare_password and return some object
 * @param string $p_username Username.
 * @return string|null prepared username
 * @access public
 */
function auth_prepare_username( $p_username ) {
	$t_username = null;

	switch( config_get_global( 'login_method' ) ) {
		case BASIC_AUTH:
			if( isset( $_SERVER['REMOTE_USER'] ) ) {
				$t_username = $_SERVER['REMOTE_USER'];
			}
			break;
		case HTTP_AUTH:
			if( !auth_http_is_logout_pending() ) {
				if( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
					$t_username = $_SERVER['PHP_AUTH_USER'];
				}
			} else {
				auth_http_set_logout_pending( false );
				auth_http_prompt();
			}
			break;
		default:
			$t_username = $p_username;
			break;
	}

	if( !is_null( $t_username ) ) {
		$t_username = user_is_name_valid( $t_username ) ? $t_username : null;
	}

	return $t_username;
}

/**
 * prepare/override the password provided from logon form (if necessary)
 * @todo when we rewrite authentication api for plugins, this should be merged with prepare_username and return some object
 * @param string $p_password Password.
 * @return string prepared password
 * @access public
 */
function auth_prepare_password( $p_password ) {
	$f_password = $p_password;
	switch( config_get_global( 'login_method' ) ) {
		case BASIC_AUTH:
			$f_password = $_SERVER['PHP_AUTH_PW'];
			break;
		case HTTP_AUTH:
			if( !auth_http_is_logout_pending() ) {

				# this will never get hit - see auth_prepare_username
				if( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
					$f_password = $_SERVER['PHP_AUTH_PW'];
				}
			} else {
				auth_http_set_logout_pending( false );
				auth_http_prompt();

				# calls exit
				return null;
			}
			break;
	}
	return $f_password;
}

/**
 * In the case where a user is attempting to authenticate but doesn't exist.
 * Check if the authentication provider supports auto-creation of users and
 * whether the password matches.
 *
 * @param string $p_username A prepared username.
 * @param string $p_password A prepared password.
 * @return int|boolean user id or false in case of failure.
 * @access private
 *
 * @throws ClientException
 */
function auth_auto_create_user( $p_username, $p_password ) {
	$t_login_method = config_get_global( 'login_method' );

	if( $t_login_method == BASIC_AUTH ) {
		$t_auto_create = true;
	} else if( $t_login_method == LDAP && ldap_authenticate_by_username( $p_username, $p_password ) ) {
		$t_auto_create = true;
	} else {
		$t_auto_create = false;
	}

	if( $t_auto_create ) {
		# attempt to create the user
		$t_cookie_string = user_create( $p_username, md5( $p_password ) );
		if( $t_cookie_string === false ) {
			# it didn't work
			return false;
		}

		# ok, we created the user, get the row again
		return user_get_id_by_name( $p_username );
	}

	return false;
}

/**
 * Given a login username provided by the user via the web UI or our API,
 * get the user id.  The login username can be a username or an email address.
 * The email address will work as long there is a single enabled account with
 * such address and it is not blank.
 *
 * @param string $p_login_name The login name.
 * @return integer|boolean user id or false.
 *
 * @throws ClientException
 */
function auth_get_user_id_from_login_name( $p_login_name ) {
	$t_user_id = user_get_id_by_name( $p_login_name );

	# If user is not found by name, check by email as long as there is only
	# a single match.
	if( $t_user_id === false
		&& !is_blank( $p_login_name )
		&& config_get_global( 'email_login_enabled' )
		&& email_is_valid( $p_login_name )
	) {
		$t_user_ids_by_email = user_get_enabled_ids_by_email( $p_login_name );
		if ( count( $t_user_ids_by_email ) == 1 ) {
			$t_user_id = $t_user_ids_by_email[0];
		}
	}

	return $t_user_id;
}

/**
 * Attempt to login the user with the given password
 * If the user fails validation, false is returned
 * If the user passes validation, the cookies are set and
 * true is returned.  If $p_perm_login is true, the long-term
 * cookie is created.
 * @param string  $p_username   A prepared username.
 * @param string  $p_password   A prepared password.
 * @param boolean $p_perm_login Whether to create a long-term cookie.
 * @return boolean indicates if authentication was successful
 * @access public
 *
 * @throws ClientException
 */
function auth_attempt_login( $p_username, $p_password, $p_perm_login = false ) {
	$t_user_id = auth_get_user_id_from_login_name( $p_username );

	if( $t_user_id === false ) {
		$t_user_id = auth_auto_create_user( $p_username, $p_password );
		if( $t_user_id === false ) {
			return false;
		}
	}

	# max. failed login attempts achieved...
	if( !user_is_login_request_allowed( $t_user_id ) ) {
		return false;
	}

	# check for anonymous login
	if( !user_is_anonymous( $t_user_id ) ) {
		# anonymous login didn't work, so check the password
		if( !auth_does_password_match( $t_user_id, $p_password ) ) {
			user_increment_failed_login_count( $t_user_id );
			return false;
		}
	}

	return auth_login_user( $t_user_id, $p_perm_login );
}

/**
 * Login the user with the specified id, if enabled.
 *
 * This is typically used by auth plugins.
 *
 * @param integer $p_user_id    The user id.
 * @param boolean $p_perm_login Whether to create a long-term cookie.
 * @return bool true: success; false; otherwise.
 * @throws ClientException
 */
function auth_login_user( $p_user_id, $p_perm_login = false ) {
	# check for disabled account
	if( !user_is_enabled( $p_user_id ) ) {
		return false;
	}

	# ok, we're good to login now
	# increment login count
	user_increment_login_count( $p_user_id );

	user_reset_failed_login_count_to_zero( $p_user_id );
	user_reset_lost_password_in_progress_count_to_zero( $p_user_id );

	# set the cookies
	auth_set_cookies( $p_user_id, $p_perm_login );
	auth_set_tokens( $p_user_id );

	return true;
}

/**
 * Impersonates the specified user by logging in.
 *
 * @param int $p_user_id The user id.
 * @return void
 * @throws ClientException
 */
function auth_impersonate( $p_user_id ) {
	auth_ensure_can_impersonate( $p_user_id );

	auth_set_cookies( $p_user_id );
	auth_set_tokens( $p_user_id );
}

/**
 * Check whether the logged in user can impersonate the specified user.
 *
 * @param int $p_user_id The user id to be impersonated.
 * @return bool true: can impersonate, false: can't.
 * @throws ClientException
 */
function auth_can_impersonate( $p_user_id ) {
	if( !access_has_global_level( config_get_global( 'impersonate_user_threshold' ) ) ) {
		return false;
	}

	# User can't impersonate themselves
	if( $p_user_id == auth_get_current_user_id() ) {
		return false;
	}

	if( !user_is_enabled( $p_user_id ) ) {
		return false;
	}

	return true;
}

/**
 * Ensure that the logged in user can impersonate the specified user.  If not,
 * then an error page will be generated.
 *
 * @param int $p_user_id The user id to be impersonated.
 * @return void.
 * @throws ClientException
 */
function auth_ensure_can_impersonate( $p_user_id ) {
	if( !auth_can_impersonate( $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Allows scripts to login using a login name or ( login name + password )
 *
 * There are multiple scenarios where this is used:
 * - Anonymous login (blank username supplied).
 * - Anonymous login with anonymous user name specified.
 * - Anonymous login with account not existing or disabled.
 * - Pre-authenticated user via some secret hash from email verify or rss feed,
 *   where username is specified but password is null.
 * - Standard authentication with username and password specified.
 *
 * @param string $p_username Username.
 * @param string $p_password Password.
 * @return boolean indicates if authentication was successful
 * @access public
 *
 * @throws ClientException
 */
function auth_attempt_script_login( $p_username, $p_password = null ) {
	global $g_script_login_cookie;

	$t_username = $p_username;
	$t_password = $p_password;

	$t_anon_allowed = auth_anonymous_enabled();
	if( $t_anon_allowed == ON ) {
		$t_anonymous_account = auth_anonymous_account();
	} else {
		$t_anonymous_account = '';
	}

	# if no user name supplied, then attempt to login as anonymous user.
	if( is_blank( $t_username ) || ( strcasecmp( $t_username, $t_anonymous_account ) == 0 ) ) {
		if( $t_anon_allowed == OFF ) {
			return false;
		}

		$t_username = $t_anonymous_account;

		# do not use password validation.
		$t_password = null;
	}

	$t_user_id = auth_get_user_id_from_login_name( $t_username );
	if( $t_user_id === false ) {
		$t_user_id = auth_auto_create_user( $t_username, $p_password );
		if( $t_user_id === false ) {
			return false;
		}
	}

	$t_user = user_get_row( $t_user_id );

	# check for disabled account
	if( OFF == $t_user['enabled'] ) {
		return false;
	}

	# validate password if supplied
	if( null !== $t_password ) {
		if( !auth_does_password_match( $t_user_id, $t_password ) ) {
			return false;
		}
	}

	# ok, we're good to login now
	# With cases like RSS feeds and MantisConnect there is a login per operation, hence, there is no
	# real significance of incrementing login count.
	# increment login count
	# user_increment_login_count( $t_user_id );
	# set the cookies
	$g_script_login_cookie = $t_user['cookie_string'];

	# cache user id for future reference
	current_user_set( $t_user_id );

	return true;
}

/**
 * Logout the current user and remove any remaining cookies from their browser
 * Returns true on success, false otherwise
 * @access public
 * @return void
 * @throws ClientException
 */
function auth_logout() {
	global $g_cache_current_user_id, $g_cache_cookie_valid;

	if( !user_is_protected( $g_cache_current_user_id ) ) {
		# Reset the user's cookie string
		user_set_field(
			$g_cache_current_user_id,
			'cookie_string',
			auth_generate_unique_cookie_string()
		);
	}

	# clear cached userid
	user_clear_cache( $g_cache_current_user_id );
	current_user_set( null );
	$g_cache_cookie_valid = null;

	# clear cookies, if they were set
	if( auth_clear_cookies() ) {
		helper_clear_pref_cookies();
	}

	if( HTTP_AUTH == config_get_global( 'login_method' ) ) {
		auth_http_set_logout_pending( true );
	}

	session_clean();
}

/**
 * Indicates whether to bypass logon form e.g. when using http authentication
 * @return boolean true: bypass, false: show form.
 * @access public
 */
function auth_automatic_logon_bypass_form() {
	return config_get_global( 'login_method' ) == HTTP_AUTH;
}

/**
 * Return the user's password maximum length for the current login method
 *
 * @return integer
 * @access public
 */
function auth_get_password_max_size() {
	switch( config_get_global( 'login_method' ) ) {
		# Max password size cannot be bigger than the database field
		case PLAIN:
		case BASIC_AUTH:
		case HTTP_AUTH:
			return DB_FIELD_SIZE_PASSWORD;

		# All other cases, i.e. password is stored as a hash
		default:
			return PASSWORD_MAX_SIZE_BEFORE_HASH;
	}
}

/**
 * Return true if the password for the user id given matches the given
 * password (taking into account the global login method)
 * @param integer $p_user_id       User id to check password against.
 * @param string  $p_test_password Password.
 * @return boolean indicating whether password matches given the user id
 * @access public
 * @throws ClientException
 */
function auth_does_password_match( $p_user_id, $p_test_password ) {
	$t_configured_login_method = config_get_global( 'login_method' );

	if( LDAP == $t_configured_login_method ) {
		return ldap_authenticate( $p_user_id, $p_test_password );
	}

	if( !auth_can_use_standard_login( $p_user_id ) ) {
		return false;
	}

	$t_password = user_get_field( $p_user_id, 'password' );
	$t_login_methods = array(
		MD5,
		CRYPT,
		PLAIN,
		BASIC_AUTH,
	);

	foreach( $t_login_methods as $t_login_method ) {
		# pass the stored password in as the salt
		if( auth_process_plain_password( $p_test_password, $t_password, $t_login_method ) == $t_password ) {
			# Do not support migration to PLAIN, since this would be a crazy thing to do.
			# Also if we do, then a user will be able to login by providing the MD5 value
			# that is copied from the database.  See #8467 for more details.
			if( ( $t_configured_login_method != PLAIN && $t_login_method == PLAIN ) ||
				( $t_configured_login_method != BASIC_AUTH && $t_login_method == BASIC_AUTH ) ) {
				continue;
			}

			# Check for migration to another login method and test whether the password was encrypted
			# with our previously insecure implementation of the CRYPT method
			if( ( $t_login_method != $t_configured_login_method ) || (( CRYPT == $t_configured_login_method ) && mb_substr( $t_password, 0, 2 ) == mb_substr( $p_test_password, 0, 2 ) ) ) {
				user_set_password( $p_user_id, $p_test_password, true );
			}

			return true;
		}
	}

	return false;
}

/**
 * Encrypt and return the plain password given, as appropriate for the current
 *  global login method.
 *
 * When generating a new password, no salt should be passed in.
 * When encrypting a password to compare to a stored password, the stored
 *  password should be passed in as salt.  If the authentication method is CRYPT then
 *  crypt() will extract the appropriate portion of the stored password as its salt
 *
 * @param string $p_password Password.
 * @param string $p_salt     Salt, defaults to null.
 * @param string $p_method   Logon method, defaults to null (use configuration login method).
 * @return string processed password, maximum DB_FIELD_SIZE_PASSWORD chars in length
 * @access public
 */
function auth_process_plain_password( $p_password, $p_salt = null, $p_method = null ) {
	$t_login_method = config_get_global( 'login_method' );
	if( $p_method !== null ) {
		$t_login_method = $p_method;
	}

	switch( $t_login_method ) {
		case CRYPT:

			# a null salt is the same as no salt, which causes a salt to be generated
			# otherwise, use the salt given
			$t_processed_password = crypt( $p_password, $p_salt );
			break;
		case MD5:
			$t_processed_password = md5( $p_password );
			break;
		case BASIC_AUTH:
		case PLAIN:
		default:
			$t_processed_password = $p_password;
			break;
	}

	# cut this off to DB_FIELD_SIZE_PASSWORD characters which the largest possible string in the database
	return mb_substr( $t_processed_password, 0, DB_FIELD_SIZE_PASSWORD );
}

/**
 * Generate a random 16 character password.
 * @todo create memorable passwords?
 * @return string 16 character random password
 * @access public
 */
function auth_generate_random_password() {
	return crypto_generate_uri_safe_nonce( 16 );
}

/**
 * Generate a confirmation code to validate password reset requests.
 * @param integer $p_user_id User ID to generate a confirmation code for.
 * @return string Confirmation code (384bit) encoded according to the base64 with URI safe alphabet approach described in RFC4648
 * @access public
 */
function auth_generate_confirm_hash( $p_user_id ) {
	$t_password = user_get_field( $p_user_id, 'password' );
	$t_last_visit = user_get_field( $p_user_id, 'last_visit' );

	$t_confirm_hash_raw = hash( 'whirlpool', 'confirm_hash' . config_get_global( 'crypto_master_salt' ) . $t_password . $t_last_visit, true );
	# Note: We truncate the last 8 bits from the hash output so that base64
	# encoding can be performed without any trailing padding.
	$t_confirm_hash_base64_encoded = base64_encode( substr( $t_confirm_hash_raw, 0, 63 ) );

	return strtr( $t_confirm_hash_base64_encoded, '+/', '-_' );
}

/**
 * Set login cookies for the user
 * If $p_perm_login is true, a long-term cookie is created
 * @param integer $p_user_id    A user identifier.
 * @param boolean $p_perm_login Indicates whether to generate a long-term cookie.
 * @access public
 * @return void
 * @throws ClientException
 */
function auth_set_cookies( $p_user_id, $p_perm_login = false ) {
	$t_cookie_string = user_get_field( $p_user_id, 'cookie_string' );
	$t_cookie_name = config_get_global( 'string_cookie' );
	gpc_set_cookie( $t_cookie_name, $t_cookie_string, auth_session_expiry( $p_user_id, $p_perm_login ) );
}

/**
 * Clear login cookies, return true if they were cleared
 * @return boolean indicating whether cookies were cleared
 * @access public
 */
function auth_clear_cookies() {
	global $g_script_login_cookie, $g_cache_cookie_valid;

	$t_cookies_cleared = false;
	$g_cache_cookie_valid = null;

	# clear cookie, if not logged in from script
	if( $g_script_login_cookie == null ) {
		$t_cookie_name = config_get_global( 'string_cookie' );
		$t_cookie_path = config_get_global( 'cookie_path' );

		gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
		$t_cookies_cleared = true;
	} else {
		$g_script_login_cookie = null;
	}
	return $t_cookies_cleared;
}

/**
 * Generate a unique random identifier for the login cookie.
 *
 * The generated string is base64-encoded with the URI-safe alphabet
 * approach described in RFC4648.
 *
 * @return string Random and unique 384bit cookie string.
 * @access public
 * @throws ClientException
 */
function auth_generate_unique_cookie_string() {
	do {
		$t_cookie_string = crypto_generate_uri_safe_nonce( 64 );
	} while( !auth_is_cookie_string_unique( $t_cookie_string ) );

	return $t_cookie_string;
}

/**
 * Determine whether a cookie string is unique.
 *
 * @param string $p_cookie_string Cookie string.
 * @return bool True if the cookie string is unique, false otherwise.
 * @access public
 *
 * @throws ClientException
 */
function auth_is_cookie_string_unique( $p_cookie_string ) {
	return false === user_get_id_by_cookie( $p_cookie_string );
}

/**
 * Return the current user's login cookie string.
 *
 * Note that the cookie cached by a script login supersedes the cookie provided
 * by the browser. This shouldn't normally matter, except that the password
 * verification uses this routine to bypass the normal authentication, and can
 * get confused when a normal user logs in, then runs the verify script.
 * The act of fetching config variables may get the wrong userid.
 * If no user is logged in and anonymous login is enabled, returns cookie for
 * anonymous user, otherwise returns '' (an empty string)
 *
 * @param boolean $p_login_anonymous Auto-login anonymous user.
 * @return string current user login cookie string
 * @access public
 */
function auth_get_current_user_cookie( $p_login_anonymous = true ) {
	global $g_script_login_cookie, $g_cache_anonymous_user_cookie_string;

	# if logging in via a script, return that cookie
	if( $g_script_login_cookie !== null ) {
		return $g_script_login_cookie;
	}

	# fetch user cookie
	$t_cookie_name = config_get_global( 'string_cookie' );
	$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

	# if cookie not found, and anonymous login enabled, use cookie of anonymous account.
	if( is_blank( $t_cookie ) ) {
		if( $p_login_anonymous && auth_anonymous_enabled() ) {
			if( $g_cache_anonymous_user_cookie_string === null ) {
				if( function_exists( 'db_is_connected' ) && db_is_connected() ) {
					# get anonymous information if database is available
					$t_row = user_get_row_by_name( auth_anonymous_account() );
					if( $t_row ) {
						$t_cookie = $t_row['cookie_string'];
						$g_cache_anonymous_user_cookie_string = $t_cookie;
						current_user_set( $t_row['id'] );
					}
				}
			} else {
				$t_cookie = $g_cache_anonymous_user_cookie_string;
			}
		}
	}

	return $t_cookie;
}

/**
 * Set authentication tokens for secure session.
 * @param integer $p_user_id User identifier.
 * @access public
 * @return void
 * @throws ClientException
 */
function auth_set_tokens( $p_user_id ) {
	$t_auth_token = token_get( TOKEN_AUTHENTICATED, $p_user_id );
	if( null == $t_auth_token ) {
		token_set( TOKEN_AUTHENTICATED, true, auth_reauthentication_expiry(), $p_user_id );
	} else {
		token_touch( $t_auth_token['id'], auth_reauthentication_expiry() );
	}
}

/**
 * Checks if reauthentication is enabled.
 * @return bool true: enabled; false: otherwise.
 * @throws ClientException
 */
function auth_reauthentication_enabled() {
	$t_auth_flags = auth_flags();
	return $t_auth_flags->getReauthenticationEnabled();
}

/**
 * Gets the reauthentication timeout/expiry.
 * @return integer The re-authentication expiry in seconds.
 * @throws ClientException
 */
function auth_reauthentication_expiry() {
	$t_auth_flags = auth_flags();
	return $t_auth_flags->getReauthenticationLifetime();
}

/**
 * Check for authentication tokens, and redirect to login page for
 * re-authentication. Currently, if using BASIC or HTTP authentication methods,
 * or if logged in anonymously, this function will always "authenticate" the
 * user (do nothing).
 *
 * @return boolean
 * @access public
 * @throws ClientException
 */
function auth_reauthenticate() {
	$t_login_method = config_get_global( 'login_method' );
	if( !auth_reauthentication_enabled() || BASIC_AUTH == $t_login_method || HTTP_AUTH == $t_login_method ) {
		return true;
	}

	$t_auth_token = token_get( TOKEN_AUTHENTICATED );
	if( null != $t_auth_token ) {
		token_touch( $t_auth_token['id'], auth_reauthentication_expiry() );
		return true;
	} else {
		$t_anon_account = auth_anonymous_account();
		$t_anon_allowed = auth_anonymous_enabled();

		$t_user_id = auth_get_current_user_id();
		$t_username = user_get_username( $t_user_id );

		# check for anonymous login
		if( ON == $t_anon_allowed && $t_anon_account == $t_username ) {
			return true;
		}

		$t_query_params = http_build_query( array(
			'reauthenticate' => 1,
			'username' => $t_username,
			'return' => string_url( $_SERVER['REQUEST_URI'] ),
		) );

		# redirect to login page
		return print_header_redirect( auth_credential_page( $t_query_params ) );
	}
}

/**
 * Determines if the cookie string is valid.
 *
 * @param string $p_cookie_string Cookie string.
 * @return bool True if valid, false otherwise.
 * @access public
 *
 * @throws ClientException
 */
function auth_is_cookie_valid( $p_cookie_string ) {
	global $g_cache_current_user_id;

	# fail if DB isn't accessible
	if( !db_is_connected() ) {
		return false;
	}

	# fail if cookie is blank
	if( '' === $p_cookie_string ) {
		return false;
	}

	# succeed if user has already been authenticated
	if( NO_USER != $g_cache_current_user_id ) {
		return true;
	}

	if( user_search_cache( 'cookie_string', $p_cookie_string ) ) {
		return true;
	}

	# look up cookie in the database to see if it is valid
	return false !== user_get_id_by_cookie( $p_cookie_string );
}

/**
 * Retrieve current user's Id.
 *
 * @return integer user id
 * @access public
 *
 * @throws ClientException
 */
function auth_get_current_user_id() {
	global $g_cache_current_user_id;

	if( NO_USER != $g_cache_current_user_id ) {
		return (int)$g_cache_current_user_id;
	}

	$t_cookie_string = auth_get_current_user_cookie();

	if( $t_result = user_search_cache( 'cookie_string', $t_cookie_string ) ) {
		$t_user_id = (int)$t_result['id'];
		current_user_set( $t_user_id );
		return $t_user_id;
	}

	# @todo error with an error saying they aren't logged in? Or redirect to the login page maybe?
	$t_user_id = user_get_id_by_cookie( $t_cookie_string );

	# The cookie was invalid. Clear the cookie (to allow people to log in again)
	# and give them an Access Denied message.
	if( $t_user_id === false ) {
		auth_clear_cookies();
		access_denied();
		exit();
	}

	current_user_set( $t_user_id );

	return $t_user_id;
}

/**
 * A method that looks up a user id given their cookie.
 *
 * @param string $p_cookie_string The cookie string to lookup
 * @return bool|int The user id or false if no user match found.
 * @access public
 *
 * @throws ClientException
 */
function auth_user_id_from_cookie( $p_cookie_string ) {
	return user_get_id_by_cookie( $p_cookie_string );
}

/**
 * Generate HTTP 401 Access Denied header and page for user, prompting for BASIC authentication
 *
 * @return void
 * @access public
 */
function auth_http_prompt() {
	header( 'HTTP/1.0 401 Authorization Required' );
	header( 'WWW-Authenticate: Basic realm="' . lang_get( 'http_auth_realm' ) . '"' );
	header( 'status: 401 Unauthorized' );

	echo '<p class="center error-msg">' . error_string( ERROR_ACCESS_DENIED ) . '</p>';
	print_link_button( 'main_page.php', lang_get( 'proceed' ) );

	exit;
}

/**
 * Update Cookies to reflect pending logout
 *
 * @param boolean $p_pending Whether pending.
 * @access public
 * @return void
 */
function auth_http_set_logout_pending( $p_pending ) {
	$t_cookie_name = config_get_global( 'logout_cookie' );

	if( $p_pending ) {
		gpc_set_cookie( $t_cookie_name, '1' );
	} else {
		$t_cookie_path = config_get_global( 'cookie_path' );
		gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
	}
}

/**
 * Check cookie values to see if Logout is pending
 *
 * @return boolean
 * @access public
 */
function auth_http_is_logout_pending() {
	$t_cookie_name = config_get_global( 'logout_cookie' );
	$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

	return( $t_cookie > '' );
}
