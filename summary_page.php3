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

<? print_summary_menu( $g_summary_page ) ?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_summary_title ?> <? echo $s_orct ?>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<? ### STATUS ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_by_status ?>:
			</td>
		</tr>
		<? print_bug_enum_summary( $s_status_enum_string, "status" ) ?>
		</table>
	</td>
	<td width="50%">
		<? ### DATE ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_by_date ?>:
			</td>
		</tr>
		<? print_bug_date_summary( $g_date_partitions ) ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td>
		<? ### SEVERITY ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_by_severity ?>:
			</td>
		</tr>
		<? print_bug_enum_summary( $s_severity_enum_string, "severity" ) ?>
		</table>
	</td>
	<td>
		<? ### RESOLUTION ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_by_resolution ?>:
			</td>
		</tr>
		<? print_bug_enum_summary( $s_resolution_enum_string, "resolution" ) ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td>
		<? ### CATEGORY ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_by_category ?>:
			</td>
		</tr>
		<? print_category_summary() ?>
		</table>
	</td>
	<td>
		<? ### PRIORITY ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_by_priority ?>:
			</td>
		</tr>
		<? print_bug_enum_summary( $s_priority_enum_string, "priority" ) ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td>
		<? ### MISCELLANEOUS ### ?>
		<table class="width100">
		<tr>
			<td class="form-title">
				<? echo $s_time_stats ?>:
			</td>
		</tr>
		<tr class="row-1">
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
		<tr class="row-2">
			<td>
				<? echo $s_longest_open ?>
			</td>
			<td>
				<? echo $t_largest_diff ?>
			</td>
		</tr>
		<tr class="row-1">
			<td>
				<? echo $s_average_time ?>
			</td>
			<td>
				<? echo $t_average_time ?>
			</td>
		</tr>
		<tr class="row-2">
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
<tr valign="top">
	<td>
		<? ### DEVELOPER ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_developer_stats ?>:
			</td>
		</tr>
		<? print_developer_summary() ?>
		</table>
	</td>
	<td>
		<? ### REPORTER ### ?>
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<? echo $s_reporter_stats ?>:
			</td>
		</tr>
		<? print_reporter_summary() ?>
		</table>
	</td>
</tr>
</table>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>