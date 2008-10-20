<?php
# Mantis - a php based bugtracking system

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

/**
 * @package MantisBT
 * @version $Id$
 * @copyright Copyright (C) 2000 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( 'core.php' );

$t_core_path = config_get( 'core_path' );

require_once( $t_core_path . 'version_api.php' );

$f_protocol_version = gpc_get_int( 'protocol_version', -1 );
$f_project_id = gpc_get_int( 'project_id', -1 );
$f_version = gpc_get_string( 'version', '' );

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );
header( 'Content-Type: text/plain' );

# Protocol Version 1
#
# <operation result>
# <multi line parameters>
#
# operation result: up_to_date | update_available | access_denied | failure
#
# up_to_date has no parameters
#
# update_available Parameters:
# latest version
# changelog link
# version description
#
# Access Denied has no parameters.  Issue for case where access is denied or project/version doesn't exist.
#
# Failure:
# error message (e.g. database failure, in maintenance, etc)

$t_latest_supported_protocol = 1;
$t_access_denied_string = VERSION_CHECK_ACCESS_DENIED . "\n";
$t_failure_string = VERSION_CHECK_FAILURE . "\n";

# Make sure protocol version is specified.
if ( $f_protocol_version == -1 ) {
	echo $t_failure_string, "protocol version not specified.\n";
	die;
}

# Make sure request version is supported.
if ( $f_protocol_version < 1 || $f_protocol_version > $t_latest_supported_protocol ) {
	echo $t_failure_string, "protocol version '" . $f_protocol_version . "' not supported.\n";
	die;
}

# Make sure project id is specified.
if ( $f_project_id == -1 ) {
	echo $t_failure_string, "project id not specified.\n";
	die;
}

# Make sure version name is specified.
if ( $f_version == '' ) {
	echo $t_failure_string, "version name not specified.\n";
	die;
}

# Retrieve configuration
$t_public_projects = config_get( 'check_version_for_public_projects_enabled' );
$t_private_projects = config_get( 'check_version_for_public_projects_enabled' );

# if the feature is disabled for all kinds of projects, then return access denied response.
if ( !$t_public_projects && !$t_private_projects ) {
	echo $t_access_denied_string;
	die;
}

# if only public projects available, make sure the request is not referring to a public project.
if ( !$t_private_projects ) {
	$t_view_state = project_get_field( $f_project_id, 'view_state');
	if ( $t_view_state != VS_PUBLIC ) {
		echo $t_access_denied_string;
		die;
	}
}

$t_client_version_id = version_get_id( $f_version, $f_project_id );
if ( $t_client_version_id === false ) {
	echo $t_failure_string, "version not found.\n";
	die;
}

$t_version = version_get_latest_by_upgrade_tag( $t_client_version_id );

if ( $t_version->id == $t_client_version_id ) {
	echo VERSION_CHECK_UP_TO_DATE . "\n";
	die;
}

echo VERSION_CHECK_UPDATE_AVAILABLE . "\n";
echo $t_version->version, "\n";
$t_path = config_get( 'path' );
echo $t_path . "changelog_page.php?version=", $t_version->id, "\n";
echo $t_version->description, "\n";