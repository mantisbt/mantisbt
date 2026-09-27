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
 * Test cases for attachment storage subdirectories.
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
 * PHPUnit tests for file_subdirectory_path() and the directory it creates.
 */
class FileSubdirectoryTest extends MantisCoreBase {

	/**
	 * @var int Saved configuration, restored during teardown.
	 */
	private static $depth;

	/**
	 * @var int Saved configuration, restored during teardown.
	 */
	private static $width;

	/**
	 * @var string[] Directories to remove during teardown.
	 */
	private $temp_dirs = array();

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
	 * Removes directories created by the tests.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		# Deepest first, so that a parent is never removed before its children.
		$t_dirs = $this->temp_dirs;
		usort( $t_dirs, function( $a, $b ) {
			return strlen( $b ) <=> strlen( $a );
		} );
		foreach( $t_dirs as $t_dir ) {
			if( is_dir( $t_dir ) ) {
				rmdir( $t_dir );
			}
		}
		$this->temp_dirs = array();

		parent::tearDown();
	}

	/**
	 * Applies a subdirectory layout.
	 *
	 * @param int $p_depth Number of levels.
	 * @param int $p_width Characters per level.
	 *
	 * @return void
	 */
	private function setLayout( int $p_depth, int $p_width ): void {
		config_set_global( 'file_upload_subdirectory_depth', $p_depth );
		config_set_global( 'file_upload_subdirectory_width', $p_width );
	}

	/**
	 * Layouts and the subdirectory they produce.
	 *
	 * @return array
	 */
	public static function layoutProvider(): array {
		$t_sep = DIRECTORY_SEPARATOR;
		$t_md5 = '0123456789abcdef0123456789abcdef';
		$t_uuid = '0000d5b1-9793-4bfb-baab-817dc6647f52';

		return array(
			'disabled by default' => array( 0, 2, $t_md5, '' ),
			'negative depth is disabled' => array( -1, 2, $t_md5, '' ),
			'zero width is disabled' => array( 1, 0, $t_md5, '' ),

			'one level of two' => array( 1, 2, $t_md5, '01' . $t_sep ),
			'two levels of two' => array( 2, 2, $t_md5, '01' . $t_sep . '23' . $t_sep ),
			'one level of three' => array( 1, 3, $t_md5, '012' . $t_sep ),
			'one level of one' => array( 1, 1, $t_md5, '0' . $t_sep ),

			# A UUID offers only 8 hexadecimal characters before its first
			# separator, so deeper layouts must not reach past them.
			'uuid, one level' => array( 1, 2, $t_uuid, '00' . $t_sep ),
			'uuid, four levels fit exactly' => array(
				4, 2, $t_uuid,
				'00' . $t_sep . '00' . $t_sep . 'd5' . $t_sep . 'b1' . $t_sep
			),
			'uuid, five levels do not fit' => array( 5, 2, $t_uuid, '' ),

			'name shorter than requested depth' => array( 4, 2, 'abcd', '' ),
			'non hexadecimal name' => array( 1, 2, 'zzzz', '' ),
			'empty name' => array( 1, 2, '', '' ),
		);
	}

	/**
	 * The subdirectory is derived from the leading characters of the name.
	 *
	 * @dataProvider layoutProvider
	 *
	 * @param int    $p_depth    Number of levels.
	 * @param int    $p_width    Characters per level.
	 * @param string $p_filename Disk file name.
	 * @param string $p_expected Expected subdirectory.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testSubdirectoryPath( int $p_depth, int $p_width, string $p_filename, string $p_expected ): void {
		$this->setLayout( $p_depth, $p_width );

		$this->assertSame( $p_expected, file_subdirectory_path( $p_filename ) );
	}

	/**
	 * Every name maps into the configured number of buckets.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testSubdirectoryPathIsStableAndBounded(): void {
		$this->setLayout( 1, 2 );

		$t_seen = array();
		for( $i = 0; $i < 500; $i++ ) {
			$t_name = md5( (string)$i );
			$t_path = file_subdirectory_path( $t_name );

			$this->assertSame(
				$t_path,
				file_subdirectory_path( $t_name ),
				'Subdirectory must depend only on the name'
			);
			$this->assertSame( substr( $t_name, 0, 2 ) . DIRECTORY_SEPARATOR, $t_path );

			$t_seen[$t_path] = true;
		}

		$this->assertLessThanOrEqual( 256, count( $t_seen ) );
	}

	/**
	 * The upload directory is returned unchanged when the layout is disabled,
	 * and no directory is created.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testEnsureSubdirectoryIsANoOpWhenDisabled(): void {
		$this->setLayout( 0, 2 );
		$t_upload = $this->makeTempDir();

		$this->assertSame(
			$t_upload,
			file_ensure_upload_subdirectory( $t_upload, '0123456789abcdef0123456789abcdef' )
		);
		$this->assertSame( array(), glob( $t_upload . '*' ) );
	}

	/**
	 * The subdirectory is created, and creating it again is harmless.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testEnsureSubdirectoryCreatesTheDirectory(): void {
		$this->setLayout( 2, 2 );
		$t_upload = $this->makeTempDir();
		$t_name = '0123456789abcdef0123456789abcdef';

		$t_path = file_ensure_upload_subdirectory( $t_upload, $t_name );
		$this->temp_dirs[] = $t_upload . '01' . DIRECTORY_SEPARATOR . '23' . DIRECTORY_SEPARATOR;
		$this->temp_dirs[] = $t_upload . '01' . DIRECTORY_SEPARATOR;

		$this->assertSame(
			$t_upload . '01' . DIRECTORY_SEPARATOR . '23' . DIRECTORY_SEPARATOR,
			$t_path
		);
		$this->assertDirectoryExists( $t_path );

		# Idempotent: an existing directory is reused rather than reported as
		# a failure.
		$this->assertSame( $t_path, file_ensure_upload_subdirectory( $t_upload, $t_name ) );
	}

	/**
	 * A new subdirectory inherits the upload directory's permissions.
	 *
	 * @group FileApi
	 * @return void
	 */
	public function testEnsureSubdirectoryInheritsPermissions(): void {
		$this->setLayout( 1, 2 );
		$t_upload = $this->makeTempDir();
		chmod( $t_upload, 0750 );

		$t_path = file_ensure_upload_subdirectory( $t_upload, 'ab0123456789abcdef0123456789abcd' );
		$this->temp_dirs[] = $t_path;

		$this->assertSame( 0750, fileperms( $t_path ) & 0777 );
	}

	/**
	 * Creates a writable directory, registered for teardown.
	 *
	 * @return string Path with a trailing directory separator.
	 */
	private function makeTempDir(): string {
		$t_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR
			. uniqid( 'mantis-subdir-' ) . DIRECTORY_SEPARATOR;
		mkdir( $t_dir, 0777, true );
		$this->temp_dirs[] = $t_dir;
		return $t_dir;
	}
}
