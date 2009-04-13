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

		$this->client->mc_issue_delete(
			$this->userName,
			$this->password,
			$issueId);
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

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// explicitly specified fields
		$this->assertEquals( $issueToAdd['summary'], $issue->summary );
		$this->assertEquals( $issueToAdd['description'], $issue->description );

		$this->client->mc_issue_delete(
			$this->userName,
			$this->password,
			$issueId);
	}
}
