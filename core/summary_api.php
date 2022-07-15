<?php
# MantisBT - A PHP based bugtracking system

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
 * Summary API
 *
 * @package CoreAPI
 * @subpackage SummaryAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses filter_constants_inc.php
 * @uses helper_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

/**
 * Print row with percentage in summary table
 *
 * @param string $p_label    The summary row label.
 * @param string $p_open     Count of open issues - normally string with hyperlink to filter.
 * @param string $p_resolved Count of resolved issues - normally string with hyperlink to filter.
 * @param string $p_closed   Count of closed issues - normally string with hyperlink to filter.
 * @param string $p_total    Count of total issues - normally string with hyperlink to filter.
 * @param string $p_resolved_ratio  Ratio of resolved
 * @param string $p_ratio    Ratio of total bugs
 * @return void
 */
function summary_helper_print_row( $p_label, $p_open, $p_resolved, $p_closed, $p_total, $p_resolved_ratio, $p_ratio) {
	echo '<tr>';
	printf( '<td class="width50">%s</td>', $p_label );
	printf( '<td class="width12 align-right">%s</td>', $p_open );
	printf( '<td class="width12 align-right">%s</td>', $p_resolved );
	printf( '<td class="width12 align-right">%s</td>', $p_closed );
	printf( '<td class="width12 align-right">%s</td>', $p_total );
	printf( '<td class="width12 align-right">%s</td>', $p_resolved_ratio );
	printf( '<td class="width12 align-right">%s</td>', $p_ratio );
	echo '</tr>';
}

/**
 * Returns a string representation of the user, together with a link to the issues
 * acted on by the user ( reported, handled or commented on )
 *
 * @param integer $p_user_id A valid user identifier.
 * @param array $p_filter Filter array.
 * @return string
 */
function summary_helper_get_developer_label( $p_user_id, array $p_filter = null ) {
	$t_user = string_display_line( user_get_name( $p_user_id ) );

	$t_link_prefix = summary_get_link_prefix( $p_filter );

	return '<a class="subtle" href="' . $t_link_prefix
		. '&amp;' . FILTER_PROPERTY_REPORTER_ID . '=' . $p_user_id
		. '&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $p_user_id
		. '&amp;' . FILTER_PROPERTY_NOTE_USER_ID . '=' . $p_user_id
		. '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE
		. '&amp;' . FILTER_PROPERTY_MATCH_TYPE . '=' . FILTER_MATCH_ANY
		. '">' . $t_user . '</a>';

}

/**
 * Calculate bug status count according to 'open', 'resolved' and 'closed',
 * then put the numbers into $p_cache array
 *
 * @param array &$p_cache    The cache array.
 * @param string $p_key      The key of the array.
 * @param string $p_status   The status of issues.
 * @param integer $p_bugcount The bug count of $p_status issues.
 * @return void
 */
function summary_helper_build_bugcount( &$p_cache, $p_key, $p_status, $p_bugcount ) {
	$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
	$t_closed_val = config_get( 'bug_closed_status_threshold' );

	if( $t_closed_val <= $p_status ) {
		if( isset( $p_cache[$p_key]['closed'] ) ) {
			$p_cache[$p_key]['closed'] += $p_bugcount;
		} else {
			$p_cache[$p_key]['closed'] = $p_bugcount;
		}
	} else if( $t_resolved_val <= $p_status ) {
		if( isset( $p_cache[$p_key]['resolved'] ) ) {
			$p_cache[$p_key]['resolved'] += $p_bugcount;
		} else {
			$p_cache[$p_key]['resolved'] = $p_bugcount;
		}
	} else {
		if( isset( $p_cache[$p_key]['open'] ) ) {
			$p_cache[$p_key]['open'] += $p_bugcount;
		} else {
			$p_cache[$p_key]['open'] = $p_bugcount;
		}
	}
}
/** 
 * Build bug links for 'open', 'resolved' and 'closed' issue counts
 * 
 * @param string $p_bug_link            The base bug link.
 * @param string &$p_bugs_open          The open bugs count, return open bugs link.
 * @param string &$p_bugs_resolved      The resolved bugs count, return resolved bugs link.
 * @param string &$p_bugs_closed        The closed bugs count, return closed bugs link.
 * @param string &$p_bugs_total         The total bugs count, return total bugs link.
 * @return void 
 */
function summary_helper_build_buglinks( $p_bug_link, &$p_bugs_open, &$p_bugs_resolved, &$p_bugs_closed, &$p_bugs_total) {
	$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
	$t_closed_val = config_get( 'bug_closed_status_threshold' );

	if( 0 < $p_bugs_open ) {
		$p_bugs_open = '<a class="subtle" href="' . $p_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_resolved_val . '">' . $p_bugs_open . '</a>';
	}
	if( 0 < $p_bugs_resolved ) {
		$p_bugs_resolved = '<a class="subtle" href="' . $p_bug_link . '&amp;' . FILTER_PROPERTY_STATUS . '=' . $t_resolved_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_closed_val . '">' . $p_bugs_resolved . '</a>';
	}
	if( 0 < $p_bugs_closed ) {
		$p_bugs_closed = '<a class="subtle" href="' . $p_bug_link . '&amp;' . FILTER_PROPERTY_STATUS . '=' . $t_closed_val . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">' . $p_bugs_closed . '</a>';
	}
	if( 0 < $p_bugs_total ) {
		$p_bugs_total = '<a class="subtle" href="' . $p_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">' . $p_bugs_total . '</a>';
	}	
}

/**
 * Calculate bug ratio 
 * @param integer $p_bugs_open            The open bugs count.
 * @param integer $p_bugs_resolved        The resolved bugs count.
 * @param integer $p_bugs_closed          The closed bugs count.
 * @param integer $p_bugs_total_count     The total bugs count.
 * @return array  array of ($t_bugs_resolved_ratio, $t_bugs_ratio)
 */
function summary_helper_get_bugratio( $p_bugs_open, $p_bugs_resolved, $p_bugs_closed, $p_bugs_total_count) {
	$t_bugs_total = $p_bugs_open + $p_bugs_resolved + $p_bugs_closed;
	$t_bugs_resolved_ratio = ( $p_bugs_resolved + $p_bugs_closed ) / ( $t_bugs_total == 0 ? 1 : $t_bugs_total );
	$t_bugs_ratio = $t_bugs_total / ( $p_bugs_total_count == 0 ? 1 : $p_bugs_total_count );
	$t_bugs_resolved_ratio = sprintf( "%.1f%%", $t_bugs_resolved_ratio * 100 );
	$t_bugs_ratio = sprintf( "%.1f%%", $t_bugs_ratio * 100 );	
	return array($t_bugs_resolved_ratio, $t_bugs_ratio);
}

