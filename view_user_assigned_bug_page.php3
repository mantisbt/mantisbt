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

	if ( isset( $f_save )) {
		### Save preferences
		$t_settings_string = $f_assign_id."#".
							$f_show_category."#".
							$f_show_severity."#".
							$f_show_status."#".
							$f_limit_view."#".
							$f_show_changed."#".
							$f_hide_resolved;
		setcookie( $g_view_assigned_cookie, $t_settings_string, time()+$g_cookie_time_length );
	}
	else if ( strlen($g_view_assigned_cookie_val)>6 ) {
		### Load preferences
		$t_setting_arr = explode( "#", $g_view_assigned_cookie_val );
		$f_assign_id = $t_setting_arr[0];
		$f_show_category = $t_setting_arr[1];
		$f_show_severity = $t_setting_arr[2];
		$f_show_status = $t_setting_arr[3];
		$f_limit_view = $t_setting_arr[4];
		$f_show_changed = $t_setting_arr[5];
		$f_hide_resolved = $t_setting_arr[6];
	}

	if ( !isset( $f_assign_id ) ) {
		$f_assign_id = get_current_user_field( "id" );
	}

	if ( !isset( $f_limit_view ) ) {
		$f_limit_view = $g_default_limit_view;
	}

	if ( !isset( $f_show_changed ) ) {
		$f_show_changed = $g_default_show_changed;
	}

	if ( !isset( $f_show_category ) ) {
		$f_show_category = "any";
	}

	if ( !isset( $f_show_severity ) ) {
		$f_show_severity = "any";
	}

	if ( !isset( $f_show_status ) ) {
		$f_show_status = "any";
	}

	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
	}

	### basically we toggle between ASC and DESC if the user clicks the
	### same sort order
	if ( isset( $f_dir ) ) {
		if ( $f_dir=="ASC" ) {
			$f_dir = "DESC";
		}
		else {
			$f_dir = "ASC";
		}
	}
	else {
		$f_dir = "DESC";
	}

	### build our query string based on our viewing criteria
	$query = "SELECT * FROM $g_mantis_bug_table";

	$t_where_clause = " WHERE project_id='$g_project_cookie_val' AND
							handler_id='$f_assign_id'";

	if (( $f_hide_resolved=="on"  )&&( $f_show_status!="resolved" )) {
		$t_where_clause = $t_where_clause." AND status<>'resolved'";
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

	$query = $query.$t_where_clause;

	if ( !isset( $f_sort ) ) {
			$f_sort="id";
	}
	$query = $query." ORDER BY '$f_sort' $f_dir";
	if ( isset( $f_limit_view ) ) {
		$query = $query." LIMIT $f_offset, $f_limit_view";
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
	[ <a href="<? echo $g_view_bug_all_page ?>"><? echo $s_all_bugs_link ?></a> ]
	[ <a href="<? echo $g_view_user_reported_bug_page ?>"><? echo $s_reported_bugs_link ?></a> ]
	[ <? echo $s_assigned_bugs_link ?> ]
</div>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<tr>
		<form method=post action="<? echo $g_view_user_assigned_bug_page ?>">
		<input type=hidden name=f_offset value="0">
		<input type=hidden name=f_save value="1">
		<td align=center>
		<select name=f_assign_id>
			<? print_handler_option_list( $f_assign_id ) ?>
		</select>
		<select name=f_show_category>
			<option value="any"><? echo $s_any ?>
			<option value="any">
			<? print_category_option_list() ?>
		</select>
		<select name=f_show_severity>
			<option value="any"><? echo $s_any ?>
			<option value="any">
			<? print_field_option_list( "severity", $f_show_severity ) ?>
		</select>
		<select name=f_show_status>
			<option value="any"><? echo $s_any ?>
			<option value="any">
			<? print_field_option_list( "status", $f_show_status ) ?>
		</select>
		<? echo $s_show ?>: <input type=text name=f_limit_view size=3 maxlength=7 value="<? echo $f_limit_view ?>">
		<? echo $s_changed ?>: <input type=text name=f_show_changed size=3 maxlength=7 value="<? echo $f_show_changed ?>">
		<? echo $s_hide_resolved ?>: <input type=checkbox name=f_hide_resolved <? if ($f_hide_resolved=="on") echo "CHECKED"?>>
		<input type=submit value="<? echo $s_filter_button ?>">
		</td>
		</form>
	</tr>
	</table>
</tr>
</table>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<tr>
		<td colspan=7 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_viewing_bugs_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color2 ?> align=center>
		<td width=8%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=id&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>"><? echo $s_id ?></a>
			</b>
		</td>
		<td width=3%>
			<b>
				#
			</b>
		</td>
		<td width=12%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=category&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>"><? echo $s_category ?></a>
			</b>
		</td>
		<td width=10%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=severity&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>"><? echo $s_severity ?></a>
			</b>
		</td>
		<td width=10%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=status&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>"><? echo $s_status ?></a>
			</b>
		</td>
		<td width=12%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=last_updated&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>"><? echo $s_updated ?></a>
			</b>
		</td>
		<td width=45%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=summary&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>"><? echo $s_summary ?></a>
			</b>
		</td>
	</tr>
	<tr height=5>
		<td bgcolor=<? echo $g_white_color ?> colspan=7>
		</td>
	</tr>
	<?
		### perform query
	    $result = db_query( $query );
		$row_count = db_num_rows( $result );

		for($i=0; $i < $row_count; $i++) {
			### prefix bug data with v_
			$row = db_fetch_array($result);
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$v_summary = string_display( $v_summary );
			$t_last_updated = date( $g_short_date_format, sql_to_unix_time( $v_last_updated ) );

			### alternate row colors
			if ($i % 2== 0) {
				$status_color=$g_primary_color_light;
			}
			else {
				$status_color=$g_primary_color_dark;
			}

			### choose color based on status only if not resolved
			### The code creates the appropriate variable name
			### then references that color variable
			### You could replace this with a bunch of if... then... else
			### statements
			if ( $v_status!="resolved" ) {
				$t_color_variable_name = "g_".$v_status."_color";
				$status_color = $$t_color_variable_name;
			}

			### grab the bugnote count
			$bugnote_count = get_bugnote_count( $v_id );

			$query = "SELECT MAX(last_modified)
					FROM $g_mantis_bugnote_table
					WHERE bug_id='$v_id'";
			$res2 = db_query( $query );
			$v_bugnote_updated = db_result( $res2, 0, 0 );
	?>
	<tr bgcolor=<? echo $status_color ?> align=center>
		<td>
			<? print_bug_link( $v_id ) ?>
		</td>
		<td>
			<?
				if ($bugnote_count > 0){
					if ( sql_to_unix_time( $v_bugnote_updated ) >
						strtotime( "-$f_show_changed hours" ) ) {
						PRINT "<b>$bugnote_count</b>";
					}
					else {
						PRINT "$bugnote_count";
					}
				}
				else {
					echo "&nbsp;";
				}
			?>
		</td>
		<td>
			<? echo $v_category ?>
		</td>
		<td>
			<? print_formatted_severity( $v_status, $v_severity ) ?>
		</td>
		<td>
			<? echo $v_status ?>
		</td>
		<td>
			<?
				if ( sql_to_unix_time( $v_last_updated ) >
					strtotime( "-$f_show_changed hours" ) ) {

					PRINT "<b>$t_last_updated</b>";
				}
				else {
					PRINT "$t_last_updated";
				}
			?>
		</td>
		<td align=left>
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

<div align=center>
<?
	$f_offset_next = $f_offset + $f_limit_view;
	$f_offset_prev = $f_offset - $f_limit_view;

	if ( $f_offset_prev < 0 ) {
		$f_offset_prev = -1;
	}

	if ( $f_offset_prev >= 0 ) {
 		PRINT "[ <a href=\"$g_view_user_assigned_bug_page?f_offset=$f_offset_prev&f_show_category=$f_show_category&f_show_severity=$f_show_severity&f_show_status=$f_show_status&f_limit_view=$f_limit_view&f_show_changed=$f_show_changed&f_hide_resolved=$f_hide_resolved&f_assign_id=$f_assign_id\">View Prev $f_limit_view</a> ] ";
	}
	if ( $row_count == $f_limit_view ) {
 		PRINT "[ <a href=\"$g_view_user_assigned_bug_page?f_offset=$f_offset_next&f_show_category=$f_show_category&f_show_severity=$f_show_severity&f_show_status=$f_show_status&f_limit_view=$f_limit_view&f_show_changed=$f_show_changed&f_hide_resolved=$f_hide_resolved&f_assign_id=$f_assign_id\">View Next $f_limit_view</a> ]";
	}
?>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>