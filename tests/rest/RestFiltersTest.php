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
 * @copyright Copyright 2024 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://mantisbt.org
 */

namespace Mantis\tests\rest;

/**
 * Tests for Filters and Issues matching Filter REST API endpoints.
 *
 * @group REST
 */
class RestFiltersTest extends RestBase
{
	const INVALID_FILTER_ID = -9999;

	/** @var array List of filters to delete to delete in tearDown() */
	protected $filterIdsToDelete = array();

	/**
	 * Create test issues for the test
	 * @return void
	 */

	protected function tearDown(): void {
		parent::tearDown();

		# Delete test filters
		foreach( $this->filterIdsToDelete as $t_filter_id ) {
			$this->builder()
				 ->delete( $this->getFilterURL( $t_filter_id ) )
				 ->send();
		}
	}


	/**
	 * Generate the API base URL get Issues matching filter.
	 *
	 * @param int|null $p_filter_id
	 *
	 * @return string API endpoint
	 */
	private function getFilterURL( ?int $p_filter_id = null ) {
		return "/filters/$p_filter_id";
	}

	/**
	 * Generate the API base URL get Issues matching filter.
	 *
	 * @param int|string $p_filter_id
	 *
	 * @return string API endpoint
	 */
	private function getIssueFilterURL( $p_filter_id ) {
		return "/issues?filter_id=$p_filter_id";
	}

	/**
	 * Retrieves available Filters.
	 *
	 * @param int|null $p_filter_id Optional Filter id, if not given returns
	 *                              all filters
	 *
	 * @return array
	 */
	private function getFilters( ?int $p_filter_id = null) {
		$t_endpoint = $this->getFilterURL( $p_filter_id );
		$t_response = $this->builder()->get( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"GET $t_endpoint successful"
		);

		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertArrayHasKey( 'filters', $t_body, "Response includes filters data" );
		$this->assertIsArray( $t_body['filters'], "Filters data should be an array" );

		return $t_body['filters'];
	}

	/**
	 * Create a test filter.
	 *
	 * The Filter will be automatically deleted at end of Test execution.
	 *
	 * There are currently no available REST API endpoints to create filters, so
	 * the MantisBT core Filter API is used.
	 *
	 * @param array $p_filter A valid Filter {@see filter_ensure_valid_filter()}
	 * @param bool  $p_public True to create a public filter
	 *
	 * @return int Id of created Filter
	 */
	private function createTestFilter( $p_filter, $p_public = false ) {
		$t_filter_id = filter_db_create_filter(
			filter_serialize( $p_filter ),
			$this->userId,
			$this->getProjectId(),
			$this->toString() . ' ' . rand(1, 10000),
			$p_public
		);
		$this->filterIdsToDelete[] = $t_filter_id;

		return $t_filter_id;
	}

	/**
	 * Test case for GET /filters endpoint.
	 *
	 * @return void
	 */
	public function testGetAllFilters() {
		# Get number of filters
		$t_count_filters = count( $this->getFilters() );

		# Create 2 test filters
		$this->createTestFilter( filter_create_any() );
		$this->createTestFilter( filter_create_reported_by( $this->getProjectId(), $this->userId ), true );

		$t_filters = $this->getFilters();
		$this->assertEquals( 2, count( $t_filters ) - $t_count_filters,
			"Number of filters expected to be 2"
		);
	}

	/**
	 * Test case for GET /filters/:id endpoint.
	 *
	 * @return void
	 */
	public function testGetSpecificFilter() {
		$t_orig_filter = filter_create_reported_by( $this->getProjectId(), $this->userId );
		$t_filter_id = $this->createTestFilter( $t_orig_filter );

		$this->getFilters( $t_filter_id );
	}

	/**
	 * Test case for GET /filters/:id endpoint with invalid Filter Id.
	 *
	 * @return void
	 */
	public function testGetInvalidFilter() {
		$t_endpoint = $this->getFilterURL( self::INVALID_FILTER_ID );
		$t_response = $this->builder()->get( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Retrieve invalid filter"
		);
	}

	/**
	 * Test case for DELETE /filters/:id endpoint.
	 *
	 * @return void
	 */
	public function testDeleteFilter() {
		# Create a test filter
		$t_filter_id = $this->createTestFilter( filter_create_any() );

		# Delete the filter
		$t_endpoint = $this->getFilterURL( $t_filter_id );
		$t_response = $this->builder()->delete( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_NO_CONTENT, $t_response->getStatusCode(),
			"Deleting filter"
		);

		# Try to delete the filter again, should fail
		$t_response = $this->builder()->delete( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Deleting non-existing filter"
		);
	}

	/**
	 * Test case for GET /issues?filter_id=:id endpoint.
	 *
	 * Creates a Filter listing resolved issues created by this test case, then
	 * executes it twice:
	 * - Once when no matching issues exist to ensure it returns an empty set
	 * - Again with 2 issues created, one matching an one that does not
	 *
	 * @return void
	 */
	public function testGetIssuesMatchingFilter() {
		# Create test filter - resolved issues created by this test case
		$t_filter = filter_get_default();
		$t_filter[FILTER_PROPERTY_SEARCH] = $this->toString();
		$t_filter[FILTER_PROPERTY_STATUS] = RESOLVED;
		$t_filter_id = $this->createTestFilter( $t_filter );

		# Get issues matching test filter - at this point there should be none
		$t_response = $this->builder()->get( $this->getIssueFilterURL( $t_filter_id ) )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Retrieve issues matching test filter"
		);
		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertArrayHasKey('issues', $t_body, "Response includes issues key" );
		$this->assertIsArray( $t_body['issues'], "Issues key is an array" );
		$this->assertEmpty( $t_body['issues'], "Test filter should return no issues" );

		# Create 2 test issues, 1 new and 1 resolved
		$t_issue_to_add = $this->getIssueToAdd();
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody() );
		$this->deleteIssueAfterRun( $t_body->issue->id );

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['status']['id'] = RESOLVED;
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody() );
		$this->deleteIssueAfterRun( $t_body->issue->id );

		# Get issues matching test filter again
		$t_response = $this->builder()->get( $this->getIssueFilterURL( $t_filter_id ) )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Retrieve issues matching test filter"
		);
		$t_body = json_decode( $t_response->getBody(), true );
		$this->assertCount( 1, $t_body['issues'], "Counting issues matching test filter" );
	}

	/**
	 * Test case for GET /issues?filter_id=:id endpoint with invalid Filter Id.
	 *
	 * @return void
	 */
	public function testGetIssuesMatchingInvalidFilter() {
		$t_endpoint = $this->getIssueFilterURL( self::INVALID_FILTER_ID );
		$t_response = $this->builder()->get( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Retrieve issues matching invalid filter"
		);

	}


}