/**
 * Used in summary reports - this function prints out the summary for the given enum setting.
 * The enum field name is passed in through $p_enum.
 * A filter can be used to limit the visibility.
 *
 * @param string $p_enum Enum field name.
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_enum( $p_enum, array $p_filter = null ) {
	$t_project_id = helper_get_current_project();

	$t_project_filter = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_project_filter ) {
		return;
	}
	$t_link_prefix = summary_get_link_prefix( $p_filter );

	$t_status_query = ( 'status' == $p_enum ) ? '' : ' ,status ';
	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(id) as bugcount, ' . $p_enum . ' ' . $t_status_query
		. ' FROM {bug} WHERE ' . $t_project_filter;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY ' . $p_enum . ' ' . $t_status_query
		. ' ORDER BY ' . $p_enum . ' ' . $t_status_query;
	$t_query->sql( $t_sql );

	$t_cache = array();
	$t_bugs_total_count = 0;

	while( $t_row = $t_query->fetch() ) {
		$t_enum = $t_row[$p_enum];
		$t_status = $t_row['status'];
		$t_bugcount = $t_row['bugcount'];
		$t_bugs_total_count += $t_bugcount;
		
		summary_helper_build_bugcount( $t_cache, $t_enum, $t_status, $t_bugcount );
	}

	switch( $p_enum ) {
		case 'status':
			$t_filter_property = FILTER_PROPERTY_STATUS;
			break;
		case 'severity':
			$t_filter_property = FILTER_PROPERTY_SEVERITY;
			break;
		case 'resolution':
			$t_filter_property = FILTER_PROPERTY_RESOLUTION;
			break;
		case 'priority':
			$t_filter_property = FILTER_PROPERTY_PRIORITY;
			break;
		default:
			# Unknown Enum type
			trigger_error( ERROR_GENERIC, ERROR );
	}

	foreach( $t_cache as $t_enum => $t_item) {
		# Build up the hyperlinks to bug views
		$t_bugs_open = isset( $t_item['open'] ) ? $t_item['open'] : 0;
		$t_bugs_resolved = isset( $t_item['resolved'] ) ? $t_item['resolved'] : 0;
		$t_bugs_closed = isset( $t_item['closed'] ) ? $t_item['closed'] : 0;
		$t_bugs_total = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;
		$t_bugs_ratio = summary_helper_get_bugratio( $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total_count);

		$t_bug_link = $t_link_prefix . '&amp;' . $t_filter_property . '=' . $t_enum;

		if( !is_blank( $t_bug_link ) ) {
			$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
			$t_closed_val = config_get( 'bug_closed_status_threshold' );
			
			if( 0 < $t_bugs_open ) {
				$t_bugs_open = '<a class="subtle" href="' . $t_bug_link
					. '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_resolved_val . '">'
					. $t_bugs_open . '</a>';
			} else {
				if( ( 'status' == $p_enum ) && ( $t_enum >= $t_resolved_val ) ) {
					$t_bugs_open = '-';
				}
			}
			if( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = '<a class="subtle" href="' . $t_bug_link
					# Only add status filter if not already part of the link
					. ( 'status' != $p_enum ? '&amp;' . FILTER_PROPERTY_STATUS . '=' . $t_resolved_val : '' )
					. '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_closed_val . '">'
					. $t_bugs_resolved . '</a>';
			} else {
				if( ( 'status' == $p_enum ) && (( $t_enum < $t_resolved_val ) || ( $t_enum >= $t_closed_val ) ) ) {
					$t_bugs_resolved = '-';
				}
			}
			if( 0 < $t_bugs_closed ) {
				$t_bugs_closed = '<a class="subtle" href="' . $t_bug_link
					# Only add status filter if not already part of the link
					. ( 'status' != $p_enum ? '&amp;' . FILTER_PROPERTY_STATUS . '=' . $t_closed_val : '' )
					. '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">'
					. $t_bugs_closed . '</a>';
			} else {
				if( ( 'status' == $p_enum ) && ( $t_enum < $t_closed_val ) ) {
					$t_bugs_closed = '-';
				}
			}
			if( 0 < $t_bugs_total ) {
				$t_bugs_total = '<a class="subtle" href="' . $t_bug_link
					. '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '='
					. META_FILTER_NONE . '">' . $t_bugs_total . '</a>';
			}	
			if( 'status' == $p_enum )  $t_bugs_ratio[0] = '-';		
		}
		summary_helper_print_row( get_enum_element( $p_enum, $t_enum ), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total, $t_bugs_ratio[0], $t_bugs_ratio[1] );
	}
}

/**
 * Print list of open bugs with the highest activity score the score is calculated assigning
 * one "point" for each history event associated with the bug.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_activity( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}
	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(h.id) as count, b.id, b.summary, b.view_state'
		. ' FROM {bug} b JOIN {bug_history} h ON h.bug_id = b.id'
		. ' WHERE b.status < ' . $t_query->param( (int)$t_resolved )
		. ' AND ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND b.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY h.bug_id, b.id, b.summary, b.last_updated, b.view_state'
		. ' ORDER BY count DESC, b.last_updated DESC';
	$t_query->sql( $t_sql );

	$t_count = 0;
	$t_private_bug_threshold = config_get( 'private_bug_threshold' );
	$t_summarydata = array();
	$t_summarybugs = array();
	while( $t_row = $t_query->fetch() ) {
		# Skip private bugs unless user has proper permissions
		if( ( VS_PRIVATE == $t_row['view_state'] ) && ( false == access_has_bug_level( $t_private_bug_threshold, $t_row['id'] ) ) ) {
			continue;
		}

		if( $t_count++ == 10 ) {
			break;
		}

		$t_summarydata[] = array(
			'id' => $t_row['id'],
			'summary' => $t_row['summary'],
			'count' => $t_row['count'],
		);
		$t_summarybugs[] = $t_row['id'];
	}

	bug_cache_array_rows( $t_summarybugs );

	foreach( $t_summarydata as $t_row ) {
		$t_bugid = string_get_bug_view_link( $t_row['id'], false );
		$t_summary = string_display_line( $t_row['summary'] );
		$t_notescount = $t_row['count'];

		echo '<tr>' . "\n";
		echo '<td class="small">' . $t_bugid . ' - ' . $t_summary . '</td><td class="align-right">' . $t_notescount . '</td>' . "\n";
		echo '</tr>' . "\n";
	}
}

/**
 * Print list of bugs opened from the longest time.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_age( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}
	$t_query = new DBQuery();
	$t_sql = 'SELECT * FROM {bug} WHERE status < ' . $t_query->param( (int)$t_resolved )
		. ' AND ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' ORDER BY date_submitted ASC, priority DESC';
	$t_query->sql( $t_sql );

	$t_count = 0;
	$t_private_bug_threshold = config_get( 'private_bug_threshold' );

	while( $t_row = $t_query->fetch() ) {
		# as we select all from bug_table, inject into the cache.
		bug_cache_database_result( $t_row );

		# Skip private bugs unless user has proper permissions
		if( ( VS_PRIVATE == bug_get_field( $t_row['id'], 'view_state' ) ) && ( false == access_has_bug_level( $t_private_bug_threshold, $t_row['id'] ) ) ) {
			continue;
		}

		if( $t_count++ == 10 ) {
			break;
		}

		$t_bugid = string_get_bug_view_link( $t_row['id'], false );
		$t_summary = string_display_line( $t_row['summary'] );
		$t_days_open = intval( ( time() - $t_row['date_submitted'] ) / SECONDS_PER_DAY );

		echo '<tr>' . "\n";
		echo '<td class="small">' . $t_bugid . ' - ' . $t_summary . '</td><td class="align-right">' . $t_days_open . '</td>' . "\n";
		echo '</tr>' . "\n";
	}
}

/**
 * print bug counts by assigned to each developer.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_developer( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();

	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}

	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(id) as bugcount, handler_id, status'
		. ' FROM {bug} WHERE handler_id>0 AND ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY handler_id, status'
		. ' ORDER BY handler_id, status';
	$t_query->sql( $t_sql );

	$t_summaryusers = array();
	$t_cache = array();
	$t_bugs_total_count = 0;

	while( $t_row = $t_query->fetch() ) {
		$t_summaryusers[] = $t_row['handler_id'];
		$t_status = $t_row['status'];
		$t_bugcount = $t_row['bugcount'];
		$t_bugs_total_count += $t_bugcount;
		$t_label = $t_row['handler_id'];

		summary_helper_build_bugcount( $t_cache, $t_label, $t_status, $t_bugcount );
	}

	user_cache_array_rows( array_unique( $t_summaryusers ) );

	foreach( $t_cache as $t_label => $t_item) {
		# Build up the hyperlinks to bug views
		$t_bugs_open = isset( $t_item['open'] ) ? $t_item['open'] : 0;
		$t_bugs_resolved = isset( $t_item['resolved'] ) ? $t_item['resolved'] : 0;
		$t_bugs_closed = isset( $t_item['closed'] ) ? $t_item['closed'] : 0;
		$t_bugs_total = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;
		$t_bugs_ratio = summary_helper_get_bugratio( $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total_count);

		$t_link_prefix = summary_get_link_prefix( $p_filter );

		$t_bug_link = $t_link_prefix . '&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $t_label;
		$t_label = summary_helper_get_developer_label( $t_label, $p_filter );
		summary_helper_build_buglinks( $t_bug_link, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
		summary_helper_print_row( $t_label, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total, $t_bugs_ratio[0], $t_bugs_ratio[1] );
	}
}

/**
 * Print bug counts by reporter id.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_reporter( array $p_filter = null ) {
	$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

	$t_project_id = helper_get_current_project();

	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}
	$t_query = new DBQuery();
	$t_sql = 'SELECT reporter_id, COUNT(*) as num FROM {bug} WHERE ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY reporter_id ORDER BY num DESC';
	$t_query->sql( $t_sql );

	$t_reporters = array();
	$t_bugs_total_count = 0;
	$t_reporters_count = 0;
	while( $t_row = $t_query->fetch() ) {
		$t_reporters[] = (int)$t_row['reporter_id'];
		$t_bugs_total_count += $t_row['num'];
		$t_reporters_count++;
		if( $t_reporters_count == $t_reporter_summary_limit ) {
			break;
		}
	}

	if( empty( $t_reporters ) ) {
		return;
	}
	user_cache_array_rows( $t_reporters );

	$t_query = new DBQuery();
	$t_sql = 'SELECT reporter_id, status, COUNT(id) AS bugcount FROM {bug}'
		. ' WHERE ' . $t_query->sql_in( 'reporter_id', $t_reporters )
		. ' AND ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY reporter_id, status ORDER BY reporter_id, status';
	$t_query->sql( $t_sql );

	$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
	$t_closed_status = config_get( 'bug_closed_status_threshold' );
	$t_reporter_stats = array();
	while( $t_row = $t_query->fetch() ) {
		$t_reporter_id = (int)$t_row['reporter_id'];
		if( !isset( $t_reporter_stats[$t_reporter_id] ) ) {
			$t_reporter_stats[$t_reporter_id] = array(
				'open' => 0,
				'resolved' => 0,
				'closed' => 0,
				'total' => 0,
				'reporter_id' => $t_reporter_id
				);
		}
		$t_bugcount = (int)$t_row['bugcount'];
		$t_status = (int)$t_row['status'];
		$t_reporter_stats[$t_reporter_id]['total'] += $t_bugcount;
		if( $t_status >= $t_closed_status ) {
			$t_reporter_stats[$t_reporter_id]['closed'] += $t_bugcount;
		} elseif ( $t_status >= $t_resolved_status ) {
			$t_reporter_stats[$t_reporter_id]['resolved'] += $t_bugcount;
		} else {
			$t_reporter_stats[$t_reporter_id]['open'] += $t_bugcount;
		}
	}

	# calculate ratios
	foreach( $t_reporter_stats as $t_reporter_id => $t_stats ) {
		$t_reporter_stats[$t_reporter_id]['ratios'] =summary_helper_get_bugratio(
				$t_stats['open'],
				$t_stats['resolved'],
				$t_stats['closed'],
				$t_bugs_total_count
				);
	}

	# sort based on total issue count
	# note that after array_multisort, we lose the numeric indexes, but we stored
	# the reporter id inside each sub-array
	array_multisort ( array_column( $t_reporter_stats, 'total' ), SORT_DESC, $t_reporter_stats );

	# print results
	foreach( $t_reporter_stats as $t_stats ) {
		if( $t_stats['total'] == 0 ) {
			continue;
		}
		$t_reporter_id = $t_stats['reporter_id'];
		$t_user = string_display_line( user_get_name( $t_reporter_id ) );
		$t_link_prefix = summary_get_link_prefix( $p_filter );

		$t_bug_link = $t_link_prefix . '&amp;' . FILTER_PROPERTY_REPORTER_ID . '=' . $t_reporter_id;
		if( 0 < $t_stats['open'] ) {
			$t_bugs_open = '<a class="subtle" href="' . $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_resolved_status . '">'. $t_stats['open'] . '</a>';
		} else {
			$t_bugs_open = 0;
		}
		if( 0 < $t_stats['resolved'] ) {
			$t_bugs_resolved = '<a class="subtle" href="' . $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS . '=' . $t_resolved_status . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_closed_status . '">' . $t_stats['resolved'] . '</a>';
		} else {
			$t_bugs_resolved = 0;
		}
		if( 0 < $t_stats['closed'] ) {
			$t_bugs_closed = '<a class="subtle" href="' . $t_bug_link . '&amp;' . FILTER_PROPERTY_STATUS . '=' . $t_closed_status . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">' . $t_stats['closed'] . '</a>';
		} else {
			$t_bugs_closed = 0;
		}
		if( 0 < $t_stats['total'] ) {
			$t_bugs_total = '<a class="subtle" href="' . $t_bug_link . '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">' . $t_stats['total'] . '</a>';
		} else {
			$t_bugs_total = 0;
		}
		$t_bugs_ratio = $t_stats['ratios'];
		summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total, $t_bugs_ratio[0], $t_bugs_ratio[1] );
	}
}

/**
 * Print a bug count per category.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_category( array $p_filter = null ) {
	$t_summary_category_include_project = config_get( 'summary_category_include_project' );

	$t_project_id = helper_get_current_project();

	$t_specific_where = trim( helper_project_specific_where( $t_project_id ) );
	if( '1<>1' == $t_specific_where ) {
		return;
	}
	$t_project_query = ( ON == $t_summary_category_include_project ) ? 'b.project_id, ' : '';

	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(b.id) as bugcount, ' . $t_project_query . ' c.name AS category_name, category_id, b.status'
		. ' FROM {bug} b JOIN {category} c ON b.category_id=c.id'
		. ' WHERE b.' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND b.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY ' . $t_project_query . ' c.name, b.category_id, b.status'
		. ' ORDER BY ' . $t_project_query . ' c.name';
	$t_query->sql( $t_sql );

	$t_cache = array();
	$t_bugs_total_count = 0;

	while( $t_row = $t_query->fetch() ) {
		$t_status = $t_row['status'];
		$t_bugcount = $t_row['bugcount'];
		$t_bugs_total_count += $t_bugcount;
		$t_label = $t_row['category_name'];
		if( ( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
			$t_label = sprintf( '[%s] %s', project_get_name( $t_row['project_id'] ), $t_label );
		} 

		summary_helper_build_bugcount( $t_cache, $t_label, $t_status, $t_bugcount );
	}

	foreach( $t_cache as $t_label => $t_item) {
		# Build up the hyperlinks to bug views
		$t_bugs_open = isset( $t_item['open'] ) ? $t_item['open'] : 0;
		$t_bugs_resolved = isset( $t_item['resolved'] ) ? $t_item['resolved'] :0;
		$t_bugs_closed = isset( $t_item['closed'] ) ? $t_item['closed'] : 0;
		$t_bugs_total = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;
		$t_bugs_ratio = summary_helper_get_bugratio( $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total_count);

		$t_link_prefix = summary_get_link_prefix( $p_filter );

		$t_bug_link = $t_link_prefix . '&amp;' . FILTER_PROPERTY_CATEGORY_ID . '=' . urlencode( $t_label );
		summary_helper_build_buglinks( $t_bug_link, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
		summary_helper_print_row( string_display_line( $t_label ), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total, $t_bugs_ratio[0], $t_bugs_ratio[1] );
	}
}

/**
 * Print bug counts by project.
 * A filter can be used to limit the visibility.
 * @todo check p_cache - static?
 *
 * @param array   $p_projects Array of project id's.
 * @param integer $p_level    Indicates the depth of the project within the sub-project hierarchy.
 * @param array   $p_cache    Summary cache.
 * @param array   $p_filter   Filter array.
 * @return void
 */
