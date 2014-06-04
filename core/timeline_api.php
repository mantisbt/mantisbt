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

class TimelineEvent {
	public $timestamp;
	public $user_id;

	public function html() {
		echo $timestamp;
	}

	public function format_timestamp( $p_timestamp ) {
		$t_normal_date_format = config_get( 'normal_date_format' );
		return date( $t_normal_date_format, $p_timestamp );
	}

	public function html_start() {
		$t_html = '<div class="entry">';

		$t_avatar = user_get_avatar( $this->user_id, 32 );
		if ( $t_avatar !== false ) {
			$t_avatar_url = $t_avatar[0];
			$t_html .= '<img class="avatar" src="' . $t_avatar_url . '"/>';
		} else {
			$t_html .= '<img class="avatar-disabled" />';
		}

		$t_html .= '<div class="timestamp">' .  $this->format_timestamp( $this->timestamp ) . '</div>';

		return $t_html;
	}

	public function html_end() {
		return '</div>';
	}
}

class IssueCreatedTimelineEvent extends TimelineEvent {
	public $issue_id;

	public function html() {
		$t_html = $this->html_start();
		$t_html .= '<div class="action">' . sprintf( lang_get( 'timeline_issue_created' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) ) . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}

class IssueNoteCreatedTimelineEvent extends TimelineEvent {
	public $issue_id;
	public $issue_note_id;

	public function html() {
		$t_html = $this->html_start();
		$t_html .= '<div class="action">' . sprintf( lang_get( 'timeline_issue_note_created' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) ) . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}

class IssueMonitorTimelineEvent extends TimelineEvent {
	public $issue_id;
	public $monitor;

	public function html() {
		$t_string = $this->monitor ? lang_get( 'timeline_issue_monitor' ) : lang_get( 'timeline_issue_unmonitor' );

		$t_html = $this->html_start();
		$t_html .= '<div class="action">' . sprintf( $t_string, user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) ) . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}

class IssueTagTimelineEvent extends TimelineEvent {
	public $issue_id;
	public $tag_name;
	public $tag;

	public function html() {
		$t_string = $this->tag ? lang_get( 'timeline_issue_tagged' ) : lang_get( 'timeline_issue_tagged' );

		$t_html = $this->html_start();
		$t_html .= '<div class="action">' . sprintf( $t_string, user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ), $this->tag_name ) . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}

class IssueStatusChangeTimelineEvent extends TimelineEvent {
	public $issue_id;
	public $old_status;
	public $new_status;

	public function html() {
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_closed = config_get( 'bug_closed_status_threshold' );

		if ( $this->old_status < $t_closed && $this->new_status >= $t_closed ) {
			$t_string = sprintf( lang_get( 'timeline_issue_closed' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) );
		} else if ( $this->old_status < $t_resolved && $this->new_status >= $t_resolved ) {
			$t_string = sprintf( lang_get( 'timeline_issue_resolved' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) );
		} else if ( $this->old_status >= $t_resolved && $this->new_status < $t_resolved ) {
			$t_string = sprintf( lang_get( 'timeline_issue_reopened' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) );
		} else {
			return;
		}

		$t_html = $this->html_start();
		$t_html .= '<div class="action">' . $t_string . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}

class IssueAssignedTimelineEvent extends TimelineEvent {
	public $issue_id;
	public $handler_id;

