<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Initial code for this addon cames from Duncan Lisset
	# Modified and "make mantis codeguidlines compatible" by Rufinus

	# --------------------------------------------------------
	# $Id: summary_jpgraph_page.php,v 1.24 2005-02-12 20:01:08 jlatour Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );
	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	html_page_top1();
	html_page_top2();

	print_summary_menu( 'summary_jpgraph_page.php' );

	$t_graphs = array( 'summary_graph_cumulative_bydate', 'summary_graph_bydeveloper', 'summary_graph_byreporter',
			'summary_graph_byseverity', 'summary_graph_bystatus', 'summary_graph_byresolution',
			'summary_graph_bycategory', 'summary_graph_bypriority' );
	$t_wide = config_get( 'graph_summary_graphs_per_row' );
	$t_width = config_get( 'graph_window_width' );
	$t_graph_width = (int) ( ( $t_width - 50 ) / $t_wide );

?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'summary_title' ) ?>
	</td>
</tr>
<?php
	for ( $t_pos = 0; $t_pos < count($t_graphs ); $t_pos++ ) {
		if ( 0 == ( $t_pos % $t_wide ) ) {
			print( "<tr valign=\"top\">\n" );
		}
		echo '<td width="50%" align="center">';
		printf("<img src=\"%s.php?width=%d\" border=\"0\" />", $t_graphs[$t_pos], $t_graph_width );
		echo '</td>';
		if ( ( $t_wide - 1 ) == ( $t_pos % $t_wide ) ) {
			print( "</tr>\n" );
		}
	}
?>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
