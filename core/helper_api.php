<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: helper_api.php,v 1.52 2004-09-28 23:00:48 vboctor Exp $
	# --------------------------------------------------------

	### Helper API ###

	# These are miscellaneous functions

	# --------------------
	# alternate color function
	#  If no index is given, continue alternating based on the last index given
	function helper_alternate_colors( $p_index, $p_odd_color, $p_even_color ) {
		static $t_index = 1;

		if ( null !== $p_index ) {
			$t_index = $p_index;
		}

		if ( 1 == $t_index++ % 2 ) {
			return $p_odd_color;
		} else {
			return $p_even_color;
		}
	}
	# --------------------
	# alternate classes for table rows
	#  If no index is given, continue alternating based on the last index given
	function helper_alternate_class( $p_index=null, $p_odd_class="row-1", $p_even_class="row-2" ) {
		static $t_index = 1;

		if ( null !== $p_index ) {
			$t_index = $p_index;
		}

		if ( 1 == $t_index++ % 2 ) {
			return "class=\"$p_odd_class\"";
		} else {
			return "class=\"$p_even_class\"";
		}
	}
	# --------------------
	# get the color string for the given status
	function get_status_color( $p_status ) {
		$t_status_enum_string	= config_get( 'status_enum_string' );
		$t_status_colors		= config_get( 'status_colors' );

		# This code creates the appropriate variable name
		# then references that color variable
		# You could replace this with a bunch of if... then... else
		# statements

		$t_color_str	= 'closed';
		$t_arr			= explode_enum_string( $t_status_enum_string );
		$t_arr_count	= count( $t_arr );
		for ( $i=0; $i < $t_arr_count ;$i++ ) {
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
		}

		return '#ffffff';
	}
	# --------------------
	# Given a enum string and num, return the appropriate string
	function get_enum_element( $p_enum_name, $p_val ) {
		$config_var = config_get( $p_enum_name.'_enum_string' );
		$string_var = lang_get(  $p_enum_name.'_enum_string' );

		# use the global enum string to search
		$t_arr			= explode_enum_string( $config_var );
		$t_arr_count	= count( $t_arr );
		for ( $i=0; $i < $t_arr_count ;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_val ) {
				# now get the appropriate translation
				return get_enum_to_string( $string_var, $p_val );
			}
		}
		return '@' . $p_val . '@';
	}
	# --------------------
	# If $p_var is not an array and is equal to $p_val then we PRINT SELECTED.
	# If $p_var is an array, then if any member is equal to $p_val we PRINT SELECTED.
	# This is used when we want to know if a variable indicated a certain
	# option element is selected
	#
	# If the second parameter is not given, the first parameter is compared
	#  to the boolean value true
	function check_selected( $p_var, $p_val=true ) {
		if ( is_array( $p_var ) ) {
			foreach( $p_var as $p_this_var ) {
				if ( $p_this_var == $p_val ) {
					PRINT ' selected="selected" ';
					return;
				}
			}
		} else {
			if ( $p_var == $p_val ) {
				PRINT ' selected="selected" ';
			}
		}
	}
	# --------------------
	# If $p_var and $p_val are equal to each other then we PRINT CHECKED
	# This is used when we want to know if a variable indicated a certain
	# element is checked
	#
	# If the second parameter is not given, the first parameter is compared
	#  to the boolean value true
	function check_checked( $p_var, $p_val=true ) {
		if ( $p_var == $p_val ) {
			PRINT ' checked="checked" ';
		}
	}

	# --------------------
	# Set up PHP for a long process execution
	# The script timeout is set based on the value of the
	#  long_process_timeout config option.
	# $p_ignore_abort specified whether to ignore user aborts by hitting
	#  the Stop button (the default is not to ignore user aborts)
	function helper_begin_long_process( $p_ignore_abort=false ) {
		$t_timeout = config_get( 'long_process_timeout' );

		# silent errors or warnings reported when safe_mode is ON.
		@set_time_limit( $t_timeout );

		ignore_user_abort( $p_ignore_abort );
		return $t_timeout;
	}

	# --------------------
	# Return the current project id as stored in a cookie
	#  If no cookie exists, the user's default project is returned
	function helper_get_current_project() {
		$t_cookie_name = config_get( 'project_cookie' );

		$t_project_id = gpc_get_cookie( $t_cookie_name, null );

		if ( null === $t_project_id ) {
			$t_project_id = current_user_get_pref( 'default_project' );
		}

		if ( !project_exists( $t_project_id ) ||
			 ( 0 == project_get_field( $t_project_id, 'enabled' ) ) ||
			 !access_has_project_level( VIEWER, $t_project_id ) ) {
			$t_project_id = ALL_PROJECTS;
		}

		return (int)$t_project_id;
	}
	# --------------------
	# Set the current project id (stored in a cookie)
	function helper_set_current_project( $p_project_id ) {
		$t_project_cookie_name	= config_get( 'project_cookie' );

		gpc_set_cookie( $t_project_cookie_name, $p_project_id, true );

		return true;
	}
	# --------------------
	# Clear all known user preference cookies
	function helper_clear_pref_cookies() {
		gpc_clear_cookie( config_get( 'project_cookie' ) );
		gpc_clear_cookie( config_get( 'manage_cookie' ) );
	}
	# --------------------
	# Check whether the user has confirmed this action.
	#
	# If the user has not confirmed the action, generate a page which asks
	#  the user to confirm and then submits a form back to the current page
	#  with all the GET and POST data and an additional field called _confirmed
	#  to indicate that confirmation has been done.
	function helper_ensure_confirmed( $p_message, $p_button_label ) {
		if (true == gpc_get_bool( '_confirmed' ) ) {
			return true;
		}

		global $PHP_SELF;
		if ( !php_version_at_least( '4.1.0' ) ) {
			global $_POST, $_GET;
		}

		html_page_top1();
		html_page_top2();

		# @@@ we need to improve this formatting.  I'd like the text to only
		#  be about 50% the width of the screen so that it doesn't become to hard
		#  to read.

		PRINT "<br />\n<div align=\"center\">\n";
		print_hr();
		PRINT "\n$p_message\n";

		PRINT '<form method="post" action="' . $PHP_SELF . "\">\n";

		print_hidden_inputs( gpc_strip_slashes( $_POST ) );
		print_hidden_inputs( gpc_strip_slashes( $_GET ) );

		PRINT "<input type=\"hidden\" name=\"_confirmed\" value=\"1\" />\n";
		PRINT '<br /><br /><input type="submit" class="button" value="' . $p_button_label . '" />';
		PRINT "\n</form>\n";

		print_hr();
		PRINT "</div>\n";
		html_page_bottom1();
		exit;
	}

	# --------------------
	# Call custom function.
	#
	# $p_function - Name of function to call (eg: do_stuff).  The function will call custom_function_override_do_stuff()
	#		if found, otherwise, will call custom_function_default_do_stuff().
	# $p_args_array - Parameters to function as an array
	function helper_call_custom_function( $p_function, $p_args_array ) {
		$t_function = 'custom_function_override_' . $p_function;

		if ( !function_exists( $t_function ) ) {
			$t_function = 'custom_function_default_' . $p_function;
		}

		return call_user_func_array( $t_function, $p_args_array );
	}
?>