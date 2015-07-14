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
 * Timeline event class for users tagging issues.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Timeline event class for users tagging issues.
 *
 * @package MantisBT
 * @subpackage classes
 */
class IssueTagTimelineEvent extends TimelineEvent {
	private $issue_id;
	private $tag_name;
	private $tag;

	/**
	 * @param integer $p_timestamp Timestamp representing the time the event occurred.
	 * @param integer $p_user_id   An user identifier.
	 * @param integer $p_issue_id  A issue identifier.
	 * @param string  $p_tag_name  Tag name linked to the issue.
	 * @param boolean $p_tag       Whether tag was being linked or unlinked from the issue.
	 */
	public function __construct( $p_timestamp, $p_user_id, $p_issue_id, $p_tag_name, $p_tag ) {
		parent::__construct( $p_timestamp, $p_user_id );

		$this->issue_id = $p_issue_id;
		$this->tag_name = $p_tag_name;
		$this->tag = $p_tag;
	}

	/**
	 * Returns html string to display
	 * @return string
	 */
	public function html() {
		$t_string = $this->tag ? lang_get( 'timeline_issue_tagged' ) : lang_get( 'timeline_issue_untagged' );
		$t_tag_row = tag_get_by_name( $this->tag_name );

		$t_html = $this->html_start();
		$t_html .= '<div class="action">'
			. sprintf(
				$t_string,
				user_get_name( $this->user_id ),
				string_get_bug_view_link( $this->issue_id ),
				$t_tag_row ? tag_get_link( $t_tag_row ) : $this->tag_name
			)
			. '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}
