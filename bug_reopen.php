<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.18 $
	# $Author: jfitzell $
	# $Date: 2002-08-30 08:36:50 $
	#
	# $Id: bug_reopen.php,v 1.18 2002-08-30 08:36:50 jfitzell Exp $
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
	check_access( $g_reopen_bug_threshold );
	bug_ensure_exists( $f_id );

	#check variables
	check_varset( $f_bugnote_text, '' );

	#clean variables
	$c_id = (integer)$f_id;

	$h_status		= get_bug_field( $f_id, 'status' );
	$h_resolution	= get_bug_field( $f_id, 'resolution' );

	# Update fields
	$t_status_val = FEEDBACK;
	$t_resolution = REOPENED;
    $query = "UPDATE $g_mantis_bug_table
    		SET status='$t_status_val',
				resolution='$t_resolution'
    		WHERE id='$c_id'";
   	$result = db_query($query);

	# log changes
	history_log_event( $f_id, 'status',     $h_status );
	history_log_event( $f_id, 'resolution', $h_resolution );

	$f_bugnote_text = trim( $f_bugnote_text );
	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
		# insert bugnote text
#@@@ jf - need to add string_prepare_textarea() call or something once that is resolved
		$result = bugnote_add( $f_id, $f_bugnote_text );

	   	# notify reporter and handler
	   	email_reopen( $f_id );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
