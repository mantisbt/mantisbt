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
 * Loads plugin files
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses plugin_api.php
 */

$g_bypass_headers = true;
header( 'Content-type: ' );

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'plugin_api.php' );

$t_plugin_path = config_get( 'plugin_path' );

$f_file = gpc_get_string( 'file' );

$t_regex = '/^'
	# File must start with plugin name, ending with /
	. '([a-zA-Z0-9_-]+)\/'
	# Path must not start with a '.' to avoid arbitrary includes higher in the file system
	. '('. '(?:(?:[a-zA-Z0-9_-][.a-zA-Z0-9_-]*\/)*)'
	# Same goes for filename
	. '(?:[a-zA-Z0-9_-][.a-zA-Z0-9_-]*)'
	. ')$/';

if( !preg_match( $t_regex, $f_file, $t_matches ) ) {
	error_parameters( $f_file );
	trigger_error( ERROR_PLUGIN_INVALID_FILE, ERROR );
}

$t_basename = $t_matches[1];
$t_file = $t_matches[2];

$t_plugin = plugin_get( $t_basename );

plugin_file_include( $t_file, $t_basename );
