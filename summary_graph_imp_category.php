<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
	# <SQLI>
	# This page displays "improved" charts on categories : categories on bars and 3Dpie
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$t_project_id = helper_get_current_project();

	#checking if it's a per project statistic or all projects
	if ( ALL_PROJECTS == $t_project_id ) {
		$specific_where = ' 1=1';
	} else {
		$specific_where = " project_id='$t_project_id'";
	}

	$t_bug_table = config_get( 'mantis_bug_table' );

	$t_res_val = RESOLVED;
	$query = "SELECT id, UNIX_TIMESTAMP(date_submitted) as date_submitted, last_updated
			FROM $t_bug_table
			WHERE project_id='$t_project_id' AND status='$t_res_val'";
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


<?php print_summary_menu( 'summary_page.php' ) ?>
<br />
<?php print_menu_graph() ?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'graph_imp_category_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="summary_graph_bycategory.php" border="0" align="center" />
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="summary_graph_bycategory_pct.php" border="0" align="center" />
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>