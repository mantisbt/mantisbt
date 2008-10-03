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
	# $Id: manage_proj_cat_copy.php,v 1.22.2.1 2007-10-13 22:33:31 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'category_api.php' );

	form_security_validate( 'manage_proj_cat_copy' );

	auth_reauthenticate();

	$f_project_id		= gpc_get_int( 'project_id' );
	$f_other_project_id	= gpc_get_int( 'other_project_id' );
	$f_copy_from		= gpc_get_bool( 'copy_from' );
	$f_copy_to			= gpc_get_bool( 'copy_to' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_other_project_id );

	if ( $f_copy_from ) {
	  $t_src_project_id = $f_other_project_id;
	  $t_dst_project_id = $f_project_id;
	} else if ( $f_copy_to ) {
	  $t_src_project_id = $f_project_id;
	  $t_dst_project_id = $f_other_project_id;
	} else {
		trigger_error( ERROR_CATEGORY_NO_ACTION, ERROR );
	}

	$rows = category_get_all_rows( $t_src_project_id );

	foreach ( $rows as $row ) {
		$t_category = $row['category'];

		if ( category_is_unique( $t_dst_project_id, $t_category ) ) {
			category_add( $t_dst_project_id, $t_category );
		}
	}

	form_security_purge( 'manage_proj_cat_copy' );

	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );

