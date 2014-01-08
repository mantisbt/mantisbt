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

	require_once( 'project_hierarchy_api.php' );

	form_security_validate( 'manage_proj_create' );

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'create_project_threshold' ) );

	$f_name 		= gpc_get_string( 'name' );
	$f_description 	= gpc_get_string( 'description' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_status		= gpc_get_int( 'status' );
	$f_file_path	= gpc_get_string( 'file_path', '' );
	$f_inherit_global = gpc_get_bool( 'inherit_global', 0 );
	$f_inherit_parent = gpc_get_bool( 'inherit_parent', 0 );

	$f_parent_id	= gpc_get_int( 'parent_id', 0 );

	if ( 0 != $f_parent_id ) {
		project_ensure_exists( $f_parent_id );
	}

	$t_project_id = project_create( strip_tags( $f_name ), $f_description, $f_status, $f_view_state, $f_file_path, true, $f_inherit_global );

	if ( ( $f_view_state == VS_PRIVATE ) && ( false === current_user_is_administrator() ) ) {
		$t_access_level = access_get_global_level();
		$t_current_user_id = auth_get_current_user_id();
		project_add_user( $t_project_id, $t_current_user_id, $t_access_level );
	}

	if ( 0 != $f_parent_id ) {
		project_hierarchy_add( $t_project_id, $f_parent_id, $f_inherit_parent );
	}

	event_signal( 'EVENT_MANAGE_PROJECT_CREATE', array( $t_project_id ) );

	form_security_purge( 'manage_proj_create' );

	$t_redirect_url = 'manage_proj_page.php';

	html_page_top( null, $t_redirect_url );
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
