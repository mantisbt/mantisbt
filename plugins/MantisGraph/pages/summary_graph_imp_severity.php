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
	 * This page displays "improved" charts on severities : bars, 3Dpie and a mix severities per status
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	html_page_top();

	print_summary_menu( 'summary_page.php' );
	echo '<br />';
	print_summary_submenu();
	$t_width = plugin_config_get( 'window_width' );
	$t_graph_width = (int) ( ( $t_width - 50 ) * 0.6 );

	# gather the data for the graphs
	$t_metrics = enum_bug_group( lang_get( 'severity_enum_string' ), 'severity' );
	$t_token = token_set( TOKEN_GRAPH, serialize( $t_metrics ) );
?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo plugin_lang_get( 'graph_imp_severity_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="<?php echo plugin_page( 'summary_graph_byseverity.php')?>&width=<?php echo $t_graph_width?>" border="0" alt="" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="<?php echo plugin_page( 'summary_graph_byseverity_pct.php')?>&width=<?php echo $t_graph_width?>" border="0" alt="" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="<?php echo plugin_page( 'summary_graph_byseverity_mix.php')?>&width=<?php echo $t_graph_width?>" border="0" alt="" /></center>
	</td>
</tr>
</table>

<?php
	html_page_bottom();
