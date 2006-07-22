<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2005  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: print_api.php,v 1.143.6.1.4.1.2.2 2006-07-22 19:56:20 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'prepare_api.php' );
	require_once( $t_core_dir . 'profile_api.php' );

	### Print API ###

	# this file handles printing functions

	# --------------------
	# Print the headers to cause the page to redirect to $p_url
	# If $p_die is true (default), terminate the execution of the script
	#  immediately
	# If we have handled any errors on this page and the 'stop_on_errors' config
	#  option is turned on, return false and don't redirect.
	function print_header_redirect( $p_url, $p_die = true, $p_sanitize = false ) {
		$t_use_iis = config_get( 'use_iis');

		if ( ON == config_get( 'stop_on_errors' ) && error_handled() ) {
			return false;
		}
		
		# validate the url as part of this site before continuing
		if ( $p_sanitize ) {
			$t_url = string_sanitize_url( $p_url );
		} else {
			$t_url = $p_url;
		}

		# don't send more headers if they have already been sent (guideweb)
		if ( ! headers_sent() ) {
			header( 'Content-Type: text/html; charset=' . lang_get( 'charset' ) );

			if ( ON == $t_use_iis ) {
				header( "Refresh: 0;url=$t_url" );
			} else {
				header( "Location: $t_url" );
			}
		} else {
			trigger_error( ERROR_PAGE_REDIRECTION, ERROR );
			return false;
		}

		if ( $p_die ) {
			die; # additional output can cause problems so let's just stop output here
		}

		return true;
	}
	# --------------------
	# Print a redirect header to view a bug
	function print_header_redirect_view( $p_bug_id ) {
		print_header_redirect( string_get_bug_view_url( $p_bug_id ) );
	}

	# --------------------
	# Get a view URL for the bug id based on the user's preference and
	#  call print_successful_redirect() with that URL
	function print_successful_redirect_to_bug( $p_bug_id ) {
		$t_url = string_get_bug_view_url( $p_bug_id, auth_get_current_user_id() );

		print_successful_redirect( $t_url );
	}

	# --------------------
	# If the show query count is ON, print success and redirect after the
	#  configured system wait time.
	# If the show query count is OFF, redirect right away.
	function print_successful_redirect( $p_redirect_to ) {
		if ( ON == config_get( 'show_queries_count' ) ) {
			html_meta_redirect( $p_redirect_to );
			html_page_top1();
			html_page_top2();
			PRINT '<br /><div class="center">';
			PRINT lang_get( 'operation_successful' ) . '<br />';
			print_bracket_link( $p_redirect_to, lang_get( 'proceed' ) );
			PRINT '</div>';
			html_page_bottom1();
		} else {
			print_header_redirect( $p_redirect_to );
		}
	}

	# --------------------
	# Print a redirect header to update a bug
	function print_header_redirect_update( $p_bug_id ) {
		print_header_redirect( string_get_bug_update_url( $p_bug_id ) );
	}
	# --------------------
	# Print a redirect header to update a bug
	function print_header_redirect_report() {
		print_header_redirect( string_get_bug_report_url() );
	}
	# --------------------
	# prints the name of the user given the id.  also makes it an email link.
	function print_user( $p_user_id ) {
	    echo prepare_user_name( $p_user_id );
	}
	# --------------------
	# same as print_user() but fills in the subject with the bug summary
	function print_user_with_subject( $p_user_id, $p_bug_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		if ( NO_USER == $p_user_id ) {
			return;
		}

		$t_username = user_get_name( $p_user_id );
		if ( user_exists( $p_user_id ) && user_get_field( $p_user_id, 'enabled' ) ) {
			$t_email = user_get_field( $p_user_id, 'email' );
			print_email_link_with_subject( $t_email, $t_username, $p_bug_id );
		} else {
			echo '<font STYLE="text-decoration: line-through">';
			echo $t_username;
			echo '</font>';
		}
	}
	# --------------------
	function print_duplicate_id( $p_duplicate_id ) {
		if ( $p_duplicate_id != 0 ) {
			PRINT string_get_bug_view_link( $p_duplicate_id );
		}
	}
	# --------------------
	# print out an email editing input
	function print_email_input( $p_field_name, $p_email ) {
		$t_limit_email_domain = config_get( 'limit_email_domain' );
		if ( $t_limit_email_domain ) {
			# remove the domain part
			$p_email = eregi_replace( "@$t_limit_email_domain$", '', $p_email );
			PRINT '<input type="text" name="'.$p_field_name.'" size="20" maxlength="64" value="'.$p_email.'" />@'.$t_limit_email_domain;
		} else {
			PRINT '<input type="text" name="'.$p_field_name.'" size="32" maxlength="64" value="'.$p_email.'" />';
		}
	}
	# --------------------
	# print out an email editing input
	function print_captcha_input( $p_field_name ) {
		echo '<input type="text" name="'.$p_field_name.'" size="5" maxlength="5" value="" />';
	}
	###########################################################################
	# Option List Printing API
	###########################################################################
	# --------------------
	# sorts the array by the first element of the array element
	# @@@ might not be used
	function cmp( $p_var1, $p_var2 ) {
		if ( $p_var1[0][0] == $p_var2[0][0] ) {
			return 0;
		}
		if ( $p_var1[0][0] < $p_var2[0][0] ) {
			return -1;
		} else {
			return 1;
		}
	}
	# --------------------
	# This populates an option list with the appropriate users by access level
	#
	# @@@ from print_reporter_option_list
	function print_user_option_list( $p_user_id, $p_project_id = null, $p_access = ANYBODY ) {
		$t_users = array();

		if ( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}

		$t_users = project_get_all_user_rows( $p_project_id, $p_access ); # handles ALL_PROJECTS case

		$t_display = array();
		$t_sort = array();
		$t_show_realname = ( ON == config_get( 'show_realname' ) );
		$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
		foreach ( $t_users as $t_user ) {
			$t_user_name = string_attribute( $t_user['username'] );
			$t_sort_name = strtolower( $t_user_name );
			if ( $t_show_realname && ( $t_user['realname'] <> "" ) ){
				$t_user_name = string_attribute( $t_user['realname'] );
				if ( $t_sort_by_last_name ) {
					$t_sort_name_bits = split( ' ', strtolower( $t_user_name ), 2 );
					$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
				} else {
					$t_sort_name = strtolower( $t_user_name );
				}
			}
			$t_display[] = $t_user_name;
			$t_sort[] = $t_sort_name;
		}
		array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
		for ($i = 0; $i < count( $t_sort ); $i++ ) {
			$t_row = $t_users[$i];
			PRINT '<option value="' . $t_row['id'] . '" ';
			check_selected( $p_user_id, $t_row['id'] );
			PRINT '>' . $t_display[$i] . '</option>';
		}
	}

	# --------------------
	# ugly functions  need to be refactored
	# This populates the reporter option list with the appropriate users
	#
	# @@@ This function really ought to print out all the users, I think.
	#  I just encountered a situation where a project used to be public and
	#  was made private, so now I can't filter on any of the reporters who
	#  actually reported the bugs at the time. Maybe we could get all user
	#  who are listed as the reporter in any bug?  It would probably be a
	#  faster query actually.
	function print_reporter_option_list( $p_user_id, $p_project_id = null ) {
		print_user_option_list( $p_user_id, $p_project_id, config_get( 'report_bug_threshold' ) );
	}

	# --------------------
	function print_duplicate_id_option_list() {
	    $query = "SELECT id
	    		FROM " . config_get ( 'mantis_bug_table' ) . "
	    		ORDER BY id ASC";
	    $result = db_query( $query );
	    $duplicate_id_count = db_num_rows( $result );
	    PRINT '<option value="0"></option>';

	    for ($i=0;$i<$duplicate_id_count;$i++) {
	    	$row = db_fetch_array( $result );
	    	$t_duplicate_id	= $row['id'];

			PRINT "<option value=\"$t_duplicate_id\">".$t_duplicate_id."</option>";
		}
	}
	# --------------------
	# Get current headlines and id  prefix with v_
	function print_news_item_option_list() {
		$t_mantis_news_table = config_get( 'mantis_news_table' );

		$t_project_id = helper_get_current_project();

		if ( access_has_project_level( ADMINISTRATOR ) ) {
			$query = "SELECT id, headline, announcement, view_state
				FROM $t_mantis_news_table
				ORDER BY date_posted DESC";
		} else {
			$query = "SELECT id, headline, announcement, view_state
				FROM $t_mantis_news_table
				WHERE project_id='$t_project_id'
				ORDER BY date_posted DESC";
		}
	    $result = db_query( $query );
	    $news_count = db_num_rows( $result );

		for ($i=0;$i<$news_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$v_headline = string_display( $v_headline );

			$t_notes = array();
			$t_note_string = '';
			if ( 1 == $v_announcement ) {
				array_push( $t_notes, lang_get( 'announcement' ) );
			}
			if ( VS_PRIVATE == $v_view_state ) {
				array_push( $t_notes, lang_get( 'private' ) );
			}
			if ( sizeof( $t_notes ) > 0 ) {
				$t_note_string = ' ['.implode( ' ', $t_notes ).']';
			}
			PRINT "<option value=\"$v_id\">$v_headline$t_note_string</option>";
		}
	}
	#---------------
	# Constructs the string for one news entry given the row retrieved from the news table.
	function print_news_entry( $p_headline, $p_body, $p_poster_id, $p_view_state, $p_announcement, $p_date_posted ) {
		$t_headline = string_display_links( $p_headline );
		$t_body = string_display_links( $p_body );
		$t_date_posted = date( config_get( 'normal_date_format' ), $p_date_posted );

		if ( VS_PRIVATE == $p_view_state ) {
			$t_news_css = 'news-heading-private';
		} else {
			$t_news_css = 'news-heading-public';
		}

		$output = '<div align="center">';
		$output .= '<table class="width75" cellspacing="0">';
		$output .= '<tr>';
		$output .= "<td class=\"$t_news_css\">";
		$output .= "<span class=\"bold\">$t_headline</span> - ";
		$output .= "<span class=\"italic-small\">$t_date_posted</span> - ";
		echo $output;

		# @@@ eventually we should replace print's with methods to construct the
		#     strings.
		print_user( $p_poster_id );
		$output = '';

		$output .= ' <span class="small">';
		if ( 1 == $p_announcement ) {
			$output .= '[' . lang_get( 'announcement' ) . ']';
		}
		if ( VS_PRIVATE == $p_view_state ) {
			$output .= '[' . lang_get( 'private' ) . ']';
		}

		$output .= '</span>';
		$output .= '</td>';
		$output .= '</tr>';
		$output .= '<tr>';
		$output .= "<td class=\"news-body\">$t_body</td>";
		$output .= '</tr>';
		$output .= '</table>';
		$output .= '</div>';

		echo $output;
	}

	# --------------------
	# print a news item given a row in the news table.
        function print_news_entry_from_row( $p_news_row ) {
		extract( $p_news_row, EXTR_PREFIX_ALL, 'v' );
		print_news_entry( $v_headline, $v_body, $v_poster_id, $v_view_state, $v_announcement, $v_date_posted );
	}

	# --------------------
	# print a news item
	function print_news_string_by_news_id( $p_news_id ) {
		$row = news_get_row( $p_news_id );

		# only show VS_PRIVATE posts to configured threshold and above
		if ( ( VS_PRIVATE == $row['view_state'] ) &&
			 !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
			continue;
		}

		print_news_entry_from_row( $row );
	}
	# --------------------
	# Used for update pages
	function print_field_option_list( $p_list, $p_item='' ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );

		$t_category_string = get_enum_string( $t_mantis_bug_table, $p_list );
	    $t_arr = explode_enum_string( $t_category_string );
		$entry_count = count( $t_arr );
		for ($i=0;$i<$entry_count;$i++) {
			$t_s = str_replace( '\'', '', $t_arr[$i] );
			PRINT "<option value=\"$t_s\"";
			check_selected( $p_item, $t_s );
			PRINT ">$t_s</option>";
		} # end for
	}
	# --------------------
	function print_assign_to_option_list( $p_user_id='', $p_project_id = null, $p_threshold = null ) {

		if ( null === $p_threshold ) {
			$p_threshold = config_get( 'handle_bug_threshold' );
		}

		print_user_option_list( $p_user_id, $p_project_id, $p_threshold );
	}
	# --------------------
	# List projects that the current user has access to
	function print_project_option_list( $p_project_id = null, $p_include_all_projects = true, $p_filter_project_id = null, $p_trace = false ) {
		project_cache_all();
		$t_project_ids = current_user_get_accessible_projects();
		if ( $p_include_all_projects ) {
			PRINT '<option value="' . ALL_PROJECTS . '"';
			check_selected( $p_project_id, ALL_PROJECTS );
			PRINT '>' . lang_get( 'all_projects' ) . '</option>' . "\n";
		}

		$t_project_count = count( $t_project_ids );
		for ($i=0;$i<$t_project_count;$i++) {
			$t_id = $t_project_ids[$i];
			if ( $t_id != $p_filter_project_id ) {
				PRINT "<option value=\"$t_id\"";
				check_selected( $p_project_id, $t_id );
				PRINT '>' . string_display( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
				print_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace );
			}
		}
	}
	# --------------------
	# List projects that the current user has access to
	function print_subproject_option_list( $p_parent_id, $p_project_id = null, $p_filter_project_id = null, $p_trace = false, $p_parents = Array() ) {
		array_push( $p_parents, $p_parent_id );
		$t_project_ids = current_user_get_accessible_subprojects( $p_parent_id );
		$t_project_count = count( $t_project_ids );
		for ($i=0;$i<$t_project_count;$i++) {
			$t_full_id = $t_id = $t_project_ids[$i];
			if ( $t_id != $p_filter_project_id ) {
				PRINT "<option value=\"";
				if ( $p_trace ) {
				  $t_full_id = join( $p_parents, ";") . ';' . $t_id;
				}
				PRINT "$t_full_id\"";
				check_selected( $p_project_id, $t_full_id );
				PRINT '>' . str_repeat( "&raquo; ", count( $p_parents ) ) . string_display( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
				print_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_parents );
			}
		}
	}
	# --------------------
	# prints the profiles given the user id
	function print_profile_option_list( $p_user_id, $p_select_id='' ) {
		if ( '' === $p_select_id ) {
			$p_select_id = profile_get_default( $p_user_id );
		}
		$t_profiles = profile_get_all_for_user( $p_user_id );

		PRINT '<option value=""></option>';
		foreach ( $t_profiles as $t_profile ) {
			extract( $t_profile, EXTR_PREFIX_ALL, 'v' );
			$v_platform	= string_display( $v_platform );
			$v_os		= string_display( $v_os );
			$v_os_build	= string_display( $v_os_build );

			PRINT "<option value=\"$v_id\"";
			check_selected( $p_select_id, $v_id );
			PRINT ">$v_platform $v_os $v_os_build</option>";
		}
	}
	# --------------------
	# prints the profiles used in a certain project
	function print_profile_option_list_for_project( $p_project_id, $p_select_id='') {
		if ( '' === $p_select_id ) {
			$p_select_id = profile_get_default( $p_user_id );
		}

		$t_profiles = profile_get_all_for_project( $p_project_id );

		PRINT '<option value=""></option>';
		foreach ( $t_profiles as $t_profile ) {
			extract( $t_profile, EXTR_PREFIX_ALL, 'v' );
			$v_platform	= string_display( $v_platform );
			$v_os		= string_display( $v_os );
			$v_os_build	= string_display( $v_os_build );

			PRINT "<option value=\"$v_id\"";
			check_selected( $p_select_id, $v_id );
			PRINT ">$v_platform $v_os $v_os_build</option>";
		}
	}
	# --------------------
	function print_news_project_option_list( $p_project_id ) {
		$t_mantis_project_table = config_get( 'mantis_project_table' );
		$t_mantis_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		if ( access_has_project_level( ADMINISTRATOR ) ) {
			$query = "SELECT *
					FROM $t_mantis_project_table
					ORDER BY name";
		} else {
			$t_user_id = auth_get_current_user_id();
			$query = "SELECT p.id, p.name
					FROM $t_mantis_project_table p, $t_mantis_project_user_list_table m
					WHERE 	p.id=m.project_id AND
							m.user_id='$t_user_id' AND
							p.enabled='1'";
		}
		$result = db_query( $query );
		$project_count = db_num_rows( $result );
		for ($i=0;$i<$project_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			PRINT "<option value=\"$v_id\"";
			check_selected( $v_id, $p_project_id );
			PRINT ">$v_name</option>";
		} # end for
	}
	# --------------------
	# Since categories can be orphaned we need to grab all unique instances of category
	# We check in the project category table and in the bug table
	# We put them all in one array and make sure the entries are unique
	function print_category_option_list( $p_category='', $p_project_id = null ) {
		$t_mantis_project_category_table = config_get( 'mantis_project_category_table' );

		if ( null === $p_project_id ) {
			$c_project_id = helper_get_current_project();
		} else {
			$c_project_id = db_prepare_int( $p_project_id );
		}

		$t_project_where = helper_project_specific_where( $c_project_id );

		# grab all categories in the project category table
		$cat_arr = array();
		$query = "SELECT DISTINCT category
				FROM $t_mantis_project_category_table
				WHERE $t_project_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$cat_arr[] = string_attribute( $row['category'] );
		}
		sort( $cat_arr );
		$cat_arr = array_unique( $cat_arr );

		foreach( $cat_arr as $t_category ) {
			PRINT "<option value=\"$t_category\"";
			check_selected( $t_category, $p_category );
			PRINT ">$t_category</option>";
		}
	}
	# --------------------
	# Since categories can be orphaned we need to grab all unique instances of category
	# We check in the project category table and in the bug table
	# We put them all in one array and make sure the entries are unique
	function print_category_complete_option_list( $p_category='', $p_project_id = null ) {
		$t_mantis_project_category_table = config_get( 'mantis_project_category_table' );
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );

		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}

		$t_project_where = helper_project_specific_where( $t_project_id );

		# grab all categories in the project category table
		$cat_arr = array();
		$query = "SELECT DISTINCT category
				FROM $t_mantis_project_category_table
				WHERE $t_project_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$cat_arr[] = string_attribute( $row['category'] );
		}

		# grab all categories in the bug table
		$query = "SELECT DISTINCT category
				FROM $t_mantis_bug_table
				WHERE $t_project_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$cat_arr[] = string_attribute( $row['category'] );
		}
		sort( $cat_arr );
		$cat_arr = array_unique( $cat_arr );

		foreach( $cat_arr as $t_category ) {
			PRINT "<option value=\"$t_category\"";
			check_selected( $p_category, $t_category );
			PRINT ">$t_category</option>";
		}
	}
	# --------------------
	# Print the option list for versions
	# $p_version = currently selected version.
	# $p_project_id = project id, otherwise current project will be used.
	# $p_released = null to get all, 1: only released, 0: only future versions
	# $p_leading_black = allow selection of no version
	# $p_with_subs = include subprojects
	function print_version_option_list( $p_version='', $p_project_id = null, $p_released = null, $p_leading_blank = true, $p_with_subs=false ) {
		if ( null === $p_project_id ) {
			$c_project_id = helper_get_current_project();
		} else {
			$c_project_id = db_prepare_int( $p_project_id );
		}

		if ( $p_with_subs ) {
			$versions = version_get_all_rows_with_subs( $c_project_id, $p_released );
		} else {
			$versions = version_get_all_rows( $c_project_id, $p_released );
		}

		if ( $p_leading_blank ) {
			echo '<option value=""></option>';
		}
		foreach( $versions as $version ) {
			$t_version = string_shorten( string_attribute( $version['version'] ) );
			echo "<option value=\"$t_version\"";
			check_selected( $p_version, $t_version );
			echo ">$t_version</option>";
		}
	}
	# --------------------
	function print_build_option_list( $p_build='' ) {
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_overall_build_arr = array();

		$t_project_id = helper_get_current_project();

		$t_project_where = helper_project_specific_where( $t_project_id );

		# Get the "found in" build list
		$query = "SELECT DISTINCT build
				FROM $t_bug_table
				WHERE $t_project_where
				ORDER BY build DESC";
		$result = db_query( $query );
		$option_count = db_num_rows( $result );

		for ( $i = 0; $i < $option_count; $i++ ) {
			$row = db_fetch_array( $result );
			$t_overall_build_arr[] = $row['build'];
		}

		foreach( $t_overall_build_arr as $t_build ) {
			PRINT "<option value=\"$t_build\"";
			check_selected( $p_build, $t_build );
			PRINT ">" . string_shorten( $t_build ) . "</option>";
		}
	}

	# --------------------
	# select the proper enum values based on the input parameter
	# $p_enum_name - name of enumeration (eg: status)
	# $p_val: current value
	function print_enum_string_option_list( $p_enum_name, $p_val = 0 ) {
		$t_config_var_name = $p_enum_name.'_enum_string';
		$t_config_var_value = config_get( $t_config_var_name );

		$t_arr  = explode_enum_string( $t_config_var_value );
		$t_enum_count = count( $t_arr );
		for ( $i = 0; $i < $t_enum_count; $i++) {
			$t_elem  = explode_enum_arr( $t_arr[$i] );
			$t_key = trim( $t_elem[0] );
			$t_elem2 = get_enum_element( $p_enum_name, $t_key );
			echo "<option value=\"$t_key\"";
			check_selected( $p_val, $t_key );
			echo ">$t_elem2</option>";
		} # end for
	}
	# --------------------
	# Select the proper enum values for status based on workflow
	# or the input parameter if workflows are not used
	# $p_enum_name : name of enumeration (eg: status)
	# $p_current_value : current value
	function get_status_option_list( $p_user_auth = 0, $p_current_value = 0, $p_show_current = true, $p_add_close = false ) {
		$t_config_var_value = config_get( 'status_enum_string' );
		$t_enum_workflow = config_get( 'status_enum_workflow' );

		if ( count( $t_enum_workflow ) < 1 ) {
			# workflow not defined, use default enum
			$t_arr  = explode_enum_string( $t_config_var_value );
		} else {
			# workflow defined - find allowed states
			if ( isset( $t_enum_workflow[$p_current_value] ) ) {
				$t_arr  = explode_enum_string( $t_enum_workflow[$p_current_value] );
			} else {
				# workflow was not set for this status, this shouldn't happen
				$t_arr  = explode_enum_string( $t_config_var_value );
			}
		}

		$t_enum_count = count( $t_arr );
		$t_enum_list = array();

		for ( $i = 0; $i < $t_enum_count; $i++ ) {
			$t_elem  = explode_enum_arr( $t_arr[$i] );
			if ( ( $p_user_auth >= access_get_status_threshold( $t_elem[0] ) ) &&
						( ! ( ( false == $p_show_current ) && ( $p_current_value == $t_elem[0] ) ) ) ) {
				$t_enum_list[$t_elem[0]] = get_enum_element( 'status', $t_elem[0] );
			}
		} # end for
		if ( true == $p_show_current ) {
				$t_enum_list[$p_current_value] = get_enum_element( 'status', $p_current_value );
			}
		if ( ( true == $p_add_close ) && ( $p_current_value >= config_get( 'bug_resolved_status_threshold' ) ) ) {
				$t_enum_list[CLOSED] = get_enum_element( 'status', CLOSED );
			}
		return $t_enum_list;
	}
	# --------------------
	# print the status option list for the bug_update pages
	function print_status_option_list( $p_select_label, $p_current_value = 0, $p_allow_close = false, $p_project_id = null ) {
		$t_current_auth = access_get_project_level( $p_project_id );

		$t_enum_list = get_status_option_list( $t_current_auth, $p_current_value, true, $p_allow_close );

		if ( count( $t_enum_list ) > 0 ) {
			# resort the list into ascending order
			ksort( $t_enum_list );
			reset( $t_enum_list );
			echo '<select name="' . $p_select_label . '">';
			foreach ( $t_enum_list as $key => $val ) {
				echo "<option value=\"$key\"";
				check_selected( $key, $p_current_value );
				echo ">$val</option>";
			}
			echo '</select>';
		} else {
			echo get_enum_to_string( 'status_enum_string', $p_current_value );
		}

	}
	# --------------------
	# prints the list of a project's users
	# if no project is specified uses the current project
	function print_project_user_option_list( $p_project_id=null ) {
 		print_user_option_list( 0, $p_project_id );
	}
	# --------------------
	# prints the list of access levels exluding ADMINISTRATOR
	# this is used when adding users to projects
	function print_project_access_levels_option_list( $p_val ) {
		$t_access_levels_enum_string = config_get( 'access_levels_enum_string' );

		# Add [default access level] to add the user to a project
		# with his default access level.
		PRINT "<option value=\"" . DEFAULT_ACCESS_LEVEL . "\"";
		PRINT ">[" . lang_get( 'default_access_level' ) . "]</option>";

		$t_arr = explode_enum_string( $t_access_levels_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_elem = explode_enum_arr( $t_arr[$i] );

#			if ( $t_elem[0] >= ADMINISTRATOR ) {
#				continue;
#			}

			$t_access_level = get_enum_element( 'access_levels', $t_elem[0] );
			PRINT "<option value=\"$t_elem[0]\"";
			check_selected( $p_val, $t_elem[0] );
			PRINT ">$t_access_level</option>";
		} # end for
	}
	# --------------------
	function print_language_option_list( $p_language ) {
		$t_arr = config_get( 'language_choices_arr' );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_language = string_attribute( $t_arr[$i] );
			PRINT "<option value=\"$t_language\"";
			check_selected( $t_language, $p_language );
			PRINT ">$t_language</option>";
		} # end for
	}
	# --------------------
	# @@@ preliminary support for multiple bug actions.
	function print_all_bug_action_option_list() {
		$commands = array(  'MOVE' => lang_get('actiongroup_menu_move'),
							'COPY' => lang_get('actiongroup_menu_copy'),
							'ASSIGN' => lang_get('actiongroup_menu_assign'),
							'CLOSE' => lang_get('actiongroup_menu_close'),
							'DELETE' => lang_get('actiongroup_menu_delete'),
							'RESOLVE' => lang_get('actiongroup_menu_resolve'),
							'SET_STICKY' => lang_get( 'actiongroup_menu_set_sticky' ),
							'UP_PRIOR' => lang_get('actiongroup_menu_update_priority'),
							'UP_STATUS' => lang_get('actiongroup_menu_update_status'),
							'UP_CATEGORY' => lang_get('actiongroup_menu_update_category'),
							'VIEW_STATUS' => lang_get( 'actiongroup_menu_update_view_status' ) );

		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS != $t_project_id ) {
			$t_user_id = auth_get_current_user_id();
			$t_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

			foreach( $t_custom_field_ids as $t_custom_field_id ) {
				# if user has not access right to modify the field, then there is no
				# point in showing it.
				if ( !custom_field_has_write_access_to_project( $t_custom_field_id, $t_project_id, $t_user_id ) ) {
					continue;
				}

				$t_custom_field_def = custom_field_get_definition( $t_custom_field_id );
				$t_command_id = 'custom_field_' . $t_custom_field_id;
				$t_command_caption = sprintf( lang_get( 'actiongroup_menu_update_field' ), lang_get_defaulted( $t_custom_field_def['name'] ) );
				$commands[$t_command_id] = $t_command_caption;
			}
		}

		$t_custom_group_actions = config_get( 'custom_group_actions' );

		foreach( $t_custom_group_actions as $t_custom_group_action ) {
			$commands[$t_custom_group_action['action']] = lang_get_defaulted( $t_custom_group_action['action'] );
		}

		while (list ($key,$val) = each ($commands)) {
			PRINT "<option value=\"".$key."\">".$val."</option>";
		}
	}
	# --------------------
	# list of users that are NOT in the specified project and that are enabled
	# if no project is specified use the current project
	# also exclude any administrators
	function print_project_user_list_option_list( $p_project_id=null ) {
		$t_mantis_project_user_list_table = config_get( 'mantis_project_user_list_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );

		if ( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}
		$c_project_id = (int)$p_project_id;

		$t_adm = ADMINISTRATOR;
		$query = "SELECT DISTINCT u.id, u.username, u.realname
				FROM $t_mantis_user_table u
				LEFT JOIN $t_mantis_project_user_list_table p
				ON p.user_id=u.id AND p.project_id='$c_project_id'
				WHERE u.access_level<$t_adm AND
					u.enabled = 1 AND
					p.user_id IS NULL
				ORDER BY u.realname, u.username";
		$result = db_query( $query );
		$t_display = array();
		$t_sort = array();
		$t_users = array();
		$t_show_realname = ( ON == config_get( 'show_realname' ) );
		$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_users[] = $row['id'];
			$t_user_name = string_attribute( $row['username'] );
			$t_sort_name = $t_user_name;
			if ( ( isset( $row['realname'] ) ) && ( $row['realname'] <> "" ) && $t_show_realname ) {
				$t_user_name = string_attribute( $row['realname'] );
				if ( $t_sort_by_last_name ) {
					$t_sort_name_bits = split( ' ', strtolower( $t_user_name ), 2 );
					$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
				} else {
					$t_sort_name = strtolower( $t_user_name );
				}
			}
			$t_display[] = $t_user_name;
			$t_sort[] = $t_sort_name;
		}
		array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
		for ($i = 0; $i < count( $t_sort ); $i++ ) {
			PRINT '<option value="' . $t_users[$i] . '">' . $t_display[$i] . '</option>';
		}
	}
	# --------------------
	# list of projects that a user is NOT in
	function print_project_user_list_option_list2( $p_user_id ) {
		$t_mantis_project_user_list_table = config_get( 'mantis_project_user_list_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "SELECT DISTINCT p.id, p.name
				FROM $t_mantis_project_table p
				LEFT JOIN $t_mantis_project_user_list_table u
				ON p.id=u.project_id AND u.user_id='$c_user_id'
				WHERE p.enabled=1 AND
					u.user_id IS NULL
				ORDER BY p.name";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_project_name	= string_attribute( $row['name'] );
			$t_user_id			= $row['id'];
			PRINT "<option value=\"$t_user_id\">$t_project_name</option>";
		}
	}
	# --------------------
	# list of projects that a user is in
	function print_project_user_list( $p_user_id, $p_include_remove_link = true ) {
		$t_mantis_project_user_list_table = config_get( 'mantis_project_user_list_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "SELECT DISTINCT p.id, p.name, p.view_state, u.access_level
				FROM $t_mantis_project_table p
				LEFT JOIN $t_mantis_project_user_list_table u
				ON p.id=u.project_id
				WHERE p.enabled=1 AND
					u.user_id='$c_user_id'
				ORDER BY p.name";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_project_id	= $row['id'];
			$t_project_name	= $row['name'];
			$t_view_state	= $row['view_state'];
			$t_access_level	= $row['access_level'];
			$t_access_level	= get_enum_element( 'access_levels', $t_access_level );
			$t_view_state	= get_enum_element( 'project_view_state', $t_view_state );

			echo $t_project_name.' ['.$t_access_level.'] ('.$t_view_state.')';
			if ( $p_include_remove_link && access_has_project_level( config_get( 'project_user_threshold' ), $t_project_id ) ) {
				echo ' [<a class="small" href="manage_user_proj_delete.php?project_id='.$t_project_id.'&amp;user_id='.$p_user_id.'">'. lang_get( 'remove_link' ).'</a>]';
			}
			echo '<br />';
		}
	}

	# --------------------
	###########################################################################
	# String printing API
	###########################################################################
	# --------------------
	# prints a link to VIEW a bug given an ID
	#  account for the user preference and site override
	function print_bug_link( $p_bug_id, $p_detail_info = true ) {
		PRINT string_get_bug_view_link( $p_bug_id, null, $p_detail_info );
	}

	# --------------------
	# prints a link to UPDATE a bug given an ID
	#  account for the user preference and site override
	function print_bug_update_link( $p_bug_id ) {
		PRINT string_get_bug_update_link( $p_bug_id );
	}

 	# --------------------
	# formats the priority given the status
	# shows the priority in BOLD if the bug is NOT closed and is of significant priority
	function print_formatted_priority_string( $p_status, $p_priority ) {
		$t_pri_str = get_enum_element( 'priority', $p_priority );

		if ( ( HIGH <= $p_priority ) &&
			 ( CLOSED != $p_status ) ) {
			PRINT "<span class=\"bold\">$t_pri_str</span>";
		} else {
			PRINT $t_pri_str;
		}
	}

	# --------------------
	# formats the severity given the status
	# shows the severity in BOLD if the bug is NOT closed and is of significant severity
	function print_formatted_severity_string( $p_status, $p_severity ) {
		$t_sev_str = get_enum_element( 'severity', $p_severity );

		if ( ( MAJOR <= $p_severity ) &&
			 ( CLOSED != $p_status ) ) {
			PRINT "<span class=\"bold\">$t_sev_str</span>";
		} else {
			PRINT $t_sev_str;
		}
	}
	# --------------------
	function print_project_category_string( $p_project_id ) {
		$t_mantis_project_category_table = config_get( 'mantis_project_category_table' );

		$c_project_id = db_prepare_int( $p_project_id );

		$query = "SELECT category
				FROM $t_mantis_project_category_table
				WHERE project_id='$c_project_id'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		$t_string = '';

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row['category'];

			if ( $i+1 < $category_count ) {
				$t_string .= $t_category.', ';
			} else {
				$t_string .= $t_category;
			}
		}

		return $t_string;
	}
	# --------------------
	function print_project_version_string( $p_project_id ) {
		$t_mantis_project_version_table = config_get( 'mantis_project_version_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );

		$c_project_id = db_prepare_int( $p_project_id );

		$query = "SELECT version
				FROM $t_mantis_project_version_table
				WHERE project_id='$c_project_id'";
		$result = db_query( $query );
		$version_count = db_num_rows( $result );
		$t_string = '';

		for ($i=0;$i<$version_count;$i++) {
			$row = db_fetch_array( $result );
			$t_version = $row['version'];

			if ( $i+1 < $version_count ) {
				$t_string .= $t_version.', ';
			} else {
				$t_string .= $t_version;
			}
		}

		return $t_string;
	}
	# --------------------
	###########################################################################
	# Link Printing API
	###########################################################################
	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_view_bug_sort_link( $p_string, $p_sort_field, $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if ( $p_columns_target == COLUMNS_TARGET_PRINT_PAGE ) {
			if ( $p_sort_field == $p_sort ) {
				# We toggle between ASC and DESC if the user clicks the same sort order
				if ( 'ASC' == $p_dir ) {
					$p_dir = 'DESC';
				} else {
					$p_dir = 'ASC';
				}
			} else {                        # Otherwise always start with ASCending
				$t_dir = 'ASC';
			}

			echo '<a href="view_all_set.php?sort='.$p_sort_field.'&amp;dir='.$p_dir.'&amp;type=2&amp;print=1">'.$p_string.'</a>';
		} else if ( $p_columns_target == COLUMNS_TARGET_VIEW_PAGE ) {
			if ( $p_sort_field == $p_sort ) {
				# we toggle between ASC and DESC if the user clicks the same sort order
				if ( 'ASC' == $p_dir ) {
					$p_dir = 'DESC';
				} else {
					$p_dir = 'ASC';
				}
			} else {                        # Otherwise always start with ASCending
				$t_dir = 'ASC';
			}

			echo '<a href="view_all_set.php?sort='.$p_sort_field.'&amp;dir='.$p_dir.'&amp;type=2">'.$p_string.'</a>';
		} else {
			echo $p_string;
		}
	}
	# --------------------
	function print_manage_user_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by, $p_hide=0 ) {
		if ( $p_sort_by == $p_field ) {   # If this is the selected field flip the order
			if ( 'ASC' == $p_dir || ASC == $p_dir ) {
				$t_dir = 'DESC';
			} else {
				$t_dir = 'ASC';
			}
		} else {                        # Otherwise always start with ASCending
			$t_dir = 'ASC';
		}

		PRINT '<a href="' . $p_page . '?sort=' . $p_field . '&amp;dir=' . $t_dir . '&amp;save=1&amp;hide=' . $p_hide . '">' . $p_string . '</a>';
	}
	# --------------------
	function print_manage_project_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by ) {
		if ( $p_sort_by == $p_field ) {   # If this is the selected field flip the order
			if ( 'ASC' == $p_dir || ASC == $p_dir ) {
				$t_dir = 'DESC';
			} else {
				$t_dir = 'ASC';
			}
		} else {                        # Otherwise always start with ASCending
			$t_dir = 'ASC';
		}

		PRINT '<a href="' . $p_page . '?sort=' . $p_field . '&amp;dir=' . $t_dir . '">' . $p_string . '</a>';
	}
	# --------------------
	# print a button which presents a standalone form.
	# if the $p_link is blank then the text is printed but no link is created
	# if $p_new_window is true, link will open in a new window, default false.
	function print_button( $p_action_page, $p_label ) {
		echo '<form method="POST" action="', $p_action_page, '"><input type="submit" class="button-small" value="', $p_label, '" /></form>';
	}
	# --------------------
	# print the bracketed links used near the top
	# if the $p_link is blank then the text is printed but no link is created
	# if $p_new_window is true, link will open in a new window, default false.
	function print_bracket_link( $p_link, $p_url_text, $p_new_window = false ) {
		if (is_blank( $p_link )) {
			PRINT "[&nbsp;$p_url_text&nbsp;]";
		} else {
			if( true == $p_new_window ) {
				PRINT "[&nbsp;<a href=\"$p_link\" target=\"_blank\">$p_url_text</a>&nbsp;]";
			} else {
				PRINT "[&nbsp;<a href=\"$p_link\">$p_url_text</a>&nbsp;]";
			}
		}
	}
	# --------------------
	# print a HTML link
	function print_link( $p_link, $p_url_text ) {
		if (is_blank( $p_link )) {
			PRINT " $p_url_text ";
		} else {
			PRINT " <a href=\"$p_link\">$p_url_text</a> ";
		}
	}
	# --------------------
	# print a HTML page link
	function print_page_link( $p_page_url, $p_text = '', $p_page_no=0, $p_page_cur=0 ) {
		if (is_blank( $p_text )) {
			$p_text = $p_page_no;
		}

		if ( ( 0 < $p_page_no ) && ( $p_page_no != $p_page_cur ) ) {
			PRINT " <a href=\"$p_page_url?page_number=$p_page_no\">$p_text</a> ";
		} else {
			PRINT " $p_text ";
		}
	}
	# --------------------
	# print a list of page number links (eg [1 2 3])
	function print_page_links( $p_page, $p_start, $p_end, $p_current ) {
		$t_items = array();
		$t_link = '';

		# Check if we have more than one page,
		#  otherwise return without doing anything.

		if ( $p_end - $p_start < 1 ) {
			return;
		}

		# Get localized strings
		$t_first = lang_get( 'first' );
		$t_last  = lang_get( 'last' );
		$t_prev  = lang_get( 'prev' );
		$t_next  = lang_get( 'next' );

		$t_page_links = 10;

		print( "[ " );

		# First and previous links
		print_page_link( $p_page, $t_first, 1, $p_current );
		print_page_link( $p_page, $t_prev, $p_current - 1, $p_current );

		# Page numbers ...

		$t_first_page = max( $p_start, $p_current - $t_page_links/2 );
		$t_first_page = min( $t_first_page, $p_end - $t_page_links );
		$t_first_page = max( $t_first_page, $p_start );

		if ( $t_first_page > 1 ) {
			print( " ... " );
		}

		$t_last_page = $t_first_page + $t_page_links;
		$t_last_page = min( $t_last_page, $p_end );

		for ( $i = $t_first_page ; $i <= $t_last_page ; $i++ ) {
			if ( $i == $p_current ) {
				array_push( $t_items, $i );
			} else {
				array_push( $t_items, "<a href=\"$p_page?page_number=$i\">$i</a>" );
			}
		}
		PRINT implode( '&nbsp;', $t_items );

		if ( $t_last_page < $p_end ) {
			print( " ... " );
		}

		# Next and Last links
		if ( $p_current < $p_end ) {
			print_page_link( $p_page, $t_next, $p_current + 1, $p_current );
		} else {
			print_page_link( $p_page, $t_next );
		}
		print_page_link( $p_page, $t_last, $p_end, $p_current );

    	print( " ]" );
	}
	# --------------------
	# print a mailto: href link
	function print_email_link( $p_email, $p_text ) {
		PRINT get_email_link($p_email, $p_text);
	}
	# --------------------
	# return the mailto: href string link instead of printing it
	function get_email_link( $p_email, $p_text ) {
	    return prepare_email_link( $p_email, $p_text );
	}
	# --------------------
	# print a mailto: href link with subject
	function print_email_link_with_subject( $p_email, $p_text, $p_bug_id ) {
		$t_subject = email_build_subject( $p_bug_id );
		PRINT get_email_link_with_subject( $p_email, $p_text, $t_subject );
	}
	# --------------------
	# return the mailto: href string link instead of printing it
	# add subject line
	function get_email_link_with_subject( $p_email, $p_text, $p_summary ) {
		if ( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
			return $p_text;
		}

		# If we apply string_url() to the whole mailto: link then the @
		#  gets turned into a %40 and you can't right click in browsers to
		#  do Copy Email Address.  If we don't apply string_url() to the
		#  summary text then an ampersand (for example) will truncate the text
		$p_summary	= string_url( $p_summary );
		$t_mailto	= string_attribute( "mailto:$p_email?subject=$p_summary" );
		$p_text		= string_display( $p_text );

		return "<a href=\"$t_mailto\">$p_text</a>";
	}
	# --------------------
	# Print a hidden input for each name=>value pair in the array
	#
	# If a value is an array an input will be created for each item with a name
	#  that ends with []
	# The names and values are passed through string_html_specialchars() before being displayed
	function print_hidden_inputs( $p_assoc_array ) {
		foreach ( $p_assoc_array as $key => $val ) {
			$key = string_html_specialchars( $key );
			if ( is_array( $val ) ) {
				foreach ( $val as $val2 ) {
					$val2 = string_html_specialchars( $val2 );
					PRINT "<input type=\"hidden\" name=\"$val\[\]\" value=\"$val2\" />\n";
				}
			} else {
				$val = string_html_specialchars( $val );
				PRINT "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
			}
		}
	}


	#=============================
	# Functions that used to be in html_api
	#=============================

	# --------------------
	# This prints the little [?] link for user help
	# The $p_a_name is a link into the documentation.html file
	function print_documentation_link( $p_a_name='' ) {
		# @@@ Disable documentation links for now.  May be re-enabled if linked to new manual.
		# PRINT "<a href=\"doc/documentation.html#$p_a_name\" target=\"_info\">[?]</a>";
	}
 	# --------------------
	# print the hr
	function print_hr( $p_hr_size=null, $p_hr_width=null ) {
		if ( null === $p_hr_size ) {
			$p_hr_size = config_get( 'hr_size' );
		}
		if ( null === $p_hr_width ) {
			$p_hr_width = config_get( 'hr_width' );
		}
		PRINT "<hr size=\"$p_hr_size\" width=\"$p_hr_width%\" />";
	}
	# --------------------
	# prints the signup link
	function print_signup_link() {
		if( ( ON == config_get( 'allow_signup' ) ) &&
		    ( ON == config_get( 'enable_email_notification' ) ) ) {
			print_bracket_link( 'signup_page.php', lang_get( 'signup_link' ) );
		}
	}
	# --------------------
	# prints the login link
	function print_login_link() {
		print_bracket_link( 'login_page.php', lang_get( 'login_title' ) );
	}
	# --------------------
	# prints the lost pwd link
	function print_lost_password_link() {
		# lost password feature disabled or reset password via email disabled -> stop here!
		if( ( ON == config_get( 'lost_password_feature' ) ) &&
			( ON == config_get( 'send_reset_password' ) ) &&
			( ON == config_get( 'enable_email_notification' ) ) ) {
			print_bracket_link( 'lost_pwd_page.php', lang_get( 'lost_password_link' ) );
		}
	}
	# --------------------
	function print_proceed( $p_result, $p_query, $p_link ) {
		PRINT '<br />';
		PRINT '<div align="center">';
		if ( $p_result ) {						# SUCCESS
			PRINT lang_get( 'operation_successful' ) . '<br />';
		} else {								# FAILURE
			print_sql_error( $p_query );
		}
		print_bracket_link( $p_link, lang_get( 'proceed' ) );
		PRINT '</div>';
	}


	#===============================
	# Deprecated Functions
	#===============================

	# --------------------
	# print our standard mysql query error
	# this function should rarely (if ever) be reached.  instead the db_()
	# functions should trap (although inelegantly).
	function print_sql_error( $p_query ) {
		global $MANTIS_ERROR, $g_administrator_email;

		PRINT $MANTIS_ERROR[ERROR_SQL];
		print_email_link( $g_administrator_email, lang_get( 'administrator' ) );
		PRINT "<br />$p_query;<br />";
	}
	# --------------------
	# This is our generic error printing function
	# Errors should terminate the script immediately
	function print_mantis_error( $p_error_num=0 ) {
		global $MANTIS_ERROR;

		PRINT '<html><head></head><body>';
		PRINT $MANTIS_ERROR[$p_error_num];
		PRINT '</body></html>';
		exit;
	}

	# --------------------
	# Get icon corresponding to the specified filename
	function print_file_icon( $p_filename ) {
		$t_file_type_icons = config_get( 'file_type_icons' );

		$ext = strtolower( file_get_extension( $p_filename ) );
		if ( is_blank( $ext ) || !isset( $t_file_type_icons[$ext] ) ) {
			$ext = '?';
		}

		$t_name = $t_file_type_icons[$ext];
		PRINT '<img src="' . config_get( 'path' ) . 'images/'. $t_name . '" width="16" height="16" border="0" />';
	}


	# --------------------
	# Prints an RSS image that is hyperlinked to an RSS feed.
	function print_rss( $p_feed_url, $p_title = '' ) {
		$t_path = config_get( 'path' );
		echo '<a href="', $p_feed_url, '" title="', $p_title, '"><img src="', $t_path, '/images/', 'rss.gif" border="0" alt="', $p_title, '" width="26" height="13" /></a>';
	}
?>
