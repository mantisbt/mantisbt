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
 * Timeline event class for creation of issues.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Timeline event class for creation of issues.
 *
 * @package MantisBT
 * @subpackage classes
 */
class IssueNoteCreatedTimelineEvent extends TimelineEvent {
	private $issue_id;
	private $issue_note_id;

	/**
	 * @param integer $p_timestamp     Timestamp representing the time the event occurred.
	 * @param integer $p_user_id       An user identifier.
	 * @param integer $p_issue_id      A issue identifier.
	 * @param integer $p_issue_note_id A issue note identifier.
	 */
	public function __construct( $p_timestamp, $p_user_id, $p_issue_id, $p_issue_note_id ) {
		parent::__construct( $p_timestamp, $p_user_id );

		$this->issue_id = $p_issue_id;
		$this->issue_note_id = $p_issue_note_id;
	}

	/**
	 * Returns html string to display
	 * @return string
	 */
	public function html() {
		$t_html = $this->html_start( 'fa-comment-o' );
		$t_html .= '<div class="action">' . sprintf( lang_get( 'timeline_issue_note_created' ), user_get_name( $this->user_id ), string_get_bug_view_link( $this->issue_id ) ) . '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}