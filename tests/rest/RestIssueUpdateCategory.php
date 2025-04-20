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
 * MantisBT Tests
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright  Copyright 2025 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://mantisbt.org
 */

namespace Mantis\tests\rest;

use Psr\Http\Message\ResponseInterface;

class RestIssueUpdateCategory extends RestBase
{
	const CFG_ALLOW_NO_CAT = 'allow_no_category';

	/** @var int Issue id */
	private int $issue_id;
	/**
	 * @var false|mixed
	 */
	private $save_config = null;

	public function setUp(): void {
		parent::setUp();

		# Create a test issue
		$t_issue_to_add = $this->getIssueToAdd();
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->issue_id = $this->getJson( $t_response, HTTP_STATUS_CREATED )->issue->id;
		$this->deleteIssueAfterRun($this->issue_id	);
	}

	public function tearDown(): void {
		parent::tearDown();

		# Restore config if it has been changed
		if( $this->save_config !== null) {
			$this->restoreConfig( self::CFG_ALLOW_NO_CAT, $this->save_config );
		}
	}

	private function getUniqueCategoryName(): string {
		do {
			$t_name = 'cat-' . rand();
		} while( !category_is_unique( $this->getProjectId(), $t_name ) );
		return $t_name;
	}

	private function updateCategory( int $p_issue_id, $p_category ): ResponseInterface {
		$t_payload = ['category' => $p_category];
		return $this->builder()->patch( "/issues/$p_issue_id", $t_payload )->send();
	}


	/**
	 * Test setting the category from various hardcoded cases.
	 * @dataProvider providerSetCategory
	 *
	 * @param mixed    $p_category    Category payload.
	 * @param int      $p_status_code Expected status code.
	 * @param int|null $p_expected_id Expected category id after update (null == unchanged).
	 */
	public function testUpdateIssueCategory1( $p_category, int $p_status_code, int $p_expected_id = null) {
		$t_response = $this->updateCategory( $this->issue_id, $p_category );
		$t_json = $this->getJson( $t_response, $p_status_code );
		if( $t_response->getStatusCode() == HTTP_STATUS_SUCCESS ) {
			$this->assertEquals( $p_expected_id, $t_json->issues[0]->category->id, "Updated category id does not match expected" );
		}
	}

	/**
	 * Data provider for {@see testUpdateIssueCategory1()}.
	 *
	 * @return array[] <Payload, status code, category id>
	 */
	public function providerSetCategory() {
		return [
			# Scalar - API expects a category name (string)
			[0, HTTP_STATUS_NOT_FOUND],
			[false, HTTP_STATUS_NOT_FOUND],
			[1, HTTP_STATUS_NOT_FOUND],
			[9999, HTTP_STATUS_NOT_FOUND],
			['General', HTTP_STATUS_SUCCESS, 1],
			['Non-existing category', HTTP_STATUS_NOT_FOUND],

			# By ID
			[['id' => 'General'], HTTP_STATUS_BAD_REQUEST],
			[['id' => false], HTTP_STATUS_BAD_REQUEST],
			[['id' => 1], HTTP_STATUS_SUCCESS, 1],
			[['id' => 9999], HTTP_STATUS_NOT_FOUND],

			# By Name
			[['name' => ''], HTTP_STATUS_NOT_FOUND],
			[['name' => 'General' ], HTTP_STATUS_SUCCESS, 1],
			[['name' => 'Non-existing category'], HTTP_STATUS_NOT_FOUND],
		];
	}

	/**
	 * Test changing to a new category.
	 *
	 * Since we need to dynamically create the category and this can't be done
	 * using the providerSetCategory() data provider, we test it separately.
	 */
	public function testUpdateIssueCategory2() {
		# Create a category for the tests
		# Use Core API as we don't yet have a REST API endpoint for that (#32470)
		$t_category_id = category_add($this->getProjectId(), $this->getUniqueCategoryName() );
		$t_category_name = category_get_name( $t_category_id );

		# If payload is scalar then it is expected to be a category name, so passing the id should fail
		$t_response = $this->updateCategory( $this->issue_id, $t_category_id );
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode() );

		$t_cases = [
			$t_category_name,
			['id' => $t_category_id],
			['name' => $t_category_name],
		];
		foreach( $t_cases as $t_category ) {
			$t_response = $this->updateCategory( $this->issue_id, $t_category );
			$t_json = $this->getJson( $t_response );
			$this->assertEquals( $t_category_id, $t_json->issues[0]->category->id, "Updated category matches" );
		}

		category_remove( $t_category_id );
	}

	/**
	 * Test clearing the category.
	 *
	 * Tests with both {@see $g_allow_no_category} ON and OFF.
	 *
	 * @dataProvider providerClearCategory
	 */
	public function testUpdateClearCategory( $p_category ) {
		# Test with mandatory category
		$this->save_config = $this->setConfig( self::CFG_ALLOW_NO_CAT, OFF );

		# Status code 
		if( $p_category === null
			|| is_array( $p_category ) && (
				array_key_exists( 'id', $p_category ) && $p_category['id'] === null
				|| array_key_exists( 'name', $p_category ) && $p_category['name'] === null
			)
		) {
			$t_expected = HTTP_STATUS_BAD_REQUEST;
		} else {
			$t_expected = HTTP_STATUS_NOT_FOUND;
		}
		$t_response = $this->updateCategory( $this->issue_id, $p_category );
		$this->assertEquals( $t_expected,
			$t_response->getStatusCode(),
			"REST API returned unexpected Status Code"
		);

		# Allowing empty category
		config_set( self::CFG_ALLOW_NO_CAT, ON );
		$t_response = $this->updateCategory( $this->issue_id, $p_category );
		$t_json = $this->getJson( $t_response );
		$this->assertObjectNotHasProperty( 'category', $t_json->issues[0] );
	}

	/**
	 * Data provider for {@see testUpdateClearCategory()}.
	 *
	 * All values allowing to unset the Category, if the server's configuration
	 * allows it ({@see $g_allow_no_category}).
	 *
	 * @return array[] <Payload>
	 */
	public function providerClearCategory() {
		return [
			[null],
			[['id' => null]],
			[['id' => 0]],
			[['name' => null]],
		];
	}

}
