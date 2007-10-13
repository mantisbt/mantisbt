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

	# --------------------------------------------------------
	# $Id: summary_page.php,v 1.54.2.1 2007-10-13 22:34:43 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'summary_api.php' );
?>
<?php
	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

	# Override the current page to make sure we get the appropriate project-specific configuration
	$g_project_override = $f_project_id;

	$t_user_id = auth_get_current_user_id();

	# @@@ giallu: this block of code is duplicated from helper_project_specific_where
	# the only diff is the commented line below: can we do better than this ?
	if ( ALL_PROJECTS == $f_project_id ) {
		$t_topprojects = $t_project_ids = user_get_accessible_projects( $t_user_id );
		foreach ( $t_topprojects as $t_project ) {
			$t_project_ids = array_merge( $t_project_ids, user_get_all_accessible_subprojects( $t_user_id, $t_project ) );
		}

		$t_project_ids = array_unique( $t_project_ids );
	} else {
		# access_ensure_project_level( VIEWER, $p_project_id );
		$t_project_ids = user_get_all_accessible_subprojects( $t_user_id, $f_project_id );
		array_unshift( $t_project_ids, $f_project_id );
	}

	$t_project_ids = array_map( 'db_prepare_int', $t_project_ids );

	if ( 0 == count( $t_project_ids ) ) {
		$specific_where = ' 1 <> 1';
	} elseif ( 1 == count( $t_project_ids ) ) {
		$specific_where = ' project_id=' . $t_project_ids[0];
	} else {
		$specific_where = ' project_id IN (' . join( ',', $t_project_ids ) . ')';
	}
	# end @@@ block

	$t_bug_table = config_get( 'mantis_bug_table' );
	$t_history_table = config_get( 'mantis_bug_history_table' );

	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	# the issue may have passed through the status we consider resolved
	#  (e.g., bug is CLOSED, not RESOLVED). The linkage to the history field
	#  will look up the most recent 'resolved' status change and return it as well
	$query = "SELECT b.id, b.date_submitted, b.last_updated, MAX(h.date_modified) as hist_update, b.status 
        FROM $t_bug_table b LEFT JOIN $t_history_table h 
            ON b.id = h.bug_id  AND h.type=0 AND h.field_name='status' AND h.new_value='$t_resolved'  
            WHERE b.status >='$t_resolved' AND $specific_where
            GROUP BY b.id, b.status, b.date_submitted, b.last_updated 
            ORDER BY b.id ASC";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$t_bug_id       = 0;
	$t_largest_diff = 0;
	$t_total_time   = 0;
	for ($i=0;$i<$bug_count;$i++) {
		$row = db_fetch_array( $result );
		$t_date_submitted = db_unixtimestamp( $row['date_submitted'] );		
		$t_id = $row['id'];
		$t_status = $row['status'];
		if ( $row['hist_update'] !== NULL ) {
            $t_last_updated   = db_unixtimestamp( $row['hist_update'] );
        } else {
        	$t_last_updated   = db_unixtimestamp( $row['last_updated'] );
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
		$t_orcttab .= $t_orct_s;
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
		<?php if ( 1 < count( $t_project_ids ) ) { ?>
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
			<td class="form-title"><?php echo lang_get( 'by_date' ); ?></td>
			<td class="right"><?php echo lang_get( 'legend_opened' ); ?></td>
			<td class="right"><?php echo lang_get( 'legend_resolved' ); ?></td>
			<td class="right"><?php echo lang_get( 'balance' ); ?></td>
		</tr>
		<?php summary_print_by_date( config_get( 'date_partitions' ) ) ?>
		</table>

		<br />

		<?php # ACTIVITY # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" width="86%"><?php echo lang_get( 'most_active' ); ?></td>
			<td class="right" width="14%"><?php echo lang_get( 'score' ); ?></td>
		</tr>
		<?php summary_print_by_activity() ?>
		</table>

		<br />

		<?php # LONGEST OPEN # ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" width="86%"><?php echo lang_get( 'longest_open' ); ?></td>
			<td class="right" width="14%"><?php echo lang_get( 'days' ); ?></td>
		</tr>
		<?php summary_print_by_age() ?>
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
				echo get_enum_element( 'resolution', $c_s[0] );
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
				echo get_enum_element( 'resolution', $c_s[0] );
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
