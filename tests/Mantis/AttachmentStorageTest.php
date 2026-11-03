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
 * Test cases for on-disk attachment storage.
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
 * PHPUnit tests for attachments stored on disk.
 *
 * These exercise the interaction between the stored `folder` column and the
 * project's configured file path, which only matters when file_upload_method
 * is DISK.
 */
class AttachmentStorageTest extends MantisCoreBase {

	/**
	 * @var int Project created for the test.
	 */
	private $project_id = 0;

	/**
	 * @var int Issue created for the test.
	 */
	private $issue_id = 0;

	/**
	 * @var string[] Directories to remove during teardown.
	 */
	private $temp_dirs = array();

	/**
	 * Creates a project with its own attachment path, and an issue in it.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		if( DISK != config_get( 'file_upload_method' ) ) {
			$this->markTestSkipped( 'Requires file_upload_method = DISK' );
		}

		self::login();

		$this->project_id = project_create(
			__CLASS__ . ' ' . rand( 1, 1000000 ),
			'Test project for attachment storage.',
			30, # release
			VS_PUBLIC,
			$this->makeTempDir()
		);

		$t_issue = new \BugData();
		$t_issue->project_id = $this->project_id;
		$t_issue->category_id = 1;
		$t_issue->summary = __CLASS__ . ': issue ' . rand( 1, 1000000 );
		$t_issue->description = 'Issue used by attachment storage tests.';
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
		if( $this->project_id && project_exists( $this->project_id ) ) {
			project_delete( $this->project_id );
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
			. uniqid( 'mantis-attach-' ) . DIRECTORY_SEPARATOR;
		mkdir( $t_dir, 0777, true );
		$this->temp_dirs[] = $t_dir;
		return $t_dir;
	}

	/**
	 * Relocates the fixture project's attachment directory, the same way an
	 * administrator would from manage_proj_edit_page.php. Existing attachments
	 * are not moved.
	 *
	 * @return void
	 */
	private function relocateProject(): void {
		$t_row = project_get_row( $this->project_id );
		project_update(
			$this->project_id,
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

		$t_query = new \DbQuery(
			'SELECT * FROM {bug_file} WHERE bug_id = :bug_id ORDER BY id DESC'
		);
		$t_query->bind( 'bug_id', $this->issue_id );
		$t_query->execute();

		return $t_query->fetch();
	}

	/**
	 * The stored folder must be where the file actually landed.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testAttachmentIsStoredUnderTheProjectPath(): void {
		$t_row = $this->addAttachment();
		$t_expected = project_get_field( $this->project_id, 'file_path' );

		$this->assertSame( $t_expected, $t_row['folder'] );
		$this->assertFileExists( $t_row['folder'] . $t_row['diskfile'] );
	}

	/**
	 * Deleting an issue must remove the attachment from disk even when the
	 * project's file path has been changed since the file was uploaded.
	 *
	 * The `folder` column records where the file really is; re-deriving the
	 * path from the project's current configuration points somewhere else, and
	 * the file is silently left behind.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testDeletingIssueRemovesAttachmentAfterProjectPathChanged(): void {
		$t_row = $this->addAttachment();
		$t_file = $t_row['folder'] . $t_row['diskfile'];
		$this->assertFileExists( $t_file );

		# Relocate the project's uploads. Existing attachments stay where they
		# are, and their folder column still records the truth.
		$this->relocateProject();

		bug_delete( $this->issue_id );
		$this->issue_id = 0;

		$this->assertFileDoesNotExist(
			$t_file,
			'Attachment was orphaned on disk after the project path changed'
		);
	}

	/**
	 * Deleting a single attachment must remove it from disk even when the
	 * project's file path has been changed since the file was uploaded.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testDeletingAttachmentAfterProjectPathChanged(): void {
		$t_row = $this->addAttachment();
		$t_file = $t_row['folder'] . $t_row['diskfile'];
		$this->assertFileExists( $t_file );

		$this->relocateProject();

		file_delete( (int)$t_row['id'] );

		$this->assertFileDoesNotExist(
			$t_file,
			'Attachment was orphaned on disk after the project path changed'
		);
	}

	/**
	 * Reading an attachment's content must work after the project's file path
	 * has been changed since the file was uploaded.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testGetContentAfterProjectPathChanged(): void {
		$t_row = $this->addAttachment();

		$this->relocateProject();

		$t_content = file_get_content( (int)$t_row['id'] );

		$this->assertIsArray( $t_content, 'Attachment content was not found' );
		$this->assertSame( 'attachment payload', $t_content['content'] );
	}
}
