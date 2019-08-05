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
 * Test fixture for issue creation webservice methods.
 *
 * @requires extension curl
 * @group REST
 */
class RestIssueAddTest extends RestBase {

	/**
	 * @var array $versions
	 */
	protected $versions;

	public function testCreateIssueWithMinimalFields() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithMinimalFields' );
		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
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

		$this->assertEquals( false, $t_issue['sticky'], 'sticky' );
		$this->assertTrue( isset( $t_issue['created_at'] ), 'created at' );
		$this->assertTrue( isset( $t_issue['updated_at'] ), 'updated at' );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithEnumIds() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithEnumIds' );
		$t_issue_to_add['status']['id'] = 50; # assigned
		$t_issue_to_add['resolution']['id'] = 30; # reopened
		$t_issue_to_add['view_state']['id'] = 50; # private
		$t_issue_to_add['priority']['id'] = 40; # high
		$t_issue_to_add['severity']['id'] = 10; # feature
		$t_issue_to_add['reproducibility']['id'] = 10; # always
		$t_issue_to_add['sticky'] = true;

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
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

		$this->assertEquals( true, $t_issue['sticky'], 'sticky' );
		$this->assertTrue( isset( $t_issue['created_at'] ), 'created at' );
		$this->assertTrue( isset( $t_issue['updated_at'] ), 'updated at' );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionString() {
		$t_version_name = $this->versions[2]['version'];
		$t_target_version_name = $this->versions[1]['version'];
		$t_fixed_in_version_name = $this->versions[0]['version'];

		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionString' );
		$t_issue_to_add['version'] = $t_version_name;
		$t_issue_to_add['target_version'] = $t_target_version_name;
		$t_issue_to_add['fixed_in_version'] = $t_fixed_in_version_name;

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['version'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['id'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['name'] ), 'version name set' );
		$this->assertEquals( $t_version_name, $t_issue['version']['name'] );
		$this->assertEquals( $t_target_version_name, $t_issue['target_version']['name'] );
		$this->assertEquals( $t_fixed_in_version_name, $t_issue['fixed_in_version']['name'] );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectName() {
		$t_version_name = $this->versions[0]['version'];

		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionObjectName' );
		$t_issue_to_add['version'] = array( 'name' => $t_version_name );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['version'] ), 'version id set' );
		$this->assertTrue( isset( $t_issue['version']['id'] ), 'version id set' );
		$this->assertTrue( is_numeric( $t_issue['version']['id'] ), 'version id numeric' );
		$this->assertTrue( isset( $t_issue['version']['name'] ), 'version name set' );
		$this->assertEquals( $t_version_name, $t_issue['version']['name'] );
		$this->assertFalse( isset( $t_issue['target_version'] ), 'target_version set' );
		$this->assertFalse( isset( $t_issue['fixed_in_version'] ), 'fixed_in_version set' );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectId() {
		$t_version_name = $this->versions[0]['version'];
		$t_version_id = $this->versions[0]['id'];

		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionObjectId' );
		$t_issue_to_add['version'] = array( 'id' => $t_version_id );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
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

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectIdAndMistatchingName() {
		$t_version_id = $this->versions[0]['id'];
		$t_wrong_version_name = $this->versions[1]['version'];
		$t_correct_version_name = $this->versions[0]['version'];

		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionObjectIdAndMistatchingName' );
		$t_issue_to_add['version'] = array( 'id' => $t_version_id, 'name' => $t_wrong_version_name );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
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

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithVersionObjectIdNotFound() {
		# Test case assumes webservice_error_when_version_not_found = ON.
		$t_version_id = 10000;
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionObjectIdNotFound' );
		$t_issue_to_add['version'] = array( 'id' => $t_version_id );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function testCreateIssueWithVersionObjectNameNotFound() {
		# Test case assumes webservice_error_when_version_not_found = ON.
		$t_version_name = 'VersionNotFound';
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionObjectNameNotFound' );
		$t_issue_to_add['version'] = array( 'name' => $t_version_name );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function testCreateIssueWithVersionStringNotFound() {
		# Test case assumes webservice_error_when_version_not_found = ON.
		$t_version_name = 'VersionNotFound';
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithVersionObjectNameNotFound' );
		$t_issue_to_add['version'] = $t_version_name;

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function testCreateIssueWithTags() {
		# TODO: Create/cleanup tags once supported by the API, till then use dump from official bug tracker
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithTags' );
		$t_issue_to_add['tags'] = array( array( 'name' => 'modern-ui' ), array( 'name' => 'patch' ) );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 201, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertTrue( isset( $t_issue['tags'] ), 'tags set' );
		$this->assertEquals( 'modern-ui', $t_issue['tags'][0]['name'] );
		$this->assertEquals( 'patch', $t_issue['tags'][1]['name'] );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithTagNameNotFound() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithTagsNotFound' );
		$t_issue_to_add['tags'] = array( array( 'name' => 'tag-not-found' ) );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		# TODO: 404 is returned here after issue is created.  We can improve this later.
		$this->assertEquals( 404, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertFalse( isset( $t_issue['tags'] ), 'tags set' );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueWithTagIdNotFound() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithTagsNotFound' );
		$t_issue_to_add['tags'] = array( array( 'id' => 100000 ) );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		# TODO: 404 is returned here after issue is created.  We can improve this later.
		$this->assertEquals( 404, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$t_issue = $t_body['issue'];

		$this->assertFalse( isset( $t_issue['tags'] ), 'tags set' );

		$this->deleteAfterRun( $t_issue['id'] );
	}

	public function testCreateIssueNoSummary() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoSummary' );
		unset( $t_issue_to_add['summary'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function testCreateIssueNoDescription() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoDescription' );
		unset( $t_issue_to_add['description'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function testCreateIssueNoCategory() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoCategory' );
		unset( $t_issue_to_add['category'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function testCreateIssueNoProject() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoProject' );
		unset( $t_issue_to_add['project'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	public function setUp() {
		parent::setUp();

		# Retrieve the 3 most recent versions
		$this->dbConnect();
		$t_versions = version_get_all_rows( $this->projectId, null, true );
		if( count( $t_versions ) < 3 ) {
			throw new Exception( "There must be at least 3 active versions defined in project $this->projectId" );
		}
		for( $i = 0; $i < 3; $i++) {
			$this->versions[] = array_shift( $t_versions );
		}
	}
}
