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
 * Copy Users between projects
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses project_api.php
 *
 * Unhandled exceptions will be caught by the default error handler
 * @noinspection PhpUnhandledExceptionInspection
 */

use Mantis\Exceptions\ClientException;

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );

form_security_validate( 'manage_proj_user_copy' );

auth_reauthenticate();

$f_project_id		= gpc_get_int( 'project_id' );
$f_other_project_id	= gpc_get_int( 'other_project_id' );
$f_copy_from		= gpc_get_bool( 'copy_from' );
$f_copy_to			= gpc_get_bool( 'copy_to' );

if( $f_copy_from ) {
	$t_src_project_id = $f_other_project_id;
	$t_dst_project_id = $f_project_id;
} else if( $f_copy_to ) {
	$t_src_project_id = $f_project_id;
	$t_dst_project_id = $f_other_project_id;
} else {
	throw new ClientException( "Copy action to/from is required", ERROR_NO_COPY_ACTION );
}

$t_access_level_limit = access_get_project_level( $t_dst_project_id );
$t_rows = project_get_local_user_rows( $t_src_project_id );
foreach( $t_rows as $t_row ) {
	$t_access_level = $t_row['access_level'];
	if( $t_access_level > $t_access_level_limit ) {
		$t_access_level = $t_access_level_limit;
	}

	$t_data = array(
		'payload' => array(
			'project' => array( 'id' => $t_dst_project_id ),
			'user' => array( 'id' => $t_row['user_id'] ),
			'access_level' => array( 'id' => $t_access_level )
		)
	);

	$t_command = new ProjectUsersAddCommand( $t_data );
	$t_command->execute();
}

form_security_purge( 'manage_proj_user_copy' );

print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id . "#project-users");
