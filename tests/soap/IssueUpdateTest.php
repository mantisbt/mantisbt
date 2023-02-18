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
 * Test fixture for issue update webservice methods.
 *
 * @requires extension soap
 * @group SOAP
 */
class IssueUpdateTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Ability to create an issue with only the mandatory parameters.
	 * 2. Ability to retrieve the issue just added.
	 * 3. Ability to modify the summary of the retrieved issue and update it in MantisBT.
	 * 4. Ability to delete the issue.
	 * @return void
	 */
	public function testUpdateSummaryBasedOnPreviousGet() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_summary_update = $t_issue_to_add['summary'] . ' - updated';

		$t_issue_to_update = $t_created_issue;
		$t_issue_to_update->summary = $t_summary_update;

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue_to_update );

		$t_updated_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue = $t_updated_issue;

		# explicitly specified fields
		$this->assertEquals( $t_issue_to_add['category'], $t_issue->category );
		$this->assertEquals( $t_summary_update, $t_issue->summary );
		$this->assertEquals( $t_issue_to_add['description'], $t_issue->description );
		$this->assertEquals( $t_issue_to_add['project']['id'], $t_issue->project->id );

		# defaulted fields
		$this->assertEquals( $t_issue_id, $t_issue->id );
		$this->assertEquals( 10, $t_issue->view_state->id );
		$this->assertEquals( 'public', $t_issue->view_state->name );
		$this->assertEquals( 30, $t_issue->priority->id );
		$this->assertEquals( 'normal', $t_issue->priority->name );
		$this->assertEquals( 50, $t_issue->severity->id );
		$this->assertEquals( 'minor', $t_issue->severity->name );
		$this->assertEquals( 10, $t_issue->status->id );
		$this->assertEquals( 'new', $t_issue->status->name );
		$this->assertEquals( $this->userName, $t_issue->reporter->name );
		$this->assertEquals( 70, $t_issue->reproducibility->id );
		$this->assertEquals( 'have not tried', $t_issue->reproducibility->name );
		$this->assertEquals( 0, $t_issue->sponsorship_total );

		if( $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_projection' ) ) {
			$this->assertEquals( 10, $t_issue->projection->id );
			$this->assertEquals( 'none', $t_issue->projection->name );
		} else {
			$this->assertFalse( isset( $t_issue->projection ) );
		}

		if( $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_eta' ) ) {
			$this->assertEquals( 10, $t_issue->eta->id );
			$this->assertEquals( 'none', $t_issue->eta->name );
		} else {
			$this->assertFalse( isset( $t_issue->eta ) );
		}

		$this->assertEquals( 10, $t_issue->resolution->id );
		$this->assertEquals( 'open', $t_issue->resolution->name );
	}

	/**
	 * A test case that tests the following:
	 * 1. Ability to create an issue with only the mandatory parameters.
	 * 2. Ability to update the summary of the issue while only supplying the mandatory fields.
	 * 3. Ability to delete the issue.
	 * @return void
	 */
	public function testUpdateSummaryBasedOnMandatoryFields() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue_to_update = $this->getIssueToAdd();
		$t_issue_to_update['sticky'] = true;

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue_to_update );

		$t_updated_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue = $t_updated_issue;

		# explicitly specified fields
		$this->assertEquals( $t_issue_to_update['category'], $t_issue->category );
		$this->assertEquals( $t_issue_to_update['summary'], $t_issue->summary );
		$this->assertEquals( $t_issue_to_update['description'], $t_issue->description );
		$this->assertEquals( $t_issue_to_update['project']['id'], $t_issue->project->id );
		$this->assertEquals( $t_issue_to_update['sticky'], $t_issue->sticky );

		# defaulted fields
		$this->assertEquals( $t_issue_id, $t_issue->id );
		$this->assertEquals( 10, $t_issue->view_state->id );
		$this->assertEquals( 'public', $t_issue->view_state->name );
		$this->assertEquals( 30, $t_issue->priority->id );
		$this->assertEquals( 'normal', $t_issue->priority->name );
		$this->assertEquals( 50, $t_issue->severity->id );
		$this->assertEquals( 'minor', $t_issue->severity->name );
		$this->assertEquals( 10, $t_issue->status->id );
		$this->assertEquals( 'new', $t_issue->status->name );
		$this->assertEquals( $this->userName, $t_issue->reporter->name );
		$this->assertEquals( 70, $t_issue->reproducibility->id );
		$this->assertEquals( 'have not tried', $t_issue->reproducibility->name );
		$this->assertEquals( 0, $t_issue->sponsorship_total );

		if( $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_projection' ) ) {
			$this->assertEquals( 10, $t_issue->projection->id );
			$this->assertEquals( 'none', $t_issue->projection->name );
		} else {
			$this->assertFalse( isset( $t_issue->projection ) );
		}

		if( $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_eta' ) ) {
			$this->assertEquals( 10, $t_issue->eta->id );
			$this->assertEquals( 'none', $t_issue->eta->name );
		} else {
			$this->assertFalse( isset( $t_issue->eta ) );
		}

		$this->assertEquals( 10, $t_issue->resolution->id );
		$this->assertEquals( 'open', $t_issue->resolution->name );
	}

	/**
	 * This test case tests the following:
	 * 1. Creation of an issue.
	 * 2. Adding a note to the issue.
	 * 3. Getting the issue and calling update - making sure the note is not duplicated.
	 * 4. Getting the issue, adding a new note and making sure that the first is not duplicated, but second is added.
	 * 5. Deleting the issue.
	 * @return void
	 */
	public function testUpdateWithNewNote() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_note_data = array(
			'text' => 'first note',
		);

		$this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note_data );

		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue_with_note );

		$t_issue_with_noteAfterUpdate = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_noteAfterUpdate->notes ) );

		$t_issue_with_one_new_note = $t_issue_with_noteAfterUpdate;
		$t_issue_with_one_new_note->notes[] = array( 'text' => 'second note', 'note_type' => 2, 'note_attr' => 'attr_value' );

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue_with_one_new_note );

		$t_issue_with_two_notes = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 2, count( $t_issue_with_two_notes->notes ) );

		$t_new_note = $t_issue_with_two_notes->notes[1];

		$this->assertEquals( 'second note', $t_new_note->text );
		$this->assertEquals( 2, $t_new_note->note_type );
		$this->assertEquals( 'attr_value', $t_new_note->note_attr );
	}

	/**
	 * This issue tests the following:
	 * 1. Retrieving all the administrator users, and verifying only one is present
	 * 2. Creating an issue
	 * 3. Retrieving the issue after it is created
	 * 4. Updating the issue to add a handler
	 * 5. Verifying that the correct handler is passed
	 * 6. Deleting the issue
	 * @return void
	 */
	public function testUpdateIssueWithHandler() {
		$t_admin_users = $this->client->mc_project_get_users( $this->userName, $this->password, $this->getProjectId(), 90 );

		$this->assertTrue( count( $t_admin_users ) >= 1, 'count(adminUsers) >= 1' );

		$t_issue_to_add = $this->getIssueToAdd();

		$t_admin_user = $t_admin_users[0];

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->handler = $t_admin_user;

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_updated_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $t_admin_user->id, $t_updated_issue->handler->id, 'handler.id' );
	}

	/**
	 * This issue tests the following
	 *
	 * 1. Creating an issue
	 * 2. Retrieving the issue
	 * 3. Updating the issue with a new date
	 * 4. Re-retrieving the issue
	 * 5. Validating the value of the due date
	 * @return void
	 */
	public function testUpdateIssueDueDate() {
		$this->skipIfDueDateIsNotEnabled();

		$t_date = '2015-10-29T12:59:14+00:00';
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->due_date = $t_date;

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_updated_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $this->dateToUTC( $t_date ), $this->dateToUTC( $t_updated_issue->due_date ), 'due_date' );
	}

	/**
	 * This issue tests the following
	 *
	 * 1. Creating an issue with no category
	 * 2. Updating the issue to unset the category
	 * 3. Retrieving the issue
	 * 4. Verifying that the category is empty.
	 * @return void
	 */
	public function testUpdateBugWithNoCategory() {
		$this->skipIfAllowNoCategoryIsDisabled();

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue_to_update = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		unset ( $t_issue_to_update->category );

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue_to_update );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( '', $t_issue->category, 'category' );
	}

	/**
	 * This test case tests the following:
	 * 1. Creation of an issue.
	 * 2. Updating the issue with a time tracking note
	 * 3. Verifying that the time tracking note on the issue is preseved
	 * 4. Deleting the issue.
	 * @return void
	 */
	public function testUpdateWithTimeTrackingNote() {
		$this->skipIfTimeTrackingIsNotEnabled();

		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->notes = array(
			array (
				'text' => 'first note',
				'time_tracking' => '30'
			)
		);

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );

		$this->assertEquals( 30, $t_issue_with_note->notes[0]->time_tracking );
	}

	/**
	 * This test case tests the following:
	 * 1. Creation of an issue.
	 * 2. Updating the issue with rare fields
	 * 3. Getting the issue
	 * 4. Verifying that the rare field values are preserved
	 * 5. Deleting the issue.
	 * @return void
	 */
	public function testUpdateWithRareFields() {
		if( !$this->client->mc_config_get_string( $this->userName, $this->password, 'enable_product_build' ) ) {
			$this->markTestSkipped( 'Product build is not enabled' );
		}

		if( !$this->client->mc_config_get_string( $this->userName, $this->password, 'allow_freetext_in_profile_fields' ) ) {
			$this->markTestSkipped( '`allow_freetext_in_profile_fields` is not enabled' );
		}

		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->build = 'build';
		$t_issue->platform = 'platform';
		$t_issue->os_build = 'os_build';
		$t_issue->os = 'os';

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_retrieved_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 'build', $t_retrieved_issue->build );
		$this->assertEquals( 'platform', $t_retrieved_issue->platform );
		$this->assertEquals( 'os', $t_retrieved_issue->os );
		$this->assertEquals( 'os_build', $t_retrieved_issue->os_build );
	}

	/**
	 * Tests for updating Tags on issues
	 * @return void
	 */
	public function testUpdateWithTagOperations() {
		# initialise tags
		$t_tag_id1 = $this->client->mc_tag_add( $this->userName, $this->password, array(
			'name' => 'IssueUpdateTest.testUpdateWithTagAdditions'
		) );
		$this->deleteTagAfterRun( $t_tag_id1 );

		$t_tag_id2 = $this->client->mc_tag_add( $this->userName, $this->password, array(
			'name' => 'IssueUpdateTest.testUpdateWithTagAdditions2'
		) );
		$this->deleteTagAfterRun( $t_tag_id2 );

		$t_tag1 = new stdClass();
		$t_tag1->id = $t_tag_id1;

		$t_tag2 = new stdClass();
		$t_tag2->id = $t_tag_id2;

		# create issue
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# update from 0 to 2 tags -> test attaching tags
		$t_issue->tags = array ( $t_tag1, $t_tag2 );
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 2, count( $t_issue->tags ) );

		# update from 2 to 1 tags -> test partially detaching tags
		$t_issue->tags = array ( $t_tag1 );
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 1, count( $t_issue->tags ) );

		# update from 1 to 2 tags -> test partially attaching tags
		$t_issue->tags = array ( $t_tag1, $t_tag2 );
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 2, count( $t_issue->tags ) );

		# update from 2 to 0 tags -> test detaching tags
		$t_issue->tags = array();
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 0, count( $t_issue->tags ) );
	}

	/**
	 * Tests for updating Monitors on issues
	 * @return void
	 */
	public function testUpdateWithMonitors() {
		# create issue
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );

		# fresh issue -> no monitors exist
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 0, count( $t_issue->monitors ) );

		# update with this user as monitor -> should be added
		$t_issue->monitors = array ( array ( 'id' => $this->userId));
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 1, count( $t_issue->monitors ) );
		self::assertEquals( $this->userId, $t_issue->monitors[0]->id );

		# update with same monitor list -> should be preserved
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 1, count( $t_issue->monitors ) );
		self::assertEquals( $this->userId, $t_issue->monitors[0]->id );

		# update with empty monitor list -> should be removed
		$t_issue->monitors = array();
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );
		self::assertEquals( 0, count( $t_issue->monitors ) );
	}
}