function summary_print_by_project( array $p_projects = array(), $p_level = 0, array $p_cache = null, array $p_filter = null ) {
	$t_project_id = helper_get_current_project();

	if( empty( $p_projects ) ) {
		if( ALL_PROJECTS == $t_project_id ) {
			$p_projects = current_user_get_accessible_projects();
		} else {
			$p_projects = array(
				$t_project_id,
			);
		}
	}

	# Retrieve statistics one time to improve performance.
	if( null === $p_cache ) {
		$t_query = new DBQuery();
		$t_sql = 'SELECT project_id, status, COUNT( status ) AS bugcount FROM {bug}';
		if( !empty( $p_filter ) ) {
			$t_subquery = filter_cache_subquery( $p_filter );
			$t_sql .= ' WHERE {bug}.id IN :filter';
			$t_query->bind( 'filter', $t_subquery );
		}
		$t_sql .= ' GROUP BY project_id, status';
		$t_query->sql( $t_sql );

		$p_cache = array();
		$t_bugs_total_count = 0;
		while( $t_row = $t_query->fetch() ) {
			$t_project_id = $t_row['project_id'];
			$t_status = $t_row['status'];
			$t_bugcount = $t_row['bugcount'];
			$t_bugs_total_count += $t_bugcount;

			summary_helper_build_bugcount( $p_cache, $t_project_id, $t_status, $t_bugcount );
		}
		$p_cache["_bugs_total_count_"] = $t_bugs_total_count;
	}

	$t_bugs_total_count = $p_cache["_bugs_total_count_"];
	foreach( $p_projects as $t_project ) {
		$t_name = str_repeat( '&raquo; ', $p_level ) . project_get_name( $t_project );

		$t_pdata = isset( $p_cache[$t_project] ) ? $p_cache[$t_project] : array( 'open' => 0, 'resolved' => 0, 'closed' => 0 );

		$t_bugs_open = isset( $t_pdata['open'] ) ? $t_pdata['open'] : 0;
		$t_bugs_resolved = isset( $t_pdata['resolved'] ) ? $t_pdata['resolved'] : 0;
		$t_bugs_closed = isset( $t_pdata['closed'] ) ? $t_pdata['closed'] : 0;
		$t_bugs_total = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;
		
		$t_bugs_ratio = summary_helper_get_bugratio( $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total_count);

# FILTER_PROPERTY_PROJECT_ID filter by project does not work ??
#		$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_PROJECT_ID . '=' . urlencode( $t_project );
#		summary_helper_build_buglinks( $t_bug_link, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

		summary_helper_print_row( string_display_line( $t_name ), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total, $t_bugs_ratio[0], $t_bugs_ratio[1]);

		if( count( project_hierarchy_get_subprojects( $t_project ) ) > 0 ) {
			$t_subprojects = current_user_get_accessible_subprojects( $t_project );

			if( count( $t_subprojects ) > 0 ) {
				summary_print_by_project( $t_subprojects, $p_level + 1, $p_cache );
			}
		}
	}
}

