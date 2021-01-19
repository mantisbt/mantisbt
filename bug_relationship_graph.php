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
$f_show_summary	= gpc_get_bool( 'summary', false );

if( 'relation' == $f_type ) {
	$t_graph_type = 'relation';
	$t_graph_type_switch = 'dependency';
	$t_graph_relation = true;
	$t_title = lang_get( 'relation_graph' );
} else {
	$t_graph_type = 'dependency';
	$t_graph_type_switch = 'relation';
	$t_graph_relation = false;
	$t_title = lang_get( 'dependency_graph' );
}

if( 'horizontal' == $f_orientation ) {
	$t_graph_orientation = 'horizontal';
	$t_graph_orientation_switch = 'vertical';
	$t_graph_horizontal = true;
} else {
	$t_graph_orientation = 'vertical';
	$t_graph_orientation_switch = 'horizontal';
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

<div class="col-md-12 col-xs-12">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-sitemap', 'ace-icon' ); ?>
				<?php echo $t_title ?>
			</h4>
		</div>

		<div class="widget-body">
			<!-- Buttons -->
			<div class="widget-toolbox padding-8 clearfix noprint">
				<div class="btn-group pull-left">
<?php
	# View Issue

	print_link_button( 'view.php?id=' . $f_bug_id, lang_get( 'view_issue' ) );

	# Relation/Dependency Graph Switch
	print_link_button(
		'bug_relationship_graph.php?bug_id=' . $f_bug_id
		. '&graph=' . $t_graph_type_switch
		. '&summary=' . $f_show_summary,
		lang_get( $t_graph_relation ? 'dependency_graph' : 'relation_graph' )
	);

	# Horizontal/Vertical Switch
	if( !$t_graph_relation ) {
		print_link_button(
			'bug_relationship_graph.php?bug_id=' . $f_bug_id
			. '&graph=' . $t_graph_type
			. '&orientation=' . $t_graph_orientation_switch
			. '&summary=' . $f_show_summary,
			lang_get( $t_graph_orientation_switch )
		);
	}

	print_link_button(
		'bug_relationship_graph.php?bug_id=' . $f_bug_id
		. '&graph=' . $t_graph_type
		. '&orientation=' . $t_graph_orientation
		. '&summary=' . !$f_show_summary,
		lang_get( $f_show_summary ? 'hide_summary' : 'show_summary' )
	);
?>
				</div>
			</div>

			<!-- Graph -->
			<div class="center padding-8">
<?php
	if( $t_graph_relation ) {
		$t_graph = relgraph_generate_rel_graph( $f_bug_id, $f_show_summary );
	} else {
		$t_graph = relgraph_generate_dep_graph( $f_bug_id, $t_graph_horizontal, $f_show_summary );
	}

	$t_map_name = 'relationship_graph_map';
	relgraph_output_map( $t_graph, $t_map_name );

	$t_graph_src = "bug_relationship_graph_img.php?bug_id=$f_bug_id&graph=$t_graph_type&orientation=$t_graph_orientation&summary=$f_show_summary";
?>
				<img src="<?php echo $t_graph_src ?>"
					 usemap="#<?php echo $t_map_name ?>"
					 alt="<?php echo $t_title ?>"
				/>
			</div>

			<!-- Legend -->
			<div class="center widget-toolbox">
<?php
	$t_legend = array(
		'related_to' => 'rel_related.png',
		'blocks' => 'rel_dependant.png',
		'duplicate_of' => 'rel_duplicate.png',
	);
	foreach( $t_legend as $t_key => $t_image ) {
		$t_string = lang_get( $t_key );
?>
				<span class="padding-8">
					<img alt="<?php echo $t_string ?>" src="images/<?php echo $t_image ?>" />
					<?php echo $t_string ?>
				</span>
<?php
	}
?>
			</div>
		</div>
	</div>

	<div class="space-10"></div>
</div>

<?php
$_GET['id'] = $f_bug_id;
$t_show_page_header = false;
$t_force_readonly = true;
$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$t_file = __FILE__;

define( 'BUG_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/bug_view_inc.php' );
