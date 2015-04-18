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
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	$g_bypass_headers = true;
	header( 'Content-type: ' );

	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

$t_plugin_path = config_get( 'plugin_path' );

$f_file = gpc_get_string( 'file' );
$t_matches = array();

if ( !preg_match( '/^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+[\/a-zA-Z0-9_-]*\.?[a-zA-Z0-9_-]*)/', $f_file, $t_matches ) ) {
	trigger_error( ERROR_GENERIC, ERROR );
}

$t_basename = $t_matches[1];
$t_file = $t_matches[2];

global $g_plugin_cache;
if ( !isset( $g_plugin_cache[$t_basename] ) ) {
	trigger_error( ERROR_PLUGIN_NOT_REGISTERED, ERROR );
}

plugin_file_include( $t_file, $t_basename );

