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
 * Avatar class.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Auth Flags class
 *
 * @package MantisBT
 * @subpackage classes
 */

require_api( 'access_api.php' );
require_api( 'plugin_api.php' );
require_api( 'user_api.php' );

/**
 * A class that that contains authentication flags.
 */
class AuthFlags {
	/**
	 * Indicates whether user can use the native login via passwords in MantisBT database.
	 * @var bool|null
	 */
	private $can_use_native_login = null;

	/**
	 * The message to display indicating that passwords are not managed by MantisBT native passwords.
	 * @var string|null
	 */
	private $password_managed_elsewhere_message = null;

	/**
	 * The login page to use instead of the standard MantisBT login page.  This can be
	 * a plugin page.
	 *
	 * @see $credentials_page
	 * @see $logout_page
	 * @var string|null
	 */
	private $login_page = null;

	/**
	 * The page to use for providing credentials.  This can be the default password page
	 * provided by MantisBT, an auth plugin provided page that asks for credentials or
	 * an auth plugin provided page that re-directs to an IDP.
	 *
	 * @see $login_page
	 * @see $logout_page
	 * @var string|null
	 */
	private $credentials_page = null;

	/**
	 * The logout page to use instead of the standard MantisBT logout page.  This can be
	 * a plugin page.
	 *
	 * @see $credentials_page
	 * @see $login_page
	 * @see $logout_redirect_page
	 * @var string|null
	 */
	private $logout_page = null;

	/**
	 * The page to redirect to after successful logout.  This can be a plugin page.  Such
	 * page can display content directly to redirect to a MantisBT page to a remote page.
	 *
	 * @see $logout_page
	 * @var string|null
	 */
	private $logout_redirect_page = null;

	/**
	 * The login session lifetime in seconds or 0 for browser session.
	 * @var int|null
	 */
	private $session_lifetime = null;

	/**
	 * Indicates whether 'remember me' option is allowed.
	 * @see $perm_session_lifetime
	 * @var bool|null
	 */
	private $perm_session_enabled = null;

	/**
	 * Indicates the lifetime for 'remember me' sessions.  MantisBT default is 1 year.
	 * @see $perm_session_enabled
	 * @var int|null
	 */
	private $perm_session_lifetime = null;

	/**
	 * Indicates if re-authentication for operations like administrative functions and updating
	 * user profile is enabled.
	 * @see $reauthentication_expiry;
	 * @var bool|null
	 */
	private $reauthentication_enabled = null;

	/**
	 * Indicates the expiry time in seconds after which the user should be asked to reauthenticate
	 * for administrative functions and updating user profile.
	 * @see $reauthentication_enabled
	 * @var int|null
	 */
	private $reauthentication_expiry = null;

	/**
	 * AuthFlags constructor.
	 */
	function __construct() {
	}

	/**
	 * Sets the message to display to user when they can't manage their password within MantisBT.
	 *
	 * @param string|null $p_message The message to display to user or null for default message.
	 * @return void
	 * @see getPasswordManagedExternallyMessage()
	 */
	function setPasswordManagedExternallyMessage( $p_message ) {
		$this->password_managed_elsewhere_message = $p_message;
	}

	/**
	 * Gets the message to display to the user when they can't manage their password within MantisBT.
	 *
	 * @return string The message.
	 * @see setPasswordManagedExternallyMessage()
	 */
	function getPasswordManagedExternallyMessage() {
		if( empty( $this->password_managed_elsewhere_message ) ) {
			return lang_get( 'no_password_change' );
		}

		return $this->password_managed_elsewhere_message;
	}

	/**
	 * Indicates whether user can use native MantisBT auth providers or not.
	 *
	 * @param bool $p_enabled true: can user standard login, false: otherwise.
	 * @return void
	 * @see getCanUseStandardLogin()
	 */
	function setCanUseStandardLogin( $p_enabled ) {
		$this->can_use_native_login = $p_enabled;
	}

	/**
	 * Gets whether user can use standard MantisBT password providers.
	 *
	 * @return bool true: can use standard MantisBT login, false: otherwise.
	 * @see setCanUseStandardLogin()
	 */
	function getCanUseStandardLogin() {
		return is_null( $this->can_use_native_login ) ? true : $this->can_use_native_login;
	}

	/**
	 * Sets login page to use instead of the default login page that asks for username or
	 * email address.
	 *
	 * @param string $p_page The relative url of the page name.
	 * @return void
	 * @see getLoginPage()
	 */
	function setLoginPage( $p_page ) {
		$this->login_page = $p_page;
	}

	/**
	 * Gets the login page to use.
	 *
	 * @return string The relative login page name.
	 * @see setLoginPage()
	 */
	function getLoginPage() {
		return is_null( $this->login_page ) ? AUTH_PAGE_USERNAME : $this->login_page;
	}

	/**
	 * Sets the page to ask for user credentials.  By default MantisBT would ask for
	 * password on this page and options like remember me, etc.
	 *
	 * @param string $p_page The relative page name.
	 * @return void
	 * @see getCredentialsPage()
	 */
	function setCredentialsPage( $p_page ) {
		$this->credentials_page = $p_page;
	}

