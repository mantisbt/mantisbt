<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.13 $
	# $Author: jfitzell $
	# $Date: 2002-08-16 06:38:34 $
	#
	# $Id: bug_reopen.php,v 1.13 2002-08-16 06:38:34 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$c_id = (integer)$f_id;
	project_access_check( $c_id );
	check_access( $g_handle_bug_threshold );
	check_bug_exists( $c_id );

	#check variables
	check_varset( $f_bugnote_text, '' );

	#clean variables
	$c_bugnote_text = string_prepare_textarea( trim( $f_bugnote_text ) );

	$h_status		= get_bug_field( $c_id, 'status' );
	$h_resolution	= get_bug_field( $c_id, 'resolution' );

	# Update fields
	$t_status_val = FEEDBACK;
	$t_resolution = REOPENED;
    $query = "UPDATE $g_mantis_bug_table
    		SET status='$t_status_val',
				resolution='$t_resolution'
    		WHERE id='$c_id'";
   	$result = db_query($query);

	# log changes
	history_log_event( $c_id, 'status',     $h_status );
	history_log_event( $c_id, 'resolution', $h_resolution );

	# check for blank bugnote
	if ( !empty( $c_bugnote_text ) ) {
		# insert bugnote text
		$result = add_bugnote( $c_id, $c_bugnote_text );

	   	# notify reporter and handler
	   	email_reopen( $c_id );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $c_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
