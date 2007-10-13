<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.
	# <SQLI>
	# This page displays "improved" charts on status : the old one and a 3D Pie

	# --------------------------------------------------------
	# $Id: summary_graph_imp_status.php,v 1.24.2.1 2007-10-13 22:34:42 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	html_page_top1();
	html_page_top2();
	print_summary_menu( 'summary_page.php' );
	echo '<br />';

	print_menu_graph();
	$t_width = config_get( 'graph_window_width' );
	$t_graph_width = (int) ( ( $t_width - 50 ) * 0.6 );

	# gather the data for the graphs
	$t_metrics = create_bug_enum_summary( lang_get( 'status_enum_string' ), 'status' );
	$t_token = token_set( TOKEN_GRAPH, serialize( $t_metrics ) );

?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'graph_imp_status_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td>
		 <center><img src="summary_graph_bystatus.php?width=<?php echo $t_graph_width?>" border="0" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_bystatus_pct.php?width=<?php echo $t_graph_width?>" border="0" /></center>
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
