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
	return (bool) $t_result;
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
 * return true or false if the host operating system is windows
 * @return bool
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
 * @param string $p_classname class name
 * @param string $p_type property type - public/private/protected/static
 * @param bool $p_return_object whether to return array of property objects
 * @param bool $p_include_parent whether to include properties of parent classes
 * @return bool
 * @access public
 */
function getClassProperties($p_classname, $p_type='public', $p_return_object = false, $p_include_parent = false ) {
	$t_ref = new ReflectionClass($p_classname);
	$t_props = $t_ref->getProperties();
	$t_props_arr = array();
	foreach($t_props as $t_prop){
		$t_name = $t_prop->getName();
		if($t_prop->isPublic() and (stripos($p_type, 'public') === FALSE)) continue;
		if($t_prop->isPrivate() and (stripos($p_type, 'private') === FALSE)) continue;
		if($t_prop->isProtected() and (stripos($p_type, 'protected') === FALSE)) continue;
		if($t_prop->isStatic() and (stripos($p_type, 'static') === FALSE)) continue;
		if ( $p_return_object )
			$t_props_arr[$t_name] = $t_prop;
		else
			$t_props_arr[$t_name] = true;
	}
	if ( $p_include_parent ) {
		if($t_parentclass = $t_ref->getParentClass()){
			$t_parent_props_arr = getClassProperties($t_parentclass->getName());//RECURSION
			if(count($t_parent_props_arr) > 0)
				$t_props_arr = array_merge($t_parent_props_arr, $t_props_arr);
		}
	}
	return $t_props_arr;
}

/**
 * return string of system font path
 * @access public
 */
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

/**
 * Helper function to recursively process complex types
 * We support the following kind of variables here:
 * 1. constant values (like the ON/OFF switches): they are defined as constants mapping to numeric values
 * 2. simple arrays with the form: array( a, b, c, d )
 * 3. associative arrays with the form: array( a=>1, b=>2, c=>3, d=>4 )
 * 4. multi-dimensional arrays
 * commas and '=>' within strings are handled
 *
 * @param string $p_value Complex value to process
 * @return parsed variable
 */
function process_complex_value( $p_value, $p_trimquotes = false ) {
	static $s_regex_array = null;
	static $s_regex_string = null;
	static $s_regex_element = null;

	$t_value = trim( $p_value );

	# Parsing regex initialization
	if( is_null( $s_regex_array ) ) {
		$s_regex_array = '^array[\s]*\((.*)\)$';
		$s_regex_string =
			# unquoted string (word)
			'[\w]+' . '|' .
			# single-quoted string
			"'(?:[^'\\\\]|\\\\.)*'" . '|' .
			# double-quoted string
			'"(?:[^"\\\\]|\\\\.)*"';
		# The following complex regex will parse individual array elements,
		# taking into consideration sub-arrays, associative arrays and single,
		# double and un-quoted strings
		# @TODO dregad reverse pattern logic for sub-array to avoid match on array(xxx)=>array(xxx)
		$s_regex_element = '('
			# Main sub-pattern - match one of
			. '(' .
					# sub-array: ungreedy, no-case match ignoring nested parenthesis
					'(?:(?iU:array\s*(?:\\((?:(?>[^()]+)|(?1))*\\))))' . '|' .
					$s_regex_string
			. ')'
			# Optional pattern for associative array, back-referencing the
			# above main pattern
			. '(?:\s*=>\s*(?2))?' .
			')';
	}

	if( preg_match( "/$s_regex_array/s", $t_value, $t_match ) === 1 ) {
		# It's an array - process each element
		$t_processed = array();

		if( preg_match_all( "/$s_regex_element/", $t_match[1], $t_elements ) ) {
			foreach( $t_elements[0] as $key => $element ) {
				if ( !trim( $element ) ) {
					# Empty element - skip it
					continue;
				}
				# Check if element is associative array
				preg_match_all( "/($s_regex_string)\s*=>\s*(.*)/", $element, $t_split );
				if( !empty( $t_split[0] ) ) {
					# associative array
					$t_new_key = constant_replace( trim( $t_split[1][0], " \t\n\r\0\x0B\"'" ) );
					$t_new_value = process_complex_value( $t_split[2][0], true );
					$t_processed[$t_new_key] = $t_new_value;
				} else {
					# regular array
					$t_new_value = process_complex_value( $element );
					$t_processed[$key] = $t_new_value;
				}
			}
		}
		return $t_processed;
	} else {
		# Scalar value
		if( $p_trimquotes ) {
			$t_value = trim( $t_value, " \t\n\r\0\x0B\"'" );
		}
		return constant_replace( $t_value );
	}
}

/**
 * Split by commas, but ignore commas that are within quotes or parenthesis.
 * Ignoring commas within parenthesis helps allow for multi-dimensional arrays.
 * @param $p_string string to split
 * @return array
 */
function special_split ( $p_string ) {
	$t_values = array();
	$t_array_element = "";
	$t_paren_level = 0;
	$t_inside_quote = False;
	$t_escape_next = False;

	foreach( str_split( trim( $p_string ) ) as $character ) {
		if( $t_escape_next ) {
			$t_array_element .= $character;
			$t_escape_next = False;
		} else if( $character == "," && $t_paren_level==0 && !$t_inside_quote ) {
			array_push( $t_values, $t_array_element );
			$t_array_element = "";
		} else {
			if( $character == "(" && !$t_inside_quote ) {
				$t_paren_level ++;
			} else if( $character == ")" && !$t_inside_quote ) {
				$t_paren_level --;
			} else if( $character == "'" ) {
				$t_inside_quote = !$t_inside_quote;
			} else if( $character == "\\" ) {
				# escape character
				$t_escape_next = true;
				# keep the escape if the string will be going through another recursion
				if( $t_paren_level > 0 ) {
					$t_array_element .= $character;
				}
				continue;
			}
			$t_array_element .= $character;
		}
	}
	array_push( $t_values, $t_array_element );
	return $t_values;
}


/**
 * Check if the passed string is a constant and returns its value
 * if yes, or the string itself if not
 * @param $p_name string to check
 * @return mixed|string value of constant $p_name, or $p_name itself
 */
function constant_replace( $p_name ) {
	if ( is_string( $p_name ) && defined( $p_name ) ) {
		# we have a constant
		return constant( $p_name );
	}
	return $p_name;
}