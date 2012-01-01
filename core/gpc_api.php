<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage GPCAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Determines (once-off) whether the client is accessing this script via a
 * secure connection. If they are, we want to use the Secure cookie flag to
 * prevent the cookie from being transmitted to other domains.
 * @global bool $g_cookie_secure_flag_enabled
 */
$g_cookie_secure_flag_enabled = isset( $_SERVER['HTTPS'] ) && ( utf8_strtolower( $_SERVER['HTTPS'] ) != 'off' );

/**
 * Determines (once-off) whether the version of PHP executing this script has
 * support for the HttpOnly cookie flag. If so, we will set this flag to true
 * so that it'll be added to all cookies sent to the client.
 * @global bool $g_cookie_httponly_flag_enabled
 */
$g_cookie_httponly_flag_enabled = version_compare( PHP_VERSION, '5.2.0', '>=' );

/**
 * GET, POST, and Cookie API
 * ---------------
 * Retrieve a GPC variable.
 * If the variable is not set, the default is returned.
 * If magic_quotes_gpc is on, slashes will be stripped from the value before being returned.
 *
 *  You may pass in any variable as a default (including null) but if
 *  you pass in *no* default then an error will be triggered if the field
 *  cannot be found
 *
 * @param string
 * @return null
 */
function gpc_get( $p_var_name, $p_default = null ) {
	if( isset( $_POST[$p_var_name] ) ) {
		$t_result = gpc_strip_slashes( $_POST[$p_var_name] );
	} else if( isset( $_GET[$p_var_name] ) ) {
		$t_result = gpc_strip_slashes( $_GET[$p_var_name] );
	}
	else if( func_num_args() > 1 ) {
		# check for a default passed in (allowing null)
		$t_result = $p_default;
	} else {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
		$t_result = null;
	}

	return $t_result;
}

/**
 *
 * @param string $p_var_name
 * @return bool
 */
function gpc_isset( $p_var_name ) {
	if( isset( $_POST[$p_var_name] ) ) {
		return true;
	}
	else if( isset( $_GET[$p_var_name] ) ) {
		return true;
	}

	return false;
}

/**
 * Retrieve a string GPC variable. Uses gpc_get().
 * If you pass in *no* default, an error will be triggered if
 * the variable does not exist
 * @param string $p_var_name
 * @param string $p_default (optional)
 * @return string|null
 */
function gpc_get_string( $p_var_name, $p_default = null ) {

	# Don't pass along a default unless one was given to us
	#  otherwise we prevent an error being triggered
	$args = func_get_args();
	$t_result = call_user_func_array( 'gpc_get', $args );

	if( is_array( $t_result ) ) {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
	}

	return $t_result;
}

/**
 * Retrieve an integer GPC variable. Uses gpc_get().
 * If you pass in *no* default, an error will be triggered if
 * the variable does not exist
 * @param string $p_var_name
 * @param int $p_default (optional)
 * @return int|null
 */
function gpc_get_int( $p_var_name, $p_default = null ) {
	# Don't pass along a default unless one was given to us
	#  otherwise we prevent an error being triggered
	$args = func_get_args();
	$t_result = call_user_func_array( 'gpc_get', $args );

	if( is_array( $t_result ) ) {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
	}
	$t_val = str_replace( ' ', '', trim( $t_result ) );
	if( !preg_match( "/^-?([0-9])*$/", $t_val ) ) {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_NOT_NUMBER, ERROR );
	}

	return (int) $t_val;
}

/**
 * Retrieve a boolean GPC variable. Uses gpc_get().
 *  If you pass in *no* default, false will be used
 * @param string $p_var_name
 * @param bool $p_default (optional)
 * @return bool|null
 */
