<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
	# <SQLI>
	# This page displays "improved" charts on resolutions : bars, 3Dpie and a mix resolutions per status

	# --------------------------------------------------------
	# $Id: summary_graph_imp_resolution.php,v 1.23 2005-02-12 20:01:08 jlatour Exp $
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
	$t_user_id = auth_get_current_user_id();
	token_delete_by_type_owner( TOKEN_GRAPH, $t_user_id );
	$t_metrics = enum_bug_group( lang_get( 'resolution_enum_string' ), 'resolution');
	$t_token = token_add( serialize( $t_metrics ), TOKEN_GRAPH, $t_user_id );

?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'graph_imp_resolution_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_byresolution.php?width=<?php echo $t_graph_width?>&token=<?php echo $t_token?>" border="0" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_byresolution_pct.php?width=<?php echo $t_graph_width?>&token=<?php echo $t_token?>" border="0" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_byresolution_mix.php?width=<?php echo $t_graph_width?>&token=<?php echo $t_token?>" border="0" /></center>
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
