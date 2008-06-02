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

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'project_hierarchy_api.php' );

	auth_reauthenticate();
	form_security_validate( 'manage_proj_update_children' );

	$f_project_id = gpc_get_int( 'project_id' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$t_subproject_ids = current_user_get_accessible_subprojects( $f_project_id, true );
	foreach ( $t_subproject_ids as $t_subproject_id ) {
		$f_inherit_child = gpc_get_bool( 'inherit_child_' . $t_subproject_id, false );
		var_dump( $t_subproject_id, $f_project_id, $f_inherit_child );
		project_hierarchy_update( $t_subproject_id, $f_project_id, $f_inherit_child );
	}

	print_successful_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
