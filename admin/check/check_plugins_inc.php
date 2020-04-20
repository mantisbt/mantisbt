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
 * Plugins Checks
 * @package MantisBT
 * @copyright Copyright (C) 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses plugin_api.php
 * @uses constant_inc.php
 */

if( !defined( 'CHECK_PLUGINS_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'plugin_api.php' );
require_api( 'constant_inc.php' );

check_print_section_header_row( 'Plugins' );

# Initialize
$t_plugins = plugin_find_all();
ksort($t_plugins);
plugin_init_installed();
$t_installed_plugins = array_filter(
	$t_plugins,
	function( $p ) { return plugin_is_registered( $p->basename ); }
);
$t_manage_plugins_link = '<a href="' . helper_mantis_url( 'manage_plugin_page.php' ) . '%s">%s</a>';

# Info row - plugins count
check_print_info_row(
	"Checking all available and installed plugins",
	count( $t_plugins ) . ' plugins, '
	. count( $t_installed_plugins ) . ' installed'
);

# Check installed plugins
foreach( $t_installed_plugins as $t_basename => $t_plugin ) {
	check_print_test_row(
		"Installed Plugin '$t_basename'' is operational",
		plugin_is_loaded( $t_basename ),
		array(
			false => "Plugin could not be loaded; check "
				. sprintf( $t_manage_plugins_link, '#installed', 'Manage Plugins page' )
				. " to ensure its dependencies are met or if it needs to be upgraded."
		)
	);
}

# Check force-installed plugins
# Note: not using plugin_get_force_installed() as we don't want to check MantisCore
$t_forced_plugins = config_get_global( 'plugins_force_installed' );
foreach( $t_forced_plugins as $t_basename => $t_priority ) {
	$t_plugin = plugin_register( $t_basename );
	check_print_test_warn_row(
		"Force-installed plugin '$t_basename' is available and valid",
		!$t_plugin instanceof InvalidPlugin,
		array(
			false => $t_plugin->description
				. " - review 'plugins_force_installed' configuration option"
		)
	);
}

# Check for invalid or missing plugins
$t_invalid_plugins = array_filter(
	$t_plugins,
	function( $p ) {
		return $p instanceof InvalidPlugin;
	}
);
foreach( $t_invalid_plugins as $t_plugin ) {
	$t_description = "'$t_plugin->name': $t_plugin->description";
	if( $t_plugin->status_message ) {
		$t_description .= "<br>$t_plugin->status_message";
	}
	$t_msg_contact = "Contact the Plugin's author.";

	switch( $t_plugin->status ) {
		case MantisPlugin::STATUS_MISSING_PLUGIN:
			check_print_test_row(
				$t_description,
				false,
				array(
					false => sprintf( $t_manage_plugins_link, '#invalid', 'Remove the Plugin' )
						. " or reinstall its source code."
				)
			);
			break;
		case MantisPlugin::STATUS_MISSING_BASE_CLASS:
			# Issue a warning instead of a failure, to cover the case of a directory
			# created under plugins/ for other purposes than storing a plugin.
			# https://github.com/mantisbt/mantisbt/pull/1565#discussion_r329311260
			check_print_test_warn_row(
				$t_description,
				false,
				array(
					false => "Rename the Plugin's directory or " . $t_msg_contact
				)
			);
			break;
		default:
			check_print_test_row(
				$t_description,
				false,
				array( false => $t_msg_contact )
			);
	}
}
