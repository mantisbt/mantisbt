<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package CoreAPI
 * @subpackage SummaryAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires config_filter_defaults_include
 */
require_once( $g_absolute_path . 'config_filter_defaults_inc.php' );

function summary_helper_print_row( $p_label, $p_open, $p_resolved, $p_closed, $p_total ) {
	printf( '<tr %s>', helper_alternate_class() );
	printf( '<td width="50%%">%s</td>', $p_label );
	printf( '<td width="12%%" class="right">%s</td>', $p_open );
	printf( '<td width="12%%" class="right">%s</td>', $p_resolved );
	printf( '<td width="12%%" class="right">%s</td>', $p_closed );
	printf( '<td width="12%%" class="right">%s</td>', $p_total );
	print( '</tr>' );
}

/**
 * Returns a string representation of the user, together with a link to the issues
 * acted on by the user ( reported, handled or commented on )
 *
 * @param int $p_user_id
 * @return string
 */
function summary_helper_get_developer_label ( $p_user_id ) {

	$t_user = string_display_line( user_get_name( $p_user_id ) );

	return "<a class='subtle' href='view_all_set.php?type=1&amp;temporary=y
			&amp;".FILTER_PROPERTY_REPORTER_ID."=$p_user_id
			&amp;".FILTER_PROPERTY_HANDLER_ID."=$p_user_id
			&amp;".FILTER_PROPERTY_NOTE_USER_ID."=$p_user_id
			&amp;".FILTER_PROPERTY_MATCH_TYPE."=".FILTER_MATCH_ANY."'>$t_user</a>";

}

# Used in summary reports
# this function prints out the summary for the given enum setting
# The enum field name is passed in through $p_enum
function summary_print_by_enum( $p_enum ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$t_project_filter = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_project_filter ) {
		return;
	}

	$t_filter_prefix = config_get( 'bug_count_hyperlink_prefix' );

	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_status_query = ( 'status' == $p_enum ) ? '' : ' ,status ';
	$query = "SELECT COUNT(id) as bugcount, $p_enum $t_status_query
				FROM $t_mantis_bug_table
				WHERE $t_project_filter
				GROUP BY $p_enum $t_status_query
				ORDER BY $p_enum $t_status_query";
	$result = db_query_bound( $query );

	$t_last_value = -1;
	$t_bugs_open = 0;
	$t_bugs_resolved = 0;
	$t_bugs_closed = 0;
	$t_bugs_total = 0;

	$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
	$t_closed_val = config_get( 'bug_closed_status_threshold' );

	while( $row = db_fetch_array( $result ) ) {
		if(( $row[$p_enum] != $t_last_value ) && ( -1 != $t_last_value ) ) {

			# Build up the hyperlinks to bug views
			$t_bug_link = '';
			switch( $p_enum ) {
				case 'status':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_last_value;
					break;
				case 'severity':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_SEVERITY_ID . '=' . $t_last_value;
					break;
				case 'resolution':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_RESOLUTION_ID . '=' . $t_last_value;
					break;
				case 'priority':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_PRIORITY_ID . '=' . $t_last_value;
					break;
			}

			if( !is_blank( $t_bug_link ) ) {
				if( 0 < $t_bugs_open ) {
					$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
				} else {
					if(( 'status' == $p_enum ) && ( $t_last_value >= $t_resolved_val ) ) {
						$t_bugs_open = '-';
					}
				}
				if( 0 < $t_bugs_resolved ) {
					$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
				} else {
					if(( 'status' == $p_enum ) && (( $t_last_value < $t_resolved_val ) || ( $t_last_value >= $t_closed_val ) ) ) {
						$t_bugs_resolved = '-';
					}
				}
				if( 0 < $t_bugs_closed ) {
					$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
				} else {
					if(( 'status' == $p_enum ) && ( $t_last_value < $t_closed_val ) ) {
						$t_bugs_closed = '-';
					}
				}
				if( 0 < $t_bugs_total ) {
					$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
				}
			}

			summary_helper_print_row( get_enum_element( $p_enum, $t_last_value ), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

			$t_bugs_open = 0;
			$t_bugs_resolved = 0;
			$t_bugs_closed = 0;
			$t_bugs_total = 0;
		}

		$t_bugs_total += $row['bugcount'];
		if( $t_closed_val <= $row['status'] ) {
			$t_bugs_closed += $row['bugcount'];
		}
		else if( $t_resolved_val <= $row['status'] ) {
			$t_bugs_resolved += $row['bugcount'];
		} else {
			$t_bugs_open += $row['bugcount'];
		}
		$t_last_value = $row[$p_enum];
	}

	if( 0 < $t_bugs_total ) {
		# Build up the hyperlinks to bug views
		$t_bug_link = '';
		switch( $p_enum ) {
			case 'status':
				$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_last_value;
				break;
			case 'severity':
				$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_SEVERITY_ID . '=' . $t_last_value;
				break;
			case 'resolution':
				$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_RESOLUTION_ID . '=' . $t_last_value;
				break;
			case 'priority':
				$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_PRIORITY_ID . '=' . $t_last_value;
				break;
		}

		if( !is_blank( $t_bug_link ) ) {
			if( 0 < $t_bugs_open ) {
				$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
			} else {
				if(( 'status' == $p_enum ) && ( $t_last_value >= $t_resolved_val ) ) {
					$t_bugs_open = '-';
				}
			}
			if( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
			} else {
				if(( 'status' == $p_enum ) && (( $t_last_value < $t_resolved_val ) || ( $t_last_value >= $t_closed_val ) ) ) {
					$t_bugs_resolved = '-';
				}
			}
			if( 0 < $t_bugs_closed ) {
				$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
			} else {
				if(( 'status' == $p_enum ) && ( $t_last_value < $t_closed_val ) ) {
					$t_bugs_closed = '-';
				}
			}
			if( 0 < $t_bugs_total ) {
				$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
			}
		}

		summary_helper_print_row( get_enum_element( $p_enum, $t_last_value ), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
	}
}

# prints the bugs submitted in the last X days (default is 1 day) for the
# current project
function summary_new_bug_count_by_date( $p_time_length = 1 ) {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );

	$c_time_length = (int) $p_time_length * SECONDS_PER_DAY;

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	$query = "SELECT COUNT(*)
				FROM $t_mantis_bug_table
				WHERE " . db_helper_compare_days( "" . db_now() . "", "date_submitted", "<= $c_time_length" ) . " AND $specific_where";
	$result = db_query_bound( $query );
	return db_result( $result, 0 );
}

