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
 * Bug API
 *
 * @package CoreAPI
 * @subpackage BugAPI
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses history_api.php
 */

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'history_api.php' );

/**
 * Get list of affected issues between a given time period
 * @param integer $p_start_time Timestamp representing start time of the period.
 * @param integer $p_end_time   Timestamp representing end time of the period.
 * @return array
 */
function timeline_get_affected_issues( $p_start_time, $p_end_time ) {
	$t_query = 'SELECT DISTINCT(bug_id) from {bug_history} WHERE date_modified >= ' . db_param() . ' AND date_modified < ' . db_param();
	$t_result = db_query( $t_query, array( $p_start_time, $p_end_time ) );

	$t_current_project = helper_get_current_project();

	$t_all_issue_ids = array();
	while( ( $t_row = db_fetch_array( $t_result ) ) !== false ) {
		$t_all_issue_ids[] = $t_row['bug_id'];
	}

	bug_cache_array_rows( $t_all_issue_ids );

	$t_issue_ids = array();
	foreach( $t_all_issue_ids as $t_issue_id ) {
		if( $t_current_project != ALL_PROJECTS && $t_current_project != bug_get_field( $t_issue_id, 'project_id' ) ) {
			continue;
		}

		if( !access_has_bug_level( config_get( 'view_bug_threshold' ), $t_issue_id ) ) {
			continue;
		}

		$t_issue_ids[] = $t_issue_id;
	}

	return $t_issue_ids;
}

/**
 * Get an array of timeline events
 * Events for which the skip() method returns true will be excluded
 * @param integer $p_start_time Timestamp representing start time of the period.
 * @param integer $p_end_time   Timestamp representing end time of the period.
 * @return array
 */
function timeline_events( $p_start_time, $p_end_time ) {
	$t_issue_ids = timeline_get_affected_issues( $p_start_time, $p_end_time );

	$t_timeline_events = array();

	foreach ( $t_issue_ids as $t_issue_id ) {
		$t_history_events_array = history_get_raw_events_array( $t_issue_id, null, $p_start_time, $p_end_time );
		$t_history_events_array = array_reverse( $t_history_events_array );

		foreach ( $t_history_events_array as $t_history_event ) {
			if( $t_history_event['date'] < $p_start_time ||
				 $t_history_event['date'] >= $p_end_time ) {
				continue;
			}

			$t_event = null;
			$t_user_id = $t_history_event['userid'];
			$t_timestamp = $t_history_event['date'];

			switch( $t_history_event['type'] ) {
				case NEW_BUG:
					$t_event = new IssueCreatedTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id );
					break;
				case BUGNOTE_ADDED:
					$t_bugnote_id = $t_history_event['old_value'];
					$t_event = new IssueNoteCreatedTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, $t_bugnote_id );
					break;
				case BUG_MONITOR:
					# Skip monitors added for others due to reminders, only add monitor events where added
					# user is the same as the logged in user.
					if( (int)$t_history_event['old_value'] == (int)$t_history_event['userid'] ) {
						$t_event = new IssueMonitorTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, true );
					}
					break;
				case BUG_UNMONITOR:
					$t_event = new IssueMonitorTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, false );
					break;
				case TAG_ATTACHED:
					$t_event = new IssueTagTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, $t_history_event['old_value'], true );
					break;
				case TAG_DETACHED:
					$t_event = new IssueTagTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, $t_history_event['old_value'], false );
					break;
				case NORMAL_TYPE:
					switch( $t_history_event['field'] ) {
						case 'status':
							$t_event = new IssueStatusChangeTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, $t_history_event['old_value'], $t_history_event['new_value'] );
							break;
						case 'handler_id':
							$t_event = new IssueAssignedTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, $t_history_event['new_value'] );
							break;
					}
					break;
			}

			# Do not include skipped events
			if( $t_event != null && !$t_event->skip() ) {
				$t_timeline_events[] = $t_event;
			}
		}
	}

	return $t_timeline_events;
}

/**
 * Sort an array of timeline events
 * @param array $p_events Array of events being sorted.
 * @return array Sorted array of events.
 */
function timeline_sort_events( array $p_events ) {
	$t_count = count( $p_events );
	$t_stable = false;

	while( !$t_stable ) {
		$t_stable = true;

		for( $i = 0; $i < $t_count - 1; ++$i ) {
			if( $p_events[$i]->compare( $p_events[$i+1] ) < 0 ) {
				$t_temp = $p_events[$i];
				$p_events[$i] = $p_events[$i+1];
				$p_events[$i+1] = $t_temp;
				$t_stable = false;
			}
		}
	}

	return $p_events;
}

/**
 * Print for display an array of events
 * @param array $p_events   Array of events to display
 * @param int   $p_max_num  Maximum number of events to display, 0 = all
 * @return int  Number of displayed events
 */
function timeline_print_events( array $p_events, $p_max_num = 0 ) {
	if( empty( $p_events ) ) {
		echo '<p>' . lang_get( 'timeline_no_activity' ) . '</p>';
		return 0;
	}

	$i = 0;
	foreach( $p_events as $t_event ) {
		# Stop displaying events if we're reached the maximum
		if( $p_max_num && $i++ >= $p_max_num ) {
			break;
		}
		echo $t_event->html();
	}
	return min( $p_max_num, $i);
}

