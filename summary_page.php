<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# if user below view summary threshold, then re-direct to mainpage.
	if ( !access_level_check_greater_or_equal( $g_view_summary_threshold ) ) {
		print_header_redirect( 'main_page.php' );
	}

	#checking if it's a per project statistic or all projects
	if ($g_project_cookie_val=='0000000') {
		$specific_where = ' 1=1';
	} else {
		$specific_where = " project_id='$g_project_cookie_val'";
	}
	$t_clo_val = CLOSED;
	$query = "SELECT id, UNIX_TIMESTAMP(date_submitted) as date_submitted,
			UNIX_TIMESTAMP(last_updated) as last_updated
			FROM $g_mantis_bug_table
			WHERE $specific_where AND status='$t_clo_val'";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$t_bug_id       = 0;
	$t_largest_diff = 0;
	$t_total_time   = 0;
	for ($i=0;$i<$bug_count;$i++) {
		$row = db_fetch_array( $result );
		$t_date_submitted = $row['date_submitted'];
		$t_last_updated   = $row['last_updated'];

		if ($t_last_updated < $t_date_submitted) {
			$t_last_updated   = 0;
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
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_summary_menu( 'summary_page.php' ) ?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_summary_title ?> <?php echo $s_orct ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<?php # STATUS # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_by_status ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( $g_status_enum_string, 'status' ) ?>
		</table>
	</td>
	<td width="50%">
		<?php # DATE # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_by_date ?>:
			</td>
		</tr>
		<?php print_bug_date_summary( $g_date_partitions ) ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td>
		<?php # SEVERITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_by_severity ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( $g_severity_enum_string, 'severity' ) ?>
		</table>
	</td>
	<td>
		<?php # RESOLUTION # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_by_resolution ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( $g_resolution_enum_string, 'resolution' ) ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td>
		<?php # CATEGORY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_by_category ?>:
			</td>
		</tr>
		<?php print_category_summary() ?>
		</table>
	</td>
	<td>
		<?php # PRIORITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_by_priority ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( $g_priority_enum_string, 'priority' ) ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td>
		<?php # MISCELLANEOUS # ?>
		<table class="width100">
		<tr>
			<td class="form-title">
				<?php echo $s_time_stats ?>:
			</td>
		</tr>
		<tr class="row-1">
			<td width="50%">
				<?php echo $s_longest_open_bug ?>
			</td>
			<td width="50%">
				<?php
					if ($t_bug_id>0) {
						print_bug_link( $t_bug_id );
					}
				?>
			</td>
		</tr>
		<tr class="row-2">
			<td>
				<?php echo $s_longest_open ?>
			</td>
			<td>
				<?php echo $t_largest_diff ?>
			</td>
		</tr>
		<tr class="row-1">
			<td>
				<?php echo $s_average_time ?>
			</td>
			<td>
				<?php echo $t_average_time ?>
			</td>
		</tr>
		<tr class="row-2">
			<td>
				<?php echo $s_total_time ?>
			</td>
			<td>
				<?php echo $t_total_time ?>
			</td>
		</tr>
		</table>
	</td>
	<td>
		&nbsp;
	</td>
</tr>
<tr valign="top">
	<td>
		<?php # DEVELOPER # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_developer_stats ?>:
			</td>
		</tr>
		<?php print_developer_summary() ?>
		</table>
	</td>
	<td>
		<?php # REPORTER # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s_reporter_stats ?>:
			</td>
		</tr>
		<?php print_reporter_summary() ?>
		</table>
	</td>
</tr>
</table>

<?php print_page_bot1( __FILE__ ) ?>
