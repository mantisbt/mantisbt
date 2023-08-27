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

use Psr\Http\Message\ResponseInterface;

require_once 'RestBase.php';

/**
 * Test fixture for issue webservice methods.
 *
 * @requires extension curl
 * @group REST
 */
class RestIssueTest extends RestBase {

	/**
	 * @var array $versions
	 */
	protected $versions;

	/**
	 * @var string $tag_name
	 */
	protected $tag_name;

	/**
	 * Mark test Skipped if test project does not have enough versions defined.
	 *
	 * @param $p_num Number of versions
	 * @return void
	 */
	protected function skipTestIfNotEnoughVersions( $p_num ) {
		if( count( $this->versions ) < $p_num ) {
			$this->markTestSkipped(
				"There must be at least $p_num active versions defined in project $this->projectId"
			);
		}
	}


	public function testCreateIssueWithMinimalFields() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];
		# file_put_contents( '/tmp/response.txt', var_export( $t_body, true ) );

		$this->assertTrue( isset( $t_issue['id'] ), 'id set' );
		$this->assertTrue( is_numeric($t_issue['id'] ), 'id is numeric' );
		$this->assertEquals( $t_issue_to_add['summary'], $t_issue['summary'], 'summary' );
		$this->assertEquals( $t_issue_to_add['description'], $t_issue['description'], 'description' );
		$this->assertEquals( $t_issue_to_add['category']['name'], $t_issue['category']['name'], 'category name' );
		$this->assertEquals( $t_issue_to_add['project']['id'], $t_issue['project']['id'], 'project id' );
		$this->assertEquals( $this->userName, $t_issue['reporter']['name'], 'reporter name' );

		# Verify Status
		$this->assertEquals( 10, $t_issue['status']['id'], 'status id' );
		$this->assertEquals( '#fcbdbd', $t_issue['status']['color'], 'status color' );
		$this->assertEquals( 'new', $t_issue['status']['name'], 'status name' );
		$this->assertEquals( 'new', $t_issue['status']['label'], 'status label' );

		# Verify Resolution
		$this->assertEquals( 10, $t_issue['resolution']['id'], 'resolution id' );
		$this->assertEquals( 'open', $t_issue['resolution']['name'], 'resolution name' );
		$this->assertEquals( 'open', $t_issue['resolution']['label'], 'resolution label' );

		# Verify View State
		$this->assertEquals( 10, $t_issue['view_state']['id'], 'view state id' );
		$this->assertEquals( 'public', $t_issue['view_state']['name'], 'view state name' );
		$this->assertEquals( 'public', $t_issue['view_state']['label'], 'view state label' );

		# Verify Priority
		$this->assertEquals( 30, $t_issue['priority']['id'], 'priority id' );
		$this->assertEquals( 'normal', $t_issue['priority']['name'], 'priority name' );
		$this->assertEquals( 'normal', $t_issue['priority']['label'], 'priority label' );

		# Verify Severity
		$this->assertEquals( 50, $t_issue['severity']['id'], 'severity id' );
		$this->assertEquals( 'minor', $t_issue['severity']['name'], 'severity name' );
		$this->assertEquals( 'minor', $t_issue['severity']['label'], 'severity label' );

		# Verify Reproducibility
		$this->assertEquals( 70, $t_issue['reproducibility']['id'], 'reproducibility id' );
		$this->assertEquals( 'have not tried', $t_issue['reproducibility']['name'], 'reproducibility name' );
		$this->assertEquals( 'have not tried', $t_issue['reproducibility']['label'], 'reproducibility label' );

		$this->assertFalse( $t_issue['sticky'], 'sticky' );
		$this->assertTrue( isset( $t_issue['created_at'] ), 'created at' );
		$this->assertTrue( isset( $t_issue['updated_at'] ), 'updated at' );

		$this->deleteIssueAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithEnumIds() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['status']['id'] = 50; # assigned
		$t_issue_to_add['resolution']['id'] = 30; # reopened
		$t_issue_to_add['view_state']['id'] = 50; # private
		$t_issue_to_add['priority']['id'] = 40; # high
		$t_issue_to_add['severity']['id'] = 10; # feature
		$t_issue_to_add['reproducibility']['id'] = 10; # always
		$t_issue_to_add['sticky'] = true;

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['id'] ) );
		$this->assertTrue( is_numeric($t_issue['id'] ) );

		# Verify Status
		$this->assertEquals( 50, $t_issue['status']['id'] );
		$this->assertEquals( '#c2dfff', $t_issue['status']['color'] );
		$this->assertEquals( 'assigned', $t_issue['status']['name'] );
		$this->assertEquals( 'assigned', $t_issue['status']['label'] );

		# Verify Resolution
		$this->assertEquals( 30, $t_issue['resolution']['id'], 'resolution id' );
		$this->assertEquals( 'reopened', $t_issue['resolution']['name'], 'resolution name' );
		$this->assertEquals( 'reopened', $t_issue['resolution']['label'], 'resolution label' );

		# Verify View State
		$this->assertEquals( 50, $t_issue['view_state']['id'], 'view state id' );
		$this->assertEquals( 'private', $t_issue['view_state']['name'], 'view state name' );
		$this->assertEquals( 'private', $t_issue['view_state']['label'], 'view state label' );

		# Verify Priority
		$this->assertEquals( 40, $t_issue['priority']['id'], 'priority id' );
		$this->assertEquals( 'high', $t_issue['priority']['name'], 'priority name' );
		$this->assertEquals( 'high', $t_issue['priority']['label'], 'priority label' );

		# Verify Severity
		$this->assertEquals( 10, $t_issue['severity']['id'], 'severity id' );
		$this->assertEquals( 'feature', $t_issue['severity']['name'], 'severity name' );
		$this->assertEquals( 'feature', $t_issue['severity']['label'], 'severity label' );

		# Verify Reproducibility
		$this->assertEquals( 10, $t_issue['reproducibility']['id'], 'reproducibility id' );
		$this->assertEquals( 'always', $t_issue['reproducibility']['name'], 'reproducibility name' );
		$this->assertEquals( 'always', $t_issue['reproducibility']['label'], 'reproducibility label' );

		$this->assertTrue( $t_issue['sticky'], 'sticky' );
		$this->assertTrue( isset( $t_issue['created_at'] ), 'created at' );
		$this->assertTrue( isset( $t_issue['updated_at'] ), 'updated at' );

		$this->deleteIssueAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionString() {
		$this->skipTestIfNotEnoughVersions( 3 );

		$t_version_name = $this->versions[2]['version'];
		$t_target_version_name = $this->versions[1]['version'];
		$t_fixed_in_version_name = $this->versions[0]['version'];

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = $t_version_name;
		$t_issue_to_add['target_version'] = $t_target_version_name;
		$t_issue_to_add['fixed_in_version'] = $t_fixed_in_version_name;

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['version'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['id'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['name'] ), 'version name set' );
		$this->assertEquals( $t_version_name, $t_issue['version']['name'] );
		$this->assertEquals( $t_target_version_name, $t_issue['target_version']['name'] );
		$this->assertEquals( $t_fixed_in_version_name, $t_issue['fixed_in_version']['name'] );

		$this->deleteIssueAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectName() {
		$this->skipTestIfNotEnoughVersions( 1 );

		$t_version_name = $this->versions[0]['version'];

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = array( 'name' => $t_version_name );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['version'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['id'] ), 'version id set' );
		$this->assertTrue( is_numeric( $t_issue['version']['id'] ), 'version id numeric' );
		$this->assertTrue( isset( $t_issue['version']['name'] ), 'version name set' );
		$this->assertEquals( $t_version_name, $t_issue['version']['name'] );
		$this->assertFalse( isset( $t_issue['target_version'] ), 'target_version set' );
		$this->assertFalse( isset( $t_issue['fixed_in_version'] ), 'fixed_in_version set' );

		$this->deleteIssueAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectId() {
		$this->skipTestIfNotEnoughVersions( 1 );

		$t_version_name = $this->versions[0]['version'];
		$t_version_id = $this->versions[0]['id'];

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = array( 'id' => $t_version_id );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['version'] ), 'version set' );
		$this->assertTrue( isset( $t_issue['version']['id'] ), 'version id set' );
		$this->assertTrue( is_numeric( $t_issue['version']['id'] ), 'version id numeric' );
		$this->assertTrue( isset( $t_issue['version']['name'] ), 'version name set' );
		$this->assertEquals( $t_version_name, $t_issue['version']['name'], 'version name' );
		$this->assertEquals( $t_version_id, $t_issue['version']['id'], 'version id' );
		$this->assertFalse( isset( $t_issue['target_version'] ), 'target_version set' );
		$this->assertFalse( isset( $t_issue['fixed_in_version'] ), 'fixed_in_version set' );

		$this->deleteIssueAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectIdAndMistatchingName() {
		$this->skipTestIfNotEnoughVersions( 2 );

		$t_version_id = $this->versions[0]['id'];
		$t_wrong_version_name = $this->versions[1]['version'];
		$t_correct_version_name = $this->versions[0]['version'];

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = array( 'id' => $t_version_id, 'name' => $t_wrong_version_name );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['version'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['id'] ), 'version id set' );
		$this->assertTrue( is_numeric( $t_issue['version']['id'] ), 'version id numeric' );
		$this->assertTrue( isset( $t_issue['version']['name'] ), 'version name set' );
		$this->assertEquals( $t_correct_version_name, $t_issue['version']['name'], 'version name' );
		$this->assertEquals( $t_version_id, $t_issue['version']['id'], 'version id' );
		$this->assertFalse( isset( $t_issue['target_version'] ), 'target_version set' );
		$this->assertFalse( isset( $t_issue['fixed_in_version'] ), 'fixed_in_version set' );

		$this->deleteIssueAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectIdNotFound() {
		# Test case assumes webservice_error_when_version_not_found = ON.
		$t_version_id = 10000;
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = array( 'id' => $t_version_id );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	public function testCreateIssueWithVersionObjectNameNotFound() {
		# Test case assumes webservice_error_when_version_not_found = ON.
		$t_version_name = 'VersionNotFound';
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = array( 'name' => $t_version_name );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	public function testCreateIssueWithVersionStringNotFound() {
		# Test case assumes webservice_error_when_version_not_found = ON.
		$t_version_name = 'VersionNotFound';
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = $t_version_name;

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	/**
	 * New tag should be created and attached to a new issue
	 */
	public function testCreateIssueWithTagNotExisting() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['tags'] = array( array( 'name' => $this->tag_name ) );

		# Change threshold to disable tag creation
		$t_threshold = config_set( 'tag_create_threshold', NOBODY );
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals(
			HTTP_STATUS_NOT_FOUND,
			$t_response->getStatusCode(),
			'New issue with non-existing tag while not allowed to create tags'
		);

		# Reset threshold and try again
		config_set( 'tag_create_threshold', $t_threshold );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$t_issue_id = $this->assertIssueCreatedWithTag( $t_response );

		$this->deleteIssueAfterRun( $t_issue_id );
	}

	public function testCreateIssueWithTagExisting() {
		$t_issue_to_add = $this->getIssueToAdd();

		# Tag by name
		$t_issue_to_add['tags'] = array( array( 'name' => $this->tag_name ) );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$t_issue_id = $this->assertIssueCreatedWithTag( $t_response );

		$this->deleteIssueAfterRun( $t_issue_id );

		# Tag by id
		# TODO: replace internal call by GET /tag request when implemented (see #32863)
		$t_tag = tag_get_by_name( $this->tag_name );
		$t_issue_to_add['tags'] = array( array( 'id' => $t_tag['id'] ) );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$t_issue_id = $this->assertIssueCreatedWithTag( $t_response );

		$this->deleteIssueAfterRun( $t_issue_id );
	}

	/**
	 * Checks that the issue was created successfully and the tag was properly attached.
	 *
	 * @param ResponseInterface $p_response
	 *
	 * @return integer Created issue Id
	 */
	protected function assertIssueCreatedWithTag( $p_response ) {
		$this->assertEquals( HTTP_STATUS_CREATED, $p_response->getStatusCode() );

		$t_body = json_decode( $p_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['tags'] ), 'tags set' );
		$this->assertEquals( $this->tag_name, $t_issue['tags'][0]['name'] );

		return $t_issue['id'];
	}

	/**
	 * Tests creation of issues with invalid tags.
	 *
	 * @param mixed   $p_tag         Tag element
	 * @param integer $p_status_code Expected status code
	 *
	 * @dataProvider providerTagsInvalid
	 */
	public function testCreateIssueWithTagInvalid( $p_tag, $p_status_code ) {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['tags'] = array( $p_tag );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( $p_status_code, $t_response->getStatusCode() );
	}

	/**
	 * Provide a series of invalid Tag elements.
	 *
	 * Test case structure:
	 *   <case> => array( <tag element>, <error code> )
	 *
	 * Creating an issue with <tag element> should fail to with <error code>.
	 *
	 * @return array List of test cases
	 *
	 */
	public function providerTagsInvalid() {
		return array(
			'EmptyTagElement' => array(
				array(),
				HTTP_STATUS_BAD_REQUEST
			),

			'NotATagElement' => array(
				array( 'what' => 'ever' ),
				HTTP_STATUS_BAD_REQUEST
			),

			'InvalidTagId' => array(
				array( 'id' => -1 ),
				HTTP_STATUS_NOT_FOUND
			),

			'EmptyTagName' => array(
				array( 'name' => '' ),
				HTTP_STATUS_BAD_REQUEST
			),
		);
	}

	public function testTagAttachDetach() {
		# Create test issue
		$t_issue_to_add = $this->getIssueToAdd();
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue_id = $t_body['issue']['id'];
		$this->deleteIssueAfterRun( $t_issue_id );

		$t_url_base = "/issues/$t_issue_id/tags/";

		# TODO: replace internal call by GET /tag request when implemented (see #32863)
		$this->assertFalse( tag_get_by_name( $this->tag_name ), "The Tag already exists" );

		# Attach the tag - it will be created
		$t_data = $this->getTagData();
		$t_response = $this->builder()->post( $t_url_base, $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Failed to attach the tag"
		);

		# TODO: replace internal call by GET /tag request when implemented (see #32863)
		$t_tag_id = tag_get_by_name( $this->tag_name )['id'];
		$this->assertNotFalse( $t_tag_id, "Tag has not been created" );

		$t_body = json_decode( $t_response->getBody() );
		$t_issue_tags = array_column( $t_body->issues[0]->tags ?? [], 'id' );
		$this->assertContains( $t_tag_id, $t_issue_tags,
			"Tag does not exist in created Issue data"
		);

		# Attach the same tag again
		$t_response = $this->builder()->post( $t_url_base, $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Failed to attach the same tag"
		);

		# Detach the tag
		$t_response = $this->builder()->delete( $t_url_base . $t_tag_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Tag was not detached"
		);

		# Try to detach the same tag again
		$t_response = $this->builder()->delete( $t_url_base . $t_tag_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Attempting to detach an unattached tag should have succeeded"
		);

		# Try to detach a non-existing tag
		$t_tag_id = 99999;
		while( tag_exists( $t_tag_id ) ) {
			$t_tag_id++;
		}
		$t_response = $this->builder()->delete( "/issues/$t_issue_id/tags/$t_tag_id" )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Detaching a non-existing tag should have failed"
		);
	}

	public function testTagAttachDetachNonExistingIssue() {
		$t_nonexistent_issue_id = 99999;
		while( bug_exists( $t_nonexistent_issue_id ) ) {
			$t_nonexistent_issue_id++;
		}
		$t_url_base = "/issues/$t_nonexistent_issue_id/tags/";

		$t_response = $this->builder()->post( $t_url_base, $this->getTagData() )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Attaching a tag to a non-existing issue should have failed"
		);

		# TODO: replace internal calls by GET /tag request when implemented (see #32863)
		$t_tag_id = tag_create( $this->tag_name );
		$this->assertTrue( tag_exists( $t_tag_id ) );
		$t_response = $this->builder()->delete( $t_url_base . $t_tag_id )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Detaching an existing tag from a non-existing issue should have failed"
		);
	}

	public function testCreateIssueNoSummary() {
		$t_issue_to_add = $this->getIssueToAdd();
		unset( $t_issue_to_add['summary'] );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	public function testCreateIssueNoDescription() {
		$t_issue_to_add = $this->getIssueToAdd();
		unset( $t_issue_to_add['description'] );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	public function testCreateIssueNoCategory() {
		$t_allow_no_category = config_get( 'allow_no_category' );
		$t_result = $t_allow_no_category ? HTTP_STATUS_CREATED : HTTP_STATUS_BAD_REQUEST;

		$t_issue_to_add = $this->getIssueToAdd();
		unset( $t_issue_to_add['category'] );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( $t_result, $t_response->getStatusCode() );

		if( $t_response->getStatusCode() == HTTP_STATUS_CREATED ) {
			$t_body = json_decode( $t_response->getBody(), true );
			$t_issue = $t_body['issue'];
			$this->deleteIssueAfterRun( $t_issue['id'] );
		}
	}

	public function testCreateIssueNoProject() {
		$t_issue_to_add = $this->getIssueToAdd();
		unset( $t_issue_to_add['project'] );

		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();

		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );
	}

	public function setUp(): void {
		parent::setUp();

		# Retrieve the 3 most recent versions
		$this->dbConnect();
		$t_versions = version_get_all_rows( $this->projectId, null, true );
		$this->versions = array_slice( $t_versions, 0, 3 );

		# Generate a unique tag name
		do {
			$this->tag_name = 'new-tag-' . rand();
		} while( !tag_is_unique( $this->tag_name ) );
	}

	public function tearDown(): void {
		parent::tearDown();

		# Delete tag if it exists
		# TODO: replace internal calls by GET /tag request when implemented (see #32863)
		$t_tag = tag_get_by_name( $this->tag_name );
		if( $t_tag ) {
			# Must be logged in to delete tag
			/** @noinspection PhpUnhandledExceptionInspection */
			auth_attempt_script_login( $this->userName, $this->password );

			tag_delete( $t_tag['id'] );
		}
	}

	/**
	 * Generates Tag Data payload for Tag Attach endpoint.
	 *
	 * @return array[]
	 */
	private function getTagData(): array {
		return [
			'tags' => [
				[ 'name' => $this->tag_name ],
			]
		];
	}
}
