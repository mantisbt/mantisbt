<p>
<table class="width100" cellspacing="0">
<tr class="row-category2">
	<td class="small-caption">
		<form method="post" action="view_all_set.php?f=3">
		<input type="hidden" name="f_type" value="1">
		<input type="hidden" name="f_sort" value="<?php echo $f_sort ?>">
		<input type="hidden" name="f_dir" value="<?php echo $f_dir ?>">
		<input type="hidden" name="f_page_number" value="<?php echo $f_page_number ?>">
		<input type="hidden" name="f_per_page" value="<?php echo $f_per_page ?>">
		<?php echo $s_reporter ?>
	</td>
	<td class="small-caption">
		<?php echo $s_assigned_to ?>
	</td>
	<td class="small-caption">
		<?php echo $s_category ?>
	</td>
	<td class="small-caption">
		<?php echo $s_severity ?>
	</td>
	<td class="small-caption">
		<?php echo $s_status ?>
	</td>
	<td class="small-caption">
		<?php echo $s_show ?>
	</td>
	<td class="small-caption">
		<?php echo $s_changed ?>
	</td>
	<td class="small-caption">
		<?php echo $s_hide_closed ?>
	</td>
</tr>
<tr>
	<td>
		<select name="f_user_id">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_reporter_option_list( $f_user_id ) ?>
		</select>
	</td>
	<td>
		<select name="f_assign_id">
			<option value="any"><?php echo $s_any ?></option>
			<option value="none" <?php check_selected( $f_assign_id, 'none' ); ?>><?php echo $s_none ?></option>
			<option value="any"></option>
			<?php #print_assign_to_option_list( $f_assign_id ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_category">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php # This shows orphaned categories as well as selectable categories ?>
			<?php print_category_complete_option_list( $f_show_category ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_severity">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_enum_string_option_list( 'severity', $f_show_severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_status">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_enum_string_option_list( 'status', $f_show_status ) ?>
		</select>
	</td>
	<td>
		<input type="text" name="f_per_page" size="3" maxlength="7" value="<?php echo $f_per_page ?>">
	</td>
	<td>
		<input type="text" name="f_highlight_changed" size="3" maxlength="7" value="<?php echo $f_highlight_changed ?>">
	</td>
	<td>
		<input type="checkbox" name="f_hide_closed" <?php check_checked( $f_hide_closed, 'on' ); ?>>
	</td>
</tr>
<tr class="row-category2">
	<td class="small-caption">
		<?php echo $s_search ?>
	</td>
	<td class="small-caption" colspan="2"><!--Start Date--></td>
	<td class="small-caption" colspan="2"><!--End Date--></td>
	<td class="small-caption" colspan="7">
		&nbsp;
	</td>
</tr>
<tr>
	<td>
	    <input type="text" size="16" name="f_search" value="<?php echo $f_search; ?>">
	</td>
	<td class="left" colspan="2">
	<!--
		<select name="f_start_month">
			<?php print_month_option_list( $f_start_month ) ?>
		</select>
		<select name="f_start_day">
			<?php print_day_option_list( $f_start_day ) ?>
		</select>
		<select name="f_start_year">
			<?php print_year_option_list( $f_start_year ) ?>
		</select>
	-->
	</td>
	<td class="left" colspan="2">
	<!--
		<select name="f_end_month">
			<?php print_month_option_list( $f_end_month ) ?>
		</select>
		<select name="f_end_day">
			<?php print_day_option_list( $f_end_day ) ?>
		</select>
		<select name="f_end_year">
			<?php print_year_option_list( $f_end_year ) ?>
		</select>
	-->
	</td>
	<td class="right" colspan="7">
		<input type="submit" name="f_filter" value="<?php echo $s_filter_button ?>">
		<input type="submit" name="f_csv" value="<?php echo $s_csv_export ?>">
		</form>
	</td>
</tr>
</table>
<?php
	$col_count = 7;
	if ( access_level_check_greater_or_equal( $g_bug_move_access_level ) ) {
		$col_count = 8;
	}

	if ( STATUS_LEGEND_POSITION_TOP == $g_status_legend_position ) {
		print_status_colors();
	}
?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="<?php echo $col_count ?>">
		<form method="get" action="bug_actiongroup_page.php">
		<?php echo $s_viewing_bugs_title ?>
		<?php
			if ( $row_count > 0 ) {
				$v_start = $t_offset+1;
				$v_end   = $t_offset+$row_count;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			PRINT "($v_start - $v_end / $t_query_count)";
		?>
		<span class="small"><?php print_bracket_link( 'print_all_bug_page.php', $s_print_all_bug_page_link ) ?></span>
	</td>
<?php if ($g_project_cookie_val!='0000000') {
			if ( access_level_check_greater_or_equal( UPDATER ) ) { ?>
	<td class="center" width="5%">
	&nbsp;
	</td>
<?php		}
		}?>
	<td class="right">
		[
		<?php
			# print out a link for each page eg. [ 1 2 3 ]
			for ( $i = 1; $i <= $t_page_count; $i++ ) {
				if ( $i == $f_page_number ) {
					PRINT "$i&nbsp;";
				} else {
					$f_search = urlencode( $f_search );
					PRINT "<a href=\"view_all_bug_page.php?f_page_number=$i&amp;f_search=$f_search\">$i</a>&nbsp;";
				}
			}
		?>
		]
	</td>
</tr>
<tr class="row-category">
<?php	if ( access_level_check_greater_or_equal( $g_bug_move_access_level ) ) { ?>
	<td class="center" width="2%">
		&nbsp;
	</td>
<?php	} ?>
<?php	if ($g_project_cookie_val!='0000000') {
			if ( access_level_check_greater_or_equal( UPDATER ) ) { ?>
	<td class="center" width="2%">
		&nbsp;
	</td>
<?php		}
		}?>
	<td class="center" width="5%">
		<?php print_view_bug_sort_link( 'P', 'priority', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'priority' ) ?>
	</td>
	<td class="center" width="8%">
		<?php print_view_bug_sort_link( $s_id, 'id', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'id' ) ?>
	</td>
	<td class="center" width="3%">
		#
	</td>
	<td class="center" width="12%">
		<?php print_view_bug_sort_link( $s_category, 'category', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'category' ) ?>
	</td>
	<td class="center" width="10%">
		<?php print_view_bug_sort_link( $s_severity, 'severity', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'severity' ) ?>
	</td>
	<td class="center" width="10%">
		<?php print_view_bug_sort_link( $s_status, 'status', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'status' ) ?>
	</td>
	<td class="center" width="12%">
		<?php print_view_bug_sort_link( $s_updated, 'last_updated', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'last_updated' ) ?>
	</td>
	<td class="center" width="38%">
		<?php print_view_bug_sort_link( $s_summary, 'summary', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'summary' ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="9">
		&nbsp;
	</td>
</tr>
<?php mark_time( 'begin loop' ); ?>
<?php
	for($i=0; $i < $row_count; $i++) {
		# prefix bug data with v_
		$row = db_fetch_array($result);

		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display( $v_summary );
		$t_last_updated = date( $g_short_date_format, $v_last_updated );

		# choose color based on status
		$status_color = get_status_color( $v_status );

		# grab the bugnote count
		$bugnote_count = bug_bugnote_count( $v_id );

		# grab the project name
		$project_name = get_project_field( $v_project_id, 'name' );

		if ( $bugnote_count > 0 ) {
			$query = "SELECT UNIX_TIMESTAMP(last_modified) as last_modified
					FROM $g_mantis_bugnote_table
					WHERE bug_id='$v_id'
					ORDER BY last_modified DESC
					LIMIT 1";
			$res2 = db_query( $query );
			$v_bugnote_updated = db_result( $res2, 0, 0 );
		}
?>
<tr>
	<?php	if ( access_level_check_greater_or_equal( $g_bug_move_access_level ) ) { ?>
	<td bgcolor="<?php echo $status_color ?>">
			<?php $t_transf="$v_id".','."$v_bug_text_id"; ?>
			<input type="checkbox" name="f_bug_arr[]" value="<?php echo $t_transf ?>">
	</td>
	<?php	} ?>

	<?php # the pencil shortcut
			#only for a per project basis
			if ($g_project_cookie_val!='0000000') {
				if ( access_level_check_greater_or_equal( UPDATER ) ) { ?>
	<td bgcolor="<?php echo $status_color ?>">
			<?php print " <input type=\"image\" name=\"update_$v_id\" src=\"images/update.png\" width=\"11\" height=\"11\">"; ?>
	</td>
	<?php		}
			}?>

	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php
			if ( ON == $g_show_priority_text ) {
				echo get_enum_element( 'priority', $v_priority );
			} else {
				print_status_icon( $v_priority );
			}
		?>
	</td>
	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php
			print_bug_link( $v_id );
		?>
	</td>
	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php
			if ($bugnote_count > 0){
				if ( $v_bugnote_updated >
					strtotime( "-$f_highlight_changed hours" ) ) {
					PRINT "<span class=\"bold\">$bugnote_count</span>";
				} else {
					PRINT $bugnote_count;
				}
			} else {
				echo '&nbsp;';
			}
		?>
	</td>
	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php
			# type project name if viewing 'all projects'
			if (( ON == $g_show_bug_project_links )&&( '0000000' == $g_project_cookie_val )) {
				echo '<small>[';
				print_view_bug_sort_link( $project_name, 'project_id', $f_sort, $f_dir );
				echo ']</small><br />';
			}

			echo $v_category;
		?>
	</td>
	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php print_formatted_severity_string( $v_status, $v_severity ) ?>
	</td>
	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php
			# print username instead of status
			if ( ( ON == $g_show_assigned_names )&&( $v_handler_id > 0 ) ) {
				echo '('.get_user_info( $v_handler_id, 'username' ).')';
			} else {
				echo get_enum_element( 'status', $v_status );
			}
		?>
	</td>
	<td class="center" bgcolor="<?php echo $status_color ?>">
		<?php
			if ( $v_last_updated >
				strtotime( "-$f_highlight_changed hours" ) ) {

				PRINT "<span class=\"bold\">$t_last_updated</span>";
			} else {
				PRINT $t_last_updated;
			}
		?>
	</td>
	<td class="left" bgcolor="<?php echo $status_color ?>">
		<?php
			PRINT $v_summary;
			if ( PRIVATE == $v_view_state ) {
			  PRINT "  [$s_private]";
			}
		 ?>
	</td>
</tr>
<?php
	}
?>
<? # Mass treatment ?>
<?php	if ( access_level_check_greater_or_equal( $g_bug_move_access_level ) ) { ?>
<tr>
	<td colspan="9">

		<!--
		<select name="f_project_id">
		<?php print_project_option_list() ?>
		</select>
		-->

		<select name="f_action">
		<?php print_all_bug_action_option_list() ?>
		</select>
		<input type="submit" value="<?php echo 'OK';  ?>" >
	</td>
</tr>
<?php } ?>
<?php mark_time( 'end loop' ); ?>
</table>

<?php # Show NEXT and PREV links as needed ?>
<p>
<div align="center">
<?php
	# print the [ prev ] link
	if ($f_page_number > 1) {
		$t_prev_page_number = $f_page_number - 1;
		print_bracket_link( 'view_all_bug_page.php?f_page_number='.$t_prev_page_number, $s_view_prev_link.' '.$f_per_page );
	} else {
		print_bracket_link( '', $s_view_prev_link.' '.$f_per_page );
	}

	# print the [ next ] link
	if ($f_page_number < $t_page_count) {
		$t_next_page_number = $f_page_number + 1;
		print_bracket_link( 'view_all_bug_page.php?f_page_number='.$t_next_page_number, $s_view_next_link.' '.$f_per_page );
	} else {
		print_bracket_link( '', $s_view_next_link.' '.$f_per_page );
	}
?>
</div>

<?php
	if ( STATUS_LEGEND_POSITION_BOTTOM == $g_status_legend_position ) {
		print_status_colors();
	}
?>
