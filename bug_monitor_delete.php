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
	 * This file turns monitoring on or off for a bug for the current user
	 *
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

	form_security_validate( 'bug_monitor_delete' );

	$f_bug_id = gpc_get_int( 'bug_id' );
	$t_bug = bug_get( $f_bug_id, true );
	$f_user_id = gpc_get_int( 'user_id', NO_USER );

	$t_logged_in_user_id = auth_get_current_user_id();

	if ( $f_user_id === NO_USER ) {
		$t_user_id = $t_logged_in_user_id;
	} else {
		user_ensure_exists( $f_user_id );
		$t_user_id = $f_user_id;
	}

	if ( user_is_anonymous( $t_user_id ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, E_USER_ERROR );
	}

	bug_ensure_exists( $f_bug_id );

	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	if ( $t_logged_in_user_id == $t_user_id ) {
		access_ensure_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id );
	} else {
		access_ensure_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $f_bug_id );
	}

	bug_unmonitor( $f_bug_id, $t_user_id );

	form_security_purge( 'bug_monitor_delete' );

	print_successful_redirect_to_bug( $f_bug_id );
