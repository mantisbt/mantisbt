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
 * Display Bug relationship Graph
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses relationship_graph_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'relationship_graph_api.php' );

# If relationship graphs were made disabled, we disallow any access to
# this script.

auth_ensure_user_authenticated();

if( ON != config_get( 'relationship_graph_enable' ) ) {
	access_denied();
}

$f_bug_id		= gpc_get_int( 'bug_id' );
$f_type			= gpc_get_string( 'graph', 'relation' );
$f_orientation	= gpc_get_string( 'orientation', config_get( 'relationship_graph_orientation' ) );
$f_show_summary	= gpc_get_bool( 'summary', false );

$t_bug = bug_get( $f_bug_id, true );

access_ensure_bug_level( config_get( 'view_bug_threshold', null, null, $t_bug->project_id ), $f_bug_id );

compress_enable();

$t_graph_relation = ( 'relation' == $f_type );
$t_graph_horizontal = ( 'horizontal' == $f_orientation );

if( $t_graph_relation ) {
	$t_graph = relgraph_generate_rel_graph( $f_bug_id, $f_show_summary );
} else {
	$t_graph = relgraph_generate_dep_graph( $f_bug_id, $t_graph_horizontal, $f_show_summary );
}

relgraph_output_image( $t_graph );
