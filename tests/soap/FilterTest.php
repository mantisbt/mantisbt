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
 *
 * @noinspection PhpIllegalPsrClassPathInspection, PhpComposerExtensionStubsInspection,
 * @noinspection PhpUndefinedMethodInspection
 */

require_once 'SoapBase.php';

/**
 * Test fixture for filter related webservice method.
 *
 * @requires extension soap
 * @group SOAP
 */
class FilterTest extends SoapBase {
	/**
	 * Test the "assigned" filter type when issue is not assigned and no target user provided.
	 * @return void
	 */
	public function testGetIssuesForUserForUnassignedNoTargetUser() {
		$t_target_user = array();
		$t_issues_before_count = $this->getIssuesForUser( 'assigned', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'assigned', $t_target_user );

		$this->assertEquals( 1, count( $t_issues_after ) - count( $t_issues_before_count ), 'count(issuesCount) - count(initialIssuesCount)' );

		# Get the first non-sticky issue and check if it matches
		$t_issue = $t_issues_after[0];
		foreach( $t_issues_after as $t_issue ) {
			if( !$t_issue->sticky ) {
				break;
			}
		}
		$this->assertEquals( $t_issue_id, $t_issue->id, 'Created Issue Id not found in filter' );
	}

	/**
	 * Test the "assigned" filter type for unassigned issues with target user specified.
	 * @return void
	 */
	public function testGetIssuesForUserForUnassignedWithTargetUser() {
		$t_target_user = array( 'name' => $this->userName );
		$t_issues_before_count = $this->getIssuesForUser( 'assigned', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'assigned', $t_target_user );

		$this->assertEquals( 0, count( $t_issues_after ) - count( $t_issues_before_count ), 'count(issuesCount) - count(initialIssuesCount)' );
	}

	/**
	 * Test the "assigned" filter type for assigned issues with no target user.
	 * @return void
	 */
	public function testGetIssuesForUserForAssignedWithNoTargetUser() {
		$t_target_user = array();
		$t_issues_before_count = $this->getIssuesForUser( 'assigned', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		# Assign the issue to the reporter.
		$t_issue_to_add['handler'] = array( 'name' => $this->userName );

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'assigned', $t_target_user );

		$this->assertEquals( 0, count( $t_issues_after ) - count( $t_issues_before_count ), 'count(issuesCount) - count(initialIssuesCount)' );
	}

	/**
	 * Test the "assigned" filter type for assigned issues with target user specified.
	 * @return void
	 */
	public function testGetIssuesForUserForAssignedWithTargetUser() {
		$t_target_user = array( 'name' => $this->userName );
		$t_issues_before_count = $this->getIssuesForUser( 'assigned', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		# Assign the issue to the reporter.
		$t_issue_to_add['handler'] = $t_target_user;

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'assigned', $t_target_user );

		$this->assertEquals( 1, count( $t_issues_after ) - count( $t_issues_before_count ), 'count(issuesCount) - count(initialIssuesCount)' );
		$this->assertEquals( $t_issue_id, $t_issues_after[0]->id, 'issueId' );
	}

	/**
	 * Test the "assigned" filter type for assigned issues with target user specified.
	 * Make sure resolved issues are not returned.
	 * @return void
	 */
	public function testGetIssuesForUserForAssignedWithTargetUserNoResolved() {
		$t_target_user = array( 'name' => $this->userName );
		$t_issues_before_count = $this->getIssuesForUser( 'assigned', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		# Assign the issue to the reporter.
		$t_issue_to_add['handler'] = $t_target_user;

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->status = array( 'name' => 'resolved' );

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_issues_after = $this->getIssuesForUser( 'assigned', $t_target_user );

		$this->assertEquals( 0, count( $t_issues_after ) - count( $t_issues_before_count ), 'count(issuesCount) - count(initialIssuesCount)' );
	}

	/**
	 * Test the "reported" filter type with no target user.
	 *
	 * @return void
	 */
	public function testGetIssuesForUserReportedNoTargetUser() {
		$this->expectException( SoapFault::class );
		$t_target_user = array();
		$this->getIssuesForUser( 'reported', $t_target_user );
	}

	/**
	 * Test the "reported" filter type with target user.
	 * @return void
	 */
	public function testGetIssuesForUserReportedWithTargetUser() {
		$t_target_user = array( 'name' => $this->userName );
		$t_issues_before = $this->getIssuesForUser( 'reported', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'reported', $t_target_user );

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of issues reported by '$this->userName' increased by 1"
		);
		$this->assertContains(
			$t_issue_id,
			array_column( $t_issues_after, 'id' ),
			"Added Issue Id exists in filtered list"
		);
	}

