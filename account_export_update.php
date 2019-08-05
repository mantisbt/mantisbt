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
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses layout_api.php
 */

use Mantis\Export\TableWriterFactory;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'layout_api.php' );

form_security_validate( 'account_export_update' );
auth_ensure_user_authenticated();
current_user_ensure_unprotected();

$f_provider_id = gpc_get_string( 'provider_id' );
$f_action = gpc_get_string( 'action' );

$t_user_id = auth_get_current_user_id();
if( $f_action == 'DEFAULT' ) {
	$t_providers = TableWriterFactory::getProviders();
	if( !isset( $t_providers[$f_provider_id] ) ) {
		#@TODO error
		exit();
	}
	$t_global_default = config_get( 'export_default_plugin', null, ALL_USERS, ALL_PROJECTS );
	$t_current_user_default = config_get( 'export_default_plugin', null, $t_user_id, ALL_PROJECTS );
	if( $t_current_user_default != $f_provider_id ) {
		# like other config overrides, if the value is the same as the global config,
		# remove this specific user override
		if( $f_provider_id == $t_global_default ) {
			config_delete( 'export_default_plugin', $t_user_id, ALL_PROJECTS );
		} else {
			config_set( 'export_default_plugin', $f_provider_id, $t_user_id, ALL_PROJECTS );
		}
	}
} else {
	error_parameters( 'ACTION' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

form_security_purge( 'account_export_update' );
$t_redirect_url = 'account_export_page.php';
layout_page_header();
layout_page_begin();
html_operation_successful( $t_redirect_url );
layout_page_end();
