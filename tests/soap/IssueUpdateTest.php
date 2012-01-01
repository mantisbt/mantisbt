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
 * Test fixture for issue update webservice methods.
 */
class IssueUpdateTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Ability to create an issue with only the mandatory parameters.
	 * 2. Ability to retrieve the issue just added.
	 * 3. Ability to modify the summary of the retrieved issue and update it in MantisBT.
	 * 4. Ability to delete the issue.
	 */
	public function testUpdateSummaryBasedOnPreviousGet() {
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateSummary' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$createdIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$t_summary_update = $issueToAdd['summary'] . ' - updated';

		$issueToUpdate = $createdIssue;	
		$issueToUpdate->summary = $t_summary_update;

		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issueToUpdate);

		$updatedIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$issue = $updatedIssue;

		// explicitly specified fields
		$this->assertEquals( $issueToAdd['category'], $issue->category );
		$this->assertEquals( $t_summary_update, $issue->summary );
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
	}

	/**
	 * A test case that tests the following:
	 * 1. Ability to create an issue with only the mandatory parameters.
	 * 2. Ability to update the summary of the issue while only supplying the mandatory fields.
	 * 3. Ability to delete the issue.
	 */
	public function testUpdateSummaryBasedOnMandatoryFields() {
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateSummaryBasedOnMandatoryFields' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issueToUpdate = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateSummaryBasedOnMandatoryFields' );
		$issueToUpdate['sticky'] = true;

		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issueToUpdate);

		$updatedIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$issue = $updatedIssue;

		// explicitly specified fields
		$this->assertEquals( $issueToUpdate['category'], $issue->category );
		$this->assertEquals( $issueToUpdate['summary'], $issue->summary );
		$this->assertEquals( $issueToUpdate['description'], $issue->description );
		$this->assertEquals( $issueToUpdate['project']['id'], $issue->project->id );
		$this->assertEquals( $issueToUpdate['sticky'], $issue->sticky );

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
	}

	/**
	 * This test case tests the following:
	 * 1. Creation of an issue.
	 * 2. Adding a note to the issue.
	 * 3. Getting the issue and calling update - making sure the note is not duplicated.
	 * 4. Getting the issue, adding a new note and making sure that the first is not duplicated, but second is added.
	 * 5. Deleting the issue.
	 */
	public function testUpdateWithNewNote() {
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateWithNewNote' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$createdIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$noteData = array(
			'text' => "first note",
		);

		$this->client->mc_issue_note_add(
			$this->userName,
			$this->password,
			$issueId,
			$noteData);

		$issueWithNote = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( 1, count( $issueWithNote->notes ) );

		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issueWithNote);
			
		$issueWithNoteAfterUpdate = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		$this->assertEquals( 1, count( $issueWithNoteAfterUpdate->notes ) );

		$issueWithOneNewNote = $issueWithNoteAfterUpdate;
		$issueWithOneNewNote->notes[] = array( 'text' => 'second note', 'note_type' => 2, 'note_attr' => 'attr_value' );

		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issueWithOneNewNote);

		$issueWithTwoNotes = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( 2, count( $issueWithTwoNotes->notes ) );
		
		$newNote = $issueWithTwoNotes->notes[1];
		
		$this->assertEquals( 'second note', $newNote->text );
		$this->assertEquals( 2, $newNote->note_type );
		$this->assertEquals( 'attr_value', $newNote->note_attr );
		
	}
	
	/**
	 * This issue tests the following:
	 * 1. Retrieving all the administrator users, and verifying only one is present
	 * 2. Creating an issue
	 * 3. Retrieving the issue after it is created
	 * 4. Updating the issue to add a handler
	 * 5. Verifying that the correct handler is passed
	 * 6. Deleting the issue
	 */
	public function testUpdateIssueWithHandler() {

		$adminUsers = $this->client->mc_project_get_users($this->userName, $this->password, $this->getProjectId(), 90); 

		$this->assertTrue(count($adminUsers) >= 1 , "count(adminUsers) >= 1");

		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateIssueWithHandler' );

		$adminUser = $adminUsers[0];

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$issue->handler = $adminUser;
		
		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issue);
		
		$updatedIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( $adminUser->id, $updatedIssue->handler->id, 'handler.id' );
	}
	
	/**
	 * This issue tests the following
	 * 
	 * 1. Creating an issue
	 * 2. Retrieving the issue
	 * 3. Updating the issue with a new date
	 * 4. Re-retrieving the issue
	 * 5. Validating the value of the due date
	 */
	public function testUpdateIssueDueDate() {
		$this->skipIfDueDateIsNotEnabled();
		
		$date = '2015-10-29T12:59:14+00:00';

		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateIssueDueDate' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		$issue->due_date = $date;
		
		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issue);
		
		$updatedIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( $date, $updatedIssue->due_date, "due_date");
	}
	
	/**
	 * This issue tests the following
	 * 
	 * 1. Creating an issue with no category
	 * 2. Updating the issue to unset the category
	 * 3. Retrieving the issue
	 * 4. Verifying that the category is empty.
	 */
	public function testUpdateBugWithNoCategory() {
		$this->skipIfAllowNoCategoryIsDisabled();
		
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateBugWithNoCategory' );
		
        $issueId = $this->client->mc_issue_add(
			$this->userName,
            $this->password,
            $issueToAdd);
		
		$this->deleteAfterRun( $issueId );	
		
		$issueToUpdate = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		unset ( $issueToUpdate->category );
		
		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issueToUpdate);
			
		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		$this->assertEquals( '', $issue->category, 'category' );
	}
	
	/*		
	 * This test case tests the following:
	 * 1. Creation of an issue.
	 * 2. Updating the issue with a time tracking note
	 * 3. Verifying that the time tracking note on the issue is preseved
	 * 4. Deleting the issue.
	 */
	public function testUpdateWithTimeTrackingNote() {
		
		$this->skipIfTimeTrackingIsNotEnabled();
		
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateWithTimeTrackingNote' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		$issue->notes = array(
			array (	
				'text' => "first note",
				'time_tracking' => "30"
			)
		);
		
 		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issue);		

		$issueWithNote = $this->client->mc_issue_get(	
			$this->userName,
			$this->password,
			$issueId);
		
		$this->assertEquals( 1, count( $issueWithNote->notes ) );
		
		$this->assertEquals( 30, $issueWithNote->notes[0]->time_tracking);
	}
	
	/*		
	 * This test case tests the following:
	 * 1. Creation of an issue.
	 * 2. Updating the issue with rare fields
	 * 3. Getting the issue
	 * 4. Verifying that the rare field values are preserved
	 * 5. Deleting the issue.
	 */
	public function testUpdateWithRareFields() {
		
		$this->skipIfTimeTrackingIsNotEnabled();
		
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateWithRareFields' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
			
		$issue->build = 'build';
		$issue->platform = 'platform';
		$issue->os_build = 'os_build';
		$issue->os = 'os';
		
 		$this->client->mc_issue_update(
			$this->userName,
			$this->password,
			$issueId,
			$issue);		

		$retrievedIssue = $this->client->mc_issue_get(	
			$this->userName,
			$this->password,
			$issueId);
		
		$this->assertEquals( 'build', $retrievedIssue->build );
		$this->assertEquals( 'platform', $retrievedIssue->platform );
		$this->assertEquals( 'os', $retrievedIssue->os );
		$this->assertEquals( 'os_build', $retrievedIssue->os_build);
	}

	public function testUpdateWithTagOperations() {
		
		// initialise tags
		$tagId1 = $this->client->mc_tag_add( $this->userName, $this->password, array (
			'name' => 'IssueUpdateTest.testUpdateWithTagAdditions'
		));
		$this->deleteTagAfterRun( $tagId1 );
		
		$tagId2 = $this->client->mc_tag_add( $this->userName, $this->password, array (
			'name' => 'IssueUpdateTest.testUpdateWithTagAdditions2'
		));
		$this->deleteTagAfterRun( $tagId2 );
		
		$tag1 = new stdClass();
		$tag1->id = $tagId1;
		
		$tag2 = new stdClass();
		$tag2->id = $tagId2;
		
		// create issue
		$issueToAdd = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateWithRareFields' );
		$issueId = $this->client->mc_issue_add( $this->userName, $this->password, $issueToAdd);
		$this->deleteAfterRun( $issueId );
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		
		// update from 0 to 2 tags -> test attaching tags
		$issue->tags = array ( $tag1, $tag2 );
		$this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue);
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		self::assertEquals(2, count ( $issue->tags ) );
		
		// update from 2 to 1 tags -> test partially detaching tags
		$issue->tags = array ( $tag1 );
		$this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue);
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		self::assertEquals(1, count ( $issue->tags ) );

		// update from 1 to 2 tags -> test partially attaching tags 
		$issue->tags = array ( $tag1, $tag2 );
		$this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue);
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		self::assertEquals(2, count ( $issue->tags ) );
		
		// update from 2 to 0 tags -> test detaching tags
		$issue->tags = array();
		$this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue);
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
		self::assertEquals(0, count ( $issue->tags ) );
	}
}
