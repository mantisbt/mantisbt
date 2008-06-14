<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: bug_assign.php,v 1.42.16.1 2007-10-13 22:32:34 giallu Exp $
	# --------------------------------------------------------

	# Assign bug to user then redirect to viewing page

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );

	# helper_ensure_post();

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

	bug_assign( $f_bug_id, $f_handler_id );

	print_successful_redirect_to_bug( $f_bug_id );
?>
