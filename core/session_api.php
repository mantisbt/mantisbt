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
 * Session API
 *
 * Handles user/browser sessions in an extendable manner. New session handlers
 * can be added and configured without affecting how the API is used. Calls to
 * session_*() are appropriately directed at the session handler class as
 * chosen in config_inc.php.
 *
 * @package CoreAPI
 * @subpackage SessionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses php_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'php_api.php' );

$g_session = null;

/**
 * Abstract interface for a MantisBT session handler.
 */
abstract class MantisSession {
	/**
	 * Session ID
	 */
	protected $id;

	/**
	 * Constructor
	 */
	abstract function __construct();

	/**
	 * get session data
	 * @param string $p_name    The name of the value to set.
	 * @param mixed  $p_default The value to set.
	 * @return string
	 */
	abstract function get( $p_name, $p_default = null );

	/**
	 * set session data
	 * @param string $p_name  The name of the value to set.
	 * @param mixed  $p_value The value to set.
	 * @return void
	 */
	abstract function set( $p_name, $p_value );

	/**
	 * delete session data
	 * @param string $p_name The name of the value to set.
	 * @return void
	 */
	abstract function delete( $p_name );

	/**
	 * destroy session
	 * @return void
	 */
	abstract function destroy();
}

/**
 * Implementation of the abstract MantisBT session interface using
 * standard PHP sessions stored on the server's filesystem according
 * to PHP's session.* settings in 'php.ini'.
 */
class MantisPHPSession extends MantisSession {
	/**
	 * Constructor
	 * @param integer $p_session_id The session id.
	 */
	function __construct( $p_session_id = null ) {
		global $g_cookie_secure_flag_enabled;

		$this->key = hash( 'whirlpool', 'session_key_v_2' . config_get_global( 'crypto_master_salt' ), false );

		# Save session information where specified or with PHP's default
		$t_session_save_path = config_get_global( 'session_save_path' );
		if( $t_session_save_path ) {
			session_save_path( $t_session_save_path );
		}

		# Handle session cookie and caching
		session_cache_limiter( 'private_no_expire' );
		session_set_cookie_params( 0, config_get( 'cookie_path' ), config_get( 'cookie_domain' ), $g_cookie_secure_flag_enabled, true );

		# Handle existent session ID
		if( !is_null( $p_session_id ) ) {
			session_id( $p_session_id );
		}

		# Initialize the session
		session_start();
		$this->id = session_id();

		# Initialize the keyed session store
		if( !isset( $_SESSION[$this->key] ) ) {
			$_SESSION[$this->key] = array();
		}
	}

	/**
	 * get session data
	 * @param string $p_name    The name of the value to set.
	 * @param mixed  $p_default The value to set.
	 * @return string
	 */
	function get( $p_name, $p_default = null ) {
		if( isset( $_SESSION[$this->key][$p_name] ) ) {
			return $_SESSION[$this->key][$p_name];
		}

		if( func_num_args() > 1 ) {
			return $p_default;
		}

		error_parameters( $p_name );
		trigger_error( ERROR_SESSION_VAR_NOT_FOUND, ERROR );
	}

	/**
	 * set session data
	 * @param string $p_name  The name of the value to set.
	 * @param mixed  $p_value The value to set.
	 * @return void
	 */
	function set( $p_name, $p_value ) {
		$_SESSION[$this->key][$p_name] = $p_value;
	}

	/**
	 * delete session data
	 * @param string $p_name The name of the value to set.
	 * @return void
	 */
	function delete( $p_name ) {
		unset( $_SESSION[$this->key][$p_name] );
	}

	/**
	 * destroy session
	 * @return void
	 */
	function destroy() {
		if( isset( $_COOKIE[session_name()] ) && !headers_sent() ) {
			gpc_set_cookie( session_name(), '', time() - 42000 );
		}

		unset( $_SESSION[$this->key] );
	}
}

/**
 * Initialize the appropriate session handler.
 * @param string $p_session_id Session ID.
 * @return void
 */
function session_init( $p_session_id = null ) {
	global $g_session, $g_session_handler;

	switch( utf8_strtolower( $g_session_handler ) ) {
		case 'php':
			$g_session = new MantisPHPSession( $p_session_id );
			break;
		case 'memcached':
			# Not yet implemented
		default:
			trigger_error( ERROR_SESSION_HANDLER_INVALID, ERROR );
			break;
	}

	if( ON == config_get_global( 'session_validation' ) && session_get( 'secure_session', false ) ) {
		session_validate( $g_session );
	}
}

/**
 * Validate the legitimacy of a session.
 * Checks may include last-known IP address, or more.
 * Triggers an error when the session is invalid.
 * @param object $p_session Session object.
 * @return void
 */
function session_validate( $p_session ) {
	$t_user_ip = '';
	if( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$t_user_ip = trim( $_SERVER['REMOTE_ADDR'] );
	}

	if( is_null( $t_last_ip = $p_session->get( 'last_ip', null ) ) ) {
		# First session usage
		$p_session->set( 'last_ip', $t_user_ip );

	} else {
		# Check a continued session request
		if( $t_user_ip != $t_last_ip ) {
			session_clean();

			trigger_error( ERROR_SESSION_NOT_VALID, WARNING );

			$t_url = config_get_global( 'path' ) . config_get_global( 'default_home_page' );
			echo "\t<meta http-equiv=\"Refresh\" content=\"4; URL=" . $t_url . "\" />\n";

			die();
		}
	}
}

/**
 * Get arbitrary data from the session.
 * @param string $p_name    Session variable name.
 * @param mixed  $p_default Default value.
 * @return mixed Session variable
 */
function session_get( $p_name, $p_default = null ) {
	global $g_session;

	$t_args = func_get_args();
	return call_user_func_array( array( $g_session, 'get' ), $t_args );
}

/**
 * Get an integer from the session.
 * @param string       $p_name    Session variable name.
 * @param integer|null $p_default Default value.
 * @return integer Session variable
 */
function session_get_int( $p_name, $p_default = null ) {
	$t_args = func_get_args();
	return (int)call_user_func_array( 'session_get', $t_args );
}

/**
 * Get a boolean from the session.
 * @param string       $p_name    Session variable name.
 * @param boolean|null $p_default Default value.
 * @return boolean Session variable
 */
function session_get_bool( $p_name, $p_default = null ) {
	$t_args = func_get_args();
	return true && call_user_func_array( 'session_get', $t_args );
}

/**
 * Get a string from the session.
 * @param string      $p_name    Session variable name.
 * @param string|null $p_default Default value.
 * @return string Session variable
 */
function session_get_string( $p_name, $p_default = null ) {
	$t_args = func_get_args();
	return '' . call_user_func_array( 'session_get', $t_args );
}

/**
 * Set a session variable.
 * @param string $p_name  Session variable name.
 * @param mixed  $p_value Variable value.
 * @return void
 */
function session_set( $p_name, $p_value ) {
	global $g_session;
	$g_session->set( $p_name, $p_value );
}

/**
 * Delete a session variable.
 * @param string $p_name Session variable name.
 * @return void
 */
function session_delete( $p_name ) {
	global $g_session;
	$g_session->delete( $p_name );
}

/**
 * Destroy the session entirely.
 * @return void
 */
function session_clean() {
	global $g_session;
	$g_session->destroy();
}

# Initialize the session
if( PHP_CGI == php_mode() ) {
	$t_session_id = gpc_get_string( 'session_id', '' );

	if( empty( $t_session_id ) ) {
		session_init();
	} else {
		session_init( $t_session_id );
	}
}

