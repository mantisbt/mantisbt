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
	 * Assign bug to user then redirect to viewing page
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_api.php' );

	form_security_validate( 'bug_assign' );

	$f_bug_id = gpc_get_int( 'bug_id' );
	$t_bug = bug_get( $f_bug_id );

	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	$f_handler_id = gpc_get_int( 'handler_id', auth_get_current_user_id() );

	# check that current user has rights to assign the issue
	access_ensure_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $f_bug_id );

	$t_bug_sponsored = sponsorship_get_amount( sponsorship_get_all_ids( $f_bug_id ) ) > 0;
	if ( $t_bug_sponsored ) {
		if ( !access_has_bug_level( config_get( 'assign_sponsored_bugs_threshold' ), $f_bug_id ) ) {
			trigger_error( ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW, ERROR );
		}
	}

	if ( $f_handler_id != NO_USER ) {
		# check that new handler has rights to handle the issue
		access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id, $f_handler_id );

		if ( $t_bug_sponsored ) {
			if ( !access_has_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $f_bug_id, $f_handler_id ) ) {
				trigger_error( ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW, ERROR );
			}
		}
	}

	# Update handler and status
	$t_bug->handler_id = $f_handler_id;
	if( ( ON == config_get( 'auto_set_status_to_assigned' ) ) && ( NO_USER != $f_handler_id ) ) {
		$t_bug->status = config_get( 'bug_assigned_status' );
	}

	# Plugin support
	$t_new_bug = event_signal( 'EVENT_UPDATE_BUG', $t_bug, $f_bug_id );
	if ( !is_null( $t_new_bug ) ) {
		$t_bug = $t_new_bug;
	}

	# Update bug and send notifications
	$t_bug->update();

	form_security_purge( 'bug_assign' );

	print_successful_redirect_to_bug( $f_bug_id );
