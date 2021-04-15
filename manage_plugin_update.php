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
 * Plugin Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

/** @ignore */
define( 'PLUGINS_DISABLED', true );

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'manage_plugin_update' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$t_update_query = new DbQuery( 'UPDATE {plugin}
	SET priority=:priority, protected=:protected
	WHERE basename=:basename'
);

$t_query = new DbQuery( 'SELECT basename FROM {plugin}' );

foreach( $t_query->fetch_all() as $t_row ) {
	$t_basename = $t_row['basename'];

	$f_change = gpc_get_bool( 'change_'.$t_basename, 0 );
	if( !$f_change ) {
		continue;
	}

	$f_priority = gpc_get_int( 'priority_'.$t_basename, 3 );
	if( $f_priority < PLUGIN_PRIORITY_LOW || $f_priority > PLUGIN_PRIORITY_HIGH ) {
		error_parameters( 'priority_' . $t_basename );
		trigger_error( ERROR_INVALID_FIELD_VALUE, ERROR );
	}

	$f_protected = gpc_get_bool( 'protected_'.$t_basename, 0 );

	$t_update_query->bind( 'basename', $t_basename );
	$t_update_query->bind( 'priority', $f_priority );
	$t_update_query->bind( 'protected', $f_protected );
	$t_update_query->execute();
}

form_security_purge( 'manage_plugin_update' );

print_successful_redirect( 'manage_plugin_page.php' );
