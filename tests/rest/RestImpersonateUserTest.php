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
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'RestBase.php';

/**
 * Test fixture for user impersonation via API.
 *
 * @requires extension curl
 * @group REST
 */
class RestImpersonateUserTests extends RestBase {

	/**
	 * Test /users/me API without impersonation
	 */
	public function testWithoutImpersonation() {
		$t_response = $this->builder()->get( '/users/me' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * Test /users/me API with impersonation for user that doesn't exist.
	 */
	public function testImpersonateUserDoesntExist() {
		$t_response = $this->builder()->get( '/users/me' )->impersonate( 'DoesNotExist' )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );
	}

	/**
	 * Test /users/me API with impersonating self
	 */
	public function testImpersonateSelf() {
		$t_response = $this->builder()->get( '/users/me' )->impersonate( 'administrator' )->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
	}

	/**
	 * Test /users/me API with impersonation for user that exists.
	 */
	public function testImpersonateUser() {
		$t_username = Faker::username();

		$t_user_to_create = array(
			'name' => $t_username,
			'access_level' => array( 'name' => 'viewer' )
		);

		# Create a user
		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		# Validate admin user information
		$t_response = $this->builder()->get( '/users/me' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_user = json_decode( $t_response->getBody(), true );
		$this->assertNotEquals( $t_username, $t_user['name'] );

		# Validate impersonated user information
		$t_response = $this->builder()->get( '/users/me' )->impersonate( $t_username )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_user = json_decode( $t_response->getBody(), true );
		$this->assertEquals( $t_username, $t_user['name'] );
		$this->assertEquals( $t_user_to_create['access_level']['name'], $t_user['access_level']['name'] );
	}

	/**
	 * Test /users/me API with impersonation for a disabled user
	 */
	public function testImpersonateUserDisabled() {
		$t_username = Faker::username();

		$t_user_to_create = array(
			'name' => $t_username,
			'access_level' => array( 'name' => 'viewer' ),
			'enabled' => false
		);

		# Create a user
		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		# Validate impersonated user information
		$t_response = $this->builder()->get( '/users/me' )->impersonate( $t_username )->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
	}

	/**
	 * Test /users/me API with impersonation where user doesn't have impersonation access level
	 */
	public function testImpersonateUserWithoutImpersonationAccessLevel() {
		$t_username = Faker::username();

		$t_user_to_create = array(
			'name' => $t_username,
			'access_level' => array( 'name' => 'viewer' ),
			'enabled' => false
		);

		# Create a user
		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );
		$t_user = $t_body['user'];

		# Create API token for user
		$t_response = $this->builder()->post( '/users/' . $t_user['id'] . '/token', $t_user_to_create )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );
		$t_token = $t_body['token'];

		# Validate impersonating disabled user fails
		$t_response = $this->builder()->get( '/users/me' )->token( $t_token )->impersonate( 'administrator' )->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
	}
}
