<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: utility_api.php,v 1.11 2004-04-08 03:31:38 prescience Exp $
	# --------------------------------------------------------

	###########################################################################
	# Utility API
	###########################################################################

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
	function get_enum_to_string( $p_enum_string, $p_num ) {
		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			if ( $t_s[0] == $p_num ) {
				return $t_s[1];
			}
		}
		return '@' . $p_num . '@';
	}

	# --------------------
	# Contributed by Peter Palmreuther
	function mime_encode( $p_string='' ) {
		$output = '';
		for ( $i=0; $i<strlen( $p_string ); $i++ ) {
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
		if ( $p_path && $p_path[strlen($p_path)-1] != DIRECTORY_SEPARATOR ) {
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
		if ( strlen( trim( $p_var ) ) == 0 ) {
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
	# Sort a multi-dimensional array by one of its keys
	function multi_sort( $p_array, $p_key, $p_direction=ASC ) {
		if ( DESC == $p_direction ) {
			$t_factor = -1;
		} else {
			# might as well allow everything else to mean ASC rather than erroring
			$t_factor = 1;
		}

		$t_function = create_function( '$a, $b', "return $t_factor * strnatcasecmp( \$a['$p_key'], \$b['$p_key'] );" );
		uasort( $p_array, $t_function );
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
?>