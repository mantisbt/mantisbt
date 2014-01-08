<?php
# MantisBT - a php based bugtracking system

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
	 * @version $Id$
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	/** @ignore */
	define( 'PLUGINS_DISABLED', true );

	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

form_security_validate( 'manage_plugin_uninstall' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

# register plugins and metadata without initializing
plugin_register_installed();

$f_basename = gpc_get_string( 'name' );
$t_plugin = plugin_register( $f_basename, true );

helper_ensure_confirmed( sprintf( lang_get( 'plugin_uninstall_message' ), string_display_line( $t_plugin->name ) ), lang_get( 'plugin_uninstall' ) );

if ( !is_null( $t_plugin ) ) {
	plugin_uninstall( $t_plugin );
} else {
	plugin_force_uninstall( $f_basename );
}

form_security_purge( 'manage_plugin_uninstall' );

print_successful_redirect( 'manage_plugin_page.php' );
