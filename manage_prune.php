<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	# Delete the users who have never logged in and are older than 1 week
	$days_old = 7;
	$query = "DELETE
			FROM $g_mantis_user_table
			WHERE login_count=0 AND TO_DAYS(NOW()) - '$days_old' > TO_DAYS(date_created)";
	$result = db_query($query);

	$t_redirect_url = $g_manage_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>