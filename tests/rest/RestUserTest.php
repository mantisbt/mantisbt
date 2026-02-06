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

namespace Mantis\tests\rest;

use Mantis\tests\core\Faker;

/**
 * Test fixture for user update webservice methods.
 *
 * @requires extension curl
 * @group REST
 */
class RestUserTest extends RestBase {

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
	 * Test updating user real_name and email to new values
	 */
	public function testUpdateUserRealNameAndEmail() {
		$t_user_to_create = array(
			'name' => Faker::username(),
			'real_name' => Faker::realname(),
			'email' => Faker::email(),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];

		$t_new_realname = Faker::realname();
		$t_new_email = Faker::email();

		$t_user_update = array(
			'user' => array(
				'real_name' => $t_new_realname,
				'email' => $t_new_email,
			)
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'update_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user = $t_body['user'];
		$this->assertEquals( $t_new_realname, $t_user['real_name'], 'real_name updated' );
		$this->assertEquals( $t_new_email, $t_user['email'], 'email updated' );
	}

	/**
	 * Test updating user real_name to empty value
	 */
	public function testUpdateUserRealNameToEmpty() {
		$t_user_to_create = array(
			'name' => Faker::username(),
			'real_name' => Faker::realname(),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];

		# Verify real_name was set on created user
		$this->assertEquals(
			$t_user_to_create['real_name'],
			$t_body['user']['real_name'],
			'real_name set on created user'
		);

		$t_user_update = array(
			'user' => array(
				'real_name' => '',
			)
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'update_user' );

		# Empty real_name is omitted from the API response
		$t_body = json_decode( $t_response->getBody(), true );
		$t_user = $t_body['user'];
		$this->assertArrayNotHasKey( 'real_name', $t_user, 'real_name cleared' );
	}

	/**
	 * Test updating user email to empty value
	 */
	public function testUpdateUserEmailToEmpty() {
		$t_user_to_create = array(
			'name' => Faker::username(),
			'email' => Faker::email(),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];

		# Verify email was set on created user
		$this->assertEquals(
			$t_user_to_create['email'],
			$t_body['user']['email'],
			'email set on created user'
		);

		$t_user_update = array(
			'user' => array(
				'email' => '',
			)
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'update_user' );

		# Empty email is omitted from the API response
		$t_body = json_decode( $t_response->getBody(), true );
		$t_user = $t_body['user'];
		$this->assertArrayNotHasKey( 'email', $t_user, 'email cleared' );
	}

	/**
	 * Test that an admin can clear another user's email even when allow_blank_email is OFF.
	 *
	 * Administrators are always allowed to set blank emails regardless of the
	 * allow_blank_email config, as they may need to manage accounts that should
	 * never receive email notifications (e.g. anonymous account).
	 */
	public function testAdminUpdateUserEmailToEmptyBlankEmailNotAllowed() {
		$t_user_to_create = array(
			'name' => Faker::username(),
			'email' => Faker::email(),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];

		# Explicitly set allow_blank_email to OFF
		$t_old_value = config_get( 'allow_blank_email' );
		config_set( 'allow_blank_email', OFF );

		try {
			$t_user_update = array(
				'user' => array(
					'email' => '',
				)
			);

			# Request as admin (no impersonation) - should succeed due to admin exemption
			$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
			$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
				'admin clearing email with allow_blank_email=OFF should succeed' );

			$t_body = json_decode( $t_response->getBody(), true );
			$t_user = $t_body['user'];
			$this->assertArrayNotHasKey( 'email', $t_user, 'email cleared' );
		} finally {
			config_set( 'allow_blank_email', $t_old_value );
		}
	}

