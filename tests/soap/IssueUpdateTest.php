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
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

		$this->client->mc_issue_delete(
			$this->userName,
			$this->password,
			$issueId);
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

		$issueToUpdate = $this->getIssueToAdd( 'IssueUpdateTest.testUpdateSummaryBasedOnMandatoryFields' );

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

		$this->client->mc_issue_delete(
			$this->userName,
			$this->password,
			$issueId);
	}
}
