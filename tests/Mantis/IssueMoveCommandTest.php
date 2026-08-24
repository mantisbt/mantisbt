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

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\ClientException;

/**
 * Tests for IssueMoveCommand.
 */
class IssueMoveCommandTest extends MantisCoreBase {
	/** @var int Created issue identifier. */
	private $issue_id;
	/** @var int Source-project category identifier. */
	private $source_category_id;
	/** @var int Created target-project identifier. */
	private $target_project_id;
	/** @var int Target-project category identifier. */
	private $target_category_id;

	/**
	 * Creates a source issue and target project for the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		self::login();

		$this->target_project_id = project_create(
			__CLASS__ . ' ' . rand( 1, 1000000 ),
			'Test project for IssueMoveCommand.',
			30 # release
		);
		$this->source_category_id = category_add( 1, __CLASS__ . ' ' . rand( 1, 1000000 ) );
		$t_category_name = category_get_field( $this->source_category_id, 'name' );
		$this->target_category_id = category_add( $this->target_project_id, $t_category_name );

		$t_issue = new \BugData();
		$t_issue->project_id = 1;
		$t_issue->category_id = $this->source_category_id;
		$t_issue->summary = __CLASS__ . ': issue ' . rand( 1, 1000000 );
		$t_issue->description = 'Issue used by IssueMoveCommand tests.';
		$this->issue_id = $t_issue->create();
	}

	/**
	 * Removes fixtures created by the test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		if( $this->issue_id && bug_exists( $this->issue_id ) ) {
			bug_delete( $this->issue_id );
		}
		if( $this->target_project_id && project_exists( $this->target_project_id ) ) {
			project_delete( $this->target_project_id );
		}
		if( $this->source_category_id && category_exists( $this->source_category_id ) ) {
			category_remove( $this->source_category_id );
		}
		parent::tearDown();
	}

	/**
	 * Moves an issue by project ID and adds a public note.
	 *
	 * @return void
	 */
	public function testMoveIssueByProjectIdAndAddNote(): void {
		$t_result = ( new \IssueMoveCommand( array(
			'query' => array( 'issue_id' => $this->issue_id ),
			'payload' => array(
				'project' => array( 'id' => $this->target_project_id ),
				'note' => array( 'text' => 'Moved by command.' ),
			),
		) ) )->execute();

		$this->assertSame( $this->target_project_id, $t_result['project_id'] );
		$this->assertSame( $this->target_project_id, (int)bug_get_field( $this->issue_id, 'project_id' ) );
		$t_note_id = bugnote_get_latest_id( $this->issue_id );
		$this->assertStringContainsString( 'Moved by command.', bugnote_get_text( $t_note_id ) );
		$this->assertMoveHistory( $t_note_id );
	}

	/**
	 * Moves an issue by a trimmed project name.
	 *
	 * @return void
	 */
	public function testMoveIssueByTrimmedProjectName(): void {
		$t_project_name = project_get_name( $this->target_project_id );
		( new \IssueMoveCommand( array(
			'query' => array( 'issue_id' => $this->issue_id ),
			'payload' => array( 'project' => array( 'name' => '  ' . $t_project_name . '  ' ) ),
		) ) )->execute();

		$this->assertSame( $this->target_project_id, (int)bug_get_field( $this->issue_id, 'project_id' ) );
	}

	/**
	 * Uses the target project's default category when the source category does not exist there.
	 *
	 * @return void
	 */
	public function testMoveIssueUsesTargetDefaultCategoryWhenSourceCategoryIsMissing(): void {
		category_remove( $this->target_category_id );
		$this->target_category_id = category_add(
			$this->target_project_id,
			__CLASS__ . ' fallback ' . rand( 1, 1000000 )
		);
		config_set(
			'default_category_for_moves',
			$this->target_category_id,
			NO_USER,
			$this->target_project_id
		);

		try {
			$this->moveIssueToTarget();

			$this->assertSame( $this->target_category_id, (int)bug_get_field( $this->issue_id, 'category_id' ) );
		} finally {
			config_delete( 'default_category_for_moves', NO_USER, $this->target_project_id );
		}
	}

	/**
	 * Rejects moving an issue to its current project.
	 *
	 * @return void
	 */
	public function testMoveIssueRejectsSameProject(): void {
		$this->expectException( ClientException::class );
		$this->expectExceptionMessage( 'already associated' );

		( new \IssueMoveCommand( array(
			'query' => array( 'issue_id' => $this->issue_id ),
			'payload' => array( 'project' => array( 'id' => 1 ) ),
		) ) )->execute();
	}

	/**
	 * Rejects moving an issue to a disabled project.
	 *
	 * @return void
	 */
	public function testMoveIssueRejectsDisabledProject(): void {
		project_update(
			$this->target_project_id,
			project_get_field( $this->target_project_id, 'name' ),
			project_get_field( $this->target_project_id, 'description' ),
			project_get_field( $this->target_project_id, 'status' ),
			project_get_field( $this->target_project_id, 'view_state' ),
			project_get_field( $this->target_project_id, 'file_path' ),
			false,
			project_get_field( $this->target_project_id, 'inherit_global' )
		);

		$this->expectException( ClientException::class );
		$this->expectExceptionMessage( 'disabled' );
		$this->moveIssueToTarget();
	}

