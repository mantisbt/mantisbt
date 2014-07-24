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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture which verifies login mechanisms
 *
 * @requires extension soap
 * @group SOAP
 */
class LoginTest extends SoapBase {

	/**
	 * Fake username
	 */
	private $dummyUser = 'no';

	/**
	 * Fake Password
	 */
	private $dummyPassword = 'user';

	/**
	 * Test Fake Login details fail
	 * @return void
	 */
	public function testLoginFailed() {
		try {
			$this->client->mc_login( $this->dummyUser, $this->dummyPassword );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests a client login and that account data returned is valid
	 * @return void
	 */
	public function testLoginSuccessfully() {
		$t_user_data = $this->client->mc_login( $this->userName, $this->password );

		$this->assertEquals( $this->userName, $t_user_data->account_data->name, 'name' );
		$this->assertEquals( $this->userId, $t_user_data->account_data->id, 'id' );
		$this->assertEquals( $this->email, $t_user_data->account_data->email, 'email' );
		$this->assertEquals( false, empty( $t_user_data->timezone ), 'timezone' );
		$this->assertEquals( 90, (integer)$t_user_data->access_level, 'access_level' );
	}

	/**
	 * Tests issue_get fails with an invalid login
	 * @return void
	 */
	public function testGetIssueGetLoginFailed() {
		try {
			$this->client->mc_issue_get( $this->dummyUser, $this->dummyPassword, 1 );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests project_get_users fails with an invalid login
	 * @return void
	 */
	public function testProjectGetUsersLoginFailed() {
		try {
			$this->client->mc_project_get_users( $this->dummyUser, $this->dummyPassword, $this->getProjectId(), 0 );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests enum_status fails with an invalid login
	 * @return void
	 */
	public function testGetEnumStatusLoginFailed() {
		try {
			$this->client->mc_enum_status( $this->dummyUser, $this->dummyPassword );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests project_get_issues fails with an invalid login
	 * @return void
	 */
	public function testProjectGetIssuesLoginFailed() {
		try {
			$this->client->mc_project_get_issues( $this->dummyUser, $this->dummyPassword, $this->getProjectId(), 0, 15 );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests filter_get_issues fails with an invalid login
	 * @return void
	 */
	public function testFilterGetIssuesLoginFailed() {
		try {
			$this->client->mc_filter_get_issues( $this->dummyUser, $this->dummyPassword, $this->getProjectId(), 1, 0, 15 );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests Login with a null password is rejected
	 * @return void
	 */
	public function testLoginWithNullPasswordIsRejected() {
		try {
			$this->client->mc_enum_status( $this->userName, null );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests Login with an empty password is rejected
	 * @return void
	 */
	public function testLoginWithEmptyPasswordIsRejected() {
		try {
			$this->client->mc_enum_status( $this->userName, '' );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Tests Login with the wrong password is rejected
	 * @return void
	 */
	public function testLoginWithIncorrectPasswordIsRejected() {
		try {
			$this->client->mc_enum_status( $this->userName, 'This really should be incorrect' );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertIsLoginFailure( $e );
		}
	}

	/**
	 * Check SOAP login failure is an access denied
	 * @param SoapFault $e A SoapFault Exception.
	 * @return void
	 */
	private function assertIsLoginFailure( SoapFault $e ) {
		$this->assertRegexp( '/Access denied/i', $e->getMessage() );
	}
}
