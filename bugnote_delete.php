<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Remove the bugnote and bugnote text and redirect back to
	# the viewing page
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );
	check_bugnote_exists( $f_bugnote_id );
	$c_bugnote_id = (integer)$f_bugnote_id;

	# grab the bugnote text id
	$query = "SELECT bugnote_text_id
			FROM $g_mantis_bugnote_table
			WHERE id='$c_bugnote_id'";
	$result = db_query( $query );
	$t_bugnote_text_id = db_result( $result, 0, 0 );

	# Remove the bugnote
	$query = "DELETE
			FROM $g_mantis_bugnote_table
			WHERE id='$c_bugnote_id'";
	$result = db_query( $query );

	# Remove the bugnote text
	$query = "DELETE
			FROM $g_mantis_bugnote_text_table
			WHERE id='$t_bugnote_text_id'";
	$result = db_query( $query );

	# log new bug
	history_log_event_special( $f_id, BUGNOTE_DELETED, $f_bugnote_id );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>