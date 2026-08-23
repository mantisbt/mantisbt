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
		$this->assertEquals( CATEGORY_STATUS_ENABLED, $t_category['status'] );

		$t_id = $t_category['id'];
		$t_response = $this->builder()->get( $this->base_url . $t_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_category = json_decode( $t_response->getBody(), true )['categories'][0];
		$this->assertEquals( $t_id, $t_category['id'] );
		$this->assertEquals( $this->getProjectId(), $t_category['project']['id'] );
		$this->assertEquals( CATEGORY_STATUS_ENABLED, $t_category['status'] );

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
	 * Test category assigned user and status fields through add and update.
	 *
	 * @return void
	 */
	public function testProjectCategoryAssignedAndStatusFields() {
		$t_category = $this->createCategory( [
			'name' => 'REST assigned category ' . rand( 1, 1000000 ),
			'assigned_to' => $this->userId,
			'status' => CATEGORY_STATUS_DISABLED,
		] );
		$this->assertEquals( CATEGORY_STATUS_DISABLED, $t_category['status'] );
		$this->assertEquals( $this->userId, $t_category['default_handler']['id'] );

		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [ 'enabled' => true ] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_category = json_decode( $t_response->getBody(), true )['category'];
		$this->assertEquals( CATEGORY_STATUS_ENABLED, $t_category['status'] );
		$this->assertEquals( $this->getProjectId(), $t_category['project']['id'] );
		$this->assertEquals( $this->userId, $t_category['default_handler']['id'] );

		$this->deleteCategory( $t_category['id'] );
	}

	/**
	 * Test that assigned users must exist when adding or updating categories.
	 *
	 * @return void
	 */
	public function testProjectCategoryAssignedToMustExist() {
		$t_response = $this->builder()->post( $this->base_url, [
			'name' => 'REST invalid handler ' . rand( 1, 1000000 ),
			'assigned_to' => 1000000,
		] )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );

		$t_category = $this->createCategory( [ 'name' => 'REST update handler ' . rand( 1, 1000000 ) ] );
		$t_response = $this->builder()->patch( $this->base_url . $t_category['id'], [ 'assigned_to' => 1000000 ] )->send();
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
		$t_category_id = config_get( 'default_category_for_moves' );
		if( $t_category_id <= 0 || !category_exists( $t_category_id ) || (int)category_get_field( $t_category_id, 'project_id' ) !== $this->getProjectId() ) {
			$this->markTestSkipped( 'The configured default category does not exist in the test project.' );
		}
		$this->assertEquals( $this->getProjectId(), category_get_field( $t_category_id, 'project_id' ) );

		$t_response = $this->builder()->delete( $this->base_url . $t_category_id )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
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
