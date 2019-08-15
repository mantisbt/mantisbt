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
 * Timeline event class for file attachment operations.
 * @copyright Copyright 2018 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

use Mantis\Exceptions\ServiceException;

/**
 * Timeline event class for file attachment operations.
 *
 * @package MantisBT
 * @subpackage classes
 */
class IssueAttachmentTimelineEvent extends TimelineEvent {
	private $issue_id;
	private $filename;
	private $type;

	/**
	 * @param integer $p_timestamp Timestamp representing the time the event occurred.
	 * @param integer $p_user_id   An user identifier.
	 * @param integer $p_issue_id  A issue identifier.
	 * @param string  $p_filename  Attachment's file name.
	 * @param integer $p_type      Event type (FILE_ADDED, FILE_DELETED)
	 */
	public function __construct( $p_timestamp, $p_user_id, $p_issue_id, $p_filename, $p_type ) {
		parent::__construct( $p_timestamp, $p_user_id );

		$this->issue_id = $p_issue_id;
		$this->filename = $p_filename;
		$this->type = $p_type;
	}

	/**
	 * Returns html string to display
	 * @return string
	 * @throws Mantis\Exceptions\ServiceException
	 */
	public function html() {
		switch( $this->type ) {
			case FILE_ADDED:
				$t_string = 'timeline_issue_file_added';
				break;
			case FILE_DELETED:
				$t_string = 'timeline_issue_file_deleted';
				break;
			default:
				throw new ServiceException( 'Unknown Event Type', ERROR_GENERIC );
		}

		$t_bug_link = string_get_bug_view_link( $this->issue_id );

		$t_html = $this->html_start( 'fa-file-o' );
		$t_html .= '<div class="action">'
			. sprintf( lang_get( $t_string ),
				prepare_user_name( $this->user_id ),
				$t_bug_link,
				string_html_specialchars( $this->filename )
			)
			. '</div>';
		$t_html .= $this->html_end();

		return $t_html;
	}
}
