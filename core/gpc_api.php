<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: gpc_api.php,v 1.41.2.1 2007-10-13 22:35:28 giallu Exp $
	# --------------------------------------------------------

	### GET, POST, and Cookie API ###

	# ---------------
	# Retrieve a GPC variable.
	# If the variable is not set, the default is returned.
	# If magic_quotes_gpc is on, slashes will be stripped from the value before being returned.
	#
	#  You may pass in any variable as a default (including null) but if
	#  you pass in *no* default then an error will be triggered if the field
	#  cannot be found
	function gpc_get( $p_var_name, $p_default = null ) {
		if ( isset( $_POST[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_POST[$p_var_name] );
		} else if ( isset( $_GET[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_GET[$p_var_name] );
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
			$t_result = null;
		}

		return $t_result;
	}
	# -----------------
	# Retrieve a string GPC variable. Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_string( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'gpc_get', $args );

		if ( is_array( $t_result ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
		}

		return $t_result;
	}
	# ------------------
	# Retrieve an integer GPC variable. Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_int( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'gpc_get', $args );

		if ( is_array( $t_result ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
		}
		$t_val = str_replace( " ", "", trim( $t_result ) );
		if ( ! preg_match( "/^-?([0-9])*$/", $t_val ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_NOT_NUMBER, ERROR );
		}

		return (int)$t_val;
	}
	# ------------------
	# Retrieve a boolean GPC variable. Uses gpc_get().
	#  If you pass in *no* default, false will be used
	function gpc_get_bool( $p_var_name, $p_default = false ) {
		$t_result = gpc_get( $p_var_name, $p_default );

		if ( $t_result === $p_default ) {
			return $p_default;
		} else {
			if ( is_array( $t_result ) ) {
				error_parameters( $p_var_name );
				trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
			}

			return gpc_string_to_bool( $t_result );
		}
	}

	#===================================
	# Custom Field Functions
	#===================================

	# ------------------
	# Retrieve a custom field variable.  Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_custom_field( $p_var_name, $p_custom_field_type, $p_default = null ) {
		switch ($p_custom_field_type ) {
			case CUSTOM_FIELD_TYPE_MULTILIST:
			case CUSTOM_FIELD_TYPE_CHECKBOX:
				$t_values = gpc_get_string_array( $p_var_name, $p_default );
				if( null !== $t_values && '' != $t_values ) {
					return implode( '|', $t_values );
				} else {
					return '';
				}
				break ;
			case CUSTOM_FIELD_TYPE_DATE:
				$t_day = gpc_get_int( $p_var_name . "_day", 0) ;
				$t_month = gpc_get_int( $p_var_name . "_month", 0) ;
				$t_year = gpc_get_int( $p_var_name . "_year", 0) ;
				if (($t_year == 0) || ($t_month == 0) || ($t_day == 0)) {
					if ($p_default == null) {
						return '' ;
					} else {
						return $p_default ;
					}
				} else {
					return strtotime($t_year . "-" . $t_month . "-" . $t_day) ;
				}
				break ;
			default:
				return gpc_get_string( $p_var_name, $p_default);
		}
	}

	#===================================
	# Array Functions
	#===================================

	# ------------------
	# Retrieve a string array GPC variable.  Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_string_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'gpc_get', $args );

		# If we the result isn't the default we were given or an array, error
		if ( !( ( ( 1 < func_num_args() ) && ( $t_result === $p_default ) ) ||
			is_array( $t_result ) ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR);
		}

		return $t_result;
	}
	# ------------------
	# Retrieve an integer array GPC variable.  Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_int_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'gpc_get', $args );

		# If we the result isn't the default we were given or an array, error
		if ( !( ( ( 1 < func_num_args() ) && ( $t_result === $p_default ) ) ||
			     is_array( $t_result ) ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR);
		}

		for ( $i=0 ; $i < sizeof( $t_result ) ; $i++ ) {
			$t_result[$i] = (int)$t_result[$i];
		}

		return $t_result;
	}
	# ------------------
	# Retrieve a boolean array GPC variable.  Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_bool_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'gpc_get', $args );

		# If we the result isn't the default we were given or an array, error
		if ( !( ( ( 1 < func_num_args() ) && ( $t_result === $p_default ) ) ||
			     is_array( $t_result ) ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR);
		}

		for ( $i=0 ; $i < sizeof( $t_result ) ; $i++ ) {
			$t_result[$i] = gpc_string_to_bool( $t_result[$i] );
		}

		return $t_result;
	}

	#===================================
	# Cookie Functions
	#===================================

	# ------------------
	# Retrieve a cookie variable
	#  You may pass in any variable as a default (including null) but if
	#  you pass in *no* default then an error will be triggered if the cookie
	#  cannot be found
	function gpc_get_cookie( $p_var_name, $p_default = null ) {
		if ( isset( $_COOKIE[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_COOKIE[$p_var_name] );
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			error_parameters( $p_var_name );
			trigger_error(ERROR_GPC_VAR_NOT_FOUND, ERROR);
		}

		return $t_result;
	}

	# ------------------
	# Set a cookie variable
	# If $p_expire is false instead of a number, the cookie will expire when
	#  the browser is closed; if it is true, the default time from the config
	#  file will be used
	# If $p_path or $p_domain are omitted, defaults are used
	#
	# @@@ this function is to be modified by Victor to add CRC... for now it
	#  just passes the parameters through to setcookie()
	function gpc_set_cookie( $p_name, $p_value, $p_expire=false, $p_path=null, $p_domain=null ) {
		if ( false === $p_expire ) {
			$p_expire = 0;
		} else if (true === $p_expire ) {
			$t_cookie_length = config_get( 'cookie_time_length' );
			$p_expire = time()+$t_cookie_length;
		}
		if ( null === $p_path ) {
			$p_path = config_get( 'cookie_path' );
		}
		if ( null === $p_domain ) {
			$p_domain = config_get( 'cookie_domain' );
		}

		return setcookie( $p_name, $p_value, $p_expire, $p_path, $p_domain );
	}

	# ------------------
	# Clear a cookie variable
	function gpc_clear_cookie( $p_name, $p_path=null, $p_domain=null ) {
		if ( null === $p_path ) {
			$p_path = config_get( 'cookie_path' );
		}
		if ( null === $p_domain ) {
			$p_domain = config_get( 'cookie_domain' );
		}

		if ( isset( $_COOKIE[$p_name] ) ) {
			unset( $_COOKIE[$p_name] ) ;
		}

		# dont try to send cookie if headers are send (guideweb)
		if ( !headers_sent() ) {
			return setcookie( $p_name, '', -1, $p_path, $p_domain );
		} else {
			return false;
		}
	}

	#===================================
	# File Functions
	#===================================

	# ------------------
	# Retrieve a file variable
	#  You may pass in any variable as a default (including null) but if
	#  you pass in *no* default then an error will be triggered if the file
	#  cannot be found
	function gpc_get_file( $p_var_name, $p_default = null ) {
		if ( isset ( $_FILES[$p_var_name] ) ) {
			# FILES are not escaped even if magic_quotes is ON, this applies to Windows paths.
			$t_result = $_FILES[$p_var_name];
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			error_parameters( $p_var_name );
			trigger_error(ERROR_GPC_VAR_NOT_FOUND, ERROR);
		}

		return $t_result;
	}

	#===================================
	# Helper Functions
	#===================================

	# ------------------
	# Convert a POST/GET parameter to an array if it is not already one.
	# $p_var_name - The name of the parameter
	# no return value.  The $_POST/$_GET are updated as appropriate. 
	function gpc_make_array( $p_var_name ) {
		if ( isset( $_POST[$p_var_name] ) && !is_array( $_POST[$p_var_name] ) ) {
			$_POST[$p_var_name] = array( $_POST[$p_var_name] );
		}

		if ( isset( $_GET[$p_var_name] ) && !is_array( $_GET[$p_var_name] ) ) {
			$_GET[$p_var_name] = array( $_GET[$p_var_name] );
		}
	}

	# ------------------
	# Convert a string to a bool
	function gpc_string_to_bool( $p_string ) {
		if ( 0 == strcasecmp( 'off', $p_string ) ||
			 0 == strcasecmp( 'no', $p_string ) ||
			 0 == strcasecmp( 'false', $p_string ) ||
			 0 == strcasecmp( '', $p_string ) ||
			 0 == strcasecmp( '0', $p_string ) ) {
			return false;
		} else {
			return true;
		}
	}

	# ------------------
	# Strip slashes if necessary (supports arrays)
	function gpc_strip_slashes( $p_var ) {
		if ( 0 == get_magic_quotes_gpc() ) {
			return $p_var;
		} else if ( !is_array( $p_var ) ){
			return stripslashes( $p_var );
		} else {
			foreach ( $p_var as $key => $value ) {
				$p_var[$key] = gpc_strip_slashes( $value );
			}
			return $p_var;
		}
	}
?>
