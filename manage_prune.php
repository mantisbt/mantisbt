<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );

	# Delete the users who have never logged in and are older than 1 week
	$days_old = 7;
	$days_old = (integer)$days_old;
	$query = "SELECT id
			FROM $g_mantis_user_table
			WHERE login_count=0 AND TO_DAYS(NOW()) - '$days_old' > TO_DAYS(date_created)";
	$result = db_query($query);

	for ($i=0; $i < db_num_rows( $result ); $i++) {
		$row = db_fetch_array( $result );
		user_delete($row['id']);
	}

	$t_redirect_url = 'manage_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
