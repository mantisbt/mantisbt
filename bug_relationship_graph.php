<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_relationship_graph.php,v 1.3 2005-04-22 21:34:03 prichards Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'relationship_graph_api.php' );

	# If relationship graphs were made disabled, we disallow any access to
	# this script.

	auth_ensure_user_authenticated();

	if ( ON != config_get( 'relationship_graph_enable' ) )
		access_denied();

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_type			= gpc_get_string( 'graph', 'relation' );
	$f_orientation	= gpc_get_string( 'orientation', config_get( 'relationship_graph_orientation' ) );

	if ( 'relation' == $f_type ) {
		$t_graph_type = 'relation';
		$t_graph_relation = true;
	} else {
		$t_graph_type = 'dependency';
		$t_graph_relation = false;
	}

	if ( 'horizontal' == $f_orientation ) {
		$t_graph_orientation = 'horizontal';
		$t_graph_horizontal = true;
	} else {
		$t_graph_orientation = 'vertical';
		$t_graph_horizontal = false;
	}

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

	compress_enable();

	html_page_top1( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
	html_page_top2();
?>
<br />

<table class="width100" cellspacing="1">

<tr>
	<!-- Title -->
	<td class="form-title">
		<?php echo lang_get( 'viewing_bug_relationship_graph_title' ) ?>
	</td>
	<!-- Links -->
	<td class="right">
		<!-- View Issue -->
		<span class="small"><?php print_bracket_link( 'view.php?id=' . $f_bug_id, lang_get( 'view_issue' ) ) ?></span>

		<!-- Relation/Dependency Graph Switch -->
		<span class="small">
<?php
		if ( $t_graph_relation )
			print_bracket_link( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&amp;graph=dependency', lang_get( 'dependency_graph' ) );
		else
			print_bracket_link( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&amp;graph=relation', lang_get( 'relation_graph' ) );
?>
		</span>
<?php
		if ( !$t_graph_relation ) {
?>
		<!-- Horizontal/Vertical Switch -->
		<span class="small">
<?php
			if ( $t_graph_horizontal )
				print_bracket_link( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&amp;graph=dependency&orientation=vertical', lang_get( 'vertical' ) );
			else
				print_bracket_link( 'bug_relationship_graph.php?bug_id=' . $f_bug_id . '&amp;graph=dependency&orientation=horizontal', lang_get( 'horizontal' ) );
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
	if ( $t_graph_relation )
		$t_graph = relgraph_generate_rel_graph( $f_bug_id, $t_bug );
	else
		$t_graph = relgraph_generate_dep_graph( $f_bug_id, $t_bug, $t_graph_horizontal );

	relgraph_output_map( $t_graph, 'relationship_graph_map' );
?>
		<div class="center relationship-graph">
			<img src="bug_relationship_graph_img.php?bug_id=<?php echo $f_bug_id ?>&amp;graph=<?php echo $t_graph_type ?>&orientation=<?php echo $t_graph_orientation ?>"
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
	include( 'bug_view_inc.php' );
	html_page_bottom1( __FILE__ );
?>
