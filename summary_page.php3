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

	$t_res_val = RESOLVED;
	$query = "SELECT id, UNIX_TIMESTAMP(date_submitted) as date_submitted, last_updated
			FROM $g_mantis_bug_table
			WHERE project_id='$g_project_cookie_val' AND status='$t_res_val'";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$t_bug_id = 0;
	$t_largest_diff = 0;
	$t_total_time = 0;
	for ($i=0;$i<$bug_count;$i++) {
		$row = db_fetch_array( $result );
		$t_date_submitted = ($row["date_submitted"]);
		$t_last_updated = sql_to_unix_time($row["last_updated"]);

		if ($t_last_updated < $t_date_submitted) {
			$t_last_updated = 0;
			$t_date_submitted = 0;
		}

		$t_diff = $t_last_updated - $t_date_submitted;
		$t_total_time = $t_total_time + $t_diff;
		if ( $t_diff > $t_largest_diff ) {
			$t_largest_diff = $t_diff;
			$t_bug_id = $row["id"];
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
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<p>
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%" cols="2">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_summary_title ?></b>
		</td>
	</tr>
	<tr valign="bottom" height="28" bgcolor="<? echo $g_white_color ?>">
		<td width="50%">
			<b><? echo $s_by_status ?>:</b>
		</td>
		<td width="50%">
			<b><? echo $s_by_date ?>:</b>
		</td>
	</tr>
	<tr align="center" valign="top" height="28" bgcolor="<? echo $g_white_color ?>">
		<td width="50%">
			<? ### STATUS ### ?>
			<table width="97%">
				<? print_bug_enum_summary( $g_status_enum_string, "status" ) ?>
			</table>
		</td>
		<td width="50%">
			<? ### DATE ### ?>
			<table width="97%">
			<? print_bug_date_summary( $g_date_partitions ) ?>
			</table>
		</td>
	</tr>
	<tr valign="bottom" height="28" bgcolor="<? echo $g_white_color ?>">
		<td width="50%">
			<b><? echo $s_by_severity ?>:</b>
		</td>
		<td width="50%">
			<b><? echo $s_by_resolution ?>:</b>
		</td>
	</tr>
	<tr align="center" valign="top" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<? ### SEVERITY ### ?>
			<table width="97%">
				<? print_bug_enum_summary( $g_severity_enum_string, "severity" ) ?>
			</table>
		</td>
		<td>
			<? ### RESOLUTION ### ?>
			<table width="97%">
				<? print_bug_enum_summary( $g_resolution_enum_string, "resolution" ) ?>
			</table>
		</td>
	</tr>
	<tr valign="bottom" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<b><? echo $s_by_category ?>:</b> (open/resolved/closed/total)
		</td>
		<td>
			<b><? echo $s_by_priority ?>:</b>
		</td>
	</tr>
	<tr align="center" valign="top" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<? ### CATEGORY ### ?>
			<table width="97%">
				<? print_category_summary() ?>
			</table>
		</td>
		<td>
			<? ### CATEGORY ### ?>
			<table width="97%">
				<? print_bug_enum_summary( $g_priority_enum_string, "priority" ) ?>
			</table>
		</td>
	</tr>
	<tr valign="bottom" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<b><? echo $s_time_stats ?>:</b>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr align="center" valign="top" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<? ### MISCELLANEOUS ### ?>
			<table width="97%">
			<tr align="center" bgcolor="<? echo $g_primary_color_dark ?>">
				<td width="50%">
					<? echo $s_longest_open_bug ?>
				</td>
				<td width="50%">
					<? if ($t_bug_id>0) { ?>
						<? if ( get_current_user_pref_field( "advanced_view" )==1 ) { ?>
							<a href="<? echo $g_view_bug_advanced_page ?>?f_id=<? echo $t_bug_id ?>"><? echo $t_bug_id ?></a>
						<? } else {?>
							<a href="<? echo $g_view_bug_page ?>?f_id=<? echo $t_bug_id ?>"><? echo $t_bug_id ?></a>
						<? } ?>
					<? } ?>
				</td>
			</tr>
			<tr align="center" bgcolor="<? echo $g_primary_color_light ?>">
				<td>
					<? echo $s_longest_open ?>
				</td>
				<td>
					<? echo $t_largest_diff ?>
				</td>
			</tr>
			<tr align="center" bgcolor="<? echo $g_primary_color_dark ?>">
				<td>
					<? echo $s_average_time ?>
				</td>
				<td>
					<? echo $t_average_time ?>
				</td>
			</tr>
			<tr align="center" bgcolor="<? echo $g_primary_color_light ?>">
				<td>
					<? echo $s_total_time ?>
				</td>
				<td>
					<? echo $t_total_time ?>
				</td>
			</tr>
			</table>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>

	<tr valign="bottom" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<b><? echo $s_developer_stats ?>:</b>
		</td>
		<td>
			<b>reporter stats (open/resolved/closed/total): </b> <? #@@@ OOPS - localize ?>
		</td>
	</tr>
	<tr align="center" valign="top" height="28" bgcolor="<? echo $g_white_color ?>">
		<td>
			<table width="97%">
				<? print_developer_summary() ?>
			</table>
		</td>
		<td>
			<table width="97%">
				<? print_reporter_summary() ?>
			</table>
		</td>
	</tr>

	</table>
	</td>
</tr>
</table>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>