	/**
	 * Test that a non-admin user cannot clear their email when allow_blank_email is OFF.
	 *
	 * When $g_allow_blank_email = OFF, non-admin users should not be able to set an
	 * empty email address. The validation in email_is_valid() only allows blank emails
	 * when allow_blank_email is ON or the current user is an administrator.
	 */
	public function testUpdateOwnEmailToEmptyBlankEmailNotAllowed() {
		$t_username = Faker::username();
		$t_user_to_create = array(
			'name' => $t_username,
			'email' => Faker::email(),
			'access_level' => array( 'name' => 'reporter' ),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];

		# Ensure allow_blank_email is OFF (default)
		$t_old_value = config_get( 'allow_blank_email' );
		config_set( 'allow_blank_email', OFF );

		try {
			$t_user_update = array(
				'user' => array(
					'email' => '',
				)
			);

			# Impersonate reporter user to act as a non-admin
			$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )
				->impersonate( $t_username )->send();
			$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
				'non-admin clearing email with allow_blank_email=OFF should fail' );
		} finally {
			config_set( 'allow_blank_email', $t_old_value );
		}
	}

	/**
	 * Test that a non-admin user can clear their email when allow_blank_email is ON.
	 */
	public function testUpdateOwnEmailToEmptyBlankEmailAllowed() {
		$t_username = Faker::username();
		$t_user_to_create = array(
			'name' => $t_username,
			'email' => Faker::email(),
			'access_level' => array( 'name' => 'reporter' ),
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];

		# Enable allow_blank_email
		$t_old_value = config_get( 'allow_blank_email' );
		config_set( 'allow_blank_email', ON );

		try {
			$t_user_update = array(
				'user' => array(
					'email' => '',
				)
			);

			# Impersonate reporter user to act as a non-admin
			$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )
				->impersonate( $t_username )->send();
			$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
				'non-admin clearing email with allow_blank_email=ON should succeed' );

			$t_body = json_decode( $t_response->getBody(), true );
			$t_user = $t_body['user'];
			$this->assertArrayNotHasKey( 'email', $t_user, 'email cleared' );
		} finally {
			config_set( 'allow_blank_email', $t_old_value );
		}
	}

	/**
	 * Test that real_name is preserved when not included in the update request
	 */
	public function testUpdateUserPreservesRealNameWhenNotProvided() {
		$t_realname = Faker::realname();
		$t_user_to_create = array(
			'name' => Faker::username(),
			'real_name' => $t_realname,
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];
		$this->assertEquals( $t_realname, $t_body['user']['real_name'], 'real_name set on created user' );

		# Update only enabled flag, real_name is not in the request
		$t_user_update = array(
			'user' => array(
				'enabled' => true,
			)
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'update_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user = $t_body['user'];
		$this->assertEquals( $t_realname, $t_user['real_name'], 'real_name preserved' );
	}

	/**
	 * Test that email is preserved when not included in the update request
	 */
	public function testUpdateUserPreservesEmailWhenNotProvided() {
		$t_email = Faker::email();
		$t_user_to_create = array(
			'name' => Faker::username(),
			'email' => $t_email,
		);

		$t_response = $this->builder()->post( '/users', $t_user_to_create )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(), 'create_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user_id = $t_body['user']['id'];
		$this->assertEquals( $t_email, $t_body['user']['email'], 'email set on created user' );

		# Update only enabled flag, email is not in the request
		$t_user_update = array(
			'user' => array(
				'enabled' => true,
			)
		);

		$t_response = $this->builder()->patch( '/users/' . $t_user_id, $t_user_update )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(), 'update_user' );

		$t_body = json_decode( $t_response->getBody(), true );
		$t_user = $t_body['user'];
		$this->assertEquals( $t_email, $t_user['email'], 'email preserved' );
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
	public static function providerInvalidUserNames() {
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
	public static function providerValidUserNames() {
		return array(
			'regular' => array( Faker::username() ),
			'with_spaces_in_middle' => array( "some user" ),
			'email' => array( 'vboctor@somedomain.com' ),
			'dot' => array( 'victor.boctor' ),
			'underscore' => array( 'victor_boctor' ),
		);
	}

}