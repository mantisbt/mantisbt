<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup.php,v 1.25 2003-02-18 02:18:00 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows actions to be performed an an array of bugs
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_action	= gpc_get_string( 'action' );
	$f_bug_arr	= gpc_get_int_array( 'bug_arr', array() );

	$t_failed_ids = array();

	foreach( $f_bug_arr as $t_bug_id ) {
		bug_ensure_exists( $t_bug_id );

		switch ( $f_action ) {

		case 'CLOSE':
			if ( access_can_close_bug( $t_bug_id ) ) {
				bug_close( $t_bug_id );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		case 'DELETE':
			if ( access_has_bug_level( config_get( 'delete_bug_threshold' ), $t_bug_id ) ) {
				bug_delete( $t_bug_id );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		case 'MOVE':
			if ( access_has_bug_level( config_get( 'move_bug_threshold' ), $t_bug_id ) ) {
				$f_project_id = gpc_get_int( 'project_id' );
				bug_set_field( $t_bug_id, 'project_id', $f_project_id );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		case 'ASSIGN':
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				// @@@ Check that $f_assign has access to handle a bug.
				$f_assign = gpc_get_int( 'assign' );
				bug_assign( $t_bug_id, $f_assign );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		case 'RESOLVE':
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) &&
				 access_has_bug_level( config_get( 'handle_bug_threshold' ), $t_bug_id )) {
				$f_resolution = gpc_get_int( 'resolution' );
				bug_resolve( $t_bug_id, $f_resolution );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		case 'UP_PRIOR':
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				$f_priority = gpc_get_int( 'priority' );
				bug_set_field( $t_bug_id, 'priority', $f_priority );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		case 'UP_STATUS':
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				$f_status = gpc_get_int( 'status' );
				bug_set_field( $t_bug_id, 'status', $f_status );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;
		}
	}

	$t_redirect_url = 'view_all_bug_page.php';

	if ( count( $t_failed_ids ) > 0 ) {
		html_page_top1();
		html_page_top2();
		
		$t_links = array();		
		foreach( $t_failed_ids as $t_id ) {
			$t_links[] = string_get_bug_view_link( $t_id );
		}
		
		echo '<div align="center">';
		echo lang_get( 'bug_actiongroup_failed' ) . implode( ', ', $t_links ) . '<br />';
		print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
		echo '</div>';
		
		html_page_bottom1( __FILE__ );	
	} else {
		print_header_redirect( $t_redirect_url );
	}
?>
