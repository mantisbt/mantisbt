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

/**
 * Test cases for User API within mantis
 *
 * @package    Tests
 * @subpackage UserAPI
 * @copyright  Copyright 2023  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

# Includes
use Mantis\Exceptions\ClientException;

require_once 'MantisCoreBase.php';

/**
 * PHPUnit tests for User API
 */
class MantisUserApiTest extends MantisCoreBase {

	/** @var string Use of reserved TLD '.test' per RFC2606 */
	const TEST_EMAIL = 'test@uniqueness.test';

	protected static $user_id;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$t_cookie = user_create(
			'User' . rand(),
			'password',
			self::TEST_EMAIL
		);
		/** @noinspection PhpUnhandledExceptionInspection */
		self::$user_id = user_get_id_by_cookie( $t_cookie );
	}

	public static function tearDownAfterClass(): void {
		user_delete( self::$user_id );
	}


	/**
	 * Tests user_is_email_unique()
	 *
	 * @dataProvider providerEmailUnique
	 * @param string $p_email
	 * @param int    $p_user_id
	 * @param bool   $p_unique  Expected result.
	 */
	public function testEmailUnique( $p_email, $p_user_id, $p_unique ) {
		if( $p_user_id == -1 ) {
			$p_user_id = $this::$user_id;
		}
		$this->assertEquals( user_is_email_unique( $p_email, $p_user_id ), $p_unique );
	}

	/**
	 * Data provider for testEmailUnique().
	 *
	 * Set user_id to `-1` to use the id of the test user created in
	 * setUpBeforeClass(). This hack is needed because PHPUnit initializes the
	 * data provider before the setup method has created the test user account.
	 *
	 * @return array [email_address, user_id, unique]
	 */
	public function providerEmailUnique() {
		[$t_user, $t_domain] = explode( '@', self::TEST_EMAIL );
		$t_user_sql_like_pattern = substr_replace( $t_user, '_', 1, 1 );

		return [
			"Existing email, new user"
				=> array( self::TEST_EMAIL, null, false ),
			"Existing email, matching user"
				=> array( self::TEST_EMAIL, -1, true ),
			"Existing email, other user"
				=> array( self::TEST_EMAIL, 1, false ),
			"Existing email with different case"
				=> array( ucfirst(self::TEST_EMAIL), null, false ),
			"Email matching SQL LIKE pattern"
				=> array( "$t_user_sql_like_pattern@$t_domain", null, true ),
			"Non-existing email"
				=> array( "unique@$t_domain", null, true ),
		];
	}

	/**
	 * Tests user_get_id_by_email()
	 *
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public function testGetIdByEmail() {
		$t_user_id = $this::$user_id;
		$t_email_with_case_variation = ucfirst( self::TEST_EMAIL );

		$this->assertEquals( $t_user_id,
			user_get_id_by_email( self::TEST_EMAIL ),
			"User email found with exact case"
			 );
		$this->assertEquals( $t_user_id,
			user_get_id_by_email( $t_email_with_case_variation ),
			"User email found with different case"
		);

		// Allow non-unique emails and create a new user with duplicate email
		config_set_global( 'email_ensure_unique', false );
		$t_cookie = user_create(
			'DupeMail' . rand(),
			'password',
			$t_email_with_case_variation
		);
		$t_user_id = user_get_id_by_cookie( $t_cookie );

		$this->assertNotFalse(
			user_get_id_by_email( self::TEST_EMAIL ),
			"User found when multiple accounts with same email exist"
		);
		user_delete( $t_user_id );

		// Expected failures
		$this->assertFalse(
			user_get_id_by_email( rand() . self::TEST_EMAIL ),
			"Non-existing email not found"
			 );

		// Same test but with exception
		$this->expectException( ClientException::class );
		user_get_id_by_email( rand() . self::TEST_EMAIL, true );
	}

}