	/**
	 * Gets the page to use to ask for user credentials.  This should be a page that is
	 * provided by MantisBT core or one of the plugins.  Such page can redirect as needed.
	 *
	 * @param string $p_query_string The query string or empty.
	 * @return string The relative url for the credential page.
	 * @see setCredentialsPage()
	 */
	function getCredentialsPage( $p_query_string ) {
		$t_page = is_null( $this->credentials_page ) ? AUTH_PAGE_CREDENTIAL : $this->credentials_page;
		return helper_url_combine( $t_page, $p_query_string );
	}

	/**
	 * Sets the relative page of the logout page to use.  Such page can be provided by MantisBT core
	 * or a plugin, it can redirect as needed.
	 *
	 * @param string $p_page The page relative url.
	 * @return void
	 * @see getLogoutPage()
	 */
	function setLogoutPage( $p_page ) {
		$this->logout_page = $p_page;
	}

	/**
	 * Gets the relative url of the logout page provided by MantisBT core or a plugin.
	 *
	 * @return string The relative url of the logout page.
	 * @see setLogoutPage()
	 */
	function getLogoutPage() {
		return is_null( $this->logout_page ) ? 'logout_page.php' : $this->logout_page;
	}

	/**
	 * Sets the relative logout redirect page, this is used by the native MantisBT logout
	 * page.  The page must be provided by MantisBT plugin, but it can redirect as necessary.
	 *
	 * @param string $p_page The relative page url.
	 * @return void
	 * @see getLogoutRedirectPage()
	 */
	function setLogoutRedirectPage( $p_page ) {
		$this->logout_redirect_page = $p_page;
	}

	/**
	 * Gets the relative logout redirect page that the native MantisBT logout page would
	 * redirect to.  It must be a page that is provided by MantisBT core or a plugin.
	 *
	 * @return string The relative redirect url.
	 * @see setLogoutRedirectPage()
	 */
	function getLogoutRedirectPage() {
		if( is_null( $this->logout_redirect_page ) ) {
			return config_get_global( 'logout_redirect_page' );
		}

		return $this->logout_redirect_page;
	}

	/**
	 * Sets the lifetime of a default login session.
	 *
	 * @param int $p_seconds The lifetime of the session in seconds or 0 for browser session.
	 * @return void
	 * @see getSessionLifetime()
	 */
	function setSessionLifetime( $p_seconds ) {
		$this->session_lifetime = $p_seconds;
	}

	/**
	 * Gets the login session lifetime.
	 *
	 * @return int The lifetime of the session in seconds or 0 for browser session.
	 * @see setSessionLifetime()
	 */
	function getSessionLifetime() {
		if( is_null( $this->session_lifetime ) ) {
			return 0;
		}

		return $this->session_lifetime;
	}

	/**
	 * Sets whether the user can select the remember me option.
	 *
	 * @param bool $p_enabled true: enabled, false: otherwise.
	 * @return void
	 * @see getPermSessionEnabled()
	 */
	function setPermSessionEnabled( $p_enabled ) {
		$this->perm_session_enabled = $p_enabled;
	}

	/**
	 * Checks whether user can use remember me option.
	 *
	 * @return bool true: enabled, false: otherwise.
	 * @see setPermSessionEnabled()
	 */
	function getPermSessionEnabled() {
		if( is_null( $this->perm_session_enabled ) ) {
			return config_get_global( 'allow_permanent_cookie' ) != OFF;
		}

		return $this->perm_session_enabled;
	}

	/**
	 * Sets the remember me session lifetime.
	 *
	 * @param int $p_seconds The lifetime of remember me session in seconds or 0 for browser session.
	 * @return void
	 * @see getPermSessionLifetime()
	 */
	function setPermSessionLifetime( $p_seconds ) {
		$this->perm_session_lifetime = $p_seconds;
	}

	/**
	 * Gets the remember me session lifetime.
	 *
	 * @return int The session lifetime in seconds or 0 for a browser session.
	 * @see setPermSessionLifetime()
	 */
	function getPermSessionLifetime() {
		if( is_null( $this->perm_session_lifetime ) ) {
			return config_get_global( 'cookie_time_length' );
		}

		return $this->perm_session_lifetime;
	}

	/**
	 * Indicates whether user will be prompted for re-authentication after a timeout.
	 *
	 * @param bool $p_enabled true: enabled, false otherwise.
	 * @return void
	 * @see getReauthenticationEnabled()
	 */
	function setReauthenticationEnabled( $p_enabled ) {
		$this->reauthentication_enabled = $p_enabled;
	}

	/**
	 * Gets whether user will be prompted for re-authentication after a timeout.
	 *
	 * @return bool true: enabled, false otherwise.
	 * @see setReauthenticationEnabled()
	 */
	function getReauthenticationEnabled() {
		if( is_null( $this->reauthentication_enabled ) ) {
			return config_get( 'reauthentication' );
		}

		return $this->reauthentication_enabled;
	}

	/**
	 * Sets the number of seconds to re-authenticate the user after.
	 *
	 * @param int $p_seconds The number of seconds to prompt for re-authentication after.
	 * @return void
	 * @see getReauthenticationEnabled()
	 */
	function setReauthenticationLifetime( $p_seconds ) {
		$this->reauthentication_expiry = $p_seconds;
	}

	/**
	 * Gets the number of seconds to re-authenticate the user after.
	 *
	 * @return int seconds after which the user should be re-authenticated.
	 * @see setReauthenticationLifetime()
	 */
	function getReauthenticationLifetime() {
		if( is_null( $this->reauthentication_expiry ) ) {
			return config_get( 'reauthentication_expiry' );
		}

		return $this->reauthentication_expiry;
	}
}

