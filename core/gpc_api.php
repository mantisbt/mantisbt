<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: gpc_api.php,v 1.10 2002-09-20 18:09:22 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# GET, POST, and Cookie API
	###########################################################################

	# ---------------
	# Retrieve a GPC variable.
	# If the variable is not set, the default is returned. 
	# If magic_quotes_gpc is on, slashes will be stripped from the value before being returned.
	#
	#  You may pass in any variable as a default (including null) but if
	#  you pass in *no* default then an error will be triggered if the field
	#  cannot be found
	function gpc_get( $p_var_name, $p_default = null ) {
		# simulate auto-globals from PHP v4.1.0 (see also code in php_api.php)
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_POST, $_GET;
		}

		if ( isset( $_POST[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_POST[$p_var_name] );
		} else if ( isset( $_GET[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_GET[$p_var_name] );
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			trigger_error(ERROR_GPC_VAR_NOT_FOUND, ERROR);
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
			trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
		}

		return (integer)$t_result;
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
				trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
			}

			return gpc_string_to_bool( $t_result );
		}
	}

	#===================================
	# Array Functions
	#===================================

	# ------------------
	# Retrieve a atring array GPC variable.  Uses gpc_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function gpc_get_string_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'gpc_get', $args );
		
		if ( ! is_array( $t_result ) ) {
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
		
		if ( ! is_array( $t_result ) ) {
			trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR);
		} 

		for ( $i=0 ; $i < sizeof( $t_result ) ; $i++ ) {
			$t_result[$i] = (integer)$t_result[$i];
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
		
		if ( ! is_array( $t_result ) ) {
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
		# simulate auto-globals from PHP v4.1.0 (see also code in php_api.php)
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_COOKIE;
		}

		if ( isset( $_COOKIE[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_COOKIE[$p_var_name] );
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			trigger_error(ERROR_GPC_VAR_NOT_FOUND, ERROR);
		}
		
		return $t_result;
	}

	# ------------------
	# Set a cookie variable
	# If $p_expire is false instead of a number, the cookie will expire when
	#  the browser is closed; if it is true, the default time from the config
	#  file will be used
	# If $p_path or $p_domaain are omitted, defaults are used
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
	function gpc_clear_cookie( $p_name, $p_path=null ) {
		if ( null === $p_path ) {
			$p_path = config_get( 'cookie_path' );
		}
		return setcookie( $p_name, '', -1, $p_path );
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
		# simulate auto-globals from PHP v4.1.0 (see also code in php_api.php)
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_FILES;
		}
		
		if ( isset ( $_FILES[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_FILES[$p_var_name] );
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			trigger_error(ERROR_GPC_VAR_NOT_FOUND, ERROR);
		}

		return $t_result;
	}

	#===================================
	# Helper Functions
	#===================================

	# ------------------
	# Convert a string to a bool
	function gpc_string_to_bool( $p_string ) {
		if ( 0 == strcasecmp( 'off', $p_string ) ||
			 0 == strcasecmp( 'no', $p_string ) ||
			 0 == strcasecmp( 'false', $p_string ) ||
			 0 == strcasecmp( '0', $p_string ) ) {
			return false;
		} else {
			return true;
		}
	}

	# ------------------
	# Strip slashes if necessary (supports arrays)
	function gpc_strip_slashes( $p_var ) {
		if (get_magic_quotes_gpc() == 0) {
			return $p_var;
		} else if ( ! is_array( $p_var ) ){
			return stripslashes( $p_var );
		} else {
			foreach ( $p_var as $key => $value ) {
				$p_var[$key] = stripslashes( $value );

				return $p_var;
			}
		}
	}
?>
