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
 * Test cases for File API within mantis
 *
 * @package    Tests
 * @subpackage FileAPI
 * @copyright  Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\ClientException;

/**
 * PHPUnit tests for File API
 */
class FileApiTest extends MantisCoreBase {

	const src_name = __DIR__ . '/../../images/mantis_logo.png';

	private static int $max_file_size;
	private static string $tmp_name;
	private static string $zero_name;
	private static string $big_name;

	public static function setUpBeforeClass(): void {
		self::$max_file_size = config_get_global( 'max_file_size' );
		config_set_global( 'max_file_size', filesize( self::src_name ) );
	}

	public static function tearDownAfterClass(): void {
		config_set_global( 'max_file_size', self::$max_file_size );

		unlink( self::$tmp_name );
		unlink( self::$zero_name );
		unlink( self::$big_name );
	}

	/**
	 * Tests file_ensure_uploaded
	 *
	 * @group FileApi
	 * @dataProvider provider_file_ensure_uploaded
	 * @param array $p_file		Input
	 * @param mixed $p_expected	Expected result
	 * @return void
	 */
	public function test_file_ensure_uploaded( $p_file, $p_expected ): void {
		if( is_subclass_of( $p_expected, '\Throwable' ) ) {
			$this->expectException( $p_expected );
		} elseif( is_string( $p_expected ) ) {
			$this->expectExceptionMessage( $p_expected );
		} else {
			$this->expectNotToPerformAssertions();
		}
		file_ensure_uploaded( $p_file );
	}

	/**
	 * Data provider for test_file_ensure_uploaded
	 *
	 * @return array
	 */
	public static function provider_file_ensure_uploaded(): array {
		self::$tmp_name = tempnam( sys_get_temp_dir(), 'tst' );
		copy( self::src_name, self::$tmp_name );

		self::$zero_name = tempnam( sys_get_temp_dir(), 'tst' );
		touch( self::$zero_name );

		self::$big_name = tempnam( sys_get_temp_dir(), 'tst' );
		copy( self::src_name, self::$big_name );
		$t_big_file = fopen( self::$big_name, 'a' );
		fwrite( $t_big_file, ' ' ); # + 1 byte
		fclose( $t_big_file );

		return [
			'Error type'
				=> [ null, \TypeError::class ],
			'No "name" key'
				=> [ [], 'name is empty' ],
			'Empty "name" key'
				=> [ [ 'name' => '' ], 'name is empty' ],
			'UPLOAD_ERR_INI_SIZE'
				=> [ [ 'name' => 'mantis_logo.png', 'error' => UPLOAD_ERR_INI_SIZE ], 'too big' ],
			'UPLOAD_ERR_FORM_SIZE'
				=> [ [ 'name' => 'mantis_logo.png', 'error' => UPLOAD_ERR_FORM_SIZE ], 'too big' ],
			'UPLOAD_ERR_PARTIAL'
				=> [ [ 'name' => 'mantis_logo.png', 'error' => UPLOAD_ERR_PARTIAL ], 'upload failure' ],
			'UPLOAD_ERR_NO_FILE'
				=> [ [ 'name' => 'mantis_logo.png', 'error' => UPLOAD_ERR_NO_FILE ], 'upload failure' ],
			'UPLOAD_ERR_CANT_WRITE'
				=> [ [ 'name' => 'mantis_logo.png', 'error' => UPLOAD_ERR_CANT_WRITE ], 'upload failure' ],
			'Too long name'
				=> [ [ 'name' => str_pad( '', DB_FIELD_SIZE_FILENAME + 1 ) ], 'is too long' ],
			'Invalid type'
				=> [ [ 'name' => 'mantis_logo.svg' ], 'type not allowed' ],
			'No "tmp_name" key'
				=> [ [ 'name' => 'mantis_logo.png' ], 'path is empty' ],
			'Empty "tmp_name" key'
				=> [ [ 'name' => 'mantis_logo.png', 'tmp_name' => '' ], 'path is empty' ],
			'Missed file'
				=> [ [ 'name' => 'mantis_logo.png', 'tmp_name' => 'foo' ], 'is not readable' ],
			'Zero size file'
				=> [ [ 'name' => 'mantis_logo.png', 'tmp_name' => self::$zero_name ], 'not uploaded' ],
			'Big size file'
				=> [ [ 'name' => 'mantis_logo.png', 'tmp_name' => self::$big_name ], 'too big' ],
			'Good file'
				=> [ [ 'name' => 'mantis_logo.png', 'tmp_name' => self::$tmp_name, 'error' => UPLOAD_ERR_OK ], null ],
		];
	}
}
