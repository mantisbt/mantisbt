<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_api.php,v 1.13 2003-03-10 09:40:55 int2str Exp $
	# --------------------------------------------------------

	###########################################################################
	# Summary printing API
	###########################################################################

	function summary_helper_print_row( $row, $label, $open, $resolved, $closed, $total ) {
		printf( "<tr %s>",  helper_alternate_class( $row ) );
		printf( "<td width=\"50%%\">%s</td>", $label );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $open );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $resolved );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $closed );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $total );
		print( "</tr>\n" );
	}
	# --------------------
	# Used in summary reports
	# Given the enum string this function prints out the summary 
	# for each enum setting
	# The enum field name is passed in through $p_enum
	function print_bug_enum_summary( $p_enum_string, $p_enum ) {
		global 	$g_mantis_bug_table;

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT status, $p_enum FROM $g_mantis_bug_table"
			. " WHERE $specific_where ORDER BY $p_enum";

		$result = db_query( $query );

		$last_value = -1;
		$bugs_open = 0;
		$bugs_resolved = 0;
		$bugs_closed = 0;
		$bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			if ( $row[$p_enum] != $last_value 
			  && $last_value != -1 ) {
				summary_helper_print_row( $i 
				  , get_enum_element( $p_enum, $last_value)
			  	  , $bugs_open, $bugs_resolved
				  , $bugs_closed, $bugs_total );

				$bugs_open = 0;
				$bugs_resolved = 0;
				$bugs_closed = 0;
				$bugs_total = 0;
			}

			$bugs_total++;

			switch( $row['status'] ) {
				case $t_resolved_val:
					$bugs_resolved++;
					break;
				case $t_closed_val:
					$bugs_closed++;
					break;
				default:
					$bugs_open++;
					break;
			}

			$last_value = $row[$p_enum];
		}

		if ( 0 < $bugs_total ) {
			summary_helper_print_row( $i 
			  , get_enum_element( $p_enum, $last_value)
			  , $bugs_open, $bugs_resolved
			  , $bugs_closed, $bugs_total );
		}
	}
	# --------------------
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function get_bug_count_by_date( $p_time_length=1 ) {
		global $g_mantis_bug_table;

		$c_time_length = (integer)$p_time_length;

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ( ALL_PROJECTS == $t_project_id ) $specific_where = ' 1=1';
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

			printf( "<tr %s>",  helper_alternate_class( $row ) );
			printf( "<td width=\"50%%\">%s</td>", $p_date_array[$i] );
			printf( "<td class=\"right\">%s</td>", $t_enum_count );
			print(  "</tr>\n" );
		} # end for
	}
	# --------------------
	# print bug counts by assigned to each developer
	function print_developer_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table;
		
		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT handler_id, status FROM $g_mantis_bug_table"
			. " WHERE handler_id>0 AND $specific_where"
			. " ORDER BY handler_id";

		$result = db_query( $query );

		$last_handler = -1;
		$bugs_open = 0;
		$bugs_resolved = 0;
		$bugs_closed = 0;
		$bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( $v_handler_id != $last_handler 
			  && $last_handler != -1 ) {
				$query = "SELECT username"
					. " FROM $g_mantis_user_table"
					. " WHERE id=$last_handler";
				$result2 = db_query( $query );
				$row2 = db_fetch_array( $result2 );
				summary_helper_print_row( $i 
				  , $row2['username']
			  	  , $bugs_open, $bugs_resolved
				  , $bugs_closed, $bugs_total );

				$bugs_open = 0;
				$bugs_resolved = 0;
				$bugs_closed = 0;
				$bugs_total = 0;
			}

			$bugs_total++;

			switch( $v_status ) {
				case $t_resolved_val:
					$bugs_resolved++;
					break;
				case $t_closed_val:
					$bugs_closed++;
					break;
				default:
					$bugs_open++;
					break;
			}

			$last_handler = $v_handler_id;
		}

		if ( 0 < $bugs_total ) {
			$query = "SELECT username"
				. " FROM $g_mantis_user_table"
				. " WHERE id=$last_handler";
			$result2 = db_query( $query );
			$row2 = db_fetch_array( $result2 );
			summary_helper_print_row( $i 
			  , $row2['username']
			  , $bugs_open, $bugs_resolved
			  , $bugs_closed, $bugs_total );
		}
	}
	# --------------------
	# print bug counts by reporter id
	function print_reporter_summary() {
		global $g_mantis_bug_table, $g_mantis_user_table;
		global $g_reporter_summary_limit;
		
		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT reporter_id, COUNT(*) as num"
			. " FROM $g_mantis_bug_table"
			. " WHERE $specific_where"
			. " GROUP BY reporter_id"
			. " ORDER BY num DESC"
			. " LIMIT $g_reporter_summary_limit";
		$result = db_query( $query );
		while ( $row = db_fetch_array( $result ) ) {
			$v_reporter_id = $row['reporter_id'];
			$query = "SELECT status FROM $g_mantis_bug_table"
				. " WHERE reporter_id=$v_reporter_id"
				. " AND $specific_where";
			$result2 = db_query( $query );

			$last_reporter = -1;
			$bugs_open = 0;
			$bugs_resolved = 0;
			$bugs_closed = 0;
			$bugs_total = 0;

			$t_resolved_val = RESOLVED;
			$t_closed_val = CLOSED;

			while ( $row2 = db_fetch_array( $result2 ) ) {
				$bugs_total++;

				switch( $row2['status'] ) {
					case $t_resolved_val:
						$bugs_resolved++;
						break;
					case $t_closed_val:
						$bugs_closed++;
						break;
					default:
						$bugs_open++;
						break;
				}
			}

			if ( 0 < $bugs_total ) {
				$query = "SELECT username"
					. " FROM $g_mantis_user_table"
					. " WHERE id=$v_reporter_id";
				$result3 = db_query( $query );
				$row3 = db_fetch_array( $result3 );
				summary_helper_print_row( $i 
				, $row3['username']
				, $bugs_open, $bugs_resolved
				, $bugs_closed, $bugs_total );
			}
		}
	}
	# --------------------
	# print a bug count per category
	function print_category_summary() {
		global 	$g_mantis_bug_table;
		global	$g_mantis_project_table;
		global	$g_summary_category_include_project;
		
		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT project_id, category, status FROM $g_mantis_bug_table"
			. " WHERE category>'' AND $specific_where"
			. " ORDER BY project_id, category, status";

		$result = db_query( $query );

		$last_category = -1;
		$last_project = -1;
		$bugs_open = 0;
		$bugs_resolved = 0;
		$bugs_closed = 0;
		$bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( $v_category != $last_category 
			  && $last_category != -1 ) {
			$label = $last_category;
				if ( ( ON == $g_summary_category_include_project ) 
				  && ( ALL_PROJECTS == $t_project_id ) ) {
					$query = "SELECT name"
						. " FROM $g_mantis_project_table"
						. " WHERE id=$last_project";
					$result2 = db_query( $query );
					$row2 = db_fetch_array( $result2 );

					$label = sprintf( "[%s] %s", $row2['name'], $label );
				}
				summary_helper_print_row( $i 
				, $label
				, $bugs_open, $bugs_resolved
				, $bugs_closed, $bugs_total );

				$bugs_open = 0;
				$bugs_resolved = 0;
				$bugs_closed = 0;
				$bugs_total = 0;
			}

			$bugs_total++;

			switch( $v_status ) {
				case $t_resolved_val:
					$bugs_resolved++;
					break;
				case $t_closed_val:
					$bugs_closed++;
					break;
				default:
					$bugs_open++;
					break;
			}

			$last_category = $v_category;
			$last_project = $v_project_id;
		}

		if ( 0 < $bugs_total ) {
			$label = $last_category;
			if ( ( ON == $g_summary_category_include_project ) 
			  && ( ALL_PROJECTS == $t_project_id ) ) {
				$query = "SELECT name"
					. " FROM $g_mantis_project_table"
					. " WHERE id=$last_project";
				$result2 = db_query( $query );
				$row2 = db_fetch_array( $result2 );
				
				$label = sprintf( "[%s] %s", $row2['name'], $label );
			}
			summary_helper_print_row( $i 
			  , $label
			  , $bugs_open, $bugs_resolved
			  , $bugs_closed, $bugs_total );
		}
	}
	# --------------------
?>
