<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_page.php,v 1.40 2004-07-27 00:22:13 thraxisp Exp $
	# --------------------------------------------------------
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
	$t_history_table = config_get( 'mantis_bug_history_table' );

	$t_clo_val = config_get( 'bug_resolved_status_threshold' );
	$query = "SELECT id, date_submitted, last_updated, status
			FROM $t_bug_table
			WHERE $specific_where AND status>='$t_clo_val'";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$t_bug_id       = 0;
	$t_largest_diff = 0;
	$t_total_time   = 0;
	for ($i=0;$i<$bug_count;$i++) {
		$row = db_fetch_array( $result );
		$t_date_submitted = db_unixtimestamp( $row['date_submitted'] );
		$t_last_updated   = db_unixtimestamp( $row['last_updated'] );
		$t_id = $row['id'];
		$t_status = $row['status'];

		# if the status is not the closed value, it may have passed through the
		#  status we consider closed (e.g., bug is CLOSED, not RESOLVED)
		#  we should look for the last time it was RESOLVED in the history
		if ( $t_status <> $t_clo_val ) {
			$query2 = "SELECT date_modified
				FROM " . $t_history_table . "
				WHERE bug_id=$t_id AND type=" . NORMAL_TYPE . 
							" AND field_name='status' AND new_value=$t_clo_val
				ORDER BY date_modified DESC";
			$result2 = db_query( $query2 );
			if ( db_num_rows( $result2 ) >= 1 ) {
				# if any were found, read the first (oldest) one and update the timestamp
				$row2 = db_fetch_array( $result2 );
				$t_last_updated   = db_unixtimestamp( $row2['date_modified'] );
			}
		}		

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
	
	$t_orct_arr = preg_split( '/[\)\/\(]/', lang_get( 'orct' ), -1, PREG_SPLIT_NO_EMPTY );

	$t_orcttab = "";
	foreach ( $t_orct_arr as $t_orct_s ) {
		$t_orcttab .= '<td class="right">';
		$t_orcttab .= ucwords( $t_orct_s );
		$t_orcttab .= '</td>';
	}
?>
<?php html_page_top1( lang_get( 'summary_link' ) ) ?>
<?php html_page_top2() ?>

<br />
<?php print_summary_menu( 'summary_page.php' ) ?>

<?php print_menu_graph() ?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'summary_title' ) ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<?php # PROJECT # ?>
		<?php if ( ALL_PROJECTS == $t_project_id ) { ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'by_project' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_project(); ?>
		</table>

		<br />
		<?php } ?>

		<?php # STATUS # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'by_status' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_enum( config_get( 'status_enum_string' ), 'status' ) ?>
		</table>

		<br />

		<?php # SEVERITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'by_severity' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_enum( config_get( 'severity_enum_string' ), 'severity' ) ?>
		</table>

		<br />

		<?php # CATEGORY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'by_category' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_category() ?>
		</table>

		<br />

		<?php # MISCELLANEOUS # ?>
		<table class="width100">
		<tr>
			<td class="form-title" colspan="5">
				<?php echo lang_get( 'time_stats' ) ?>
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
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'developer_stats' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_developer() ?>
		</table>
	</td>



	<td width="50%">
		<?php # DATE # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="5">
				<?php echo lang_get( 'by_date' ) ?>
			</td>
		</tr>
		<?php summary_print_by_date( config_get( 'date_partitions' ) ) ?>
		</table>

		<br />

		<?php # RESOLUTION # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'by_resolution' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_enum( config_get( 'resolution_enum_string' ), 'resolution' ) ?>
		</table>

		<br />

		<?php # PRIORITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'by_priority' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_enum( config_get( 'priority_enum_string' ), 'priority' ) ?>
		</table>

		<br />

		<?php # REPORTER # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'reporter_stats' ) ?>
			</td>
			<?php echo $t_orcttab ?>
		</tr>
		<?php summary_print_by_reporter() ?>
		</table>

		<br />

		<?php # REPORTER EFFECTIVENESS # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'reporter_effectiveness' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'severity' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'errors' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'total' ) ?>
			</td>
		</tr>
		<?php summary_print_reporter_effectiveness( config_get( 'severity_enum_string' ), config_get( 'resolution_enum_string' ) ) ?>
		</table>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<?php # REPORTER / RESOLUTION # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'reporter_by_resolution' ) ?>
			</td>
			<?php
			$t_arr = explode_enum_string( config_get( 'resolution_enum_string' ) );
			$enum_count = count( $t_arr );

			for ($i=0;$i<$enum_count;$i++) {
				print '<td>';
				$t_s = explode_enum_arr( $t_arr[$i] );
				$c_s[0] = db_prepare_string( $t_s[0] );
				echo ucwords( get_enum_element( 'resolution', $c_s[0] ) );
				print '</td>';
			}
			
			print '<td>';
			print lang_get( 'percentage_errors' );
			print '</td>';
			?>
		</tr>
		<?php summary_print_reporter_resolution( config_get( 'resolution_enum_string' ) ) ?>
		</table>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<?php # DEVELOPER / RESOLUTION # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="1">
				<?php echo lang_get( 'developer_by_resolution' ) ?>
			</td>
			<?php
			$t_arr = explode_enum_string( config_get( 'resolution_enum_string' ) );
			$enum_count = count( $t_arr );

			for ($i=0;$i<$enum_count;$i++) {
				print '<td>';
				$t_s = explode_enum_arr( $t_arr[$i] );
				$c_s[0] = db_prepare_string( $t_s[0] );
				echo ucwords( get_enum_element( 'resolution', $c_s[0] ) );
				print '</td>';
			}
			
			print '<td>';
			print lang_get( 'percentage_fixed' );
			print '</td>';
			?>
		</tr>
		<?php summary_print_developer_resolution( config_get( 'resolution_enum_string' ) ) ?>
		</table>
	</td>
</tr>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
