<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Initial code for this addon cames from Duncan Lisset
	# Modified and "make mantis codeguidlines compatible" by Rufinus
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$t_res_val = RESOLVED;
	$query = "SELECT id, UNIX_TIMESTAMP(date_submitted) as date_submitted,
			UNIX_TIMESTAMP(last_updated) as last_updated
			FROM $g_mantis_bug_table
			WHERE project_id='$g_project_cookie_val' AND status='$t_res_val'";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$t_bug_id = 0;
	$t_largest_diff = 0;
	$t_total_time = 0;
	for ($i=0;$i<$bug_count;$i++) {
		$row = db_fetch_array( $result );
		$t_date_submitted = ($row['date_submitted']);
		$t_last_updated = $row['last_updated'];

		if ($t_last_updated < $t_date_submitted) {
			$t_last_updated = 0;
			$t_date_submitted = 0;
		}

		$t_diff = $t_last_updated - $t_date_submitted;
		$t_total_time = $t_total_time + $t_diff;
		if ( $t_diff > $t_largest_diff ) {
			$t_largest_diff = $t_diff;
			$t_bug_id = $row['id'];
		}
	}
	if ( $bug_count < 1 ) {
		$bug_count = 1;
	}
	$t_average_time 	= $t_total_time / $bug_count;

	$t_largest_diff 	= number_format( $t_largest_diff / 86400, 2 );
	$t_total_time		= number_format( $t_total_time / 86400, 2 );
	$t_average_time 	= number_format( $t_average_time / 86400, 2 );


?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php print_summary_menu( 'summary_jpgraph_page.php' ) ?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'summary_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="summary_graph_cumulative_bydate.php" border="0" />
	</td>
	<td width="50%">
		<img src="summary_graph_bydeveloper.php" border="0" />
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="summary_graph_byreporter.php" border="0" />
	</td>
	<td width="50%">
		<img src="summary_graph_byseverity.php" border="0" />
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="summary_graph_bystatus.php" border="0" />
	</td>
	<td width="50%">
		<img src="summary_graph_byresolution.php" border="0" />
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="summary_graph_bycategory.php" border="0" />
	</td>
	<td width="50%">
		<img src="summary_graph_bypriority.php" border="0" />
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
