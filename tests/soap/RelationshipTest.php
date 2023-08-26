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
 * Test fixture for issue relationships
 *
 * @requires extension soap
 * @group SOAP
 */
class RelationshipTest extends SoapBase {

	/**
	 * Creates two issues and adds a relationship between them
	 * @return void
	 */
	public function testCreateIssuesAndAddRelation() {
		$t_first_issue = $this->getIssueToAdd( '1' );
		$t_first_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_first_issue );
		$this->deleteAfterRun( $t_first_issue_id );

		$t_second_issue = $this->getIssueToAdd( '2' );
		$t_second_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_second_issue );
		$this->deleteAfterRun( $t_second_issue_id );

		$t_relationship = array (
			'type' => array (
				'id' => 0 # BUG_DUPLICATE
			),
			'target_id' => $t_second_issue_id
		);

		$this->client->mc_issue_relationship_add( $this->userName, $this->password, $t_first_issue_id, $t_relationship );

		$t_retrieved_first_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_first_issue_id );
		$t_retrieved_second_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_second_issue_id );

		$this->assertEquals( 1, count( $t_retrieved_first_issue->relationships ) );

		$t_first_relationship = $t_retrieved_first_issue->relationships[0];
		$this->assertEquals( $t_second_issue_id, $t_first_relationship->target_id );
		$this->assertEquals( 0, $t_first_relationship->type->id );

		$this->assertEquals( 1, count( $t_retrieved_second_issue->relationships ) );
		$t_second_relationship = $t_retrieved_second_issue->relationships[0];
		$this->assertEquals( $t_first_issue_id, $t_second_relationship->target_id );
		$this->assertEquals( 4, $t_second_relationship->type->id ); # BUG_HAS_DUPLICATE
	}

	/**
	 * Creates two issues, adds and then deletes a relationship between them
	 * @return void
	 */
	public function testDeleteRelation() {
		$t_first_issue = $this->getIssueToAdd( '1' );
		$t_first_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_first_issue );
		$this->deleteAfterRun( $t_first_issue_id );

		$t_second_issue = $this->getIssueToAdd( '2' );
		$t_second_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_second_issue );
		$this->deleteAfterRun( $t_second_issue_id );

		$t_relationship = array (
			'type' => array (
				'id' => 0 # BUG_DUPLICATE
			),
			'target_id' => $t_second_issue_id
		);

		$t_relationship_id = $this->client->mc_issue_relationship_add( $this->userName, $this->password, $t_first_issue_id, $t_relationship );
		$this->client->mc_issue_relationship_delete( $this->userName, $this->password, $t_first_issue_id, $t_relationship_id );

		$t_retrieved_first_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_first_issue_id );
		$t_retrieved_second_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_second_issue_id );

		$this->assertObjectNotHasAttribute( 'relationships', $t_retrieved_first_issue );
		$this->assertObjectNotHasAttribute( 'relationships', $t_retrieved_second_issue );
	}
}
