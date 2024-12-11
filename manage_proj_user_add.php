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
 * Add User to Project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'manage_proj_user_add' );

auth_reauthenticate();

$f_project_id	= gpc_get_int( 'project_id' );
$f_user_id		= gpc_get_int_array( 'user_id', array() );
$f_access_level	= gpc_get_int( 'access_level' );

# Add user(s) to the specified project
foreach( $f_user_id as $t_user_id ) {
	$t_data = array(
		'payload' => array(
			'project' => array(
				'id' => $f_project_id
			),
			'user' => array(
				'id' => $t_user_id
			),
			'access_level' => array(
				'id' => $f_access_level
			)
		)
	);

	$t_command = new ProjectUsersAddCommand( $t_data );
	$t_command->execute();
}

form_security_purge( 'manage_proj_user_add' );

print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id . '#project-users');
