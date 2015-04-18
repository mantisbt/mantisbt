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
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

$t_plugin_path = config_get( 'plugin_path' );

$f_page= gpc_get_string( 'page' );
$t_matches = array();

if ( !preg_match( '/^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+[\/a-zA-Z0-9_-]*)/', $f_page, $t_matches ) ) {
	trigger_error( ERROR_GENERIC, ERROR );
}

$t_basename = $t_matches[1];
$t_action = $t_matches[2];

global $g_plugin_cache;
if ( !isset( $g_plugin_cache[$t_basename] ) ) {
	trigger_error( ERROR_PLUGIN_NOT_REGISTERED, ERROR );
}

$t_page = $t_plugin_path.$t_basename.DIRECTORY_SEPARATOR.
		'pages'.DIRECTORY_SEPARATOR.$t_action.'.php';

if ( !is_file( $t_page ) ) {
		trigger_error( ERROR_PLUGIN_PAGE_NOT_FOUND, ERROR );
}

if( plugin_needs_upgrade( $g_plugin_cache[$t_basename] ) ) {
	error_parameters( $t_basename );
	trigger_error( ERROR_PLUGIN_UPGRADE_NEEDED, ERROR );
}

plugin_push_current( $t_basename );
include( $t_page );

