<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'summary_api.php' );
?>
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

	$t_clo_val = CLOSED;
	$query = "SELECT id, UNIX_TIMESTAMP(date_submitted) as date_submitted,
			UNIX_TIMESTAMP(last_updated) as last_updated
			FROM $t_bug_table
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
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<?php print_summary_menu( 'summary_page.php' ) ?>

<?php print_menu_graph() ?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'summary_title' ) ?> <?php echo lang_get( 'orct' ) ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<?php # STATUS # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'by_status' ) ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( config_get( 'status_enum_string' ), 'status' ) ?>
		</table>

		<br />

		<?php # SEVERITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'by_severity' ) ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( config_get( 'severity_enum_string' ), 'severity' ) ?>
		</table>

		<br />

		<?php # CATEGORY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'by_category' ) ?>:
			</td>
		</tr>
		<?php print_category_summary() ?>
		</table>

		<br />

		<?php # MISCELLANEOUS # ?>
		<table class="width100">
		<tr>
			<td class="form-title">
				<?php echo lang_get( 'time_stats' ) ?>:
			</td>
		</tr>
		<tr class="row-1">
			<td width="50%">
				<?php echo lang_get( 'longest_open_bug' ) ?>
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
				<?php echo lang_get( 'longest_open' ) ?>
			</td>
			<td>
				<?php echo $t_largest_diff ?>
			</td>
		</tr>
		<tr class="row-1">
			<td>
				<?php echo lang_get( 'average_time' ) ?>
			</td>
			<td>
				<?php echo $t_average_time ?>
			</td>
		</tr>
		<tr class="row-2">
			<td>
				<?php echo lang_get( 'total_time' ) ?>
			</td>
			<td>
				<?php echo $t_total_time ?>
			</td>
		</tr>
		</table>

		<br />

		<?php # DEVELOPER # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'developer_stats' ) ?>:
			</td>
		</tr>
		<?php print_developer_summary() ?>
		</table>
	</td>



	<td width="50%">
		<?php # DATE # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'by_date' ) ?>:
			</td>
		</tr>
		<?php print_bug_date_summary( config_get( 'date_partitions' ) ) ?>
		</table>

		<br />

		<?php # RESOLUTION # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'by_resolution' ) ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( config_get( 'resolution_enum_string' ), 'resolution' ) ?>
		</table>

		<br />

		<?php # PRIORITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'by_priority' ) ?>:
			</td>
		</tr>
		<?php print_bug_enum_summary( config_get( 'priority_enum_string' ), 'priority' ) ?>
		</table>

		<br />

		<?php # REPORTER # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'reporter_stats' ) ?>:
			</td>
		</tr>
		<?php print_reporter_summary() ?>
		</table>
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
