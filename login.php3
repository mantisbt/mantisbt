<?php_track_vars?>
<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

   	### get user info
	$query = "SELECT *
			FROM $g_mantis_user_table
			WHERE username='$f_username'";
	$result = db_mysql_query( $query );
	$row = mysql_fetch_array( $result );

	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
	}
	else {
		### invalid login, retry
		header( "Location: $g_login_error_page" );
		exit;
	}

	if( password_match( $f_password, $u_password ) && ( $u_enabled=="on" ) ) {
		### set permanent cookie (1 year)
		if ( $f_perm_login=="on") {
			setcookie( $g_string_cookie, $u_cookie_string, time()+$g_time_length );
		}
		### set temp cookie, cookie dies after browser closes
		else {
			setcookie( $g_string_cookie, $u_cookie_string );
		}

		### set last access cookie
		setcookie( $g_last_access_cookie, $u_last_visit );
		header( "Location: $g_main_page" );
		exit;
	}
	else {
		### invalid login, retry
		header( "Location: $g_login_error_page" );
		exit;
	}
?>