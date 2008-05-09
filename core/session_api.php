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
	# $Id$
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
	require_once( $t_core_dir.'gpc_api.php' );

    session_start();

	# ---------------
	# Retrieve a session variable.
	# If the variable is not set, the default is returned.
	# If magic_quotes_session is on, slashes will be stripped from the value before being returned.
	#
	#  You may pass in any variable as a default (including null) but if
	#  you pass in *no* default then an error will be triggered if the field
	#  cannot be found
	function session_get( $p_var_name, $p_default = null ) {
		if ( isset( $_SESSION[$p_var_name] ) ) {
			$t_result = gpc_strip_slashes( $_SESSION[$p_var_name] );
		} else if ( func_num_args() > 1 ) { #check for a default passed in (allowing null)
			$t_result = $p_default;
		} else {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_VAR_NOT_FOUND, ERROR );
			$t_result = null;
		}

		return $t_result;
	}
	# -----------------
	# Retrieve a string GPC variable. Uses session_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function session_get_string( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'session_get', $args );

		if ( is_array( $t_result ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_ARRAY_UNEXPECTED, ERROR );
		}

		return $t_result;
	}
	# ------------------
	# Retrieve an integer GPC variable. Uses session_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function session_get_int( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'session_get', $args );

		if ( is_array( $t_result ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_ARRAY_UNEXPECTED, ERROR );
		}
		$t_val = str_replace( " ", "", trim( $t_result ) );
		if ( ! preg_match( "/^-?([0-9])*$/", $t_val ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_NOT_NUMBER, ERROR );
		}

		return (int)$t_val;
	}
	# ------------------
	# Retrieve a boolean GPC variable. Uses session_get().
	#  If you pass in *no* default, false will be used
	function session_get_bool( $p_var_name, $p_default = false ) {
		$t_result = session_get( $p_var_name, $p_default );

		if ( $t_result === $p_default ) {
			return $p_default;
		} else {
			if ( is_array( $t_result ) ) {
				error_parameters( $p_var_name );
				trigger_error( ERROR_SESSION_ARRAY_UNEXPECTED, ERROR );
			}

			return gpc_string_to_bool( $t_result );
		}
	}

	#===================================
	# Array Functions
	#===================================

	# ------------------
	# Retrieve a string array GPC variable.  Uses session_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function session_get_string_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'session_get', $args );

		# If we the result isn't the default we were given or an array, error
		if ( !( ( ( 1 < func_num_args() ) && ( $t_result === $p_default ) ) ||
			is_array( $t_result ) ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_ARRAY_EXPECTED, ERROR);
		}

		return $t_result;
	}
	# ------------------
	# Retrieve an integer array GPC variable.  Uses session_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function session_get_int_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'session_get', $args );

		# If we the result isn't the default we were given or an array, error
		if ( !( ( ( 1 < func_num_args() ) && ( $t_result === $p_default ) ) ||
			     is_array( $t_result ) ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_ARRAY_EXPECTED, ERROR);
		}

		for ( $i=0 ; $i < sizeof( $t_result ) ; $i++ ) {
			$t_result[$i] = (int)$t_result[$i];
		}

		return $t_result;
	}
	# ------------------
	# Retrieve a boolean array GPC variable.  Uses session_get().
	#  If you pass in *no* default, an error will be triggered if
	#  the variable does not exist
	function session_get_bool_array( $p_var_name, $p_default = null ) {
		# Don't pass along a default unless one was given to us
		#  otherwise we prevent an error being triggered
		$args = func_get_args();
		$t_result = call_user_func_array( 'session_get', $args );

		# If we the result isn't the default we were given or an array, error
		if ( !( ( ( 1 < func_num_args() ) && ( $t_result === $p_default ) ) ||
			     is_array( $t_result ) ) ) {
			error_parameters( $p_var_name );
			trigger_error( ERROR_SESSION_ARRAY_EXPECTED, ERROR);
		}

		for ( $i=0 ; $i < sizeof( $t_result ) ; $i++ ) {
			$t_result[$i] = session_string_to_bool( $t_result[$i] );
		}

		return $t_result;
	}

	# ---------------
	# Set a session variable.
	#
	function session_set( $p_var_name, $p_value ) {
		$_SESSION[$p_var_name] = $p_value;
	}

	# ---------------
	# Clear a session variable.
	#
	function session_clear( $p_var_name ) {
		unset( $_SESSION[$p_var_name] );
	}

	# ---------------
	# remove all session variables.
	#
	function session_clean() {
		// clear all session variables
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (isset($_COOKIE[session_name()])) {
		    setcookie(session_name(), '', time()-42000, '/');
		}

		// Finally, destroy the session.
		session_destroy();
	}
?>
