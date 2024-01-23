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
 * Test fixture for user update webservice methods.
 *
 * @requires extension curl
 * @group REST
 */
class RestUserTests extends RestBase {

	/**
	 * Test /users/me API which users use to get information about themselves.
	 */
	public function testGetCurrentUser() {
		$t_response = $this->builder()->get( '/users/me' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_user = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_user['id'] ) );
		$this->assertTrue( is_numeric( $t_user['id'] ) );
		$this->assertTrue( isset( $t_user['name'] ) );
		$this->assertEquals( 'english', $t_user['language'] );
		$this->assertTrue( is_numeric( $t_user['access_level']['id'] ) );
		$this->assertTrue( isset( $t_user['access_level']['name'] ) );
		$this->assertTrue( isset( $t_user['access_level']['label'] ) );
		$this->assertGreaterThanOrEqual( 1, count( $t_user['projects'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['id'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['name'] ) );
	}

	/**
	 * Test creating a user as an anonymous user
	 */
	public function testCreateUserAnonymous() {
		$this->skipTestIfAnonymousDisabled();

		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->anonymous()->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
	}

	/**
	 * Test the use of POST /users to create users with just a username
	 *
	 * @dataProvider providerValidUserNames
	 *
	 * @param string $p_username
	 */
	public function testCreateUserMinimal( $p_username ) {
		$t_user_to_create = array(
			'name' => $p_username
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );

		$t_user = $t_body['user'];
		$this->assertTrue( isset( $t_user['id'] ) );
		$this->assertTrue( is_numeric( $t_user['id'] ) );
		$this->assertEquals( $t_user_to_create['name'], $t_user['name'] );
		$this->assertEquals( 'english', $t_user['language'] );
		$this->assertEquals( 25, $t_user['access_level']['id'] );
		$this->assertEquals( "reporter", $t_user['access_level']['name'] );
		$this->assertEquals( "reporter", $t_user['access_level']['label'] );
		$this->assertGreaterThanOrEqual( 1, count( $t_user['projects'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['id'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['name'] ) );
	}

	/**
	 * Test the use of POST /users to create users with all supported fields
	 */
	public function testCreateUserFull() {
		$t_user_to_create = array(
			'name' => Faker::username(),
			'real_name' => Faker::realname(),
			'email' => Faker::email(),
			'password' => Faker::password(),
			'access_level' => array( "name" => "developer" ),
			'protected' => false,
			'enabled' => false,
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );

		$t_user = $t_body['user'];
		$this->assertTrue( isset( $t_user['id'] ) );
		$this->assertTrue( is_numeric( $t_user['id'] ) );
		$this->assertEquals( $t_user_to_create['name'], $t_user['name'] );
		$this->assertEquals( $t_user_to_create['access_level']['name'], $t_user['access_level']['name'] );
		$this->assertEquals( $t_user_to_create['access_level']['name'], $t_user['access_level']['label'] );
		$this->assertGreaterThanOrEqual( 1, count( $t_user['projects'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['id'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['name'] ) );

		# TODO: test protected, enabled, language and timezone
	}

	/**
	 * Test creating users with duplicate usernames
	 */
	public function testCreateUserDuplicateUsername() {
		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(), 'create_duplicate_user' );
	}

	/**
	 * Test updating user
	 */
	public function testUpdateUser() {
		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );
		$t_user_id = $t_body['user']['id'];

		$t_updated_user = array(
			'name' => Faker::username(),
			'email' => Faker::email(),
			'real_name' => Faker::realname(),
			'access_level' => array( 'name' => 'manager' ),
			'enabled' => false,
			'protected' => false
		);

		$t_user_update = array(
			'user' => $t_updated_user
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );

		$t_user = $t_body['user'];
		$this->assertTrue( isset( $t_user['id'] ) );
		$this->assertTrue( is_numeric( $t_user['id'] ) );
		$this->assertEquals( $t_user_id, $t_user['id'] );
		$this->assertEquals( $t_updated_user['name'], $t_user['name'] );
	}

	/**
	 * Test updating user with duplicate username
	 */
	public function testUpdateUserDuplicateUsername() {
		$t_username_1 = Faker::username();
		$t_username_2 = Faker::username();

		$t_user_to_create = array(
			'name' => $t_username_1
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_user_to_create = array(
			'name' => $t_username_2
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );
		$t_user_id = $t_body['user']['id'];

		$t_updated_user = array(
			'name' => $t_username_1
		);

		$t_user_update = array(
			'user' => $t_updated_user
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test updating user with invalid username
	 *
	 * @dataProvider providerInvalidUserNames
	 */
	public function testUpdateUserInvalidName( $p_username ) {
		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );
		$t_user_id = $t_body['user']['id'];

		$t_updated_user = array(
			'name' => $p_username
		);

		$t_user_update = array(
			'user' => $t_updated_user
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test updating user with invalid username
	 */
	public function testUpdateUserAnonymous() {
		$this->skipTestIfAnonymousDisabled();

		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['user'] ) );
		$t_user_id = $t_body['user']['id'];

		$t_updated_user = array(
			'name' => Faker::username()
		);

		$t_user_update = array(
			'user' => $t_updated_user
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
	}

	/**
	 * Test getting an existing user by id.
	 */
	public function testGetUserById() {
		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'get_user_by_id: ' . $t_user_id );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['users'] ), 'users_element_exists' );
		$this->assertCount( 1, $t_body['users'], 'users_count' );

		$t_user = $t_body['users'][0];
		$this->assertTrue( isset( $t_user['id'] ), 'user id exists' );
		$this->assertTrue( is_numeric( $t_user['id'] ), 'user id numeric' );
		$this->assertEquals( $t_user_to_create['name'], $t_user['name'], 'username check' );
		$this->assertEquals( 'english', $t_user['language'], 'language' );
		$this->assertEquals( 25, $t_user['access_level']['id'], 'access level id' );
		$this->assertEquals( "reporter", $t_user['access_level']['name'], 'access level name' );
		$this->assertEquals( "reporter", $t_user['access_level']['label'], 'access level label' );
	}

	/**
	 * Test getting an existing user by username.
	 */
	public function testGetUserByUsername() {
		$t_user_to_create = array(
			'name' => Faker::username(),
			'email' => Faker::email(),
			'real_name' => Faker::realname(),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/users/username/' . $t_user_to_create['name'] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'get_user_by_name' );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['users'] ), 'users element exists by name' );
		$this->assertCount( 1, $t_body['users'], 'users count by name' );

		$t_user = $t_body['users'][0];
		$this->assertTrue( isset( $t_user['id'] ), 'user id element exists by username' );
		$this->assertEquals( $t_user_id, $t_user['id'], 'user id as expected by username' );
		$this->assertEquals( $t_user_to_create['name'], $t_user['name'], 'user name as expected by username' );
	}

	/**
	 * Test getting an existing user by id.
	 */
	public function testGetUserByIdSelect() {
		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->
			get( '/users/' . $t_user_id . '?select=id,name,projects' )->
			send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_body['users'] ) );
		$this->assertCount( 1, $t_body['users'] );
		$t_user = $t_body['users'][0];
		$this->assertTrue( isset( $t_user['id'] ) );
		$this->assertTrue( is_numeric( $t_user['id'] ) );
		$this->assertEquals( $t_user_to_create['name'], $t_user['name'] );
		$this->assertFalse( isset( $t_user['language'] ) );
		$this->assertFalse( isset( $t_user['access_level'] ) );
		$this->assertGreaterThanOrEqual( 1, count( $t_user['projects'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['id'] ) );
		$this->assertTrue( isset( $t_user['projects'][0]['name'] ) );

		# TODO: Showing of email and realname is dependent on the following thresholds
		# $g_show_user_email_threshold = NOBODY;
		# $g_show_user_realname_threshold = NOBODY;
	}

	/**
	 * Test getting a non-existent user by id.
	 */
	public function testGetUserByIdAnonymous() {
		$this->skipTestIfAnonymousDisabled();

		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/users/' . $t_user_id )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
	}

	/**
	 * Test getting a non-existent user by id.
	 */
	public function testGetUserByIdNotFoundAnonymous() {
		$this->skipTestIfAnonymousDisabled();

		$t_response = $this->builder()->get( '/users/1000000' )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );
	}

	/**
	 * Test getting a non-existent user by id.
	 */
	public function testGetUserByIdNotFound() {
		$t_response = $this->builder()->get( '/users/1000000' )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );
	}

	/**
	 * Test getting a non-existent user by id.
	 */
	public function testGetUserByIdZeroAnonymous() {
		$this->skipTestIfAnonymousDisabled();

		$t_response = $this->builder()->get( '/users/0' )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test getting a user by id zero
	 */
	public function testGetUserByIdZero() {
		$t_response = $this->builder()->get( '/users/0' )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test getting a user by a negative id
	 */
	public function testGetUserByIdNegative() {
		$t_response = $this->builder()->get( '/users/-1' )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test delete an existing user by id.
	 */
	public function testDeleteUserById() {
		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_response = $this->builder()->delete( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_NO_CONTENT, $t_response->getStatusCode() );

		# Try to delete user again
		$t_response = $this->builder()->delete( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Deleting non-existing user"
		);

		$t_response = $this->builder()->get( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );
	}

	/**
	 * Test delete an existing user by id.
	 */
	public function testDeleteUserByIdAnonymous() {
		$this->skipTestIfAnonymousDisabled();

		$t_user_to_create = array(
			'name' => Faker::username()
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_response = $this->builder()->delete( '/users/' . $t_user_id )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * Test deleting a non-existent user by id.
	 */
	public function testDeleteUserByIdNotFound() {
		$t_response = $this->builder()->delete( '/users/1000000' )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );
	}

	/**
	 * Test deleting a user by id zero.
	 */
	public function testDeleteUserByIdZero() {
		$t_response = $this->builder()->delete( '/users/0' )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test deleting the current logged in user.
	 */
	public function testDeleteCurrentUser() {
		$t_response = $this->builder()->get( '/users/me' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_user = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_user['id'];

		$t_response = $this->builder()->delete( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test attempt to delete self.
	 * 
	 * @return void
	 */
	public function testDeleteCurrentUserWithImpersonation() {
		$t_username = Faker::username();
		$t_user_to_create = array(
			'name' => $t_username,
			'access_level' => array( 'name' => 'administrator' )
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$t_user_id = $this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->delete( '/users/' . $t_user_id )->impersonate( $t_username )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test deleting the current logged in user (anonymous).
	 */
	public function testDeleteCurrentUserAnonymous() {
		$t_response = $this->builder()->get( '/users/me' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_user = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_user['id'];

		# if anonymous login enabled, this will not give 401
		# TODO: adapt / test with different settings for anonymous login
		$t_response = $this->builder()->delete( '/users/' . $t_user_id )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * @dataProvider providerInvalidUserNames
	 */
	public function testCreateUserInvalidUsername( $p_username ) {
		$t_user_to_create = array(
			'name' => $p_username
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Provides a set of invalid usernames
	 *
	 * @return array test cases
	 */
	public function providerInvalidUserNames() {
		return array(
			'blank_spaces' => array( ' ' ),
			'blank_tabs' => array( "\t" ),
			'empty' => array( '' ),
			'too_long' => array( Faker::randStr( 500 ) )
		);
	}

	/**
	 * Providers a set of valid usernames
	 *
	 * @return array test cases
	 */
	public function providerValidUserNames() {
		return array(
			'regular' => array( Faker::username() ),
			'with_spaces_in_middle' => array( "some user" ),
			'email' => array( 'vboctor@somedomain.com' ),
			'dot' => array( 'victor.boctor' ),
			'underscore' => array( 'victor_boctor' ),
		);
	}

}