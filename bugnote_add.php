<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Insert the bugnote into the database then redirect to the bug page
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );
	check_bug_exists( $f_id );
	$c_id = (integer)$f_id;

	check_varset( $f_private, '0' );

	# get user information
	$u_id = get_current_user_field( 'id' );

	$f_bugnote_text = trim( $f_bugnote_text );
	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
		$c_bugnote_text = string_prepare_textarea( $f_bugnote_text );
		# insert bugnote text
		$query = "INSERT
				INTO $g_mantis_bugnote_text_table
				( id, note )
				VALUES
				( null, '$c_bugnote_text' )";
		$result = db_query( $query );

		# retrieve bugnote text id number
		$t_bugnote_text_id = db_insert_id();

		# Check for private bugnotes.
		if ( $f_private && access_level_check_greater_or_equal( $g_private_bugnote_threshold ) ) {
			$c_view_state = PRIVATE;
		} else {
			$c_view_state = PUBLIC;
		}

		# insert bugnote info
		$query = "INSERT
				INTO $g_mantis_bugnote_table
				( id, bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
				VALUES
				( null, '$c_id', '$u_id','$t_bugnote_text_id', '$c_view_state', NOW(), NOW() )";
		$result = db_query( $query );

		# update bug last updated
	   	$result = bug_date_update( $f_id );

		# get bugnote id
		$t_bugnote_id = db_insert_id();

		# log new bug
		history_log_event_special( $f_id, BUGNOTE_ADDED , $t_bugnote_id );

	   	# notify reporter and handler
	   	if ( get_bug_field( $f_id, 'status' ) == FEEDBACK ) {
	   		if ( get_bug_field( $f_id, 'resolution' ) == REOPENED ) {
	   			email_reopen( $f_id );
	   		} else {
	   			email_feedback( $f_id );
	   		}
	   	} else {
	   		email_bugnote_add( $f_id );
	   	}
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
