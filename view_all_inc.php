<p>
<table class="width100" cellspacing="0">
<form method="post" action="<? echo $link_page ?>?f=3">
<input type="hidden" name="f_offset" value="0">
<input type="hidden" name="f_save" value="1">
<input type="hidden" name="f_sort" value="<? echo $f_sort ?>">
<input type="hidden" name="f_dir" value="<? echo $f_dir ?>">
<input type="hidden" name="f_page_number" value="<? echo $f_page_number ?>">
<input type="hidden" name="f_per_page" value="<? echo $f_per_page ?>">
<tr>
    <td class="small-caption">
        <? echo $s_search ?>
    </td>
    <td class="small-caption">
		<? echo $s_reporter ?>
	</td>
    <td class="small-caption">
		<? echo $s_assigned_to ?>
	</td>
    <td class="small-caption">
		<? echo $s_category ?>
	</td>
    <td class="small-caption">
		<? echo $s_severity ?>
	</td>
    <td class="small-caption">
		<? echo $s_status ?>
	</td>
    <td class="small-caption">
		<? echo $s_show ?>
	</td>
    <td class="small-caption">
		<? echo $s_changed ?>
	</td>
    <td class="small-caption">
		<? echo $s_hide_closed ?>
	</td>
    <td class="small-caption">
		&nbsp;
	</td>
