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
 * Test fixture for issue notes webservice methods.
 *
 * @requires extension soap
 * @group SOAP
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
	 * @return void
	 */
	public function testAddNote() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# Create a note, even though type is set to 2 (time tracking),
		# the note doesn't have time tracking info, and hence type will
		# be set to 0 (BUGNOTE).
		$t_note_data = array(
			'text' => 'first note',
		    'note_type' => 2 );

		$t_issue_note_id = $this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note_data );

		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );

		$t_note = $t_issue_with_note->notes[0];

		$this->assertEquals( $t_issue_note_id, $t_note->id );
		$this->assertEquals( $this->userName, $t_note->reporter->name );
		$this->assertEquals( $t_note_data['text'], $t_note->text );
		$this->assertEquals( 10, $t_note->view_state->id );
		$this->assertEquals( 'public', $t_note->view_state->name );
		$this->assertEquals( $t_note->date_submitted, $t_note->last_modified );
		$this->assertEquals( 0, $t_note->note_type );
		$this->assertEquals( '', $t_note->note_attr );

		# $timestamp = strtotime( $note->date_submitted );
		# $t_submited_date = date( "ymd", $timestamp );
		# $t_today_date = date( "ymd" );

		# $this->assertEquals( $t_today_date, $t_submited_date );
	}

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Add a note to the issue by specifying the text and time_tracking.
	 * 3. Get the issue.
	 * 4. Verify the note id against the one returned when adding the note.
	 * 5. Verify the time_tracking entry
	 * 6. Delete the issue.
	 * @return void
	 */
	public function testAddNoteWithTimeTracking() {
		$this->skipIfTimeTrackingIsNotEnabled();

		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		# Even though note type is not set, since there is time tracking info
		# it will be set to 2 (TIME_TRACKING).
		$t_note_data = array(
			'text' => 'first note',
			'time_tracking' => '30'
		);

		$t_issue_note_id = $this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note_data );

		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );

		$t_note = $t_issue_with_note->notes[0];

		$this->assertEquals( 30, $t_note->time_tracking );
		$this->assertEquals( 2, $t_note->note_type );

		$this->client->mc_issue_delete( $this->userName, $this->password, $t_issue_id );

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
	 * @return void
	 */
	public function testAddThenUpdateThenDeleteNote() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_note_data = array(
			'text' => 'some note',
		);

		$t_issue_note_id = $this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note_data );

		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );

		$t_note_data_new = array(
			'id' => $t_issue_note_id,
			'text' => 'some new note',
			'view_state' => array ( 'id' => 10 ) # public
		);

		$this->client->mc_issue_note_update( $this->userName, $this->password, $t_note_data_new );

		$t_issue_with_new_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );
		$this->assertEquals( $t_note_data_new['text'], $t_issue_with_new_note->notes[0]->text );
		$this->assertEquals( 'public', $t_issue_with_new_note->notes[0]->view_state->name );

		$this->client->mc_issue_note_delete( $this->userName, $this->password, $t_issue_note_id );

		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertFalse( isset( $t_issue_with_note->notes ) );
	}

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Attempt to add a note with no text.
	 * 3. Make sure the SoapFault exception is thrown.
	 * 4. Delete the issue.
	 * @return void
	 */
	public function testAddNoteWithNoText() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_note = array();

		try {
			$this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note );

			$this->assertTrue( false );
		} catch ( SoapFault $e ) {
		}
	}
}
