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
	# $Id: bug_monitor.php,v 1.28.16.1 2007-10-13 22:32:42 giallu Exp $
	# --------------------------------------------------------

	# This file turns monitoring on or off for a bug for the current user

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );

	# helper_ensure_post();

	$f_bug_id	= gpc_get_int( 'bug_id' );
	$t_bug = bug_get( $f_bug_id, true );

	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	$f_action	= gpc_get_string( 'action' );

	access_ensure_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id );

	if ( 'delete' == $f_action ) {
		bug_unmonitor( $f_bug_id, auth_get_current_user_id() );
	} else { # should be 'add' but we have to account for other values
		bug_monitor( $f_bug_id, auth_get_current_user_id() );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
