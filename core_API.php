<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	### INCLUDES                                                            ###
	###########################################################################

	require( "config_inc.php" );
	require( "strings_".$g_language.".txt" );

	###########################################################################
	### FUNCTIONS                                                           ###
	###########################################################################

	###########################################################################
	# Database : MYSQL for now
	###########################################################################
	### --------------------
	# connect to database
	function db_connect($p_hostname="localhost", $p_username="root",
						$p_password="", $p_database="mantis",
						$p_port=3306 ) {

		$t_result = mysql_connect(  $p_hostname.":".$p_port,
									$p_username, $p_password );
		$t_result = mysql_select_db( $p_database );

		### Temproary error handling
		if ( !$t_result ) {
			echo "ERROR: FAILED CONNECTION TO DATABASE";
			exit;
		}
	}
	### --------------------
	# persistent connect to database
	function db_pconnect($p_hostname="localhost", $p_username="root",
						$p_password="", $p_database="mantis",
						$p_port=3306 ) {

		$t_result = mysql_pconnect(  $p_hostname.":".$p_port,
									$p_username, $p_password );
		$t_result = mysql_select_db( $p_database );

		### Temproary error handling
		if ( !$t_result ) {
			echo "ERROR: FAILED CONNECTION TO DATABASE";
			exit;
		}
	}
	### --------------------
	# execute query, requires connection to be opened,
	# goes to error page if error occurs
	# Use this when you don't want to handler an error yourself
	function db_query( $p_query ) {

		$t_result = mysql_query( $p_query );
		if ( !$t_result ) {
			echo "ERROR: FAILED QUERY: ".$p_query;
			exit;
		}
		else {
			return $t_result;
		}
	}
	### --------------------
	function db_select_db( $p_db_name ) {
		return mysql_select_db( $p_db_name );
	}
	### --------------------
	function db_num_rows( $p_result ) {
		return mysql_num_rows( $p_result );
	}
	### --------------------
	function db_fetch_array( $p_result ) {
		return mysql_fetch_array( $p_result );
	}
	### --------------------
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {
		if ( $p_result && ( db_num_rows( $p_result ) > 0 ) ) {
			return mysql_result( $p_result, $p_index1, $p_index2 );
		}
		else {
			return false;
		}
	}
	### --------------------
	function db_close() {
		$t_result = mysql_close();
	}
	### --------------------
	###########################################################################
	# Core HTML API
	###########################################################################
	### --------------------
	function print_html_top() {
		PRINT "<html>";
	}
	### --------------------
	function print_head_top() {
	   PRINT "<head>";
	}
	### --------------------
	function print_title( $p_title ) {
	   PRINT "<title>$p_title</title>";
	}
	### --------------------
	function print_css( $p_css="" ) {
		if ( !empty($p_css )) {
			include( "$p_css" );
		}
	}
	### --------------------
	function print_meta_redirect( $p_url, $p_time ) {
	   PRINT "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\">";
	}
	### --------------------
	function print_head_bottom() {
	   PRINT "</head>";
	}
	### --------------------
	function print_body_top() {
		PRINT "<body>";
	}
	### --------------------
	function print_header( $p_title="Mantis" ) {
		global 	$g_show_project_in_title,
				$g_project_cookie_val,
				$g_mantis_project_table;

		if ( $g_show_project_in_title==1 ) {
			$query = "SELECT name
					FROM $g_mantis_project_table
					WHERE id='$g_project_cookie_val'";
			$result = db_query( $query );
			$t_project_name = db_result( $result, 0, 0 );

			PRINT "<h3>$t_project_name</h3>";
		} else {
			PRINT "<h3>$p_title</h3>";
		}
	}
	### --------------------
	function print_top_page( $p_page ) {
		if ( file_exists( $p_page ) ) {
			include( $p_page );
		}
	}
	### --------------------
	function print_bottom_page( $p_page ) {
		if ( file_exists( $p_page ) ) {
			include( $p_page );
		}
	}
	### --------------------
	function print_footer( $p_file ) {
		global 	$g_string_cookie_val, $g_webmaster_email, $g_show_source,
				$g_menu_include_file, $g_show_footer_menu;

		if (isset($g_string_cookie_val)) {
			if ( $g_show_footer_menu ) {
				print_bottom_menu( $g_menu_include_file );
			}
		}

		print_source_link( $p_file );

		PRINT "<p>";
		PRINT "<hr size=1>";
		print_mantis_version();
		PRINT "<address>Copyright (C) 2000, 2001</address>";
		PRINT "<address><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></address>";
	}
	### --------------------
	function print_body_bottom() {
		PRINT "</body>";
	}
	### --------------------
	function print_html_bottom() {
		PRINT "</html>";
	}
	### --------------------
	###########################################################################
	# HTML Appearance Helper API
	###########################################################################
	### --------------------
	# prints the user that is logged in and the date/time
	function print_login_info() {
		global 	$g_mantis_user_table,
				$g_string_cookie_val, $g_project_cookie_val,
				$g_complete_date_format, $g_set_project,
				$s_switch, $s_logged_in_as;

		$t_username = get_current_user_field( "username" );
		$t_now = date($g_complete_date_format);
		PRINT "<table width=100%><tr>";
		PRINT "<td align=left width=33%>";
			PRINT "$s_logged_in_as: <i>$t_username</i>";
		PRINT "</td>";
		PRINT "<td align=center width=34%>";
			PRINT "<i>$t_now</i>";
		PRINT "</td>";
		PRINT "<td align=right width=33%>";
			PRINT "<form method=post action=$g_set_project>";
			PRINT "<select name=f_project_id>";
				print_project_option_list( $g_project_cookie_val );
			PRINT "</select>";
			PRINT "<input type=submit value=\"$s_switch\">";
		PRINT "</td>";
			PRINT "</form>";
		PRINT "</tr></table>";
	}
	### --------------------
	function print_menu( $p_menu_file="" ) {
		global 	$g_primary_border_color, $g_primary_color_light;

		print_login_info();

		PRINT "<table width=100% bgcolor=$g_primary_border_color>";
		PRINT "<tr align=center height=20>";
			PRINT "<td align=center bgcolor=$g_primary_color_light>";
				include( $p_menu_file );
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}
	### --------------------
	function print_bottom_menu( $p_menu_file="" ) {
		global 	$g_primary_border_color, $g_primary_color_light;

		PRINT "<p>";
		PRINT "<table width=100% bgcolor=$g_primary_border_color>";
		PRINT "<tr align=center height=20>";
			PRINT "<td align=center bgcolor=$g_primary_color_light>";
				include( $p_menu_file );
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}
	### --------------------
	### checks to see whether we need to be displaying the source link
	function print_source_link( $p_file ) {
		global $g_show_source, $g_show_source_page, $g_string_cookie_val;

		if (!isset($g_string_cookie_val)) {
			return;
		}

		if ( $g_show_source==1 ) {
			if ( access_level_check_greater_or_equal( "administrator" ) ) {
				PRINT "<p>";
				PRINT "<div align=center>";
				PRINT "<a href=\"$g_show_source_page?f_url=$p_file\">Show Source</a>";
				PRINT "</div>";
			}
		}
		else if ( $g_show_source==2 ) {
			PRINT "<p>";
			PRINT "<div align=center>";
			PRINT "<a href=\"$g_show_source_page?f_url=$p_file\">Show Source</a>";
			PRINT "</div>";
		}
	}
	### --------------------
	### checks to see whether we need to be displaying the version number
	function print_mantis_version() {
		global $g_mantis_version, $g_show_version;

		if ( $g_show_version==1 ) {
			PRINT "<i>Mantis version $g_mantis_version</i>";
		}
	}
	### --------------------
	function print_manage_menu() {
		global 	$g_path, $g_manage_create_user_page, $g_manage_project_menu_page,
				$g_documentation_page,
				$s_create_new_account_link, $s_manage_categories_link,
				$s_manage_product_versions_link, $s_documentation_link,
				$s_projects;

		PRINT "<div align=center>";
			PRINT "[ <a href=\"$g_path.$g_manage_create_user_page\">$s_create_new_account_link</a> ] ";
			PRINT "[ <a href=\"$g_path.$g_manage_project_menu_page\">$s_projects</a> ] ";
			PRINT "[ <a href=\"$g_path.$g_documentation_page\">$s_documentation_link</a> ]";
		PRINT "</div>";
	}
	### --------------------
	function print_documentaion_link( $p_a_name="" ) {
		global $g_documentation_html;

		PRINT "<a href=\"$g_documentation_html#$p_a_name\" target=_info>[?]</a>";
	}
	###########################################################################
	# Basic Print API
	###########################################################################
	### --------------------
	function print_user( $p_user_id ) {
		global $g_mantis_user_table;

		if ( $p_user_id=="0000000" ) {
			return;
		}
	    $query = "SELECT username, email
	    		FROM $g_mantis_user_table
	    		WHERE id='$p_user_id'";
	    $result = db_query( $query );
	    if ( db_num_rows( $result )>0 ) {
			$t_handler_username	= db_result( $result, 0, 0 );
			$t_handler_email	= db_result( $result, 0, 1 );

			PRINT "<a href=\"mailto:$t_handler_email\">".$t_handler_username."</a>";
		}
		else {
			PRINT "user no longer exists";
		}
	}
	### --------------------
	function print_duplicate_id( $p_duplicate_id ) {
		global 	$g_view_bug_page, $g_view_bug_advanced_page,
				$g_mantis_user_pref_table;

		if ( $p_duplicate_id!='0000000' ) {
			if ( get_user_value( $g_mantis_user_pref_table, "advanced_view" )=="on" ) {
				PRINT "<a href=\"$g_view_bug_page?f_id=$p_duplicate_id\">".$p_duplicate_id."</a>";
			}
			else {
				PRINT "<a href=\"$g_view_bug_page?f_id=$p_duplicate_id\">".$p_duplicate_id."</a>";
			}
		}
	}
	### --------------------
	### Returns the number of bugntoes for the given bug_id
	function get_bugnote_count( $p_id ) {
		global $g_mantis_bugnote_table;

		$query = "SELECT COUNT(*)
					FROM $g_mantis_bugnote_table
					WHERE bug_id ='$p_id'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	### --------------------
	###########################################################################
	# Option List Printing API
	###########################################################################
	### --------------------
	function print_handler_option_list( $p_handler_id ) {
		global $g_mantis_user_table;

	    $query = "SELECT id, username
	    		FROM $g_mantis_user_table
	    		WHERE access_level='administrator' OR access_level='developer'
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

		if ( get_current_user_field( "access_level" )=="administrator" ) {
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
			$v_headline = string_unsafe( $v_headline );
			$v_headline = htmlspecialchars( $v_headline );

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
		global $g_mantis_user_table;

		$query = "SELECT id, username
			FROM $g_mantis_user_table
			WHERE access_level='developer' OR
				access_level='administrator'";
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
	function print_project_option_list( $p_project_id="" ) {
		global $g_mantis_project_table,
				$g_project_cookie_val;

		$query = "SELECT id, name
			FROM $g_mantis_project_table
			WHERE enabled='on' AND view_state='public'
			ORDER BY name";
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
		global $g_mantis_user_profile_table;

		### Get profiles
		$query = "SELECT id, platform, os, os_build, default_profile
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
			$v_platform	= string_unsafe( $v_platform );
			$v_os		= string_unsafe( $v_os );
			$v_os_build	= string_unsafe( $v_os_build );

			if ( $p_select_id==$v_id ) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_platform $v_os $v_os_build";
			} else if (( $v_default_profile=="on" )&&( empty( $p_select_id ) )) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_platform $v_os $v_os_build";
			} else {
				PRINT "<option value=\"$v_id\">$v_platform $v_os $v_os_build";
			}
		}
	}
	### --------------------
	function print_project_status_option_list( $p_status="" ) {
		global $g_mantis_project_table;

		$t_enum_string = get_enum_string( $g_mantis_project_table, "status" );
		$t_arr = explode( ",", $t_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = str_replace( "'", "", $t_arr[$i] );
			if ( $t_s==$p_status ) {
				PRINT "<option value=\"$t_s\" SELECTED>$t_s";
			} else {
				PRINT "<option value=\"$t_s\">$t_s";
			}
		} ### end for
	}
	### --------------------
	function print_news_project_option_list( $p_id ) {
		global 	$g_mantis_project_table, $g_mantis_project_user_list_table,
				$g_project_cookie;

		$t_user_id = get_current_user_field( "id" );
		$t_access_level = get_current_user_field( "access_level" );
		if ( $t_access_level=="administrator" ) {
			$query = "SELECT *
					FROM $g_mantis_project_table
					ORDER BY name";
		} else {
			$query = "SELECT p.id, p.name
					FROM $g_mantis_project_table p, $g_mantis_project_user_list_table m
					WHERE 	p.id=m.project_id AND
							m.user_id='$t_user_id AND m.enabled='on' AND
							m.access_level='manager'";
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
	function print_table_field_option_list( $p_table_name, $p_field_name, $p_item="" ) {
		$t_field_string = get_enum_string( $p_table_name, $p_field_name );
	    $t_arr = explode( ",", $t_field_string );
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
	###########################################################################
	# String printing API
	###########################################################################
	### --------------------
	function get_enum_string( $p_table_name, $p_field_name ) {
		$query = "SHOW FIELDS
				FROM $p_table_name";
		$result = db_query( $query );
		$entry_count = db_num_rows( $result );
		for ($i=0;$i<$entry_count;$i++) {
			$row = db_fetch_array( $result );
	    	$t_type = stripslashes($row["Type"]);
	    	$t_field = $row["Field"];
	    	if ( $t_field==$p_field_name ) {
		    	return substr( $t_type, 5, strlen($t_type)-6);
		    }
	    } ### end for
	}
	### --------------------
	# returns the number of items in a list
	# default delimiter is a ,
	function get_list_item_count( $p_delim_char, $t_enum_string ) {
		return count( explode( $p_delim_char,$t_enum_string ) );
	}
	### --------------------
	### prints a link to a bug given an ID
	### it accounts for the user preference
	function print_bug_link( $p_id ) {
		global $g_mantis_user_pref_table, $g_view_bug_page, $g_view_bug_advanced_page;

		if ( get_user_value( $g_mantis_user_pref_table, "advanced_view" )=="on" ) {
			PRINT "<a href=\"$g_view_bug_advanced_page?f_id=$p_id\">$p_id</a>";
		}
		else {
			PRINT "<a href=\"$g_view_bug_page?f_id=$p_id\">$p_id</a>";
		}
	}
	### --------------------
	### formats the severity given the status
	function print_formatted_severity( $p_status, $p_severity ) {
		if ( ( ( $p_severity=="major" ) ||
			 ( $p_severity=="crash" ) ||
			 ( $p_severity=="block" ) )&&
			 ( $p_status!="resolved" ) ) {
			PRINT "<b>$p_severity</b>";
		}
		else {
			PRINT "$p_severity";
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
	# Helper API
	###########################################################################
	### --------------------
	### Returns the specified field of the specified bug
	function get_bug_field( $p_field_name, $p_bug_id ) {
		global 	$g_string_cookie_val,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_bug_table;

		db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

		### get info
		$query = "SELECT $p_field_name
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	### --------------------
	### Returns the specified field of the specified bug_text
	function get_bug_text_field( $p_field_name ) {
		global 	$g_string_cookie_val,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_bug_text_table;

		db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

		### get info
		$query = "SELECT $p_field_name
				FROM $g_mantis_bug_text_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	### --------------------
	###########################################################################
	# Summary printing API
	###########################################################################
	### --------------------
	### Used in summary reports
	function print_bug_enum_summary( $p_enum, $p_status="" ) {
		global $g_mantis_bug_table, $g_primary_color_light, $g_primary_color_dark,
			$g_project_cookie_val;

		$t_enum_string = get_enum_string( $g_mantis_bug_table, $p_enum );
		$t_arr = explode( ",", $t_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = str_replace( "'", "", $t_arr[$i] );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s' AND
						project_id='$g_project_cookie_val'";
			if ( !empty( $p_status ) ) {
				if ( $p_status=="open" ) {
					$query = $query." AND status<>'resolved'";
				}
				else if ( $p_status=="open" ) {
					$query = $query." AND status='resolved'";
				}
				else {
					$query = $query." AND status='$p_status'";
				}
			}
			$result = db_query( $query );
			$t_enum_count = db_result( $result, 0 );

			### alternate row colors
			if ( $i % 2 == 1) {
				$bgcolor=$g_primary_color_light;
			}
			else {
				$bgcolor=$g_primary_color_dark;
			}

			PRINT "<tr align=center bgcolor=$bgcolor>";
				PRINT "<td width=50%>";
					echo $t_s;
				PRINT "</td>";
				PRINT "<td width=50%>";
					echo $t_enum_count;
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	# expects the paramter to be neutral in time length
	# automatically adds the -
	function get_bug_count_by_date( $p_time_length="day" ) {
		global $g_mantis_bug_table, $g_project_cookie_val;

		$day = strtotime( "-".$p_time_length );
		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE UNIX_TIMESTAMP(last_updated)>$day AND
					project_id='$g_project_cookie_val'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	### --------------------
	### Used in summary reports
	function print_bug_date_summary( $p_date_array ) {
		global $g_mantis_bug_table, $g_primary_color_light, $g_primary_color_dark;

		$arr_count = count( $p_date_array );
		for ($i=0;$i<$arr_count;$i++) {
			$t_enum_count = get_bug_count_by_date( $p_date_array[$i] );

			### alternate row colors
			if ( $i % 2 == 1) {
				$bgcolor=$g_primary_color_light;
			}
			else {
				$bgcolor=$g_primary_color_dark;
			}

			PRINT "<tr align=center bgcolor=$bgcolor>";
				PRINT "<td width=50%>";
					echo $p_date_array[$i];
				PRINT "</td>";
				PRINT "<td width=50%>";
					echo $t_enum_count;
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	function print_developer_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_primary_color_light, $g_primary_color_dark,
				$g_project_cookie_val;

		$query = "SELECT id, username
				FROM $g_mantis_user_table
				WHERE access_level='developer' OR access_level='administrator'
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status<>'resolved' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='resolved' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			### alternate row colors
			if ( $i % 2 == 1) {
				$bgcolor=$g_primary_color_light;
			}
			else {
				$bgcolor=$g_primary_color_dark;
			}

			PRINT "<tr align=center bgcolor=$bgcolor>";
				PRINT "<td width=50%>";
					echo $v_username;
				PRINT "</td>";
				PRINT "<td width=50%>";
					PRINT "$open_bug_count / $resolved_bug_count / $total_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	function print_category_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_category_table, $g_project_cookie_val,
				$g_primary_color_light, $g_primary_color_dark;

		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row["category"];

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$t_category' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$catgory_bug_count = db_result( $result2, 0, 0 );


			### alternate row colors
			if ( $i % 2 == 1) {
				$bgcolor=$g_primary_color_light;
			}
			else {
				$bgcolor=$g_primary_color_dark;
			}

			PRINT "<tr align=center bgcolor=$bgcolor>";
				PRINT "<td width=50%>";
					echo $t_category;
				PRINT "</td>";
				PRINT "<td width=50%>";
					PRINT "$catgory_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	###########################################################################
	# Cookie API
	###########################################################################
	### --------------------
	### checks to see that a user is logged in
	### if the user is and the account is enabled then let them pass
	### otherwise redirect them to the login page
	function login_cookie_check( $p_redirect_url="" ) {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$g_login_page, $g_logout_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {
			if ( empty( $g_project_cookie_val ) ) {
				header( "Location: $g_logout_page" );
				exit;
			}

			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$t_enabled = get_current_user_field( "enabled" );
			### check for acess enabled
			if ( $t_enabled!="on" ) {
				header( "Location: $g_logout_page" );
			}

			### grab creation date to protect from change
			$t_date_created = get_current_user_field( "date_created" );
			### update last_visit date
			$query = "UPDATE $g_mantis_user_table
					SET last_visit=NOW(), date_created='$t_date_created'
					WHERE cookie_string='$g_string_cookie_val'";
			$result = db_query( $query );
			db_close();

			if ( empty( $g_project_cookie_val ) ) {
				header( "Location: $p_redirect_url" );
				exit;
			}

			### go to redirect
			if ( !empty( $p_redirect_url ) ) {
				header( "Location: $p_redirect_url" );
				exit;
			}
			### continue with current page
			else {
				return;
			}
		}
		### not logged in
		else {
			header( "Location: $g_login_page" );
			exit;
		}
	}
	### --------------------
	### checks to see if a returning user is valid
	### also sets the last time they visited
	### otherwise redirects to the login page
	function index_login_cookie_check( $p_redirect_url="" ) {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$g_login_page, $g_logout_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {
			if ( empty( $g_project_cookie_val ) ) {
				header( "Location: $g_login_page" );
				exit;
			}

			### set last visit cookie

			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$t_enabled = get_current_user_field( "enabled" );

			### check for acess enabled
			if ( $t_enabled!="on" ) {
				header( "Location: $g_login_page" );
			}

			$t_last_access = get_current_user_field( "last_visit" );

			### go to redirect
			if ( !empty( $p_redirect_url ) ) {
				header( "Location: $p_redirect_url" );
				exit;
			}
			### continue with current page
			else {
				return;
			}
		}
		### not logged in
		else {
			header( "Location: $g_login_page" );
			exit;
		}
	}
	### --------------------
	###########################################################################
	# Authentication API
	###########################################################################
	### --------------------
	function password_match( $p_test_password, $p_password ) {
		$salt = substr( $p_password, 0, 2 );
		if ( crypt( $p_test_password, $salt ) == $p_password ) {
			return true;
		}
		else {
			return false;
		}
	}
	### --------------------
	function create_random_password( $p_email ) {
		mt_srand( time() );
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		return substr( crypt( md5( $p_email.$t_val ) ), 0, 12 );
	}
	### --------------------
	function check_project() {
	}
	### --------------------
	###########################################################################
	# User Management API
	###########################################################################
	### --------------------
	# This string is used to use as the login identified for the web cookie
	# It is not guarranteed to be unique but should be good enough
	# It is chopped to be 128 characters in length to fit into the database
	function create_cookie_string( $p_email ) {
		mt_srand( time() );
		$t_val = mt_rand( 1000, mt_getrandmax() ) + mt_rand( 1000, mt_getrandmax() );
		$t_string = $p_email.$t_val;
		$t_cookie_string = crypt( $t_string ).md5( time() );
		$t_cookie_string = $t_cookie_string.crypt( $t_string, $t_string ).md5( $t_string ).mt_rand( 1000, mt_getrandmax() );

		return substr( $t_cookie_string, 0, 128 );
	}
	### --------------------
	### Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_field( $p_field_name ) {
		global 	$g_string_cookie_val,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {

			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = db_query( $query );
			return db_result( $result, 0 );
		}
		else {
			return 0;
		}
	}
	### --------------------
	### Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_profile_field( $p_field_name ) {
		global 	$g_string_cookie_val,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table, $g_mantis_user_pref_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {

			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			$t_id = get_current_user_field( "id" );
			### get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_pref_table
					WHERE user_id='$t_id'";
			$result = db_query( $query );
			return db_result( $result, 0 );
		}
		else {
			return 0;
		}
	}
	### --------------------
	###########################################################################
	# Preferences API
	###########################################################################
	### --------------------
	### return a vlue of a table of the currently logged in user
	function get_user_value( $p_table_name, $p_table_field ) {
		global 	$g_hostname, $g_db_username, $g_db_password, $g_database_name;

		### get user id
		$u_id = get_current_user_field( "id " );

		if ( $u_id ) {
			db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
			$query = "SELECT $p_table_field
					FROM $p_table_name
					WHERE user_id='$u_id'";
			$result = db_query( $query );

			if ( db_num_rows( $result ) > 0 ) {
				return db_result( $result, 0 );
			}
			else {
				return "";
			}
		}
		else {
			return "";
		}
	}
	### --------------------
	###########################################################################
	# Date API
	###########################################################################
	### --------------------
	function sql_to_unix_time( $p_timeString ) {
		return mktime( substr( $p_timeString, 8, 2 ),
					   substr( $p_timeString, 10, 2 ),
					   substr( $p_timeString, 12, 2 ),
					   substr( $p_timeString, 4, 2 ),
					   substr( $p_timeString, 6, 2 ),
					   substr( $p_timeString, 0, 4 ) );
	}
	### --------------------
	###########################################################################
	# String API
	###########################################################################
	### --------------------
	function string_safe( $p_string ) {
		return addslashes( nl2br( $p_string ) );
	}
	### --------------------
	function string_unsafe( $p_string ) {
		return stripslashes( $p_string );
	}
	### --------------------
	function string_display( $p_string ) {
		return htmlspecialchars(stripslashes( $p_string ));
	}
	### --------------------
	function string_display_with_br( $p_string ) {
		return str_replace( "&lt;br&gt;", "<br>", htmlspecialchars(stripslashes( $p_string )));
	}
	### --------------------
	function string_edit( $p_string ) {
		return str_replace( "<br>", "",  stripslashes( $p_string ) );
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
	function word_wrap ($string, $cols = 80, $prefix = "") {

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
	###########################################################################
	# Access Control API
	###########################################################################
	### --------------------
	# "administrator", "developer", "updater", "reporter", "viewer"
	### --------------------
	### This is a helper function used to order the access levels
	function access_level_value( $p_access_level ) {
		if ( $p_access_level == "administrator" ) {
			return 10;
		}
		else if ( $p_access_level == "manager" ) {
			return 8;
		}
		else if ( $p_access_level == "developer" ) {
			return 7;
		}
		else if ( $p_access_level == "updater" ) {
			return 5;
		}
		else if ( $p_access_level == "reporter" ) {
			return 3;
		}
		else if ( $p_access_level == "viewer" ) {
			return 1;
		}

	}
	### --------------------
	### check to see if the access level is strictly equal
	function access_level_check_equal( $p_access_level ) {
		global $g_string_cookie_val, $g_mantis_user_table;

		if ( !isset($g_string_cookie_val) ) {
			return false;
		}

		$t_access_level = get_current_user_field( "access_level" );
		if ( access_level_value( $t_access_level ) == access_level_value( $p_access_level ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	### --------------------
	### check to see if the access level is equal or greater
	function access_level_check_greater_or_equal( $p_access_level ) {
		global $g_string_cookie_val, $g_mantis_user_table;

		if (( !isset( $g_string_cookie_val ) )||
			( empty( $g_string_cookie_val ) )) {
			return false;
		}

		$t_access_level = get_current_user_field( "access_level" );
		if ( access_level_value( $t_access_level ) >= access_level_value( $p_access_level ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	### --------------------
	function is_manager_for_project( $p_project_id ) {
		global $g_mantis_project_table, $g_mantis_project_user_list_table;

		$t_user_id = get_current_user_field( "id" );
		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_user_list_table
				WHERE project_id='$p_project_id' AND user_id='$t_user_id' AND
					access_level='manager'";
		$result = db_query( $query );
		$t_count = db_result( $result );
		if ($t_count > 1) {
			return true;
		} else {
			return false;
		}
	}
	### --------------------
	###########################################################################
	# Email API
	###########################################################################
	### --------------------
	### Send password to user
	function email_signup( $p_user_id, $p_password ) {
		global $g_mantis_user_table, $g_mantis_url,
			$s_new_account_subject,
			$s_new_account_greeting, $s_new_account_url,
			$s_new_account_username, $s_new_account_password,
			$s_new_account_message, $s_new_account_do_not_reply;

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		### Build Welcome Message
		$t_message = $s_new_account_greeting.
						$s_new_account_url.$g_mantis_url."\n".
						$s_new_account_username.$v_username."\n".
						$s_new_account_password.$p_password."\n\n".
						$s_new_account_message.
						$s_new_account_do_not_reply;

		$t_headers = "";
		email_send( $v_email, $s_new_account_subject, $t_message, $t_headers );
	}
	### --------------------
	### Send new password when user forgets
	function email_reset( $p_user_id, $p_password ) {
		global 	$g_mantis_user_table, $g_mantis_url,
				$s_reset_request_msg, $s_account_name_msg,
				$s_news_password_msg;

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		### Build Welcome Message
		$t_message = $s_reset_request_msg."\n\n".
					$s_account_name_msg.": ".$v_username."\n".
					$s_news_password_msg.": ".$p_password."\n\n";
					$g_mantis_url."\n\n";

		email_send( $v_email, "New Password", $t_message );
	}
	### --------------------
	function email_new_bug( $p_bug_id ) {
		global $g_mantis_user_table, $s_new_bug_msg, $g_email_new_address;

		$query = "SELECT id
				FROM $g_mantis_user_table
				WHERE access_level='developer' OR
						access_level='manager' OR
						access_level='administrator'";
		$result = db_query( $query );

		$user_count = db_num_rows( $result );
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			$t_id = $row["id"];

			email_bug_info2( $p_bug_id, $s_new_bug_msg, $t_id );
		}

		if ( !empty($g_email_new_address) ) {
			email_bug_info3( $p_bug_id, $s_new_bug_msg, $g_email_new_address );
		}
	}
	### --------------------
	function email_new_bug2( $p_bug_id ) {
		global $g_mantis_user_table, $s_new_bug_msg, $g_email_new_address;

		$query = "SELECT email
				FROM $g_mantis_user_table
				WHERE access_level='developer' OR
						access_level='manager' OR
						access_level='administrator'";
		$result = db_query( $query );

		$user_count = db_num_rows( $result );
		$t_bcc_header = "bcc:";
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			$t_email = $row["email"];

			$t_bcc_header .= $t_email.", ";
		}

		$t_bcc_header = substr( $t_bcc_header, 0, strlen( $t_bcc_header ) - 2 )."\n";

		if ( $user_count > 0 ) {
			email_bug_info4( $p_bug_id, $s_new_bug_msg, $g_email_new_address, $t_bcc_header );
		}
	}
	### --------------------
	### Notify reporter and handler when new bugnote is added
	function email_bugnote_add( $p_bug_id ) {
		global $s_email_bugnote_msg, $g_email_update_address;

		email_bug_info( $p_bug_id, $s_email_bugnote_msg );

		if ( !empty($g_email_update_address) ) {
			email_bug_info3( $p_bug_id, $s_email_bugnote_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_resolved( $p_bug_id ) {
		global $s_email_resolved_msg, $g_email_update_address;

		email_bug_info( $p_bug_id, $s_email_resolved_msg );

		if ( !empty($g_email_update_address) ) {
			email_bug_info3( $p_bug_id, $s_email_resolved_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_feedback( $p_bug_id ) {
		global $s_email_feedback_msg, $g_email_update_address;

		email_bug_info( $p_bug_id, $s_email_feedback_msg );

		if ( !empty($g_email_update_address) ) {
			email_bug_info3( $p_bug_id, $s_email_feedback_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_reopen( $p_bug_id ) {
		global $s_email_resolved_msg, $g_email_update_address;

		email_bug_info( $p_bug_id, $s_email_resolved_msg );

		if ( !empty($g_email_update_address) ) {
			email_bug_info3( $p_bug_id, $s_email_resolved_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_assign( $p_bug_id ) {
		global $s_email_resolved_msg, $g_email_update_address;

		email_bug_info( $p_bug_id, $s_email_resolved_msg );

		if ( !empty($g_email_update_address) ) {
			email_bug_info3( $p_bug_id, $s_email_resolved_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_build_bug_message( $p_bug_id ) {
		global 	$g_mantis_bug_table, $g_mantis_bug_text_table,
				$g_mantis_user_table, $g_mantis_project_table,
				$g_complete_date_format,
				$g_bugnote_order, $g_mantis_url, $g_view_bug_page,
				$s_email_reporter, $s_email_handler,
				$s_email_project, $s_email_bug, $s_email_category,
				$s_email_reproducibility, $s_email_severity,
				$s_email_priority, $s_email_status, $s_email_resolution,
				$s_email_duplicate, $s_email_date_submitted,
				$s_email_last_modified, $s_email_summary,
				$s_email_description;

		$query = "SELECT *
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		$query = "SELECT *
				FROM $g_mantis_bug_text_table
				WHERE id='$v_bug_text_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v2" );

		$query = "SELECT name
				FROM $g_mantis_project_table
				WHERE id='$v_project_id'";
		$result = db_query( $query );
		$t_project_name = db_result( $result, 0, 0 );

		if ( $v_reporter_id > "0000000" ) {
			$query = "SELECT username
					FROM $g_mantis_user_table
					WHERE id='$v_reporter_id'";
			$result = db_query( $query );
			$t_reporter_name = db_result( $result, 0, 0 );
		} else {
			$t_reporter_name = "Does not exist";
		}

		$t_handler_name = "";
		if ( $v_handler_id > "0000000" ) {
			$query = "SELECT username
					FROM $g_mantis_user_table
					WHERE id='$v_handler_id'";
			$result = db_query( $query );
			$t_handler_name = db_result( $result, 0, 0 );
		} else {
			$t_handler_name = "Does not exist";
		}

		$v2_description = stripslashes( str_replace( "<br>", "", $v2_description ) );
		$v_summary = stripslashes( $v_summary );
		$v_date_submitted = date( $g_complete_date_format, sql_to_unix_time( $v_date_submitted ) );
		$v_last_updated = date( $g_complete_date_format, sql_to_unix_time( $v_last_updated ) );

		$t_message = "=======================================================================\n";
		$t_message .= $g_mantis_url.$g_view_bug_page."?f_id=".$p_bug_id."\n";
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_reporter:        $t_reporter_name\n";
		$t_message .= "$s_email_handler:         $t_handler_name\n";
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_project:         $t_project_name\n";
		$t_message .= "$s_email_bug:             $v_id\n";
		$t_message .= "$s_email_category:        $v_category\n";
		$t_message .= "$s_email_reproducibility: $v_reproducibility\n";
		$t_message .= "$s_email_severity:        $v_severity\n";
		$t_message .= "$s_email_priority:        $v_priority\n";
		$t_message .= "$s_email_status:          $v_status\n";
		if ( $v_status=="resolved" ) {
			$t_message .= "$s_email_resolution:      $v_resolution\n";
			if ( $v_resolution=="duplicate" ) {
				$t_message .= "$s_email_duplicate:      $v_duplicate_id\n";
			}
		}
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_date_submitted:   $v_date_submitted\n";
		$t_message .= "$s_email_last_modified:    $v_last_updated\n";
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_summary:  $v_summary\n\n";
		$t_message .= "$s_email_description: $v2_description\n";
		$t_message .= "=======================================================================\n\n";

		return $t_message;
	}
	### --------------------
	function email_build_bugnote_message( $p_bug_id ) {
		global 	$g_mantis_bugnote_table, $g_mantis_bugnote_text_table,
				$g_mantis_user_table, $g_complete_date_format,
				$g_bugnote_order;

		$t_message = "";

		$query = "SELECT *
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$p_bug_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$bugnote_count = db_num_rows( $result );

		### BUILT MESSAGE
		for ( $i=0; $i<$bugnote_count; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "t" );

			$query = "SELECT note
					FROM $g_mantis_bugnote_text_table
					WHERE id='$t_bugnote_text_id'";
			$result2 = db_query( $query );

			$query = "SELECT username
					FROM $g_mantis_user_table
					WHERE id='$t_reporter_id'";
			$result3 = db_query( $query );
			$t_username = db_result( $result3, 0, 0 );

			$t_note = db_result( $result2, 0, 0 );
			$t_note = string_edit( $t_note );
			$t_last_modified = date( $g_complete_date_format, sql_to_unix_time( $t_last_modified ) );
			$t_string = " ".$t_username." - ".$t_last_modified." ";
			$t_message = $t_message."-----------------------------------------------------------------------\n";
			$t_message = $t_message.$t_string."\n";
			$t_message = $t_message."-----------------------------------------------------------------------\n";
			$t_message = $t_message.$t_note."\n\n";
		}

		return $t_message;
	}
	### --------------------
	### Send bug info to reporter and handler
	function email_bug_info( $p_bug_id, $p_message ) {
		global $g_mantis_user_table, $g_mantis_bug_table, $g_mantis_project_table;

		### Get Subject
		$query = "SELECT project_id, summary
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		$p_subject = $row["summary"];
		$t_project_id = $row["project_id"];

		$query = "SELECT name
				FROM $g_mantis_project_table
				WHERE id='$t_project_id'";
		$result = db_query( $query );
		$t_project_name = db_result( $result, 0, 0 );

		### Get Reporter and Handler IDs
		$query = "SELECT reporter_id, handler_id
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		### Get Reporter Email
		$query = "SELECT email
				FROM $g_mantis_user_table
				WHERE id='$v_reporter_id'";
		$result = db_query( $query );
		$t_reporter_email = db_result( $result, 0, 0 );

		### Get Handler Email
		if ( $v_handler_id > 0 ) {
			$query = "SELECT email
					FROM $g_mantis_user_table
					WHERE id='$v_handler_id'";
			$result = db_query( $query );
			$t_handler_email = db_result( $result, 0, 0 );
		}

		### Build subject
		$p_subject = "[".$t_project_name." ".$p_bug_id."]: ".stripslashes( $p_subject );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );
		$t_message .= email_build_bugnote_message( $p_bug_id );

		### send mail
		$res1 = 1;
		$res2 = 1;

		### Send to reporter if valid
		if ((!empty($t_reporter_email))&&(is_valid_email($t_reporter_email))) {
			$res1 = email_send( $t_reporter_email, $p_subject, $t_message );
		}

		### Send to handler if valid
		if ((!empty($t_handler_email))&&
			($t_handler_email!=$t_reporter_email)&&
			(is_valid_email($t_handler_email))) {
			$res2 = email_send( $t_handler_email, $p_subject, $t_message );
		}
	}
	### --------------------
	### Send only to specified user (by user id)
	function email_bug_info2( $p_bug_id, $p_message, $p_user_id ) {
		global $g_mantis_user_table, $g_mantis_bug_table, $g_mantis_project_table;

		### Get Subject
		$query = "SELECT project_id, summary
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		$p_subject = $row["summary"];
		$t_project_id = $row["project_id"];

		$query = "SELECT name
				FROM $g_mantis_project_table
				WHERE id='$t_project_id'";
		$result = db_query( $query );
		$t_project_name = db_result( $result, 0, 0 );

		### Get User Email
		$query = "SELECT email
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		$t_user_email = db_result( $result, 0, 0 );

		### Build subject
		$p_subject = "[".$t_project_name." ".$p_bug_id."]: ".stripslashes( $p_subject );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );

		### send mail
		if (is_valid_email($t_user_email)) {
			$res = email_send( $t_user_email, $p_subject, $t_message );
		}
	}
	### --------------------
	### Send only to specified user (by name)
	function email_bug_info3( $p_bug_id, $p_message, $p_user_email ) {
		global $g_mantis_user_table, $g_mantis_bug_table, $g_mantis_project_table;

		### Get Subject
		$query = "SELECT project_id, summary
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		$p_subject = $row["summary"];
		$t_project_id = $row["project_id"];

		$query = "SELECT name
				FROM $g_mantis_project_table
				WHERE id='$t_project_id'";
		$result = db_query( $query );
		$t_project_name = db_result( $result, 0, 0 );

		### Build subject
		$p_subject = "[".$t_project_name." ".$p_bug_id."]: ".stripslashes( $p_subject );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );

		### send mail
		if (is_valid_email($p_user_email)) {
			$res = email_send( $p_user_email, $p_subject, $t_message );
		}
	}
	### --------------------
	### Send to reporter and bcc_list
	function email_bug_info4( $p_bug_id, $p_message, $p_user_email, $p_bcc_header ) {
		global $g_mantis_user_table, $g_mantis_bug_table, $g_mantis_project_table;

		### Get Subject
		$query = "SELECT project_id, summary
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		$p_subject = $row["summary"];
		$t_project_id = $row["project_id"];

		$query = "SELECT name
				FROM $g_mantis_project_table
				WHERE id='$t_project_id'";
		$result = db_query( $query );
		$t_project_name = db_result( $result, 0, 0 );

		### Build subject
		$p_subject = "[".$t_project_name." ".$p_bug_id."]: ".stripslashes( $p_subject );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );

		### send mail
		if (is_valid_email($p_user_email)) {
			$res = email_send( $p_user_email, $p_subject, $t_message, $p_bcc_header );
		}
	}
	### --------------------
	function email_send( $p_recipient, $p_subject, $p_message, $p_header="" ) {
		global $g_from_email, $g_enable_email_notification;

		if ( $g_enable_email_notification==1 ) {

			### NEED TO STRIP ALL FIELDS OF INVALID CHARACTERS

			### Visit http://www.php.net/manual/function.mail.php
			### if you have problems with mailing

			$t_recipient = trim( $p_recipient );

			$t_subject = trim( $p_subject );

			$t_message = trim( $p_message );
			/*if ( floor( phpversion() )>=4 ) {
				$t_message = trim( wordwrap( $t_message, 72 ) );
			} else {
				$t_message = trim( word_wrap( $t_message, 72 ) );
			}*/

			$t_headers = "From: $g_from_email\n";
			#$t_headers .= "Reply-To: $p_reply_to_email\n";
			$t_headers .= "X-Sender: <$g_from_email>\n";
			$t_headers .= "X-Mailer: PHP/".phpversion()."\n";
			#$t_headers .= "X-Priority: 1\n"; ### Urgent = 1
			#$t_headers .= "Return-Path: <$g_return_path_email>\n"; ### return if error
			### If you want to send foreign charsets
			#$t_headers .= "Content-Type: text/html; charset=iso-8859-1\n";

			$t_headers .= $p_header;

			#echo $t_recipient."<BR>".$t_subject."<BR>".$t_message."<BR>".$t_headers;
			#exit;

			$result = mail( $t_recipient, $t_subject, $t_message, $t_headers );
			if ( !$result ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient";
				exit;
			}
		}
	}
	### --------------------
	# check to see that the format is valid and that the mx record exists
	function is_valid_email( $p_email ) {
		global $g_validate_email;

		### if we don't validate then just accept
		if ( $g_validate_email==0 ) {
			return true;
		}

		if (eregi("^[_.0-9a-z-]+@([0-9a-z][-0-9a-z.]+).([a-z]{2,3}$)", $p_email, $check)) {
			if (getmxrr($check[1].".".$check[2], $temp)) {
				return true;
			} else {
				$host = substr(strstr($check[0], '@'), 1).".";
				#### for no mx record... try dns
				if (checkdnsrr ( $host, "ANY" ))
					return true;
			}
		}
		### Everything failed.  Bad email.
		return false;
	}
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
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
	### --------------------
	function is_duplicate_version( $p_version, $p_project_id ) {
		global $g_mantis_project_version_table;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_version_table
				WHERE project_id='$p_project_id' AND
					version='$p_version'";
		$result = db_query( $query );
		$version_count =  db_result( $result, 0, 0 );
		if ( $version_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	### --------------------
?>