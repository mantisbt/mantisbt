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
 * Test cases for Antispam API within mantis
 *
 * @package    Tests
 * @subpackage AntispamAPI
 * @copyright  Copyright 2023  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\ClientException;

/**
 * PHPUnit tests for Antispam API
 */
class AntispamApiTest extends MantisCoreBase {

	private static int $antispam_max_event_count;
	private static int $default_new_account_access_level;

	public static function setUpBeforeClass(): void {
		# Trick to trigger zero limit
		self::$antispam_max_event_count = config_get( 'antispam_max_event_count' );
		config_set_global( 'antispam_max_event_count', -1 );
		self::$default_new_account_access_level = config_get( 'default_new_account_access_level' );
		config_set_global( 'default_new_account_access_level', NOBODY );
	}

	public static function tearDownAfterClass(): void {
		config_set_global( 'antispam_max_event_count', self::$antispam_max_event_count );
		config_set_global( 'default_new_account_access_level', self::$default_new_account_access_level );
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
		$this->expectExceptionMessage( $p_expected );
		file_ensure_uploaded( $p_file );
	}

	/**
	 * Data provider for test_file_ensure_uploaded
	 *
	 * @return array
	 */
	public static function provider_file_ensure_uploaded(): array {
		return [
			'SPAM'
				=> [ [], 'rate limit' ],
		];
	}
}
