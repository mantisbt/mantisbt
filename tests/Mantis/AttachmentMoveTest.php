<?php declare(strict_types=1);
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
 * Test cases for moving on-disk attachments between projects.
 *
 * @package    Tests
 * @subpackage FileAPI
 * @copyright  Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for file_move_bug_attachments().
 */
class AttachmentMoveTest extends MantisCoreBase {

	/**
	 * @var int Project the issue starts in.
	 */
	private $source_project_id = 0;

	/**
	 * @var int Project the issue is moved to.
	 */
	private $target_project_id = 0;

	/**
	 * @var int Issue created for the test.
	 */
	private $issue_id = 0;

	/**
	 * @var string[] Directories to remove during teardown.
	 */
	private $temp_dirs = array();

	/**
	 * Creates two projects with separate attachment paths, and an issue.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		if( DISK != config_get( 'file_upload_method' ) ) {
			$this->markTestSkipped( 'Requires file_upload_method = DISK' );
		}

		self::login();

		$this->source_project_id = $this->makeProject( 'source' );
		$this->target_project_id = $this->makeProject( 'target' );

		$t_issue = new \BugData();
		$t_issue->project_id = $this->source_project_id;
		$t_issue->category_id = 1;
		$t_issue->summary = __CLASS__ . ': issue ' . rand( 1, 1000000 );
		$t_issue->description = 'Issue used by attachment move tests.';
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
		foreach( array( $this->source_project_id, $this->target_project_id ) as $t_id ) {
			if( $t_id && project_exists( $t_id ) ) {
				project_delete( $t_id );
			}
		}
		foreach( $this->temp_dirs as $t_dir ) {
			if( is_dir( $t_dir ) ) {
				array_map( 'unlink', glob( $t_dir . '*' ) ?: array() );
				rmdir( $t_dir );
			}
		}
		$this->temp_dirs = array();

		parent::tearDown();
	}

	/**
	 * Creates a writable upload directory, registered for teardown.
	 *
	 * @return string Path with a trailing directory separator.
	 */
	private function makeTempDir(): string {
		$t_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR
			. uniqid( 'mantis-move-' ) . DIRECTORY_SEPARATOR;
		mkdir( $t_dir, 0777, true );
		$this->temp_dirs[] = $t_dir;
		return $t_dir;
	}

	/**
	 * Creates a project with its own attachment directory.
	 *
	 * @param string $p_label Distinguishes the project in failure output.
	 *
	 * @return int Project id.
	 */
	private function makeProject( string $p_label ): int {
		return project_create(
			__CLASS__ . " $p_label " . rand( 1, 1000000 ),
			"Test project ($p_label) for attachment moves.",
			30, # release
			VS_PUBLIC,
			$this->makeTempDir()
		);
	}

	/**
	 * Repoints a project's attachment directory, the same way an administrator
	 * would from manage_proj_edit_page.php. Existing attachments are not moved.
	 *
	 * @param int $p_project_id Project to relocate.
	 *
	 * @return void
	 */
	private function relocateProject( int $p_project_id ): void {
		$t_row = project_get_row( $p_project_id );
		project_update(
			$p_project_id,
			$t_row['name'],
			$t_row['description'],
			$t_row['status'],
			$t_row['view_state'],
			$this->makeTempDir(),
			$t_row['enabled'],
			$t_row['inherit_global']
		);
	}

	/**
	 * Attaches a small file to the fixture issue.
	 *
	 * @return array The {bug_file} row for the new attachment.
	 */
	private function addAttachment(): array {
		$t_tmp_file = tempnam( sys_get_temp_dir(), 'mantis-src-' );
		file_put_contents( $t_tmp_file, 'attachment payload' );

		file_add(
			$this->issue_id,
			array(
				'name' => 'attachment.txt',
				'tmp_name' => $t_tmp_file,
				'type' => 'text/plain',
				'error' => UPLOAD_ERR_OK,
				'size' => filesize( $t_tmp_file ),
				# Not an HTTP upload, so file_add() must copy rather than
				# calling move_uploaded_file().
				'browser_upload' => false,
			)
		);

		return $this->attachmentRow();
	}

	/**
	 * Reads back the fixture issue's most recent attachment row.
	 *
	 * @return array The {bug_file} row.
	 */
	private function attachmentRow(): array {
		$t_query = new \DbQuery(
			'SELECT * FROM {bug_file} WHERE bug_id = :bug_id ORDER BY id DESC'
		);
		$t_query->bind( 'bug_id', $this->issue_id );
		$t_query->execute();

		return $t_query->fetch();
	}

	/**
	 * Moving an issue relocates its attachments and records the new folder.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testMoveRelocatesAttachment(): void {
		$t_before = $this->addAttachment();
		$t_old_file = $t_before['folder'] . $t_before['diskfile'];
		$this->assertFileExists( $t_old_file );

		file_move_bug_attachments( $this->issue_id, $this->target_project_id );

		$t_after = $this->attachmentRow();
		$this->assertSame(
			project_get_field( $this->target_project_id, 'file_path' ),
			$t_after['folder']
		);
		$this->assertFileExists( $t_after['folder'] . $t_after['diskfile'] );
		$this->assertFileDoesNotExist( $t_old_file );
	}

	/**
	 * Moving an issue must work when the source project's file path has been
	 * changed since the attachment was uploaded.
	 *
	 * The attachment is still in its original directory, which the folder
	 * column records; deriving the source from the project's current file_path
	 * looks in the wrong place and the move fails outright.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testMoveRelocatesAttachmentAfterSourcePathChanged(): void {
		$t_before = $this->addAttachment();
		$t_old_file = $t_before['folder'] . $t_before['diskfile'];
		$this->assertFileExists( $t_old_file );

		$this->relocateProject( $this->source_project_id );

		file_move_bug_attachments( $this->issue_id, $this->target_project_id );

		$t_after = $this->attachmentRow();
		$this->assertSame(
			project_get_field( $this->target_project_id, 'file_path' ),
			$t_after['folder']
		);
		$this->assertFileExists( $t_after['folder'] . $t_after['diskfile'] );
		$this->assertFileDoesNotExist( $t_old_file );
	}

	/**
	 * An attachment sitting outside the source project's configured directory
	 * must still be moved, even when both projects point at the same path.
	 *
	 * The early return compares the two projects' configured paths, which says
	 * nothing about where the attachment actually is.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testMoveRelocatesAttachmentWhenProjectPathsMatch(): void {
		$t_before = $this->addAttachment();
		$t_old_file = $t_before['folder'] . $t_before['diskfile'];

		# Point both projects at one directory, distinct from where the
		# attachment was written.
		$t_shared = $this->makeTempDir();
		foreach( array( $this->source_project_id, $this->target_project_id ) as $t_id ) {
			$t_row = project_get_row( $t_id );
			project_update(
				$t_id,
				$t_row['name'],
				$t_row['description'],
				$t_row['status'],
				$t_row['view_state'],
				$t_shared,
				$t_row['enabled'],
				$t_row['inherit_global']
			);
		}

		file_move_bug_attachments( $this->issue_id, $this->target_project_id );

		$t_after = $this->attachmentRow();
		$this->assertSame( $t_shared, $t_after['folder'] );
		$this->assertFileExists( $t_after['folder'] . $t_after['diskfile'] );
		$this->assertFileDoesNotExist( $t_old_file );
	}
}
