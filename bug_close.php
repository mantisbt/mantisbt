<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.15 $
	# $Author: vboctor $
	# $Date: 2002-06-14 05:36:48 $
	#
	# $Id: bug_close.php,v 1.15 2002-06-14 05:36:48 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( UPDATER );
	check_bug_exists( $f_id );
	$c_id = (integer)$f_id;

	$t_handler_id	= get_current_user_field( 'id' );

	$h_status	= get_bug_field( $c_id, 'status' );

	# Update fields
	$t_clo_val = CLOSED;
	$query ="UPDATE $g_mantis_bug_table ".
			"SET status='$t_clo_val' ".
			"WHERE id='$c_id'";
	$result = db_query($query);

	# log changes
	history_log_event( $c_id, 'status', $h_status );

	# get user information
	$u_id = get_current_user_field( 'id' );

	$f_bugnote_text = trim( $f_bugnote_text );
	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
		$c_bugnote_text = string_prepare_textarea( $f_bugnote_text );
		# insert bugnote text
		$query ="INSERT ".
				"INTO $g_mantis_bugnote_text_table ".
				"( id, note ) ".
				"VALUES ".
				"( null, '$c_bugnote_text' )";
		$result = db_query( $query );

		# retrieve bugnote text id number
		$t_bugnote_text_id = db_insert_id();

		# insert bugnote info
		$query ="INSERT ".
				"INTO $g_mantis_bugnote_table ".
				"( id, bug_id, reporter_id, bugnote_text_id, date_submitted, last_modified ) ".
				"VALUES ".
				"( null, '$c_id', '$u_id','$t_bugnote_text_id', NOW(), NOW() )";
		$result = db_query( $query );

		# updated the last_updated date
		$result = bug_date_update( $c_id );

		# get bugnote id
		$t_bugnote_id = db_insert_id();

		# log new bugnote
		history_log_event_special( $c_id, BUGNOTE_ADDED, $t_bugnote_id );

		email_close( $c_id );
	}

	$t_redirect_url = 'view_all_bug_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
