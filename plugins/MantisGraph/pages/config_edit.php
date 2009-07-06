<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

form_security_validate( 'plugin_graph_config_edit' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_library = gpc_get_int( 'eczlibrary', ON );
$f_jpgraph_antialias = gpc_get_int( 'jpgraph_antialias', ON );


if( plugin_config_get( 'eczlibrary' ) != $f_library ) {
	plugin_config_set( 'eczlibrary', $f_library );
}

if( plugin_config_get( 'jpgraph_antialias' ) != $f_jpgraph_antialias ) {
	plugin_config_set( 'jpgraph_antialias', $f_jpgraph_antialias );
}

form_security_purge( 'plugin_graph_config_edit' );

print_successful_redirect( plugin_page( 'config', true ) );
