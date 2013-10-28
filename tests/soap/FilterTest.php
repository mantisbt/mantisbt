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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for filter related webservice method.
 */
class FilterTest extends SoapBase {
	
	const ISSUES_TO_RETRIEVE = 50;

	/**
	 * Test the "assigned" filter type when issue is not assigned and no target user provided.
	 */
	public function testGetIssuesForUserForUnassignedNoTargetUser() {
		$targetUser = array();
		$initialIssuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserForUnassignedNoTargetUser' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$this->assertEquals( 1, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
		$this->assertEquals( $issueId, $issuesCount[0]->id, "issueId");
	}

	/**
	 * Test the "assigned" filter type for unassigned issues with target user specified.
	 */
	public function testGetIssuesForUserForUnassignedWithTargetUser() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserForUnassignedWithTargetUser' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$this->assertEquals( 0, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
	}

	/**
	 * Test the "assigned" filter type for assigned issues with no target user.
	 */
	public function testGetIssuesForUserForAssignedWithNoTargetUser() {
		$targetUser = array();
		$initialIssuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserForAssignedWithNoTargetUser' );

		// Assign the issue to the reporter.
		$issueToAdd['handler'] = array( 'name' => $this->userName );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$this->assertEquals( 0, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
	}

	/**
	 * Test the "assigned" filter type for assigned issues with target user specified.
	 */
	public function testGetIssuesForUserForAssignedWithTargetUser() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserForAssignedWithTargetUser' );

		// Assign the issue to the reporter.
		$issueToAdd['handler'] = $targetUser;

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$this->assertEquals( 1, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
		$this->assertEquals( $issueId, $issuesCount[0]->id, "issueId");
	}

	/**
	 * Test the "assigned" filter type for assigned issues with target user specified.
	 * Make sure resolved issues are not returned.
	 */
	public function testGetIssuesForUserForAssignedWithTargetUserNoResolved() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserForAssignedWithTargetUserNoResolved' );

		// Assign the issue to the reporter.
		$issueToAdd['handler'] = $targetUser;

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);		

		$issue->status = array( 'name' => 'resolved' );

        $this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue );

		$issuesCount = $this->getIssuesForUser( 'assigned', $targetUser );

		$this->assertEquals( 0, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
	}

	/**
	 * Test the "reported" filter type with no target user.
	 * @expectedException SoapFault
	 */
	public function testGetIssuesForUserReportedNoTargetUser() {
		$targetUser = array();
		$initialIssuesCount = $this->getIssuesForUser( 'reported', $targetUser );
	}

	/**
	 * Test the "reported" filter type with target user.
	 */
	public function testGetIssuesForUserReportedWithTargetUser() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'reported', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserReportedWithTargetUser' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'reported', $targetUser );

		$this->assertEquals( 1, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
		$this->assertEquals( $issueId, $issuesCount[0]->id, "issueId");
	}

	/**
	 * Test the "monitored" filter type with no target user.
	 */
	public function testGetIssuesForUserMonitoredNoTargetUser() {
		$targetUser = array();
		$initialIssuesCount = $this->getIssuesForUser( 'monitored', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserMonitoredNoTargetUser' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'monitored', $targetUser );

		$this->assertEquals( 0, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
	}

	/**
	 * Test the "monitored" filter type with target user.
	 */
	public function testGetIssuesForUserMonitoredWithTargetUser() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'monitored', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserMonitoredWithTargetUser' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issuesCount = $this->getIssuesForUser( 'monitored', $targetUser );

		$this->assertEquals( 0, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
	}

	/**
	 * Test the "monitored" filter type with target user and a monitored issue.
	 */
	public function testGetIssuesForUserForMonitoredWithTargetUserAndMatch() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'monitored', $targetUser );

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetIssuesForUserForMonitoredWithTargetUserAndMatch' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);		

		// Monitor the issue so it matches the file.		
        $issue->monitors = array( array( 'id' => $this->userId ) );
        $this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue );

		$issuesCount = $this->getIssuesForUser( 'monitored', $targetUser );

		$this->assertEquals( 1, count( $issuesCount ) - count( $initialIssuesCount ), "count(issuesCount) - count(initialIssuesCount)");
		$this->assertEquals( $issueId, $issuesCount[0]->id, "issueId");
	}

	/**
	 * Test the "monitored" filter type with target user.
	 * @expectedException SoapFault
	 */
	public function testGetIssuesForUserInvalidFilter() {
		$targetUser = array( 'name' => $this->userName );
		$initialIssuesCount = $this->getIssuesForUser( 'unknown', $targetUser );
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issues
	 * 2. Creating an issue
	 * 3. Retrieving all the project's issues
	 * 4. Verifying that one extra issue is found in the results
	 * 5. Verifying that the first returned issue is the one we have submitted
	 */
	public function testGetProjectIssues() {

		$initialIssues = $this->getProjectIssues();

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.getProjectIssues' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$projectIssues = $this->getProjectIssues();

		$this->assertEquals( 1, count( $projectIssues ) - count( $initialIssues ), "count(projectIssues) - count(initialIssues)");
		$this->assertEquals( $issueId, $projectIssues[0]->id, "issueId");
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issue headers
	 * 2. Creating an issue
	 * 3. Retrieving all the project's issue headers
	 * 4. Verifying that one extra issue is found in the results
	 * 5. Verifying that the first returned issue is the one we have submitted
	 */
	public function testGetProjectIssueHeaders() {

		$initialIssues = $this->getProjectIssueHeaders();

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.getProjectIssues' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$projectIssues = $this->getProjectIssueHeaders();

		$this->assertEquals( 1, count( $projectIssues ) - count( $initialIssues ), "count(projectIssues) - count(initialIssues)" );
		$this->assertEquals( $issueId, $projectIssues[0]->id, "issueId" );
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issue headers
	 * 2. Creating an issue
	 * 3. Retrieving the issue
	 * 4. Creating 3 notes for that issue
	 * 5. Retrieving all the project's issue headers
	 * 7. Verifying that the first returned issue has 3 notes
	 */
	public function testGetProjectIssueHeadersCountNotes() {

		$initialIssues = $this->getProjectIssueHeaders();

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.getProjectIssues' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$note = array(
			'text' => 'Note text.'
		);

		$noteCount = 3;

		for ( $i = 0 ; $i < $noteCount ; $i++) {
			$this->client->mc_issue_note_add(
				$this->userName,
				$this->password,
				$issueId,
				$note);
		}

		$projectIssues = $this->getProjectIssueHeaders();

		$this->assertEquals( 3, $projectIssues[0]->notes_count, "notes_count" );
	}


	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issues
	 * 2. Creating an issue with status = closed and resolution = fixed
	 * 3. Retrieving all the project's issues
	 * 4. Verifying that one extra issue is found in the results
	 */
	public function testGetProjectClosedIssues() {

		$initialIssues = $this->getProjectIssues();

		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetProjectClosedIssues' );
		$issueToAdd['status'] = 'closed';
		$issueToAdd['resolution'] = 'fixed';

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$projectIssues = $this->getProjectIssues();

		$this->assertEquals( 1, count( $projectIssues ) - count( $initialIssues ), "count(projectIssues) - count(initialIssues)");
	}

	/**
	 * A test case that tests the following:
	 *
	 * 1. Creating an issue with a category
	 * 2. Retrieving all the project's issues
	 * 3. Verifying that the created issue is present in the retrieved issues
	 *
	 * Test created to verify issue #11609
	 */
	public function testGetProjectIssuesWithoutCategory() {

		$this->skipIfAllowNoCategoryIsDisabled();

		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateBugWithNoCategory' );
		unset ( $issueToAdd['category'] );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$projectIssues = $this->getProjectIssues();

		$this->assertEquals( $issueId, $projectIssues[0]->id, "id" );
	}
	
	/**
	 * Verifies that after the last page no more issues are being returned
	 */
	public function testGetIssueHeadersPaged() {
		
		$this->doTestGetPages('mc_project_get_issue_headers');
	}
	
	private function doTestGetPages( $methodName ) {

		$currentIssues = count($this->getProjectIssues());
		if ( $currentIssues >= 3) {
			$issueCount = $currentIssues;
		} else {
			// need to add
				
			$issueCount = 3;
				
			$toAdd = $issueCount - $currentIssues;
				
			while ( $toAdd > 0 ) {
		
				$issue = $this->getIssueToAdd('FilterTest.doTestGatePages.' .$methodName);
				$issueId = $this->client->mc_issue_add($this->userName, $this->password, $issue);
				$this->deleteAfterRun($issueId);
		
				$toAdd--;
			}
		}
		
		$pageSize = $issueCount - 1;
		
		// first page should be full
		self::assertEquals($pageSize, count(call_user_func_array(array($this->client, $methodName), array($this->userName, $this->password, $this->getProjectId(), 1, $pageSize ))));
		// second page should get just one issue, as $pageSize = $issueCount - 1;
		self::assertEquals(1, count(call_user_func_array(array($this->client, $methodName), array($this->userName, $this->password, $this->getProjectId(), 2, $pageSize ))));
		// third page should be empty
		self::assertEquals(0, count(call_user_func_array(array($this->client, $methodName), array($this->userName, $this->password, $this->getProjectId(), 3, $pageSize ))));
	}
	
	/**
	 * Verifies that after the last page no more issues are being returned
	 */
	public function testGetIssuesPaged() {

		$this->doTestGetPages('mc_project_get_issues');
	}
	
	public function testGetAllProjectsIssues() {
	
		$initialIssues = $this->getAllProjectsIssues();
	
		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetAllProjectsIssues' );
	
		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );
	
		$projectIssues = $this->getAllProjectsIssues();
	
		$this->assertEquals( 1, count( $projectIssues ) - count( $initialIssues ), "count(projectIssues) - count(initialIssues)");
		$this->assertEquals( $issueId, $projectIssues[0]->id, "issueId");
	}
	
	public function testGetAllProjectsIssueHeaders() {
	
		$initialIssues = $this->getAllProjectsIssueHeaders();
	
		$issueToAdd = $this->getIssueToAdd( 'FilterTest.testGetProjectIssueHeaders' );
	
		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );
	
		$projectIssues = $this->getAllProjectsIssueHeaders();
	
		$this->assertEquals( 1, count( $projectIssues ) - count( $initialIssues ), "count(projectIssues) - count(initialIssues)" );
		$this->assertEquals( $issueId, $projectIssues[0]->id, "issueId" );
	}
	
	public function testFilterGetIssuesReturnsIssueMonitors() {
	    
	    $issueToAdd = $this->getIssueToAdd( 'FilterTest.testFilterGetIssuesReturnsIssueMonitors' );
	    $issueToAdd['monitors'] = array(
	    	array ( 'id' => $this->userId )
	    );
	    
	    $issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
	    
	    $this->deleteAfterRun($issueId);
	    
	    $issues = $this->getAllProjectsIssues();
	    $createdIssue = null;
	    foreach ( $issues as $issue ) {
	        if ( $issue->id == $issueId ) {
	            $createdIssue = $issue;
	            break;
	        }
	    }
	    
	    self::assertNotNull($createdIssue, 'Created issue with id '. $issueId. ' was not found.');
	    self::assertObjectHasAttribute('monitors', $createdIssue, 'Created issue with id ' . $issueId . ' does not have a "monitors" attribute');
        self::assertEquals($this->userId, $createdIssue->monitors[0]->id);
	}

	/**
	 *
	 * @return array the project issues
	 */
	private function getProjectIssues() {

		return $this->client->mc_project_get_issues(
			$this->userName,
			$this->password,
			$this->getProjectId(),
			0,
			self::ISSUES_TO_RETRIEVE);
	}

	/**
	 * Gets the issues for the specified user.
	 * @param $filterType The filter type ('assigned', 'monitored', 'reported')
	 * @param $targetUser The target user object reference.
	 * @return array Matching issues
	 */
	private function getIssuesForUser( $filterType, $targetUser ) {
		// mc_project_get_issues_for_user( $p_username, $p_password, $p_project_id, $filterType, $p_target_user, $p_page_number, $p_per_page )
		return $this->client->mc_project_get_issues_for_user(
			$this->userName,
			$this->password,
			0,
			$filterType,
			$targetUser,
			1, // page number
			self::ISSUES_TO_RETRIEVE);
	}

	/**
	 *
	 * @return array the project issues
	 */
	private function getAllProjectsIssues() {

		return $this->client->mc_project_get_issues(
			$this->userName,
			$this->password,
			0,
			0,
			self::ISSUES_TO_RETRIEVE);
	}
	
	/**
	 *
	 * @return array the project issues
	 */
	private function getProjectIssueHeaders() {

		return $this->client->mc_project_get_issue_headers(
			$this->userName,
			$this->password,
			$this->getProjectId(),
			0,
			self::ISSUES_TO_RETRIEVE);
	}

	/**
	 *
	 * @return array the project issues
	 */
	private function getAllProjectsIssueHeaders() {

		return $this->client->mc_project_get_issue_headers(
			$this->userName,
			$this->password,
			0,
			0,
			self::ISSUES_TO_RETRIEVE);
	}
}