# returns the number of bugs resolved in the last X days (default is 1 day) for the
# current project
function summary_resolved_bug_count_by_date( $p_time_length = 1 ) {
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_bug_history_table = db_get_table( 'mantis_bug_history_table' );
	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	$c_time_length = (int) $p_time_length * SECONDS_PER_DAY;

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	$query = "SELECT COUNT(DISTINCT(b.id))
				FROM $t_bug_table b
				LEFT JOIN $t_bug_history_table h
				ON b.id = h.bug_id
				AND h.type = " . NORMAL_TYPE . "
				AND h.field_name = 'status'
				WHERE b.status >= " . db_param() . "
				AND h.old_value < " . db_param() . "
				AND h.new_value >= " . db_param() . "
				AND " . db_helper_compare_days( "" . db_now() . "", "date_modified", "<= $c_time_length" ) . "
				AND $specific_where";
	$result = db_query_bound( $query, Array( $t_resolved, $t_resolved, $t_resolved ) );
	return db_result( $result, 0 );
}

# This function shows the number of bugs submitted in the last X days
# An array of integers representing days is passed in
function summary_print_by_date( $p_date_array ) {
	$arr_count = count( $p_date_array );
	foreach( $p_date_array as $t_days ) {
		$t_new_count = summary_new_bug_count_by_date( $t_days );
		$t_resolved_count = summary_resolved_bug_count_by_date( $t_days );

		$t_start_date = mktime( 0, 0, 0, date( 'm' ), ( date( 'd' ) - $t_days ), date( 'Y' ) );
		$t_new_bugs_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_FILTER_BY_DATE . '=on&amp;' . FILTER_PROPERTY_START_YEAR . '=' . date( 'Y', $t_start_date ) . '&amp;' . FILTER_PROPERTY_START_MONTH . '=' . date( 'm', $t_start_date ) . '&amp;' . FILTER_PROPERTY_START_DAY . '=' . date( 'd', $t_start_date ) . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">';

		print( "<tr " . helper_alternate_class() . ">\n" );
		print( "    <td width=\"50%\">" . $t_days . "</td>\n" );

		if( $t_new_count > 0 ) {
			print( "    <td class=\"right\">$t_new_bugs_link$t_new_count</a></td>\n" );
		} else {
			print( "    <td class=\"right\">$t_new_count</td>\n" );
		}
		print( "    <td class=\"right\">$t_resolved_count</td>\n" );

		$t_balance = $t_new_count - $t_resolved_count;
		$t_style = '';
		if( $t_balance > 0 ) {

			# we are talking about bugs: a balance > 0 is "negative" for the project...
			$t_style = " negative";
			$t_balance = sprintf( '%+d', $t_balance );

			# "+" modifier added in PHP >= 4.3.0
		}
		else if( $t_balance < 0 ) {
			$t_style = ' positive';
			$t_balance = sprintf( '%+d', $t_balance );
		}

		print( "\n<td class=\"right$t_style\">$t_balance</td>\n" );
		print( "</tr>\n" );
	}

	# end foreach
}

