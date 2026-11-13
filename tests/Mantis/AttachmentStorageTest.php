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
	 * @var int Saved configuration, restored during teardown.
	 */
	private static $depth;

	/**
	 * @var int Saved configuration, restored during teardown.
	 */
	private static $width;

	/**
	 * Saves the configuration the tests overwrite.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$depth = config_get_global( 'file_upload_subdirectory_depth' );
		self::$width = config_get_global( 'file_upload_subdirectory_width' );
	}

	/**
	 * Restores the saved configuration.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		config_set_global( 'file_upload_subdirectory_depth', self::$depth );
		config_set_global( 'file_upload_subdirectory_width', self::$width );

		parent::tearDownAfterClass();
	}

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

		# Default to the flat layout; cases that need subdirectories opt in.
		config_set_global( 'file_upload_subdirectory_depth', 0 );
		config_set_global( 'file_upload_subdirectory_width', 2 );

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
			$this->removeTree( $t_dir );
		}
		$this->temp_dirs = array();

		parent::tearDown();
	}

	/**
	 * Removes a directory and everything below it.
	 *
	 * @param string $p_dir Directory to remove.
	 *
	 * @return void
	 */
	private function removeTree( string $p_dir ): void {
		if( !is_dir( $p_dir ) ) {
			return;
		}
		foreach( glob( rtrim( $p_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . '*' ) ?: array() as $t_entry ) {
			if( is_dir( $t_entry ) ) {
				$this->removeTree( $t_entry );
			} else {
				unlink( $t_entry );
			}
		}
		rmdir( $p_dir );
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

		return $this->attachmentRow();
	}

	/**
	 * Reads back an attachment row.
	 *
	 * @param int $p_file_id Attachment to read, or 0 for the most recent.
	 *
	 * @return array The {bug_file} row.
	 */
	private function attachmentRow( int $p_file_id = 0 ): array {
		if( $p_file_id ) {
			$t_query = new \DbQuery( 'SELECT * FROM {bug_file} WHERE id = :id' );
			$t_query->bind( 'id', $p_file_id );
		} else {
			$t_query = new \DbQuery(
				'SELECT * FROM {bug_file} WHERE bug_id = :bug_id ORDER BY id DESC'
			);
			$t_query->bind( 'bug_id', $this->issue_id );
		}
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
	 * With subdirectories enabled, uploads land under one and remain fully
	 * usable: the folder column records the subdirectory, and reading and
	 * deleting both follow it.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testUploadUsesSubdirectoryWhenEnabled(): void {
		config_set_global( 'file_upload_subdirectory_depth', 1 );
		config_set_global( 'file_upload_subdirectory_width', 2 );

		$t_row = $this->addAttachment();
		$t_upload = project_get_field( $this->project_id, 'file_path' );
		$t_expected = $t_upload . substr( $t_row['diskfile'], 0, 2 ) . DIRECTORY_SEPARATOR;

		$this->assertSame( $t_expected, $t_row['folder'] );
		$this->assertFileExists( $t_row['folder'] . $t_row['diskfile'] );
		$this->assertFileDoesNotExist( $t_upload . $t_row['diskfile'] );

		$t_content = file_get_content( (int)$t_row['id'] );
		$this->assertIsArray( $t_content );
		$this->assertSame( 'attachment payload', $t_content['content'] );

		file_delete( (int)$t_row['id'] );
		$this->assertFileDoesNotExist( $t_row['folder'] . $t_row['diskfile'] );
	}

	/**
	 * Attachments written before subdirectories were enabled keep working,
	 * because each one records where it actually is.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testExistingFlatAttachmentsSurviveEnablingSubdirectories(): void {
		config_set_global( 'file_upload_subdirectory_depth', 0 );
		$t_flat = $this->addAttachment();
		$t_flat_file = $t_flat['folder'] . $t_flat['diskfile'];

		config_set_global( 'file_upload_subdirectory_depth', 1 );
		config_set_global( 'file_upload_subdirectory_width', 2 );
		$t_nested = $this->addAttachment();

		# The two are stored differently and both resolve.
		$this->assertNotSame( $t_flat['folder'], $t_nested['folder'] );
		$this->assertFileExists( $t_flat_file );
		$this->assertFileExists( $t_nested['folder'] . $t_nested['diskfile'] );

		$t_content = file_get_content( (int)$t_flat['id'] );
		$this->assertIsArray( $t_content, 'Pre-existing attachment became unreadable' );
		$this->assertSame( 'attachment payload', $t_content['content'] );

		bug_delete( $this->issue_id );
		$this->issue_id = 0;
		$this->assertFileDoesNotExist( $t_flat_file );
		$this->assertFileDoesNotExist( $t_nested['folder'] . $t_nested['diskfile'] );
	}

	/**
	 * Runs admin/reorganize_attachments.php against the current configuration.
	 *
	 * @param string ...$p_args Extra command line arguments.
	 *
	 * @return array{0:int,1:string} Exit status and combined output.
	 */
	private function runReorganize( string ...$p_args ): array {
		# The layout the test set at runtime is not in config_inc.php, so hand
		# it to the child process. core.php has to be loaded first: it pulls in
		# config_defaults_inc.php, which would otherwise overwrite the values.
		$t_root = dirname( __DIR__, 2 );
		$t_script = sprintf(
			'require %s; $g_file_upload_subdirectory_depth = %d;'
				. ' $g_file_upload_subdirectory_width = %d; $argv = %s; require %s;',
			var_export( $t_root . '/core.php', true ),
			(int)config_get_global( 'file_upload_subdirectory_depth' ),
			(int)config_get_global( 'file_upload_subdirectory_width' ),
			var_export(
				array_merge( array( 'reorganize_attachments.php' ), $p_args ),
				true
			),
			var_export( $t_root . '/admin/reorganize_attachments.php', true )
		);

		$t_cmd = sprintf(
			'%s -r %s 2>&1',
			escapeshellarg( PHP_BINARY ),
			escapeshellarg( $t_script )
		);

		exec( $t_cmd, $t_output, $t_status );

		return array( $t_status, implode( "\n", $t_output ) );
	}

	/**
	 * The migration moves existing flat attachments into subdirectories, and
	 * back out again, keeping the folder column in step.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testReorganizeMovesAttachmentsBothWays(): void {
		config_set_global( 'file_upload_subdirectory_depth', 0 );
		$t_row = $this->addAttachment();
		$t_upload = project_get_field( $this->project_id, 'file_path' );
		$this->assertFileExists( $t_upload . $t_row['diskfile'] );

		# Flat -> nested.
		config_set_global( 'file_upload_subdirectory_depth', 1 );
		config_set_global( 'file_upload_subdirectory_width', 2 );
		list( $t_status, $t_output ) = $this->runReorganize();
		$this->assertSame( 0, $t_status, $t_output );

		$t_nested_dir = $t_upload . substr( $t_row['diskfile'], 0, 2 ) . DIRECTORY_SEPARATOR;
		$this->assertFileExists( $t_nested_dir . $t_row['diskfile'] );
		$this->assertFileDoesNotExist( $t_upload . $t_row['diskfile'] );
		$this->assertSame( $t_nested_dir, $this->attachmentRow( (int)$t_row['id'] )['folder'] );

		# Re-running is a no-op.
		list( $t_status, $t_output ) = $this->runReorganize();
		$this->assertSame( 0, $t_status, $t_output );
		$this->assertStringContainsString( 'moved 0', $t_output );
		$this->assertFileExists( $t_nested_dir . $t_row['diskfile'] );

		# Nested -> flat.
		config_set_global( 'file_upload_subdirectory_depth', 0 );
		list( $t_status, $t_output ) = $this->runReorganize();
		$this->assertSame( 0, $t_status, $t_output );

		$this->assertFileExists( $t_upload . $t_row['diskfile'] );
		$this->assertFileDoesNotExist( $t_nested_dir . $t_row['diskfile'] );
		$this->assertSame( $t_upload, $this->attachmentRow( (int)$t_row['id'] )['folder'] );
	}

	/**
	 * A dry run reports what it would do without touching anything.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testReorganizeDryRunChangesNothing(): void {
		config_set_global( 'file_upload_subdirectory_depth', 0 );
		$t_row = $this->addAttachment();
		$t_upload = project_get_field( $this->project_id, 'file_path' );

		config_set_global( 'file_upload_subdirectory_depth', 1 );
		config_set_global( 'file_upload_subdirectory_width', 2 );
		list( $t_status, $t_output ) = $this->runReorganize( '--dry-run' );

		$this->assertSame( 0, $t_status, $t_output );
		$this->assertStringContainsString( 'would move', $t_output );
		$this->assertFileExists( $t_upload . $t_row['diskfile'] );
		$this->assertSame( $t_upload, $this->attachmentRow( (int)$t_row['id'] )['folder'] );
	}

	/**
	 * A run interrupted between moving a file and recording its new location
	 * is recoverable: the attachment still resolves, and a second run repairs
	 * the folder column.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testReorganizeRecoversFromInterruption(): void {
		config_set_global( 'file_upload_subdirectory_depth', 0 );
		$t_row = $this->addAttachment();
		$t_upload = project_get_field( $this->project_id, 'file_path' );

		# Simulate a crash after rename() but before the UPDATE: the file is
		# in its new home while the database still points at the old one.
		config_set_global( 'file_upload_subdirectory_depth', 1 );
		config_set_global( 'file_upload_subdirectory_width', 2 );
		$t_nested_dir = $t_upload . substr( $t_row['diskfile'], 0, 2 ) . DIRECTORY_SEPARATOR;
		mkdir( $t_nested_dir, 0777, true );
		rename( $t_upload . $t_row['diskfile'], $t_nested_dir . $t_row['diskfile'] );

		# The attachment is still readable despite the stale folder value.
		$t_content = file_get_content( (int)$t_row['id'] );
		$this->assertIsArray( $t_content, 'Attachment unreadable after interruption' );

		list( $t_status, $t_output ) = $this->runReorganize();
		$this->assertSame( 0, $t_status, $t_output );

		$this->assertSame( $t_nested_dir, $this->attachmentRow( (int)$t_row['id'] )['folder'] );
		$this->assertFileExists( $t_nested_dir . $t_row['diskfile'] );
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
