<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.8 $
	# $Author: vboctor $
	# $Date: 2002-06-14 05:36:48 $
	#
	# $Id: bug_monitor.php,v 1.8 2002-06-14 05:36:48 vboctor Exp $
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
	check_bug_exists( $f_id );

	$t_view_state = get_bug_field( $f_id, 'view_state' );

	$t_threshold = $g_monitor_bug_threshold;
	if ( ( PRIVATE == $t_view_state ) && ( $g_private_bug_threshold > $t_threshold ) ) {
		$t_threshold = $g_private_bug_threshold;
	}

	check_access( $t_threshold );

	$c_id = (integer)$f_id;

	# get user information
	$u_id = get_current_user_field( 'id' );

	if ( 'add' == $f_action ) {
		# Make sure we aren't already monitoring this bug
		$query ="SELECT bug_id ".
				"FROM $g_mantis_bug_monitor_table ".
				"WHERE bug_id='$c_id' AND user_id='$u_id' ".
				"LIMIT 1";
 		$result = db_query( $query );
		$t_num_rows = db_num_rows( $result );

		if ( $t_num_rows == 0 ) {
			# Insert monitoring record
			$query ="INSERT ".
					"INTO $g_mantis_bug_monitor_table ".
					"( user_id, bug_id ) ".
					"VALUES ".
					"( '$u_id', '$c_id' )";
			$result = db_query($query);

			# log new monitoring action
			history_log_event_special( $f_id, BUG_MONITOR, $u_id );
		}
	} elseif ( 'delete' == $f_action ) {
		# Delete monitoring record
		$query ="DELETE ".
				"FROM $g_mantis_bug_monitor_table ".
				"WHERE user_id = '$u_id' AND bug_id = '$c_id'";
		$result = db_query($query);

		# log new un-monitor action
		history_log_event_special( $f_id, BUG_UNMONITOR, $u_id );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
