<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.12 $
	# $Author: jfitzell $
	# $Date: 2002-08-30 08:36:50 $
	#
	# $Id: bug_monitor.php,v 1.12 2002-08-30 08:36:50 jfitzell Exp $
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
	bug_ensure_exists( $f_id );

	$t_view_state = get_bug_field( $f_id, 'view_state' );

	$t_threshold = $g_monitor_bug_threshold;
	if ( ( PRIVATE == $t_view_state ) && ( $g_private_bug_threshold > $t_threshold ) ) {
		$t_threshold = $g_private_bug_threshold;
	}

	check_access( $t_threshold );

	$c_id = (integer)$f_id;

	# get user information
	$u_id = current_user_get_field( 'id' );

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
