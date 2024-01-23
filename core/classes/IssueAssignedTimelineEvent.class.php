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
 * Timeline event class for assignment of issues.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Timeline event class for assignment of issues.
 *
 * @package MantisBT
 * @subpackage classes
 */
class IssueAssignedTimelineEvent extends TimelineEvent {
	private $issue_id;
	private $handler_id;

	/**
	 * @param integer $p_timestamp  Timestamp representing the time the event occurred.
	 * @param integer $p_user_id    A user identifier.
	 * @param integer $p_issue_id   A issue identifier.
	 * @param integer $p_handler_id A user identifier.
	 */
	public function __construct( $p_timestamp, $p_user_id, $p_issue_id, $p_handler_id ) {
		parent::__construct( $p_timestamp, $p_user_id );

		$this->issue_id = $p_issue_id;
		$this->handler_id = $p_handler_id;
	}

	/**
	 * Returns html string to display
	 * @return string
	 */
	public function html() {
		if( $this->user_id == $this->handler_id ) {
			$t_html = $this->html_start( 'fa-flag-o' );
			$t_string = sprintf( lang_get( 'timeline_issue_assigned_to_self' ), prepare_user_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) );
		} else if( $this->handler_id != NO_USER ) {
			$t_html = $this->html_start( 'fa-hand-o-right' );
			$t_string = sprintf( lang_get( 'timeline_issue_assigned' ), prepare_user_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ), prepare_user_name( $this->handler_id ) );
		} else {
            $t_html = $this->html_start( 'fa-flag-o' );
			$t_string = sprintf( lang_get( 'timeline_issue_unassigned' ), prepare_user_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) );
		}

		$t_html .= '<div class="action">' . $t_string . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}