/**
 * Print developer / resolution report.
 * A filter can be used to limit the visibility.
 *
 * @param string $p_resolution_enum_string Resolution enumeration string value.
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_developer_resolution( $p_resolution_enum_string, array $p_filter = null ) {
	$t_project_id = helper_get_current_project();

	# Get the resolution values to use
	$c_res_s = MantisEnum::getValues( $p_resolution_enum_string );
	$t_enum_res_count = count( $c_res_s );

	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}

	$t_specific_where .= ' AND handler_id > 0';

	# Get all of the bugs and split them up into an array
	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(id) as bugcount, handler_id, resolution'
		. ' FROM {bug} WHERE ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY handler_id, resolution'
		. ' ORDER BY handler_id, resolution';
	$t_query->sql( $t_sql );

	$t_handler_res_arr = array();
	$t_arr = $t_query->fetch();
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

		$t_arr = $t_query->fetch();
	}

	# Sort array so devs with highest number of bugs are listed first
	uasort( $t_handler_res_arr,
		function( $a, $b ) {
			return $b['total'] - $a['total'];
		}
	);

	$t_threshold_fixed = config_get( 'bug_resolution_fixed_threshold' );
	$t_threshold_notfixed = config_get( 'bug_resolution_not_fixed_threshold' );

	$t_link_prefix = summary_get_link_prefix( $p_filter );

	$t_row_count = 0;

	# We now have a multi dimensional array of users and resolutions, with the value of each resolution for each user
	foreach( $t_handler_res_arr as $t_handler_id => $t_arr2 ) {
		$t_total = $t_arr2['total'];

		# Only print developers who have had at least one bug assigned to them. This helps
		# prevent divide by zeroes, showing developers not on this project, and showing
		# users that aren't actually developers...

		if( $t_total > 0 ) {
			echo '<tr>';
			$t_row_count++;
			echo '<td>';
			echo summary_helper_get_developer_label( $t_handler_id, $p_filter );
			echo "</td>\n";

			# We need to track the percentage of bugs that are considered fixed, as well as
			# those that aren't considered bugs to begin with (when looking at %age)
			$t_bugs_fixed = 0;
			$t_bugs_notbugs = 0;
			for( $j = 0;$j < $t_enum_res_count;$j++ ) {
				$t_res_bug_count = 0;

				if( isset( $t_arr2[$c_res_s[$j]] ) ) {
					$t_res_bug_count = $t_arr2[$c_res_s[$j]];
				}

				echo '<td class="align-right">';
				if( 0 < $t_res_bug_count ) {
					$t_bug_link = $t_link_prefix .
						'&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $t_handler_id .
						'&amp;' . FILTER_PROPERTY_RESOLUTION . '=' . $c_res_s[$j] .
						'&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE;
					echo '<a class="subtle" href="' . $t_bug_link . '">' . $t_res_bug_count . '</a>';
				} else {
					echo $t_res_bug_count;
				}
				echo "</td>\n";

				if( $c_res_s[$j] >= $t_threshold_fixed ) {
					if( $c_res_s[$j] < $t_threshold_notfixed ) {
						# Count bugs with a resolution between fixed and not fixed thresholds
						$t_bugs_fixed += $t_res_bug_count;
					} else {
						# Count bugs with a resolution above the not fixed threshold
						$t_bugs_notbugs += $t_res_bug_count;
					}
				}

			}

			# Display Total
			echo '<td class="align-right">';
			$t_bug_link =  $t_link_prefix .
				'&amp;' . FILTER_PROPERTY_HANDLER_ID . '=' . $t_handler_id .
				'&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE;
			echo '<a class="subtle" href="' . $t_bug_link . '">' . $t_total . '</a>';
			echo "</td>\n";

			# Percentage
			$t_percent_fixed = 0;
			if( ( $t_total - $t_bugs_notbugs ) > 0 ) {
				$t_percent_fixed = ( $t_bugs_fixed / ( $t_arr2['total'] - $t_bugs_notbugs ) );
			}
			echo '<td class="align-right">';
			printf( '% 1.0f%%', ( $t_percent_fixed * 100 ) );
			echo "</td>\n";
			echo '</tr>';
		}
	}
}

/**
 * Print reporter / resolution report.
 * A filter can be used to limit the visibility.
 *
 * @param string $p_resolution_enum_string Resolution enumeration string value.
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_reporter_resolution( $p_resolution_enum_string, array $p_filter = null ) {
	$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

	$t_project_id = helper_get_current_project();

	# Get the resolution values to use
	$c_res_s = MantisEnum::getValues( $p_resolution_enum_string );
	$t_enum_res_count = count( $c_res_s );

	# Checking if it's a per project statistic or all projects
	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}

	# Get all of the bugs and split them up into an array

	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(id) as bugcount, reporter_id, resolution'
		. ' FROM {bug} WHERE ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY reporter_id, resolution';
	$t_query->sql( $t_sql );

	$t_reporter_res_arr = array();
	$t_reporter_bugcount_arr = array();
	$t_arr = $t_query->fetch();
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

		$t_arr = $t_query->fetch();
	}

	# Sort our total bug count array so that the reporters with the highest number of bugs are listed first,
	arsort( $t_reporter_bugcount_arr );

	$t_threshold_fixed = config_get( 'bug_resolution_fixed_threshold' );
	$t_threshold_notfixed = config_get( 'bug_resolution_not_fixed_threshold' );

	$t_link_prefix = summary_get_link_prefix( $p_filter );

	$t_row_count = 0;

	# We now have a multi dimensional array of users and resolutions, with the value of each resolution for each user
	foreach( $t_reporter_bugcount_arr as $t_reporter_id => $t_total_user_bugs ) {

		# Limit the number of reporters listed
		if( $t_row_count >= $t_reporter_summary_limit ) {
			break;
		}

		# Only print reporters who have reported at least one bug. This helps
		# prevent divide by zeroes, showing reporters not on this project, and showing
		# users that aren't actually reporters...
		if( $t_total_user_bugs > 0 ) {
			$t_arr2 = $t_reporter_res_arr[$t_reporter_id];

			echo '<tr>';
			$t_row_count++;
			echo '<td>';
			echo string_display_line( user_get_name( $t_reporter_id ) );
			echo "</td>\n";

			# We need to track the percentage of bugs that are considered fix, as well as
			# those that aren't considered bugs to begin with (when looking at %age)
			$t_bugs_fixed = 0;
			$t_bugs_notbugs = 0;
			for( $j = 0;$j < $t_enum_res_count;$j++ ) {
				$t_res_bug_count = 0;

				if( isset( $t_arr2[$c_res_s[$j]] ) ) {
					$t_res_bug_count = $t_arr2[$c_res_s[$j]];
				}

				echo '<td class="align-right">';
				if( 0 < $t_res_bug_count ) {
					$t_bug_link = $t_link_prefix .
						'&amp;' . FILTER_PROPERTY_REPORTER_ID . '=' . $t_reporter_id .
						'&amp;' . FILTER_PROPERTY_RESOLUTION . '=' . $c_res_s[$j] .
						'&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE;
					echo '<a class="subtle" href="' . $t_bug_link . '">' . $t_res_bug_count . '</a>';
				} else {
					echo $t_res_bug_count;
				}
				echo "</td>\n";

				if( $c_res_s[$j] >= $t_threshold_fixed ) {
					if( $c_res_s[$j] < $t_threshold_notfixed ) {
						# Count bugs with a resolution between fixed and not fixed thresholds
						$t_bugs_fixed += $t_res_bug_count;
					} else {
						# Count bugs with a resolution above the not fixed threshold
						$t_bugs_notbugs += $t_res_bug_count;
					}
				}

			}

			# Display Total
			echo '<td class="align-right">';
			$t_bug_link =  $t_link_prefix .
				'&amp;' . FILTER_PROPERTY_REPORTER_ID . '=' . $t_reporter_id .
				'&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE;
			echo '<a class="subtle" href="' . $t_bug_link . '">' . $t_total_user_bugs . '</a>';
			echo "</td>\n";

			# Percentage
			$t_percent_errors = 0;
			if( $t_total_user_bugs > 0 ) {
				$t_percent_errors = ( $t_bugs_notbugs / $t_total_user_bugs );
			}
			echo '<td class="align-right">';
			printf( '% 1.0f%%', ( $t_percent_errors * 100 ) );
			echo "</td>\n";
			echo '</tr>';
		}
	}
}

/**
 * Print reporter effectiveness report.
 * A filter can be used to limit the visibility.
 *
 * @param string $p_severity_enum_string   Severity enumeration string.
 * @param string $p_resolution_enum_string Resolution enumeration string.
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_reporter_effectiveness( $p_severity_enum_string, $p_resolution_enum_string, array $p_filter = null ) {
	$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

	$t_project_id = helper_get_current_project();

	$t_severity_multipliers = config_get( 'severity_multipliers' );
	$t_resolution_multipliers = config_get( 'resolution_multipliers' );

	# Get the severity values to use
	$c_sev_s = MantisEnum::getValues( $p_severity_enum_string );
	$t_enum_sev_count = count( $c_sev_s );

	# Get the resolution values to use
	$c_res_s = MantisEnum::getValues( $p_resolution_enum_string );

	# Checking if it's a per project statistic or all projects
	$t_specific_where = helper_project_specific_where( $t_project_id );
	if( ' 1<>1' == $t_specific_where ) {
		return;
	}

	# Get all of the bugs and split them up into an array
	$t_query = new DBQuery();
	$t_sql = 'SELECT COUNT(id) as bugcount, reporter_id, resolution, severity'
		. ' FROM {bug} WHERE ' . $t_specific_where;
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY reporter_id, resolution, severity';
	$t_query->sql( $t_sql );

	$t_reporter_ressev_arr = array();
	$t_reporter_bugcount_arr = array();
	$t_arr = $t_query->fetch();
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

		$t_arr = $t_query->fetch();
	}

	# Sort our total bug count array so that the reporters with the highest number of bugs are listed first,
	arsort( $t_reporter_bugcount_arr );

	$t_row_count = 0;

	# We now have a multi dimensional array of users, resolutions and severities, with the
	# value of each resolution and severity for each user
	foreach( $t_reporter_bugcount_arr as $t_reporter_id => $t_total_user_bugs ) {

		# Limit the number of reporters listed
		if( $t_row_count >= $t_reporter_summary_limit ) {
			break;
		}

		# Only print reporters who have reported at least one bug. This helps
		# prevent divide by zeroes, showing reporters not on this project, and showing
		# users that aren't actually reporters...
		if( $t_total_user_bugs > 0 ) {
			$t_arr2 = $t_reporter_ressev_arr[$t_reporter_id];

			echo '<tr>';
			$t_row_count++;
			echo '<td>';
			echo string_display_line( user_get_name( $t_reporter_id ) );
			echo '</td>';

			$t_total_severity = 0;
			$t_total_errors = 0;
			for( $j = 0; $j < $t_enum_sev_count; $j++ ) {
				if( !isset( $t_arr2[$c_sev_s[$j]] ) ) {
					continue;
				}

				$t_sev_bug_count = $t_arr2[$c_sev_s[$j]]['total'];
				$t_sev_mult = 1;
				if( isset( $t_severity_multipliers[$c_sev_s[$j]] ) ) {
					$t_sev_mult = $t_severity_multipliers[$c_sev_s[$j]];
				}

				if( $t_sev_bug_count > 0 ) {
					$t_total_severity += ( $t_sev_bug_count * $t_sev_mult );
				}

				foreach( $t_resolution_multipliers as $t_res => $t_res_mult ) {
					if( isset( $t_arr2[$c_sev_s[$j]][$t_res] ) ) {
						$t_total_errors += ( $t_sev_mult * $t_res_mult );
					}
				}
			}
			echo '<td class="align-right">' . $t_total_severity . '</td>';
			echo '<td class="align-right">' . $t_total_errors . '</td>';
			printf( '<td class="align-right">%d</td>', $t_total_severity - $t_total_errors );
			echo '</tr>';
		}
	}
}

/**
 * Calculate time stats for resolved issues.
 * A filter can be used to limit the visibility.
 *
 * @param integer $p_project_id.
 * @param array $p_filter Filter array.
 * @return array
 */
