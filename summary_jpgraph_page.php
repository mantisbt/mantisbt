<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Initial code for this addon cames from Duncan Lisset
	# Modified and "make mantis codeguidlines compatible" by Rufinus
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

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
		$t_date_submitted = ($row["date_submitted"]);
		$t_last_updated = $row["last_updated"];

		if ($t_last_updated < $t_date_submitted) {
			$t_last_updated = 0;
			$t_date_submitted = 0;
		}

		$t_diff = $t_last_updated - $t_date_submitted;
		$t_total_time = $t_total_time + $t_diff;
		if ( $t_diff > $t_largest_diff ) {
			$t_largest_diff = $t_diff;
			$t_bug_id = $row["id"];
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
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_summary_menu( $g_summary_jpgraph_page ) ?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_summary_title ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_cumulative_bydate ?>" border="0">
	</td>
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_bydeveloper ?>" border="0">
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_byreporter ?>" border="0">
	</td>
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_byseverity ?>" border="0">
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_bystatus ?>" border="0">
	</td>
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_byresolution ?>" border="0">
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_bycategory ?>" border="0">
	</td>
	<td width="50%">
		<img src="<?php echo $g_summary_jpgraph_bypriority ?>" border="0">
	</td>
</tr>
</table>

<?php print_page_bot1( __FILE__ ) ?>