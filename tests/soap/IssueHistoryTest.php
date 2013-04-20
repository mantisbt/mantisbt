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
 * @copyright Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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
		
		$issueHistory = $this->client->mc_issue_get_history( $this->userName, $this->password, $issueId );
		
		$this->assertEquals(1, count($issueHistory) );
		// validate the format of the initial history entry
		$historyData = $issueHistory[0];

		$this->assertNotEmpty($historyData->date);
		
		$this->assertEquals($this->userId, $historyData->userid);
		$this->assertEquals($this->userName, $historyData->username);
		
		$this->assertEquals(1, $historyData->type);

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
		
		$this->client->mc_issue_update(
				$this->userName,
				$this->password,
				$issueId,
				$issueToUpdate);		
		
		$issueHistory = $this->client->mc_issue_get_history( $this->userName, $this->password, $issueId );
		
		$this->assertEquals(2, count($issueHistory) );
		
		// validate the format of the history entry following the update
		$historyData = $issueHistory[1];

		$this->assertNotEmpty($historyData->date);
		
		$this->assertEquals($this->userId, $historyData->userid);
		$this->assertEquals($this->userName, $historyData->username);
		
		$this->assertEquals(0, $historyData->type);

		$this->assertEquals('summary', $historyData->field);
		$this->assertEquals($issueToAdd['summary'], $historyData->old_value);
		$this->assertEquals($t_summary_update, $historyData->new_value);
	}
}
