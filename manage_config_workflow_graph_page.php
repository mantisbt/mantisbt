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
 * Manage configuration for workflow Config
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses workflow_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'graphviz_api.php' );
require_api( 'workflow_api.php' );

auth_reauthenticate();

if( !config_get( 'relationship_graph_enable' ) ) {
	access_denied();
}

html_page_top( lang_get( 'manage_workflow_graph' ) );
html_javascript_link( 'd3.v3.min.js' );
html_javascript_link( 'dagre-d3.min.js' );

print_manage_menu( 'adm_permissions_report.php' );
print_manage_config_menu( 'manage_config_workflow_graph_page.php' );

$t_project = helper_get_current_project();

$t_status_arr  = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );

$t_graph = new Graph();

foreach ( $t_status_arr as $t_from_status => $t_from_label ) {
	$t_graph->add_node( $t_from_status, array( 'label' => $t_from_label ) );
	$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
	foreach ( $t_enum_status as $t_to_status_id => $t_to_status_label ) {
		$t_graph->add_node( $t_to_status_id, array( 'label' => $t_to_status_label ) );
		if( workflow_transition_edge_exists( $t_from_status, $t_to_status_id ) ) {
			$t_graph->add_edge( $t_from_status, $t_to_status_id );
		}
	}
}

if( $t_project == ALL_PROJECTS ) {
	$t_project_title = lang_get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( lang_get( 'config_project' ), string_display( project_get_name( $t_project ) ) );
}
?>
	<br />
	<div class="center">
		<p class="bold"><?php echo $t_project_title ?></p>
		<br />
		<?php
			$t_graph->generate();
		?>
	</div>
<?php
html_page_bottom();
