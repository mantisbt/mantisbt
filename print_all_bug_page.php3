<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# check to see if the cookie does not exist
	if ( empty( $g_view_all_cookie_val ) ) {
		$t_settings_string = "v1#any#any#any#".$g_default_limit_view."#".
							$g_default_show_changed."#0#any#any#last_updated#DESC";
		setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		print_header_redirect( $g_print_all_bug_page."?f=2" );
	}

	# Check to see if new cookie is needed
	$t_setting_arr 			= explode( "#", $g_view_all_cookie_val );
	if ( $t_setting_arr[0] != "v1" ) {
		$t_settings_string = "v1#any#any#any#".$g_default_limit_view."#".
							$g_default_show_changed."#0#any#any#last_updated#DESC";
		setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		print_header_redirect( $g_print_all_bug_page."?f=1" );
	}

	if( !isset( $f_search_text ) ) {
		$f_search_text = false;
	}

	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
	}

	if ( !isset( $f_hide_closed ) ) {
		$f_hide_closed = "";
	}

	if ( isset( $f_save ) ) {
		if ( $f_save == 1 ) {
			# We came here via the FILTER form button click
			# Save preferences
			$t_settings_string = "v1#".
								$f_show_category."#".
								$f_show_severity."#".
								$f_show_status."#".
								$f_limit_view."#".
								$f_highlight_changed."#".
								$f_hide_closed."#".
								$f_user_id."#".
								$f_assign_id."#".
								$f_sort."#".
								$f_dir;
			setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		} else if ( $f_save == 2 ) {
			# We came here via clicking a sort link
			# Load pre-existing preferences
			$t_setting_arr 			= explode( "#", $g_view_all_cookie_val );
			$f_show_category 		= $t_setting_arr[1];
			$f_show_severity	 	= $t_setting_arr[2];
			$f_show_status 			= $t_setting_arr[3];
			$f_limit_view 			= $t_setting_arr[4];
			$f_highlight_changed 	= $t_setting_arr[5];
			$f_hide_closed 			= $t_setting_arr[6];
			$f_user_id 				= $t_setting_arr[7];
			$f_assign_id 			= $t_setting_arr[8];

			if ( !isset( $f_sort ) ) {
				$f_sort		 			= $t_setting_arr[9];
			}
			if ( !isset( $f_dir ) ) {
				$f_dir		 			= $t_setting_arr[10];
			}
			# Save new preferences
			$t_settings_string = "v1#".
								$f_show_category."#".
								$f_show_severity."#".
								$f_show_status."#".
								$f_limit_view."#".
								$f_highlight_changed."#".
								$f_hide_closed."#".
								$f_user_id."#".
								$f_assign_id."#".
								$f_sort."#".
								$f_dir;

			setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		}
	} else {
		# Load preferences
		$t_setting_arr 			= explode( "#", $g_view_all_cookie_val );
		$f_show_category 		= $t_setting_arr[1];
		$f_show_severity	 	= $t_setting_arr[2];
		$f_show_status 			= $t_setting_arr[3];
		$f_limit_view 			= $t_setting_arr[4];
		$f_highlight_changed 	= $t_setting_arr[5];
		$f_hide_closed 			= $t_setting_arr[6];
		$f_user_id 				= $t_setting_arr[7];
		$f_assign_id 			= $t_setting_arr[8];
		$f_sort 				= $t_setting_arr[9];
		$f_dir		 			= $t_setting_arr[10];
	}

	# Build our query string based on our viewing criteria

	$query = "SELECT * FROM $g_mantis_bug_table";

	$t_where_clause = " WHERE project_id='$g_project_cookie_val'";

	if ( $f_user_id != "any" ) {
		$t_where_clause .= " AND reporter_id='$f_user_id'";
	}

	if ( $f_assign_id == "none" ) {
		$t_where_clause .= " AND handler_id=0";
	} else if ( $f_assign_id != "any" ) {
		$t_where_clause .= " AND handler_id='$f_assign_id'";
	}

	$t_clo_val = CLOSED;
	if (( $f_hide_closed=="on"  )&&( $f_show_status!="closed" )) {
		$t_where_clause = $t_where_clause." AND status<>'$t_clo_val'";
	}

	if ( $f_show_category != "any" ) {
		$t_where_clause = $t_where_clause." AND category='$f_show_category'";
	}
	if ( $f_show_severity != "any" ) {
		$t_where_clause = $t_where_clause." AND severity='$f_show_severity'";
	}
	if ( $f_show_status != "any" ) {
		$t_where_clause = $t_where_clause." AND status='$f_show_status'";
	}

	# Simple Text Search - Thnaks to Alan Knowles
	if ($f_search_text) {
		$t_where_clause .= " AND ((summary LIKE '%".addslashes($f_search_text)."%')
							OR (description LIKE '%".addslashes($f_search_text)."%')
							OR (steps_to_reproduce LIKE '%".addslashes($f_search_text)."%')
							OR (additional_information LIKE '%".addslashes($f_search_text)."%')
							OR ($g_mantis_bug_table.id LIKE '%".addslashes($f_search_text)."%'))
							AND $g_mantis_bug_text_table.id = $g_mantis_bug_table.bug_text_id";
		$query = "SELECT $g_mantis_bug_table.*, $g_mantis_bug_text_table.description
				FROM $g_mantis_bug_table, $g_mantis_bug_text_table ".$t_where_clause;
	} else {
		$query = $query.$t_where_clause;
	}

	if ( !isset( $f_sort ) ) {
		$f_sort="last_updated";
	}
	$query = $query." ORDER BY '$f_sort' $f_dir";
	if ( $f_sort != "priority" ) {
		$query = $query.", priority DESC";
	}

	if ( isset( $f_limit_view ) ) {
		$query = $query." LIMIT $f_offset, $f_limit_view";
	}

	# perform query
    $result = db_query( $query );
	$row_count = db_num_rows( $result );

	$link_page = $g_print_all_bug_page;
	$page_type = "all";
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<?
	if ( get_current_user_pref_field( "refresh_delay" ) > 0 ) {
		print_meta_redirect( $PHP_SELF."?f_offset=".$f_offset, get_current_user_pref_field( "refresh_delay" )*60 );
	}
