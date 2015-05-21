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
 * Utility API
 *
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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );

/**
 * converts a 1 value to X
 * converts a 0 value to a space
 * @param integer $p_num A numeric to translate as a boolean for display.
 * @return string X or space
 * @access public
 */
function trans_bool( $p_num ) {
	if( 0 == $p_num ) {
		return '&#160;';
	} else {
		return '<i class="fa fa-check fa-lg"></i>';
	}
}

/**
 * Add a trailing DIRECTORY_SEPARATOR to a string if it isn't present
 * @param string $p_path A string representing a file system path.
 * @return string
 * @access public
 */
function terminate_directory_path( $p_path ) {
	return rtrim( $p_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
}

/**
 * Return true if the parameter is an empty string or a string
 * containing only whitespace, false otherwise
 * @param string $p_var String to test whether it is blank.
 * @return boolean
 * @access public
 */
function is_blank( $p_var ) {
	$p_var = trim( $p_var );
	$t_str_len = strlen( $p_var );
	if( 0 == $t_str_len ) {
		return true;
	}
	return false;
}

/**
 * Get the named php ini variable but return it as a boolean
 * @param string $p_name A php.ini variable to evaluate.
 * @return boolean
 * @access public
 */
function ini_get_bool( $p_name ) {
	$t_result = ini_get( $p_name );

	if( is_string( $t_result ) ) {
		switch( strtolower( $t_result ) ) {
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
	}
	return (bool)$t_result;
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
 * @param array   $p_array     Array to sort.
 * @param string  $p_key       Array key to sort array on.
 * @param integer $p_direction Sort direction.
 * @return array sorted array
 * @access public
 */
function multi_sort( array $p_array, $p_key, $p_direction = ASCENDING ) {
	if( DESCENDING == $p_direction ) {
		$t_factor = -1;
	} else {
		# might as well allow everything else to mean ASC rather than erroring
		$t_factor = 1;
	}

	if( empty( $p_array ) ) {
		return $p_array;
	}
	if( !is_array( current( $p_array ) ) ) {
		error_parameters( 'tried to multisort an invalid multi-dimensional array' );
		trigger_error( ERROR_GENERIC, ERROR );
	}

	# Security measure: see http://www.mantisbt.org/bugs/view.php?id=9704 for details
	if( array_key_exists( $p_key, current( $p_array ) ) ) {
		uasort(
			$p_array,
			function( $a, $b ) use( $t_factor, $p_key ) {
				return $t_factor * strnatcasecmp( $a[$p_key], $b[$p_key] );
			}
		);
	} else {
		trigger_error( ERROR_INVALID_SORT_FIELD, ERROR );
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
 * @param string $p_string To test against the php script name.
 * @return boolean
 * @access public
 */
function is_page_name( $p_string ) {
	return isset( $_SERVER['SCRIPT_NAME'] ) && ( 0 < strpos( $_SERVER['SCRIPT_NAME'], $p_string ) );
}

/**
 * return true or false if the host operating system is windows
 * @return boolean
 * @access public
 */
function is_windows_server() {
	if( defined( 'PHP_WINDOWS_VERSION_MAJOR' ) ) {
		return (PHP_WINDOWS_VERSION_MAJOR > 0);
	}
	return ('WIN' == substr( PHP_OS, 0, 3 ) );
}

/**
 * return array of class properties (via reflection api)
 * @param string  $p_classname      Class name.
 * @param string  $p_type           Property type - public/private/protected/static.
 * @param boolean $p_return_object  Whether to return array of property objects.
 * @param boolean $p_include_parent Whether to include properties of parent classes.
 * @return array
 * @access public
 */
function getClassProperties( $p_classname, $p_type = 'public', $p_return_object = false, $p_include_parent = false ) {
	$t_ref = new ReflectionClass( $p_classname );
	$t_props = $t_ref->getProperties();
	$t_props_arr = array();
	foreach( $t_props as $t_prop ){
		$t_name = $t_prop->getName();
		if( $t_prop->isPublic() and (stripos( $p_type, 'public' ) === false) ) {
			continue;
		}
		if( $t_prop->isPrivate() and (stripos( $p_type, 'private' ) === false) ) {
			continue;
		}
		if( $t_prop->isProtected() and (stripos( $p_type, 'protected' ) === false) ) {
			continue;
		}
		if( $t_prop->isStatic() and (stripos( $p_type, 'static' ) === false) ) {
			continue;
		}
		if( $p_return_object ) {
			$t_props_arr[$t_name] = $t_prop;
		} else {
			$t_props_arr[$t_name] = true;
		}
	}
	if( $p_include_parent ) {
		if( $t_parentclass = $t_ref->getParentClass() ) {
			$t_parent_props_arr = getClassProperties( $t_parentclass->getName() );
			if( count( $t_parent_props_arr ) > 0 ) {
				$t_props_arr = array_merge( $t_parent_props_arr, $t_props_arr );
			}
		}
	}
	return $t_props_arr;
}

/**
 * return string of system font path
 * @access public
 * @return string representing system path to font location
 */
function get_font_path() {
		$t_font_path = config_get_global( 'system_font_folder' );
		if( $t_font_path == '' ) {
			if( is_windows_server() ) {
				$t_system_root = $_SERVER['SystemRoot'];
				if( empty( $t_system_root ) ) {
					return '';
				} else {
					$t_font_path = $t_system_root . '/fonts/';
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

/**
 * Return instance of fileinfo class if enabled in php
 * @return finfo
 */
function finfo_get_if_available() {
	if( class_exists( 'finfo' ) ) {
		$t_info_file = config_get( 'fileinfo_magic_db_file' );

		if( is_blank( $t_info_file ) ) {
			$t_finfo = new finfo( FILEINFO_MIME );
		} else {
			$t_finfo = new finfo( FILEINFO_MIME, $t_info_file );
		}

		if( $t_finfo ) {
			return $t_finfo;
		}
	}

	return null;
}
