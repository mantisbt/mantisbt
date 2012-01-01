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
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for non-login user methods
 */
class TagTest extends SoapBase {

    /**
     * Tests retrieving, creating and deleting tags
     */
    public function testTagOperations() {
        
    	$currentTagCount = sizeof ( $this->client->mc_tag_get_all ( $this->userName, $this->password, 1, 500)->results );
    	
    	$tagToCreate = array ( 
    		'name' => 'TagTest.testTagOperations',
    		'description' => 'Tag created by unit test'
    	);
    	
    	$newTagId = $this->client->mc_tag_add ( $this->userName, $this->password, $tagToCreate );
    	
    	$resultsWithCreatedTag = $this->client->mc_tag_get_all ( $this->userName, $this->password, 1, 500)->results;
    	
    	$newTagCount = sizeof (  $resultsWithCreatedTag );
    	
    	$this->client->mc_tag_delete( $this->userName, $this->password, $newTagId );
    	
    	$finalTagCount = sizeof ( $this->client->mc_tag_get_all ( $this->userName, $this->password, 1, 500)->results );
    	
    	self::assertEquals ( $currentTagCount +1 , $newTagCount );
    	self::assertEquals ( $currentTagCount  , $finalTagCount );
    	
    	$createdTag = null;
    	
    	foreach ( $resultsWithCreatedTag as $tag ) {
    		
    		if ( $tag->id == $newTagId ) {
    			$createdTag = $tag;
    			break;
    		}
    	}
    	
    	self::assertNotNull( $createdTag );
    	self::assertEquals ( $createdTag->name, $tagToCreate['name']);
    	self::assertEquals ( $createdTag->description, $tagToCreate['description'] );
    	self::assertNotNull ( $createdTag->date_created );
    	self::assertNotNull ( $createdTag->date_updated );
    	self::assertEquals ( $createdTag->user_id->name, $this->userName );
    	
    }
    
    /**
	 * Tests that creating tags with invalid names is not allowed
     */
    public function testCreateTagWithInvalidName() {
    	
    	$tagToCreate = array (
    	    		'name' => '',
    	    		'description' => ''
    	);
    	 
    	try {
    		$this->client->mc_tag_add ( $this->userName, $this->password, $tagToCreate );
    		self::fail("Expected an error");
    	} catch ( SoapFault $e ) {
    		$this->assertContains( "Invalid tag name", $e->getMessage() );
    	}
    }

    /**
	 * Tests that creating tags with invalid names is not allowed
     */
    public function testDeleteNonExistantTag() {
    	
    	try {
    		$this->client->mc_tag_delete ( $this->userName, $this->password, -1 );
    		self::fail("Expected an error");
    	} catch ( SoapFault $e ) {
    		$this->assertContains( "No tag with id", $e->getMessage() );
    	}
    }
    
    /**
	 * Tests that creating a tag with no description works
     */
    public function testCreateTagWithNoDescription() {
    	
    	$tagToCreate = array (
    		'name' => 'TagTest.testCreateTagWithNoDescription'
    	);
    	
    	$tagId = $this->client->mc_tag_add ( $this->userName, $this->password, $tagToCreate );
    	
    	$this->deleteTagAfterRun( $tagId );
    }
    
    /**
	 * Tests that creating a tag with no description works
     */
    public function testCreateTagWithExistingName() {
    	
    	$tagToCreate = array (
    		'name' => 'TagTest.testCreateTagWithExistingName'
    	);
    	$tagId = $this->client->mc_tag_add ( $this->userName, $this->password, $tagToCreate );
    	$this->deleteTagAfterRun( $tagId );
    	
    	try {
    		$this->client->mc_tag_add ( $this->userName, $this->password, $tagToCreate );
    		self::fail("Expected an error");
    	} catch ( SoapFault $e ) {
    		$this->assertContains( "A tag with the same name already exists", $e->getMessage() );
    	}
    }
    
    /**
     * Tests that setting tags on issues works
     */
    public function testSetTagsOnIssue() {
    	
    	// create tag
    	$tagToCreate = array (
    	    		'name' => 'TagTest.testCreateTagWithExistingName'
    	);
    	$tagId = $this->client->mc_tag_add ( $this->userName, $this->password, $tagToCreate );
    	$this->deleteTagAfterRun( $tagId );
    	
    	// create issue
    	$issueToCreate = $this->getIssueToAdd('testTestTagsOnIssue');
    	$issueId = $this->client->mc_issue_add ( $this->userName, $this->password, $issueToCreate );
    	$this->deleteAfterRun( $issueId );
    	
    	// set tags
    	$this->client->mc_issue_set_tags ( $this->userName, $this->password, $issueId, array ( array ( 'id' => $tagId ) ) );
    	
    	$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId );
    	
    	self::assertEquals( 1, count ( $issue->tags ) );
    }
}