function gpc_get_bool( $p_var_name, $p_default = false ) {
	$t_result = gpc_get( $p_var_name, $p_default );

	if( $t_result === $p_default ) {
		return $p_default;
	} else {
		if( is_array( $t_result ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
		}

		return gpc_string_to_bool( $t_result );
	}
}

/**
 * see if a custom field variable is set.  Uses gpc_isset().
 * @param string $p_var_name
 * @param int $p_custom_field_type
 * @return bool
 */
function gpc_isset_custom_field( $p_var_name, $p_custom_field_type ) {
	$t_field_name = 'custom_field_' . $p_var_name;

	switch ($p_custom_field_type ) {
		case CUSTOM_FIELD_TYPE_DATE:
			// date field is three dropdowns that default to 0
			// Dropdowns are always present, so check if they are set
			return gpc_isset( $t_field_name . '_day' ) &&
				gpc_get_int( $t_field_name . '_day', 0 ) != 0 &&
				gpc_isset( $t_field_name . '_month' ) &&
				gpc_get_int( $t_field_name . '_month', 0 ) != 0 &&
				gpc_isset( $t_field_name . '_year' ) &&
				gpc_get_int( $t_field_name . '_year', 0 ) != 0 ;
		case CUSTOM_FIELD_TYPE_STRING:
		case CUSTOM_FIELD_TYPE_NUMERIC:
		case CUSTOM_FIELD_TYPE_FLOAT:
		case CUSTOM_FIELD_TYPE_ENUM:
		case CUSTOM_FIELD_TYPE_EMAIL:
			return gpc_isset( $t_field_name ) && !is_blank( gpc_get_string( $t_field_name ) );
		default:
			return gpc_isset( $t_field_name );
	}
}

/**
 * Retrieve a custom field variable.  Uses gpc_get().
 * If you pass in *no* default, an error will be triggered if
 * the variable does not exist
 * @param string $p_var_name
 * @param int $p_custom_field_Type
 * @param mixed $p_default
 * @return string
 */
function gpc_get_custom_field( $p_var_name, $p_custom_field_type, $p_default = null ) {
	switch( $p_custom_field_type ) {
		case CUSTOM_FIELD_TYPE_MULTILIST:
		case CUSTOM_FIELD_TYPE_CHECKBOX:
		    // ensure that the default is an array, if set
		    if ( ($p_default !== null) && !is_array($p_default) ) {
		        $p_default = array( $p_default );
		    }
			$t_values = gpc_get_string_array( $p_var_name, $p_default );
			if( is_array( $t_values ) ) {
				return implode( '|', $t_values );
			} else {
				return '';
			}
			break;
		case CUSTOM_FIELD_TYPE_DATE:
			$t_day = gpc_get_int( $p_var_name . '_day', 0 );
			$t_month = gpc_get_int( $p_var_name . '_month', 0 );
			$t_year = gpc_get_int( $p_var_name . '_year', 0 );
			if(( $t_year == 0 ) || ( $t_month == 0 ) || ( $t_day == 0 ) ) {
				if( $p_default == null ) {
					return '';
				} else {
					return $p_default;
				}
			} else {
				return strtotime( $t_year . '-' . $t_month . '-' . $t_day );
			}
			break;
		default:
			return gpc_get_string( $p_var_name, $p_default );
	}
}

/**
 * Retrieve a string array GPC variable.  Uses gpc_get().
 * If you pass in *no* default, an error will be triggered if
 * the variable does not exist
 * @param string $p_var_name
 * @param array $p_default
 * @return array
 */
function gpc_get_string_array( $p_var_name, $p_default = null ) {
	# Don't pass along a default unless one was given to us
	#  otherwise we prevent an error being triggered
	$args = func_get_args();
	$t_result = call_user_func_array( 'gpc_get', $args );

	# If we the result isn't the default we were given or an array, error
	if( !((( 1 < func_num_args() ) && ( $t_result === $p_default ) ) || is_array( $t_result ) ) ) {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR );
	}

	return $t_result;
}

/**
 * Retrieve an integer array GPC variable.  Uses gpc_get().
 * If you pass in *no* default, an error will be triggered if
 * the variable does not exist
 * @param string $p_var_name
 * @param array $p_default
 * @return array
 */
function gpc_get_int_array( $p_var_name, $p_default = null ) {
	# Don't pass along a default unless one was given to us
	#  otherwise we prevent an error being triggered
	$args = func_get_args();
	$t_result = call_user_func_array( 'gpc_get', $args );

	# If we the result isn't the default we were given or an array, error
	if( !((( 1 < func_num_args() ) && ( $t_result === $p_default ) ) || is_array( $t_result ) ) ) {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR );
	}

	$t_count = count( $t_result );
	for( $i = 0;$i < $t_count;$i++ ) {
		$t_result[$i] = (int) $t_result[$i];
	}

	return $t_result;
}

/**
 * Retrieve a boolean array GPC variable.  Uses gpc_get().
 * If you pass in *no* default, an error will be triggered if the variable does not exist.
 * @param string $p_var_name
 * @param string $p_default
 * @return array
 */
function gpc_get_bool_array( $p_var_name, $p_default = null ) {
	# Don't pass along a default unless one was given to us
	#  otherwise we prevent an error being triggered
	$args = func_get_args();
	$t_result = call_user_func_array( 'gpc_get', $args );

	# If we the result isn't the default we were given or an array, error
	if( !((( 1 < func_num_args() ) && ( $t_result === $p_default ) ) || is_array( $t_result ) ) ) {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR );
	}

	for( $i = 0;$i < count( $t_result );$i++ ) {
		$t_result[$i] = gpc_string_to_bool( $t_result[$i] );
	}

	return $t_result;
}

/**
 * Retrieve a cookie variable
 * You may pass in any variable as a default (including null) but if
 * you pass in *no* default then an error will be triggered if the cookie cannot be found
 * @param string $p_var_name
 * @param string $p_default
 * @return string
 */
function gpc_get_cookie( $p_var_name, $p_default = null ) {
	if( isset( $_COOKIE[$p_var_name] ) ) {
		$t_result = gpc_strip_slashes( $_COOKIE[$p_var_name] );
	}
	else if( func_num_args() > 1 ) {
		# check for a default passed in (allowing null)
		$t_result = $p_default;
	} else {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
	}

	return $t_result;
}

