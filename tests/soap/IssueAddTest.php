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
 * Test fixture for issue creation webservice methods.
 *
 * @requires extension soap
 * @group SOAP
 */
class IssueAddTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Ability to create an issue with only the mandatory parameters.
	 * 2. mc_issue_get_biggest_id()
	 * 3. mc_issue_get_id_from_summary()
	 * 4. The defaulting of the non-mandatory parameters.
	 * @return void
	 */
	public function testCreateIssue() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue_exists = $this->client->mc_issue_exists( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( true, $t_issue_exists );

		$t_biggest_id = $this->client->mc_issue_get_biggest_id( $this->userName, $this->password, $t_issue_to_add['project']['id'] );

		$this->assertEquals( $t_issue_id, $t_biggest_id );

		$t_issue_id_from_summary = $this->client->mc_issue_get_id_from_summary( $this->userName, $this->password, $t_issue_to_add['summary'] );

		$this->assertEquals( $t_issue_id, $t_issue_id_from_summary );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# explicitly specified fields
		$this->assertEquals( $t_issue_to_add['category'], $t_issue->category );
		$this->assertEquals( $t_issue_to_add['summary'], $t_issue->summary );
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
		$this->assertEquals( false, $t_issue->sticky );
	}

	/**
	 * A test cases that tests the creation of issues with html markup in summary
	 * and description.
	 * @return void
	 */
	public function testCreateIssueWithHtmlMarkup() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_to_add['summary'] .= ' <strong>WithHtmlMarkup</strong>';
		$t_issue_to_add['description'] .= ' <strong>WithHtmlMarkup</strong>';

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# explicitly specified fields
		$this->assertEquals( $t_issue_to_add['summary'], $t_issue->summary );
		$this->assertEquals( $t_issue_to_add['description'], $t_issue->description );

	}

	/**
	 * This issue tests the following:
	 * 1. Creating an issue with some fields that are typically not used at creation time.
	 *    For example: projection, eta, resolution, status, fixed_in_version, and target_version.
	 * 2. Get the issue and confirm that all fields are set as expected.
	 * 3. Delete the issue.
	 *
	 * This test case was added for bug #9132.
	 * @return void
	 */
	public function testCreateIssueWithRareFields() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_eta_enabled = $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_eta' );
		$t_projection_enabled = $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_projection' );

		if( $t_projection_enabled ) {
			$t_issue_to_add['projection'] = array( 'id' => 90 );    # redesign
		}

		if( $t_eta_enabled ) {
			$t_issue_to_add['eta'] = array( 'id' => 60 );           # > 1 month
		}

		$t_issue_to_add['resolution'] = array( 'id' => 80 );    # suspended
		$t_issue_to_add['status'] = array( 'id' => 40 );        # confirmed
		$t_issue_to_add['sticky'] = true;

		# Must use valid versions for this to work.
		# $t_issue_to_add['fixed_in_version'] = 'fixed version';
		# $t_issue_to_add['target_version'] = 'target version';

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# explicitly specified fields
		if( $t_projection_enabled ) {
			$this->assertEquals( $t_issue_to_add['projection']['id'], $t_issue->projection->id );
		}

		if( $t_eta_enabled ) {
			$this->assertEquals( $t_issue_to_add['eta']['id'], $t_issue->eta->id );
		}

		$this->assertEquals( $t_issue_to_add['resolution']['id'], $t_issue->resolution->id );
		$this->assertEquals( $t_issue_to_add['status']['id'], $t_issue->status->id );
		$this->assertEquals( $t_issue_to_add['sticky'], $t_issue->sticky );

		# Since versions are not defined, they are not going to be set
		$this->assertFalse( isset( $t_issue->fixed_in_version ) );
		$this->assertFalse( isset( $t_issue->target_version ) );
	}

	/**
	 * This issue tests the following:
	 * 1. Retrieving all the administrator users, and verifying at least one is present
	 * 2. Creating an issue with the first admin user as a handler
	 * 3. Retrieving the issue after it is created
	 * 4. Verifying that the correct handler is passed
	 * 5. Deleting the issue
	 * @return void
	 */
	public function testCreateIssueWithHandler() {
		$t_admin_users = $this->client->mc_project_get_users( $this->userName, $this->password, $this->getProjectId(), 90 );

		$this->assertTrue( count( $t_admin_users ) >= 1, 'count(adminUsers) >= 1' );

		$t_issue_to_add = $this->getIssueToAdd();

		$t_admin_user = $t_admin_users[0];

		$t_issue_to_add['handler'] = $t_admin_user;

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $t_admin_user->id, $t_issue->handler->id, 'handler.id' );
	}

	/**
	 * This issue tests the following
	 *
	 * 1. Creating an issue with a due date
	 * 2. Retrieving the issue
	 * 3. Validating that the due date is properly set
	 * @return void
	 */
	public function testCreateIssueWithDueDate() {
		$this->skipIfDueDateIsNotEnabled();

		$t_date = '2015-10-29T12:59:14+00:00';

		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_to_add['due_date'] = $t_date;

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $this->dateToUTC( $t_date ), $this->dateToUTC( $t_issue->due_date ), 'due_date' );
	}

	/**
	 * This issue tests the following
	 *
	 * 1. Creating an issue without a due date
	 * 2. Retrieving the issue
	 * 3. Validating that the due date is properly encoded as null
	 *
	 * This stricter verification originates in some SOAP frameworks, notably
	 * Axis, not accepting the empty tag format, which is accepted by the PHP5
	 * SOAP extension nevertheless.
	 * @return void
	 */
	public function testCreateIssueWithNullDueDate() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertFalse( isset( $t_issue->due_date ), 'due_date should not be set' );
	}

	/**
	 * This issue tests the following
	 *
	 * 1. Creating an issue with no category
	 * 2. Retrieving the issue
	 * 3. Verifying that the category is empty.
	 *
	 * @return void
	 */
	public function testCreateBugWithNoCategory() {
		$this->skipIfAllowNoCategoryIsDisabled();

		$t_issue_to_add = $this->getIssueToAdd();
		unset( $t_issue_to_add['category'] );

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( '', $t_issue->category, 'category' );

	}

	/**
	 * A test cases that tests the creation of issues
	 * with a note passed in which contains time tracking data.
	 * @return void
	 */
	public function testCreateIssueWithTimeTrackingNote() {
		$this->skipIfTimeTrackingIsNotEnabled();

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['notes'] = array(
			array(
				'text' => 'first note',
				'time_tracking' => '30'
			)
		);

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# verify note existence and time tracking data
		$this->assertEquals( 1, count( $t_issue->notes ) );

		$t_note = $t_issue->notes[0];

		$this->assertEquals( 30, $t_note->time_tracking );

		$this->client->mc_issue_delete( $this->userName, $this->password, $t_issue_id );
	}

	/**
	 * This issue tests the following
	 *
	 * 1. Creating an issue where the handler is given by name
	 * 2. Retrieving the issue
	 * 3. Verifying that the handler name is correctly set
	 * @return void
	 */
	public function testCreateIssueWithHandlerByName() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['handler'] = array(
			'name' => $this->userName
		);

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $this->userName, $t_issue->handler->name );
	}

	/**
	 * Tests that a created issue with a non-existent version returns the correct error message.
	 * @return void
	 */
	public function testCreateIssueWithFaultyVersionGeneratesError() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = 'noSuchVersion';

		try {
			$this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

			$this->fail( 'Invalid version did not raise error.' );
		} catch ( SoapFault $e ) {
			$this->assertStringContainsString(
				"Version 'noSuchVersion' does not exist in project",
				$e->getMessage()
			);
		}
	}

	/**
	 * Tests that an issue with a proper version set is correctly created
	 * @return void
	 */
	public function testCreateIssueWithVersion() {
		$t_version = array (
			'project_id' => $this->getProjectId(),
			'name' => '1.0',
			'released' => 'true',
			'description' => 'Test version',
			'date_order' => ''
		);

		$t_version_id = $this->client->mc_project_version_add( $this->userName, $this->password, $t_version );

		$this->deleteVersionAfterRun( $t_version_id );

		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['version'] = $t_version['name'];

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $t_version['name'], $t_created_issue->version );
	}

	/**
	 * Test that the biggest id is correctly retrieved
	 * @return void
	 */
	public function testGetBiggestId() {
		$t_first_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $this->getIssueToAdd( '1' ) );
		$this->deleteAfterRun( $t_first_issue_id );

		$t_second_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $this->getIssueToAdd( '2' ) );
		$this->deleteAfterRun( $t_second_issue_id );

	    $t_first_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_first_issue_id );

	    # this update should trigger this issue's id to be returned as the biggest
	    # reported as bug #12887
		$this->client->mc_issue_update( $this->userName, $this->password, $t_first_issue_id, $t_first_issue );

		$this->assertEquals( $t_second_issue_id, $this->client->mc_issue_get_biggest_id( $this->userName, $this->password, $this->getProjectId() ) );
	}

	/**
	 * A test cases that tests the creation of issues
	 * with a note passed in which contains time tracking data.
	 * @return void
	 */
	public function testCreateIssueWithMiscNote() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['notes'] = array(
			array(
				'text' => 'first note',
				'note_type' => 2,
			    'note_attr' => 'attr_value'
			)
		);

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# verify note existence and time tracking data
		$this->assertEquals( 1, count( $t_issue->notes ) );

		$t_note = $t_issue->notes[0];

		$this->assertEquals( 2, $t_note->note_type );
		$this->assertEquals( 'attr_value', $t_note->note_attr );
	}

	/**
	 * A test cases that tests the creation of issues with a tag
	 * @return void
	 */
	public function testCreateIssueWithTags() {
		# initialise tags
		$t_tag_id1 = $this->client->mc_tag_add( $this->userName, $this->password, array(
					'name' => 'IssueCreateTest.createIssueWithTags'
		) );
		$this->deleteTagAfterRun( $t_tag_id1 );

		$t_tag_id2 = $this->client->mc_tag_add( $this->userName, $this->password, array(
					'name' => 'IssueCreateTest.createIssueWithTags2'
		) );
		$this->deleteTagAfterRun( $t_tag_id2 );

		# create issue
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['tags'] = array( array( 'id' => $t_tag_id1 ), array( 'id' => $t_tag_id2 ) );
		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
		$this->deleteAfterRun( $t_issue_id );
		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		self::assertEquals( 2, count( $t_issue->tags ) );
	}

	/**
	 * Tests that an issue with enumerated fields set by name has the field values correctly set
	 *
	 * @return void
	 */
	public function testCreateIssueWithFieldsByName() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_to_add['view_state'] = array( 'name' => 'private');
		$t_issue_to_add['resolution'] = array( 'name' => 'suspended');
		$t_issue_to_add['status'] = array( 'name' => 'confirmed');

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( $t_issue_to_add['view_state']['name'], $t_issue->view_state->name );
		$this->assertEquals( $t_issue_to_add['resolution']['name'], $t_issue->resolution->name );
		$this->assertEquals( $t_issue_to_add['status']['name'], $t_issue->status->name );
	}

	/**
	 * A test cases that tests the creation of issues with non-latin text, to validate that
	 * it is not stripped.
	 * @return void
	 */
	public function testCreateIssueWithNonLatinText() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_to_add['summary'] = 'Здравствуйте!'; # Russian, hello
		$t_issue_to_add['description'] = '你好'; # Mandarin Chinese, hello

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# explicitly specified fields
		$this->assertEquals( $t_issue_to_add['summary'], $t_issue->summary, 'summary is not correct' );
		$this->assertEquals( $t_issue_to_add['description'], $t_issue->description, 'description is not correct' );
	}

    /**
     * A test case that tests the following:
     * 1. mc_issues_get()
     * 3. mc_issues_get_header()
     * @return void
     */
    public function testIssuesGet() {

        $t_issue_to_add = $this->getIssueToAdd( '1' );
        $t_issue_to_add_2 = $this->getIssueToAdd( '2' );

        $t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );
        $t_issue_id_2 = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add_2 );

        $this->deleteAfterRun( $t_issue_id );
        $this->deleteAfterRun( $t_issue_id_2 );

        $t_search_result_issues = $this->client->mc_issues_get( $this->userName, $this->password, array( $t_issue_id, $t_issue_id_2 ) );
        $t_search_result_headers = $this->client->mc_issues_get_header( $this->userName, $this->password, array( $t_issue_id, $t_issue_id_2 ) );

        $this->assertEquals( 2, count( $t_search_result_headers ));
        $this->assertStringContainsString( 'testIssuesGet-1', $t_search_result_headers[0]->summary );
        $this->assertStringContainsString( 'testIssuesGet-2', $t_search_result_headers[1]->summary );

        $this->assertEquals( VS_PUBLIC, $t_search_result_headers[0]->view_state );
        $this->assertEquals( 0, $t_search_result_headers[0]->notes_count );
        $this->assertEquals( 0, $t_search_result_headers[0]->attachments_count );
        $this->assertEquals( 10, $t_search_result_headers[0]->status );

        $this->assertEquals( 2, count( $t_search_result_issues ));
        $this->assertStringContainsString( 'testIssuesGet-1', $t_search_result_issues[0]->summary );
        $this->assertStringContainsString( 'testIssuesGet-2', $t_search_result_issues[1]->summary );

        $this->assertEquals( VS_PUBLIC, $t_search_result_issues[0]->view_state->id);
        $this->assertEquals( 10, $t_search_result_issues[0]->status->id );
    }
}
