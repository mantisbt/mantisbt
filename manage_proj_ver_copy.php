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
 * Copy Versions between projects
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
 * @uses print_api.php
 * @uses project_api.php
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
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'version_api.php' );

form_security_validate( 'manage_proj_ver_copy' );

auth_reauthenticate();

$f_project_id		= gpc_get_int( 'project_id' );
$f_other_project_id	= gpc_get_int( 'other_project_id' );
$f_copy_from		= gpc_get_bool( 'copy_from' );
$f_copy_to			= gpc_get_bool( 'copy_to' );

project_ensure_exists( $f_project_id );
project_ensure_exists( $f_other_project_id );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_other_project_id );

if( $f_copy_from ) {
	$t_src_project_id = $f_other_project_id;
	$t_dst_project_id = $f_project_id;
} else if( $f_copy_to ) {
	$t_src_project_id = $f_project_id;
	$t_dst_project_id = $f_other_project_id;
} else {
	trigger_error( ERROR_VERSION_NO_ACTION, ERROR );
}

# Get all active versions (i.e. exclude obsolete ones)
$t_rows = version_get_all_rows( $t_src_project_id );

foreach ( $t_rows as $t_row ) {
	$t_dst_version_id = version_get_id( $t_row['version'], $t_dst_project_id );
	if( $t_dst_version_id === false ) {
		# Version does not exist in target project
		version_add(
			$t_dst_project_id,
			$t_row['version'],
			$t_row['released'],
			$t_row['description'],
			$t_row['date_order']
		);
	} else {
		# Update existing version
		# Since we're ignoring obsolete versions, those marked as such in the
		# source project after an earlier copy operation will not be updated
		# in the target project.
		$t_version_data = new VersionData( $t_row );
		$t_version_data->id = $t_dst_version_id;
		$t_version_data->project_id = $t_dst_project_id;

		version_update( $t_version_data );
	}
}

form_security_purge( 'manage_proj_ver_copy' );

print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
