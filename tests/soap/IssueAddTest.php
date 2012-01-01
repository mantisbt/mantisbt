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
 * Test fixture for issue creation webservice methods.
 */
class IssueAddTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Ability to create an issue with only the mandatory parameters.
	 * 2. mc_issue_get_biggest_id()
	 * 3. mc_issue_get_id_from_summary()
	 * 4. The defaulting of the non-mandatory parameters. 
	 */
	public function testCreateIssue() {
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssue' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issueExists = $this->client->mc_issue_exists(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( true, $issueExists );

		$biggestId = $this->client->mc_issue_get_biggest_id(
			$this->userName,
			$this->password,
			$issueToAdd['project']['id']);

		$this->assertEquals( $issueId, $biggestId );

		$issueIdFromSummary = $this->client->mc_issue_get_id_from_summary(
			$this->userName,
			$this->password,
			$issueToAdd['summary']);

		$this->assertEquals( $issueId, $issueIdFromSummary );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// explicitly specified fields
		$this->assertEquals( $issueToAdd['category'], $issue->category );
		$this->assertEquals( $issueToAdd['summary'], $issue->summary );
		$this->assertEquals( $issueToAdd['description'], $issue->description );
		$this->assertEquals( $issueToAdd['project']['id'], $issue->project->id );

		// defaulted fields
		$this->assertEquals( $issueId, $issue->id );
		$this->assertEquals( 10, $issue->view_state->id );
		$this->assertEquals( 'public', $issue->view_state->name );
		$this->assertEquals( 30, $issue->priority->id );
		$this->assertEquals( 'normal', $issue->priority->name );
		$this->assertEquals( 50, $issue->severity->id );
		$this->assertEquals( 'minor', $issue->severity->name );
		$this->assertEquals( 10, $issue->status->id );
		$this->assertEquals( 'new', $issue->status->name );
		$this->assertEquals( $this->userName, $issue->reporter->name );
		$this->assertEquals( 70, $issue->reproducibility->id );
		$this->assertEquals( 'have not tried', $issue->reproducibility->name );
		$this->assertEquals( 0, $issue->sponsorship_total );
		$this->assertEquals( 10, $issue->projection->id );
		$this->assertEquals( 'none', $issue->projection->name );
		$this->assertEquals( 10, $issue->eta->id );
		$this->assertEquals( 'none', $issue->eta->name );
		$this->assertEquals( 10, $issue->resolution->id );
		$this->assertEquals( 'open', $issue->resolution->name );
		$this->assertEquals( false, $issue->sticky );

	}

	/**
	 * A test cases that tests the creation of issues with html markup in summary
	 * and description.
	 */
	public function testCreateIssueWithHtmlMarkup() {
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithHtmlMarkup' );

		$issueToAdd['summary'] .= " <b>WithHtmlMarkup</b>";
		$issueToAdd['description'] .= " <b>WithHtmlMarkup</b>";

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// explicitly specified fields
		$this->assertEquals( $issueToAdd['summary'], $issue->summary );
		$this->assertEquals( $issueToAdd['description'], $issue->description );

	}
	
	/**
	 * This issue tests the following:
	 * 1. Creating an issue with some fields that are typically not used at creation time.
	 *    For example: projection, eta, resolution, status, fixed_in_version, and target_version.
	 * 2. Get the issue and confirm that all fields are set as expected.
	 * 3. Delete the issue.
	 * 
	 * This test case was added for bug #9132.
	 */
	public function testCreateIssueWithRareFields() {
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithRareFields' );

		$issueToAdd['projection'] = array( 'id' => 90 );    // redesign
		$issueToAdd['eta'] = array( 'id' => 60 );           // > 1 month
		$issueToAdd['resolution'] = array( 'id' => 80 );    // suspended
		$issueToAdd['status'] = array( 'id' => 40 );        // confirmed
		$issueToAdd['fixed_in_version'] = 'fixed version';
		$issueToAdd['target_version'] = 'target version';
		$issueToAdd['sticky'] = true;

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// explicitly specified fields
		$this->assertEquals( $issueToAdd['projection']['id'], $issue->projection->id );
		$this->assertEquals( $issueToAdd['eta']['id'], $issue->eta->id );
		$this->assertEquals( $issueToAdd['resolution']['id'], $issue->resolution->id );
		$this->assertEquals( $issueToAdd['status']['id'], $issue->status->id );
		$this->assertEquals( $issueToAdd['fixed_in_version'], $issue->fixed_in_version );
		$this->assertEquals( $issueToAdd['target_version'], $issue->target_version );
		$this->assertEquals( $issueToAdd['sticky'], $issue->sticky );
	}

	/**
	 * This issue tests the following:
	 * 1. Retrieving all the administrator users, and verifying at least one is present
	 * 2. Creating an issue with the first admin user as a handler
	 * 3. Retrieving the issue after it is created
	 * 4. Verifying that the correct handler is passed
	 * 5. Deleting the issue
	 */
	public function testCreateIssueWithHandler() {

		$adminUsers = $this->client->mc_project_get_users($this->userName, $this->password, $this->getProjectId(), 90); 

		$this->assertTrue(count($adminUsers) >= 1 , "count(adminUsers) >= 1");

		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithHandler' );

		$adminUser = $adminUsers[0];

		$issueToAdd['handler'] = $adminUser;

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( $adminUser->id, $issue->handler->id, 'handler.id' );
	}
	
	/**
	 * This issue tests the following
	 * 
	 * 1. Creating an issue with a due date
	 * 2. Retrieving the issue
	 * 3. Validating that the due date is properly set
	 */
	public function testCreateIssueWithDueDate() {
		
		$this->skipIfDueDateIsNotEnabled();
		
		$date = '2015-10-29T12:59:14+00:00';
		
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithDueDate' );
		
		$issueToAdd['due_date'] = $date;
		
		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );			

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
			
		$this->assertEquals( $date, $issue->due_date, "due_date");
	}
	
	/**
	 * This issue tests the following
	 * 
	 * 1. Creating an issue without a due date
	 * 2. Retrieving the issue
	 * 3. Validating that the due date is properly encoded as null
	 * 
	 * This stricter verification originates in some SOAP frameworks, notably
	 * Axis, not accepting the empty tag format sent by nusoap by default, which
	 * is accepted by the PHP5 SOAP extension nevertheless.
	 */
	public function testCreateIssueWithNullDueDate() {
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithNullDueDate' );
		
		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );			

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
		
		$this->assertNull( $issue->due_date , 'due_date is not null' );
		$this->assertEquals( 'true', $this->readDueDate( $this->client->__getLastResponse() ) , 'xsi:nil not set to true' );
	}
	
	/**
	 * This issue tests the following
	 * 
	 * 1. Creating an issue with no category
	 * 2. Retrieving the issue
	 * 3. Verifying that the category is empty.
	 * 
	 */
	public function testCreateBugWithNoCategory() {
		$this->skipIfAllowNoCategoryIsDisabled();
		
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateBugWithNoCategory' );
		unset ( $issueToAdd['category'] );
		
		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );	

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		$this->assertEquals( '', $issue->category, 'category' );
		
	}
	
	/**
	 * 
	 * @param string $issueDataXml
	 * @return string the xsi:null value
	 */
	private function readDueDate( $issueDataXml ) {
		$reader = new XMLReader(); 
		$reader->XML( $this->client->__getLastResponse());
		
		while ( $reader->read() ) {
			switch ( $reader->nodeType ) {
				
				case XMLReader::ELEMENT:
					if ( $reader->name == 'due_date') {
						return $reader->getAttribute( 'xsi:nil' );
					}
					break;
			}
		}
		return null;
	}
	
	/**
	 * A test cases that tests the creation of issues 
	 * with a note passed in which contains time tracking data.
	 */
	public function testCreateIssueWithTimeTrackingNote() {
		
		$this->skipIfTimeTrackingIsNotEnabled();
		
		$issueToAdd = $this->getIssueToAdd( 'testCreateIssueWithNote' );
		$issueToAdd['notes'] = array(
			array(
				'text' => "first note",
				'time_tracking' => "30"
			)
		);

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// verify note existence and time tracking data
		$this->assertEquals( 1, count( $issue->notes ) );

		$note = $issue->notes[0];
		
		$this->assertEquals( 30, $note->time_tracking );
		
		$this->client->mc_issue_delete(
			$this->userName,
			$this->password,
			$issueId);
	}
	
	/**
	 * This issue tests the following
	 * 
	 * 1. Creating an issue where the handler is given by name
	 * 2. Retrieving the issue
	 * 3. Verifying that the handler name is correctly set
	 */
	public function testCreateIssueWithHandlerByName() {
		
		$issueToAdd = $this->getIssueToAdd( 'testCreateIssueWithHandlerByName' );
		$issueToAdd['handler'] = array(
			'name' => $this->userName
		);

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( $this->userName,  $issue->handler->name );
	}
	
	/**
	 * Tests that a created issue with a non-existent version returns the correct error message.
	 */
	public function testCreateIssueWithFaultyVersionGeneratesError() {

		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithFaultyVersionGeneratesError' );
		$issueToAdd['version'] = 'noSuchVersion';
		
		try {
			$this->client->mc_issue_add(
				$this->userName,
				$this->password,
				$issueToAdd);
			
			$this->fail( "Invalid version did not raise error." );
		} catch ( SoapFault $e) {
			$this->assertContains( "Version 'noSuchVersion' does not exist in project", $e->getMessage() );	
		}
	}
	
	/**
	 * Tests that an issue with a proper version set is correctly created
	 */
	public function testCreateIssueWithVersion() {
		
		$version = array (
			'project_id' => $this->getProjectId(),
			'name' => '1.0',
			'released' => 'true',
			'description' => 'Test version',
			'date_order' => ''
		);
		
		$versionId = $this->client->mc_project_version_add( $this->userName, $this->password, $version );
		
		$this->deleteVersionAfterRun( $versionId );
		
		$issueToAdd = $this->getIssueToAdd( 'IssueAddTest.testCreateIssueWithVersion' );
		$issueToAdd['version'] = $version['name'];
		
		$issueId = $this->client->mc_issue_add( $this->userName, $this->password, $issueToAdd );
		
		$this->deleteAfterRun($issueId);
		
		$createdIssue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		
		$this->assertEquals( $version['name'], $createdIssue->version );
	}
	
	/**
	 * Test that the biggest id is correctly retrieved
	 */
	public function testGetBiggestId() {
	    
	    $firstIssueId = $this->client->mc_issue_add( $this->userName, $this->password, $this->getIssueToAdd( 'IssueAddTest.testGetBiggestId1'));
        $this->deleteAfterRun( $firstIssueId );
	    
	    $secondIssueId = $this->client->mc_issue_add( $this->userName, $this->password, $this->getIssueToAdd( 'IssueAddTest.testGetBiggestId2'));
	    $this->deleteAfterRun( $secondIssueId );
	    
	    $firstIssue = $this->client->mc_issue_get( $this->userName, $this->password, $firstIssueId );
	    
	    // this update should trigger this issue's id to be returned as the biggest
	    // reported as bug #12887
		$this->client->mc_issue_update( $this->userName, $this->password, $firstIssueId, $firstIssue);
		
		$this->assertEquals( $secondIssueId, $this->client->mc_issue_get_biggest_id( $this->userName, $this->password, $this->getProjectId() ));
	}
	
	/**
	 * A test cases that tests the creation of issues 
	 * with a note passed in which contains time tracking data.
	 */
	public function testCreateIssueWithMiscNote() {
		
		$issueToAdd = $this->getIssueToAdd( 'testCreateIssueWithMiscNote' );
		$issueToAdd['notes'] = array(
			array(
				'text' => "first note",
				'note_type' => 2,
			    'note_attr' => 'attr_value'
			)
		);

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun($issueId);

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// verify note existence and time tracking data
		$this->assertEquals( 1, count( $issue->notes ) );

		$note = $issue->notes[0];
		
		$this->assertEquals( 2, $note->note_type );
		$this->assertEquals( 'attr_value', $note->note_attr );
	}
	
	public function testCreateIssueWithTags() {
		
		// initialise tags
		$tagId1 = $this->client->mc_tag_add( $this->userName, $this->password, array (
					'name' => 'IssueCreateTest.createIssueWithTags'
		));
		$this->deleteTagAfterRun( $tagId1 );
		
		$tagId2 = $this->client->mc_tag_add( $this->userName, $this->password, array (
					'name' => 'IssueCreateTest.createIssueWithTags2'
		));
		$this->deleteTagAfterRun( $tagId2 );
		
		// create issue
		$issueToAdd = $this->getIssueToAdd( 'IssueCreateTest.createIssueWithTags' );
		$issueToAdd['tags'] = array ( array( 'id' => $tagId1), array('id' => $tagId2 ) );
		$issueId = $this->client->mc_issue_add( $this->userName, $this->password, $issueToAdd);
		$this->deleteAfterRun( $issueId );
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		
		self::assertEquals ( 2, count ( $issue->tags ) );
	}
}
