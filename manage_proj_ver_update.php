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
 * Update Project Versions
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
 * @uses html_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'manage_proj_ver_update' );

auth_reauthenticate();

$f_version_id   = gpc_get_int( 'version_id' );
$f_date_order	= gpc_get_string( 'date_order' );
$f_new_version	= gpc_get_string( 'new_version' );
$f_description  = gpc_get_string( 'description' );
$f_released     = gpc_get_bool( 'released' );
$f_obsolete	    = gpc_get_bool( 'obsolete' );

$f_new_version	= trim( $f_new_version );
$t_version = version_get( $f_version_id );

$t_data = array(
	'query' => array(
		'project_id' => $t_version->project_id,
		'version_id' => $f_version_id
	),
	'payload' => array(
		'name' => $f_new_version,
		'description' => $f_description,
		'released' => $f_released,
		'obsolete' => $f_obsolete,
		'timestamp' => $f_date_order
	)
);

$t_command = new VersionUpdateCommand( $t_data );
$t_command->execute();

form_security_purge( 'manage_proj_ver_update' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $t_version->project_id . '#versions';
print_header_redirect( $t_redirect_url );
