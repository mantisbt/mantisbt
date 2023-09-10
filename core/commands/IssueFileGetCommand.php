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

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that gets issue attachments.
 */
class IssueFileGetCommand extends Command {
	/**
	 * The issue id.
	 */
	private $issue_id;

	/**
	 * Constructor
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {
		$this->issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		$t_issue = bug_get( $this->issue_id, true );
		$this->user_id = auth_get_current_user_id();

		if( $t_issue->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			global $g_project_override;
			$g_project_override = $t_issue->project_id;
		}

		$t_file_id = $this->query( 'file_id' );
		$t_attachments = file_get_visible_attachments( $this->issue_id );
		$t_matching_attachments = array();
		foreach( $t_attachments as $t_attachment ) {
			if( $t_file_id != null && $t_file_id != $t_attachment['id'] ) {
				continue;
			}

			$t_result = file_get_content( $t_attachment['id'] );
			$t_attachment['content_type'] = $t_result['type'];
			$t_attachment['content'] = $t_result['content'];
	
			$t_matching_attachments[] = $t_attachment;
		}

		return $t_matching_attachments;
	}
}

