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
	# $Id: utility_api.php,v 1.22.2.1 2007-10-13 22:35:47 giallu Exp $
	# --------------------------------------------------------

	### Utility API ###

	# Utility functions are *small* functions that are used often and therefore
	#  have *no* prefix, to keep their names short.
	#
	# Utility functions have *no* dependencies on any other APIs, since they are
	#  included first in order to make them available to all the APIs.
	#  Miscellaneous functions that provide functionality on top of other APIS
	#  are found in the helper_api.

	# --------------------
	# converts a 1 value to X
	# converts a 0 value to a space
	function trans_bool( $p_num ) {
		if ( 0 == $p_num ) {
			return '&nbsp;';
		} else {
			return 'X';
		}
	}

	# --------------------
	# Breaks up an enum string into num:value elements
	function explode_enum_string( $p_enum_string ) {
		return explode( ',', $p_enum_string );
	}

	# --------------------
	# Given one num:value pair it will return both in an array
	# num will be first (element 0) value second (element 1)
	function explode_enum_arr( $p_enum_elem ) {
		return explode( ':', $p_enum_elem );
	}

	# --------------------
	# Get the string associated with the $p_enum value
	function get_enum_to_array( $p_enum_string ) {
		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0; $i < $enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$t_index = (int) $t_s[0];
			$t_array[$t_index] = $t_s[1];
		}
		return $t_array;
	}

	# --------------------
	# Get the string associated with the $p_enum value
	function get_enum_to_string( $p_enum_string, $p_num ) {
		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0; $i < $enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			if ( $t_s[0] == $p_num ) {
				return $t_s[1];
			}
		}
		return '@' . $p_num . '@';
	}

	# --------------------
	# Contributed by Peter Palmreuther
	function mime_encode( $p_string = '' ) {
		$output = '';
		$str_len = strlen( $p_string );
		for ( $i=0; $i < $str_len; $i++ ) {
			if (( ord( $p_string[$i] ) < 33 ) ||
				( ord( $p_string[$i] ) > 127 ) ||
				( eregi( "[\%\[\]\{\}\(\)]", $p_string[$i] ) )) {
				$output .= sprintf( '%%%02X', ord( $p_string[$i] ) );
			} else {
				$output .= $p_string[$i];
			}
		}
		return( $output );
	}

	# --------------------
	# This function checks to see if a variable is set
	# if it is not then it assigns the default value
	# otherwise it does nothing
	function check_varset( &$p_var, $p_default_value ) {
	     if ( !isset( $p_var ) ) {
	         $p_var = $p_default_value;
	     }
	}

	# --------------------
	# Add a trailing DIRECTORY_SEPARATOR to a string if it isn't present
	function terminate_directory_path( $p_path ) {
		$str_len = strlen($p_path);
		if ( $p_path && $p_path[$str_len-1] != DIRECTORY_SEPARATOR ) {
			$p_path = $p_path.DIRECTORY_SEPARATOR;
		}

		return $p_path;
	}

	# --------------------
	# Print a debug string by generating a notice
	function debug( $p_string ) {
		trigger_error( $p_string, NOTICE );
	}

	# --------------------
	# Return true if the parameter is an empty string or a string
	#  containing only whitespace, false otherwise
	function is_blank( $p_var ) {
		$p_var = trim( $p_var );
		$str_len = strlen( $p_var );
		if ( 0 == $str_len ) {
			return true;
		}
		return false;
	}

	# --------------------
	# Get the named php ini variable but return it as a bool
	function ini_get_bool( $p_name ) {
		$result = ini_get( $p_name );

		if ( is_string( $result ) ) {
			switch ( $result ) {
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
			return (bool)$result;
		}
	}

	# --------------------
	# Get the named php ini variable but return it as a number after converting "K" and "M"
	function ini_get_number( $p_name ) {
		$t_result = ini_get( $p_name );
		$t_val = spliti( 'M', $t_result);
		if ( $t_val[0] != $t_result ) {
			return $t_val[0] * 1000000;
		}
		$t_val = spliti( 'K', $t_result);
		if ( $t_val[0] != $t_result ) {
			return $t_val[0] * 1000;
		}
		return $t_result;
	}



	# --------------------
	# Sort a multi-dimensional array by one of its keys
	function multi_sort( $p_array, $p_key, $p_direction=ASCENDING ) {
		if ( DESCENDING == $p_direction ) {
			$t_factor = -1;
		} else {
			# might as well allow everything else to mean ASC rather than erroring
			$t_factor = 1;
		}

		// Security measure: see http://www.mantisbt.org/bugs/view.php?id=9704 for details
		if ( array_key_exists( $p_key, $p_array ) ) {
			$t_function = create_function( '$a, $b', "return $t_factor * strnatcasecmp( \$a['$p_key'], \$b['$p_key'] );" );
			uasort( $p_array, $t_function );
		}
		return $p_array;
	}

	# --------------------
	# Copies item with given key from source array to the destination,
	# if the key exists in the source. If not - does nothing.
	function copy_array_item_if_exist( &$p_arr_src, &$p_arr_dst, $key ) {
		if( array_key_exists( $key, $p_arr_src ) ) {
			$p_arr_dst[$key] = $p_arr_src[$key];
		}
	}

	# --------------------
	# Return GD version
	# It doesn't use gd_info() so it works with PHP < 4.3.0 as well
	function get_gd_version()
	{
		$t_GDfuncList = get_extension_funcs('gd');
		if( ! is_array( $t_GDfuncList ) ) {
			return 0;
		} else {
			if( in_array('imagegd2',$t_GDfuncList) ) {
				return 2;
			} else {
				return 1;
			}
		}
	}
	
	# ---------------------
	# return true or false if string matches current page name
	function is_page_name( $p_string ) {
	   return isset( $_SERVER['PHP_SELF'] ) && ( 0 < strpos( $_SERVER['PHP_SELF'], $p_string ) );
	}
	
?>
