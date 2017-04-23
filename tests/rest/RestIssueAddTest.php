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
	public function testCreateIssueWithMinimalFields() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueWithMinimalFields' );
		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertTrue( is_array( $t_response ) );
		$this->assertTrue( isset( $t_response['issue'] ) );

		$t_issue = $t_response['issue'];

		$this->assertTrue( isset( $t_issue['id'] ) );
		$this->assertTrue( is_numeric($t_issue['id'] ) );
		$this->assertEquals( $t_issue_to_add['summary'], $t_issue['summary'] );
		$this->assertEquals( $t_issue_to_add['description'], $t_issue['description'] );
		$this->assertEquals( $t_issue_to_add['category']['name'], $t_issue['category']['name'] );
		$this->assertEquals( $t_issue_to_add['project']['id'], $t_issue['project']['id'] );
		$this->assertEquals( $this->userName, $t_issue['reporter']['name'] );

		# Verify Status
		$this->assertEquals( 10, $t_issue['status']['id'] );
		$this->assertEquals( '#fcbdbd', $t_issue['status']['color'] );
		$this->assertEquals( 'new', $t_issue['status']['name'] );
		$this->assertEquals( 'new', $t_issue['status']['label'] );

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

		$this->deleteAfterRun( $t_response['issue']['id'] );
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

		$this->assertTrue( is_array( $t_response ) );
		$this->assertTrue( isset( $t_response['issue'] ) );

		$t_issue = $t_response['issue'];

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

		$this->deleteAfterRun( $t_response['issue']['id'] );
	}

	public function testCreateIssueNoSummary() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoSummary' );
		unset( $t_issue_to_add['summary'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertFalse( is_array( $t_response ) );
	}

	public function testCreateIssueNoDescription() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoDescription' );
		unset( $t_issue_to_add['description'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertFalse( is_array( $t_response ) );
	}

	public function testCreateIssueNoCategory() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoCategory' );
		unset( $t_issue_to_add['category'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertFalse( is_array( $t_response ) );
	}

	public function testCreateIssueNoProject() {
		$t_issue_to_add = $this->getIssueToAdd( 'RestIssueAddTest.testCreateIssueNoProject' );
		unset( $t_issue_to_add['project'] );

		$t_response = $this->post( '/issues', $t_issue_to_add );

		$this->assertFalse( is_array( $t_response ) );
	}
}