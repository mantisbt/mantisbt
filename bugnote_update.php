<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_update.php,v 1.44 2006-12-12 18:26:28 davidnewcomb Exp $
	# --------------------------------------------------------
?>
<?php
	# Update bugnote data then redirect to the appropriate viewing page
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_bugnote_id	 = gpc_get_int( 'bugnote_id' );
	$f_bugnote_text	 = gpc_get_string( 'bugnote_text', '' );
	$f_time_tracking = gpc_get_string( 'time_tracking', '0:00' );

	# Check if the current user is allowed to edit the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_reporter_id ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) )) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}

	# Check if the bug is readonly
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	if ( bug_is_readonly( $t_bug_id ) ) {
		error_parameters( $t_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$f_bugnote_text = trim( $f_bugnote_text ) . "\n\n";

	bugnote_set_text( $f_bugnote_id, $f_bugnote_text );
	bugnote_set_time_tracking( $f_bugnote_id, $f_time_tracking );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
