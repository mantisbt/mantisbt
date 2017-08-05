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
 * Display bug relationship graph
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
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
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
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
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

if( 'relation' == $f_type ) {
	$t_graph_type = 'relation';
	$t_graph_relation = true;
} else {
	$t_graph_type = 'dependency';
	$t_graph_relation = false;
}

if( 'horizontal' == $f_orientation ) {
	$t_graph_orientation = 'horizontal';
	$t_graph_horizontal = true;
} else {
	$t_graph_orientation = 'vertical';
	$t_graph_horizontal = false;
}

$t_bug = bug_get( $f_bug_id, true );

if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

access_ensure_bug_level( config_get( 'view_bug_threshold' ), $f_bug_id );

compress_enable();

layout_page_header( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
layout_page_begin();
?>
<br />

<table class="width100" cellspacing="1">

<tr>
	<!-- Title -->
	<td class="bold">
		<?php
		if( $t_graph_relation ) {
			echo lang_get( 'viewing_bug_relationship_graph_title' );
		} else {
			echo lang_get( 'viewing_bug_dependency_graph_title' );
		}
		?>
	</td>
	<!-- Links -->
	<td class="pull-right">
		<!-- View Issue -->
		<span class="small"><?php print_link_button( 'view.php?id=' . $f_bug_id, lang_get( 'view_issue' ) ) ?></span>

		<!-- Relation/Dependency Graph Switch -->
		<span class="small">
<?php
		if( $t_graph_relation ) {
			print_link_button( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&graph=dependency', lang_get( 'dependency_graph' ) );
		} else {
			print_link_button( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&graph=relation', lang_get( 'relation_graph' ) );
		}
?>
		</span>
<?php
		if( !$t_graph_relation ) {
?>
		<!-- Horizontal/Vertical Switch -->
		<span class="small">
<?php
		if( $t_graph_horizontal ) {
			print_link_button( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&graph=dependency&orientation=vertical', lang_get( 'vertical' ) );
		} else {
			print_link_button( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&graph=dependency&orientation=horizontal', lang_get( 'horizontal' ) );
		}
?>
		</span>
<?php
		}
?>
	</td>
</tr>

<tr>
	<!-- Graph -->
	<td colspan="2">
<?php
	if( $t_graph_relation ) {
		$t_graph = relgraph_generate_rel_graph( $f_bug_id );
	} else {
		$t_graph = relgraph_generate_dep_graph( $f_bug_id, $t_graph_horizontal );
	}

	relgraph_output_map( $t_graph, 'relationship_graph_map' );
?>
		<div class="center relationship-graph">
			<img src="bug_relationship_graph_img.php?bug_id=<?php echo $f_bug_id ?>&amp;graph=<?php echo $t_graph_type ?>&amp;orientation=<?php echo $t_graph_orientation ?>"
				border="0" usemap="#relationship_graph_map" />
		</div>
	</td>
</tr>

<tr>
	<!-- Legend -->
	<td colspan="2">
		<table class="hide">
		<tr>
			<td class="center">
				<img alt="" src="images/rel_related.png" />
				<?php echo lang_get( 'related_to' ) ?>
			</td>
			<td class="center">
				<img alt="" src="images/rel_dependant.png" />
				<?php echo lang_get( 'blocks' ) ?>
			</td>
			<td class="center">
				<img alt="" src="images/rel_duplicate.png" />
				<?php echo lang_get( 'duplicate_of' ) ?>
			</td>
		</tr>
		</table>
	</td>
</tr>

</table>

<br />

<?php
$_GET['id'] = $f_bug_id;
$t_fields_config_option = 'bug_view_page_fields';
$t_show_page_header = false;
$t_force_readonly = true;
$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$t_file = __FILE__;

define( 'BUG_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/bug_view_inc.php' );
