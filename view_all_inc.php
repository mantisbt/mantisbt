<p>
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<form method="post" action="<? echo $link_page ?>">
	<input type="hidden" name="f_offset" value="0">
	<input type="hidden" name="f_save" value="1">
	<input type="hidden" name="f_sort" value="<? echo $f_sort ?>">
	<input type="hidden" name="f_dir" value="<? echo $f_dir ?>">
	<table width="100%">
	<tr align="center">
        <td>
            <span class="smallcaption"><? echo $s_search; ?></span>
        </td>
		<td>
			<span class="smallcaption"><? echo $s_reporter ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_assigned_to ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_category ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_severity ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_status ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_show ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_changed ?></span>
		</td>
		<td>
			<span class="smallcaption"><? echo $s_hide_closed ?></span>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr align="center">
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
				<option value="none" <? if ( $f_assign_id=="none" ) echo "SELECTED" ?>><? echo $s_none ?></option>
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
			<input type="text" name="f_limit_view" size="3" maxlength="7" value="<? echo $f_limit_view ?>">
		</td>
		<td>
			<input type="text" name="f_highlight_changed" size="3" maxlength="7" value="<? echo $f_highlight_changed ?>">
		</td>
		<td>
			<input type="checkbox" name="f_hide_closed" <? if ($f_hide_closed=="on") echo "CHECKED"?>>
		</td>
		<td>
			<input type="submit" value="<? echo $s_filter_button ?>">
		</td>
	</tr>
	</table>
	</form>
	</td>
</tr>
</table>

<p>
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="7" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_viewing_bugs_title ?></b>
			<?
				if ( $row_count > 0 ) {
					$v_start = $f_offset+1;
					$v_end   = $f_offset+$row_count;
				} else {
					$v_start = 0;
					$v_end   = 0;
				}
				PRINT "($v_start - $v_end)";
			?>
		</td>
	</tr>
	<tr align="center" bgcolor="<? echo $g_category_title_color2 ?>">
		<td width="2%">
			&nbsp;
		</td>
		<td width="8%">
			<? print_view_bug_sort_link( $link_page, $s_id, "id", $f_sort, $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "id" ) ?>
		</td>
		<td width="3%">
			<b>
				#
			</b>
		</td>
		<td width="12%">
			<? print_view_bug_sort_link( $link_page, $s_category, "category", $f_sort, $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "category" ) ?>
		</td>
		<td width="10%">
			<? print_view_bug_sort_link( $link_page, $s_severity, "severity", $f_sort, $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "severity" ) ?>
		</td>
		<td width="10%">
			<? print_view_bug_sort_link( $link_page, $s_status, "status", $f_sort, $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "status" ) ?>
		</td>
		<td width="12%">
			<? print_view_bug_sort_link( $link_page, $s_updated, "last_updated", $f_sort, $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "last_updated" ) ?>
		</td>
		<td width="43%">
			<? print_view_bug_sort_link( $link_page, $s_summary, "summary", $f_sort, $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "summary" ) ?>
		</td>
	</tr>
	<tr height="5">
		<td colspan="7" bgcolor="<? echo $g_white_color ?>">
		</td>
	</tr>
	<?
		for($i=0; $i < $row_count; $i++) {
			# prefix bug data with v_
			$row = db_fetch_array($result);
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$v_summary = string_display( $v_summary );
			$t_last_updated = date( $g_short_date_format, sql_to_unix_time( $v_last_updated ) );

			# alternate row colors
			$status_color = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );

			# choose color based on status only if not resolved
			# The code creates the appropriate variable name
			# then references that color variable
			# You could replace this with a bunch of if... then... else
			# statements
			if ( !( $v_status==CLOSED ) ) {
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
	<tr align="center" bgcolor="<? echo $status_color ?>">
		<td bgcolor="<? echo $g_white_color ?>">
			<? print_status_icon( $v_priority ) ?>
		</td>
		<td>
			<? print_bug_link( $v_id ) ?>
		</td>
		<td>
			<?
				if ($bugnote_count > 0){
					if ( sql_to_unix_time( $v_bugnote_updated ) >
						strtotime( "-$f_highlight_changed hours" ) ) {
						PRINT "<b>$bugnote_count</b>";
					} else {
						PRINT "$bugnote_count";
					}
				} else {
					echo "&nbsp;";
				}
			?>
		</td>
		<td>
			<? echo $v_category ?>
		</td>
		<td>
			<? print_formatted_severity_string( $v_status, $v_severity ) ?>
		</td>
		<td>
			<?
				# print username instead of status
				if (( $g_show_assigned_names==1 )&&( $v_handler_id > 0 )&&
					( $v_status!=CLOSED )&&( $v_status!=RESOLVED )) {
					echo "(".get_user_info( $v_handler_id, "username" ).")";
				} else {
					#echo get_enum_element( $g_status_enum_string, $v_status );
					echo get_enum_element( $s_status_enum_string, $v_status );
				}
			?>
		</td>
		<td>
			<?
				if ( sql_to_unix_time( $v_last_updated ) >
					strtotime( "-$f_highlight_changed hours" ) ) {

					PRINT "<b>$t_last_updated</b>";
				} else {
					PRINT "$t_last_updated";
				}
			?>
		</td>
		<td align="left">
			<? echo $v_summary ?>
		</td>
	</tr>
	<?
		}
	?>
	</table>
	</td>
</tr>
</table>

<? # Show NEXT and PREV links as needed ?>
<p>
<div align="center">
<?
	$f_offset_next = $f_offset + $f_limit_view;
	$f_offset_prev = $f_offset - $f_limit_view;

	if ( $f_offset_prev < 0 ) {
		$f_offset_prev = -1;
	}

	if ( $f_dir=="DESC" ) {
		$f_dir = "ASC";
	} else {
		$f_dir = "DESC";
	}


	if ( $f_offset_prev >= 0 ) {
		#print_bracket_link( $link_page."?f_sort=".$f_sort."&f_dir=".$f_dir."&f_offset=".$f_offset_prev, $s_view_prev_link." ".$f_limit_view );
		print_bracket_link( $link_page."?&f_offset=".$f_offset_prev, $s_view_prev_link." ".$f_limit_view );
	}
	if ( $row_count == $f_limit_view ) {
		#print_bracket_link( $link_page."?f_sort=".$f_sort."&f_dir=".$f_dir."&f_offset=".$f_offset_next, $s_view_next_link." ".$f_limit_view );
		print_bracket_link( $link_page."?f_offset=".$f_offset_next, $s_view_next_link." ".$f_limit_view );
	}
?>
</div>