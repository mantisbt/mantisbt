<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: helper_api.php,v 1.11 2002-08-30 08:36:50 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Helper API
	###########################################################################

	# These are miscellaneous functions to help the package

	# --------------------
	# Calculates the CRC given bug id and calling file name (use __FILE__).
	# It uses a configuration variable as a seed.
	function calc_crc ( $p_bug_id, $p_file ) {
		global $g_admin_crypt_word;
		$t_crc_str = sprintf("%s%s%07d", $g_admin_crypt_word, basename($p_file), (integer)$p_bug_id);
		return crc32($t_crc_str);
	}
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
	# check to see if user exists
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function check_user_exists( $p_user_id ) {
		global $g_mantis_user_table;

		$c_user_id = (integer)$p_user_id;

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_user_table ".
				"WHERE id='$c_user_id'";
		$result = db_query( $query );
		if ( 0 == db_result( $result, 0, 0 ) ) {
			print_header_redirect( 'main_page.php' );
		}
	}
	# --------------------
	# alternate color function
	function alternate_colors( $p_num, $p_color1='', $p_color2='' ) {
		global $g_primary_color1, $g_primary_color2;

		if ( empty( $p_color1 ) ) {
			$p_color1 = $g_primary_color1;
		}
		if ( empty( $p_color2 ) ) {
			$p_color2 = $g_primary_color2;
		}

		if ( 1 == $p_num % 2 ) {
			return $p_color1;
		} else {
			return $p_color2;
		}
	}
	# --------------------
	# get the color string for the given status
	function get_status_color( $p_status ) {
		global $g_status_enum_string, $g_status_colors, $g_custom_status_slot, $g_customize_attributes;


		# This code creates the appropriate variable name
		# then references that color variable
		# You could replace this with a bunch of if... then... else
		# statements

		if ($g_customize_attributes) {
			# custom colors : to be deleted when moving to manage_project_page.php
			$t_project_id = '0000000';

			# insert attriutes for color displaying in viex_bug_page.php
			insert_attributes( 'status', $t_project_id, 'global' );
			insert_attributes( 'status', $t_project_id, 'str' ) ;
		}

		$t_color_str = 'closed';
		$t_arr = explode_enum_string( $g_status_enum_string );
		$t_arr_count = count( $t_arr );
		for ( $i=0;$i<$t_arr_count;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_status ) {
				# now get the appropriate translation
				$t_color_str = $elem_arr[1];
				break;
			}
		}

		$t_color_variable_name = 'g_'.$t_color_str.'_color';
		global $$t_color_variable_name;
		if ( isset( $$t_color_variable_name ) ) {
			return $$t_color_variable_name;
		} elseif ( isset ( $g_status_colors[$t_color_str] ) ) {
			return $g_status_colors[$t_color_str];
		} elseif ($g_customize_attributes) {   // custom attributes
				# if not found before, look into custom status colors
				$t_colors_arr = attribute_get_all('colors', $t_project_id);
				$t_offset = ( $p_status-( $g_custom_status_slot[0]+1 ) );
				if ( isset( $t_colors_arr[$t_offset ]) ) {
					return $t_colors_arr[$t_offset];
				}
		}
		return '#ffffff';
	}
	# --------------------
	# Get the default project of a user
	function get_default_project( $p_user_id ) {
		global $g_mantis_user_pref_table;

		$c_user_id = (integer)$p_user_id;

		$query ="SELECT default_project ".
				"FROM $g_mantis_user_pref_table ".
				"WHERE user_id='$c_user_id' ".
				"LIMIT 1";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
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
	# Given a enum string and num, return the appropriate string
	function get_enum_element( $p_enum_name, $p_val ) {
		$g_var = 'g_'.$p_enum_name.'_enum_string';
		$s_var = 's_'.$p_enum_name.'_enum_string';
		global $$g_var, $$s_var, $g_customize_attributes;

		# custom attributes
		if ($g_customize_attributes) {
			# to be deleted when moving to manage_project_page.php
			$t_project_id = '0000000';

			# custom attributes insertion
			insert_attributes( $p_enum_name, $t_project_id, 'global' );
			insert_attributes( $p_enum_name, $t_project_id, 'str' ) ;
		}
		# use the global enum string to search
		$t_arr = explode_enum_string( $$g_var );
		$t_arr_count = count( $t_arr );
		for ( $i=0;$i<$t_arr_count;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_val ) {
				# now get the appropriate translation
				return get_enum_to_string( $$s_var, $p_val );
			}
		}
		return '@null@';
	}
	# --------------------
	# Returns the specified field of the current project
	function get_current_project_field( $p_field_name ) {
		global $g_project_cookie_val;

		return project_get_field ( $g_project_cookie_val, $p_field_name );
	}
	# --------------------
	# Some proxies strip out HTTP_REFERER.
	# This function helps determine which pages to redirect to
	# based on site and user preference.
	function get_view_redirect_url( $p_bug_id, $p_no_referer=0 ) {
		global $HTTP_REFERER, $g_show_view;

		if ( ( !isset( $HTTP_REFERER ) ) ||
			 ( empty( $HTTP_REFERER ) ) ||
			 ( 1 == $p_no_referer ) ) {
			switch ( $g_show_view ) {
				case BOTH:
						if ( ON == current_user_get_pref( 'advanced_view' ) ) {
							return 'view_bug_advanced_page.php?f_id='.$p_bug_id;
						} else {
							return 'view_bug_page.php?f_id='.$p_bug_id;
						}
				case SIMPLE_ONLY:
						return 'view_bug_page.php?f_id='.$p_bug_id;
				case ADVANCED_ONLY:
						return 'view_bug_advanced_page.php?f_id='.$p_bug_id;
				default:return 'view_bug_page.php?f_id='.$p_bug_id;
			}
		} else {
			return $HTTP_REFERER;
		}
	}
	# --------------------
	# Some proxies strip out HTTP_REFERER.
	# This function helps determine which pages to redirect to
	# based on site and user preference.
	function get_report_redirect_url( $p_no_referer=0 ) {
		global $HTTP_REFERER, $g_show_report;

		if ( ( !isset( $HTTP_REFERER ) ) ||
			 ( empty( $HTTP_REFERER ) ) ||
			 ( 1 == $p_no_referer ) ) {
			switch( $g_show_report ) {
				case BOTH:
						if ( ON == current_user_get_pref( 'advanced_report' ) ) {
							return 'bug_add_advanced_page.php';
		 				} else {
							return 'bug_add_page.php';
						}
				case SIMPLE_ONLY:
						return 'bug_add_page.php';
				case ADVANCED_ONLY:
						return 'bug_add_advanced_page.php';
				default:return 'bug_add_page.php';
			}
		} else {
			return $HTTP_REFERER;
		}
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
	# File type check
	function file_type_check( $p_file_name ) {
		global $g_allowed_files, $g_disallowed_files;

		# grab extension
		$t_ext_array = explode( '.', $p_file_name );
		$last_position = count( $t_ext_array )-1;
		$t_extension = $t_ext_array[$last_position];

		# check against disallowed files
		$t_disallowed_arr =  explode_enum_string( $g_disallowed_files );
		foreach ( $t_disallowed_arr as $t_val ) {
		    if ( $t_val == $t_extension ) {
		    	return false;
		    }
		}

		# check against allowed files
		$t_allowed_arr = explode_enum_string( $g_allowed_files );
		# if the allowed list is populated then the file must be in the list.
		if ( empty( $g_allowed_files ) ) {
			return true;
		}
		foreach ( $t_allowed_arr as $t_val ) {
		    if ( $t_val == $t_extension ) {
				return true;
		    }
		}
		return false;
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
	# If $p_var and $p_val are euqal to each other then we echo SELECTED
	# This is used when we want to know if a variable indicated a certain
	# option element is selected
	function check_selected( $p_var, $p_val ) {
		if ( $p_var == $p_val ) {
			echo 'selected="selected"';
		}
	}
	# --------------------
	# If $p_var and $p_val are euqal to each other then we echo CHECKED
	# This is used when we want to know if a variable indicated a certain
	# element is checked
	function check_checked( $p_var, $p_val ) {
		if ( $p_var == $p_val ) {
			echo 'checked="checked"';
		}
	}
	# --------------------
	# Return the current project id as stored in a cookie
	function helper_get_current_project() {
		$t_cookie_name = config_get( 'project_cookie' );

		return gpc_get_cookie( $t_cookie_name, '' );
	}
	# --------------------
	# Add a trailing DIRECTORY_SEPARATOR to a string if it isn't present
	function helper_terminate_directory_path( $p_path ) {
		if ( $p_path && $p_path[strlen($p_path)-1] != DIRECTORY_SEPARATOR ) {
			$p_path = $p_path.DIRECTORY_SEPARATOR;
		}

		return $p_path;
	}
?>