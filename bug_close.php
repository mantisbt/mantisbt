<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.19 $
	# $Author: jfitzell $
	# $Date: 2002-08-16 10:16:25 $
	#
	# $Id: bug_close.php,v 1.19 2002-08-16 10:16:25 jfitzell Exp $
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

	#check variables
	check_varset( $f_bugnote_text, '' );

	#clean variables
	$c_id = (integer)$f_id;

	$t_handler_id	= get_current_user_field( 'id' );

	$h_status	= get_bug_field( $f_id, 'status' );

	# Update fields
	$t_status_val = CLOSED;
	$query ="UPDATE $g_mantis_bug_table ".
			"SET status='$t_status_val' ".
			"WHERE id='$c_id'";
	$result = db_query($query);

	# log changes
	history_log_event( $f_id, 'status', $h_status );

	$f_bugnote_text = trim( $f_bugnote_text );
	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
		# insert bugnote text
#@@@ jf - need to add string_prepare_textarea() call or something once that is resolved
		$result = bugnote_add( $f_id, $f_bugnote_text );

		email_close( $f_id );
	}

	$t_redirect_url = 'view_all_bug_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
