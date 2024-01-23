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
 * Timeline event class for status change of issues.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Timeline event class for status change of issues.
 *
 * @package MantisBT
 * @subpackage classes
 */
class IssueStatusChangeTimelineEvent extends TimelineEvent {
	private $issue_id;
	private $old_status;
	private $new_status;
	private $type;

	/**
	 * Status change types to be displayed in Timeline
	 * IGNORED = not displayed
	 */
	const IGNORED = 0;
	const RESOLVED = 1;
	const CLOSED = 2;
	const REOPENED = 3;

	/**
	 * @param integer $p_timestamp  Timestamp representing the time the event occurred.
	 * @param integer $p_user_id    A user identifier.
	 * @param integer $p_issue_id   A issue identifier.
	 * @param integer $p_old_status Old status value of issue.
	 * @param integer $p_new_status New status value of issue.
	 */
	public function __construct( $p_timestamp, $p_user_id, $p_issue_id, $p_old_status, $p_new_status ) {
		parent::__construct( $p_timestamp, $p_user_id );

		$this->issue_id = $p_issue_id;
		$this->old_status = $p_old_status;
		$this->new_status = $p_new_status;
		$this->type = $this->change_type();
	}

	/**
	 * Return the type of status change
	 * @return int One of the status change constants defined above
	 */
	private function change_type() {
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_closed = config_get( 'bug_closed_status_threshold' );

		if( $this->old_status < $t_closed && $this->new_status >= $t_closed ) {
			return IssueStatusChangeTimelineEvent::CLOSED;
		} else if( $this->old_status < $t_resolved && $this->new_status >= $t_resolved ) {
			return IssueStatusChangeTimelineEvent::RESOLVED;
		} else if( $this->old_status >= $t_resolved && $this->new_status < $t_resolved ) {
			return IssueStatusChangeTimelineEvent::REOPENED;
		} else {
			return IssueStatusChangeTimelineEvent::IGNORED;
		}
	}

	/**
	 * Whether to skip this timeline event.
	 * This normally implements access checks for the event.
	 * @return boolean
	 */
	public function skip() {
		return $this->type == IssueStatusChangeTimelineEvent::IGNORED;
	}

	/**
	 * Returns html string to display
	 * @return string
	 */
	public function html() {
		switch( $this->type ) {
			case IssueStatusChangeTimelineEvent::RESOLVED:
                $t_html = $this->html_start( 'fa-thumbs-o-up' );
				$t_string = sprintf(
					lang_get( 'timeline_issue_resolved' ),
					prepare_user_name( $this->user_id ),
					string_get_bug_view_link( $this->issue_id )
				);
				break;
			case IssueStatusChangeTimelineEvent::CLOSED:
                $t_html = $this->html_start( 'fa-power-off' );
				$t_string = sprintf(
					lang_get( 'timeline_issue_closed' ),
					prepare_user_name( $this->user_id ),
					string_get_bug_view_link( $this->issue_id )
				);
				break;
			case IssueStatusChangeTimelineEvent::REOPENED:
                $t_html = $this->html_start( 'fa-refresh' );
				$t_string = sprintf(
					lang_get( 'timeline_issue_reopened' ),
					prepare_user_name( $this->user_id ),
					string_get_bug_view_link( $this->issue_id )
				);
				break;
			case IssueStatusChangeTimelineEvent::IGNORED:
				return '';
			default:
				# Unknown status change type
				trigger_error( ERROR_GENERIC, ERROR );
				return '';
		}
        
		$t_html .= '<div class="action">' . $t_string . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}
