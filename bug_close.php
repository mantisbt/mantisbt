<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.17 $
	# $Author: jfitzell $
	# $Date: 2002-08-16 06:38:34 $
	#
	# $Id: bug_close.php,v 1.17 2002-08-16 06:38:34 jfitzell Exp $
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
	check_access( UPDATER );
	check_bug_exists( $c_id );

	#check variables
	check_varset( $f_bugnote_text, '' );

	#clean variables
	$c_bugnote_text = string_prepare_textarea( trim( $f_bugnote_text ) );

	$t_handler_id	= get_current_user_field( 'id' );

	$h_status	= get_bug_field( $c_id, 'status' );

	# Update fields
	$t_status_val = CLOSED;
	$query ="UPDATE $g_mantis_bug_table ".
			"SET status='$t_status_val' ".
			"WHERE id='$c_id'";
	$result = db_query($query);

	# log changes
	history_log_event( $c_id, 'status', $h_status );

	# check for blank bugnote
	if ( !empty( $c_bugnote_text ) ) {
		# insert bugnote text
		$result = add_bugnote( $c_id, $c_bugnote_text );

		email_close( $c_id );
	}

	$t_redirect_url = 'view_all_bug_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
