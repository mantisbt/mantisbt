<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Basic Print API
	###########################################################################

	# this file handles printing and string manipulation functions

	### --------------------
	function print_header_redirect( $p_url ) {
		header( "Status: 302" );
		header( "Content-Type: text/html" );
		header( "Pragma: no-cache" );
		header( "Expires: Fri, 01 Jan 1999 00:00:00 GMT" );
		header( "Cache-control: no-cache, no-cache=\"Set-Cookie\", private" );
		header( "Location: $p_url" );
	}
	### --------------------
	function print_user( $p_user_id ) {
		global $g_mantis_user_table, $s_user_no_longer_exists;

		if ( $p_user_id=="0000000" ) {
			return;
		}
	    $query = "SELECT username, email
	    		FROM $g_mantis_user_table
	    		WHERE id='$p_user_id'";
	    $result = db_query( $query );
	    if ( db_num_rows( $result )>0 ) {
			$t_username	= db_result( $result, 0, 0 );
			$t_email	= db_result( $result, 0, 1 );

                      print_email_link($t_email,$t_username);
		}
		else {
			PRINT $s_user_no_longer_exists;
		}
	}
	### --------------------
	# returns username if account
	function get_user( $p_user_id ) {
		global $g_mantis_user_table, $s_user_no_longer_exists;

		if ( $p_user_id=="0000000" ) {
			return "";
		}
		$query = "SELECT username
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		if ( db_num_rows( $result )>0 ) {
			return db_result( $result, 0, 0 );
		} else {
			return $s_user_no_longer_exists;
		}
	}
	### --------------------
	function print_duplicate_id( $p_duplicate_id ) {
		global 	$g_view_bug_page, $g_view_bug_advanced_page;

		if ( $p_duplicate_id!="0000000" ) {
			if ( get_current_user_pref_field( "advanced_view" )==1 ) {
				PRINT "<a href=\"$g_view_bug_advanced_page?f_id=$p_duplicate_id\">".$p_duplicate_id."</a>";
			} else {
				PRINT "<a href=\"$g_view_bug_page?f_id=$p_duplicate_id\">".$p_duplicate_id."</a>";
			}
		}
	}
	### --------------------
	###########################################################################
	# Option List Printing API
	###########################################################################
	### --------------------
	function print_handler_option_list( $p_handler_id ) {
		global $g_mantis_user_table;

		$t_dev = DEVELOPER;
		$t_man = MANAGER;
		$t_adm = ADMINISTRATOR;

	    $query = "SELECT id, username
	    		FROM $g_mantis_user_table
				WHERE 	access_level=$t_dev OR
						access_level=$t_man OR
						access_level=$t_adm
	    		ORDER BY username";
	    $result = db_query( $query );
	    $user_count = db_num_rows( $result );
	    for ($i=0;$i<$user_count;$i++) {
	    	$row = db_fetch_array( $result );
	    	$t_handler_id	= $row["id"];
	    	$t_handler_name	= $row["username"];

	    	if ( $t_handler_id==$p_handler_id ) {
				PRINT "<option value=\"$t_handler_id\" SELECTED>".$t_handler_name;
			}
			else {
				PRINT "<option value=\"$t_handler_id\">".$t_handler_name;
			}
		}
	}
	### --------------------
	function print_user_option_list( $p_user_id ) {
		global $g_mantis_user_table;

	    $query = "SELECT id, username
	    		FROM $g_mantis_user_table
	    		ORDER BY username";
	    $result = db_query( $query );
	    $user_count = db_num_rows( $result );
	    for ($i=0;$i<$user_count;$i++) {
	    	$row = db_fetch_array( $result );
	    	$t_user_id   = $row["id"];
	    	$t_user_name = $row["username"];

	    	if ( $t_user_id==$p_user_id ) {
				PRINT "<option value=\"$t_user_id\" SELECTED>".$t_user_name;
			}
			else {
				PRINT "<option value=\"$t_user_id\">".$t_user_name;
			}
		}
	}
	### --------------------
	function print_reporter_option_list( $p_user_id ) {
		global $g_mantis_user_table;

		$t_rep = REPORTER;
	    $query = "SELECT id, username
	    		FROM $g_mantis_user_table
	    		WHERE access_level>='$t_rep'
	    		ORDER BY username";
	    $result = db_query( $query );
	    $user_count = db_num_rows( $result );
	    for ($i=0;$i<$user_count;$i++) {
	    	$row = db_fetch_array( $result );
	    	$t_user_id   = $row["id"];
	    	$t_user_name = $row["username"];

	    	if ( $t_user_id==$p_user_id ) {
				PRINT "<option value=\"$t_user_id\" SELECTED>".$t_user_name;
			}
			else {
				PRINT "<option value=\"$t_user_id\">".$t_user_name;
			}
		}
	}
	### --------------------
	function print_duplicate_id_option_list() {
		global $g_mantis_bug_table;

	    $query = "SELECT id
	    		FROM $g_mantis_bug_table
	    		ORDER BY id ASC";
	    $result = db_query( $query );
	    $duplicate_id_count = db_num_rows( $result );
	    PRINT "<option value=\"0000000\">";

	    for ($i=0;$i<$duplicate_id_count;$i++) {
	    	$row = db_fetch_array( $result );
	    	$t_duplicate_id	= $row["id"];

			PRINT "<option value=\"$t_duplicate_id\">".$t_duplicate_id;
		}
	}
	### --------------------
	### Get current headlines and id  prefix with v_
	function print_news_item_option_list() {
		global $g_mantis_news_table, $g_project_cookie_val;

		if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
			$query = "SELECT id, headline
				FROM $g_mantis_news_table
				ORDER BY date_posted DESC";
		} else {
			$query = "SELECT id, headline
				FROM $g_mantis_news_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY date_posted DESC";
		}
	    $result = db_query( $query );
	    $news_count = db_num_rows( $result );

		for ($i=0;$i<$news_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );
			$v_headline = string_display( $v_headline );

			PRINT "<option value=\"$v_id\">$v_headline";
		}
	}
	### --------------------
	### Used for update pages
	function print_field_option_list( $p_list, $p_item="" ) {
		global $g_mantis_bug_table;

		$t_category_string = get_enum_string( $g_mantis_bug_table, $p_list );
	    $t_arr = explode( ",", $t_category_string );
		$entry_count = count( $t_arr );
		for ($i=0;$i<$entry_count;$i++) {
			$t_s = str_replace( "'", "", $t_arr[$i] );
			if ( $p_item==$t_s ) {
				PRINT "<option value=\"$t_s\" SELECTED>$t_s";
			}
			else {
				PRINT "<option value=\"$t_s\">$t_s";
			}
		} ### end for
	}
	### --------------------
	function print_assign_to_option_list( $p_id="" ) {
		global $g_mantis_user_table, $g_mantis_project_table,
				$g_mantis_project_user_list_table, $g_project_cookie_val;

		$t_dev = DEVELOPER;
		$t_man = MANAGER;
		$t_adm = ADMINISTRATOR;

		$query = "SELECT DISTINCT id, username
			FROM $g_mantis_user_table
				WHERE 	(access_level=$t_dev OR
						access_level=$t_man OR
						access_level=$t_adm)";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );
			if ( $v_id==$p_id ) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_username";
			} else {
				PRINT "<option value=\"$v_id\">$v_username";
			}
		} ### end for
	}
	### --------------------
	### Only list the PUBLIC and ENABLED projects
	function print_project_option_list( $p_project_id="" ) {
		global $g_mantis_project_table, $g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$t_access_level = get_current_user_field( "access_level" );
		$t_user_id = get_current_user_field( "id" );

		$t_pub = PUBLIC;
		$t_prv = PRIVATE;
		$query = "SELECT DISTINCT( p.id ), p.name
			FROM $g_mantis_project_table p, $g_mantis_project_user_list_table u
			WHERE (p.enabled=1 AND
				p.view_state='$t_pub') OR
				(p.enabled=1 AND
				p.view_state='$t_prv' AND
				p.access_min<='$t_access_level') OR
				(p.enabled=1 AND
				p.view_state='$t_prv' AND
				u.user_id='$t_user_id'  AND
                            u.project_id=p.id)
			ORDER BY p.name";
		$result = db_query( $query );
		$project_count = db_num_rows( $result );

		for ($i=0;$i<$project_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			if ( $p_project_id==$v_id ) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_name";
			} else {
				PRINT "<option value=\"$v_id\">$v_name";
			}
		} ### end for
	}
	### --------------------
	# prints the profiles given the user id
	function print_profile_option_list( $p_id, $p_select_id="" ) {
		global $g_mantis_user_profile_table, $g_mantis_user_pref_table;

		$query = "SELECT default_profile
			FROM $g_mantis_user_pref_table
			WHERE user_id='$p_id'";
	    $result = db_query( $query );
	    $v_default_profile = db_result( $result, 0, 0 );

		### Get profiles
		$query = "SELECT id, platform, os, os_build
			FROM $g_mantis_user_profile_table
			WHERE user_id='$p_id'
			ORDER BY id";
	    $result = db_query( $query );
	    $profile_count = db_num_rows( $result );

		PRINT "<option value=\"\">";
		for ($i=0;$i<$profile_count;$i++) {
			### prefix data with v_
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );
			$v_platform	= string_display( $v_platform );
			$v_os		= string_display( $v_os );
			$v_os_build	= string_display( $v_os_build );

			if ( $v_id==$v_default_profile ) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_platform $v_os $v_os_build";
			} else {
				PRINT "<option value=\"$v_id\">$v_platform $v_os $v_os_build";
			}
		}
	}
	### --------------------
	function print_news_project_option_list( $p_id ) {
		global 	$g_mantis_project_table, $g_mantis_project_user_list_table,
				$g_project_cookie;

		if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
			$query = "SELECT *
					FROM $g_mantis_project_table
					ORDER BY name";
		} else {
			$t_user_id = get_current_user_field( "id" );
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
			extract( $row, EXTR_PREFIX_ALL, "v" );

			if ( $v_id==$p_id ) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_name";
			} else {
				PRINT "<option value=\"$v_id\">$v_name";
			}
		} ### end for
	}
	### --------------------
	function print_category_option_list( $p_category="" ) {
		global $g_mantis_project_category_table, $g_project_cookie_val;

		$query = "SELECT *
				FROM $g_mantis_project_category_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row["category"];
			if ( $t_category==$p_category ) {
				PRINT "<option value=\"$t_category\" SELECTED>$t_category";
			} else {
				PRINT "<option value=\"$t_category\">$t_category";
			}
		}
	}
	### --------------------
	function print_version_option_list( $p_version="" ) {
		global $g_mantis_project_version_table, $g_project_cookie_val;

		$query = "SELECT *
				FROM $g_mantis_project_version_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY version DESC";
		$result = db_query( $query );
		$version_count = db_num_rows( $result );
		for ($i=0;$i<$version_count;$i++) {
			$row = db_fetch_array( $result );
			$t_version = $row["version"];
			if ( $t_version==$p_version ) {
				PRINT "<option value=\"$t_version\" SELECTED>$t_version";
			} else {
				PRINT "<option value=\"$t_version\">$t_version";
			}
		}
	}
	### --------------------
	function print_enum_string_option_list( $p_enum_string, $p_val=0 ) {
		$t_arr = explode_enum_string( $p_enum_string);
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_elem = explode_enum_arr( $t_arr[$i] );
			if ( $t_elem[0]==$p_val ) {
				PRINT "<option value=\"$t_elem[0]\" SELECTED>$t_elem[1]";
			} else {
				PRINT "<option value=\"$t_elem[0]\">$t_elem[1]";
			}
		} ### end for
	}
	### --------------------
	# @@@ Unused
	function print_enum_string( $p_value, $p_string ) {
		global $s_status_enum_string;
	}
	### --------------------
	function print_project_user_option_list( $p_val=0 ) {
		global $g_mantis_project_table, $s_access_levels_enum_string;

		$t_arr = explode_enum_string( $s_access_levels_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_elem = explode_enum_arr( $t_arr[$i] );

			if ( $t_elem[0]==ADMINISTRATOR ) { continue; }

			if ( $t_elem[0]==$p_val ) {
				PRINT "<option value=\"$t_elem[0]\" SELECTED>$t_elem[1]";
			} else {
				PRINT "<option value=\"$t_elem[0]\">$t_elem[1]";
			}
		} ### end for
	}
	### --------------------
	function print_language_option_list( $p_language ) {
		global $g_language_choices_arr;

		$t_arr = $g_language_choices_arr;
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			if ( $t_arr[$i]==$p_language ) {
				PRINT "<option value=\"$t_arr[$i]\" SELECTED>$t_arr[$i]";
			} else {
				PRINT "<option value=\"$t_arr[$i]\">$t_arr[$i]";
			}
		} ### end for
	}
	### --------------------
	###########################################################################
	# String printing API
	###########################################################################
	### --------------------
	### prints a link to a bug given an ID
	### it accounts for the user preference and site override
	function print_bug_link( $p_id ) {
		global 	$g_view_bug_page, $g_view_bug_advanced_page, $g_show_view;

		switch ( $g_show_view ) {
		case 0:
			if ( get_current_user_pref_field( "advanced_view" )==1 ) {
				PRINT "<a href=\"$g_view_bug_advanced_page?f_id=$p_id\">$p_id</a>";
			} else {
				PRINT "<a href=\"$g_view_bug_page?f_id=$p_id\">$p_id</a>";
			}
			break;
		case 1:
			PRINT "<a href=\"$g_view_bug_page?f_id=$p_id\">$p_id</a>";
			break;
		case 2:
			PRINT "<a href=\"$g_view_bug_advanced_page?f_id=$p_id\">$p_id</a>";
			break;
		}
	}
	### --------------------
	### returns a href link to a bug given an ID
	### it accounts for the user preference and site override
	function get_bug_link( $p_id ) {
		global 	$g_view_bug_page, $g_view_bug_advanced_page, $g_show_view;

		switch ( $g_show_view ) {
		case 0:
			if ( get_current_user_pref_field( "advanced_view" )==1 ) {
				return "<a href=\"$g_view_bug_advanced_page?f_id=$p_id\">$p_id</a>";
			} else {
				return "<a href=\"$g_view_bug_page?f_id=$p_id\">$p_id</a>";
			}
			break;
		case 1:
			return "<a href=\"$g_view_bug_page?f_id=$p_id\">$p_id</a>";
			break;
		case 2:
			return "<a href=\"$g_view_bug_advanced_page?f_id=$p_id\">$p_id</a>";
			break;
		}
	}
	### --------------------
	# formats the severity given the status
	# shows the severity in BOLD if the bug is NOT closed and is of significant severity
	function print_formatted_severity_string( $p_status, $p_severity ) {
		global $g_severity_enum_string, $s_severity_enum_string;

		#$t_sev_str = get_enum_element( $g_severity_enum_string, $p_severity );
		$t_sev_str = get_enum_element( $s_severity_enum_string, $p_severity );
		if ( ( ( $p_severity==MAJOR ) ||
			 ( $p_severity==CRASH ) ||
			 ( $p_severity==BLOCK ) )&&
			 ( $p_status!=CLOSED ) ) {
			PRINT "<b>$t_sev_str</b>";
		}
		else {
			PRINT "$t_sev_str";
		}
	}
	### --------------------
	function print_project_category_string( $p_project_id ) {
		global $g_mantis_project_category_table, $g_mantis_project_table;

		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE project_id='$p_project_id'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		$t_string = "";

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row["category"];

			if ( $i+1 < $category_count ) {
				$t_string .= $t_category.", ";
			} else {
				$t_string .= $t_category;
			}
		}

		return $t_string;
	}
	### --------------------
	function print_project_version_string( $p_project_id ) {
		global $g_mantis_project_version_table, $g_mantis_project_table;

		$query = "SELECT version
				FROM $g_mantis_project_version_table
				WHERE project_id='$p_project_id'";
		$result = db_query( $query );
		$version_count = db_num_rows( $result );
		$t_string = "";

		for ($i=0;$i<$version_count;$i++) {
			$row = db_fetch_array( $result );
			$t_version = $row["version"];

			if ( $i+1 < $version_count ) {
				$t_string .= $t_version.", ";
			} else {
				$t_string .= $t_version;
			}
		}

		return $t_string;
	}
	### --------------------
	###########################################################################
	# Link Printing API
	###########################################################################
	### --------------------
	function print_view_bug_sort_link(  $p_page, $p_string, $p_sort_field, $p_dir ) {
		PRINT "<b><a href=\"$p_page?f_sort=$p_sort_field&f_dir=$p_dir\">$p_string</a></b>";
	}
	### --------------------
	function print_manage_user_sort_link(  $p_page, $p_string, $p_sort_field, $p_dir, $p_hide=0 ) {
		PRINT "<b><a href=\"$p_page?f_sort=$p_sort_field&f_dir=$p_dir&f_save=1&f_hide=$p_hide\">$p_string</a></b>";
	}
	### --------------------
	function print_manage_project_sort_link(  $p_page, $p_string, $p_sort_field, $p_dir ) {
		PRINT "<b><a href=\"$p_page?f_sort=$p_sort_field&f_dir=$p_dir\">$p_string</a></b>";
	}
	### --------------------
	# print the bracketed links used near the top
	# if the $p_link is blank then the text is printed but no link is created
	function print_bracket_link( $p_link, $p_url_text ) {
		if (empty( $p_link )) {
			PRINT "[ $p_url_text ]";
		} else {
			PRINT "[ <a href=\"$p_link\">$p_url_text</a> ]";
		}
	}
	### --------------------
	# print a mailto: href link
	function print_email_link( $p_email, $p_text ) {
		PRINT get_email_link($p_email,$p_text);
	}
	### --------------------
	# return the mailto: href string link instead of printing it
	function get_email_link( $p_email, $p_text ) {
		global $g_hide_user_email;
              if($g_hide_user_email){
                return "$p_text";
              }else{
                return "<a href=\"mailto:$p_email\">$p_text</a>";
              }
	}
	### --------------------
	# print our standard mysql query error
	# this function should rarely (if ever) be reached.  instead the db_()
	# functions should trap (altough inelegantly).
	function print_sql_error( $p_query ) {
		global $s_sql_error_detected, $g_administrator_email, $s_administrator;

		PRINT "$s_sql_error_detected";
		print_email_link( $g_administrator_email, $s_administrator );
		PRINT "<p>$p_query;<p>";
	}
	### --------------------
	###########################################################################
	# Filter API
	###########################################################################
	### --------------------
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
	### --------------------
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
	### --------------------
	# @@@ does nothing
	function filter_img_tags( $p_string ) {
		return $p_string;
	}
	### --------------------
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

			$p_string = preg_replace( "/&lt;($tag)&gt;/", "<\\1>", $p_string );
		}

		return $p_string;
	}
	### --------------------
	###########################################################################
	# String Processing API
	###########################################################################
	### --------------------
	# every string that comes form a textarea should be processed through this
	# function *before* insertion into the database.
	function string_prepare_textarea( $p_string ) {
		global $g_allow_href_tags, $g_allow_html_tags;

		$p_string = htmlspecialchars( $p_string );

		if ( $g_allow_html_tags==1 ) {
			$p_string = filter_html_tags( $p_string );
		}

		if ( $g_allow_href_tags==1 ) {
			$p_string = filter_href_tags( $p_string );
		}

		#@@@$p_string = filter_img_tags( $p_string );
		$p_string = addslashes( $p_string );
		return $p_string;
	}
	### --------------------
	# every string that comes form a text field should be processed through this
	# function *before* insertion into the database.
	function string_prepare_text( $p_string ) {
		global $g_allow_href_tags, $g_allow_html_tags;
		# the " breaks a text box when you go back to edit so we will convert
		# it here
		$p_string = str_replace( "\"", "'", $p_string );
		$p_string = htmlspecialchars( $p_string );

		if ( $g_allow_html_tags==1 ) {
			$p_string = filter_html_tags( $p_string );
		}

		if ( $g_allow_href_tags==1 ) {
			$p_string = filter_href_tags( $p_string );
		}

		#@@@$p_string = filter_img_tags( $p_string );
		$p_string = addslashes( $p_string );
		return $p_string;
	}
	### --------------------
	# Use this to prepare a string for display to HTML
	function string_display( $p_string ) {
		$p_string = stripslashes( $p_string );
		$p_string = process_bug_link( $p_string );
		$p_string = nl2br( $p_string );
		return $p_string;
	}
	### --------------------
	# Prepare a string for plain text display in email
	function string_email( $p_string ) {
		$p_string = stripslashes( $p_string );
		$p_string = process_bug_link_email( $p_string );
		$p_string = str_replace( "&lt;", "<",  $p_string );
		$p_string = str_replace( "&gt;", ">",  $p_string );
		$p_string = str_replace( "&quot;", "\"",  $p_string );
		$p_string = str_replace( "&amp;", "\"",  $p_string );

		return $p_string;
	}
	### --------------------
	# Process a string for display in a textarea box
	function string_edit_textarea( $p_string ) {
		$p_string = stripslashes( $p_string );
		#@@@$p_string = str_replace( "<br>", "",  $p_string );
		$p_string = unfilter_href_tags( $p_string );
		$p_string = str_replace( "<br />", "\n",  $p_string );
		$p_string = str_replace( "&lt;", "<",  $p_string );
		$p_string = str_replace( "&gt;", ">",  $p_string );
		$p_string = str_replace( "&quot;", "\"",  $p_string );
		return $p_string;
	}
	### --------------------
	# Process a string for display in a text box
	function string_edit_text( $p_string ) {
		$p_string = stripslashes( $p_string );
		#@@@$p_string = str_replace( "<br>", "",  $p_string );
		$p_string = unfilter_href_tags( $p_string );
		$p_string = str_replace( "&lt;", "<",  $p_string );
		$p_string = str_replace( "&gt;", ">",  $p_string );
		$p_string = str_replace( "&quot;", "'",  $p_string );
		return $p_string;
	}
	### --------------------
	###########################################################################
	# Miscellaneous String Functions API
	###########################################################################
	### --------------------
	# duplicates str_pad() from PHP4
	# left pad $p_string with $p_pad until we reach $p_length
	function str_pd( $p_string, $p_pad, $p_length ) {
		$t_num = $p_length - strlen( $p_string );
		for ($i=0;$i<$t_num;$i++) {
			$p_string = $p_pad.$p_string;
		}
		return $p_string;
	}
	### --------------------
	/* word_wrap($string, $cols, $prefix)
	 *
	 * Takes $string, and wraps it on a per-word boundary (does not clip
	 * words UNLESS the word is more than $cols long), no more than $cols per
	 * line. Allows for optional prefix string for each line. (Was written to
	 * easily format replies to e-mails, prefixing each line with "> ".
	 *
	 * Copyright 1999 Dominic J. Eidson, use as you wish, but give credit
	 * where credit due.
	 */
	function word_wrap ($string, $cols = 72, $prefix = "") {

		$t_lines = split( "\n", $string);
		$outlines = "";

		while(list(, $thisline) = each($t_lines)) {
		    if(strlen($thisline) > $cols) {

				$newline = "";
				$t_l_lines = split(" ", $thisline);

				while(list(, $thisword) = each($t_l_lines)) {
				    while((strlen($thisword) + strlen($prefix)) > $cols) {
						$cur_pos = 0;
						$outlines .= $prefix;

						for($num=0; $num < $cols-1; $num++) {
						    $outlines .= $thisword[$num];
						    $cur_pos++;
						} ### end for

						$outlines .= "\n";
						$thisword = substr($thisword, $cur_pos, (strlen($thisword)-$cur_pos));
				    } ### end innermost while

				    if((strlen($newline) + strlen($thisword)) > $cols) {
						$outlines .= $prefix.$newline."\n";
						$newline = $thisword." ";
				    } else {
						$newline .= $thisword." ";
				    }
				}  ### end while

				$outlines .= $prefix.$newline."\n";
		    } else {
				$outlines .= $prefix.$thisline."\n";
		    }
		} ### end outermost while
		return $outlines;
    }
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>