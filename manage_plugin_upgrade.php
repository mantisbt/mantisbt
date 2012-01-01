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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	/** @ignore */
	define( 'PLUGINS_DISABLED', true );

	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

form_security_validate( 'manage_plugin_upgrade' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_basename = gpc_get_string( 'name' );
$t_plugin = plugin_register( $f_basename, true );

if ( !is_null( $t_plugin ) ) {
	$t_status = plugin_upgrade( $t_plugin );
}

form_security_purge( 'manage_plugin_upgrade' );

print_successful_redirect( 'manage_plugin_page.php' );
