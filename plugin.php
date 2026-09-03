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
 * Plugin file loader
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses plugin_api.php
 *
 * @noinspection PhpUnhandledExceptionInspection
 */

use Mantis\Exceptions\ClientException;

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'plugin_api.php' );

$t_plugin_path = config_get_global( 'plugin_path' );

$f_page = gpc_get_string( 'page' );

if( !preg_match( '/^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+[\/a-zA-Z0-9_-]*)/', $f_page, $t_matches ) ) {
	throw new ClientException( "Invalid Plugin page '$f_page'",
		ERROR_PLUGIN_INVALID_PAGE,
		[ $f_page ]
	);
}

$t_basename = $t_matches[1];
$t_action = $t_matches[2];

$t_plugin = plugin_get( $t_basename );

if( plugin_needs_upgrade( $t_plugin ) ) {
	throw new ClientException( "Plugin '$t_basename' must be upgraded",
		ERROR_PLUGIN_UPGRADE_NEEDED,
		[ $t_basename ]
	);
}

# Plugin can be registered but fail to load e.g. due to unmet dependencies
if( !plugin_is_loaded( $t_basename ) ) {
	throw new ClientException( "Plugin '$t_basename' is not loaded",
		ERROR_PLUGIN_NOT_LOADED,
		[ $t_basename ]
	);
}

$t_page = $t_plugin_path . $t_basename . '/pages/' . $t_action . '.php';

if( !is_file( $t_page ) ) {
	throw new ClientException( "Page '$t_action' does not exist in Plugin '$t_basename'",
		ERROR_PLUGIN_PAGE_NOT_FOUND,
		[ $t_basename, $t_action ]
	);
}

# rewrite headers to allow caching
if( gpc_isset( 'cache_key' ) ) {
	http_caching_headers( true );
}

plugin_push_current( $t_basename );
include( $t_page );
