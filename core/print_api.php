<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: print_api.php,v 1.50 2003-02-24 03:38:49 int2str Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'string_api.php' );

	###########################################################################
	# Basic Print API
	#
	# this file handles printing functions
	###########################################################################

	# --------------------
	# Print the headers to cause the page to redirect to $p_url
	# If $p_die is true (default), terminate the execution of the script
	#  immediately
	# If we have handled any errors on this page and the 'stop_on_errors' config
	#  option is turned on, return false and don't redirect.
	function print_header_redirect( $p_url, $p_die=true ) {
		$t_use_iis = config_get( 'use_iis');

		if ( ON == config_get( 'stop_on_errors' ) && error_handled() ) {
			return false;
		}

		if ( OFF == $t_use_iis ) {
			header( 'Status: 302' );
		}
		header( 'Content-Type: text/html' );
		header( 'Pragma: no-cache' );
		header( 'Expires: Fri, 01 Jan 1999 00:00:00 GMT' );
		header( 'Cache-control: no-cache, no-cache="Set-Cookie", private' );
		if ( ON == $t_use_iis ) {
			header( "Refresh: 0;url=$p_url" );
		} else {
			header( "Location: $p_url" );
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
			echo '<br /><div class="center">';
			echo lang_get( 'operation_successful' ) . '<br />';
			print_bracket_link( $p_redirect_to, lang_get( 'proceed' ) );
			echo '</div>';
			html_page_bottom1();
		} else {
			print_header_redirect( $t_url );
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
		# Catch a user_id of 0 (like when a handler hasn't been assigned)
		if ( 0 == $p_user_id ) {
			return;
		}

		$t_username = user_get_name( $p_user_id );
		if ( user_exists( $p_user_id ) ) {
			$t_email = user_get_email( $p_user_id );
			if ( ! is_blank( $t_email ) ) {
				print_email_link( $t_email, $t_username );
			} else {
				echo $t_username;
			}
		} else {
			echo $t_username;
		}
	}
	# --------------------
	# same as print_user() but fills in the subject with the bug summary
	function print_user_with_subject( $p_user_id, $p_bug_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		if ( 0 == $p_user_id ) {
			return;
		}
		
		$t_username = user_get_name( $p_user_id );
		if ( user_exists( $p_user_id ) ) {
			$t_email = user_get_field( $p_user_id, 'email' );
			print_email_link_with_subject( $t_email, $t_username, $p_bug_id );
		} else {
			echo $t_username;
		}
	}
	# --------------------
	function print_duplicate_id( $p_duplicate_id ) {
		if ( $p_duplicate_id != '0000000' ) {
			echo string_get_bug_view_link( $p_duplicate_id );
		}
	}
	# --------------------
	# print out an email editing input
	function print_email_input( $p_field_name, $p_email ) {
		$t_limit_email_domain = config_get( 'limit_email_domain' );
		if ( $t_limit_email_domain ) {
			# remove the domain part
			$p_email = eregi_replace( "@$t_limit_email_domain$", '', $p_email );
			echo '<input type="text" name="'.$p_field_name.'" size="20" maxlength="64" value="'.$p_email.'" />@'.$t_limit_email_domain;
		} else {
			echo '<input type="text" name="'.$p_field_name.'" size="32" maxlength="64" value="'.$p_email.'" />';
		}
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
	# ugly functions  need to be refactored
	# This populates the reporter option list with the appropriate users
	#
	# @@@ This function really ought to print out all the users, I think.
	#  I just encountered a situation where a project used to be public and
	#  was made private, so now I can't filter on any of the reporters who
	#  actually reported the bugs at the time. Maybe we could get all user
	#  who are listed as the reporter in any bug?  It would probably be a
	#  faster query actually.
	function print_reporter_option_list( $p_user_id ) {
		$t_users = array();

		# checking if it's per project or all projects
		# 0 is all projects
		if ( 0 == helper_get_current_project() ) {
			$t_adm = ADMINISTRATOR;
			$t_rep = config_get( 'report_bug_threshold' );
			$t_pub = PUBLIC;
			$t_prv = PRIVATE;

			$t_user_table = config_get( 'mantis_user_table' );
			$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );
			$t_project_table = config_get( 'mantis_project_table' );

			$query = "SELECT DISTINCT u.id, u.username
					FROM 	$t_user_table u,
							$t_project_user_list_table l,
							$t_project_table p
					WHERE	(p.view_state='$t_pub' AND
							u.access_level>='$t_rep') OR
							(l.access_level>='$t_rep' AND
							l.user_id=u.id) OR
							u.access_level>='$t_adm'
					ORDER BY u.username";
			$result = db_query( $query );
			$user_count = db_num_rows( $result );
			for ( $i=0 ; $i < $user_count ; $i++ ) {
				$row = db_fetch_array( $result );
				$t_users[] = $row;
			}
		} else {
			$t_users = project_get_all_user_rows( helper_get_current_project() );
		}

		foreach ( $t_users as $t_user ) {
			echo '<option value="' . $t_user['id'] . '" ';
			check_selected( $t_user['id'], $p_user_id );
			echo '>' . $t_user['username'] . '</option>';
		}
	}

	# --------------------
	function print_duplicate_id_option_list() {
	    $query = "SELECT id
	    		FROM " . config_get ( 'mantis_bug_table' ) . "
	    		ORDER BY id ASC";
	    $result = db_query( $query );
	    $duplicate_id_count = db_num_rows( $result );
	    PRINT '<option value="0000000"></option>';

	    for ($i=0;$i<$duplicate_id_count;$i++) {
	    	$row = db_fetch_array( $result );
	    	$t_duplicate_id	= $row['id'];

			PRINT "<option value=\"$t_duplicate_id\">".$t_duplicate_id."</option>";
		}
	}
	# --------------------
	# Get current headlines and id  prefix with v_
	function print_news_item_option_list() {
		global	$g_mantis_news_table, $g_project_cookie_val;

		if ( access_has_project_level( ADMINISTRATOR ) ) {
			$query = "SELECT id, headline, announcement, view_state
				FROM $g_mantis_news_table
				ORDER BY date_posted DESC";
		} else {
			$query = "SELECT id, headline, announcement, view_state
				FROM $g_mantis_news_table
				WHERE project_id='$g_project_cookie_val'
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
			if ( PRIVATE == $v_view_state ) {
				array_push( $t_notes, lang_get( 'private' ) );
			}
			if ( sizeof( $t_notes ) > 0 ) {
				$t_note_string = ' ['.implode( ' ', $t_notes ).']';
			}
			PRINT "<option value=\"$v_id\">$v_headline$t_note_string</option>";
		}
	}
	# --------------------
	# Used for update pages
	function print_field_option_list( $p_list, $p_item='' ) {
		global $g_mantis_bug_table;

		$t_category_string = get_enum_string( $g_mantis_bug_table, $p_list );
	    $t_arr = explode_enum_string( $t_category_string );
		$entry_count = count( $t_arr );
		for ($i=0;$i<$entry_count;$i++) {
			$t_s = str_replace( '\'', '', $t_arr[$i] );
			echo "<option value=\"$t_s\"";
			check_selected( $p_item, $t_s );
			echo ">$t_s</option>";
		} # end for
	}
	# --------------------
	function print_assign_to_option_list( $p_user_id='' ) {
		$t_users = array();

		# checking if it's per project or all projects
		# 0 is all projects
		if ( 0 == helper_get_current_project() ) {
			$t_adm = ADMINISTRATOR;
			$t_dev = config_get( 'handle_bug_threshold' );
			$t_pub = PUBLIC;
			$t_prv = PRIVATE;

			$t_user_table = config_get( 'mantis_user_table' );
			$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );
			$t_project_table = config_get( 'mantis_project_table' );

			$query = "SELECT DISTINCT u.id, u.username
					FROM 	$t_user_table u,
							$t_project_user_list_table l,
							$t_project_table p
					WHERE	(p.view_state='$t_pub' AND
							u.access_level>='$t_dev') OR
							(l.access_level>='$t_dev' AND
							l.user_id=u.id) OR
							u.access_level>='$t_adm'
					ORDER BY u.username";
			$result = db_query( $query );
			$user_count = db_num_rows( $result );
			for ( $i=0 ; $i < $user_count ; $i++ ) {
				$row = db_fetch_array( $result );
				$t_users[$row['username']] = $row;
			}
		} else {
			$t_users = project_get_all_user_rows( helper_get_current_project(),
												config_get( 'handle_bug_threshold' ) );
		}

		foreach ( $t_users as $t_user ) {
			$v_id = $val[1];
			$v_username = $val[0];
			echo '<option value="' . $t_user['id'] . '" ';
			check_selected( $t_user['id'], $p_user_id );
			echo '>' . $t_user['username'] . '</option>';
		}
	}
	# --------------------
	# List projects that the current user has access to
	function print_project_option_list( $p_project_id = null, $p_include_all_projects = true ) {
		global $g_mantis_project_table, $g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$t_user_id = current_user_get_field( 'id' );
		$t_access_level = current_user_get_field( 'access_level' );

		$t_pub = PUBLIC;
		$t_prv = PRIVATE;

		if ( ADMINISTRATOR == $t_access_level ) {
			$query = "SELECT DISTINCT( p.id ), p.name
						FROM $g_mantis_project_table p
						WHERE p.enabled=1
						ORDER BY p.name";
		} else {
			$query = "SELECT DISTINCT( p.id ), p.name
						FROM $g_mantis_project_table p
						LEFT JOIN $g_mantis_project_user_list_table u
						ON p.id=u.project_id
						WHERE p.enabled=1 AND
						((p.view_state=$t_pub) OR
						 (p.view_state=$t_prv AND u.user_id=$t_user_id))
						ORDER BY p.name";
		}

		$result = db_query( $query );
		$project_count = db_num_rows( $result );

		if ( $p_include_all_projects ) {
			echo '<option value="0"';
			check_selected( $p_project_id, 0 );
			echo '>' . lang_get( 'all_projects' ) . '</option>';
		}

		for ($i=0;$i<$project_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			echo "<option value=\"$v_id\"";
			check_selected( $p_project_id, $v_id );
			echo '>' . string_display( $v_name ) . '</option>';
		}
	}
	# --------------------
	# prints the profiles given the user id
	function print_profile_option_list( $p_user_id, $p_select_id='' ) {
		global $g_mantis_user_profile_table, $g_mantis_user_pref_table;

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "SELECT default_profile
			FROM $g_mantis_user_pref_table
			WHERE user_id='$c_user_id'";
	    $result = db_query( $query );
	    $v_default_profile = db_result( $result, 0, 0 );

		# Get profiles
		$query = "SELECT id, platform, os, os_build
			FROM $g_mantis_user_profile_table
			WHERE user_id='$c_user_id'
			ORDER BY id";
	    $result = db_query( $query );
	    $profile_count = db_num_rows( $result );

		PRINT '<option value=""></option>';
		for ($i=0;$i<$profile_count;$i++) {
			# prefix data with v_
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$v_platform	= string_display( $v_platform );
			$v_os		= string_display( $v_os );
			$v_os_build	= string_display( $v_os_build );

			echo "<option value=\"$v_id\"";
			check_selected( $v_id, $v_default_profile );
			echo ">$v_platform $v_os $v_os_build</option>";
		}
	}
	# --------------------
	function print_news_project_option_list( $p_project_id ) {
		global 	$g_mantis_project_table, $g_mantis_project_user_list_table,
				$g_project_cookie;

		if ( access_has_project_level( ADMINISTRATOR ) ) {
			$query = "SELECT *
					FROM $g_mantis_project_table
					ORDER BY name";
		} else {
			$t_user_id = current_user_get_field( 'id' );
			$query = "SELECT p.id, p.name
					FROM $g_mantis_project_table p, $g_mantis_project_user_list_table m
					WHERE 	p.id=m.project_id AND
							m.user_id='$t_user_id' AND
							p.enabled='1'";
		}
		$result = db_query( $query );
		$project_count = db_num_rows( $result );
		for ($i=0;$i<$project_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			echo "<option value=\"$v_id\"";
			check_selected( $v_id, $p_project_id );
			echo ">$v_name</option>";
		} # end for
	}
	# --------------------
	# Since categories can be orphaned we need to grab all unique instances of category
	# We check in the project category table and in the bug table
	# We put them all in one array and make sure the entries are unique
	function print_category_option_list( $p_category='' ) {
		global $g_mantis_bug_table, $g_mantis_project_category_table, $g_project_cookie_val;

		# grab all categories in the project category table
		$cat_arr = array();
		$query = "SELECT DISTINCT( category ) as category
				FROM $g_mantis_project_category_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$cat_arr[] = $row['category'];
		}
		sort( $cat_arr );
		$cat_arr = array_unique( $cat_arr );

		foreach( $cat_arr as $t_category ) {
			echo "<option value=\"$t_category\"";
			check_selected( $t_category, $p_category );
			echo ">$t_category</option>";
		}
	}
	# --------------------
	# Since categories can be orphaned we need to grab all unique instances of category
	# We check in the project category table and in the bug table
	# We put them all in one array and make sure the entries are unique
	function print_category_complete_option_list( $p_category='' ) {
		global $g_mantis_bug_table, $g_mantis_project_category_table, $g_project_cookie_val;

		# grab all categories in the project category table
		$cat_arr = array();
		$query = "SELECT DISTINCT( category ) as category
				FROM $g_mantis_project_category_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$cat_arr[] = $row['category'];
		}

		# grab all categories in the bug table
		$query = "SELECT DISTINCT( category ) as category
				FROM $g_mantis_bug_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$cat_arr[] = $row['category'];
		}
		sort( $cat_arr );
		$cat_arr = array_unique( $cat_arr );

		foreach( $cat_arr as $t_category ) {
			echo "<option value=\"$t_category\"";
			check_selected( $t_category, $p_category );
			echo ">$t_category</option>";
		}
	}
	# --------------------
	function print_category_option_listOLD( $p_category='' ) {
		global $g_mantis_project_category_table, $g_project_cookie_val;

		# @@@ not implemented yet
		if ( '0000000' == $g_project_cookie_val ) {
			$query = "SELECT category
					FROM $g_mantis_project_category_table
					WHERE project_id='$g_project_cookie_val'
					ORDER BY category";

		} else {
			$query = "SELECT category
					FROM $g_mantis_project_category_table
					WHERE project_id='$g_project_cookie_val'
					ORDER BY category";
		}

		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row['category'];
			echo "<option value=\"$t_category\"";
			check_selected( $t_category, $p_category );
			echo ">$t_category</option>";
		}
	}
	# --------------------
	function print_version_option_list( $p_version='' ) {
		global $g_mantis_project_version_table, $g_project_cookie_val;

		$query = "SELECT *
				FROM $g_mantis_project_version_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY date_order DESC";
		$result = db_query( $query );
		$version_count = db_num_rows( $result );

		echo "<option value=\"\">\n";
		for ($i=0;$i<$version_count;$i++) {
			$row = db_fetch_array( $result );
			$t_version = $row['version'];
			echo "<option value=\"$t_version\"";
			check_selected( $t_version, $p_version );
			echo ">$t_version</option>";
		}
	}
	# --------------------
	# select the proper enum values based on the input parameter
	# we use variable variables in order to achieve this
	function print_enum_string_option_list( $p_enum_name, $p_val=0 ) {
		$g_var = 'g_'.$p_enum_name.'_enum_string';
		global $$g_var;

		$t_arr  = explode_enum_string( $$g_var );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_elem  = explode_enum_arr( $t_arr[$i] );
			$t_elem2 = get_enum_element( $p_enum_name, $t_elem[0] );
			echo "<option value=\"$t_elem[0]\"";
			check_selected( $t_elem[0], $p_val );
			echo ">$t_elem2</option>";
		} # end for
	}
	# --------------------
	# prints the list of a project's users
	# if no project is specified uses the current project
	function print_project_user_option_list( $p_project_id=null ) {
 		if ( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}

		$t_rows = project_get_all_user_rows( $p_project_id );
		foreach ( $t_rows as $t_row ) {
			$t_user_id = $t_row['id'];
			$t_username = $t_row['username'];
			echo "<option value=\"$t_user_id\">$t_username</option>";
		}
	}
	# --------------------
	# prints the list of access levels exluding ADMINISTRATOR
	# this is used when adding users to projects
	function print_project_access_levels_option_list( $p_val ) {
		global $g_mantis_project_table, $g_access_levels_enum_string;

		$t_arr = explode_enum_string( $g_access_levels_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_elem = explode_enum_arr( $t_arr[$i] );

			if ( $t_elem[0] >= ADMINISTRATOR ) {
				continue;
			}

			$t_access_level = get_enum_element( 'access_levels', $t_elem[0] );
			echo "<option value=\"$t_elem[0]\"";
			check_selected( $p_val, $t_elem[0] );
			echo ">$t_access_level</option>";
		} # end for
	}
	# --------------------
	function print_language_option_list( $p_language ) {
		global $g_language_choices_arr;

		$t_arr = $g_language_choices_arr;
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			echo "<option value=\"$t_arr[$i]\"";
			check_selected( $t_arr[$i], $p_language );
			echo ">$t_arr[$i]</option>";
		} # end for
	}
	# --------------------
	# @@@ preliminary support for multiple bug actions.
	function print_all_bug_action_option_list() {
		$commands = array(  'MOVE' => lang_get('actiongroup_menu_move'),
							'ASSIGN' => lang_get('actiongroup_menu_assign'),
							'CLOSE' => lang_get('actiongroup_menu_close'),
							'DELETE' => lang_get('actiongroup_menu_delete'),
							'RESOLVE' => lang_get('actiongroup_menu_resolve'),
							'UP_PRIOR' => lang_get('actiongroup_menu_update_priority'),
							'UP_STATUS' => lang_get('actiongroup_menu_update_status') );

		while (list ($key,$val) = each ($commands)) {
			PRINT "<option value=\"".$key."\">".$val."</option>";
		}
	}
	# --------------------
	# list of users that are NOT in the specified project
	# if no project is specified use the current project
	function print_project_user_list_option_list( $p_project_id=0 ) {
		global	$g_mantis_project_user_list_table, $g_mantis_user_table,
				$g_project_cookie_val;

		if ( 0 == $p_project_id ) {
			$p_project_id = $g_project_cookie_val;
		}
		$c_project_id = (integer)$p_project_id;

		$t_adm = ADMINISTRATOR;
		$query = "SELECT DISTINCT u.id, u.username
				FROM $g_mantis_user_table u
				LEFT JOIN $g_mantis_project_user_list_table p
				ON p.user_id=u.id AND p.project_id='$c_project_id'
				WHERE u.access_level<$t_adm AND
					p.user_id IS NULL AND
					u.access_level<'$t_adm'
				ORDER BY u.username";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_username = $row['username'];
			$t_user_id = $row['id'];
			PRINT "<option value=\"$t_user_id\">$t_username</option>";
		}
	}
	# --------------------
	# list of projects that a user is NOT in
	function print_project_user_list_option_list2( $p_user_id ) {
		global	$g_mantis_project_user_list_table, $g_mantis_project_table;

		$c_user_id = db_prepare_int( $p_user_id );

		$t_prv = PRIVATE;
		$query = "SELECT DISTINCT p.id, p.name
				FROM $g_mantis_project_table p
				LEFT JOIN $g_mantis_project_user_list_table u
				ON p.id=u.project_id AND u.user_id='$c_user_id'
				WHERE p.enabled=1 AND
					p.view_state='$t_prv' AND
					u.user_id IS NULL
				ORDER BY p.name";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_project_name	= $row['name'];
			$t_user_id			= $row['id'];
			PRINT "<option value=\"$t_user_id\">$t_project_name</option>";
		}
	}
	# --------------------
	# list of projects that a user is NOT in
	function print_project_user_list( $p_user_id ) {
		global	$g_mantis_project_user_list_table, $g_mantis_project_table;

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "SELECT DISTINCT p.id, p.name, p.view_state, u.access_level
				FROM $g_mantis_project_table p
				LEFT JOIN $g_mantis_project_user_list_table u
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
			PRINT $t_project_name.' ['.$t_access_level.'] ('.$t_view_state.') [<a class="small" href="manage_user_proj_delete.php?project_id='.$t_project_id.'&amp;user_id='.$p_user_id.'">'. lang_get( 'remove_link' ).'</a>]<br />';
		}
	}

	# --------------------
	###########################################################################
	# String printing API
	###########################################################################
	# --------------------
	# prints a link to VIEW a bug given an ID
	#  account for the user preference and site override
	function print_bug_link( $p_bug_id ) {
		echo string_get_bug_view_link( $p_bug_id );
	}

	# --------------------
	# prints a link to UPDATE a bug given an ID
	#  account for the user preference and site override
	function print_bug_update_link( $p_bug_id ) {
		echo string_get_bug_update_link( $p_bug_id );
	}

 	# --------------------
	# formats the priority given the status
	# shows the priority in BOLD if the bug is NOT closed and is of significant priority
	function print_formatted_priority_string( $p_status, $p_priority ) {
		$t_pri_str = get_enum_element( 'priority', $p_priority );

		if ( ( HIGH <= $p_priority ) &&
			 ( CLOSED != $p_status ) ) {
			echo "<span class=\"bold\">$t_pri_str</span>";
		} else {
			echo $t_pri_str;
		}
	}

	# --------------------
	# formats the severity given the status
	# shows the severity in BOLD if the bug is NOT closed and is of significant severity
	function print_formatted_severity_string( $p_status, $p_severity ) {
		$t_sev_str = get_enum_element( 'severity', $p_severity );

		if ( ( MAJOR <= $p_severity ) &&
			 ( CLOSED != $p_status ) ) {
			echo "<span class=\"bold\">$t_sev_str</span>";
		} else {
			echo $t_sev_str;
		}
	}
	# --------------------
	function print_project_category_string( $p_project_id ) {
		global $g_mantis_project_category_table, $g_mantis_project_table;

		$c_project_id = (integer)$p_project_id;

		$query = "SELECT category
				FROM $g_mantis_project_category_table
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
		global $g_mantis_project_version_table, $g_mantis_project_table;

		$c_project_id = (integer)$p_project_id;

		$query = "SELECT version
				FROM $g_mantis_project_version_table
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
	function print_view_bug_sort_link( $p_string, $p_sort_field, $p_sort, $p_dir ) {
		if ( $p_sort_field == $p_sort ) {
			# we toggle between ASC and DESC if the user clicks the same sort order
			if ( 'ASC' == $p_dir ) {
				$p_dir = 'DESC';
			} else {
				$p_dir = 'ASC';
			}
		}
		PRINT '<a href="view_all_set.php?sort='.$p_sort_field.'&amp;dir='.$p_dir.'&amp;type=2">'.$p_string.'</a>';
	}
	# --------------------
	function print_view_bug_sort_link2( $p_string, $p_sort_field, $p_sort, $p_dir ) {
		if ( $p_sort_field == $p_sort ) {
			# We toggle between ASC and DESC if the user clicks the same sort order
			if ( 'ASC' == $p_dir ) {
				$p_dir = 'DESC';
			} else {
				$p_dir = 'ASC';
			}
		}
		PRINT '<a href="view_all_set.php?sort='.$p_sort_field.'&amp;dir='.$p_dir.'&amp;type=2&amp;print=1">'.$p_string.'</a>';
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

		echo '<a href="' . $p_page . '?sort=' . $p_field . '&amp;dir=' . $t_dir . '&amp;save=1&amp;hide=' . $p_hide . '">' . $p_string . '</a>';
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

		echo '<a href="' . $p_page . '?sort=' . $p_field . '&amp;dir=' . $t_dir . '">' . $p_string . '</a>';
	}
	# --------------------
	# print the bracketed links used near the top
	# if the $p_link is blank then the text is printed but no link is created
	function print_bracket_link( $p_link, $p_url_text ) {
		if (is_blank( $p_link )) {
			PRINT "[&nbsp;$p_url_text&nbsp;]";
		} else {
			PRINT "[&nbsp;<a href=\"$p_link\">$p_url_text</a>&nbsp;]";
		}
	}
	# --------------------
	# print a list of page number links (eg [1 2 3])
	function print_page_links( $p_page, $p_start, $p_end, $p_current ) {
		$t_items = array();

		for ( $i = $p_start ; $i <= $p_end ; $i++ ) {
			if ( $i == $p_current ) {
				array_push( $t_items, $i );
			} else {
				array_push( $t_items, "<a href=\"$p_page?page_number=$i\">$i</a>" );
			}
		}

		echo '[ '.implode( '&nbsp;', $t_items ).' ]';
	}
	# --------------------
	# print a mailto: href link
	function print_email_link( $p_email, $p_text ) {
		echo get_email_link($p_email, $p_text);
	}
	# --------------------
	# return the mailto: href string link instead of printing it
	function get_email_link( $p_email, $p_text ) {
		if ( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
			return $p_text;
		}
		
		$p_email	= string_url( $p_email );
		$p_text		= string_display( $p_text );

		return "<a href=\"mailto:$p_email\">$p_text</a>";
	}
	# --------------------
	# print a mailto: href link with subject
	function print_email_link_with_subject( $p_email, $p_text, $p_bug_id ) {
		$t_subject = email_build_subject( $p_bug_id );
		echo get_email_link_with_subject( $p_email, $p_text, $t_subject );
	}
	# --------------------
	# return the mailto: href string link instead of printing it
	# add subject line
	function get_email_link_with_subject( $p_email, $p_text, $p_summary ) {
		if ( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
			return $p_text;
		}
		
		$p_email	= string_url( $p_email );
		$p_text		= string_display( $p_text );
		$p_summary	= string_url( $p_summary );

		return "<a href=\"mailto:$p_email?subject=$p_summary\">$p_text</a>";
	}
	# --------------------
	# Print a hidden input for each name=>value pair in the array
	#  
	# If a value is an array an input will be created for each item with a name
	#  that ends with []
	# The names and values are passed through htmlspecialchars() before being displayed
	function print_hidden_inputs( $p_assoc_array ) {
		foreach ( $p_assoc_array as $key => $val ) {
			$key = htmlspecialchars( $key );
			if ( is_array( $val ) ) {
				foreach ( $val as $val2 ) {
					$val2 = htmlspecialchars( $val2 );
					echo "<input type=\"hidden\" name=\"$val\[\]\" value=\"$val2\" />\n";
				}
			} else {
				$val = htmlspecialchars( $val );
				echo "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
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
		PRINT "<a href=\"doc/documentation.html#$p_a_name\" target=\"_info\">[?]</a>";
	}
	# --------------------
	# checks to see whether we need to be displaying the source link
	# WARNING: displaying source (and the ability to do so) can be a security risk
	# used in html_footer()
	function print_source_link( $p_file ) {
		global $g_show_source, $g_string_cookie_val;

		if (!isset($g_string_cookie_val)) {
			return;
		}

		if (( ON == $g_show_source )&&
			( access_has_project_level( ADMINISTRATOR ) )) {
				PRINT '<br />';
				PRINT '<div align="center">';
				PRINT "<a href=\"show_source_page.php?url=$p_file\">Show Source</a>";
				PRINT '</div>';
		}
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
		echo "<hr size=\"$p_hr_size\" width=\"$p_hr_width%\" />";
	}
	# --------------------
	# prints the signup link
	function print_signup_link() {
		global $g_allow_signup;

		if ( $g_allow_signup != 0 ) {
			PRINT '<br /><div align="center">';
			print_bracket_link( 'signup_page.php', lang_get( 'signup_link' ) );
			PRINT '</div>';
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
		global $g_file_type_icons;

		$ext = strtolower( file_get_extension( $p_filename ) );
		if ( is_blank( $ext ) || !isset( $g_file_type_icons[$ext] ) ) {
			$ext = '?';
		}

		$t_name = $g_file_type_icons[$ext];
		echo '<img src="' . config_get( 'path' ) . 'images/'. $t_name . '" width="16" height="16" border="0" />';
	}
?>