function summary_helper_get_time_stats( $p_project_id, array $p_filter = null ) {
	$t_specific_where = helper_project_specific_where( $p_project_id );
	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	# The issue may have passed through the status we consider resolved
	# (e.g. bug is CLOSED, not RESOLVED). The linkage to the history field
	# will look up the most recent 'resolved' status change and return it as well

	$t_stats = array(
		'bug_id'       => 0,
		'largest_diff' => 0,
		'total_time'   => 0,
		'average_time' => 0,
		);

	$t_sql_inner = ' FROM {bug} b LEFT JOIN {bug_history} h'
		. ' ON b.id = h.bug_id  AND h.type = :hist_type'
		. ' AND h.field_name = :hist_field AND b.date_submitted <= h.date_modified'
		. ' WHERE b.status >= :int_resolved'
		. ' AND h.new_value >= :str_resolved AND h.old_value < :str_resolved'
		. ' AND ' . $t_specific_where;
	$t_params = array(
		'hist_type' => 0,
		'hist_field' => 'status',
		'int_resolved' => (int)$t_resolved,
		'str_resolved' => (string)$t_resolved
		);
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql_inner .= ' AND b.id IN :filter';
		$t_params['filter'] = $t_subquery;
	}

	if( db_has_capability( DB_CAPABILITY_WINDOW_FUNCTIONS ) ) {
		if(db_is_mssql() ) {
			# sqlserver by default uses the column datatype, which is INT. This datatype can be overflowed
			# when a big number of issues are included, since we are adding the total number of seconds.
			$t_diff_expr = 'CAST(diff AS BIGINT)';
		} else {
			$t_diff_expr = 'diff';
		}
		$t_sql = 'SELECT id, diff, SUM(' . $t_diff_expr . ') OVER () AS total_time, AVG(' . $t_diff_expr . ') OVER () AS avg_time'
			. ' FROM ( SELECT b.id, MAX(h.date_modified) - b.date_submitted AS diff'
			. $t_sql_inner
			. ' GROUP BY b.id,b.date_submitted ) subquery'
			. ' ORDER BY diff DESC';
		$t_query = new DbQuery( $t_sql, $t_params );
		$t_query->set_limit(1);
		if( $t_row = $t_query->fetch() ) {
			$t_stats = array(
				'bug_id'       => $t_row['id'],
				'largest_diff' => number_format( (int)$t_row['diff'] / SECONDS_PER_DAY, 2 ),
				'total_time'   => number_format( (int)$t_row['total_time'] / SECONDS_PER_DAY, 2 ),
				'average_time' => number_format( (int)$t_row['avg_time'] / SECONDS_PER_DAY, 2 ),
				);
		}
	} else {
		$t_sql = 'SELECT b.id, b.date_submitted, b.last_updated, MAX(h.date_modified) AS hist_update, b.status'
			. $t_sql_inner
			. ' GROUP BY b.id, b.status, b.date_submitted, b.last_updated ORDER BY b.id ASC';
		$t_query = new DbQuery( $t_sql, $t_params );

		$t_bug_count = 0;
		$t_largest_diff = 0;
		$t_total_time = 0;
		while( $t_row = $t_query->fetch() ) {
			$t_bug_count++;
			$t_date_submitted = $t_row['date_submitted'];
			$t_last_updated = $t_row['hist_update'] !== null ? $t_row['hist_update'] : $t_row['last_updated'];

			if( $t_last_updated < $t_date_submitted ) {
				$t_last_updated = 0;
				$t_date_submitted = 0;
			}

			$t_diff = $t_last_updated - $t_date_submitted;
			$t_total_time += $t_diff;
			if( $t_diff > $t_largest_diff ) {
				$t_largest_diff = $t_diff;
				$t_bug_id = $t_row['id'];
			}
		}

		if( $t_bug_count > 0 ) {
			$t_average_time = $t_total_time / $t_bug_count;
		} else {
			$t_average_time = 0;
			$t_bug_id = 0;
		}

		$t_stats = array(
			'bug_id'       => $t_bug_id,
			'largest_diff' => number_format( $t_largest_diff / SECONDS_PER_DAY, 2 ),
			'total_time'   => number_format( $t_total_time / SECONDS_PER_DAY, 2 ),
			'average_time' => number_format( $t_average_time / SECONDS_PER_DAY, 2 ),
		);
	}
	return $t_stats;
}

