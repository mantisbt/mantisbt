<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
	# $Id: manage_proj_delete.php,v 1.29.2.1 2007-10-13 22:33:38 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	form_security_validate( 'manage_proj_delete' );

	auth_reauthenticate();

	$f_project_id = gpc_get_int( 'project_id' );

	access_ensure_project_level( config_get( 'delete_project_threshold' ), $f_project_id );

	$t_project_name = project_get_name( $f_project_id );

	helper_ensure_confirmed( lang_get( 'project_delete_msg' ) .
			'<br/>' . lang_get( 'project_name' ) . ': ' . $t_project_name,
			lang_get( 'project_delete_button' ) );

	project_delete( $f_project_id );

	form_security_purge( 'manage_proj_delete' );

	# Don't leave the current project set to a deleted project -
	#  set it to All Projects
	if ( helper_get_current_project() == $f_project_id ) {
		helper_set_current_project( ALL_PROJECTS );
	}

    $t_redirect_url = 'manage_proj_page.php';
	print_header_redirect( $t_redirect_url );
?>
