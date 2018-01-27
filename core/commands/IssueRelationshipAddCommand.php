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
 * A command that adds a relationship to an issue.  If a relationship already
 * exists with the target issue, it will be updated.
 *
 * Sample:
 *
 * {
 *   "query": { "issue_id": 1234 },
 *   "payload": {
 *     "issue": {
 *       "id": 1235
 *     },
 *     "type": {
 *       "id": 1,
 *       "name": "related-to"
 *     }
 *   }
 * }
 *
 * Relation Type IDs: check "bug relationship constants" in core/constants_inc.php
 */
class IssueRelationshipAddCommand extends Command {
	/**
	 * The relationship type id
	 *
	 * @var integer
	 */
	private $typeId;

	/**
	 * The source issue
	 *
	 * @var BugData
	 */
	private $sourceIssue = null;

	/**
	 * The target issue
	 *
	 * @var BugData
	 */
	private $targetIssue = null;

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
		$t_type = $this->payload( 'type', array( 'id' => BUG_RELATED ) );
		$this->typeId = $this->getRelationTypeId( $t_type );

		$t_source_issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		$t_target_issue_ref = $this->payload( 'issue' );

		if( !isset( $t_target_issue_ref['id'] ) ) {
			throw new ClientException(
				'Invalid issue id',
				ERROR_INVALID_FIELD_VALUE,
				array( 'issue_id' )
			);
		}

		$t_target_issue_id = helper_parse_issue_id( $t_target_issue_ref['id'], 'target_issue_id' );

		$this->sourceIssue = bug_get( $t_source_issue_id, true );

		$t_update_threshold = config_get( 'update_bug_threshold', null, null, $this->sourceIssue->project_id );

		# Ensure user has access to update the source issue
		if( !access_has_bug_level( $t_update_threshold, $t_source_issue_id ) ) {
			throw new ClientException(
				'Access denied to add relationship',
				ERROR_ACCESS_DENIED
			);
		}

		# Ensure that source and target issues are not the same
		if( $t_source_issue_id == $t_target_issue_id ) {
			throw new ClientException(
				"Issue can't have relationship to itself",
				ERROR_RELATIONSHIP_SAME_BUG
			);
		}

		# Ensure that related issue exists and gets its information
		$this->targetIssue = bug_get( $t_target_issue_id, true );

		# Ensure source issue is not read-only
		if( bug_is_readonly( $t_source_issue_id ) ) {
			throw new ClientException(
				sprintf( "Issue %d is read-only", $t_source_issue_id ),
				ERROR_BUG_READ_ONLY_ACTION_DENIED,
				array( $t_source_issue_id )
			);
		}

		# Ensure that user can view target issue
		$t_view_threshold = config_get( 'view_bug_threshold', null, null, $this->targetIssue->project_id );
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
		if( $this->sourceIssue->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			global $g_project_override;
			$g_project_override = $this->sourceIssue->project_id;
		}

		# Create or update the relationship
		$t_relationship_id = relationship_upsert( $this->sourceIssue->id, $this->targetIssue->id, $this->typeId );

		return array( 'id' => $t_relationship_id );
	}

	/**
	 * Get relationship type id from relationship type reference.
	 *
	 * @param array The relationship type reference with id, name or both.
	 * @return integer relationship type id.
	 */
	private function getRelationTypeId( $p_relationship_type ) {
		if( isset( $p_relationship_type['id'] ) ) {
			$t_type_id = (int)$p_relationship_type['id'];
		} else if( isset( $p_relationship_type['name'] ) ) {
			$t_type_id = relationship_get_id_from_api_name( $p_relationship_type['name'] );
		} else {
			throw new ClientException(
				'Invalid relationship type',
				ERROR_INVALID_FIELD_VALUE,
				array( 'relationship_type' )
			);
		}

		return $t_type_id;
	}
}