# Print list of open bugs with the highest activity score
# the score is calculated assigning one "point" for each history event
# associated with the bug
function summary_print_by_activity() {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_history_table = db_get_table( 'mantis_bug_history_table' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}
	$query = "SELECT COUNT(h.id) as count, b.id, b.summary, b.view_state
				FROM $t_mantis_bug_table AS b, $t_mantis_history_table AS h
				WHERE h.bug_id = b.id
				AND b.status < " . db_param() . "
				AND $specific_where
				GROUP BY h.bug_id, b.id, b.summary, b.last_updated, b.view_state
				ORDER BY count DESC, b.last_updated DESC";
	$result = db_query_bound( $query, Array( $t_resolved ) );

	$t_count = 0;
	$t_private_bug_threshold = config_get( 'private_bug_threshold' );
	$t_summarydata = Array();
	$t_summarybugs = Array();
	while( $row = db_fetch_array( $result ) ) {

		// Skip private bugs unless user has proper permissions
		if(( VS_PRIVATE == $row['view_state'] ) && ( false == access_has_bug_level( $t_private_bug_threshold, $row['id'] ) ) ) {
			continue;
		}

		if( $t_count++ == 10 ) {
			break;
		}

		$t_summarydata[] = array(
			'id' => $row['id'],
			'summary' => $row['summary'],
			'count' => $row['count'],
		);
		$t_summarybugs[] = $row['id'];
	}

	bug_cache_array_rows( $t_summarybugs );

	foreach( $t_summarydata as $row ) {
		$t_bugid = string_get_bug_view_link( $row['id'] );
		$t_summary = string_display_line( $row['summary'] );
		$t_notescount = $row['count'];

		print "<tr " . helper_alternate_class() . ">\n";
		print "<td class=\"small\">$t_bugid - $t_summary</td><td class=\"right\">$t_notescount</td>\n";
		print "</tr>\n";
	}
}

# Print list of bugs opened from the longest time
function summary_print_by_age() {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}
	$query = "SELECT * FROM $t_mantis_bug_table
				WHERE status < " . db_param() . "
				AND $specific_where
				ORDER BY date_submitted ASC, priority DESC";
	$result = db_query_bound( $query, array( $t_resolved ) );

	$t_count = 0;
	$t_private_bug_threshold = config_get( 'private_bug_threshold' );
	while( $row = db_fetch_array( $result ) ) {

		// as we select all from bug_table, inject into the cache.
		bug_cache_database_result( $row );

		// Skip private bugs unless user has proper permissions
		if(( VS_PRIVATE == bug_get_field( $row['id'], 'view_state' ) ) && ( false == access_has_bug_level( $t_private_bug_threshold, $row['id'] ) ) ) {
			continue;
		}

		if( $t_count++ == 10 ) {
			break;
		}

		$t_bugid = string_get_bug_view_link( $row['id'] );
		$t_summary = string_display_line( $row['summary'] );
		$t_days_open = intval(( time() - $row['date_submitted'] ) / SECONDS_PER_DAY );

		print "<tr " . helper_alternate_class() . ">\n";
		print "<td class=\"small\">$t_bugid - $t_summary</td><td class=\"right\">$t_days_open</td>\n";
		print "</tr>\n";
	}
}

