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
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses layout_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'layout_api.php' );

form_security_validate( 'manage_config_export_set' );
auth_ensure_user_authenticated();

if( !export_can_manage_global_config() ) {
	access_denied();
}

$f_provider_id = gpc_get_string( 'provider_id' );
$f_action = gpc_get_string( 'action' );


$t_export_config = config_get( 'export_plugins', array(), ALL_USERS, ALL_PROJECTS );
$t_config_changed = false;

switch( $f_action ) {
	case 'ENABLE':
		if( !isset( $t_export_config[$f_provider_id] ) ) {
			#TODO error
			exit();
		}
		$t_export_config[$f_provider_id]['enabled'] = true;
		$t_config_changed = true;
		break;

	case 'DISABLE':
		if( !isset( $t_export_config[$f_provider_id] ) ) {
			#TODO error
			exit();
		}
		$t_export_config[$f_provider_id]['enabled'] = false;
		$t_config_changed = true;
		break;

	case 'REMOVE':
		unset( $t_export_config[$f_provider_id] );
		$t_config_changed = true;
		break;

	case 'DEFAULT':
		config_set( 'export_default_plugin', $f_provider_id, ALL_USERS, ALL_PROJECTS );
		break;
	default:
		error_parameters( 'ACTION' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

if( $t_config_changed ) {
	config_set( 'export_plugins', $t_export_config, ALL_USERS, ALL_PROJECTS );
}

form_security_purge( 'manage_config_export_set' );
$t_redirect_url = 'manage_config_export_page.php';
layout_page_header();
layout_page_begin();
html_operation_successful( $t_redirect_url );
layout_page_end();
