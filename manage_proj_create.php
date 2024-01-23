<?php
# MantisBT - A PHP based bugtracking system

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
 * Create a project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );

form_security_validate( 'manage_proj_create' );

auth_reauthenticate();

$f_name 		= gpc_get_string( 'name' );
$f_description 	= gpc_get_string( 'description' );
$f_view_state	= gpc_get_int( 'view_state' );
$f_status		= gpc_get_int( 'status' );
$f_file_path	= gpc_get_string( 'file_path', '' );
$f_inherit_global = gpc_get_bool( 'inherit_global', 0 );

$t_data = array(
	'payload' => array(
		'name' => $f_name,
		'description' => $f_description,
		'file_path' => $f_file_path,
		'inherit_global' => $f_inherit_global,
		'view_state' => array( 'id' => $f_view_state ),
		'status' => array( 'id' => $f_status )
	),
	'options' => array(
		'return_project' => false
	)
);

$t_command = new ProjectAddCommand( $t_data );
$t_result = $t_command->execute();

$t_project_id = $t_result['id'];

$f_parent_id	= gpc_get_int( 'parent_id', 0 );
$f_inherit_parent = gpc_get_bool( 'inherit_parent', 0 );

# If parent project id != 0 then we're creating a subproject
if( 0 != $f_parent_id ) {
	$t_data = array(
		'query' => array(
			'project_id' => (int)$f_parent_id
		),
		'payload' => array(
			'project' => array(
				'id' => (int)$t_project_id
			 ),
			'inherit_parent' => (bool)$f_inherit_parent
		)
	);

	$t_command = new ProjectHierarchyAddCommand( $t_data );
	$t_command->execute();

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_parent_id . '#subprojects';
} else {
	$t_redirect_url = 'manage_proj_page.php';
}

form_security_purge( 'manage_proj_create' );

print_header_redirect( $t_redirect_url );
