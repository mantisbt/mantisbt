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
 */
class IssueHistoryTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue
	 * 2. validates that history entry is present
	 */
	public function testCreatedIssueHasHistoryEntry() {

		$issueToAdd = $this->getIssueToAdd( 'IssueHistoryTest.testCreatedIssueHasHistoryEntry' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issueHistory = $this->getIssueHistory( $issueId );

		$this->assertEquals(1, count($issueHistory) );
		// validate the format of the initial history entry
		$historyData = $issueHistory[0];

		$this->assertNotEmpty($historyData->date);

		$this->assertEquals($this->userId, $historyData->userid);
		$this->assertEquals($this->userName, $historyData->username);

		$this->assertEquals(NEW_BUG, $historyData->type);

		$this->assertEmpty($historyData->field);
		$this->assertEmpty($historyData->old_value);
		$this->assertEmpty($historyData->new_value);
	}

	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue
	 * 2. Updates the issue summary
	 * 3. Validates that a history entry for the update was created
	 */
	public function testUpdatedIssueHasHistoryEntry() {

		$issueToAdd = $this->getIssueToAdd( 'IssueHistoryTest.testUpdatedIssueHasHistoryEntry' );

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

		# Wait a sec before updating the issue to ensure that the
		# history entries are in the correct sequence
		sleep(1);
		$this->client->mc_issue_update(
				$this->userName,
				$this->password,
				$issueId,
				$issueToUpdate);

		$issueHistory = $this->getIssueHistory( $issueId );

		$this->assertEquals(2, count($issueHistory) );

		// validate the format of the history entry following the update
		$this->assertPropertyHistoryEntry(
			$issueHistory[1],
			'summary',
			$t_summary_update,
			$issueToAdd['summary']
		);
	}

	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue with non-default status and resolution
	 * 2. Validates that history entries are created for the status and resolution
	 */
	function testCreatedIssueWithNonDefaultStatusAndResolutionHasHistoryEntries() {

		$issueToAdd = $this->getIssueToAdd( 'IssueHistoryTest.testCreatedIssueWithNonDefaultStatusAndResolutionHasHistoryEntries' );
		$issueToAdd['status'] = array( 'id' => CONFIRMED ); // confirmed
		$issueToAdd['resolution'] = array ( 'id' => REOPENED ); // reopened

		$issueId = $this->client->mc_issue_add(
				$this->userName,
				$this->password,
				$issueToAdd);

		$this->deleteAfterRun( $issueId );

		$issueHistory = $this->getIssueHistory( $issueId );

		$this->assertEquals(3, count($issueHistory) );

		# History entries logged simultaneously may not be returned in
		# the same order, so we need to individually check for each one
		foreach( $issueHistory as $t_entry ) {
			if( $t_entry->type != NORMAL_TYPE ) {
				# Ignore unwanted history types
				continue;
			}
			$t_field = $t_entry->field;
			switch( $t_field ) {
				case 'status':
					$t_old_value = NEW_;
					break;
				case 'resolution':
					$t_old_value = OPEN;
					break;
				default:
					# We shouldn't get there, but just in case...
					continue 2;
			}
			$this->assertPropertyHistoryEntry(
				$t_entry,
				$t_field,
				$issueToAdd[$t_field]['id'],
				$t_old_value
			);
		}
	}

	/**
	 * Returns the issue's history in ascending order, regardless of the
	 * default history sort order
	 */
	private function getIssueHistory( $issueId ) {
		$issueHistory = $this->client->mc_issue_get_history( $this->userName, $this->password, $issueId );

		# Get default order for history entries
		$t_order = $this->client->mc_config_get_string( $this->userName, $this->password, 'history_order' );

		if( $t_order == 'ASC' ) {
			return $issueHistory;
		} else {
			return array_reverse( $issueHistory );
		}
	}

	private function assertPropertyHistoryEntry( $historyEntry, $fieldName, $fieldValue, $oldValue) {
		$this->assertNotEmpty($historyEntry->date);

		$this->assertEquals($this->userId, $historyEntry->userid);
		$this->assertEquals($this->userName, $historyEntry->username);

		$this->assertEquals(0, $historyEntry->type);

		$this->assertEquals($fieldName, $historyEntry->field);
		$this->assertEquals($fieldValue, $historyEntry->new_value);
		$this->assertEquals($oldValue, $historyEntry->old_value);
	}
}