	/**
	 * Test the "monitored" filter type with no target user.
	 * @return void
	 */
	public function testGetIssuesForUserMonitoredNoTargetUser() {
		$t_target_user = array();
		$t_issues_before = $this->getIssuesForUser( 'monitored', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'monitored', $t_target_user );

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of monitored issues increased by 1"
		);
	}

	/**
	 * Test the "monitored" filter type with target user.
	 * @return void
	 */
	public function testGetIssuesForUserMonitoredWithTargetUser() {
		$t_target_user = array( 'name' => $this->userName );
		$t_issues_before = $this->getIssuesForUser( 'monitored', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getIssuesForUser( 'monitored', $t_target_user );

		$this->assertCount( count( $t_issues_before ),
			$t_issues_after,
			"Number of monitored issues did not change"
		);
	}

	/**
	 * Test the "monitored" filter type with target user and a monitored issue.
	 * @return void
	 */
	public function testGetIssuesForUserForMonitoredWithTargetUserAndMatch() {
		$t_target_user = array( 'name' => $this->userName );
		$t_issues_before = $this->getIssuesForUser( 'monitored', $t_target_user );
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# Monitor the issue so it matches the file.
		$t_issue->monitors = array( array( 'id' => $this->userId ) );
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_issues_after = $this->getIssuesForUser( 'monitored', $t_target_user );

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of monitored issues increased by 1"
		);
		$this->assertContains(
			$t_issue_id,
			array_column( $t_issues_after, 'id' ),
			"Added Issue Id exists in filtered list"
		);
	}

	/**
	 * Test the "monitored" filter type with target user.
	 *
	 * @return void
	 */
	public function testGetIssuesForUserInvalidFilter() {
		$this->expectException( SoapFault::class );
		$t_target_user = array( 'name' => $this->userName );
		$this->getIssuesForUser( 'unknown', $t_target_user );
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issues
	 * 2. Creating an issue
	 * 3. Retrieving all the project's issues
	 * 4. Verifying that one extra issue is found in the results
	 * 5. Verifying that the first returned issue is the one we have submitted
	 * @return void
	 */
	public function testGetProjectIssues() {
		$t_issues_before = $this->getProjectIssues();
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getProjectIssues();

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of issues reported by '$this->userName' increased by 1"
		);
		$this->assertContains(
			$t_issue_id,
			array_column( $t_issues_after, 'id' ),
			"Added Issue Id exists in filtered list"
		);
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issue headers
	 * 2. Creating an issue
	 * 3. Retrieving all the project's issue headers
	 * 4. Verifying that one extra issue is found in the results
	 * 5. Verifying that the first returned issue is the one we have submitted
	 * @return void
	 */
	public function testGetProjectIssueHeaders() {
		$t_issues_before = $this->getProjectIssueHeaders();
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getProjectIssueHeaders();

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of issues increased by 1"
		);
		$this->assertContains(
			$t_issue_id,
			array_column( $t_issues_after, 'id' ),
			"Added Issue Id exists in filtered list"
		);
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issue headers
	 * 2. Creating an issue
	 * 3. Retrieving the issue
	 * 4. Creating 3 notes for that issue
	 * 5. Retrieving all the project's issue headers
	 * 7. Verifying that the first returned issue has 3 notes
	 * @return void
	 */
	public function testGetProjectIssueHeadersCountNotes() {
		$this->getProjectIssueHeaders();
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_note = array(
			'text' => 'Note text.'
		);
		$t_note_count = 3;
		for( $i = 0; $i < $t_note_count; $i++ ) {
			$this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note );
		}

		$t_project_issues = $this->getProjectIssueHeaders();

		$this->assertEquals( $t_note_count,
			$t_project_issues[0]->notes_count,
			"Created issue has the expected number of Bugnotes" );
	}


	/**
	 * A test case that tests the following:
	 * 1. Retrieving all the project's issues
	 * 2. Creating an issue with status = closed and resolution = fixed
	 * 3. Retrieving all the project's issues
	 * 4. Verifying that one extra issue is found in the results
	 * @return void
	 */
	public function testGetProjectClosedIssues() {
		$t_issues_before = $this->getProjectIssues();

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['status'] = 'closed';
		$t_issue_to_add['resolution'] = 'fixed';

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getProjectIssues();

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of Closed Issues increased by 1"
		);
	}

	/**
	 * A test case that tests the following:
	 *
	 * 1. Creating an issue with a category
	 * 2. Retrieving all the project's issues
	 * 3. Verifying that the created issue is present in the retrieved issues
	 *
	 * Test created to verify issue #11609
	 * @return void
	 */
	public function testGetProjectIssuesWithoutCategory() {
		$this->skipIfAllowNoCategoryIsDisabled();

		$t_issue_to_add = $this->getIssueToAdd();
		unset ( $t_issue_to_add['category'] );

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_project_issues = $this->getProjectIssues();

		$this->assertEquals( $t_issue_id,
			$t_project_issues[0]->id,
			"Added Issue exists in filtered list"
		);
	}

	/**
	 * Verifies that after the last page no more issues are being returned
	 * @return void
	 */
	public function testGetIssueHeadersPaged() {
		$this->doTestGetPages( 'mc_project_get_issue_headers' );
	}

	/**
	 * Handles paging of issue testing
	 * @param string $p_method_name Method name to call in client.
	 * @return void
	 */
	private function doTestGetPages( $p_method_name ) {
		$t_current_issues = count( $this->getProjectIssues() );
		if( $t_current_issues >= 3 ) {
			$t_issue_count = $t_current_issues;
		} else {
			# need to add

			$t_issue_count = 3;

			$t_to_add = $t_issue_count - $t_current_issues;

			while( $t_to_add > 0 ) {
				$t_issue = $this->getIssueToAdd();
				$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue );
				$this->deleteAfterRun( $t_issue_id );

				$t_to_add--;
			}
		}

		$t_page_size = $t_issue_count - 1;

		# first page should be full
		self::assertCount( $t_page_size,
			call_user_func_array( array( $this->client, $p_method_name ),
				array(
					$this->userName,
					$this->password,
					$this->getProjectId(),
					1,
					$t_page_size
				) )
		);
		# second page should get just one issue, as $t_page_size = $t_issue_count - 1;
		self::assertCount( 1,
			call_user_func_array( array( $this->client, $p_method_name ),
				array(
					$this->userName,
					$this->password,
					$this->getProjectId(),
					2,
					$t_page_size
				) )
		);
		# third page should be empty
		self::assertCount( 0,
			call_user_func_array( array( $this->client, $p_method_name ),
				array(
					$this->userName,
					$this->password,
					$this->getProjectId(),
					3,
					$t_page_size
				) )
		);
	}

	/**
	 * Verifies that after the last page no more issues are being returned
	 * @return void
	 */
	public function testGetIssuesPaged() {
		$this->doTestGetPages( 'mc_project_get_issues' );
	}

	/**
	 * Tests for getAllProjectsIssues
	 * @return void
	 */
	public function testGetAllProjectsIssues() {
		$t_issues_before = $this->getAllProjectsIssues();
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getAllProjectsIssues();

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of issues reported by '$this->userName' increased by 1"
		);
		$this->assertEquals( $t_issue_id,
			$t_issues_after[0]->id,
			"Added Issue exists in filtered list"
		);
	}

	/**
	 * Tests for getAllProjectsIssueHeaders
	 * @return void
	 */
	public function testGetAllProjectsIssueHeaders() {
		$t_issues_before = $this->getAllProjectsIssueHeaders();
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		$t_issues_after = $this->getAllProjectsIssueHeaders();

		$this->assertCount( count( $t_issues_before ) + 1,
			$t_issues_after,
			"Number of issues increased by 1"
		);
		$this->assertEquals( $t_issue_id,
			$t_issues_after[0]->id,
			"Added Issue exists in filtered list"
		);
	}

	/**
	 * Test to check that Get Issues returns issue monitors
	 * @return void
	 */
	public function testFilterGetIssuesReturnsIssueMonitors() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['monitors'] = array(
			array( 'id' => $this->userId )
		);

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issues = $this->getAllProjectsIssues();
		$t_created_issue = null;
		foreach( $t_issues as $t_issue ) {
			if( $t_issue->id == $t_issue_id ) {
				$t_created_issue = $t_issue;
				break;
			}
		}

		self::assertNotNull( $t_created_issue, 'Created issue with id ' . $t_issue_id . ' was not found.' );
		self::assertObjectHasAttribute( 'monitors',
			$t_created_issue,
			'Created issue with id ' . $t_issue_id . ' does not have a "monitors" attribute'
		);
		self::assertEquals( $this->userId, $t_created_issue->monitors[0]->id );
	}

	/**
	 * Get issues for a given project
	 * @return array the project issues
	 */
	private function getProjectIssues() {
		return $this->client->mc_project_get_issues( $this->userName,
			$this->password,
			$this->getProjectId(),
			0,
			$this->maxIssues
		);
	}

	/**
	 * Gets the issues for the specified user.
	 * @param string $p_filter_type The filter type ('assigned', 'monitored', 'reported').
	 * @param array  $p_target_user The target user object reference.
	 * @return array Matching issues
	 */
	private function getIssuesForUser( $p_filter_type, array $p_target_user ) {
		return $this->client->mc_project_get_issues_for_user(
			$this->userName,
			$this->password,
			$this->getProjectId(),
			$p_filter_type,
			$p_target_user,
			1, # page number
			$this->maxIssues
		);
	}

	/**
	 * Get issues for all projects
	 * @return array the project issues
	 */
	private function getAllProjectsIssues() {
		$t_issues = $this->client->mc_project_get_issues(
			$this->userName,
			$this->password,
			0,
			0,
			$this->maxIssues
		);
		$this->skipIfTooManyIssues( $t_issues );
		return $t_issues;
	}

	/**
	 * Get issue headers for a given project
	 * @return array the project issues
	 */
	private function getProjectIssueHeaders() {
		return $this->client->mc_project_get_issue_headers(
			$this->userName,
			$this->password,
			$this->getProjectId(),
			0,
			$this->maxIssues
		);
	}

	/**
	 * Get issue headers for all projects
	 * @return array the project issues
	 */
	private function getAllProjectsIssueHeaders() {
		$t_issues = $this->client->mc_project_get_issue_headers(
			$this->userName,
			$this->password,
			0,
			0,
			$this->maxIssues
		);
		$this->skipIfTooManyIssues( $t_issues );
		return $t_issues;
	}

	/**
	 * Marks the test case as skipped if the number of issues.
	 *
	 * Some tests can't be completed if there are more Issues in the database
	 * than the maximum number to retrieve {@see SoapBase::$maxIssues} because
	 * they compare this number before/after executing the test.
	 *
	 * @param array $p_issues Issues as returned by mc_project_get_issues* API
	 *
	 * @return void
	 */
	private function skipIfTooManyIssues( $p_issues ) {
		if( $this->maxIssues <= count( $p_issues ) ) {
			$this->markTestSkipped( "Skipping - database contains more than "
				. $this->maxIssues . " Issues"
			);
		}
	}

	/**
	 * Test the custom filter search by all possible parameters
	 * methods: mc_filter_search_issue_headers, mc_filter_search_issue_ids and mc_filter_search_issues
	 * @return void
	 */
	public function testCustomFilterSearchAllPossibleParameters() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['severity'] = array( 'id' => BLOCK );
		$t_issue_to_add['status'] = array( 'id' => 50 );
		$t_issue_to_add['priority'] = array( 'id' => NORMAL );
		$t_issue_to_add['reporter'] = array( 'id' => 1 );
		$t_issue_to_add['handler'] = array( 'id' => 1 );
		$t_issue_to_add['resolution'] = array( 'id' => FIXED );
		$t_issue_to_add['sticky'] = true;
		$t_issue_to_add['view_state'] = array( 'id' => VS_PUBLIC );
		$t_issue_to_add['platform'] = 'test_plaform';
		$t_issue_to_add['os'] = 'test_os';
		$t_issue_to_add['os_build'] = 'test_6';

		# Must use valid versions for this to work.
		# $t_issue_to_add['fixed_in_version'] = 'test_69';
		# $t_issue_to_add['target_version'] = 'test_70';

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_filter = array(
			'project_id' => array( 0 ),
			'category' => array( $this->getCategory() ),
			'severity_id' => array( BLOCK ),
			'status_id' => array( 50 ),
			'priority_id' => array( NORMAL ),
			'reporter_id' => array( 1 ),
			'handler_id' => array( 1 ),
			'resolution_id' => array( FIXED ),

			'hide_status_id' => array( -2 ),
			'sort' => 'last_updated',
			'sort_direction' => 'DESC',
			'sticky' => true,
			'view_state' => array( VS_PUBLIC ),
			# These versions must exist for this to work
			# 'fixed_in_version' => array( 'test_69' ),
			# 'target_version' => array( 'test_70' ),
			'platform' => array( 'test_plaform' ),
			'os' => array( 'test_os' ),
			'os_build' => array( 'test_6' )
		);

		$t_search_result_headers = $this->client->mc_filter_search_issue_headers( $this->userName, $this->password, $t_filter, 1, -1 );
		$t_search_result_ids = $this->client->mc_filter_search_issue_ids( $this->userName, $this->password, $t_filter, 1, -1 );
		$t_search_result_issues = $this->client->mc_filter_search_issues( $this->userName, $this->password, $t_filter, 1, -1 );

		$this->assertCount( 1, $t_search_result_headers );
		$this->assertEquals( VS_PUBLIC, $t_search_result_headers[0]->view_state );
		$this->assertEquals( $this->getCategory(), $t_search_result_headers[0]->category );
		$this->assertEquals( 0, $t_search_result_headers[0]->notes_count );
		$this->assertEquals( 0, $t_search_result_headers[0]->attachments_count );
		$this->assertEquals( BLOCK, $t_search_result_headers[0]->severity );

		$this->assertCount( 1, $t_search_result_ids );

		$this->assertCount( 1, $t_search_result_issues );
		$this->assertEquals( VS_PUBLIC, $t_search_result_issues[0]->view_state->id);
		$this->assertEquals( $this->getCategory(), $t_search_result_issues[0]->category );
		$this->assertEquals( BLOCK, $t_search_result_issues[0]->severity->id );

		// filter doesn't match any issue
		$t_filter['severity_id'] = array( FEATURE );

		$t_search_result_headers = $this->client->mc_filter_search_issue_headers( $this->userName, $this->password, $t_filter, 1, -1 );
		$t_search_result_ids = $this->client->mc_filter_search_issue_ids( $this->userName, $this->password, $t_filter, 1, -1 );
		$t_search_result_issues = $this->client->mc_filter_search_issues( $this->userName, $this->password, $t_filter, 1, -1 );

		$this->assertCount( 0, $t_search_result_headers );
		$this->assertCount( 0, $t_search_result_ids );
		$this->assertCount( 0, $t_search_result_issues );
	}
}