?>
<? print_head_bottom() ?>
<? print_body_top() ?>

<form method="post" action="<? echo $link_page ?>?f=3">
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

<table width="100%" cellpadding="0">
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
	<td align="right">
		<a href="<? echo $g_summary_page ?>">Back to Summary</a>
	</td>
<p>
</tr>
<tr align="center" bgcolor="<? echo $g_category_title_color2 ?>">
	<td width="8%">
		<? print_view_bug_sort_link( $link_page, "P", "priority", $f_sort, $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "priority" ) ?>
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
	<td width="37%">
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
		$status_color = alternate_colors( $i, "#ffffff", $g_primary_color_light );

		# grab the bugnote count
		$bugnote_count = get_bugnote_count( $v_id );

		$query = "SELECT MAX(last_modified)
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$v_id'";
		$res2 = db_query( $query );
		$v_bugnote_updated = db_result( $res2, 0, 0 );
?>
<tr align="center" bgcolor="<? echo $status_color ?>">
	<td>
		<? echo get_enum_element($s_priority_enum_string, $v_priority) ?>
	</td>
	<td>
		<? echo $v_id ?>
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
				echo get_enum_element( $s_status_enum_string, $v_status );
			}
		?>
	</td>
	<td>
		<i>
		<?
			if ( sql_to_unix_time( $v_last_updated ) >
				strtotime( "-$f_highlight_changed hours" ) ) {

				PRINT "<b>$t_last_updated</b>";
			} else {
				PRINT "$t_last_updated";
			}
		?>
		</i>
	</td>
	<td align="left">
		<? echo $v_summary ?>
	</td>
</tr>
<?
	}
?>
</table>

<? print_body_bottom() ?>
<? print_html_bottom() ?>
