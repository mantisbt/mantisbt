<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup.php,v 1.19 2003-01-23 23:02:50 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows actions to be performed an an array of bugs
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'bug_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_action	= gpc_get_string( 'action' );
	$f_bug_arr	= gpc_get_int_array( 'bug_arr', array() );

	foreach( $f_bug_arr as $t_bug_id ) {
		bug_ensure_exists( $t_bug_id );

		switch ( $f_action ) {

		case 'CLOSE':
			check_access( config_get( 'close_bug_threshold' ) );
			bug_close( $t_bug_id );
			break;

		case 'DELETE':
			check_access( config_get( 'allow_bug_delete_access_level' ) );
			bug_delete( $t_bug_id );
			break;

		case 'MOVE':
			check_access( config_get( 'bug_move_access_level' ) );
			$f_project_id = gpc_get_int( 'project_id' );
			bug_set_field( $t_bug_id, 'project_id', $f_project_id );
			break;

		case 'ASSIGN':
			check_access( config_get( 'update_bug_threshold' ) );
			// @@@ Check that $f_assign has access to handle a bug.
			$f_assign = gpc_get_int( 'assign' );
			bug_assign( $t_bug_id, $f_assign );
			break;

		case 'RESOLVE':
			check_access( config_get( 'handle_bug_threshold' ) );
			$f_resolution = gpc_get_int( 'resolution' );
			bug_resolve( $t_bug_id, $f_resolution );
			break;

		case 'UP_PRIOR':
			check_access( config_get( 'update_bug_threshold' ) );
			$f_priority = gpc_get_int( 'priority' );
			bug_set_field( $t_bug_id, 'priority', $f_priority );
			break;

		case 'UP_STATUS':
			check_access( config_get( 'update_bug_threshold' ) );
			$f_status = gpc_get_int( 'status' );
			bug_set_field( $t_bug_id, 'status', $f_status );
			break;
		}
	}

	print_meta_redirect( 'view_all_bug_page.php', 0 );
?>
