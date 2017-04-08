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

	function setPasswordManagedExternallyMessage( $p_message ) {
		$this->password_managed_elsewhere_message = $p_message;
	}

	function getPasswordManagedExternallyMessage() {
		if( empty( $this->password_managed_elsewhere_message ) ) {
			return lang_get( 'password_managed_elsewhere_message' );
		}
	}

	function setCanUseStandardLogin( $p_enabled ) {
		$this->can_use_native_login = $p_enabled;
	}

	function getCanUseStandardLogin() {
		return is_null( $this->can_use_native_login ) ? true : $this->can_use_native_login;
	}

	function setLoginPage( $p_page ) {
		$this->login_page = $p_page;
	}

	function getLoginPage() {
		return is_null( $this->login_page ) ? AUTH_PAGE_USERNAME : $this->login_page;
	}

	function setCredentialsPage( $p_page ) {
		$this->credentials_page = $p_page;
	}

	function getCredentialsPage() {
		return is_null( $this->credentials_page ) ? AUTH_PAGE_CREDENTIAL : $this->credentials_page;
	}

	function setLogoutPage( $p_page ) {
		$this->logout_page = $p_page;
	}

	function getLogoutPage() {
		if( is_null( $this->logout_page ) ) {
			return 'logout_page.php';
		}

		return $this->logout_page;
	}

	function setLogoutRedirectPage( $p_page ) {
		$this->logout_redirect_page = $p_page;
	}

	function getLogoutRedirectPage() {
		if( is_null( $this->logout_redirect_page ) ) {
			return config_get( 'logout_redirect_page' );
		}

		return $this->logout_redirect_page;
	}

	function setSessionLifetime( $p_seconds ) {
		$this->session_lifetime = $p_seconds;
	}

	function getSessionLifetime() {
		if( is_null( $this->session_lifetime ) ) {
			return 0;
		}

		return $this->session_lifetime;
	}

	function setPermSessionEnabled( $p_enabled ) {
		$this->perm_session_enabled = $p_enabled;
	}

	function getPermSessionEnabled() {
		if( is_null( $this->perm_session_enabled ) ) {
			return config_get_global( 'allow_permanent_cookie' ) != OFF;
		}

		return $this->perm_session_enabled;
	}

	function setPermSessionLifetime( $p_seconds ) {
		$this->perm_session_lifetime = $p_seconds;
	}

	function getPermSessionLifetime() {
		if( is_null( $this->perm_session_lifetime ) ) {
			return config_get_global( 'cookie_time_length' );
		}

		return $this->perm_session_lifetime;
	}

	function setReauthenticationEnabled( $p_enabled ) {
		$this->reauthentication_enabled = $p_enabled;
	}

	function getReauthenticationEnabled() {
		if( is_null( $this->reauthentication_enabled ) ) {
			return config_get( 'reauthentication' );
		}

		return $this->reauthentication_enabled;
	}

	function setReauthenticationLifetime( $p_seconds ) {
		$this->reauthentication_expiry = $p_seconds;
	}

	function getReauthenticationLifetime() {
		if( is_null( $this->reauthentication_expiry ) ) {
			return config_get( 'reauthentication_expiry' );
		}

		return $this->reauthentication_expiry;
	}
}

