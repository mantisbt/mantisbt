<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

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

	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
	}
	### build our query string based on our viewing criteria
	$query = "SELECT * FROM $g_mantis_bug_table";

	$t_where_clause = "";

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

	if ( !empty( $t_where_clause ) ) {
		$t_where_clause = substr( $t_where_clause, 5, strlen( $t_where_clause ) );
		$t_where_clause = " WHERE ".$t_where_clause;
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
	[ <a href="<? echo $g_view_user_reported_bug_page ?>">Reported Bugs</a> ]
	[ <a href="<? echo $g_view_user_assigned_bug_page ?>">Assigned Bugs</a> ]
</div>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=7>
	<tr>
		<form method=post action="<? echo $g_view_bug_all_page ?>">
		<input type=hidden name=f_offset value="0">
		<td align=center>
		<select name=f_show_category>
			<option value="any">any
			<option value="any">
			<? print_field_option_list( "category", $f_show_category ) ?>
		</select>
		<select name=f_show_severity>
			<option value="any">any
			<option value="any">
			<? print_field_option_list( "severity", $f_show_severity ) ?>
		</select>
		<select name=f_show_status>
			<option value="any">any
			<option value="any">
			<? print_field_option_list( "status", $f_show_status ) ?>
		</select>
		Show: <input type=text name=f_limit_view size=3 maxlength=7 value="<? echo $f_limit_view ?>">
		Changed(hrs): <input type=text name=f_show_changed size=3 maxlength=7 value="<? echo $f_show_changed ?>">
		Hide Resolved: <input type=checkbox name=f_hide_resolved <? if ($f_hide_resolved=="on") echo "CHECKED"?>>
		<input type=submit value=" Filter ">
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
	<table width=100% cols=7>
	<tr>
		<td colspan=7 bgcolor=<? echo $g_table_title_color ?>>
			<b>Viewing Bugs</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color2 ?> align=center>
		<td width=8%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=id&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>">ID</a>
			</b>
		</td>
		<td width=3%>
			<b>
				#
			</b>
		</td>
		<td width=12%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=category&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>">Category</a>
			</b>
		</td>
		<td width=10%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=severity&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>">Severity</a>
			</b>
		</td>
		<td width=10%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=status&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>">Status</a>
			</b>
		</td>
		<td width=12%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=last_updated&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>">Updated</a>
			</b>
		</td>
		<td width=45%>
			<b>
				<a href="<? echo $g_view_bug_all_page ?>?f_sort=summary&f_dir=<? echo $f_dir?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>">Summary</a>
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
		$row_count = mysql_num_rows( $result );

		for($i=0; $i < $row_count; $i++) {
			### prefix bug data with v_
			$row = mysql_fetch_array($result);
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$v_summary = string_display( $v_summary );
			$t_last_updated = date( "m-d", sql_to_unix_time( $v_last_updated ) );

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
	?>
	<tr bgcolor=<? echo $status_color ?> align=center>
		<td>
			<? print_bug_link( $v_id ) ?>
		</td>
		<td>
			<? if ($bugnote_count > 0) echo $bugnote_count ?>
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
		<td>
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

<?
	$f_offset_next = $f_offset + $f_limit_view;
	$f_offset_prev = $f_offset - $f_limit_view;

	if ( $f_offset_prev < 0 ) {
		$f_offset_prev = -1;
	}
?>

<div align=center>
<? if ( $f_offset_prev >= 0 ) { ?>
 [ <a href="<? echo $g_view_bug_all_page ?>?f_offset=<? echo $f_offset_prev ?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>&f_hide_resolved=<? echo $f_hide_resolved ?>">View Prev <? echo $f_limit_view ?></a> ]
<? } ?>
<? if ( $row_count == $f_limit_view ) { ?>
 [ <a href="<? echo $g_view_bug_all_page ?>?f_offset=<? echo $f_offset_next ?>&f_show_category=<? echo $f_show_category ?>&f_show_severity=<? echo $f_show_severity ?>&f_show_status=<? echo $f_show_status ?>&f_limit_view=<? echo $f_limit_view ?>&f_show_changed=<? echo $f_show_changed ?>&f_hide_resolved=<? echo $f_hide_resolved ?>">View Next <? echo $f_limit_view ?></a> ]
<? } ?>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>