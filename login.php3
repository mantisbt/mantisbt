<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

   	### get user info
	$query = "SELECT *
			FROM $g_mantis_user_table
			WHERE username='$f_username'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );

	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
	}
	else {
		### invalid login, retry
		header( "Location: $g_login_error_page" );
		exit;
	}

	if( password_match( $f_password, $u_password ) && ( $u_enabled=="on" ) ) {
		### increment login count
		$t_date_created = get_current_user_field( "date_created" );
		$query = "UPDATE $g_mantis_user_table
				SET login_count=login_count+1,
					date_created='$t_date_created'
				WHERE username='$f_username'";
		$result = db_query( $query );

		### set permanent cookie (1 year)
		if ( $f_perm_login=="on") {
			setcookie( $g_string_cookie, $u_cookie_string, time()+$g_cookie_time_length );
			setcookie( $g_project_cookie, $f_project_id, time()+$g_cookie_time_length );
		}
		### set temp cookie, cookie dies after browser closes
		else {
			setcookie( $g_string_cookie, $u_cookie_string );
			setcookie( $g_project_cookie, $f_project_id );
		}

		header( "Location: $g_main_page" );
		exit;
	}
	else {
		### invalid login, retry
		header( "Location: $g_login_error_page" );
		exit;
	}
?>