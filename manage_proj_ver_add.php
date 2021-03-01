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
 * Add a new version to project
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
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

form_security_validate( 'manage_proj_ver_add' );

auth_reauthenticate();

$f_project_id	= gpc_get_int( 'project_id' );
$f_version		= gpc_get_string( 'version' );
$f_add_and_edit = gpc_get_bool( 'add_and_edit_version' );

# We reverse the array so that if the user enters multiple versions
#  they will likely appear with the last item entered at the top of the list
#  (i.e. in reverse chronological order).  Unless we find a way to make the
#  date_order fields different for each one, however, this is fragile, since
#  the DB may actually pull the rows out in any order
$t_versions = array_reverse( explode( '|', $f_version ) );

$t_version_id = 0;

foreach ( $t_versions as $t_version ) {
	if( is_blank( $t_version ) ) {
		continue;
	}

	$t_version = trim( $t_version );

	$t_data = array(
		'query' => array(
			'project_id' => (int)$f_project_id,
		),
		'payload' => array(
			'name' => $t_version,
		)
	);

	$t_command = new VersionAddCommand( $t_data );
	$t_result = $t_command->execute();
	$t_version_id = $t_result['id'];
}

form_security_purge( 'manage_proj_ver_add' );

if( $t_version_id == 0 ) {
	error_parameters( lang_get( 'version' ) );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

if( $f_add_and_edit ) {
	$t_redirect_url = 'manage_proj_ver_edit_page.php?version_id=' . $t_version_id;
} else {
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
}

layout_page_header( null, $t_redirect_url );
layout_page_begin( 'manage_overview_page.php' );

html_operation_successful( $t_redirect_url );

layout_page_end();
