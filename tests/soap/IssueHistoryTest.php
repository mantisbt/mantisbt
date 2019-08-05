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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for issue history
 *
 * @requires extension soap
 * @group SOAP
 */
class IssueHistoryTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue
	 * 2. validates that history entry is present
	 * @return void
	 */
	public function testCreatedIssueHasHistoryEntry() {
		$t_issue_to_add = $this->getIssueToAdd( 'IssueHistoryTest.testCreatedIssueHasHistoryEntry' );

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue_history = $this->getIssueHistory( $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_history ) );
		# validate the format of the initial history entry
		$t_history_data = $t_issue_history[0];

		$this->assertNotEmpty( $t_history_data->date );

		$this->assertEquals( $this->userId, $t_history_data->userid );
		$this->assertEquals( $this->userName, $t_history_data->username );

		$this->assertEquals( NEW_BUG, $t_history_data->type );

		$this->assertEmpty( $t_history_data->field );
		$this->assertEmpty( $t_history_data->old_value );
		$this->assertEmpty( $t_history_data->new_value );
	}

	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue
	 * 2. Updates the issue summary
	 * 3. Validates that a history entry for the update was created
	 * @return void
	 */
	public function testUpdatedIssueHasHistoryEntry() {
		$t_issue_to_add = $this->getIssueToAdd( 'IssueHistoryTest.testUpdatedIssueHasHistoryEntry' );

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_created_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_summary_update = $t_issue_to_add['summary'] . ' - updated';

		$t_issue_to_update = $t_created_issue;
		$t_issue_to_update->summary = $t_summary_update;

		# Wait a sec before updating the issue to ensure that the
		# history entries are in the correct sequence
		sleep( 1 );
		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue_to_update );

		$t_issue_history = $this->getIssueHistory( $t_issue_id );

		$this->assertEquals( 2, count( $t_issue_history ) );

		# validate the format of the history entry following the update
		$this->assertPropertyHistoryEntry( $t_issue_history[1], 'summary', $t_summary_update, $t_issue_to_add['summary'] );
	}

	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue with non-default status and resolution
	 * 2. Validates that a single history entry is created.
	 * @return void
	 */
	function testCreatedIssueWithNonDefaultStatusAndResolutionHasHistoryEntries() {
		$t_issue_to_add = $this->getIssueToAdd( 'IssueHistoryTest.testCreatedIssueWithNonDefaultStatusAndResolutionHasHistoryEntries' );
		$t_issue_to_add['status'] = array( 'id' => CONFIRMED ); # confirmed
		$t_issue_to_add['resolution'] = array ( 'id' => REOPENED ); # reopened

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue_history = $this->getIssueHistory( $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_history ) );
	}

	/**
	 * Returns the issue's history in ascending order, regardless of the
	 * default history sort order
	 * @param integer $p_issue_id Issue identifier.
	 * @return array
	 */
	private function getIssueHistory( $p_issue_id ) {
		$p_issue_history = $this->client->mc_issue_get_history( $this->userName, $this->password, $p_issue_id );

		# Get default order for history entries
		$t_order = $this->client->mc_config_get_string( $this->userName, $this->password, 'history_order' );

		if( $t_order == 'ASC' ) {
			return $p_issue_history;
		} else {
			return array_reverse( $p_issue_history );
		}
	}

	/**
	 * Check History entry is valid
	 * @param object $p_history_entry History object to check.
	 * @param string $p_field_name    Field name.
	 * @param mixed  $p_field_value   New field value.
	 * @param mixed  $p_old_value     Old field value.
	 * @return void
	 */
	private function assertPropertyHistoryEntry( $p_history_entry, $p_field_name, $p_field_value, $p_old_value ) {
		$this->assertNotEmpty( $p_history_entry->date );

		$this->assertEquals( $this->userId, $p_history_entry->userid );
		$this->assertEquals( $this->userName, $p_history_entry->username );

		$this->assertEquals( 0, $p_history_entry->type );

		$this->assertEquals( $p_field_name, $p_history_entry->field );
		$this->assertEquals( $p_field_value, $p_history_entry->new_value );
		$this->assertEquals( $p_old_value, $p_history_entry->old_value );
	}
}
