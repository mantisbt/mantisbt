<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * This file turns monitoring on or off for a bug for the current user
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );

	# helper_ensure_post();

	$f_bug_id	= gpc_get_int( 'bug_id' );
	$t_bug = bug_get( $f_bug_id, true );
	$f_username = gpc_get_string( 'username', '' );

	$t_logged_in_user_id = auth_get_current_user_id(); 

	if ( is_blank( $f_username ) ) {
		$t_user_id = $t_logged_in_user_id;
	} else {
		$t_user_id = user_get_id_by_name( $f_username );
		if ( $t_user_id === false ) {
			error_parameters( $f_username );
			trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, ERROR );
		}
	}

	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	$f_action	= gpc_get_string( 'action' );

	if ( $t_logged_in_user_id == $t_user_id ) {
		access_ensure_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id );		
	} else {
		access_ensure_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $f_bug_id );
	}

	if ( 'delete' == $f_action ) {
		bug_unmonitor( $f_bug_id, $t_user_id );
	} else { # should be 'add' but we have to account for other values
		bug_monitor( $f_bug_id, $t_user_id );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
