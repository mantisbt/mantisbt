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
 * Test fixture for issue notes webservice methods.
 */
class IssueNoteTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Add a note to the issue by only specifying the text.
	 * 3. Get the issue.
	 * 4. Verify the note id against the one returned when adding the note.
	 * 5. Verify the text as per specified earlier.
	 * 6. Verify the defaulting of the rest of the fields.
	 * 7. Verify that submitted / last updated times are the same.
	 * 8. Verify that submitted / last updated matches today.
	 * 9. Delete the issue.
	 */
	public function testAddNote() {
		$issueToAdd = $this->getIssueToAdd( 'IssueNoteTest.testAddNote' );

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
		    'note_type' => 2,
		    'note_attr' => 'attr_value'
		);
		
		$issueNoteId = $this->client->mc_issue_note_add(
			$this->userName,
			$this->password,
			$issueId,
			$noteData);

		$issueWithNote = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( 1, count( $issueWithNote->notes ) );

		$note = $issueWithNote->notes[0];

		$this->assertEquals( $issueNoteId, $note->id );
		$this->assertEquals( $this->userName, $note->reporter->name );
		$this->assertEquals( $noteData['text'], $note->text );
		$this->assertEquals( 10, $note->view_state->id );
		$this->assertEquals( 'public', $note->view_state->name );
		$this->assertEquals( $note->date_submitted, $note->last_modified );
		$this->assertEquals( 2, $note->note_type );
		$this->assertEquals( 'attr_value', $note->note_attr );

		/*
		$timestamp = strtotime( $note->date_submitted );
		$t_submited_date = date( "ymd", $timestamp );
		$t_today_date = date( "ymd" );

		$this->assertEquals( $t_today_date, $t_submited_date );
		*/
	}

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Add a note to the issue by specifying the text and time_tracking.
	 * 3. Get the issue.
	 * 4. Verify the note id against the one returned when adding the note.
	 * 5. Verify the time_tracking entry
	 * 6. Delete the issue.
	 */
	public function testAddNoteWithTimeTracking() {

		$this->skipIfTimeTrackingIsNotEnabled();

		$issueToAdd = $this->getIssueToAdd( 'IssueNoteTest.testAddNoteWithTimeTracking' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$noteData = array(
			'text' => "first note",
			'time_tracking' => "30"
		);
		
		$issueNoteId = $this->client->mc_issue_note_add(
			$this->userName,
			$this->password,
			$issueId,
			$noteData);

		$issueWithNote = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( 1, count( $issueWithNote->notes ) );

		$note = $issueWithNote->notes[0];
		
		$this->assertEquals( 30, $note->time_tracking );

		$this->client->mc_issue_delete(
			$this->userName,
			$this->password,
			$issueId);

	}

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Add a note to the issue.
	 * 3. Get the issue.
	 * 4. Verify that the issue has one note.
     * 5.  Update this note
     * 6.  Verify that the note has been updated
     * 7.  Delete the note.
     * 8.  Get the issue.
     * 9.  Verify that the issue has no notes.
     * 10. Delete the issue.
	 */
	public function testAddThenUpdateThenDeleteNote() {
		$issueToAdd = $this->getIssueToAdd( 'IssueNoteTest.testAddThenUpdateThenDeleteNote' );

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
			'text' => "some note",
		);

		$issueNoteId = $this->client->mc_issue_note_add(
			$this->userName,
			$this->password,
			$issueId,
			$noteData);

		$issueWithNote = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertEquals( 1, count( $issueWithNote->notes ) );
		
        $noteDataNew = array(
            'id' => $issueNoteId,
            'text' => "some new note"
        );

        $this->client->mc_issue_note_update(
            $this->userName,
            $this->password,
            $noteDataNew);

        $issueWithNewNote = $this->client->mc_issue_get(
            $this->userName,
            $this->password,
            $issueId);

        $this->assertEquals( 1, count( $issueWithNote->notes ) );

        $this->assertEquals( $noteDataNew['text'], $issueWithNewNote->notes[0]->text );

		$this->client->mc_issue_note_delete(
			$this->userName,
			$this->password,
			$issueNoteId);

		$issueWithNote = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		$this->assertFalse( isset( $issueWithNote->notes ) );
	}

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Attempt to add a note with no text.
	 * 3. Make sure the SoapFault exception is thrown.
	 * 4. Delete the issue.
	 */
	public function testAddNoteWithNoText() {
		$issueToAdd = $this->getIssueToAdd( 'IssueNoteTest.testAddNote' );

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
		);

		try {
			$issueNoteId = $this->client->mc_issue_note_add(
				$this->userName,
				$this->password,
				$issueId,
				$noteData);

			$this->assertTrue(false);
		} catch ( SoapFault $e ) {
		}
	}
}
