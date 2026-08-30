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
 * Add existing custom field to project
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

form_security_validate( 'manage_proj_custom_field_add_existing' );

auth_reauthenticate();

$f_field_id		= gpc_get_int( 'field_id' );
$f_project_id	= gpc_get_int( 'project_id' );

$t_command = new ProjectFieldLinkCommand( [
	'query' => [
		'field_id' => $f_field_id,
		'project_id' => $f_project_id,
	],
] );
$t_command->execute();

form_security_purge( 'manage_proj_custom_field_add_existing' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id . '#customfields';
print_header_redirect( $t_redirect_url );
