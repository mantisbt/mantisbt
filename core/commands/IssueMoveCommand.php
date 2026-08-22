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

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'project_api.php' );
require_api( 'helper_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * Moves an issue from one project to another.
 *
 * Data:
 * - query.issue_id: issue id
 * - payload.project: project object reference (id or name)
 * - payload.note: optional IssueNoteAddCommand payload
 */
class IssueMoveCommand extends Command {
	/**
	 * The issue being moved.
	 *
	 * @var int
	 */
	private $issue_id;

	/**
	 * The issue's current project.
	 *
	 * @var int
	 */
	private $source_project_id;

	/**
	 * The destination project.
	 *
	 * @var int
	 */
	private $target_project_id;

	/**
	 * The optional note payload.
	 *
	 * @var array
	 */
	private $note = array();

	/**
	 * Validates the issue, destination project, permissions, and note payload.
	 *
	 * @return void
	 * @throws ClientException When validation fails.
	 */
	protected function validate(): void {
		$this->issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		bug_ensure_exists( $this->issue_id );

		if( bug_is_readonly( $this->issue_id ) ) {
			throw new ClientException( 'Issue is read-only', ERROR_BUG_READ_ONLY_ACTION_DENIED );
		}

		$this->source_project_id = (int)bug_get_field( $this->issue_id, 'project_id' );
		$this->target_project_id = $this->get_target_project_id( $this->payload( 'project' ) );
		$this->note = $this->payload( 'note', array() );

		global $g_project_override;
		$g_project_override = $this->source_project_id;

		config_flush_cache();

		if( $this->source_project_id == $this->target_project_id ) {
			throw new ClientException(
				'The issue is already associated with the specified project.',
				ERROR_INVALID_FIELD_VALUE,
				array( 'project' ) );
		}

		$t_view_bug_threshold = config_get( 'view_bug_threshold', null, null, $this->source_project_id );
		if( !access_has_bug_level( $t_view_bug_threshold, $this->issue_id ) ) {
			throw new ClientException( 'Access denied to view issue in source project.', ERROR_ACCESS_DENIED );
		}

		$t_move_bug_threshold = config_get( 'move_bug_threshold', null, null, $this->source_project_id );
		if( !access_has_bug_level( $t_move_bug_threshold, $this->issue_id ) ) {
			throw new ClientException( 'Access denied to move issue from source project.', ERROR_ACCESS_DENIED );
		}

		$t_report_bug_threshold = config_get( 'report_bug_threshold', null, null, $this->target_project_id );
		if( !access_has_project_level( $t_report_bug_threshold, $this->target_project_id ) ) {
			throw new ClientException( 'Access denied to create issues in target project.', ERROR_ACCESS_DENIED );
		}

		if( !empty( $this->note ) ) {
			if( !is_array( $this->note ) ) {
				throw new ClientException( 'The note field must be an object.', ERROR_INVALID_FIELD_VALUE, array( 'note' ) );
			}

		}
	}

	/**
	 * Moves the issue, emits move/update notifications, and adds the note.
	 *
	 * @return array The moved issue identifiers.
	 */
	protected function process(): array {
		$t_existing_issue = bug_get( $this->issue_id, true );
		bug_move( $this->issue_id, $this->target_project_id );
		bug_clear_cache_all( $this->issue_id );

		$t_updated_issue = bug_get( $this->issue_id, true );
		event_signal( 'EVENT_UPDATE_BUG', array( $t_existing_issue, $t_updated_issue ) );
		helper_call_custom_function( 'issue_update_notify', array( $this->issue_id ) );

		if( !empty( $this->note ) && ( !is_blank( $this->note['text'] ?? '' ) || !empty( $this->note['time_tracking'] ) ) ) {
			$t_note_result = ( new IssueNoteAddCommand( array(
				'query' => array( 'issue_id' => $this->issue_id ),
				'payload' => $this->note,
			) ) )->execute();

			$t_note_id = (int)( $t_note_result['id'] ?? 0 );
		} else {
			$t_note_id = 0;
		}

		event_signal( 'EVENT_MOVE_BUG', array( $this->issue_id, $this->source_project_id, $this->target_project_id, $t_note_id ) );

		return array( 'issue_id' => $this->issue_id, 'project_id' => $this->target_project_id );
	}

	/**
	 * Resolves a project object reference to an existing project id.
	 *
	 * @param array|object $p_project Project reference containing id or name.
	 * @return int Project id.
	 * @throws ClientException When the reference is invalid or unknown.
	 */
	private function get_target_project_id( $p_project ) {
		if( is_object( $p_project ) ) {
			$p_project = get_object_vars( $p_project );
		}

		if( !is_array( $p_project ) || ( !isset( $p_project['id'] ) && !isset( $p_project['name'] ) ) ) {
			throw new ClientException( 'The project field is required. Please provide either id or name.', ERROR_EMPTY_FIELD, array( 'project' ) );
		}

		if( isset( $p_project['id'] ) && (int)$p_project['id'] > 0 ) {
			$t_project_id = (int)$p_project['id'];

			if( !project_exists( $t_project_id ) ) {
				throw new ClientException( "Project '$t_project_id' does not exist.", ERROR_PROJECT_NOT_FOUND, array( $t_project_id ) );
			}
			if( !project_enabled( $t_project_id ) ) {
				throw new ClientException( "Project '$t_project_id' is disabled.", ERROR_ACCESS_DENIED, array( $t_project_id ) );
			}

			return $t_project_id;
		}

		$t_project_name = trim( $p_project['name'] );
		$t_project_id = project_get_id_by_name( $t_project_name );

		if( !$t_project_id ) {
			throw new ClientException( 'Project ' . $t_project_name . ' not found', ERROR_PROJECT_NOT_FOUND );
		}
		if( !project_enabled( $t_project_id ) ) {
			throw new ClientException( 'Project ' . $t_project_name . ' is disabled', ERROR_ACCESS_DENIED );
		}

		return (int)$t_project_id;
	}
}
