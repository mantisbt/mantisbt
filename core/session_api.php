<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2008 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Session API for handling user/browser sessions in an extendable manner.
 * New session handlers can be added and configured without affecting how
 * the API is used.  Calls to session_*() are appropriately directed at the
 * session handler class as chosen in config_inc.php.
 *
 * @package SessionAPI
 */

$g_session = null;

/**
 * Abstract interface for a Mantis session handler.
 */
/* abstract */ class MantisSession {
	var $id;

	/* abstract */ function __construct() {}

	/* abstract */ function get( $p_name, $p_default=null ) {}
	/* abstract */ function set( $p_name, $p_value ) {}
	/* abstract */ function delete( $p_name ) {}

	/* abstract */ function destroy() {}
}

/**
 * Implementation of the abstract Mantis session interface using
 * standard PHP sessions stored on the server's filesystem according
 * to PHP's session.* settings in 'php.ini'.
 */
class MantisPHPSession extends MantisSession {
	function __construct() {
		$t_session_save_path = config_get_global( 'session_save_path' );
		if ( $t_session_save_path ) {
			session_save_path( $t_session_save_path );
		}

		session_cache_limiter( 'private_no_expire' );
		if ( isset( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
			session_set_cookie_params( 0, config_get( 'cookie_path' ), config_get( 'cookie_domain' ), true );
		} else {
			session_set_cookie_params( 0, config_get( 'cookie_path' ), config_get( 'cookie_domain' ), false );
		}
		session_start();
		$this->id = session_id();
	}

	# Chain the PHP4 class constructor
	function MantisPHPSession() {
		$this->__construct();
	}

	function get( $p_name, $p_default=null ) {
		if ( isset( $_SESSION[ $p_name ] ) ) {
			return unserialize( $_SESSION[ $p_name ] );
		}

		if ( func_num_args() > 1 ) {
			return $p_default;
		}

		error_parameters( $p_name );
		trigger_error( ERROR_SESSION_VAR_NOT_FOUND, ERROR );
	}

	function set( $p_name, $p_value ) {
		$_SESSION[ $p_name ] = serialize( $p_value );
	}

	function delete( $p_name ) {
		unset( $_SESSION[ $p_name ] );
	}

	function destroy() {
		if ( isset( $_COOKIE[ session_name() ] ) && !headers_sent() ) {
			gpc_set_cookie( session_name(), '', time() - 42000 );
		}

		unset( $_SESSION );
		session_destroy();
	}
}

/**
 * Initialize the appropriate session handler.
 */
function session_init() {
	global $g_session, $g_session_handler;

	switch( strtolower( $g_session_handler ) ) {
		case 'php':
			$g_session = new MantisPHPSession();
			break;

		case 'adodb':
			# Not yet implemented
		case 'memcached':
			# Not yet implemented
		default:
			trigger_error( ERROR_SESSION_HANDLER_INVALID, ERROR );
			break;
	}
}

/**
 * Get arbitrary data from the session.
 * @param string Session variable name
 * @param mixed Default value
 * @return mixed Session variable
 */
function session_get( $p_name, $p_default=null ) {
	global $g_session;

	$t_args = func_get_args();
	return call_user_func_array( array( $g_session, 'get' ), $t_args );
}

/**
 * Get an integer from the session.
 * @param string Session variable name
 * @param mixed Default value
 * @return int Session variable
 */
function session_get_int( $p_name, $p_default=null ) {
	global $g_session;
	$t_args = func_get_args();
	return (int) call_user_func_array( 'session_get', $t_args );
}

/**
 * Get a boolean from the session.
 * @param string Session variable name
 * @param mixed Default value
 * @return boolean Session variable
 */
function session_get_bool( $p_name, $p_default=null ) {
	global $g_session;
	$t_args = func_get_args();
	return true && call_user_func_array( 'session_get', $t_args );
}

/**
 * Get a string from the session.
 * @param string Session variable name
 * @param mixed Default value
 * @return string Session variable
 */
function session_get_string( $p_name, $p_default=null ) {
	global $g_session;
	$t_args = func_get_args();
	return "" . call_user_func_array( 'session_get', $t_args );
}

/**
 * Set a session variable.
 * @param string Session variable name
 * @param mixed Variable value
 */
function session_set( $p_name, $p_value ) {
	global $g_session;
	$g_session->set( $p_name, $p_value );
}

/**
 * Destroy the session entirely.
 */
function session_clean() {
	global $g_session;
	$g_session->destroy();
}


##### Initialize the session
session_init();
