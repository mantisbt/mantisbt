<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	### CONFIGURATION VARIABLES                                             ###
	###########################################################################

	require( "config_inc.php" );

	###########################################################################
	### FUNCTIONS                                                           ###
	###########################################################################

	####################
	# MySQL
	####################
	#--------------------
	# connect to database
	function db_mysql_connect( 	$p_hostname, $p_username="root", $p_password="",
								$p_database, $p_port=3306 ) {

		$t_result = mysql_connect(  $p_hostname.":".$p_port,
									$p_username, $p_password );
		$t_result = mysql_select_db( $p_database );
	}
	#--------------------
	# execute query, requires connection to be opened,
	# goes to error page if error occurs
	# Use this when you don't want to handler an error yourself
	function db_mysql_query( $p_query ) {
		$t_result = mysql_query( $p_query );
		if ( !$t_result ) {
			header( "Location: $g_mysql_error_page?f_message=$p_query" );
			exit;
		}
		else {
			return $t_result;
		}
	}
	#--------------------
	function db_mysql_close() {
		$t_result = mysql_close();
	}
	#--------------------
	function db_mysql_error() {
		$t_error = mysql_errno().":".mysql_error();
	}
	#--------------------
	####################
	# Core HTML API
	####################
	#--------------------
	function print_html_top() {
		PRINT "<html>";
	}
	#--------------------
	function print_head_top() {
	   PRINT "<head>";
	}
	#--------------------
	function print_title( $p_title ) {
	   PRINT "<title>$p_title</title>";
	}
	#--------------------
	function print_css( $p_css="" ) {
		if ( !empty($p_css )) {
			include( "$p_css" );
		}
	}
	#--------------------
	function print_meta_redirect( $p_url, $p_time=0 ) {
	   PRINT "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\">";
	}
	#--------------------
	function print_head_bottom() {
	   PRINT "</head>";
	}
	#--------------------
	function print_body_top() {
		PRINT "<body>";
	}
	#--------------------
	function print_header( $p_title="Mantis" ) {
		PRINT "<div align=center><h3><font face=Verdana>$p_title</font></h3></div>";
	}
	#--------------------
	function print_footer() {
		global 	$g_string_cookie_val, $g_webmaster_email,
				$g_last_access_cookie_val;

		PRINT "<hr size=1>";
		PRINT "<address><font size=-1>Copyright (c) 2000</font></address>";
		PRINT "<address><font size=-1><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></font></address>";
	}
	#--------------------
	function print_body_bottom() {
		PRINT "</body>";
	}
	#--------------------
	function print_html_bottom() {
		PRINT "<html>";
	}
	#--------------------
	function print_menu( $p_menu_file="" ) {
		global $g_primary_border_color, $g_primary_color_light;

		PRINT "<table width=100% bgcolor=$g_primary_border_color>";
		PRINT "<tr align=center height=20>";
			PRINT "<td align=center bgcolor=$g_primary_color_light>";
				include( $p_menu_file );
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}
	#--------------------
	function print_category_string() {
		global $g_mantis_bug_table

		$query = "SHOW FIELDS
				FROM $g_mantis_bug_table";
		$result = db_mysql_query( $query );
		$entry_count = mysql_num_rows( $result );
		for ($i=0;$i<$entry_count;$i++) {
			$row = mysql_fetch_array( $result );
	    	$t_type = stripslashes($row["Type"]);
	    	$t_field = $row["Field"];
	    	if ( $t_field=="category" ) {
		    	return substr( $t_type, 5, strlen($t_type)-6);
		    }
	    }
	}
	#--------------------
	function print_categories( $p_category="" ) {
		global $g_mantis_bug_table

		$query = "SHOW FIELDS
				FROM $g_mantis_bug_table";
		$result = db_mysql_query( $query );
		$entry_count = mysql_num_rows( $result );
		for ($i=0;$i<$entry_count;$i++) {
			$row = mysql_fetch_array( $result );
	    	$t_type = stripslashes($row["Type"]);
	    	$t_field = $row["Field"];
	    	if ( $t_field=="category" ) {
		    	break;
		    }
	    }

	    $t_str = substr( $t_type, 5, strlen($t_type)-6).",";
		$cat_count = count(explode(",",$t_str))-1;
		for ($i=0;$i<$cat_count;$i++) {
			$t_s = substr( $t_str, 1, strpos($t_str, ",")-2 );
			$t_str = substr( $t_str, strpos($t_str, ",")+1, strlen($t_str) );
			if ( $p_category==$t_s ) {
				PRINT "<option value=\"$t_s\" SELECTED>$t_s";
			}
			else {
				PRINT "<option value=\"$t_s\">$t_s";
			}
		}
	}
	#--------------------
	####################
	# Cookie API
	####################
	#--------------------
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

			### update last_visit date
			$query = "UPDATE $g_mantis_user_table
					SET last_visit=NOW()
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
	#--------------------
	### checks to see if a returning user is valid
	### also sets the last time they visited
	### otherwise redirects to the login page
	function index_login_cookie_check( $p_redirect_url="" ) {
		global 	$g_string_cookie_val, $g_login_page, $g_last_access_cookie,
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

			setcookie( $g_last_access_cookie, $t_last_access );

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
	#--------------------
	####################
	# Authentication API
	####################
	#--------------------
	function password_match( $p_test_password, $p_password ) {
		$salt = substr( $p_password, 0, 2 );
		if ( crypt( $p_test_password, $salt ) == $p_password ) {
			return true;
		}
		else {
			return false;
		}
	}
	#--------------------
	#####################
	# User Management API
	#####################
	#--------------------
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
	#--------------------
	####################
	# Date API
	####################
	#--------------------
	function days_old( $month, $day, $year ) {

	}
	#--------------------
	function sql_to_unix_time( $p_timeString ) {
		return mktime( substr( $p_timeString, 8, 2 ),
					   substr( $p_timeString, 10, 2 ),
					   substr( $p_timeString, 12, 2 ),
					   substr( $p_timeString, 4, 2 ),
					   substr( $p_timeString, 6, 2 ),
					   substr( $p_timeString, 0, 4 ) );
	}
	#--------------------
	####################
	# String API
	####################
	#--------------------
	function string_safe( $p_string ) {
		return addslashes( nl2br( $p_string ) );
	}
	#--------------------
	function string_unsafe( $p_string ) {
		return stripslashes( $p_string );
	}
	#--------------------
	function string_edit( $p_string ) {
		return str_replace( "<br>", " ",  stripslashes( $p_string ) );
	}
	#--------------------
	#####################
	# Access Control API
	#####################
	#--------------------
	# "administrator", "developer", "updater", "reporter", "viewer"
	#--------------------
	function access_level() {
		global $g_access_cookie;
		return $HTTP_COOKIE_VARS[$g_access_cookie];
	}
	#--------------------
	### This is used to order the access levels
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
	#--------------------
	function access_level_check_equal( $p_access_level ) {
		global $g_string_cookie_val, $g_mantis_user_table;

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
	#--------------------
	function access_level_check_greater( $p_access_level ) {
		global $g_string_cookie_val, $g_mantis_user_table;

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
	#--------------------

	###########################################################################
	### END                                                                 ###
	###########################################################################
?>
