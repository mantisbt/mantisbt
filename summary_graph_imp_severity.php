<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
	# <SQLI>
	# This page displays "improved" charts on severities : bars, 3Dpie and a mix severities per status

	# --------------------------------------------------------
	# $Id: summary_graph_imp_severity.php,v 1.22 2005-02-12 20:01:08 jlatour Exp $
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
	$t_metrics = enum_bug_group( lang_get( 'severity_enum_string' ), 'severity' );
	$t_token = token_add( serialize( $t_metrics ), TOKEN_GRAPH, $t_user_id );

?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'graph_imp_severity_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_byseverity.php?width=<?php echo $t_graph_width?>&token=<?php echo $t_token?>" border="0" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_byseverity_pct.php?width=<?php echo $t_graph_width?>&token=<?php echo $t_token?>" border="0" /></center>
	</td>
</tr>
<tr valign="top">
	<td>
		<center><img src="summary_graph_byseverity_mix.php?width=<?php echo $t_graph_width?>&token=<?php echo $t_token?>" border="0" /></center>
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
