<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup.php,v 1.30 2004-06-11 02:00:00 narcissus Exp $
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

		case 'COPY':
			$f_project_id = gpc_get_int( 'project_id' );

			if ( access_has_project_level( config_get( 'report_bug_threshold' ), $f_project_id ) ) {
				$t_bug_data = new BugData;
				$t_bug_data = bug_get($t_bug_id, true);

				$t_new_bug_id =  bug_create(
								/* Change project */
								$f_project_id,
								$t_bug_data->reporter_id,
								$t_bug_data->handler_id,
								$t_bug_data->priority,
								$t_bug_data->severity,
								$t_bug_data->reproducibility,
								$t_bug_data->category,
								$t_bug_data->os,
								$t_bug_data->os_build,
								$t_bug_data->platform,
								$t_bug_data->version,
								$t_bug_data->build,
								bug_get_field( $t_bug_id, 'profile_id' ),
								$t_bug_data->summary,
								$t_bug_data->view_state,
								$t_bug_data->description,
								$t_bug_data->steps_to_reproduce,
								$t_bug_data->additional_information );

				bug_set_field( $t_new_bug_id, 'duplicate_id', bug_get_field( $t_bug_id, 'duplicate_id' ) );
				bug_set_field( $t_new_bug_id, 'status', bug_get_field( $t_bug_id, 'status' ) );
				bug_set_field( $t_new_bug_id, 'resolution', bug_get_field( $t_bug_id, 'resolution' ) );
				bug_set_field( $t_new_bug_id, 'projection', bug_get_field( $t_bug_id, 'projection' ) );
				bug_set_field( $t_new_bug_id, 'date_submitted', bug_get_field( $t_bug_id, 'date_submitted' ) );
				bug_set_field( $t_new_bug_id, 'last_updated', bug_get_field( $t_bug_id, 'last_updated' ) );
				bug_set_field( $t_new_bug_id, 'eta', bug_get_field( $t_bug_id, 'eta' ) );
				bug_set_field( $t_new_bug_id, 'fixed_in_version', bug_get_field( $t_bug_id, 'fixed_in_version' ) );
				bug_set_field( $t_new_bug_id, 'sponsorship_total', bug_get_field( $t_bug_id, 'sponsorship_total' ) );

				# Get custom field values
				$query = "SELECT field_id, bug_id, value
						   FROM mantis_custom_field_string_table
						   WHERE bug_id = '$t_bug_id';";
				$result = db_query( $query );
				$t_count = db_num_rows( $result );

				$t_bug_customs = array();
				for ( $i = 0 ; $i < $t_count ; $i++ ) {
					$t_bug_customs[] = db_fetch_array( $result );
				}

				foreach( $t_bug_customs as $t_bug_custom ) {
					$query = "INSERT INTO `mantis_custom_field_string_table` 
							   (`field_id`, `bug_id`, `value`)
							   VALUES ('" . $t_bug_custom['field_id'] . "', '$t_new_bug_id', '" . $t_bug_custom['value'] . "');";
					db_query( $query );
				}
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

		case 'VIEW_STATUS':
			if ( access_has_bug_level( config_get( 'change_view_status_threshold' ), $t_bug_id ) ) {
				$f_view_status = gpc_get_int( 'view_status' );
				bug_set_field( $t_bug_id, 'view_state', $f_view_status );
			} else {
				$t_failed_ids[] = $t_bug_id;
			}
			break;

		default:
			trigger_error( ERROR_GENERIC, ERROR );
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