# print bug counts by assigned to each developer
function summary_print_by_developer() {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_user_table = db_get_table( 'mantis_user_table' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	$query = "SELECT COUNT(id) as bugcount, handler_id, status
				FROM $t_mantis_bug_table
				WHERE handler_id>0 AND $specific_where
				GROUP BY handler_id, status
				ORDER BY handler_id, status";
	$result = db_query_bound( $query );

	$t_last_handler = -1;
	$t_bugs_open = 0;
	$t_bugs_resolved = 0;
	$t_bugs_closed = 0;
	$t_bugs_total = 0;

	$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
	$t_closed_val = config_get( 'bug_closed_status_threshold' );

	$t_summaryusers = array();
	$t_summarydata = array();
	while( $row = db_fetch_array( $result ) ) {
		$t_summarydata[] = $row;
		$t_summaryusers[] = $row['handler_id'];
	}

	user_cache_array_rows( array_unique( $t_summaryusers ) );

	foreach( $t_summarydata as $row ) {
		$v_handler_id = $row['handler_id'];
		$v_bugcount = $row['bugcount'];

		if(( $v_handler_id != $t_last_handler ) && ( -1 != $t_last_handler ) ) {
			$t_user = summary_helper_get_developer_label( $t_last_handler );

			$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $t_last_handler;
			if( 0 < $t_bugs_open ) {
				$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
			}
			if( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
			}
			if( 0 < $t_bugs_closed ) {
				$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
			}
			if( 0 < $t_bugs_total ) {
				$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
			}

			summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

			$t_bugs_open = 0;
			$t_bugs_resolved = 0;
			$t_bugs_closed = 0;
			$t_bugs_total = 0;
		}

		$t_bugs_total += $v_bugcount;
		if( $t_closed_val <= $row['status'] ) {
			$t_bugs_closed += $v_bugcount;
		}
		else if( $t_resolved_val <= $row['status'] ) {
			$t_bugs_resolved += $v_bugcount;
		} else {
			$t_bugs_open += $v_bugcount;
		}
		$t_last_handler = $v_handler_id;
	}

	if( 0 < $t_bugs_total ) {
		$t_user = summary_helper_get_developer_label( $t_last_handler );

		$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $t_last_handler;
		if( 0 < $t_bugs_open ) {
			$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
		}
		if( 0 < $t_bugs_resolved ) {
			$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
		}
		if( 0 < $t_bugs_closed ) {
			$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
		}
		if( 0 < $t_bugs_total ) {
			$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
		}

		summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
	}
}

# print bug counts by reporter id
function summary_print_by_reporter() {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_user_table = db_get_table( 'mantis_user_table' );
	$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	$query = "SELECT reporter_id, COUNT(*) as num
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id
				ORDER BY num DESC";
	$result = db_query_bound( $query, null, $t_reporter_summary_limit );

	$t_reporters = array();
	while( $row = db_fetch_array( $result ) ) {
		$t_reporters[] = $row['reporter_id'];
	}

	user_cache_array_rows( $t_reporters );

	foreach( $t_reporters as $t_reporter ) {
		$v_reporter_id = $t_reporter;
		$query = "SELECT COUNT(id) as bugcount, status FROM $t_mantis_bug_table
					WHERE reporter_id=" . db_param() . "
					AND $specific_where
					GROUP BY status
					ORDER BY status";
		$result2 = db_query_bound( $query, array( $v_reporter_id ) );

		$last_reporter = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
		$t_closed_val = config_get( 'bug_closed_status_threshold' );

		while( $row2 = db_fetch_array( $result2 ) ) {
			$t_bugs_total += $row2['bugcount'];
			if( $t_closed_val <= $row2['status'] ) {
				$t_bugs_closed += $row2['bugcount'];
			}
			else if( $t_resolved_val <= $row2['status'] ) {
				$t_bugs_resolved += $row2['bugcount'];
			} else {
				$t_bugs_open += $row2['bugcount'];
			}
		}

		if( 0 < $t_bugs_total ) {
			$t_user = string_display_line( user_get_name( $v_reporter_id ) );

			$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_REPORTER_ID . '=' . $v_reporter_id;
			if( 0 < $t_bugs_open ) {
				$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
			}
			if( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
			}
			if( 0 < $t_bugs_closed ) {
				$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
			}
			if( 0 < $t_bugs_total ) {
				$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
			}

			summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
		}
	}
}

# print a bug count per category
function summary_print_by_category() {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_category_table = db_get_table( 'mantis_category_table' );
	$t_mantis_project_table = db_get_table( 'mantis_project_table' );
	$t_summary_category_include_project = config_get( 'summary_category_include_project' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$specific_where = trim( helper_project_specific_where( $t_project_id ) );
	if( '1<>1' == $specific_where ) {
		return;
	}
	$t_project_query = ( ON == $t_summary_category_include_project ) ? 'b.project_id, ' : '';

	$query = "SELECT COUNT(b.id) as bugcount, $t_project_query c.name AS category_name, category_id, b.status
				FROM $t_mantis_bug_table b
				JOIN $t_mantis_category_table AS c ON b.category_id=c.id
				WHERE b.$specific_where
				GROUP BY $t_project_query c.name, b.category_id, b.status
				ORDER BY $t_project_query c.name";

	$result = db_query_bound( $query );

	$last_category_name = -1;
	$last_category_id = -1;
	$last_project = -1;
	$t_bugs_open = 0;
	$t_bugs_resolved = 0;
	$t_bugs_closed = 0;
	$t_bugs_total = 0;

	$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
	$t_closed_val = config_get( 'bug_closed_status_threshold' );

	while( $row = db_fetch_array( $result ) ) {
		$v_category_id = $row['category_id'];
		$v_category_name = $row['category_name'];

		if(( $v_category_name != $last_category_name ) && ( $last_category_name != -1 ) ) {
			$label = string_display_line( $last_category_name );
			if(( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
				$label = sprintf( '[%s] %s', string_display_line( project_get_name( $last_project ) ), $label );
			}

			$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_CATEGORY . '=' . urlencode( $last_category_name );
			if( 0 < $t_bugs_open ) {
				$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
			}
			if( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
			}
			if( 0 < $t_bugs_closed ) {
				$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
			}
			if( 0 < $t_bugs_total ) {
				$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
			}

			summary_helper_print_row( $label, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

			$t_bugs_open = 0;
			$t_bugs_resolved = 0;
			$t_bugs_closed = 0;
			$t_bugs_total = 0;
		}

		$t_bugs_total += $row['bugcount'];
		if( $t_closed_val <= $row['status'] ) {
			$t_bugs_closed += $row['bugcount'];
		}
		else if( $t_resolved_val <= $row['status'] ) {
			$t_bugs_resolved += $row['bugcount'];
		} else {
			$t_bugs_open += $row['bugcount'];
		}

		$last_category_id = $v_category_id;
		$last_category_name = $v_category_name;
		if(( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
			$last_project = $row['project_id'];
		}
	}

	if( 0 < $t_bugs_total ) {
		$label = string_display_line( $last_category_name );
		if(( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
			$label = sprintf( '[%s] %s', string_display_line( project_get_name( $last_project ) ), $label );
		}

		$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_CATEGORY . '=' . urlencode( $last_category_name );
		if( !is_blank( $t_bug_link ) ) {
			if( 0 < $t_bugs_open ) {
				$t_bugs_open = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_resolved_val . '">' . $t_bugs_open . '</a>';
			}
			if( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_closed_val . '">' . $t_bugs_resolved . '</a>';
			}
			if( 0 < $t_bugs_closed ) {
				$t_bugs_closed = $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_closed . '</a>';
			}
			if( 0 < $t_bugs_total ) {
				$t_bugs_total = $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">' . $t_bugs_total . '</a>';
			}
		}

		summary_helper_print_row( $label, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
	}
}

# print bug counts by project
function summary_print_by_project( $p_projects = null, $p_level = 0, $p_cache = null ) {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_project_table = db_get_table( 'mantis_project_table' );

	$t_project_id = helper_get_current_project();

	if( null == $p_projects ) {
		if( ALL_PROJECTS == $t_project_id ) {
			$p_projects = current_user_get_accessible_projects();
		} else {
			$p_projects = Array(
				$t_project_id,
			);
		}
	}

	# Retrieve statistics one time to improve performance.
	if( null === $p_cache ) {
		$query = "SELECT project_id, status, COUNT( status ) AS bugcount
					FROM $t_mantis_bug_table
					GROUP BY project_id, status";

		$result = db_query_bound( $query );
		$p_cache = Array();

		$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
		$t_closed_val = config_get( 'bug_closed_status_threshold' );

		while( $row = db_fetch_array( $result ) ) {
			$t_project_id = $row['project_id'];
			$t_status = $row['status'];
			$t_bugcount = $row['bugcount'];

			if ( $t_closed_val <= $t_status ) {
				if ( isset( $p_cache[$t_project_id]['closed'] ) ) {
					$p_cache[$t_project_id]['closed'] += $t_bugcount;
				} else {
					$p_cache[$t_project_id]['closed'] = $t_bugcount;
				}
			} else if ( $t_resolved_val <= $t_status ) {
				if ( isset( $p_cache[$t_project_id]['resolved'] ) ) {
					$p_cache[$t_project_id]['resolved'] += $t_bugcount;
				} else {
					$p_cache[$t_project_id]['resolved'] = $t_bugcount;
				}
			} else {
				if ( isset( $p_cache[$t_project_id]['open'] ) ) {
					$p_cache[$t_project_id]['open'] += $t_bugcount;
				} else {
					$p_cache[$t_project_id]['open'] = $t_bugcount;
				}
			}
		}
	}

	foreach( $p_projects as $t_project ) {
		$t_name = str_repeat( "&raquo; ", $p_level ) . string_display_line( project_get_name( $t_project ) );

		$t_pdata = isset( $p_cache[$t_project] ) ? $p_cache[$t_project] : array( 'open' => 0, 'resolved' => 0, 'closed' => 0 );

		$t_bugs_open = isset( $t_pdata['open'] ) ? $t_pdata['open'] : 0;
		$t_bugs_resolved = isset( $t_pdata['resolved'] ) ? $t_pdata['resolved'] : 0;
		$t_bugs_closed = isset( $t_pdata['closed'] ) ? $t_pdata['closed'] : 0;
		$t_bugs_total = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;

		summary_helper_print_row( $t_name, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

		if ( count( project_hierarchy_get_subprojects ( $t_project ) ) > 0 ) {
			$t_subprojects = current_user_get_accessible_subprojects( $t_project );

			if( count( $t_subprojects ) > 0 ) {
				summary_print_by_project( $t_subprojects, $p_level + 1, $p_cache );
			}
		}
	}
}

# Print developer / resolution report
function summary_print_developer_resolution( $p_resolution_enum_string ) {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_user_table = db_get_table( 'mantis_user_table' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	# Get the resolution values ot use
	$c_res_s = MantisEnum::getValues( $p_resolution_enum_string );
	$enum_res_count = count( $c_res_s );

	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	$specific_where .= ' AND handler_id > 0';

	# Get all of the bugs and split them up into an array
	$query = "SELECT COUNT(id) as bugcount, handler_id, resolution
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY handler_id, resolution
				ORDER BY handler_id, resolution";
	$result = db_query_bound( $query );

	$t_handler_res_arr = array();
	$t_arr = db_fetch_array( $result );
	while( $t_arr ) {
		if( !isset( $t_handler_res_arr[$t_arr['handler_id']] ) ) {
			$t_handler_res_arr[$t_arr['handler_id']] = array();
			$t_handler_res_arr[$t_arr['handler_id']]['total'] = 0;
		}
		if( !isset( $t_handler_res_arr[$t_arr['handler_id']][$t_arr['resolution']] ) ) {
			$t_handler_res_arr[$t_arr['handler_id']][$t_arr['resolution']] = 0;
		}
		$t_handler_res_arr[$t_arr['handler_id']][$t_arr['resolution']] += $t_arr['bugcount'];
		$t_handler_res_arr[$t_arr['handler_id']]['total'] += $t_arr['bugcount'];

		$t_arr = db_fetch_array( $result );
	}

	$t_filter_prefix = config_get( 'bug_count_hyperlink_prefix' );
	$t_row_count = 0;

	# We now have a multi dimensional array of users and resolutions, with the value of each resolution for each user
	foreach( $t_handler_res_arr as $t_handler_id => $t_arr2 ) {

		# Only print developers who have had at least one bug assigned to them. This helps
		# prevent divide by zeroes, showing developers not on this project, and showing
		# users that aren't actually developers...

		if( $t_arr2['total'] > 0 ) {
			echo '<tr align="center" ' . helper_alternate_class( $t_row_count ) . '>';
			$t_row_count++;
			echo '<td>';
			echo summary_helper_get_developer_label( $t_handler_id );
			echo '</td>';

			# We need to track the percentage of bugs that are considered fixed, as well as
			# those that aren't considered bugs to begin with (when looking at %age)
			$t_bugs_fixed = 0;
			$t_bugs_notbugs = 0;
			for( $j = 0;$j < $enum_res_count;$j++ ) {
				$res_bug_count = 0;

				if( isset( $t_arr2[$c_res_s[$j]] ) ) {
					$res_bug_count = $t_arr2[$c_res_s[$j]];
				}

				echo '<td>';
				if( 0 < $res_bug_count ) {
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $t_handler_id;
					$t_bug_link = $t_bug_link . '&amp;' . FILTER_PROPERTY_RESOLUTION_ID . '=' . $c_res_s[$j] . '">';
					echo $t_bug_link . $res_bug_count . '</a>';
				} else {
					echo $res_bug_count;
				}
				echo '</td>';

				if( $c_res_s[$j] >= config_get( 'bug_resolution_fixed_threshold' ) ) {
					if ( $c_res_s[$j] < config_get( 'bug_resolution_not_fixed_threshold' ) ) {
						# Count bugs with a resolution between fixed and not fixed thresholds
						$t_bugs_fixed += $res_bug_count;
					} else {
						# Count bugs with a resolution above the not fixed threshold
						$t_bugs_notbugs += $res_bug_count;
					}
				}

			}

			$t_percent_fixed = 0;
			if(( $t_arr2['total'] - $t_bugs_notbugs ) > 0 ) {
				$t_percent_fixed = ( $t_bugs_fixed / ( $t_arr2['total'] - $t_bugs_notbugs ) );
			}
			echo '<td>';
			printf( '% 1.0f%%', ( $t_percent_fixed * 100 ) );
			echo '</td>';
			echo '</tr>';
		}
	}
}

# Print reporter / resolution report
function summary_print_reporter_resolution( $p_resolution_enum_string ) {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_user_table = db_get_table( 'mantis_user_table' );
	$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	# Get the resolution values ot use
	$c_res_s = MantisEnum::getValues( $p_resolution_enum_string );
	$enum_res_count = count( $c_res_s );

	# Checking if it's a per project statistic or all projects
	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	# Get all of the bugs and split them up into an array
	$query = "SELECT COUNT(id) as bugcount, reporter_id, resolution
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id, resolution";
	$result = db_query_bound( $query );

	$t_reporter_res_arr = array();
	$t_reporter_bugcount_arr = array();
	$t_arr = db_fetch_array( $result );
	while( $t_arr ) {
		if( !isset( $t_reporter_res_arr[$t_arr['reporter_id']] ) ) {
			$t_reporter_res_arr[$t_arr['reporter_id']] = array();
			$t_reporter_bugcount_arr[$t_arr['reporter_id']] = 0;
		}
		if( !isset( $t_reporter_res_arr[$t_arr['reporter_id']][$t_arr['resolution']] ) ) {
			$t_reporter_res_arr[$t_arr['reporter_id']][$t_arr['resolution']] = 0;
		}
		$t_reporter_res_arr[$t_arr['reporter_id']][$t_arr['resolution']] += $t_arr['bugcount'];
		$t_reporter_bugcount_arr[$t_arr['reporter_id']] += $t_arr['bugcount'];

		$t_arr = db_fetch_array( $result );
	}

	# Sort our total bug count array so that the reporters with the highest number of bugs are listed first,
	arsort( $t_reporter_bugcount_arr );

	$t_row_count = 0;

	# We now have a multi dimensional array of users and resolutions, with the value of each resolution for each user
	foreach( $t_reporter_bugcount_arr as $t_reporter_id => $t_total_user_bugs ) {

		# Limit the number of reporters listed
		if( $t_row_count > $t_reporter_summary_limit ) {
			break;
		}

		# Only print reporters who have reported at least one bug. This helps
		# prevent divide by zeroes, showing reporters not on this project, and showing
		# users that aren't actually reporters...
		if( $t_total_user_bugs > 0 ) {
			$t_arr2 = $t_reporter_res_arr[$t_reporter_id];

			echo '<tr align="center" ' . helper_alternate_class( $t_row_count ) . '>';
			$t_row_count++;
			echo '<td>';
			echo string_display_line( user_get_name( $t_reporter_id ) );
			echo '</td>';

			# We need to track the percentage of bugs that are considered fix, as well as
			# those that aren't considered bugs to begin with (when looking at %age)
			$t_bugs_fixed = 0;
			$t_bugs_notbugs = 0;
			for( $j = 0;$j < $enum_res_count;$j++ ) {
				$res_bug_count = 0;

				if( isset( $t_arr2[$c_res_s[$j]] ) ) {
					$res_bug_count = $t_arr2[$c_res_s[$j]];
				}

				echo '<td>';
				if( 0 < $res_bug_count ) {
					$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_REPORTER_ID . '=' . $t_reporter_id;
					$t_bug_link = $t_bug_link . '&amp;' . FILTER_PROPERTY_RESOLUTION_ID . '=' . $c_res_s[$j] . '">';
					echo $t_bug_link . $res_bug_count . '</a>';
				} else {
					echo $res_bug_count;
				}
				echo '</td>';

				if( $c_res_s[$j] >= config_get( 'bug_resolution_fixed_threshold' ) ) {
					if ( $c_res_s[$j] < config_get( 'bug_resolution_not_fixed_threshold' ) ) {
						# Count bugs with a resolution between fixed and not fixed thresholds
						$t_bugs_fixed += $res_bug_count;
					} else {
						# Count bugs with a resolution above the not fixed threshold
						$t_bugs_notbugs += $res_bug_count;
					}
				}

			}

			$t_percent_errors = 0;
			if( $t_total_user_bugs > 0 ) {
				$t_percent_errors = ( $t_bugs_notbugs / $t_total_user_bugs );
			}
			echo '<td>';
			printf( '% 1.0f%%', ( $t_percent_errors * 100 ) );
			echo '</td>';
			echo '</tr>';
		}
	}
}

# Print reporter effectiveness report
function summary_print_reporter_effectiveness( $p_severity_enum_string, $p_resolution_enum_string ) {
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_mantis_user_table = db_get_table( 'mantis_user_table' );
	$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	$t_severity_multipliers = config_get( 'severity_multipliers' );
	$t_resolution_multipliers = config_get( 'resolution_multipliers' );

	# Get the severity values to use
	$c_sev_s = MantisEnum::getValues( $p_severity_enum_string );
	$enum_sev_count = count( $c_sev_s );

	# Get the resolution values to use
	$c_res_s = MantisEnum::getValues( $p_resolution_enum_string );
	$enum_res_count = count( $c_res_s );

	# Checking if it's a per project statistic or all projects
	$specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $specific_where ) {
		return;
	}

	# Get all of the bugs and split them up into an array
	$query = "SELECT COUNT(id) as bugcount, reporter_id, resolution, severity
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id, resolution, severity";
	$result = db_query_bound( $query );

	$t_reporter_ressev_arr = array();
	$t_reporter_bugcount_arr = array();
	$t_arr = db_fetch_array( $result );
	while( $t_arr ) {
		if( !isset( $t_reporter_ressev_arr[$t_arr['reporter_id']] ) ) {
			$t_reporter_ressev_arr[$t_arr['reporter_id']] = array();
			$t_reporter_bugcount_arr[$t_arr['reporter_id']] = 0;
		}
		if( !isset( $t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']] ) ) {
			$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']] = array();
			$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']]['total'] = 0;
		}
		if( !isset( $t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']][$t_arr['resolution']] ) ) {
			$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']][$t_arr['resolution']] = 0;
		}
		$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']][$t_arr['resolution']] += $t_arr['bugcount'];
		$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']]['total'] += $t_arr['bugcount'];
		$t_reporter_bugcount_arr[$t_arr['reporter_id']] += $t_arr['bugcount'];

		$t_arr = db_fetch_array( $result );
	}

	# Sort our total bug count array so that the reporters with the highest number of bugs are listed first,
	arsort( $t_reporter_bugcount_arr );

	$t_row_count = 0;

	# We now have a multi dimensional array of users, resolutions and severities, with the
	# value of each resolution and severity for each user
	foreach( $t_reporter_bugcount_arr as $t_reporter_id => $t_total_user_bugs ) {

		# Limit the number of reporters listed
		if( $t_row_count > $t_reporter_summary_limit ) {
			break;
		}

		# Only print reporters who have reported at least one bug. This helps
		# prevent divide by zeroes, showing reporters not on this project, and showing
		# users that aren't actually reporters...
		if( $t_total_user_bugs > 0 ) {
			$t_arr2 = $t_reporter_ressev_arr[$t_reporter_id];

			echo '<tr ' . helper_alternate_class( $t_row_count ) . '>';
			$t_row_count++;
			echo '<td>';
			echo string_display_line( user_get_name( $t_reporter_id ) );
			echo '</td>';

			$t_total_severity = 0;
			$t_total_errors = 0;
			for( $j = 0; $j < $enum_sev_count; $j++ ) {
				if( !isset( $t_arr2[$c_sev_s[$j]] ) ) {
					continue;
				}

				$sev_bug_count = $t_arr2[$c_sev_s[$j]]['total'];
				$t_sev_mult = 1;
				if( $t_severity_multipliers[$c_sev_s[$j]] ) {
					$t_sev_mult = $t_severity_multipliers[$c_sev_s[$j]];
				}

				if( $sev_bug_count > 0 ) {
					$t_total_severity += ( $sev_bug_count * $t_sev_mult );
				}

				foreach( $t_resolution_multipliers as $t_res => $t_res_mult ) {
					if( isset( $t_arr2[$c_sev_s[$j]][$t_res] ) ) {
						$t_total_errors += ( $t_sev_mult * $t_res_mult );
					}
				}
			}
			echo '<td>';
			echo $t_total_severity;
			echo '</td>';
			echo '<td>';
			echo $t_total_errors;
			echo '</td>';
			echo '<td>';
			print( $t_total_severity - $t_total_errors );
			echo '</td>';
			echo '</tr>';
		}
	}
}
