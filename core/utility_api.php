<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: utility_api.php,v 1.3 2003-01-03 03:24:25 jfitzell Exp $
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
		return '@null@';
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

?>