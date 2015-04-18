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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	form_security_validate( 'manage_proj_user_copy' );

	auth_reauthenticate();

	$f_project_id		= gpc_get_int( 'project_id' );
	$f_other_project_id	= gpc_get_int( 'other_project_id' );
	$f_copy_from		= gpc_get_bool( 'copy_from' );
	$f_copy_to			= gpc_get_bool( 'copy_to' );

	if ( $f_copy_from ) {
		$t_src_project_id = $f_other_project_id;
		$t_dst_project_id = $f_project_id;
	} else if ( $f_copy_to ) {
		$t_src_project_id = $f_project_id;
		$t_dst_project_id = $f_other_project_id;
	} else {
		/** @todo Should this become a separate error? */
		trigger_error( ERROR_CATEGORY_NO_ACTION, ERROR );
	}

	# We should check both since we are in the project section and an
	#  admin might raise the first threshold and not realize they need
	#  to raise the second
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_dst_project_id );
	access_ensure_project_level( config_get( 'project_user_threshold' ), $t_dst_project_id );

	project_copy_users( $t_dst_project_id, $t_src_project_id, access_get_project_level( $t_dst_project_id ) );

	form_security_purge( 'manage_proj_user_copy' );

	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
