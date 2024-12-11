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
 * @copyright Copyright 2023 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://mantisbt.org
 */

require_once 'RestBase.php';

/**
 * Tests for Issue Relationships REST API endpoints.
 *
 * @group REST
 */
class RestIssueRelationshipsTest extends RestBase
{
	/** @var int Source Issue Id */
	protected $src_id;

	/** @var int Target Issue Id */
	protected $tgt_id;

	/** @var string Base API URL */
	protected $base_url;

	/**
	 * Create 2 test issues for the test
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		# Source issue
		$t_issue_to_add = $this->getIssueToAdd( 'Source' );
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$this->src_id = $t_body['issue']['id'];

		# Target issue
		$t_issue_to_add = $this->getIssueToAdd( 'Target' );
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$this->tgt_id = $t_body['issue']['id'];

		$this->deleteIssueAfterRun( $this->src_id );
		$this->deleteIssueAfterRun( $this->tgt_id );
	}

	/**
	 * Generate the API base URL to add/remove relationships.
	 *
	 * @param int $p_issue_id Issue Id, defaults to source issue id
	 * @param int $p_rel_id   Optional Relationship Id
	 *
	 * @return string API endpoint
	 */
	private function getBaseURL( $p_issue_id = null, $p_rel_id = null ) {
		$p_issue_id = $p_issue_id ?? $this->src_id;
		$p_rel_id = $p_rel_id ?? '';

		return "/issues/$p_issue_id/relationships/$p_rel_id";
	}

	/**
	 * Generate Issue Relationship data.
	 *
	 * @param string $p_rel_type
	 *
	 * @return array
	 */
	private function createRelationshipData( $p_rel_type = 'related-to' ) {
		$t_type_key = is_numeric( $p_rel_type ) ? 'id' : 'name';
		return [
			'issue' => [
				'id' => $this->tgt_id,
			],
			'type' => [
				$t_type_key => $p_rel_type,
			]
		];
	}

	/**
	 * Retrieves an issue's relationship data.
	 *
	 * @param int $p_issue_id
	 * @return array
	 */
	private function getIssueRelationships( $p_issue_id ) {
		$t_response = $this->builder()->get( '/issues/' . $p_issue_id )->send();
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issues'][0];

		return $t_issue['relationships'] ?? [];
	}

	public function testIssueRelationships() {
		$this->assertEmpty($this->getIssueRelationships( $this->tgt_id ),
			"No prior relationships exist"
		);

		# Regular relationship
		$t_data = $this->createRelationshipData();
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Create regular relationship"
		);
		$t_target_rel = $this->getIssueRelationships( $this->tgt_id );
		$this->assertEquals( 'related-to', $t_target_rel[0]['type']['name'],
			"Relationship type in target issue matches"
		);
		$this->assertEquals( $this->src_id, $t_target_rel[0]['issue']['id'],
			"Related Issue in target matches"
		);

		# Parent-child relationship
		$t_data = $this->createRelationshipData( BUG_DEPENDANT );
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Update to a parent-child relationship, by id"
		);
		$t_source_rel = $this->getIssueRelationships( $this->src_id );
		$this->assertEquals( 'parent-of', $t_source_rel[0]['type']['name'],
			"Relationship type name in source issue matches id"
		);
		$t_target_rel = $this->getIssueRelationships( $this->tgt_id );
		$this->assertEquals( 'child-of', $t_target_rel[0]['type']['name'],
			"Relationship type in target issue matches complement"
		);
	}

	public function testCreateInvalidRelationships() {
		$t_data = $this->createRelationshipData();
		$t_response = $this->builder()->post( $this->getBaseURL( 9999999 ), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Create relationship for non-existing issue"
		);

		$t_data['issue']['id'] = $this->src_id;
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Issue cannot be related to itself"
		);

		$t_data = $this->createRelationshipData( 'xxxxx' );
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Create invalid relationship type by name"
		);

		$t_data = $this->createRelationshipData( 99 );
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Create invalid relationship type by id"
		);
	}

	public function testDeleteRelationships() {
		# Create a relationship for deletion
		$t_data = $this->createRelationshipData();
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Create relationship for deletion"
		);
		$t_source_rel = $this->getIssueRelationships( $this->src_id );
		$t_rel_id = $t_source_rel[0]['id'];

		# Create an unrelated issue
		$t_issue_to_add = $this->getIssueToAdd( 'Unrelated' );
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_unrelated_issue_id = $t_body['issue']['id'];
		$this->deleteIssueAfterRun( $t_unrelated_issue_id );

		# Attempt to delete existing relationship from unrelated issue
		$t_response = $this->builder()
			->delete( $this->getBaseURL( $t_unrelated_issue_id, $t_rel_id ) )
			->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Delete an existing relationship from unrelated, existing issue"
		);

		# Delete relationship
		$t_endpoint = $this->getBaseURL( null, $t_rel_id );
		$t_response = $this->builder()->delete( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Delete existing relationship"
		);

		# Delete non-existing relationship
		$t_response = $this->builder()->delete( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Delete non-existing relationship"
		);
	}

	/**
	 * It should not be possible to resolve a parent issue having unresolved
	 * children.
	 *
	 * @return void
	 */
	public function testResolveIssueWithChildRelationships()
	{
		# Create a Parent-child relationship
		$t_data = $this->createRelationshipData( BUG_DEPENDANT );
		$t_response = $this->builder()->post( $this->getBaseURL(), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Create parent-child relationship"
		);

		# Attempt to resolve the parent issue should fail
		$t_data = ['status' => ['id' => RESOLVED]];
		$t_response = $this->builder()->patch( '/issues/' . $this->src_id, $t_data)->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Resolve parent issue with unresolved children"
		);

		# Resolve the child issue
		$t_response = $this->builder()->patch( '/issues/' . $this->tgt_id, $t_data)->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Resolving child issue"
		);

		# Resolving the parent should work now
		$t_response = $this->builder()->patch( '/issues/' . $this->src_id, $t_data)->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Resolve parent issue with resolved children"
		);
	}

}
