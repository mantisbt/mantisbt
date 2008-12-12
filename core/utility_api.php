<?php
# Mantis - a php based bugtracking system

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
 * Utility functions are *small* functions that are used often and therefore
 * have *no* prefix, to keep their names short.
 *
 * Utility functions have *no* dependencies on any other APIs, since they are
 * included first in order to make them available to all the APIs.
 * Miscellaneous functions that provide functionality on top of other APIS
 * are found in the helper_api.
 *
 * @package CoreAPI
 * @subpackage UtilityAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * converts a 1 value to X
 * converts a 0 value to a space
 * @param int $p_num boolean numeric
 * @return string X or space
 * @access public
 */
function trans_bool( $p_num ) {
	if( 0 == $p_num ) {
		return '&nbsp;';
	} else {
		return 'X';
	}
}

/**
 * Add a trailing DIRECTORY_SEPARATOR to a string if it isn't present
 * @param string $p_path
 * @return string
 * @access public
 */
function terminate_directory_path( $p_path ) {
	$str_len = strlen( $p_path );
	if( $p_path && $p_path[$str_len - 1] != DIRECTORY_SEPARATOR ) {
		$p_path = $p_path . DIRECTORY_SEPARATOR;
	}

	return $p_path;
}

/**
 * Return true if the parameter is an empty string or a string
 * containing only whitespace, false otherwise
 * @param string $p_var string to test
 * @return bool
 * @access public
 */
function is_blank( $p_var ) {
	$p_var = trim( $p_var );
	$str_len = strlen( $p_var );
	if( 0 == $str_len ) {
		return true;
	}
	return false;
}

/**
 * Get the named php ini variable but return it as a bool
 * @param string $p_name
 * @return bool
 * @access public
 */
function ini_get_bool( $p_name ) {
	$result = ini_get( $p_name );

	if( is_string( $result ) ) {
		switch( $result ) {
			case 'off':
			case 'false':
			case 'no':
			case 'none':
			case '':
			case '0':
				return false;
				break;
			case 'on':
			case 'true':
			case 'yes':
			case '1':
				return true;
				break;
		}
	} else {
		return (bool) $result;
	}
}

/**
 * Get the named php ini variable but return it as a number after converting "K" and "M"
 * @param string $p_name
 * @return int
 * @access public
 */
function ini_get_number( $p_name ) {
	$t_result = ini_get( $p_name );
	$t_val = spliti( 'M', $t_result );
	if( $t_val[0] != $t_result ) {
		return $t_val[0] * 1000000;
	}
	$t_val = spliti( 'K', $t_result );
	if( $t_val[0] != $t_result ) {
		return $t_val[0] * 1000;
	}
	return $t_result;
}

/**
 * Sort a multi-dimensional array by one of its keys
 * @param array $p_array Array to sort
 * @param string $p_key key to sort array on
 * @param int $p_direction sort direction
 * @return array sorted array
 * @access public
 */
function multi_sort( $p_array, $p_key, $p_direction = ASCENDING ) {
	if( DESCENDING == $p_direction ) {
		$t_factor = -1;
	} else {
		# might as well allow everything else to mean ASC rather than erroring
		$t_factor = 1;
	}

	if( empty( $p_array ) ) {
		return $p_array;
	}
	if( !is_array( current($p_array ) ) ) {
		error_parameters( 'tried to multisort an invalid multi-dimensional array' );
		trigger_error(ERROR_GENERIC, ERROR);
	}

	// Security measure: see http://www.mantisbt.org/bugs/view.php?id=9704 for details
	if( array_key_exists( $p_key, current($p_array) ) ) {
		$t_function = create_function( '$a, $b', "return $t_factor * strnatcasecmp( \$a['" . $p_key . "'], \$b['" . $p_key . "'] );" );
		uasort( $p_array, $t_function );
	} else {
		trigger_error(ERROR_INVALID_SORT_FIELD, ERROR);
	}
	return $p_array;
}

/**
 * Return GD version
 * It doesn't use gd_info() so it works with PHP < 4.3.0 as well
 * @return int represents gd version
 * @access public
 */
function get_gd_version() {
	$t_GDfuncList = get_extension_funcs( 'gd' );
	if( !is_array( $t_GDfuncList ) ) {
		return 0;
	} else {
		if( in_array( 'imagegd2', $t_GDfuncList ) ) {
			return 2;
		} else {
			return 1;
		}
	}
}

/**
 * return true or false if string matches current page name 
 * @param string $p_string page name
 * @return bool
 * @access public
 */
function is_page_name( $p_string ) {
	return isset( $_SERVER['PHP_SELF'] ) && ( 0 < strpos( $_SERVER['PHP_SELF'], $p_string ) );
}
