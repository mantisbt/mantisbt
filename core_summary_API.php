<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Summary printing API
	###########################################################################
	# --------------------
	# Used in summary reports
	# Given the enum string this function prints out the summary for each enum setting
	# The enum field name is passed in through $p_enum
	function print_bug_enum_summary( $p_enum_string, $p_enum ) {
		global 	$g_mantis_bug_table, $g_primary_color1, $g_primary_color2,
				$g_project_cookie_val, $g_summary_pad;

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );

		#checking if it's a per project statistic or all projects
		if ($g_project_cookie_val=='0000000') $specific_where = " 1=1";
		else $specific_where = " project_id='$g_project_cookie_val'";

		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND $specific_where";
			$result = db_query( $query );
			$t_enum_count = db_result( $result, 0 );

			$t_res_val = RESOLVED;
			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' AND $specific_where";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_clo_val' AND $specific_where";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_res_val' AND $specific_where";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			$open_bug_count = str_pd( $open_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count = str_pd( $resolved_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count = str_pd( $closed_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$t_enum_count = str_pd( $t_enum_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );

			# alternate row colors
			$t_bgcolor = alternate_colors( $i );

			PRINT "<tr align=\"center\">";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					echo get_enum_element( $p_enum, $t_s[0] );
				PRINT "</td>";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					#PRINT "$open_bug_count $resolved_bug_count $closed_bug_count $t_enum_count";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $t_enum_count";
				PRINT "</td>";
			PRINT "</tr>";
		} # end for
	}
	# --------------------
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function get_bug_count_by_date( $p_time_length=1 ) {
		global $g_mantis_bug_table, $g_project_cookie_val;
    $p_time_length = (integer)$p_time_length;

		#checking if it's a per project statistic or all projects
		if ($g_project_cookie_val=='0000000') $specific_where = " 1=1";
		else $specific_where = " project_id='$g_project_cookie_val'";

		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE TO_DAYS(NOW()) - TO_DAYS(date_submitted) <= '$p_time_length' AND $specific_where";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# This function shows the number of bugs submitted in the last X days
	# An array of integers representing days is passed in
	function print_bug_date_summary( $p_date_array ) {
		global $g_primary_color1, $g_primary_color2;

		$arr_count = count( $p_date_array );
		for ($i=0;$i<$arr_count;$i++) {
			$t_enum_count = get_bug_count_by_date( $p_date_array[$i] );

			# alternate row colors
			$t_bgcolor = alternate_colors( $i );

			PRINT "<tr align=\"center\">";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					echo $p_date_array[$i];
				PRINT "</td>";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					echo $t_enum_count;
				PRINT "</td>";
			PRINT "</tr>";
		} # end for
	}
	# --------------------
	# print bug counts by assigned to each developer
	function print_developer_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_primary_color1, $g_primary_color2,
				$g_project_cookie_val,
				$g_summary_pad;

		$t_dev = DEVELOPER;
		$t_man = MANAGER;
		$t_adm = ADMINISTRATOR;

		$query = "SELECT id, username
				FROM $g_mantis_user_table
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		#checking if it's a per project statistic or all projects
		if ($g_project_cookie_val=='0000000') $specific_where = " 1=1";
		else $specific_where = " project_id='$g_project_cookie_val'";

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND $specific_where";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			#only developers with relevant stats are displayed
			if ($total_bug_count>0) {
			$t_res_val = RESOLVED;
			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' AND $specific_where";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='$t_clo_val' AND $specific_where";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='$t_res_val' AND $specific_where";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			$open_bug_count = str_pd( $open_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count = str_pd( $resolved_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count = str_pd( $closed_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$total_bug_count = str_pd( $total_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );

			# alternate row colors
			$t_bgcolor = alternate_colors( $i );

			PRINT "<tr align=\"center\">";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					echo $v_username;
				PRINT "</td>";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
			} #end if
		} # end for
	}
	# --------------------
	# print bug counts by reporter id
	function print_reporter_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_primary_color1, $g_primary_color2,
				$g_reporter_summary_limit,
				$g_project_cookie_val,
				$g_summary_pad;

		#checking if it's a per project statistic or all projects
		if ($g_project_cookie_val=='0000000') $specific_where = " 1=1";
		else $specific_where = " project_id='$g_project_cookie_val'";

		$t_view = VIEWER;
		$query = "SELECT reporter_id, COUNT(*) as num
				FROM $g_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id
				ORDER BY num DESC
				LIMIT $g_reporter_summary_limit";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );

			$v_id = $row["reporter_id"];
			$v_username = get_user_info( $v_id, "username" );

			$t_res_val = RESOLVED;
			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE reporter_id='$v_id' AND $specific_where ";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE reporter_id='$v_id' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' AND $specific_where ";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE reporter_id='$v_id' AND status='$t_clo_val' AND $specific_where ";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE reporter_id='$v_id' AND status='$t_res_val' AND $specific_where ";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			$open_bug_count = str_pd( $open_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count = str_pd( $resolved_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count = str_pd( $closed_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$total_bug_count = str_pd( $total_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );

			# alternate row colors
			$t_bgcolor = alternate_colors( $i );

			PRINT "<tr align=\"center\">";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					echo $v_username;
				PRINT "</td>";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} # end for
	}
	# --------------------
	# print a bug count per category
	function print_category_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_category_table, $g_project_cookie_val,
				$g_primary_color1, $g_primary_color1,
				$g_summary_pad;

		#checking if it's a per project statistic or all projects
		if ($g_project_cookie_val=='0000000') $specific_where = " 1=1";
		else $specific_where = " project_id='$g_project_cookie_val'";

		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE $specific_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row["category"];

			$t_category2 = addslashes( $t_category );
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$t_category2' AND $specific_where ";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			$t_clo_val = CLOSED;
			$t_res_val = RESOLVED;

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$t_category2' AND
						status<>'$t_clo_val' AND
						status<>'$t_res_val'AND $specific_where ";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$t_category2' AND status='$t_clo_val' AND $specific_where ";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$t_category2' AND status='$t_res_val' AND $specific_where ";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			$open_bug_count = str_pd( $open_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count = str_pd( $resolved_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count = str_pd( $closed_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );
			$total_bug_count = str_pd( $total_bug_count, "&nbsp;", $g_summary_pad, STR_PAD_LEFT );

			# alternate row colors
			$t_bgcolor = alternate_colors( $i );

			PRINT "<tr align=\"center\">";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					echo $t_category;
				PRINT "</td>";
				PRINT "<td width=\"50%\" bgcolor=\"$t_bgcolor\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} # end for
	}
	# --------------------
?>