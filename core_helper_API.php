<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Helper API
	###########################################################################

	# These are miscellaneous functions to help the package

	# --------------------
	# updates the last_updated field
	function bug_date_update( $p_bug_id ) {
		global $g_mantis_bug_table;

		$query = "UPDATE last_updated
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
	}
	# --------------------
	# updates the last_modified field
	function bugnote_date_update( $p_bugnote_id ) {
		global $g_mantis_bugnote_table;

		$query = "UPDATE last_modified
				FROM $g_mantis_bugnote_table
				WHERE id='$p_bugnote_id'";
		$result = db_query( $query );
	}
	# --------------------
	# Returns the specified field value of the specified bug
	function get_bug_field( $p_field_name, $p_bug_id ) {
		global 	$g_string_cookie_val,
				$g_mantis_bug_table;

		# get info
		$query = "SELECT $p_field_name
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# checks to see if the category is a duplicate
	# we do it this way because each different project can have the same category names
	function is_duplicate_category( $p_category, $p_project_id ) {
		global $g_mantis_project_category_table;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_category_table
				WHERE project_id='$p_project_id' AND
					category='$p_category'";
		$result = db_query( $query );
		$category_count =  db_result( $result, 0, 0 );
		if ( $category_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# checks to see if the version is a duplicate
	# we do it this way because each different project can have the same category names
	function is_duplicate_version( $p_version, $p_project_id, $p_date_order='0' ) {
		global $g_mantis_project_version_table;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_version_table
				WHERE project_id='$p_project_id' AND
					version='$p_version' AND
					date_order='$p_date_order'";
		$result = db_query( $query );
		$version_count =  db_result( $result, 0, 0 );
		if ( $version_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# converts a 1 value to X
	# converts a 0 value to a space
	function trans_bool( $p_num ) {
		if ( 0 == $p_num ) {
			return "&nbsp;";
		} else {
			return "X";
		}
	}
	# --------------------
	# check to see if bug exists
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function check_bug_exists( $p_bug_id ) {
		global $g_mantis_bug_table, $g_main_page;

		$query = "SELECT *
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		if ( 0 == db_num_rows( $result ) ) {
			print_header_redirect( $g_main_page );
		}
	}
	# --------------------
	# check to see if bugnote exists
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function check_bugnote_exists( $p_bugnote_id ) {
		global $g_mantis_bugnote_table, $g_main_page;

		$query = "SELECT *
				FROM $g_mantis_bugnote_table
				WHERE id='$p_bugnote_id'";
		$result = db_query( $query );
		if ( 0 == db_num_rows( $result ) ) {
			print_header_redirect( $g_main_page );
		}
	}
	# --------------------
	# check to see if user exists
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function check_user_exists( $p_user_id ) {
		global $g_mantis_user_table, $g_main_page;

		$query = "SELECT *
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		if ( 0 == db_num_rows( $result ) ) {
			print_header_redirect( $g_main_page );
		}
	}
	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function check_project_exists( $p_project_id ) {
		global $g_mantis_project_table, $g_main_page;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_table
				WHERE id='$p_project_id'";
		$result = db_query( $query );
		if ( 0 == db_result( $result, 0, 0 ) ) {
			print_header_redirect( $g_main_page );
		}
	}
	# --------------------
	# check to see if project exists by name
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function is_duplicate_project( $p_name ) {
		global $g_mantis_project_table;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_table
				WHERE name='$p_name'";
		$result = db_query( $query );
		if ( 0 == db_result( $result, 0, 0 ) ) {
			return false;
		} else {
			return true;
		}
	}
	# --------------------
	# retrieve the number of open assigned bugs to a user in a project
	function get_assigned_open_bug_count( $p_project_id, $p_cookie_str ) {
		global $g_mantis_bug_table, $g_mantis_user_table;

		$query = "SELECT id
				FROM $g_mantis_user_table
				WHERE cookie_string='$p_cookie_str'";
		$result = db_query( $query );
		$t_id = db_result( $result );

		$t_res = RESOLVED;
		$t_clo = CLOSED;
		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE 	project_id='$p_project_id' AND
						status<>'$t_res' AND status<>'$t_clo' AND
						handler_id='$t_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# retrieve the number of open reported bugs by a user in a project
	function get_reported_open_bug_count( $p_project_id, $p_cookie_str ) {
		global $g_mantis_bug_table, $g_mantis_user_table;

		$query = "SELECT id
				FROM $g_mantis_user_table
				WHERE cookie_string='$p_cookie_str'";
		$result = db_query( $query );
		$t_id = db_result( $result );

		$t_res = RESOLVED;
		$t_clo = CLOSED;
		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE 	project_id='$p_project_id' AND
						status<>'$t_res' AND status<>'$t_clo' AND
						reporter_id='$t_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# process the $p_string and convert bugs in this format #123 to a html link
	function process_bug_link( $p_string ) {
		global $g_view_bug_page, $g_view_bug_advanced_page;

		if ( ON == get_current_user_pref_field( "advanced_view" ) ) {
			return preg_replace("/#([0-9]+)/",
								"<a href=\"$g_view_bug_advanced_page?f_id=\\1\">#\\1</a>",
								$p_string);
		} else {
			return preg_replace("/#([0-9]+)/",
								"<a href=\"$g_view_bug_page?f_id=\\1\">#\\1</a>",
								$p_string);
		}
	}
	# --------------------
	# process the $p_string and convert bugs in this format #123 to a plain text link
	function process_bug_link_email( $p_string ) {
		global $g_view_bug_page, $g_view_bug_advanced_page;

		if ( ON == get_current_user_pref_field( "advanced_view" ) ) {
			return preg_replace("/#([0-9]+)/",
								"http://$g_view_bug_advanced_page?f_id=\\1",
								$p_string);
		} else {
			return preg_replace("/#([0-9]+)/",
								"http://$g_view_bug_page?f_id=\\1",
								$p_string);
		}
	}
	# --------------------
	# alternate color function
	function alternate_colors( $p_num, $p_color1="", $p_color2="" ) {
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
	# Get the default project of a user
	function get_default_project( $p_user_id ) {
		global $g_mantis_user_pref_table;

		$query = "SELECT default_project
				FROM $g_mantis_user_pref_table
				WHERE user_id='$p_user_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# Get the string associated with the $p_enum value
	function get_enum_to_string( $p_enum_string, $p_num ) {
		$t_arr = explode( ",", $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ":", $t_arr[$i] );
			if ( $t_s[0] == $p_num ) {
				return $t_s[1];
			}
		}
		return "";
	}
	# --------------------
	# Breaks up an enum string into num:value elements
	function explode_enum_string( $p_enum_string ) {
		return explode( ",", $p_enum_string );
	}
	# --------------------
	# Given one num:value pair it will return both in an array
	# num will be first (element 0) value second (element 1)
	function explode_enum_arr( $p_enum_elem ) {
		return explode( ":", $p_enum_elem );
	}
	# --------------------
	# Given a enum string and num, return the appriate string
	function get_enum_element( $p_enum_string, $p_val ) {
		$arr = explode_enum_string( $p_enum_string );
		for ( $i=0;$i<count( $arr );$i++ ) {
			$elem_arr = explode_enum_arr( $arr[$i] );
			if ( $elem_arr[0] == $p_val ) {
				return $elem_arr[1];
			}
		}
		return "@null@";
	}
	# --------------------
	# Returns the number of bugntoes for the given bug_id
	function get_bugnote_count( $p_id ) {
		global $g_mantis_bugnote_table;

		$query = "SELECT COUNT(*)
					FROM $g_mantis_bugnote_table
					WHERE bug_id ='$p_id'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# Returns the specified field of the project
	function get_project_field( $p_project_id, $p_field_name ) {
		global $g_mantis_project_table;

		$query = "SELECT $p_field_name
				FROM $g_mantis_project_table
				WHERE id='$p_project_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# Some proxies strip out HTTP_REFERER.
	# This function helps determine which pages to redirect to
	# based on site and user preference.
	function get_view_redirect_url( $p_bug_id, $p_no_referer=0 ) {
		global $HTTP_REFERER, $g_show_view, $g_view_bug_page, $g_view_bug_advanced_page;

		if ( ( !isset( $HTTP_REFERER ) ) ||
			 ( empty( $HTTP_REFERER ) ) ||
			 ( 1 == $p_no_referer ) ) {
			switch ( $g_show_view ) {
				case BOTH:
						if ( ON == get_current_user_pref_field( "advanced_view" ) ) {
							return $g_view_bug_advanced_page."?f_id=".$p_bug_id;
						} else {
							return $g_view_bug_page."?f_id=".$p_bug_id;
						}
				case SIMPLE_ONLY:
						return $g_view_bug_page."?f_id=".$p_bug_id;
				case ADVANCED_ONLY:
						return $g_view_bug_advanced_page."?f_id=".$p_bug_id;
				default:return $g_view_bug_page."?f_id=".$p_bug_id;
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
		global $HTTP_REFERER, $g_show_report, $g_report_bug_page, $g_report_bug_advanced_page;

		if ( ( !isset( $HTTP_REFERER ) ) ||
			 ( empty( $HTTP_REFERER ) ) ||
			 ( 1 == $p_no_referer ) ) {
			switch( $g_show_report ) {
				case BOTH:
						if ( ON == get_current_user_pref_field( "advanced_report" ) ) {
							return $g_report_bug_advanced_page;
		 				} else {
							return $g_report_bug_page;
						}
				case SIMPLE_ONLY:
						return $g_report_bug_page;
				case ADVANCED_ONLY:
						return $g_report_bug_advanced_page;
				default:return $g_report_bug_page;
			}
		} else {
			return $HTTP_REFERER;
		}
	}
	# --------------------
?>