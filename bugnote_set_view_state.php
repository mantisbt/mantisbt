<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_set_view_state.php,v 1.22 2003-02-20 07:30:05 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Set an existing bugnote private or public.
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	$f_bugnote_id	= gpc_get_int( 'bugnote_id' );
	$f_private		= gpc_get_bool( 'private' );

	access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );

	# Check if the bug has been resolved
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	if ( bug_get_field( $t_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' ) ) {
		trigger_error( ERROR_BUG_RESOLVED_ACTION_DENIED, ERROR );
	}

	bugnote_set_view_state( $f_bugnote_id, $f_private );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