/**
 * Set a cookie variable
 * If $p_expire is false instead of a number, the cookie will expire when
 * the browser is closed; if it is true, the default time from the config
 * file will be used.
 * If $p_path or $p_domain are omitted, defaults are used.
 * Set $p_httponly to false if client-side Javascript needs to read/write
 * the cookie. Otherwise it is safe to leave this value unspecified, as
 * the default value is true.
 * @todo this function is to be modified by Victor to add CRC... for now it just passes the parameters through to setcookie()
 * @param string $p_name
 * @param string $p_value
 * @param bool $p_expire default false
 * @param string $p_path default null
 * @param string $p_domain default null
 * @param bool $p_httponly default true
 * @return bool - true on success, false on failure
 */
function gpc_set_cookie( $p_name, $p_value, $p_expire = false, $p_path = null, $p_domain = null, $p_httponly = true ) {
	global $g_cookie_secure_flag_enabled;
	global $g_cookie_httponly_flag_enabled;
	if( false === $p_expire ) {
		$p_expire = 0;
	}
	else if( true === $p_expire ) {
		$t_cookie_length = config_get( 'cookie_time_length' );
		$p_expire = time() + $t_cookie_length;
	}
	if( null === $p_path ) {
		$p_path = config_get( 'cookie_path' );
	}
	if( null === $p_domain ) {
		$p_domain = config_get( 'cookie_domain' );
	}

	if( $g_cookie_httponly_flag_enabled ) {
		# The HttpOnly cookie flag is only supported in PHP >= 5.2.0
		return setcookie( $p_name, $p_value, $p_expire, $p_path, $p_domain, $g_cookie_secure_flag_enabled, $g_cookie_httponly_flag_enabled );
	}

	return setcookie( $p_name, $p_value, $p_expire, $p_path, $p_domain, $g_cookie_secure_flag_enabled );
}

/**
 * Clear a cookie variable
 * @param string $p_name
 * @param string $p_path
 * @param string $p_domain
 * @return bool
 */
function gpc_clear_cookie( $p_name, $p_path = null, $p_domain = null ) {
	if( null === $p_path ) {
		$p_path = config_get( 'cookie_path' );
	}
	if( null === $p_domain ) {
		$p_domain = config_get( 'cookie_domain' );
	}

	if( isset( $_COOKIE[$p_name] ) ) {
		unset( $_COOKIE[$p_name] );
	}

	# dont try to send cookie if headers are send (guideweb)
	if( !headers_sent() ) {
		return setcookie( $p_name, '', -1, $p_path, $p_domain );
	} else {
		return false;
	}
}

/**
 * Retrieve a file variable
 * You may pass in any variable as a default (including null) but if
 * you pass in *no* default then an error will be triggered if the file
 * cannot be found
 * @param string $p_var_name
 * @param mixed $p_file
 * @return mixed
 */
function gpc_get_file( $p_var_name, $p_default = null ) {
	if( isset( $_FILES[$p_var_name] ) ) {

		# FILES are not escaped even if magic_quotes is ON, this applies to Windows paths.
		$t_result = $_FILES[$p_var_name];
	}
	else if( func_num_args() > 1 ) {

		# check for a default passed in (allowing null)
		$t_result = $p_default;
	} else {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
	}

	return $t_result;
}

/**
 * Convert a POST/GET parameter to an array if it is not already one.
 * @param string $p_var_name - The name of the parameter
 * @return null no return value.  The $_POST/$_GET are updated as appropriate.
 */
function gpc_make_array( $p_var_name ) {
	if( isset( $_POST[$p_var_name] ) && !is_array( $_POST[$p_var_name] ) ) {
		$_POST[$p_var_name] = array(
			$_POST[$p_var_name],
		);
	}

	if( isset( $_GET[$p_var_name] ) && !is_array( $_GET[$p_var_name] ) ) {
		$_GET[$p_var_name] = array(
			$_GET[$p_var_name],
		);
	}
}

/**
 * Convert a string to a bool
 * @param string $p_string
 * @return bool
 */
function gpc_string_to_bool( $p_string ) {
	if( 0 == strcasecmp( 'off', $p_string ) || 0 == strcasecmp( 'no', $p_string ) || 0 == strcasecmp( 'false', $p_string ) || 0 == strcasecmp( '', $p_string ) || 0 == strcasecmp( '0', $p_string ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Strip slashes if necessary (supports arrays)
 * @param mixed $p_var
 * @return mixed
 */
function gpc_strip_slashes( $p_var ) {
	if( 0 == get_magic_quotes_gpc() ) {
		return $p_var;
	} else if( !is_array( $p_var ) ) {
		return stripslashes( $p_var );
	} else {
		foreach( $p_var as $key => $value ) {
			$p_var[$key] = gpc_strip_slashes( $value );
		}
		return $p_var;
	}
}
