<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Summary printing API
	###########################################################################
	### --------------------
	# Used in summary reports
	# Given the enum string this function prints out the summary for each enum setting
	# The enum field name is passed in through $p_enum
	function print_bug_enum_summary( $p_enum_string, $p_enum ) {
		global 	$g_mantis_bug_table, $g_primary_color_light, $g_primary_color_dark,
				$g_project_cookie_val;

		$t_arr = explode( ",", $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ":", $t_arr[$i] );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						project_id='$g_project_cookie_val'";
			if ( !empty( $p_status ) ) {
				if ( $p_status==OPEN ) {
					$t_res_val = RESOLVED;
					$query = $query." AND status<>'$t_res_val'";
				} else if ( $p_status==OPEN ) { # @@@ ????? Duplicate
					$t_res_val = RESOLVED;
					$query = $query." AND status='$t_res_val'";
				} else {
					$query = $query." AND status='$p_status'";
				}
			}
			$result = db_query( $query );
			$t_enum_count = db_result( $result, 0 );

			### alternate row colors
			$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );

			PRINT "<tr align=\"center\" bgcolor=\"$t_bgcolor\">";
				PRINT "<td width=\"50%\">";
					echo get_enum_to_string( $p_enum_string, $t_s[0] );
				PRINT "</td>";
				PRINT "<td width=\"50%\">";
					echo $t_enum_count;
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function get_bug_count_by_date( $p_time_length=1 ) {
		global $g_mantis_bug_table, $g_project_cookie_val;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE TO_DAYS(NOW()) - TO_DAYS(date_submitted) <= '$p_time_length' AND
					project_id='$g_project_cookie_val'";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	### --------------------
	# This funciton shows the number of bugs submitted in the last X days
	# An array of integers representing days is passed in
	function print_bug_date_summary( $p_date_array ) {
		global $g_primary_color_light, $g_primary_color_dark;

		$arr_count = count( $p_date_array );
		for ($i=0;$i<$arr_count;$i++) {
			$t_enum_count = get_bug_count_by_date( $p_date_array[$i] );

			### alternate row colors
			$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );

			PRINT "<tr align=\"center\" bgcolor=\"$t_bgcolor\">";
				PRINT "<td width=\"50%\">";
					echo $p_date_array[$i];
				PRINT "</td>";
				PRINT "<td width=\"50%\">";
					echo $t_enum_count;
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	# print bug counts by assigned to each developer
	function print_developer_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_primary_color_light, $g_primary_color_dark,
				$g_project_cookie_val;

		$t_dev = DEVELOPER;
		$t_man = MANAGER;
		$t_adm = ADMINISTRATOR;

		$query = "SELECT id, username
				FROM $g_mantis_user_table
				WHERE 	access_level=$t_dev OR
						access_level=$t_man OR
						access_level=$t_adm
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			$t_res_val = RESOLVED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status<>'$t_res_val' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='$t_clo_val' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='$t_res_val' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			### alternate row colors
			$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );

			PRINT "<tr align=\"center\" bgcolor=\"$t_bgcolor\">";
				PRINT "<td width=\"50%\">";
					echo $v_username;
				PRINT "</td>";
				PRINT "<td width=\"50%\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	# print bug counts by reporter id
	function print_reporter_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_primary_color_light, $g_primary_color_dark,
				$g_project_cookie_val;

		$t_view = VIEWER;
		$query = "SELECT id, username
				FROM $g_mantis_user_table
				WHERE 	access_level<>'$t_view'
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			$t_res_val = RESOLVED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status<>'$t_res_val' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='$t_clo_val' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND status='$t_res_val' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			### alternate row colors
			$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );

			PRINT "<tr align=\"center\" bgcolor=\"$t_bgcolor\">";
				PRINT "<td width=\"50%\">";
					echo $v_username;
				PRINT "</td>";
				PRINT "<td width=\"50%\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	# print a bug count per category
	function print_category_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_category_table, $g_project_cookie_val,
				$g_primary_color_light, $g_primary_color_dark;

		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE project_id='$g_project_cookie_val'
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row["category"];

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$t_category' AND
						project_id='$g_project_cookie_val'";
			$result2 = db_query( $query );
			$catgory_bug_count = db_result( $result2, 0, 0 );

			### alternate row colors
			$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );

			PRINT "<tr align=\"center\" bgcolor=\"$t_bgcolor\">";
				PRINT "<td width=\"50%\">";
					echo $t_category;
				PRINT "</td>";
				PRINT "<td width=\"50%\">";
					PRINT "$catgory_bug_count";
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>