<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page stores the reported bug and then redirects to view_all_bug_page.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( UPDATER );
	$c_project_id = (integer)$f_project_id;

	# We check to see if the variable exists to avoid warnings
	$result = 1;
	if ( isset( $f_bug_arr ) ) {
		$t_count = count( $f_bug_arr );
		for ( $i=0; $i < $t_count; $i++ ) {
			$t_new_id = $f_bug_arr[$i];
			$query = "UPDATE $g_mantis_bug_table
					SET project_id='$c_project_id'
					WHERE id='$t_new_id'";
			$result = db_query( $query );

			if ( !$result ) {
				break;
			}
		}
	}

	$t_redirect_url = $g_view_all_bug_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>