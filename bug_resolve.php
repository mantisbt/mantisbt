<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.23 $
	# $Author: jfitzell $
	# $Date: 2002-09-21 10:17:13 $
	#
	# $Id: bug_resolve.php,v 1.23 2002-09-21 10:17:13 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_handle_bug_threshold );
	bug_ensure_exists( $f_id );

	#check variables
	check_varset( $f_bugnote_text, '' );
	check_varset( $f_resolution, FIXED );
	check_varset( $f_duplicate_id, '' );
	
	# make sure it is not market as duplicate to itself
	if ( $f_duplicate_id == $f_id ) {
		print_mantis_error( ERROR_GENERIC );
	}

	#clean variables
	$c_resolution	= (integer)$f_resolution;
	$c_duplicate_id	= (integer)$f_duplicate_id;
	$c_id			= (integer)$f_id;

	$h_handler_id	= bug_get_field( $f_id, 'handler_id' );
	$h_status		= bug_get_field( $f_id, 'status' );
	$h_resolution	= bug_get_field( $f_id, 'resolution' );
	$h_duplicate_id	= bug_get_field( $f_id, 'duplicate_id' );

	$t_handler_id   = current_user_get_field( 'id' );

	# Update fields
	$t_status_val = RESOLVED;
	if ( isset( $f_close_now ) ) {
		$t_status_val = CLOSED;
	}
    $query = "UPDATE $g_mantis_bug_table
    		SET handler_id='$t_handler_id',
    			status='$t_status_val',
    			resolution='$c_resolution',
    			duplicate_id='$c_duplicate_id'
    		WHERE id='$c_id'";
   	$result = db_query($query);

	# log changes
	history_log_event( $f_id, 'handler_id',   $h_handler_id );
	history_log_event( $f_id, 'status',       $h_status );
	history_log_event( $f_id, 'resolution',   $h_resolution );
	history_log_event( $f_id, 'duplicate_id', $h_duplicate_id );

	$f_bugnote_text = trim( $f_bugnote_text );

	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
		# insert bugnote text
		$result = bugnote_add( $f_id, $f_bugnote_text );

	   	# notify reporter and handler
		email_resolved( $f_id );
	} else {
		# updated the last_updated date
		$result = bug_update_date( $f_id );
	}

	# Determine which view page to redirect back to.
	if ( $result ) {
		print_header_redirect( string_get_bug_view_url( $f_id ) );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
