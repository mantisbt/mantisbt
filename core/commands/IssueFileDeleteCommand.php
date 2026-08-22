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
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that deletes an issue attachment.
 */
class IssueFileDeleteCommand extends Command {
	/** @var int */
	private $issue_id;

	/** @var int */
	private $file_id;

	/**
	 * Validate the deletion request and its permissions.
	 *
	 * @throws ClientException
	 */
	function validate() {
		$this->issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		bug_ensure_exists( $this->issue_id );

		$this->file_id = (int)$this->query( 'file_id' );
		if( $this->file_id < 1 ) {
			throw new ClientException( "'file_id' must be >= 1", ERROR_INVALID_FIELD_VALUE, array( 'file_id' ) );
		}

		$t_file_issue_id = file_get_field( $this->file_id, 'bug_id' );
		# A missing attachment and an attachment belonging to another issue are
		# handled identically to avoid exposing whether a file id exists.
		if( (int)$t_file_issue_id !== $this->issue_id ) {
			throw new ClientException( 'Attachment does not belong to issue', ERROR_INVALID_FIELD_VALUE, array( 'file_id' ) );
		}

		$t_project_id = (int)bug_get_field( $this->issue_id, 'project_id' );
		if( $t_project_id != helper_get_current_project() ) {
			global $g_project_override;
			$g_project_override = $t_project_id;
		}

		if( bug_is_readonly( $this->issue_id ) ) {
			throw new ClientException(
				sprintf( "Issue '%d' is read-only.", $this->issue_id ),
				ERROR_BUG_READ_ONLY_ACTION_DENIED,
				array( $this->issue_id )
			);
		}

		$t_file_owner_id = file_get_field( $this->file_id, 'user_id' );
		$t_user_id = auth_get_current_user_id();
		$t_is_owner = $t_file_owner_id == $t_user_id;
		if( !$t_is_owner || !config_get( 'allow_delete_own_attachments' ) ) {
			if( !access_has_bug_level( config_get( 'delete_attachments_threshold' ), $this->issue_id, $t_user_id ) ) {
				throw new ClientException( 'Access denied', ERROR_ACCESS_DENIED );
			}
		}
	}

	/**
	 * Delete the attachment and return its issue id.
	 *
	 * @return array
	 */
	protected function process() {
		file_delete( $this->file_id, 'bug' );
		return array( 'issue_id' => $this->issue_id );
	}
}
