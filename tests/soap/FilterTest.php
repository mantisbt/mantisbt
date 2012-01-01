<?php
# MantisBT - a php based bugtracking system

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
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for filter related webservice method.
 */
class FilterTest extends SoapBase {
	
	const ISSUES_TO_RETRIEVE = 50;
	
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

		$issueCount;
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

	/**
	 *
	 * @return Array the project issues
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
	 *
	 * @return Array the project issues
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
	 * @return Array the project issues
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
	 * @return Array the project issues
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
