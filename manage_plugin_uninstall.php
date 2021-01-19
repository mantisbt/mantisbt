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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 */

/** @ignore */
define( 'PLUGINS_DISABLED', true );

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'plugin_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'manage_plugin_uninstall' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

# register plugins and metadata without initializing
plugin_register_installed();

$f_basename = gpc_get_string( 'name' );
$t_plugin = plugin_register( $f_basename, true );

switch( $t_plugin->status ) {
	case MantisPlugin::STATUS_MISSING_PLUGIN:
	case MantisPlugin::STATUS_MISSING_BASE_CLASS:
		$t_message = 'plugin_remove_message';
		$t_button = 'remove_link';
		break;
	default:
		$t_message = 'plugin_uninstall_message';
		$t_button = 'plugin_uninstall';
}

helper_ensure_confirmed(
	sprintf( lang_get( $t_message ), string_display_line( $t_plugin->name ) ),
	lang_get( $t_button )
);

if( !is_null( $t_plugin ) ) {
	plugin_uninstall( $t_plugin );
}

form_security_purge( 'manage_plugin_uninstall' );

print_successful_redirect( 'manage_plugin_page.php' );
