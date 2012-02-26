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
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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
		return '&#160;';
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
	return rtrim( $p_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
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
		switch( strtolower( $result ) ) {
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
 * Get the named php.ini variable but return it as a number after converting
 * the giga (g/G), mega (m/M) and kilo (k/K) postfixes. These postfixes do not
 * adhere to IEEE 1541 in that k=1024, not k=1000. For more information see
 * http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 * @param string $p_name Name of the configuration option to read.
 * @return int Integer value of the configuration option.
 * @access public
 */
function ini_get_number( $p_name ) {
	$t_value = ini_get( $p_name );

	$t_result = 0;
	switch( substr( $t_value, -1 ) ) {
		case 'G':
		case 'g':
			$t_result = (int)$t_value * 1073741824;
			break;
		case 'M':
		case 'm':
			$t_result = (int)$t_value * 1048576;
			break;
		case 'K':
		case 'k':
			$t_result = (int)$t_value * 1024;
			break;
		default:
			$t_result = (int)$t_value;
			break;
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
	return isset( $_SERVER['SCRIPT_NAME'] ) && ( 0 < strpos( $_SERVER['SCRIPT_NAME'], $p_string ) );
}

/**
 * A function that determines whether the logo should be centered or left aligned based on the page.
 * @return bool true: centered, false: otherwise.
 * @access public
 */
function should_center_logo() {
	return ( is_page_name( 'login_page' ) || is_page_name( 'signup_page' ) || is_page_name( 'signup' ) || is_page_name( 'lost_pwd_page' ) );
}

function is_windows_server() {
	if( defined( 'PHP_WINDOWS_VERSION_MAJOR' ) ) {
		return (PHP_WINDOWS_VERSION_MAJOR > 0);
	}
	return ('WIN' == substr( PHP_OS, 0, 3 ) );
}

function getClassProperties($className, $types='public', $return_object = false, $include_parent = false ) {
	$ref = new ReflectionClass($className);
	$props = $ref->getProperties();
	$props_arr = array();
	foreach($props as $prop){
		$f = $prop->getName();
		if($prop->isPublic() and (stripos($types, 'public') === FALSE)) continue;
		if($prop->isPrivate() and (stripos($types, 'private') === FALSE)) continue;
		if($prop->isProtected() and (stripos($types, 'protected') === FALSE)) continue;
		if($prop->isStatic() and (stripos($types, 'static') === FALSE)) continue;
		if ( $return_object )
			$props_arr[$f] = $prop;
		else
			$props_arr[$f] = true;
	}
	if ( $include_parent ) {
		if($parentClass = $ref->getParentClass()){
			$parent_props_arr = getClassProperties($parentClass->getName());//RECURSION
			if(count($parent_props_arr) > 0)
				$props_arr = array_merge($parent_props_arr, $props_arr);
		}
	}
	return $props_arr;
}

function get_font_path() {
		$t_font_path = config_get_global( 'system_font_folder' );
		if( $t_font_path == '' ) {
			if ( is_windows_server() ) {
				$sroot = $_SERVER['SystemRoot'];
				if( empty($sroot) ) {
					return '';
				} else {
					$t_font_path = $sroot.'/fonts/';
				}
			} else {
				if( file_exists( '/usr/share/fonts/corefonts/' ) ) {
					$t_font_path = '/usr/share/fonts/corefonts/';
				} else if( file_exists( '/usr/share/fonts/truetype/msttcorefonts/' ) ) {
					$t_font_path = '/usr/share/fonts/truetype/msttcorefonts/';
				} else if( file_exists( '/usr/share/fonts/msttcorefonts/' ) ) {
					$t_font_path = '/usr/share/fonts/msttcorefonts/';
				} else {
					$t_font_path = '/usr/share/fonts/truetype/';
				}
			}
		}
		return $t_font_path;
}

function finfo_get_if_available() {
	
	if ( class_exists( 'finfo' ) ) {
		$t_info_file = config_get( 'fileinfo_magic_db_file' );
	
		if ( is_blank( $t_info_file ) ) {
			$finfo = new finfo( FILEINFO_MIME );
		} else {
			$finfo = new finfo( FILEINFO_MIME, $t_info_file );
		}
	
		if ( $finfo ) {
			return $finfo;
		}
	}

	return null;
}