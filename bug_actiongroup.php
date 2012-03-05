<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * This page allows actions to be performed an an array of bugs
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_api.php' );

	auth_ensure_user_authenticated();
	helper_begin_long_process();

	$f_action	= gpc_get_string( 'action' );
	$f_custom_field_id = gpc_get_int( 'custom_field_id', 0 );
	$f_bug_arr	= gpc_get_int_array( 'bug_arr', array() );
	$f_bug_notetext = gpc_get_string( 'bugnote_text', '' );
	$f_bug_noteprivate = gpc_get_bool( 'private' );
	$t_form_name = 'bug_actiongroup_' . $f_action;
	form_security_validate( $t_form_name );

	$t_custom_group_actions = config_get( 'custom_group_actions' );

	foreach( $t_custom_group_actions as $t_custom_group_action ) {
		if ( $f_action == $t_custom_group_action['action'] ) {
			require_once( $t_custom_group_action['action_page'] );
			exit;
		}
	}

	$t_failed_ids = array();

	if ( 0 != $f_custom_field_id ) {
		$t_custom_field_def = custom_field_get_definition( $f_custom_field_id );
	}

	foreach( $f_bug_arr as $t_bug_id ) {
		bug_ensure_exists( $t_bug_id );
		$t_bug = bug_get( $t_bug_id, true );

		if( $t_bug->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are viewing...
			# ... override the current project. This to avoid problems with categories and handlers lists etc.
			$g_project_override = $t_bug->project_id;
			/** @todo (thraxisp) the next line goes away if the cache was smarter and used project */
			config_flush_cache(); # flush the config cache so that configs are refetched
		}

		$t_status = $t_bug->status;

		switch ( $f_action ) {

		case 'CLOSE':
			$t_closed = config_get( 'bug_closed_status_threshold' );
			if ( access_can_close_bug( $t_bug_id ) ) {
				if( ( $t_status < $t_closed ) &&
					bug_check_workflow( $t_status, $t_closed ) ) {

					/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $f_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					bug_close( $t_bug_id, $f_bug_notetext, $f_bug_noteprivate );
					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_status' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'DELETE':
			if ( access_has_bug_level( config_get( 'delete_bug_threshold' ), $t_bug_id ) ) {
				event_signal( 'EVENT_BUG_DELETED', array( $t_bug_id ) );
				bug_delete( $t_bug_id );
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'MOVE':
			$f_project_id = gpc_get_int( 'project_id' );
			if( access_has_bug_level( config_get( 'move_bug_threshold' ), $t_bug_id ) &&
			    access_has_project_level( config_get( 'report_bug_threshold', null, null, $f_project_id ), $f_project_id ) ) {
				/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
				bug_move( $t_bug_id, $f_project_id );
				helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'COPY':
			$f_project_id = gpc_get_int( 'project_id' );

			if ( access_has_project_level( config_get( 'report_bug_threshold' ), $f_project_id ) ) {
				# Copy everything except history
				bug_copy( $t_bug_id, $f_project_id, true, true, false, true, true, true );
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'ASSIGN':
			$f_assign = gpc_get_int( 'assign' );
			if ( ON == config_get( 'auto_set_status_to_assigned' ) ) {
				$t_assign_status = config_get( 'bug_assigned_status' );
			} else {
				$t_assign_status = $t_status;
			}
			# check that new handler has rights to handle the issue, and
			#  that current user has rights to assign the issue
			$t_threshold = access_get_status_threshold( $t_assign_status, bug_get_field( $t_bug_id, 'project_id' ) );
			if ( access_has_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $t_bug_id ) ) {
				if ( access_has_bug_level( config_get( 'handle_bug_threshold' ), $t_bug_id, $f_assign ) ) {
					if ( bug_check_workflow( $t_status, $t_assign_status ) ) {
						/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
						bug_assign( $t_bug_id, $f_assign, $f_bug_notetext, $f_bug_noteprivate );
						helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
					} else {
						$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_status' );
					}
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_handler' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'RESOLVE':
			$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
			if ( access_has_bug_level( access_get_status_threshold( $t_resolved_status, bug_get_field( $t_bug_id, 'project_id' ) ), $t_bug_id ) ) {
				if ( ( $t_status < $t_resolved_status ) &&
						bug_check_workflow($t_status, $t_resolved_status ) ) {
					$f_resolution = gpc_get_int( 'resolution' );
					$f_fixed_in_version = gpc_get_string( 'fixed_in_version', '' );
					/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					bug_resolve( $t_bug_id, $f_resolution, $f_fixed_in_version, $f_bug_notetext, null, null, $f_bug_noteprivate );
					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_status' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'UP_PRIOR':
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				$f_priority = gpc_get_int( 'priority' );
				/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
				bug_set_field( $t_bug_id, 'priority', $f_priority );
				helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'UP_STATUS':
			$f_status = gpc_get_int( 'status' );
			$t_project = bug_get_field( $t_bug_id, 'project_id' );
			if ( access_has_bug_level( access_get_status_threshold( $f_status, $t_project ), $t_bug_id ) ) {
				if ( TRUE == bug_check_workflow($t_status, $f_status ) ) {
					/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					bug_set_field( $t_bug_id, 'status', $f_status );

					# Add bugnote if supplied
					if ( !is_blank( $f_bug_notetext ) ) {
						bugnote_add( $t_bug_id, $f_bug_notetext, null, $f_bug_noteprivate );
					}

					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_status' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'UP_CATEGORY':
			$f_category_id = gpc_get_int( 'category' );
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				if ( category_exists( $f_category_id ) ) {
					/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					bug_set_field( $t_bug_id, 'category_id', $f_category_id );
					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_category' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'UP_FIXED_IN_VERSION':
			$f_fixed_in_version = gpc_get_string( 'fixed_in_version' );
			$t_project_id = bug_get_field( $t_bug_id, 'project_id' );

			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				if ( $f_fixed_in_version === '' || version_get_id( $f_fixed_in_version, $t_project_id ) !== false ) {
					/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					bug_set_field( $t_bug_id, 'fixed_in_version', $f_fixed_in_version );
					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_version' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'UP_TARGET_VERSION':
			$f_target_version = gpc_get_string( 'target_version' );
			$t_project_id = bug_get_field( $t_bug_id, 'project_id' );

			if ( access_has_bug_level( config_get( 'roadmap_update_threshold' ), $t_bug_id ) ) {
				if ( $f_target_version === '' || version_get_id( $f_target_version, $t_project_id ) !== false ) {
					/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					bug_set_field( $t_bug_id, 'target_version', $f_target_version );
					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_version' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'VIEW_STATUS':
			if ( access_has_bug_level( config_get( 'change_view_status_threshold' ), $t_bug_id ) ) {
				$f_view_status = gpc_get_int( 'view_status' );
				/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
				bug_set_field( $t_bug_id, 'view_state', $f_view_status );
				helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'SET_STICKY':
			if ( access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $t_bug_id ) ) {
				$f_sticky = bug_get_field( $t_bug_id, 'sticky' );
				// The new value is the inverted old value
				/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
				bug_set_field( $t_bug_id, 'sticky', intval( !$f_sticky ) );
				helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = lang_get( 'bug_actiongroup_access' );
			}
			break;

		case 'CUSTOM':
			if ( 0 === $f_custom_field_id ) {
				trigger_error( ERROR_GENERIC, ERROR );
			}

			/** @todo we need to issue a helper_call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
			$t_form_var = "custom_field_$f_custom_field_id";
			$t_custom_field_value = gpc_get_custom_field( $t_form_var, $t_custom_field_def['type'], null );
			custom_field_set_value( $f_custom_field_id, $t_bug_id, $t_custom_field_value );
			helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			break;

		default:
			trigger_error( ERROR_GENERIC, ERROR );
		}

		// Bug Action Event
		event_signal( 'EVENT_BUG_ACTION', array( $f_action, $t_bug_id ) );
	}

	form_security_purge( $t_form_name );

	$t_redirect_url = 'view_all_bug_page.php';

	if ( count( $t_failed_ids ) > 0 ) {
		html_page_top();

		echo '<div align="center"><br />';
		echo '<table class="width75">';
		foreach( $t_failed_ids as $t_id => $t_reason ) {
			printf( "<tr><td width=\"50%%\">%s: %s</td><td>%s</td></tr>\n", string_get_bug_view_link( $t_id ), bug_get_field( $t_id, 'summary' ), $t_reason );
		}
		echo '</table><br />';
		print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
		echo '</div>';

		html_page_bottom();
	} else {
		print_header_redirect( $t_redirect_url );
	}
