<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	### INCLUDES                                                            ###
	###########################################################################

	require( "config_inc.php" );
	require( "strings_".$g_language.".php" );

	###########################################################################
	### FUNCTIONS                                                           ###
	###########################################################################

	###########################################################################
	# MySQL
	###########################################################################
	### --------------------
	# connect to database
	function db_mysql_connect( 	$p_hostname="localhost", $p_username="root", $p_password="",
								$p_database="mantis", $p_port=3306 ) {

		$t_result = mysql_connect(  $p_hostname.":".$p_port,
									$p_username, $p_password );
		$t_result = mysql_select_db( $p_database );
	}
	### --------------------
	# execute query, requires connection to be opened,
	# goes to error page if error occurs
	# Use this when you don't want to handler an error yourself
	function db_mysql_query( $p_query ) {
		global $g_mysql_error_page;

		$t_result = mysql_query( $p_query );
		if ( !$t_result ) {
			header( "Location: $g_mysql_error_page?f_message=$p_query" );
			exit;
		}
		else {
			return $t_result;
		}
	}
	### --------------------
	function db_mysql_close() {
		$t_result = mysql_close();
	}
	### --------------------
	function db_mysql_error() {
		$t_error = mysql_errno().":".mysql_error();
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
		PRINT "<div align=center><h3>$p_title</h3></div>";
	}
	### --------------------
	function print_footer( $p_file ) {
		global 	$g_string_cookie_val, $g_webmaster_email, $g_show_source;

		print_source_link( $p_file );

		PRINT "<hr size=1>";
		print_mantis_version();
		PRINT "<address><font size=-1>Copyright (c) 2000</font></address>";
		PRINT "<address><font size=-1><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></font></address>";
	}
	### --------------------
	function print_body_bottom() {
		PRINT "</body>";
	}
	### --------------------
	function print_html_bottom() {
		PRINT "<html>";
	}
	### --------------------
	###########################################################################
	# HTML Appearance Helper API
	###########################################################################
	### --------------------
	# prints the user that is logged in and the date/time
	function print_login_info() {
		global 	$g_mantis_user_table, $g_string_cookie_val;

		$query = "SELECT username
				FROM $g_mantis_user_table
				WHERE cookie_string='$g_string_cookie_val'";
		$result = db_mysql_query( $query );
		$t_username = mysql_result( $result, 0 );

		$t_now = date("d-m-y h:m T");
		PRINT "<table width=100%><tr>";
		PRINT "<td align=left width=50%>";
			PRINT "Logged in as: <i>$t_username</i>";
		PRINT "</td>";
		PRINT "<td align=right width=50%>";
			PRINT "<i>$t_now</i>";
		PRINT "</td>";
		PRINT "</tr></table>";
	}
	### --------------------
	function print_menu( $p_menu_file="" ) {
		global 	$g_primary_border_color, $g_primary_color_light,
				$g_show_login_date_info;

		if ($g_show_login_date_info==1) {
			print_login_info();
		}

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
		global $g_show_source, $g_show_source_page;

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
	### checks to see whether we need to be displaying the source link
	function print_mantis_version() {
		global $g_mantis_version, $g_show_version;

		if ( $g_show_version==1 ) {
			PRINT "<i>Mantis version $g_mantis_version</i>";
		}
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
	    		WHERE access_level='administrator' OR access_level='developer'";
	    $result = mysql_query( $query );
	    $user_count = mysql_num_rows( $result );
	    for ($i=0;$i<$user_count;$i++) {
	    	$row = mysql_fetch_array( $result );
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
	function print_duplicate_id_option_list( $p_duplicate_id ) {
		global $g_mantis_bug_table;

	    $query = "SELECT id
	    		FROM $g_mantis_bug_table
	    		ORDER BY id ASC";
	    $result = mysql_query( $query );
	    $duplicate_id_count = mysql_num_rows( $result );
	    PRINT "<option value=\"0000000\">";

	    for ($i=0;$i<$duplicate_id_count;$i++) {
	    	$row = mysql_fetch_array( $result );
	    	$t_duplicate_id	= $row["id"];

	    	if ( $t_duplicate_id==$p_duplicate_id ) {
				PRINT "<option value=\"$t_duplicate_id\" SELECTED>".$t_duplicate_id;
			}
			else {
				PRINT "<option value=\"$t_duplicate_id\">".$t_duplicate_id;
			}
		}
	}
	### --------------------
	### Get current headlines and id  prefix with v_
	function print_news_item_option_list() {
		global $g_mantis_news_table;

		$query = "SELECT id, headline
			FROM $g_mantis_news_table
			ORDER BY id DESC";
	    $result = db_mysql_query( $query );
	    $news_count = mysql_num_rows( $result );

		for ($i=0;$i<$news_count;$i++) {
			$row = mysql_fetch_array( $result );
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

		$t_category_string = get_enum_string( $p_list );
	    $t_str = $t_category_string.",";
		$entry_count = get_list_item_count($t_str)-1;
		for ($i=0;$i<$entry_count;$i++) {
			$t_s = substr( $t_str, 1, strpos($t_str, ",")-2 );
			$t_str = substr( $t_str, strpos($t_str, ",")+1, strlen($t_str) );
			if ( $p_item==$t_s ) {
				PRINT "<option value=\"$t_s\" SELECTED>$t_s";
			}
			else {
				PRINT "<option value=\"$t_s\">$t_s";
			}
		} ### end for
	}
	### --------------------
	###########################################################################
	# Print API
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
	    $result = db_mysql_query( $query );
	    if ( mysql_num_rows( $result )>0 ) {
			$t_handler_username	= mysql_result( $result, 0, 0 );
			$t_handler_email	= mysql_result( $result, 0, 1 );

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
	# prints the profiles given the user id
	function print_profile_option_list( $p_id ) {
		global $g_mantis_user_profile_table;

		### Get profiles
		$query = "SELECT id, platform, os, os_build, default_profile
			FROM $g_mantis_user_profile_table
			WHERE user_id='$p_id'
			ORDER BY id DESC";
	    $result = db_mysql_query( $query );
	    $profile_count = mysql_num_rows( $result );

		PRINT "<option value=\"\">";
		for ($i=0;$i<$profile_count;$i++) {
			### prefix data with v_
			$row = mysql_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );
			$v_platform	= string_unsafe( $v_platform );
			$v_os		= string_unsafe( $v_os );
			$v_os_build	= string_unsafe( $v_os_build );

			if ( $v_default_profile=="on" ) {
				PRINT "<option value=\"$v_id\" SELECTED>$v_platform $v_os $v_os_build";
			}
			else {
				PRINT "<option value=\"$v_id\">$v_platform $v_os $v_os_build";
			}
		}
	}
	### --------------------
	###########################################################################
	# String printing API
	###########################################################################
	### --------------------
	function get_enum_string( $p_field_name ) {
		global $g_mantis_bug_table;

		$query = "SHOW FIELDS
				FROM $g_mantis_bug_table";
		$result = db_mysql_query( $query );
		$entry_count = mysql_num_rows( $result );
		for ($i=0;$i<$entry_count;$i++) {
			$row = mysql_fetch_array( $result );
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
	function get_list_item_count( $t_enum_string, $p_delim_char="," ) {
		return count(explode($p_delim_char,$t_enum_string));
	}
	### --------------------
	### Used in summary reports
	function print_bug_enum_summary( $p_enum, $p_status="" ) {
		global $g_mantis_bug_table, $g_primary_color_light, $g_primary_color_dark;

		$t_enum_string = get_enum_string( $p_enum );
	    $t_str = $t_enum_string.",";
		$enum_count = get_list_item_count($t_str)-1;
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = substr( $t_str, 1, strpos($t_str, ",")-2 );
			$t_str = substr( $t_str, strpos($t_str, ",")+1, strlen($t_str) );

			$query = "SELECT COUNT(id)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s'";
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
			$result = mysql_query( $query );
			$t_enum_count = mysql_result( $result, 0 );

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
	###########################################################################
	# Cookie API
	###########################################################################
	### --------------------
	### checks to see that a user is logged in
	### if the user is and the account is enabled then let them pass
	### otherwise redirect them to the login page
	function login_cookie_check( $p_redirect_url="" ) {
		global 	$g_string_cookie_val, $g_login_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {

			db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$query = "SELECT enabled
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = db_mysql_query( $query );
			$row = mysql_fetch_array( $result );
			if ( $row ) {
				$t_enabled = $row["enabled"];
			}

			### check for acess enabled
			if ( $t_enabled!="on" ) {
				header( "Location: $g_logout_page" );
			}

			### grab creation date to protect from change
			### Suspect a bug in mysql.. not sure.  Same deal for bug updates
			$query = "SELECT date_created
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = mysql_query( $query );
			$t_date_created = mysql_result( $result, 0 );

			### update last_visit date
			$query = "UPDATE $g_mantis_user_table
					SET last_visit=NOW(), date_created='$t_date_created'
					WHERE cookie_string='$g_string_cookie_val'";
			$result = mysql_query( $query );
			db_mysql_close();

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
		global 	$g_string_cookie_val, $g_login_page,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {
			### set last visit cookie

			db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$query = "SELECT enabled
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = db_mysql_query( $query );
			$row = mysql_fetch_array( $result );
			if ( $row ) {
				$t_enabled = $row["enabled"];
			}

			### check for acess enabled
			if ( $t_enabled!="on" ) {
				header( "Location: $g_logout_page" );
			}

			$query = "SELECT last_visit
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = mysql_query( $query );
			$t_last_access = mysql_result( $result, "last_visit" );
			db_mysql_close();

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
	### Returns the specified field of the currently logged in user, otherwise 0
	function get_current_user_field( $p_field_name ) {
		global 	$g_string_cookie_val,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_mantis_user_table;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {

			db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			### get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_table
					WHERE cookie_string='$g_string_cookie_val'";
			$result = db_mysql_query( $query );
			return mysql_result( $result, 0 );
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

			db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

			$t_id = get_current_user_field( "id" );
			### get user info
			$query = "SELECT $p_field_name
					FROM $g_mantis_user_pref_table
					WHERE user_id='$t_id'";
			$result = db_mysql_query( $query );
			return mysql_result( $result, 0 );
		}
		else {
			return 0;
		}
	}
	### --------------------
	### Returns the number of bugntoes for the given bug_id
	function get_bugnote_count( $p_id ) {
		global $g_mantis_bugnote_table;

		$query = "SELECT COUNT(id)
					FROM $g_mantis_bugnote_table
					WHERE bug_id ='$p_id'";
		$result = db_mysql_query( $query );
		return mysql_result( $result, 0 );
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
			db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
			$query = "SELECT $p_table_field
					FROM $p_table_name
					WHERE user_id='$u_id'";
			$result = db_mysql_query( $query );

			if ( mysql_num_rows( $result ) > 0 ) {
				return mysql_result( $result, 0 );
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
	###
	function days_old( $month, $day, $year ) {

	}
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
	# expects the paramter to be neutral in time length
	# automatically adds the -
	function get_bug_count_by_date( $p_time_length="day" ) {
		global $g_mantis_bug_table;

		$day = strtotime( "-".$p_time_length );
		$query = "SELECT COUNT(id)
				FROM $g_mantis_bug_table
				WHERE UNIX_TIMESTAMP(last_updated)>$day";
		$result = mysql_query( $query );
		return mysql_result( $result, 0 );
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

		$query = "SELECT access_level
				FROM $g_mantis_user_table
				WHERE cookie_string='$g_string_cookie_val'";
		$result = mysql_query( $query );
		$t_access_level = mysql_result( $result, "access_level" );

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

		if ( !isset($g_string_cookie_val) ) {
			return false;
		}

		$query = "SELECT access_level
				FROM $g_mantis_user_table
				WHERE cookie_string='$g_string_cookie_val'";
		$result = mysql_query( $query );
		$t_access_level = mysql_result( $result, "access_level" );

		if ( access_level_value( $t_access_level ) >= access_level_value( $p_access_level ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>