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
 * A command that deletes a relationship from an issue.
 *
 * To delete a relationship we need to ensure that:
 * - User not anomymous
 * - Source bug exists and is not in read-only state (peer bug could not exist...)
 * - User that update the source bug and at least view the destination bug
 * - Relationship must exist
 *
 * Sample:
 * {
 *   "query": {
 *     "issue_id": 1234,
 *     "relationship_id": 5555
 *   }
 * }
 */
class IssueRelationshipDeleteCommand extends Command {
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
		$t_issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		$t_relationship_id = helper_parse_id( $this->query( 'relationship_id' ), 'relationship_id' );

		$t_issue = bug_get( $t_issue_id, true );
		if( $t_issue->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are viewing...
			# ... override the current project. This to avoid problems with categories and handlers lists etc.
			$g_project_override = $t_issue->project_id;
		}

		# Ensure user has access to update the issue
		$t_update_threshold = config_get( 'update_bug_threshold', null, null, $t_issue->project_id );
		if( !access_has_bug_level( $t_update_threshold, $t_issue_id ) ) {
			throw new ClientException(
				'Access denied to add relationship',
				ERROR_ACCESS_DENIED
			);
		}

		# Ensure source issue is not read-only
		if( bug_is_readonly( $t_issue_id ) ) {
			throw new ClientException(
				sprintf( "Issue %d is read-only", $t_issue_id ),
				ERROR_BUG_READ_ONLY_ACTION_DENIED,
				array( $t_issue_id )
			);
		}

		# Retrieve the target issue of the relationship
		$t_target_issue_id = relationship_get_linked_bug_id( $t_relationship_id, $t_issue_id );
		$t_target_issue = bug_get( $t_target_issue_id, true );

		# Ensure that user can view target issue
		$t_view_threshold = config_get( 'view_bug_threshold', null, null, $t_target_issue->project_id );
		if( !access_has_bug_level( $t_view_threshold, $t_target_issue_id ) ) {
			throw new ClientException(
				sprintf( "Access denied to issue %d", $t_target_issue_id ),
				ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW,
				array( $t_target_issue_id )
			);
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_relationship_id = helper_parse_id( $this->query( 'relationship_id' ), 'relationship_id' );
		relationship_delete( $t_relationship_id );
		return array();
	}
}

