<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_api.php,v 1.16 2003-03-10 11:51:29 int2str Exp $
	# --------------------------------------------------------

	#######################################################################
	# Summary printing API
	#######################################################################

	function summary_helper_print_row( $p_label, $p_open, $p_resolved, $p_closed, $p_total ) {
		printf( "<tr %s>",  helper_alternate_class() );
		printf( "<td width=\"50%%\">%s</td>", $p_label );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $p_open );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $p_resolved );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $p_closed );
		printf( "<td width=\"12%%\" class=\"right\">%d</td>", $p_total );
		print( "</tr>\n" );
	}
	# --------------------
	# Used in summary reports
	# Given the enum string this function prints out the summary 
	# for each enum setting
	# The enum field name is passed in through $p_enum
	function summary_print_by_enum( $p_enum_string, $p_enum ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ( ALL_PROJECTS == $t_project_id ) 
			$t_project_filter = ' 1=1';
		else 
			$t_project_filter = " project_id='$t_project_id'";

		$query = "SELECT status, $p_enum FROM $t_mantis_bug_table"
			. " WHERE $t_project_filter ORDER BY $p_enum";

		$result = db_query( $query );

		$t_last_value = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			if ( $row[$p_enum] != $t_last_value 
			  && $t_last_value != -1 ) {
				summary_helper_print_row( 
				  get_enum_element( $p_enum, $t_last_value)
			  	  , $t_bugs_open, $t_bugs_resolved
				  , $t_bugs_closed, $t_bugs_total );

				$t_bugs_open = 0;
				$t_bugs_resolved = 0;
				$t_bugs_closed = 0;
				$t_bugs_total = 0;
			}

			$t_bugs_total++;

			switch( $row['status'] ) {
				case $t_resolved_val:
					$t_bugs_resolved++;
					break;
				case $t_closed_val:
					$t_bugs_closed++;
					break;
				default:
					$t_bugs_open++;
					break;
			}

			$t_last_value = $row[$p_enum];
		}

		if ( 0 < $t_bugs_total ) {
			summary_helper_print_row( 
			  get_enum_element( $p_enum, $t_last_value)
			  , $t_bugs_open, $t_bugs_resolved
			  , $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function summary_bug_count_by_date( $p_time_length=1 ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );

		$c_time_length = (integer)$p_time_length;

		$t_project_id = helper_get_current_project();

		#checking if it's a per project statistic or all projects
		if ( ALL_PROJECTS == $t_project_id ) $specific_where = ' 1=1';
		else $specific_where = " project_id='$t_project_id'";

		$query = "SELECT COUNT(*)
				FROM $t_mantis_bug_table
				WHERE TO_DAYS(NOW()) - TO_DAYS(date_submitted) <= '$c_time_length' AND $specific_where";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# This function shows the number of bugs submitted in the last X days
	# An array of integers representing days is passed in
	function summary_print_by_date( $p_date_array ) {
		$arr_count = count( $p_date_array );
		for ($i=0;$i<$arr_count;$i++) {
			$t_enum_count = summary_bug_count_by_date( $p_date_array[$i] );

			printf( "<tr %s>",  helper_alternate_class() );
			printf( "<td width=\"50%%\">%s</td>", $p_date_array[$i] );
			printf( "<td class=\"right\">%s</td>", $t_enum_count );
			print(  "</tr>\n" );
		} # end for
	}
	# --------------------
	# print bug counts by assigned to each developer
	function summary_print_by_developer() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );
		
		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT handler_id, status FROM $t_mantis_bug_table"
			. " WHERE handler_id>0 AND $specific_where"
			. " ORDER BY handler_id";

		$result = db_query( $query );

		$t_last_handler = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( $v_handler_id != $t_last_handler 
			  && $t_last_handler != -1 ) {
				$query = "SELECT username"
					. " FROM $t_mantis_user_table"
					. " WHERE id=$t_last_handler";
				$result2 = db_query( $query );
				$row2 = db_fetch_array( $result2 );
				summary_helper_print_row( 
				  $row2['username']
			  	  , $t_bugs_open, $t_bugs_resolved
				  , $t_bugs_closed, $t_bugs_total );

				$t_bugs_open = 0;
				$t_bugs_resolved = 0;
				$t_bugs_closed = 0;
				$t_bugs_total = 0;
			}

			$t_bugs_total++;

			switch( $v_status ) {
				case $t_resolved_val:
					$t_bugs_resolved++;
					break;
				case $t_closed_val:
					$t_bugs_closed++;
					break;
				default:
					$t_bugs_open++;
					break;
			}

			$t_last_handler = $v_handler_id;
		}

		if ( 0 < $t_bugs_total ) {
			$query = "SELECT username"
				. " FROM $t_mantis_user_table"
				. " WHERE id=$t_last_handler";
			$result2 = db_query( $query );
			$row2 = db_fetch_array( $result2 );
			summary_helper_print_row(
			  $row2['username']
			  , $t_bugs_open, $t_bugs_resolved
			  , $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
	# print bug counts by reporter id
	function summary_print_by_reporter() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );
		$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );
		
		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT reporter_id, COUNT(*) as num"
			. " FROM $t_mantis_bug_table"
			. " WHERE $specific_where"
			. " GROUP BY reporter_id"
			. " ORDER BY num DESC"
			. " LIMIT $t_reporter_summary_limit";
		$result = db_query( $query );
		while ( $row = db_fetch_array( $result ) ) {
			$v_reporter_id = $row['reporter_id'];
			$query = "SELECT status FROM $t_mantis_bug_table"
				. " WHERE reporter_id=$v_reporter_id"
				. " AND $specific_where";
			$result2 = db_query( $query );

			$last_reporter = -1;
			$t_bugs_open = 0;
			$t_bugs_resolved = 0;
			$t_bugs_closed = 0;
			$t_bugs_total = 0;

			$t_resolved_val = RESOLVED;
			$t_closed_val = CLOSED;

			while ( $row2 = db_fetch_array( $result2 ) ) {
				$t_bugs_total++;

				switch( $row2['status'] ) {
					case $t_resolved_val:
						$t_bugs_resolved++;
						break;
					case $t_closed_val:
						$t_bugs_closed++;
						break;
					default:
						$t_bugs_open++;
						break;
				}
			}

			if ( 0 < $t_bugs_total ) {
				$query = "SELECT username"
					. " FROM $t_mantis_user_table"
					. " WHERE id=$v_reporter_id";
				$result3 = db_query( $query );
				$row3 = db_fetch_array( $result3 );
				summary_helper_print_row(
				$row3['username']
				, $t_bugs_open, $t_bugs_resolved
				, $t_bugs_closed, $t_bugs_total );
			}
		}
	}
	# --------------------
	# print a bug count per category
	function summary_print_by_category() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );
		$t_summary_category_include_project = 
			config_get( 'summary_category_include_project' );
		
		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) 
			$specific_where = ' 1=1';
		else 
			$specific_where = " project_id='$t_project_id'";

		$query = "SELECT project_id, category, status FROM $t_mantis_bug_table"
			. " WHERE category>'' AND $specific_where"
			. " ORDER BY project_id, category, status";

		$result = db_query( $query );

		$last_category = -1;
		$last_project = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( $v_category != $last_category 
			  && $last_category != -1 ) {
			$label = $last_category;
				if ( ( ON == $t_summary_category_include_project ) 
				  && ( ALL_PROJECTS == $t_project_id ) ) {
					$query = "SELECT name"
						. " FROM $t_mantis_project_table"
						. " WHERE id=$last_project";
					$result2 = db_query( $query );
					$row2 = db_fetch_array( $result2 );

					$label = sprintf( "[%s] %s", $row2['name'], $label );
				}
				summary_helper_print_row(
				$label
				, $t_bugs_open, $t_bugs_resolved
				, $t_bugs_closed, $t_bugs_total );

				$t_bugs_open = 0;
				$t_bugs_resolved = 0;
				$t_bugs_closed = 0;
				$t_bugs_total = 0;
			}

			$t_bugs_total++;

			switch( $v_status ) {
				case $t_resolved_val:
					$t_bugs_resolved++;
					break;
				case $t_closed_val:
					$t_bugs_closed++;
					break;
				default:
					$t_bugs_open++;
					break;
			}

			$last_category = $v_category;
			$last_project = $v_project_id;
		}

		if ( 0 < $t_bugs_total ) {
			$label = $last_category;
			if ( ( ON == $t_summary_category_include_project ) 
			  && ( ALL_PROJECTS == $t_project_id ) ) {
				$query = "SELECT name"
					. " FROM $t_mantis_project_table"
					. " WHERE id=$last_project";
				$result2 = db_query( $query );
				$row2 = db_fetch_array( $result2 );
				
				$label = sprintf( "[%s] %s", $row2['name'], $label );
			}
			summary_helper_print_row( 
			  $label
			  , $t_bugs_open, $t_bugs_resolved
			  , $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
	# print bug counts by project
	function summary_print_by_project() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );
		
		$t_project_id = helper_get_current_project();

		# This function only works when "all projects" is selected
		if ( ALL_PROJECTS != $t_project_id ) 
			return;

		$query = "SELECT project_id, status FROM $t_mantis_bug_table"
			. " ORDER BY project_id";

		$result = db_query( $query );

		$t_last_project = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( $v_project_id != $t_last_project 
			  && $t_last_project != -1 ) {
				$query = "SELECT name"
					. " FROM $t_mantis_project_table"
					. " WHERE id=$t_last_project";
				$result2 = db_query( $query );
				$row2 = db_fetch_array( $result2 );
				summary_helper_print_row( 
				  $row2['name']
			  	  , $t_bugs_open, $t_bugs_resolved
				  , $t_bugs_closed, $t_bugs_total );

				$t_bugs_open = 0;
				$t_bugs_resolved = 0;
				$t_bugs_closed = 0;
				$t_bugs_total = 0;
			}

			$t_bugs_total++;

			switch( $v_status ) {
				case $t_resolved_val:
					$t_bugs_resolved++;
					break;
				case $t_closed_val:
					$t_bugs_closed++;
					break;
				default:
					$t_bugs_open++;
					break;
			}

			$t_last_project = $v_project_id;
		}

		if ( 0 < $t_bugs_total ) {
			$query = "SELECT name"
				. " FROM $t_mantis_project_table"
				. " WHERE id=$t_last_project";
			$result2 = db_query( $query );
			$row2 = db_fetch_array( $result2 );
			summary_helper_print_row(
			  $row2['name']
			  , $t_bugs_open, $t_bugs_resolved
			  , $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
?>
