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
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'compress_api.php' );
	require_once( 'current_user_api.php' );
	require_once( 'relationship_graph_api.php' );

	# If relationship graphs were made disabled, we disallow any access to
	# this script.

	auth_ensure_user_authenticated();

	if ( ON != config_get( 'relationship_graph_enable' ) )
		access_denied();

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_type			= gpc_get_string( 'graph', 'relation' );
	$f_orientation	= gpc_get_string( 'orientation', config_get( 'relationship_graph_orientation' ) );

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$t_bug = bug_get( $f_bug_id, true );

	compress_enable();

	$t_graph_relation = ( 'relation' == $f_type );
	$t_graph_horizontal = ( 'horizontal' == $f_orientation );

	if ( $t_graph_relation )
		$t_graph = relgraph_generate_rel_graph( $f_bug_id, $t_bug );
	else
		$t_graph = relgraph_generate_dep_graph( $f_bug_id, $t_bug, $t_graph_horizontal );

	relgraph_output_image( $t_graph );
