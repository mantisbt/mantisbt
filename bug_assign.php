<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# This module is based on bug_update.php and provides a quick method
	# for assigning a call to the currently signed on user.
	# Copyright (C) 2001  Steve Davies - steved@ihug.co.nz

	# --------------------------------------------------------
	# $Revision: 1.17 $
	# $Author: vboctor $
	# $Date: 2002-06-14 12:35:50 $
	#
	# $Id: bug_assign.php,v 1.17 2002-06-14 12:35:50 vboctor Exp $
	# --------------------------------------------------------

	# Assign bug to user then redirect to viewing page
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_handle_bug_threshold );

	# extract current information into history variables
	$result = get_bug_row ( $f_id );
	if ( 0 == db_num_rows( $result ) ) {
		# speed is not an issue in this case, so re-use code
		check_bug_exists( $f_id );
	}

	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'h' );

	if ( ON == $g_auto_set_status_to_assigned ) {
		$t_ass_val = ASSIGNED;
	} else {
		$t_ass_val = $h_status;
	}

	$t_handler_id = get_current_user_field( 'id' );

	if ( ( $t_ass_val != $h_status ) || ( $t_handler_id != $h_handler_id ) ) {
		$c_id = (integer)$f_id;

		# get user id
		$query ="UPDATE $g_mantis_bug_table ".
				"SET handler_id='$t_handler_id', status='$t_ass_val' ".
				"WHERE id='$c_id'";
		$result = db_query($query);

		# updated the last_updated date
		$result = bug_date_update( $f_id );

		# log changes
		history_log_event_direct( $c_id, 'status', $h_status, $t_ass_val, $t_handler_id );
		history_log_event_direct( $c_id, 'handler_id', $h_handler_id, $t_handler_id, $t_handler_id );

		# send assigned to email
		email_assign( $f_id );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
