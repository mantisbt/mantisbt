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
 * Mantis Webservice Tests for Mentions
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for handling issues with mentions in them.
 *
 * @requires extension soap
 * @group SOAP
 */
class MentionTest extends SoapBase {
	/**
	 * Create an issue with mentions and make sure the mentions round trip
	 * correctly.
	 * @return void
	 */
	public function testCreateIssue() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['summary'] .= ' @administrator';
		$t_issue_to_add['description'] .= ' @administrator';
		$t_issue_to_add['additional_information'] = 'additional info @administrator';
		$t_issue_to_add['steps_to_reproduce'] = 'steps to reproduce @administrator';

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# explicitly specified fields
		$this->assertEquals( $t_issue_to_add['summary'], $t_issue->summary );
		$this->assertEquals( $t_issue_to_add['description'], $t_issue->description );
		$this->assertEquals( $t_issue_to_add['additional_information'], $t_issue->additional_information );
		$this->assertEquals( $t_issue_to_add['steps_to_reproduce'], $t_issue->steps_to_reproduce );
	}

	/**
	 * Create an issue with mentions and find it by summary.
	 * @return void
	 */
	public function testFindIssueBySummary() {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_issue_to_add['summary'] .= ' @administrator';

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue_id_from_summary = $this->client->mc_issue_get_id_from_summary( $this->userName, $this->password, $t_issue_to_add['summary'] );

		$this->assertEquals( $t_issue_id, $t_issue_id_from_summary );
	}

	/**
	 * Testing adding and retrieving a note with mentions.
	 * @return void
	 */
	public function testAddNote() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_note_data = array(
			'text' => 'first note @administrator',
			'note_type' => 2,
			'note_attr' => 'attr_value'
		);

		$t_issue_note_id = $this->client->mc_issue_note_add( $this->userName, $this->password, $t_issue_id, $t_note_data );
		$t_issue_with_note = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->assertEquals( 1, count( $t_issue_with_note->notes ) );

		$t_note = $t_issue_with_note->notes[0];

		$this->assertEquals( $t_issue_note_id, $t_note->id );
		$this->assertEquals( $t_note_data['text'], $t_note->text );
	}
}