</tr>
<tr>
	<td>
	    <input type="text" name="f_search_text" value="<? echo $f_search_text; ?>">
	</td>
	<td>
		<select name="f_user_id">
			<option value="any"><? echo $s_any ?></option>
			<option value="any"></option>
			<? print_reporter_option_list( $f_user_id ) ?>
		</select>
	</td>
	<td>
		<select name="f_assign_id">
			<option value="any"><? echo $s_any ?></option>
			<option value="none" <? if ( "none" == $f_assign_id ) echo "SELECTED" ?>><? echo $s_none ?></option>
			<option value="any"></option>
			<? print_assign_to_option_list( $f_assign_id ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_category">
			<option value="any"><? echo $s_any ?></option>
			<option value="any"></option>
			<? print_category_option_list( $f_show_category ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_severity">
			<option value="any"><? echo $s_any ?></option>
			<option value="any"></option>
			<? print_enum_string_option_list( $s_severity_enum_string, $f_show_severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_status">
			<option value="any"><? echo $s_any ?></option>
			<option value="any"></option>
			<? print_enum_string_option_list( $s_status_enum_string, $f_show_status ) ?>
		</select>
	</td>
	<td>
		<input type="text" name="f_per_page" size="3" maxlength="7" value="<? echo $f_per_page ?>">
	</td>
	<td>
		<input type="text" name="f_highlight_changed" size="3" maxlength="7" value="<? echo $f_highlight_changed ?>">
	</td>
	<td>
		<input type="checkbox" name="f_hide_closed" <? if ( "on" == $f_hide_closed ) echo "CHECKED"?>>
	</td>
<!--
		<td>
		<input type="checkbox" name="f_export_csv">
	</td>-->
	<td>
		<input type="submit" value="<? echo $s_filter_button ?>">
	</td>
</tr>
</form>
</table>

<p>
<form method="post" action="<? echo $g_view_all_bug_update ?>">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="8">
		<? echo $s_viewing_bugs_title ?>
		<?
			if ( $row_count > 0 ) {
				$v_start = $t_offset+1;
				$v_end   = $t_offset+$row_count;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
		?>
		(<?= $v_start ?> - <?= $v_end ?> / <?= $t_query_count ?>)
	</td>
	<td class="right">
		[
		<?
			# print out a link for each page i.e.
			#     [ 1 2 3 ]
			#
			for ( $i = 0; $i < $t_page_count; $i++ ) {
				if ( $i == $f_page_number ) {
					echo $i;
				} else {
		?>
				<a href="<?= $g_view_all_bug_page ?>?f_page_number=<?= $i ?>"><?= $i ?></a>
		<?
				}
			}
		?>
		]
	</td>
</tr>
<tr class="row-category">
	<td class="center" width="2%">
		&nbsp;
	</td>
	<td class="center" width="5%">
		<? print_view_bug_sort_link( $link_page, "P", "priority", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "priority" ) ?>
	</td>
	<td class="center" width="8%">
		<? print_view_bug_sort_link( $link_page, $s_id, "id", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "id" ) ?>
	</td>
	<td class="center" width="3%">
		#
	</td>
	<td class="center" width="12%">
		<? print_view_bug_sort_link( $link_page, $s_category, "category", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "category" ) ?>
	</td>
	<td class="center" width="10%">
		<? print_view_bug_sort_link( $link_page, $s_severity, "severity", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "severity" ) ?>
	</td>
	<td class="center" width="10%">
		<? print_view_bug_sort_link( $link_page, $s_status, "status", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "status" ) ?>
	</td>
	<td class="center" width="12%">
		<? print_view_bug_sort_link( $link_page, $s_updated, "last_updated", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "last_updated" ) ?>
	</td>
	<td class="center" width="38%">
		<? print_view_bug_sort_link( $link_page, $s_summary, "summary", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "summary" ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="9">
		&nbsp;
	</td>
</tr>
<?
	for($i=0; $i < $row_count; $i++) {
		# prefix bug data with v_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "v" );

		$v_summary = string_display( $v_summary );
		$t_last_updated = date( $g_short_date_format, $v_last_updated );

		# alternate row colors
		$status_color = alternate_colors( $i, $g_primary_color1, $g_primary_color2 );

		# choose color based on status only if not resolved
		# The code creates the appropriate variable name
		# then references that color variable
		# You could replace this with a bunch of if... then... else
		# statements
		if ( !( CLOSED == $v_status ) ) {
			$t_color_str = get_enum_element( $g_status_enum_string, $v_status );
			$t_color_variable_name = "g_".$t_color_str."_color";
			$status_color = $$t_color_variable_name;
		}

		# grab the bugnote count
		$bugnote_count = get_bugnote_count( $v_id );

		$query = "SELECT MAX(last_modified)
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$v_id'";
		$res2 = db_query( $query );
		$v_bugnote_updated = db_result( $res2, 0, 0 );
?>
<tr bgcolor="<? echo $status_color ?>">
	<td>
		<?	if ( access_level_check_greater_or_equal( UPDATER ) ) { ?>
			<input type="checkbox" name="f_bug_arr[]" value="<? echo $v_id ?>">
		<?	} else { ?>
			&nbsp;
		<?	} ?>
	</td>
	<td class="center">
		<?
			if ( ON == $g_show_priority_text ) {
				echo get_enum_element($s_priority_enum_string, $v_priority);
			} else {
				print_status_icon( $v_priority );
			}
		?>
	</td>
	<td class="center">
		<? print_bug_link( $v_id ) ?>
	</td>
	<td class="center">
		<?
			if ($bugnote_count > 0){
				if ( $v_bugnote_updated >
					strtotime( "-$f_highlight_changed hours" ) ) {
					PRINT "<span class=\"bold\">$bugnote_count</span>";
				} else {
					PRINT "$bugnote_count";
				}
			} else {
				echo "&nbsp;";
			}
		?>
	</td>
	<td class="center">
		<? echo $v_category ?>
	</td>
	<td class="center">
		<? print_formatted_severity_string( $v_status, $v_severity ) ?>
	</td>
	<td class="center">
		<?
			# print username instead of status
			if ( ( ON == $g_show_assigned_names )&&( $v_handler_id > 0 ) ) {
				echo "(".get_user_info( $v_handler_id, "username" ).")";
			} else {
				echo get_enum_element( $s_status_enum_string, $v_status );
			}
		?>
	</td>
	<td class="center">
		<?
			if ( $v_last_updated >
				strtotime( "-$f_highlight_changed hours" ) ) {

				PRINT "<span class=\"bold\">$t_last_updated</span>";
			} else {
				PRINT "$t_last_updated";
			}
		?>
	</td>
	<td class="left">
		<? echo $v_summary ?>
	</td>
</tr>
<?
	}
?>
</table>
<select name="f_project_id">
<? print_project_option_list() ?>
</select>
<input type="submit" value="<? echo $s_move_bugs ?>">
</form>

<? # Show NEXT and PREV links as needed ?>
<p>
<div align="center">
<?
	# print the [ prev ] link
	if ($f_page_number > 1) {
		$t_prev_page_number = $f_page_number - 1;
		print_bracket_link( $link_page."?f_page_number=".$t_prev_page_number, $s_view_prev_link." ".$f_per_page );
	} else {
		print_bracket_link( "", $s_view_prev_link." ".$f_per_page );
	}

	# print the [ next ] link
	if ($f_page_number < $t_page_count) {
		$t_next_page_number = $f_page_number + 1;
		print_bracket_link( $link_page."?f_page_number=".$t_next_page_number, $s_view_next_link." ".$f_per_page );
	} else {
		print_bracket_link( "", $s_view_next_link." ".$f_per_page );
	}
?>
</div>

<? # print a legend for the status color coding ?>
<p>
<table class="width100" cellspacing="0">
<tr>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_new_color?>"><? echo get_enum_element( $s_status_enum_string, NEW_ ) ?></td>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_feedback_color?>"><? echo get_enum_element( $s_status_enum_string, FEEDBACK ) ?></td>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_acknowledged_color?>"><? echo get_enum_element( $s_status_enum_string, ACKNOWLEDGED ) ?></td>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_confirmed_color?>"><? echo get_enum_element( $s_status_enum_string, CONFIRMED ) ?></td>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_assigned_color?>"><? echo get_enum_element( $s_status_enum_string, ASSIGNED ) ?></td>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_resolved_color?>"><? echo get_enum_element( $s_status_enum_string, RESOLVED ) ?></td>
	<td class="small-caption" width="10%" bgcolor="<? echo $g_closed_color?>"><? echo get_enum_element( $s_status_enum_string, CLOSED ) ?></td>
</tr>
</table>