<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: helper_api.php,v 1.15 2002-09-01 01:23:23 vboctor Exp $
	# --------------------------------------------------------

	###########################################################################
	# Helper API
	###########################################################################

	# These are miscellaneous functions to help the package

	# --------------------
	# Calculates the CRC given bug id and calling file name (use __FILE__).
	# It uses a configuration variable as a seed.
	function helper_calc_crc ( $p_bug_id, $p_file ) {
		$t_crc_str = sprintf("%s%s%07d", config_get( 'admin_crypt_word' ), basename($p_file), (integer)$p_bug_id);
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
	# alternate color function
	function alternate_colors( $p_num, $p_color1='', $p_color2='' ) {
		if ( empty( $p_color1 ) ) {
			$p_color1 = config_get( 'primary_color1' );
		}
		if ( empty( $p_color2 ) ) {
			$p_color2 = config_get( 'primary_color2' );
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
		$t_status_enum_string = config_get( 'status_enum_string' );
		$t_status_colors = config_get( 'status_colors' );
		$t_custom_status_slot = config_get( 'custom_status_slot' );
		$t_customize_attributes = config_get( 'customize_attributes' );
		

		# This code creates the appropriate variable name
		# then references that color variable
		# You could replace this with a bunch of if... then... else
		# statements

		if ($t_customize_attributes) {
			# custom colors : to be deleted when moving to manage_project_page.php
			$t_project_id = '0000000';

			# insert attriutes for color displaying in viex_bug_page.php
			attribute_insert( 'status', $t_project_id, 'global' );
			attribute_insert( 'status', $t_project_id, 'str' ) ;
		}

		$t_color_str = 'closed';
		$t_arr = explode_enum_string( $t_status_enum_string );
		$t_arr_count = count( $t_arr );
		for ( $i=0;$i<$t_arr_count;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_status ) {
				# now get the appropriate translation
				$t_color_str = $elem_arr[1];
				break;
			}
		}

		$t_color_variable_name = $t_color_str.'_color';
		if ( config_is_set( $t_color_variable_name ) ) {
			return config_get( $t_color_variable_name );
		} elseif ( isset ( $t_status_colors[$t_color_str] ) ) {
			return $t_status_colors[$t_color_str];
		} elseif ($t_customize_attributes) {   // custom attributes
				# if not found before, look into custom status colors
				$t_colors_arr = attribute_get_all('colors', $t_project_id);
				$t_offset = ( $p_status-( $t_custom_status_slot[0]+1 ) );
				if ( isset( $t_colors_arr[$t_offset ]) ) {
					return $t_colors_arr[$t_offset];
				}
		}
		return '#ffffff';
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
		$config_var = config_get( $p_enum_name.'_enum_string' );
		$string_var = lang_get(  $p_enum_name.'_enum_string' );
		$t_customize_attributes = config_get( 'customize_attributes' );

		# custom attributes
		if ($t_customize_attributes) {
			# to be deleted when moving to manage_project_page.php
			$t_project_id = '0000000';

			# custom attributes insertion
			attribute_insert( $p_enum_name, $t_project_id, 'global' );
			attribute_insert( $p_enum_name, $t_project_id, 'str' ) ;
		}
		# use the global enum string to search
		$t_arr = explode_enum_string( $config_var );
		$t_arr_count = count( $t_arr );
		for ( $i=0;$i<$t_arr_count;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_val ) {
				# now get the appropriate translation
				return get_enum_to_string( $string_var, $p_val );
			}
		}
		return '@null@';
	}
	# --------------------
	# Some proxies strip out HTTP_REFERER.
	# This function helps determine which pages to redirect to
	# based on site and user preference.
	function get_view_redirect_url( $p_bug_id, $p_no_referer=false ) {
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_SERVER;
		}

		$t_show_view = config_get( 'show_view' );

		if ( ( !isset( $_SERVER['HTTP_REFERER'] ) ) ||
			 ( empty( $_SERVER['HTTP_REFERER'] ) ) ||
			 ( true == $p_no_referer ) ) {
			switch ( $t_show_view ) {
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
			return $_SERVER['HTTP_REFERER'];
		}
	}
	# --------------------
	# Some proxies strip out HTTP_REFERER.
	# This function helps determine which pages to redirect to
	# based on site and user preference.
	function get_report_redirect_url( $p_no_referer=false ) {
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_SERVER;
		}

		$t_show_report = config_get( 'show_report' );

		if ( ( !isset( $_SERVER['HTTP_REFERER'] ) ) ||
			 ( empty( $_SERVER['HTTP_REFERER'] ) ) ||
			 ( true == $p_no_referer ) ) {
			switch( $t_show_report ) {
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
			return $_SERVER['HTTP_REFERER'];
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
			echo ' selected="selected" ';
		}
	}
	# --------------------
	# If $p_var and $p_val are euqal to each other then we echo CHECKED
	# This is used when we want to know if a variable indicated a certain
	# element is checked
	function check_checked( $p_var, $p_val ) {
		if ( $p_var == $p_val ) {
			echo ' checked="checked" ';
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