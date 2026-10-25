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

/**
 * Test fixture for project category REST operations.
 *
 * @group REST
 */
class RestProjectCategoryTest extends RestBase {
	/** @var string */
	private $base_url;

	/**
	 * Create a category through the REST API.
	 *
	 * @param array $p_data Category payload.
	 * @return array Category representation.
	 */
	private function createCategory( array $p_data ): array {
		$t_response = $this->builder()->post( $this->base_url, $p_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		return json_decode( $t_response->getBody(), true )['category'];
	}

	/**
	 * Delete a category through the REST API.
	 *
	 * @param int $p_category_id Category identifier.
	 * @return void
	 */
	private function deleteCategory( int $p_category_id ): void {
		$t_response = $this->builder()->delete( $this->base_url . $p_category_id )->send();
		$this->assertEquals( HTTP_STATUS_NO_CONTENT, $t_response->getStatusCode() );
	}

	/**
	 * Set up the project category endpoint.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->base_url = '/projects/' . $this->getProjectId() . '/categories/';
	}

	/**
	 * Test creating, retrieving, updating, and deleting a project category.
	 *
	 * @return void
	 */
	public function testProjectCategoryCrud() {
		$t_name = 'REST category ' . rand( 1, 1000000 );
		$t_category = $this->createCategory( [ 'name' => $t_name ] );
		$this->assertEquals( $t_name, $t_category['name'] );
		$this->assertEquals( $this->getProjectId(), $t_category['project']['id'] );
		$this->assertTrue( $t_category['enabled'] );
		$this->assertArrayNotHasKey( 'status', $t_category );

		$t_id = $t_category['id'];
		$t_response = $this->builder()->get( $this->base_url . $t_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_category = json_decode( $t_response->getBody(), true )['categories'][0];
		$this->assertEquals( $t_id, $t_category['id'] );
		$this->assertEquals( $this->getProjectId(), $t_category['project']['id'] );
		$this->assertTrue( $t_category['enabled'] );
		$this->assertArrayNotHasKey( 'status', $t_category );

		$t_response = $this->builder()->patch( $this->base_url . $t_id, [ 'name' => $t_name . ' updated' ] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$this->assertEquals( $t_name . ' updated', json_decode( $t_response->getBody(), true )['category']['name'] );

		$this->deleteCategory( $t_id );
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $this->builder()->get( $this->base_url . $t_id )->send()->getStatusCode() );
	}

	/**
	 * Test retrieving the project category collection.
	 *
	 * @return void
	 */
	public function testProjectCategoryList() {
		$t_category = $this->createCategory( [ 'name' => 'REST listed category ' . rand( 1, 1000000 ) ] );

		$t_response = $this->builder()->get( $this->base_url )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_categories = json_decode( $t_response->getBody(), true )['categories'];
		$this->assertContains( $t_category['id'], array_column( $t_categories, 'id' ) );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that category handlers are hidden without manage project access.
	 *
	 * @return void
	 */
	public function testProjectCategoryHandlerRequiresManageAccess() {
		$t_category = $this->createCategory( [
			'name' => 'REST hidden handler category ' . rand( 1, 1000000 ),
			'handler' => [ 'id' => $this->userId ],
		] );
		$t_project_id = $this->getProjectId();
		$t_had_database_value = config_is_set_in_database( 'manage_project_threshold', ALL_USERS, $t_project_id );
		$t_old_value = config_get( 'manage_project_threshold', null, ALL_USERS, $t_project_id );
		config_set( 'manage_project_threshold', NOBODY, ALL_USERS, $t_project_id );

		try {
			$t_response = $this->builder()->get( $this->base_url . $t_category['id'] )->send();
			$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
			$t_category = json_decode( $t_response->getBody(), true )['categories'][0];
			$this->assertArrayNotHasKey( 'handler', $t_category );
		} finally {
			if( $t_had_database_value ) {
				config_set( 'manage_project_threshold', $t_old_value, ALL_USERS, $t_project_id );
			} else {
				config_delete( 'manage_project_threshold', ALL_USERS, $t_project_id );
			}
			$this->deleteCategory( $t_category['id'] );
		}
	}

	/**
	 * Test category handler and enabled fields through add and update.
	 *
	 * @return void
	 */
	public function testProjectCategoryHandlerAndEnabledFields() {
		$t_category = $this->createCategory( [
			'name' => 'REST assigned category ' . rand( 1, 1000000 ),
			'handler' => [ 'id' => $this->userId ],
			'enabled' => false,
		] );
		$this->assertFalse( $t_category['enabled'] );
		$this->assertArrayNotHasKey( 'status', $t_category );
		$this->assertEquals( $this->userId, $t_category['handler']['id'] );

		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [
			'handler' => [ 'name' => $this->userName ],
			'enabled' => true,
		] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_category = json_decode( $t_response->getBody(), true )['category'];
		$this->assertTrue( $t_category['enabled'] );
		$this->assertArrayNotHasKey( 'status', $t_category );
		$this->assertEquals( $this->getProjectId(), $t_category['project']['id'] );
		$this->assertEquals( $this->userId, $t_category['handler']['id'] );

		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [ 'handler' => null ] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_category = json_decode( $t_response->getBody(), true )['category'];
		$this->assertArrayNotHasKey( 'handler', $t_category );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that the REST API ignores the SOAP-only status field.
	 *
	 * @return void
	 */
	public function testProjectCategoryStatusFieldIsNotSupported() {
		$t_response = $this->builder()->post( $this->base_url, [
			'name' => 'REST status category ' . rand( 1, 1000000 ),
			'status' => CATEGORY_STATUS_DISABLED,
		] )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_category = json_decode( $t_response->getBody(), true )['category'];
		$this->assertTrue( $t_category['enabled'] );
		$this->assertArrayNotHasKey( 'status', $t_category );
		$this->deleteCategory( $t_category['id'] );

		$t_category = $this->createCategory( [ 'name' => 'REST status update category ' . rand( 1, 1000000 ) ] );
		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [ 'status' => CATEGORY_STATUS_DISABLED ] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_updated_category = json_decode( $t_response->getBody(), true )['category'];
		$this->assertTrue( $t_updated_category['enabled'] );
		$this->assertArrayNotHasKey( 'status', $t_updated_category );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that assigned users must exist when adding or updating categories.
	 *
	 * @return void
	 */
	public function testProjectCategoryHandlerMustExist() {
		$t_response = $this->builder()->post( $this->base_url, [
			'name' => 'REST invalid handler ' . rand( 1, 1000000 ),
			'handler' => [ 'id' => 1000000 ],
		] )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );

		$t_category = $this->createCategory( [ 'name' => 'REST update handler ' . rand( 1, 1000000 ) ] );
		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [ 'handler' => [ 'id' => 1000000 ] ] )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that duplicate category names are rejected.
	 *
	 * @return void
	 */
	public function testProjectCategoryDuplicateName() {
		$t_name = 'REST duplicate category ' . rand( 1, 1000000 );
		$t_category = $this->createCategory( [ 'name' => $t_name ] );

		$t_response = $this->builder()->post( $this->base_url, [ 'name' => $t_name ] )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that the configured default category cannot be deleted.
	 *
	 * @return void
	 */
	public function testProjectCategoryCannotDeleteDefault() {
		$t_default_category_id = config_get( 'default_category_for_moves' );
		if( category_exists( $t_default_category_id ) ) {
			$t_default_category_project = (int)category_get_field( $t_default_category_id, 'project_id' );
			$t_skip = $t_default_category_project !== ALL_PROJECTS && $t_default_category_project !== $this->getProjectId();
		} else {
			$t_skip = true;
		}
		if( $t_skip ) {
			$this->markTestSkipped( 'The configured default category does not exist in the test project.' );
		}

		# Check varies depending on whether category is global or project-specific
		/** @noinspection PhpUndefinedVariableInspection */
		if( ALL_PROJECTS == $t_default_category_project ) {
			$t_endpoint = '/projects/0/categories/';
			# category_ensure_can_remove() triggers ERROR_CATEGORY_CANNOT_UPDATE_DEFAULT which maps to 500
			$t_expected = HTTP_STATUS_INTERNAL_SERVER_ERROR;
		} else {
			$t_endpoint = $this->base_url;
			$t_expected = HTTP_STATUS_BAD_REQUEST;
		}

		$t_response = $this->builder()->delete( $t_endpoint . $t_default_category_id )->send();
		$this->assertEquals( $t_expected, $t_response->getStatusCode() );
	}

	/**
	 * Test that anonymous users cannot update or delete categories.
	 *
	 * @return void
	 */
	public function testProjectCategoryAnonymousAccess() {
		$this->skipTestIfAnonymousDisabled();
		$t_category = $this->createCategory( [ 'name' => 'REST anonymous category ' . rand( 1, 1000000 ) ] );

		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [ 'name' => 'should fail' ] )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );
		$t_response = $this->builder()->delete( $this->base_url . $t_category['id'] )->anonymous()->send();
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that category creation requires a name.
	 *
	 * @return void
	 */
	public function testProjectCategoryRequiresName() {
		$t_response = $this->builder()->post( $this->base_url, [] )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * Test that category lookup does not cross the project boundary.
	 *
	 * @return void
	 */
	public function testProjectCategoryCannotCrossProjectBoundary() {
		$t_response = $this->builder()->get( $this->base_url . '1000000' )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );
	}
}