/**
 * Returns a filter to be used in summary pages.
 * A temporary filter is retrieved if a valid temporary filter key is submitted
 * to the page as request parameter "filter".
 * If no filter key was provided, returns a generic filter that shows all
 * accesible issues by the user.
 *
 * @return array	Filter array
 */
function summary_get_filter() {
	$t_filter = null;
	$f_tmp_key = gpc_get_string( 'filter', null );
	if( null !== $f_tmp_key ) {
		$t_filter = filter_temporary_get( $f_tmp_key, null );
	}
	# if filter parameter doesn't exist or can't be loaded, return a default filter
	if( null === $t_filter ) {
			# TODO: for summary, as default, we want to show all status.
			# Until a better implementation for default/empty filters, we need to adjust here
			$t_filter = filter_get_default();
			$t_filter[FILTER_PROPERTY_HIDE_STATUS] = array( META_FILTER_NONE );
			$t_filter['_view_type'] = FILTER_VIEW_TYPE_SIMPLE;
	}
	return $t_filter;
}

/**
 * Print filter related information for summary page.
 * If a filter has been applied, display a notice, bug count, link to view issues.
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_filter_info( array $p_filter = null ) {
	if( null === $p_filter ) {
		return;
	}
	# If filter is temporary, then it has been provided explicitly.
	# When no filter is specified for summary page, we receive a defaulted filter
	# which don't have any specific id.
	if( !filter_is_temporary( $p_filter ) ) {
		return;
	}
	$t_filter_query = filter_cache_subquery( $p_filter );
	$t_bug_count = $t_filter_query->get_bug_count();
	$t_view_issues_link = helper_url_combine( 'view_all_bug_page.php', filter_get_temporary_key_param( $p_filter ) );
	?>
	<div class="space-10"></div>
	<div class="col-md-12 col-xs-12">
		<div class="alert alert-warning center">
		<?php
		echo '<a href="', $t_view_issues_link, '" title="', lang_get( 'view_bugs_link' ), '">';
		echo lang_get( 'summary_notice_filter_is_applied' ), '&nbsp;',
			'(', $t_bug_count, ' ', lang_get( 'bugs' ) , ')';
		echo '</a>';
		?>
		</div>
	</div>
	<?php
}

/**
 * Calculate the number of "open" and "resolve" issues actions in the last X days.
 * This includes each and successive resolution transitions.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_date_array   An array of integers representing days is passed in.
 * @param array $p_filter       Filter array.
 * @return array	Accumulated count for each day range.
 */
