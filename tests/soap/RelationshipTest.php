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
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for issue relationships
 */
class RelationshipTest extends SoapBase {
	
	/**
	 * Creates two issues and adds a relationship between them
	 */
	public function testCreateIssuesAndAddRelation() {
		
	    $firstIssue = $this->getIssueToAdd( 'RelationshipTest.testCreateIssueAndAddRelation1' );

		$firstIssueId = $this->client->mc_issue_add( $this->userName, $this->password, $firstIssue);
			
		$this->deleteAfterRun( $firstIssueId );
		
	    $secondIssue = $this->getIssueToAdd( 'RelationshipTest.testCreateIssueAndAddRelation2' );

		$secondIssueId = $this->client->mc_issue_add( $this->userName, $this->password, $secondIssue);
			
		$this->deleteAfterRun( $secondIssueId );
		
		$relationship = array (
		    'type' => array (
		        'id' => 0 # BUG_DUPLICATE
		    ),
		    'target_id' => $secondIssueId
		);
		
		$this->client->mc_issue_relationship_add( $this->userName, $this->password, $firstIssueId, $relationship );
		
		$retrievedFirstIssue = $this->client->mc_issue_get($this->userName, $this->password, $firstIssueId);
		$retrievedSecondIssue = $this->client->mc_issue_get($this->userName, $this->password, $secondIssueId);
		
		$this->assertEquals(1, count ( $retrievedFirstIssue->relationships) );
		
		$firstRelationship = $retrievedFirstIssue->relationships[0];
		$this->assertEquals($secondIssueId, $firstRelationship->target_id);
		$this->assertEquals(0, $firstRelationship->type->id);
		
		$this->assertEquals(1, count ( $retrievedSecondIssue->relationships) );
		$secondRelationship = $retrievedSecondIssue->relationships[0];
		$this->assertEquals($firstIssueId, $secondRelationship->target_id);
		$this->assertEquals(4, $secondRelationship->type->id); # BUG_HAS_DUPLICATE
	}
	
	/**
	 * Creates two issues, adds and then deletes a relationship between them
	 */
	public function testDeleteRelation() {
		
	    $firstIssue = $this->getIssueToAdd( 'RelationshipTest.testCreateIssueAndAddRelation1' );

		$firstIssueId = $this->client->mc_issue_add( $this->userName, $this->password, $firstIssue);
			
		$this->deleteAfterRun( $firstIssueId );
		
	    $secondIssue = $this->getIssueToAdd( 'RelationshipTest.testCreateIssueAndAddRelation2' );

		$secondIssueId = $this->client->mc_issue_add( $this->userName, $this->password, $secondIssue);
			
		$this->deleteAfterRun( $secondIssueId );
		
		$relationship = array (
		    'type' => array (
		        'id' => 0 # BUG_DUPLICATE
		    ),
		    'target_id' => $secondIssueId
		);
		
		$relationshipId = $this->client->mc_issue_relationship_add( $this->userName, $this->password, $firstIssueId, $relationship );
		$this->client->mc_issue_relationship_delete ( $this->userName, $this->password, $firstIssueId, $relationshipId);
		
		$retrievedFirstIssue = $this->client->mc_issue_get($this->userName, $this->password, $firstIssueId);
		$retrievedSecondIssue = $this->client->mc_issue_get($this->userName, $this->password, $secondIssueId);
		
		$this->assertObjectNotHasAttribute('relationships', $retrievedFirstIssue);
		$this->assertObjectNotHasAttribute('relationships', $retrievedSecondIssue);
	}
}
