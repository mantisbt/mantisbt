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
require_once 'MantisCoreBase.php';

/**
 * PHPUnit tests for User API
 */
class MantisUserApiTest extends MantisCoreBase {

	const TEST_EMAIL = 'test@uniqueness.test';

	protected static $user_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$t_cookie = user_create(
			'User' . rand(),
			'password',
			self::TEST_EMAIL
		);
		/** @noinspection PhpUnhandledExceptionInspection */
		self::$user_id = user_get_id_by_cookie( $t_cookie );
	}

	public static function tearDownAfterClass() {
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
				=> array( 't_st@uniqueness.test', null, true ),
			"Non-existing email"
				=> array( 'unique@uniqueness.test', null, true ),
		];
	}

}
