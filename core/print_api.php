<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: print_api.php,v 1.9 2002-09-01 01:23:24 vboctor Exp $
	# --------------------------------------------------------

	###########################################################################
	# Basic Print API
	#
	# this file handles printing and string manipulation functions
	###########################################################################

	# --------------------
	function print_header_redirect( $p_url ) {
		$t_use_iis = config_get( 'use_iis');

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
		die; # additional output can cause problems so let's just stop output here
	}
	# --------------------
	# prints the name of the user given the id.  also makes it an email link.
	function print_user( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		# invalid user
		if ( '0000000' == $p_user_id ) {
			return;
		}
	    $query = "SELECT username, email
	    		FROM " . config_get( 'mantis_user_table' ) . "
	    		WHERE id='$c_user_id'";
	    $result = db_query( $query );
	    if ( db_num_rows( $result ) > 0 ) {
			$t_username	= db_result( $result, 0, 0 );
			$t_email	= db_result( $result, 0, 1 );

			print_email_link( $t_email, $t_username );
		} else {
			echo lang_get ( 'user_no_longer_exists' );
		}
	}
	# --------------------
	# same as print_user() but fills in the subject with the bug summary
	function print_user_with_subject( $p_user_id, $p_bug_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		if ( '0000000' == $p_user_id ) {
			return;
		}
	    $query = "SELECT username, email
	    		FROM " . config_get( 'mantis_user_table' ) . "
	    		WHERE id='$c_user_id'";
	    $result = db_query( $query );
	    if ( db_num_rows( $result ) > 0 ) {
			$t_username	= db_result( $result, 0, 0 );
			$t_email	= db_result( $result, 0, 1 );

			print_email_link_with_subject( $t_email, $t_username, $p_bug_id );
		} else {
			echo lang_get( 'user_no_longer_exists' );
		}
	}
	# --------------------
	function print_duplicate_id( $p_duplicate_id ) {
		if ( $p_duplicate_id != '0000000' ) {
			if ( ON == current_user_get_pref( 'advanced_view' ) ) {
				PRINT "<a href=\"view_bug_advanced_page.php?f_id=$p_duplicate_id\">".$p_duplicate_id."</a>";
			} else {
				PRINT "<a href=\"view_bug_page.php?f_id=$p_duplicate_id\">".$p_duplicate_id."</a>";
			}
		}
	}
	# --------------------
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
	function print_reporter_option_list( $p_user_id ) {
		global	$g_mantis_user_table, $g_mantis_project_user_list_table,
				$g_mantis_project_table, $g_project_cookie_val;

		$t_adm = ADMINISTRATOR;
		$t_rep = REPORTER;
		$t_pub = PUBLIC;
		$t_prv = PRIVATE;
		$user_arr = array();

		# checking if it's per project or all projects
		# 0 is all projects
		if ( '0000000' == $g_project_cookie_val ) {
			$query = "SELECT DISTINCT u.id, u.username, u.email
					FROM 	$g_mantis_user_table u,
							$g_mantis_project_user_list_table l,
							$g_mantis_project_table p
					WHERE	(p.view_state='$t_pub' AND
							u.access_level>='$t_rep') OR
							(l.access_level>='$t_rep' AND
							l.user_id=u.id) OR
							u.access_level>='$t_adm'
					ORDER BY u.username";
			$result = db_query( $query );
			$user_count = db_num_rows( $result );
			for ($i=0;$i<$user_count;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v' );
				$user_arr[$v_username] = array( $v_username, $v_id );
			}
		} else {
			$temp_arr = array();
			# grab the administrators
			$query = "SELECT id, username
					FROM $g_mantis_user_table
					ORDER BY username";
			$result = db_query( $query );
			$user_count = db_num_rows( $result );
			for ($i=0;$i<$user_count;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v' );
				$temp_arr[$v_username] = array( $v_username, $v_id );
			}

			foreach ( $temp_arr as $key => $val ) {
				$v_id = $val[1];
				$v_username = $val[0];

				# always add all administrators
				$t_access_level = user_get_field( $v_id, 'access_level' );
				if ( ADMINISTRATOR == $t_access_level ) {
					$user_arr[$v_username] = array( $v_username, $v_id );
					continue;
				}

				# see if users belong
				$t_project_view_state = project_get_field( $g_project_cookie_val, 'view_state' );
				if ( PUBLIC == $t_project_view_state ) {
					$query = "SELECT l.access_level
							FROM	$g_mantis_project_user_list_table l,
									$g_mantis_project_table p
							WHERE	l.project_id='$g_project_cookie_val' AND
									p.id=l.project_id AND
									l.user_id='$v_id'";
					$result = db_query( $query );
					$count = db_num_rows( $result );
					if ( $count > 0 ){
						$t_access_level = db_result( $result );
					}
					if ( $t_access_level >= REPORTER ) {
						$user_arr[$v_username] = array( $v_username, $v_id );
					}

				} else {
					$query = "SELECT COUNT(*)
							FROM	$g_mantis_project_user_list_table l,
									$g_mantis_project_table p
							WHERE	l.project_id='$g_project_cookie_val' AND
									p.id=l.project_id AND
									l.user_id='$v_id' AND
									l.access_level>='$t_rep'";
					$result = db_query( $query );
					$count = db_result( $result, 0, 0 );
					if ( $count > 0 ) {
						$user_arr[$v_username] = array( $v_username, $v_id );
						continue;
					}
				}
			}
		}

		asort( $user_arr );
		foreach ( $user_arr as $key => $val ) {
			$v_id = $val[1];
			$v_username = $val[0];
			echo "<option value=\"$v_id\"";
			check_selected( $v_id, $p_user_id );
			echo ">$v_username</option>";
		} # end foreach
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

		if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
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
		global $g_mantis_user_table, $g_mantis_project_table,
				$g_mantis_project_user_list_table, $g_project_cookie_val,
				$g_handle_bug_threshold;

		$t_adm = ADMINISTRATOR;
		$t_dev = $g_handle_bug_threshold;
		$t_pub = PUBLIC;
		$t_prv = PRIVATE;
		$user_arr = array();

		# checking if it's per project or all projects
		# 0 is all projects
		if ( '0000000' == $g_project_cookie_val ) {
			$query = "SELECT DISTINCT u.id, u.username, u.email
					FROM 	$g_mantis_user_table u,
							$g_mantis_project_user_list_table l,
							$g_mantis_project_table p
					WHERE	(p.view_state='$t_pub' AND
							u.access_level>='$t_dev') OR
							(l.access_level>='$t_dev' AND
							l.user_id=u.id) OR
							u.access_level>='$t_adm'
					ORDER BY u.username";
			$result = db_query( $query );
			$user_count = db_num_rows( $result );
			for ($i=0;$i<$user_count;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v' );
				$user_arr[$v_username] = array( $v_username, $v_id );
			}
		} else {
			$temp_arr = array();
			# grab the administrators
			$query = "SELECT id, username
					FROM $g_mantis_user_table
					ORDER BY username";
			$result = db_query( $query );
			$user_count = db_num_rows( $result );
			for ($i=0;$i<$user_count;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v' );
				$temp_arr[$v_username] = array( $v_username, $v_id );
			}

			foreach ( $temp_arr as $key => $val ) {
				$v_id = $val[1];
				$v_username = $val[0];

				# always add all administrators
				$t_access_level = user_get_field( $v_id, 'access_level' );
				if ( ADMINISTRATOR == $t_access_level ) {
					$user_arr[$v_username] = array( $v_username, $v_id );
					continue;
				}

				# see if users belong
				$t_project_view_state = project_get_field( $g_project_cookie_val, 'view_state' );
				if ( PUBLIC == $t_project_view_state ) {
					$query = "SELECT l.access_level
							FROM	$g_mantis_project_user_list_table l,
									$g_mantis_project_table p
							WHERE	l.project_id='$g_project_cookie_val' AND
									p.id=l.project_id AND
									l.user_id='$v_id'";
					$result = db_query( $query );
					$count = db_num_rows( $result );
					if ( $count > 0 ){
						$t_access_level = db_result( $result );
					}
					if ( $t_access_level >= $t_dev ) {
						$user_arr[$v_username] = array( $v_username, $v_id );
					}

				} else {
					$query = "SELECT COUNT(*)
							FROM	$g_mantis_project_user_list_table l,
									$g_mantis_project_table p
							WHERE	l.project_id='$g_project_cookie_val' AND
									p.id=l.project_id AND
									l.user_id='$v_id' AND
									l.access_level>='$t_dev'";
					$result = db_query( $query );
					$count = db_result( $result, 0, 0 );
					if ( $count > 0 ) {
						$user_arr[$v_username] = array( $v_username, $v_id );
						continue;
					}
				}
			}
		}

		asort( $user_arr );
		foreach ( $user_arr as $key => $val ) {
			$v_id = $val[1];
			$v_username = $val[0];
			echo "<option value=\"$v_id\"";
			check_selected( $v_id, $p_user_id );
			echo ">$v_username</option>";
		} # end foreach
	}
	# --------------------
	# List projects that the current user has access to
	function print_project_option_list( $p_project_id='' ) {
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

		for ($i=0;$i<$project_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			echo "<option value=\"$v_id\"";
			check_selected( $p_project_id, $v_id );
			echo ">$v_name</option>";
		}
	}
	# --------------------
	# prints the profiles given the user id
	function print_profile_option_list( $p_id, $p_select_id='' ) {
		global $g_mantis_user_profile_table, $g_mantis_user_pref_table;

		$c_id = db_prepare_int( $p_id );

		$query = "SELECT default_profile
			FROM $g_mantis_user_pref_table
			WHERE user_id='$c_id'";
	    $result = db_query( $query );
	    $v_default_profile = db_result( $result, 0, 0 );

		# Get profiles
		$query = "SELECT id, platform, os, os_build
			FROM $g_mantis_user_profile_table
			WHERE user_id='$c_id'
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
	function print_news_project_option_list( $p_id ) {
		global 	$g_mantis_project_table, $g_mantis_project_user_list_table,
				$g_project_cookie;

		if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
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
			check_selected( $v_id, $p_id );
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
		global $$g_var, $g_customize_attributes;

		# custom attributes
		if ($g_customize_attributes) {
			# to be deleted when moving to manage_project_page.php
			$f_project_id = '0000000';

			# custom attributes insertion
			attribute_insert( $p_enum_name, $f_project_id, 'global' );
		}
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
	# prints the list of access levels exluding ADMINISTRATOR
	# this is used when adding users to projects
	function print_project_user_option_list( $p_val ) {
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
		$commands = array(  'MOVE' => 'Move Bugs',
							'ASSIGN' => 'Assign',
							'CLOSE' => 'Close',
							'DELETE' => 'Delete',
							'RESOLVE' => 'Resolve',
							'UP_PRIOR' => 'Update Priority',
							'UP_STATUS' => 'Update Status' );

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
			$t_id = $row['id'];
			PRINT "<option value=\"$t_id\">$t_username</option>";
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
			$t_id			= $row['id'];
			PRINT "<option value=\"$t_id\">$t_project_name</option>";
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
			PRINT $t_project_name.' ['.$t_access_level.'] ('.$t_view_state.') [<a class="small" href="manage_user_proj_delete.php?f_project_id='.$t_project_id.'&amp;f_user_id='.$p_user_id.'">'. lang_get( 'remove_link' ).'</a>]<br />';
		}
	}
	# --------------------
	# color list printing options for custom status
	function print_custom_status_color_list() {
		global $g_custom_colors ;

		while (list ($key,$val) = each ($g_custom_colors)) {
			PRINT "<option value=\"".$key."\">".$val."</option>";
		}
	}

	# --------------------
	###########################################################################
	# String printing API
	###########################################################################
	# --------------------
	# prints a link to a bug given an ID
	# it accounts for the user preference and site override
	function print_bug_link( $p_id ) {
		global 	$g_show_view;

		switch ( $g_show_view ) {
		case BOTH:
			if ( ON == current_user_get_pref( 'advanced_view' ) ) {
				PRINT "<a href=\"view_bug_advanced_page.php?f_id=$p_id\">$p_id</a>";
			} else {
				PRINT "<a href=\"view_bug_page.php?f_id=$p_id\">$p_id</a>";
			}
			break;
		case SIMPLE_ONLY:
			PRINT "<a href=\"view_bug_page.php?f_id=$p_id\">$p_id</a>";
			break;
		case ADVANCED_ONLY:
			PRINT "<a href=\"view_bug_advanced_page.php?f_id=$p_id\">$p_id</a>";
			break;
		}
	}
	# --------------------
	# prints a link to the update page given an ID
	# it accounts for the user preference and site override
	function get_bug_update_page() {
		global 	$g_show_update;

		switch ( $g_show_update ) {
		case BOTH:
			if ( ON == current_user_get_pref( 'advanced_update' ) ) {
				return 'bug_update_advanced_page.php';
			} else {
				return 'bug_update_page.php';
			}
			break;
		case SIMPLE_ONLY:
				return 'bug_update_page.php';
			break;
		case ADVANCED_ONLY:
				return 'bug_update_advanced_page.php';
			break;
		}
	}
	# --------------------
	# returns a href link to a bug given an ID
	# it accounts for the user preference and site override
	function get_bug_link( $p_id ) {
		global 	$g_show_view;

		switch ( $g_show_view ) {
		case BOTH:
			if ( ON == current_user_get_pref( 'advanced_view' ) ) {
				return "<a href=\"view_bug_advanced_page.php?f_id=$p_id\">$p_id</a>";
			} else {
				return "<a href=\"view_bug_page.php?f_id=$p_id\">$p_id</a>";
			}
			break;
		case SIMPLE_ONLY:
			return "<a href=\"view_bug_page.php?f_id=$p_id\">$p_id</a>";
			break;
		case ADVANCED_ONLY:
			return "<a href=\"view_bug_advanced_page.php?f_id=$p_id\">$p_id</a>";
			break;
		}
	}
	# --------------------
	# returns a href link to a bug given an ID
	# it accounts for the user preference and site override
	function get_bug_link_plain( $p_id ) {
		global 	$g_show_view;

		switch ( $g_show_view ) {
		case BOTH:
			if ( ON == current_user_get_pref( 'advanced_view' ) ) {
				return 'view_bug_advanced_page.php?f_id='.$p_id;
			} else {
				return 'view_bug_page.php?f_id='.$p_id;
			}
			break;
		case SIMPLE_ONLY:
			return 'view_bug_page.php?f_id='.$p_id;
			break;
		case ADVANCED_ONLY:
			return 'view_bug_advanced_page.php?f_id='.$p_id;
			break;
		}
	}
	# --------------------
	# formats the severity given the status
	# shows the severity in BOLD if the bug is NOT closed and is of significant severity
	function print_formatted_severity_string( $p_status, $p_severity ) {
		$t_sev_str = get_enum_element( 'severity', $p_severity );
		if ( ( ( MAJOR == $p_severity ) ||
			   ( CRASH == $p_severity ) ||
			   ( BLOCK == $p_severity ) ) &&
			 ( CLOSED != $p_status ) ) {
			PRINT "<span class=\"bold\">$t_sev_str</span>";
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
		PRINT '<a href="view_all_set.php?f_sort='.$p_sort_field.'&amp;f_dir='.$p_dir.'&amp;f_type=2">'.$p_string.'</a>';
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
		PRINT '<a href="view_all_set.php?f_sort='.$p_sort_field.'&amp;f_dir='.$p_dir.'&amp;f_type=2&amp;f_print=1">'.$p_string.'</a>';
	}
	# --------------------
	function print_manage_user_sort_link(  $p_page, $p_string, $p_field, $p_dir, $p_sort_by, $p_hide=0 ) {
		if ($p_sort_by == $p_field) {   # If this is the selected field flip the order
			if ($p_dir == 'ASC') {
				$t_dir = 'DESC';
			} else {
				$t_dir = 'ASC';
			}
		} else {                        # Otherwise always start with ASCending
				$t_dir = 'ASC';
		}

		PRINT '<a href="'.$p_page.'?f_sort='.$p_field.'&amp;f_dir='.$t_dir.'&amp;f_save=1&amp;f_hide='.$p_hide.'">'.$p_string.'</a>';
	}
	# --------------------
	function print_manage_project_sort_link(  $p_page, $p_string, $p_field, $p_dir, $p_sort_by ) {
		if ($p_sort_by == $p_field) {   # If this is the selected field flip the order
			if ($p_dir == 'ASC') {
				$t_dir = 'DESC';
			} else {
				$t_dir = 'ASC';
			}
		} else {                        # Otherwise always start with ASCending
			$t_dir = 'ASC';
		}

		PRINT '<a href="'.$p_page.'?f_sort='.$p_field.'&amp;f_dir='.$t_dir.'">'.$p_string.'</a>';
	}
	# --------------------
	# print the bracketed links used near the top
	# if the $p_link is blank then the text is printed but no link is created
	function print_bracket_link( $p_link, $p_url_text ) {
		if (empty( $p_link )) {
			PRINT "[ $p_url_text ]";
		} else {
			PRINT "[ <a href=\"$p_link\">$p_url_text</a> ]";
		}
	}
	# --------------------
	# print a mailto: href link
	function print_email_link( $p_email, $p_text ) {
		PRINT get_email_link($p_email,$p_text);
	}
	# --------------------
	# return the mailto: href string link instead of printing it
	function get_email_link( $p_email, $p_text ) {
		global $g_show_user_email, $g_anonymous_account;

		switch ( $g_show_user_email ) {
			case NONE:	return $p_text;
			case ALL:	return "<a href=\"mailto:$p_email\">$p_text</a>";
			case NO_ANONYMOUS:	if ( current_user_get_field( 'username' ) != $g_anonymous_account ) {
									return "<a href=\"mailto:$p_email\">$p_text</a>";
								} else {
									return $p_text;
								}
			case ADMIN_ONLY:	if ( ADMINISTRATOR == current_user_get_field( 'access_level' ) ) {
									return "<a href=\"mailto:$p_email\">$p_text</a>";
								} else {
									return $p_text;
								}
			default:	return $p_text;
		}
	}
	# --------------------
	# print a mailto: href link with subject
	function print_email_link_with_subject( $p_email, $p_text, $p_bug_id ) {
		global $g_mantis_bug_table;

		$t_subject = email_build_subject( $p_bug_id );
		PRINT get_email_link_with_subject( $p_email, $p_text, $t_subject );
	}
	# --------------------
	# return the mailto: href string link instead of printing it
	# add subject line
	function get_email_link_with_subject( $p_email, $p_text, $p_summary ) {
		global $g_show_user_email, $g_anonymous_account;

		switch ( $g_show_user_email ) {
			case NONE:	return $p_text;
			case ALL:	return "<a href=\"mailto:$p_email?subject=$p_summary\">$p_text</a>";
			case NO_ANONYMOUS:	if ( current_user_get_field( 'username' ) != $g_anonymous_account ) {
									return "<a href=\"mailto:$p_email?subject=$p_summary\">$p_text</a>";
								} else {
									return $p_text;
								}
			case ADMIN_ONLY:	if ( ADMINISTRATOR == current_user_get_field( 'access_level' ) ) {
									return "<a href=\"mailto:$p_email?subject=$p_summary\">$p_text</a>";
								} else {
									return $p_text;
								}
			default:	return $p_text;
		}
	}
	# --------------------
	# print our standard mysql query error
	# this function should rarely (if ever) be reached.  instead the db_()
	# functions should trap (although inelegantly).
	function print_sql_error( $p_query ) {
		global $MANTIS_ERROR, $g_administrator_email;

		PRINT $MANTIS_ERROR[ERROR_SQL];
		print_email_link( $g_administrator_email, lang_get( 'administrator' ) );
		PRINT "<p>$p_query;<p>";
	}
	# --------------------
	###########################################################################
	# Filter API
	###########################################################################
	# --------------------
	# make http and mailto link urls
	function filter_href_tags( $p_string ) {
    	$p_string = eregi_replace( "([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
    							"<a href=\"\\1://\\2\\3\">\\1://\\2\\3</a>",
    							$p_string);
        $p_string = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
        						"<a href=\"mailto:\\1\" target=\"_new\">\\1</a>",
        						$p_string);
		return $p_string;
	}
	# --------------------
	# undo http and mailto link urls for editing purposes
	function unfilter_href_tags( $p_string ) {
    	$p_string = eregi_replace( "<a href=\"([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])\">([^[:space:]]*)([[:alnum:]#?/&=])</a>",
    							"\\1://\\2\\3",
    							$p_string);
        $p_string = eregi_replace( "<a href=\"mailto:(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))\" target=\"_new\">(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))</a>",
        						"\\1",
        						$p_string);
		return $p_string;
	}
	# --------------------
	# @@@ currently does nothing
	function filter_img_tags( $p_string ) {
		return $p_string;
	}
	# --------------------
	# process $g_html_tags to be treated as html
	function filter_html_tags( $p_string ) {
		global $g_html_tags;

		$t_filter_from 	= @array( "/\//", "/</", "/>/" );
		$t_filter_to 	= @array( "\/", "", "" );
		//$t_filter_from 	= @array( "\/", "<", ">" );
		//$t_filter_to 	= @array( "\/", "", "" );

		$t_tag_count = count( $g_html_tags );
		for ($i=0;$i<$t_tag_count;$i++) {
			$tag = preg_replace( $t_filter_from, $t_filter_to, $g_html_tags[$i] );

			$p_string = preg_replace( "/&lt;($tag)&gt;/i", "<\\1>", $p_string );
		}

		return $p_string;
	}
	# --------------------

?>