function summary_by_dates_bug_count( array $p_date_array, array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_specific_where = helper_project_specific_where( $t_project_id );
	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	$t_date_array = array_values( $p_date_array );
	sort( $t_date_array );

	$t_query = new DBQuery();
	$t_now = db_now();
	$t_prev_days = 0;
	$t_sql_ranges = 'CASE';
	foreach( $t_date_array as $t_ix => $t_days ) {
		$c_days = (int)$t_days;
		$t_range_start = $t_now - $c_days * SECONDS_PER_DAY + 1;
		$t_range_end = $t_now - $t_prev_days * SECONDS_PER_DAY;
		$t_sql_ranges .= ' WHEN date_modified'
				. ' BETWEEN ' . $t_query->param( $t_range_start )
				. ' AND ' . $t_query->param( $t_range_end )
				. ' THEN ' . $t_ix;
		$t_prev_days = $c_days;
	}
	$t_sql_ranges .= ' ELSE -1 END';

	$t_sql_inner = 'SELECT CASE WHEN h.type = :hist_type_new THEN :action_open'
		. ' WHEN h.type = :hist_type_upd AND h.old_value >= :status AND h.new_value  < :status THEN :action_open'
		. ' WHEN h.type = :hist_type_upd AND h.old_value < :status AND h.new_value  >= :status THEN :action_close'
		. ' ELSE null END AS action, date_modified'
        . ' FROM {bug_history} h JOIN {bug} b ON b.id = h.bug_id'
		. ' WHERE h.date_modified > :mint_ime'
		. ' AND ( h.type = :hist_type_new OR h.type = :hist_type_upd AND h.field_name = :hist_field )'
		. ' AND ' . $t_specific_where;
	$t_query->bind( array (
		'hist_type_upd' => NORMAL_TYPE,
		'hist_type_new' => NEW_BUG,
		'hist_field' => 'status',
		'status' => (string)$t_resolved,
		'action_open' => 'O',
		'action_close' => 'C',
		'mint_ime' => $t_now - $t_prev_days * SECONDS_PER_DAY
		) );

	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql_inner .= ' AND b.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}

	$t_sql = 'SELECT action, date_range, COUNT(*) AS range_count FROM'
		. ' ( SELECT action, ' . $t_sql_ranges . ' AS date_range'
		. ' FROM (' . $t_sql_inner . ') sub_actions'
		. ' WHERE action IS NOT NULL ) sub_count'
		. ' GROUP BY action, date_range ORDER BY date_range, action';
	$t_query->sql( $t_sql );

	# initialize the result array to 0
	$t_count_array = array();
	foreach( $t_date_array as $t_ix => $t_value ) {
		$t_count_array['open'][$t_ix] = 0;
		$t_count_array['close'][$t_ix] = 0;
	}

	# The query returns the count specific to each date range (some ranges may
	# not exist in the query result if the count is 0).
	# Fill the array with those values first.
	while( $t_row = $t_query->fetch() ) {
		$t_index = (int)$t_row['date_range'];
		if( $t_index >= 0 ) {
			switch( $t_row['action'] ) {
				case 'O':
					$t_count_array['open'][$t_index] = $t_row['range_count'];
					break;
				case 'C':
					$t_count_array['close'][$t_index] = $t_row['range_count'];
			}
		}
	}

	# This function returns the accumulated count. Process the array to add
	# each successive date range
	$t_count_open = 0;
	$t_count_closed = 0;
	foreach( $t_date_array as $t_ix => $t_value ) {
		$t_count_open += $t_count_array['open'][$t_ix];
		$t_count_array['open'][$t_ix] = $t_count_open;
		$t_count_closed += $t_count_array['close'][$t_ix];
		$t_count_array['close'][$t_ix] = $t_count_closed;
	}
	return $t_count_array;
}

