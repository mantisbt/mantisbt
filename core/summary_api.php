<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_api.php,v 1.10 2003-02-24 09:44:09 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Summary printing API
	###########################################################################

	# --------------------
	# Used in summary reports
	# Given the enum string this function prints out the summary for each enum setting
	# The enum field name is passed in through $p_enum
	function print_bug_enum_summary( $p_enum_string, $p_enum ) {
		global 	$g_mantis_bug_table, $g_summary_pad;

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ($t_project_id=='0000000') $specific_where = ' 1=1';
		else $specific_where = " project_id='$t_project_id'";

		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$c_s[0] = addslashes($t_s[0]);

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$c_s[0]' AND $specific_where";
			$result = db_query( $query );
			$t_enum_count = db_result( $result, 0 );

			$t_res_val = RESOLVED;
			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$c_s[0]' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' AND $specific_where";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$c_s[0]' AND
						status='$t_clo_val' AND $specific_where";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$c_s[0]' AND
						status='$t_res_val' AND $specific_where";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			$open_bug_count		= str_pad( $open_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count	= str_pad( $resolved_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count	= str_pad( $closed_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$t_enum_count		= str_pad( $t_enum_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );

			print '<tr align="center" ' . helper_alternate_class( $i ) . '>';
				PRINT "<td width=\"50%\">";
					echo get_enum_element( $p_enum, $t_s[0] );
				PRINT '</td>';
				PRINT "<td width=\"50%\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $t_enum_count";
				PRINT '</td>';
			PRINT '</tr>';
		} # end for
	}
	# --------------------
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function get_bug_count_by_date( $p_time_length=1 ) {
		global $g_mantis_bug_table;

		$c_time_length = (integer)$p_time_length;

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ($t_project_id=='0000000') $specific_where = ' 1=1';
		else $specific_where = " project_id='$t_project_id'";

		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE TO_DAYS(NOW()) - TO_DAYS(date_submitted) <= '$c_time_length' AND $specific_where";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# This function shows the number of bugs submitted in the last X days
	# An array of integers representing days is passed in
	function print_bug_date_summary( $p_date_array ) {
		$arr_count = count( $p_date_array );
		for ($i=0;$i<$arr_count;$i++) {
			$t_enum_count = get_bug_count_by_date( $p_date_array[$i] );

			print '<tr align="center" ' . helper_alternate_class( $i ) . '>';
				PRINT "<td width=\"50%\">";
					echo $p_date_array[$i];
				PRINT '</td>';
				PRINT "<td width=\"50%\">";
					echo $t_enum_count;
				PRINT '</td>';
			PRINT '</tr>';
		} # end for
	}
	# --------------------
	# print bug counts by assigned to each developer
	function print_developer_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_summary_pad, $g_handle_bug_threshold;

		$t_dev = $g_handle_bug_threshold;
		$t_man = MANAGER;
		$t_adm = ADMINISTRATOR;

		$query = "SELECT id, username
				FROM $g_mantis_user_table
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ($t_project_id=='0000000') $specific_where = ' 1=1';
		else $specific_where = " project_id='$t_project_id'";

		$t_row_count = 0;

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

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

			$open_bug_count		= str_pad( $open_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count	= str_pad( $resolved_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count	= str_pad( $closed_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$total_bug_count	= str_pad( $total_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );

			print '<tr align="center" ' . helper_alternate_class( $i ) . '>';
				PRINT "<td width=\"50%\">";
					echo $v_username;
				PRINT '</td>';
				PRINT "<td width=\"50%\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT '</td>';
			PRINT '</tr>';

			$t_row_count++;
			} #end if
		} # end for
	}
	# --------------------
	# print bug counts by reporter id
	function print_reporter_summary() {
		global 	$g_mantis_bug_table, 
				$g_reporter_summary_limit,
				$g_summary_pad;

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ($t_project_id=='0000000') $specific_where = ' 1=1';
		else $specific_where = " project_id='$t_project_id'";

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

			$v_id = $row['reporter_id'];
			$v_username = user_get_name( $v_id );

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

			$open_bug_count		= str_pad( $open_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count	= str_pad( $resolved_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count	= str_pad( $closed_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$total_bug_count	= str_pad( $total_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );

			print '<tr align="center" ' . helper_alternate_class( $i ) . '>';
				PRINT "<td width=\"50%\">";
					echo $v_username;
				PRINT '</td>';
				PRINT "<td width=\"50%\">";
					PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
				PRINT '</td>';
			PRINT '</tr>';
		} # end for
	}
	# --------------------
	# print a bug count per category
	function print_category_summary() {
		global 	$g_mantis_bug_table, $g_mantis_project_table,
				$g_mantis_project_category_table,
				$g_summary_pad, $g_summary_category_include_project;

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ($t_project_id=='0000000') {
			$specific_where = '';
		} else {
			$specific_where = " AND (project_id='$t_project_id')";
		}

		$query = "SELECT name as project, category
				FROM $g_mantis_project_category_table pc, $g_mantis_project_table p
				WHERE (p.id = pc.project_id) $specific_where
				ORDER BY project, category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_category = $row['category'];
			$t_project = $row['project'];

			$c_category = addslashes( $t_category );
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$c_category' $specific_where ";
			$result2 = db_query( $query );
			$total_bug_count = db_result( $result2, 0, 0 );

			$t_clo_val = CLOSED;
			$t_res_val = RESOLVED;

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$c_category' AND
						status<>'$t_clo_val' AND
						status<>'$t_res_val' $specific_where ";
			$result2 = db_query( $query );
			$open_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$c_category' AND status='$t_clo_val' $specific_where ";
			$result2 = db_query( $query );
			$closed_bug_count = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$c_category' AND status='$t_res_val' $specific_where ";
			$result2 = db_query( $query );
			$resolved_bug_count = db_result( $result2, 0, 0 );

			$open_bug_count		= str_pad( $open_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$resolved_bug_count	= str_pad( $resolved_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$closed_bug_count	= str_pad( $closed_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );
			$total_bug_count	= str_pad( $total_bug_count, '&nbsp;', $g_summary_pad, STR_PAD_LEFT );

			print '<tr align="center" ' . helper_alternate_class( $i ) . '>';
			PRINT "<td width=\"50%\">";
			if ( ( ON == $g_summary_category_include_project ) && ( $t_project_id=='0000000' ) ) {
				PRINT "[$t_project] ";
			}
			PRINT "$t_category</td><td width=\"50%\">";
			PRINT "$open_bug_count / $resolved_bug_count / $closed_bug_count / $total_bug_count";
			PRINT '</td>';
			PRINT '</tr>';
		} # end for
	}
	# --------------------
?>