	public function html() {
		if ( $this->user_id == $this->handler_id ) {
			$t_string = sprintf( lang_get( 'timeline_issue_assigned_to_self' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) );
		} else {
			$t_string = sprintf( lang_get( 'timeline_issue_assigned' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ), user_get_name( $this->handler_id ) );
		}

		$t_html = $this->html_start();
		$t_html .= '<div class="action">' . $t_string . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}

function timeline_get_affected_issues( $p_start_time, $p_end_time ) {
	$t_mantis_bug_history_table = db_get_table( 'bug_history' );

	$query = "SELECT DISTINCT(bug_id) from $t_mantis_bug_history_table WHERE date_modified >= " . db_param() . " AND date_modified < " . db_param();
	$result = db_query_bound( $query, array( $p_start_time, $p_end_time ) );

	$t_current_project = helper_get_current_project();

	$t_all_issue_ids = array();
	while ( ( $t_row = db_fetch_array( $result ) ) !== false ) {
		$t_all_issue_ids[] = $t_row['bug_id'];
	}

	bug_cache_array_rows( $t_all_issue_ids );

	$t_issue_ids = array();
	foreach ( $t_all_issue_ids as $t_issue_id ) {
		if ( $t_current_project != ALL_PROJECTS && $t_current_project != bug_get_field( $t_issue_id, 'project_id' ) ) {
			continue;
		}

		if ( !access_has_bug_level( VIEWER, $t_issue_id ) ) {
			continue;
		}

		$t_issue_ids[] = $t_issue_id;
	}

	return $t_issue_ids;
}

function timeline_events( $p_start_time, $p_end_time ) {
	$t_issue_ids = timeline_get_affected_issues( $p_start_time, $p_end_time );

	$t_timeline_events = array();

	foreach ( $t_issue_ids as $t_issue_id ) {
		$t_history_events_array = history_get_raw_events_array( $t_issue_id );
		$t_history_events_array = array_reverse( $t_history_events_array );

		foreach ( $t_history_events_array as $t_history_event ) {
			if ( $t_history_event['date'] < $p_start_time ||
				 $t_history_event['date'] >= $p_end_time ) {
				continue;
			}

			$t_event = null;

			if ( $t_history_event['type'] == NEW_BUG ) {
				$t_event = new IssueCreatedTimelineEvent();
				$t_event->issue_id = $t_issue_id;
			} else if ( $t_history_event['type'] == BUGNOTE_ADDED ) {
				$t_bugnote_id = $t_history_event['old_value'];

				if ( !bugnote_exists( $t_bugnote_id ) ) {
					continue;
				}

				if ( !access_has_bugnote_level( VIEWER, $t_bugnote_id ) ) {
					continue;
				}

				$t_event = new IssueNoteCreatedTimelineEvent();
				$t_event->issue_id = $t_issue_id;
				$t_event->issue_note_id = $t_bugnote_id;
			} else if ( $t_history_event['type'] == BUG_MONITOR ) {
				# Skip monitors added for others due to reminders, only add monitor events where added
				# user is the same as the logged in user.
				if ( (int)$t_history_event['old_value'] == (int)$t_history_event['userid'] ) {
					$t_event = new IssueMonitorTimelineEvent();
					$t_event->issue_id = $t_issue_id;
					$t_event->monitor = true;
				}
			} else if ( $t_history_event['type'] == BUG_UNMONITOR ) {
				$t_event = new IssueMonitorTimelineEvent();
				$t_event->issue_id = $t_issue_id;
				$t_event->monitor = false;
			} else if ( $t_history_event['type'] == TAG_ATTACHED ) {
				$t_event = new IssueTagTimelineEvent();
				$t_event->issue_id = $t_issue_id;
				$t_event->tag_name = $t_history_event['old_value'];
				$t_event->tag = true;
			} else if ( $t_history_event['type'] == TAG_DETACHED ) {
				$t_event = new IssueTagTimelineEvent();
				$t_event->issue_id = $t_issue_id;
				$t_event->tag_name = $t_history_event['old_value'];
				$t_event->tag = false;
			} else if ( $t_history_event['type'] == NORMAL_TYPE ) {
				switch ( $t_history_event['field'] ) {
					case 'status':
						$t_event = new IssueStatusChangeTimelineEvent();
						$t_event->issue_id = $t_issue_id;
						$t_event->old_status = $t_history_event['old_value'];
						$t_event->new_status = $t_history_event['new_value'];
						break;
					case 'handler_id':
						if ( access_has_bug_level( config_get( 'view_handler_threshold' ), $t_issue_id ) ) {
							$t_event = new IssueAssignedTimelineEvent();
							$t_event->issue_id = $t_issue_id;
							$t_event->handler_id = $t_history_event['new_value'];
						}
						break;
				}
			}

			if ( $t_event != null ) {
				$t_event->user_id = $t_history_event['userid'];
				$t_event->timestamp = $t_history_event['date'];
				$t_timeline_events[] = $t_event;
			}
		}
	}

	return $t_timeline_events;
}

function timeline_sort_events( $p_events ) {
	$t_count = count( $p_events );
	$t_stable = false;

	while ( !$t_stable ) {
		$t_stable = true;

		for ( $i = 0; $i < $t_count - 1; ++$i ) {
			if ( $p_events[$i]->timestamp < $p_events[$i + 1]->timestamp ) {
				$t_temp = $p_events[$i];
				$p_events[$i] = $p_events[$i+1];
				$p_events[$i+1] = $t_temp;
				$t_stable = false;
			}
		}
	}

	return $p_events;
}

function timeline_print_events( $p_events ) {
	foreach ( $p_events as $t_event ) {
		echo $t_event->html();
	}
}

