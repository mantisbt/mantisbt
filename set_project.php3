<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### Check for invalid project_id selection
	if ( empty( $f_project_id ) || ( $f_project_id=="0000000" ) ) {
		PRINT "You must choose a valid project";
		exit;
	}

	### Add item
	setcookie( $g_project_cookie, $f_project_id, time()+$g_cookie_time_length );

	header( "Location: $g_main_page" );
?>