	/**
	 * Rejects a move without source-project move permission.
	 *
	 * @return void
	 */
	public function testMoveIssueRejectsMissingMovePermission(): void {
		$t_old_threshold = config_get( 'move_bug_threshold', null, NO_USER, 1 );
		config_set( 'move_bug_threshold', NOBODY, NO_USER, 1 );

		try {
			$this->expectException( ClientException::class );
			$this->expectExceptionMessage( 'move issue from source project' );
			$this->moveIssueToTarget();
		} finally {
			config_set( 'move_bug_threshold', $t_old_threshold, NO_USER, 1 );
		}
	}

	/**
	 * Rejects a move without source-project view permission.
	 *
	 * @return void
	 */
	public function testMoveIssueRejectsMissingViewPermission(): void {
		$t_old_threshold = config_get( 'view_bug_threshold', null, NO_USER, 1 );
		config_set( 'view_bug_threshold', NOBODY, NO_USER, 1 );

		try {
			$this->expectException( ClientException::class );
			$this->expectExceptionMessage( 'view issue in source project' );
			$this->moveIssueToTarget();
		} finally {
			config_set( 'view_bug_threshold', $t_old_threshold, NO_USER, 1 );
		}
	}

	/**
	 * Rejects a move without target-project report permission.
	 *
	 * @return void
	 */
	public function testMoveIssueRejectsMissingTargetReportPermission(): void {
		$t_old_threshold = config_get( 'report_bug_threshold', null, NO_USER, $this->target_project_id );
		config_set( 'report_bug_threshold', NOBODY, NO_USER, $this->target_project_id );

		try {
			$this->expectException( ClientException::class );
			$this->expectExceptionMessage( 'target project' );
			$this->moveIssueToTarget();
		} finally {
			config_set( 'report_bug_threshold', $t_old_threshold, NO_USER, $this->target_project_id );
		}
	}

	/**
	 * Adds a private note while moving an issue.
	 *
	 * @return void
	 */
	public function testMoveIssueAddsPrivateNote(): void {
		$this->moveIssueToTarget( array( 'text' => 'Private move note.', 'view_state' => array( 'id' => VS_PRIVATE ) ) );

		$t_note_id = bugnote_get_latest_id( $this->issue_id );
		$this->assertSame( VS_PRIVATE, (int)bugnote_get_field( $t_note_id, 'view_state' ) );
		$this->assertMoveHistory( $t_note_id );
	}

	/**
	 * Adds an attachment to a move note.
	 *
	 * @return void
	 */
	public function testMoveIssueAddsNoteAttachment(): void {
		$t_file_path = tempnam( sys_get_temp_dir(), 'move-issue-' );
		file_put_contents( $t_file_path, 'IssueMoveCommand attachment test.' );

		try {
			$this->moveIssueToTarget( array(
				'text' => 'Move note with attachment.',
				'files' => array( array(
					'name' => 'move-issue.txt',
					'tmp_name' => $t_file_path,
					'type' => 'text/plain',
					'error' => UPLOAD_ERR_OK,
					'size' => filesize( $t_file_path ),
				) ),
			) );

			$t_note_id = bugnote_get_latest_id( $this->issue_id );
			$t_note_attachments = array_filter(
				bug_get_attachments( $this->issue_id ),
				function( $p_attachment ) use ( $t_note_id ) {
					return (int)$p_attachment['bugnote_id'] === $t_note_id;
				}
			);
			$this->assertCount( 1, $t_note_attachments );
		} finally {
			unlink( $t_file_path );
		}
	}

	/**
	 * Executes a move to the fixture target project.
	 *
	 * @param array $p_note Optional note payload.
	 * @return array Command result.
	 */
	private function moveIssueToTarget( array $p_note = array() ): array {
		$t_payload = array( 'project' => array( 'id' => $this->target_project_id ) );
		if( !empty( $p_note ) ) {
			$t_payload['note'] = $p_note;
		}

		return ( new \IssueMoveCommand( array(
			'query' => array( 'issue_id' => $this->issue_id ),
			'payload' => $t_payload,
		) ) )->execute();
	}

	/**
	 * Verifies the history entries created by a move with a note.
	 *
	 * @param int $p_note_id Added note identifier.
	 * @return void
	 */
	private function assertMoveHistory( int $p_note_id ): void {
		$t_history = history_get_raw_events_array( $this->issue_id );
		$t_project_history = array_values( array_filter(
			$t_history,
			function( $p_event ) {
				return $p_event['field'] === 'project_id';
			}
		) );
		$this->assertCount( 1, $t_project_history );
		$this->assertSame( 1, (int)$t_project_history[0]['old_value'] );
		$this->assertSame( $this->target_project_id, (int)$t_project_history[0]['new_value'] );

		$t_note_history = array_values( array_filter(
			$t_history,
			function( $p_event ) {
				return (int)$p_event['type'] === BUGNOTE_ADDED;
			}
		) );
		$this->assertCount( 1, $t_note_history );
		$this->assertSame( bugnote_format_id( $p_note_id ), $t_note_history[0]['old_value'] );
	}
}