/**
 * This function shows the number of "open" and "resolve" issues actions in the
 * last X days. This includes each issue submission, and it's succesive resolve
 * and reopen transitions.
 * A filter can be used to limit the visibility.
 *
 * @param array $p_date_array An array of integers representing days is passed in.
 * @param array $p_filter Filter array.
 * @return void
 */
function summary_print_by_date( array $p_date_array, array $p_filter = null ) {
	# clean and sort dates array
	$t_date_array = array_values( $p_date_array );
	sort( $t_date_array );

	$t_by_dates_count = summary_by_dates_bug_count( $t_date_array, $p_filter );
	$t_open_count_array = $t_by_dates_count['open'];
	$t_resolved_count_array = $t_by_dates_count['close'];

	foreach( $t_date_array as $t_ix => $t_days ) {
		$t_new_count = $t_open_count_array[$t_ix];
		$t_resolved_count = $t_resolved_count_array[$t_ix];
		$t_start_date = mktime( 0, 0, 0, date( 'm' ), ( date( 'd' ) - $t_days ), date( 'Y' ) );
		$t_end_date = mktime( 0, 0, 0 ) + SECONDS_PER_DAY;

		$t_link_prefix = summary_get_link_prefix( $p_filter );

		# if we come from a filter, don't clear status properties
		if( !filter_is_temporary( $p_filter ) ) {
			$t_status_prop = '&amp;' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE;
		} else {
			$t_status_prop = '';
		}
		$t_new_bugs_link = $t_link_prefix
				. '&amp;' . FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED . '=' . ON
				. '&amp;' . FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR . '=' . date( 'Y', $t_start_date )
				. '&amp;' . FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH . '=' . date( 'm', $t_start_date )
				. '&amp;' . FILTER_PROPERTY_DATE_SUBMITTED_START_DAY . '=' . date( 'd', $t_start_date )
				. '&amp;' . FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR . '=' . date( 'Y', $t_end_date )
				. '&amp;' . FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH . '=' . date( 'm', $t_end_date )
				. '&amp;' . FILTER_PROPERTY_DATE_SUBMITTED_END_DAY . '=' . date( 'd', $t_end_date )
				. $t_status_prop . '">';
		echo '<tr>' . "\n";
		echo '    <td class="width50">' . $t_days . '</td>' . "\n";

		if( $t_new_count > 0 ) {
			echo '    <td class="align-right"><a class="subtle" href="' . $t_new_bugs_link . $t_new_count . '</a></td>' . "\n";
		} else {
			echo '    <td class="align-right">' . $t_new_count . '</td>' . "\n";
		}
		echo '    <td class="align-right">' . $t_resolved_count . '</td>' . "\n";

		$t_balance = $t_new_count - $t_resolved_count;
		$t_style = '';
		if( $t_balance > 0 ) {

			# we are talking about bugs: a balance > 0 is "negative" for the project...
			$t_style = ' red';
			$t_balance = sprintf( '%+d', $t_balance );

			# "+" modifier added in PHP >= 4.3.0
		} else if( $t_balance < 0 ) {
			$t_style = ' green';
			$t_balance = sprintf( '%+d', $t_balance );
		}

		echo '    <td class="align-right' . $t_style . '">' . $t_balance . "</td>\n";
		echo '</tr>' . "\n";
	}
}

function summary_get_link_prefix( array $p_filter = null ) {
	$t_filter_action = filter_is_temporary( $p_filter ) ? FILTER_ACTION_PARSE_ADD : FILTER_ACTION_PARSE_NEW;
	$t_link_prefix = 'view_all_set.php?type=' . $t_filter_action . '&temporary=y&new=1';
	$t_link_prefix = helper_url_combine( $t_link_prefix, filter_get_temporary_key_param( $p_filter ) );
	return $t_link_prefix;
}
