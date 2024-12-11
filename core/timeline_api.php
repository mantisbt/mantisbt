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
 * Get an array of timeline events
 * Events for which the skip() method returns true will be excluded
 * @param integer $p_start_time Timestamp representing start time of the period.
 * @param integer $p_end_time   Timestamp representing end time of the period.
 * @param integer $p_max_events The maximum number of events to return or 0 for unlimited.
 * @param array $p_filter		Filter array to use for filtering bugs, or null for no limit.
 * @param integer $p_user_id	A user id, to limit timeline to a user's history, or null for no limit.
 * @return array
 */
function timeline_events( $p_start_time, $p_end_time, $p_max_events, $p_filter = null, $p_user_id = null ) {
	$t_timeline_events = array();

	$t_query_options = array();
	$t_query_options['start_time'] = $p_start_time;
	$t_query_options['end_time'] = $p_end_time;
	$t_query_options['order'] = 'DESC';
	if( null !== $p_filter ) {
		$t_query_options['filter'] = $p_filter;
	}
	if( null !== $p_user_id ) {
		$t_query_options['user_id'] = $p_user_id;
	}
	$t_result = history_query_result( $t_query_options );
	$t_count = 0;

	while ( $t_history_event = history_get_event_from_row( $t_result, /* $p_user_id */ auth_get_current_user_id(), /* $p_check_access_to_issue */ true ) ) {
		$t_event = null;
		$t_user_id = (int)$t_history_event['userid'];
		$t_timestamp = $t_history_event['date'];
		$t_issue_id = $t_history_event['bug_id'];
		$t_type = $t_history_event['type'];

		switch( $t_type ) {
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
				if( (int)$t_history_event['old_value'] == $t_user_id ) {
					$t_event = new IssueMonitorTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, true );
				}
				break;
			case BUG_UNMONITOR:
				# Skip removing other users from monitoring list, only add unmonitor events where removed
				# user is the same as the logged in user.
				if( (int)$t_history_event['old_value'] == $t_user_id ) {
					$t_event = new IssueMonitorTimelineEvent( $t_timestamp, $t_user_id, $t_issue_id, false );
				}
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
			case FILE_ADDED:
			case FILE_DELETED:
				$t_event = new IssueAttachmentTimelineEvent(
					$t_timestamp,
					$t_user_id,
					$t_issue_id,
					$t_history_event['old_value'],
					$t_type
				);
				break;
		}

		# Do not include skipped events
		if( $t_event != null && !$t_event->skip() ) {
			$t_timeline_events[] = $t_event;
			$t_count++;

			if ( $p_max_events > 0 && $t_count >= $p_max_events ) {
				break;
			}
		}
	}

	return $t_timeline_events;
}

/**
 * Print for display an array of events
 * @param array $p_events   Array of events to display
 * @return void
 */
function timeline_print_events( array $p_events ) {
	if( empty( $p_events ) ) {
		echo '<h6 class="padding-4">' . lang_get( 'timeline_no_activity' ) . '</h6>';
	} else {
		foreach( $p_events as $t_event ) {
			echo $t_event->html();
		}
	}
}
