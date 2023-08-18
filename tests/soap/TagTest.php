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
 * Test fixture for non-login user methods
 *
 * @requires extension soap
 * @group SOAP
 */
class TagTest extends SoapBase {

	/**
	 * Tests retrieving, creating and deleting tags
	 * @return void
	 */
	public function testTagOperations() {
		$t_current_tag_count = sizeof( $this->client->mc_tag_get_all( $this->userName, $this->password, 1, 500 )->results );

		$t_tag_to_create = array (
			'name' => 'TagTest.testTagOperations',
			'description' => 'Tag created by unit test'
		);

		$t_new_tag_id = $this->client->mc_tag_add( $this->userName, $this->password, $t_tag_to_create );

		$t_results_with_created_tag = $this->client->mc_tag_get_all( $this->userName, $this->password, 1, 500 )->results;

		$t_new_tag_count = sizeof( $t_results_with_created_tag );

		$this->client->mc_tag_delete( $this->userName, $this->password, $t_new_tag_id );

		$t_final_tag_count = sizeof( $this->client->mc_tag_get_all( $this->userName, $this->password, 1, 500 )->results );

		self::assertEquals( $t_current_tag_count+1, $t_new_tag_count );
		self::assertEquals( $t_current_tag_count, $t_final_tag_count );

		$t_created_tag = null;

		foreach ( $t_results_with_created_tag as $t_tag ) {
			if( $t_tag->id == $t_new_tag_id ) {
				$t_created_tag = $t_tag;
				break;
			}
		}

		self::assertNotNull( $t_created_tag );
		self::assertEquals( $t_created_tag->name, $t_tag_to_create['name'] );
		self::assertEquals( $t_created_tag->description, $t_tag_to_create['description'] );
		self::assertNotNull( $t_created_tag->date_created );
		self::assertNotNull( $t_created_tag->date_updated );
		self::assertEquals( $t_created_tag->user_id->name, $this->userName );

	}

	/**
	 * Tests that creating tags with invalid names is not allowed
	 * @return void
	 */
	public function testCreateTagWithInvalidName() {
		$t_tag_to_create = array (
		    		'name' => '',
		    		'description' => ''
		);

		try {
			$this->client->mc_tag_add( $this->userName, $this->password, $t_tag_to_create );
			self::fail( 'Expected an error' );
		} catch ( SoapFault $e ) {
			$this->assertStringContainsString( 'Invalid tag name', $e->getMessage() );
		}
	}

	/**
	 * Tests that creating tags with invalid names is not allowed
	 * @return void
	 */
	public function testDeleteNonExistantTag() {
		try {
			$this->client->mc_tag_delete( $this->userName, $this->password, -1 );
			self::fail( 'Expected an error' );
		} catch ( SoapFault $e ) {
			$this->assertStringContainsString( 'No tag with id', $e->getMessage() );
		}
	}

	/**
	 * Tests that creating a tag with no description works
	 * @return void
	 */
	public function testCreateTagWithNoDescription() {
		$t_tag_to_create = array (
			'name' => 'TagTest.testCreateTagWithNoDescription'
		);

		$t_tag_id = $this->client->mc_tag_add( $this->userName, $this->password, $t_tag_to_create );

		$this->deleteTagAfterRun( $t_tag_id );
	}

	/**
	 * Tests that creating a tag with no description works
	 * @return void
	 */
	public function testCreateTagWithExistingName() {
		$t_tag_to_create = array (
			'name' => 'TagTest.testCreateTagWithExistingName'
		);
		$t_tag_id = $this->client->mc_tag_add( $this->userName, $this->password, $t_tag_to_create );
		$this->deleteTagAfterRun( $t_tag_id );

		try {
			$this->client->mc_tag_add( $this->userName, $this->password, $t_tag_to_create );
			self::fail( 'Expected an error' );
		} catch ( SoapFault $e ) {
			$this->assertStringContainsString( 'A tag with the same name already exists', $e->getMessage() );
		}
	}

	/**
	 * Tests that setting tags on issues works
	 * @return void
	 */
	public function testSetTagsOnIssue() {
		# create tag
		$t_tag_to_create = array (
		    		'name' => 'TagTest.testCreateTagWithExistingName'
		);
		$t_tag_id = $this->client->mc_tag_add( $this->userName, $this->password, $t_tag_to_create );
		$this->deleteTagAfterRun( $t_tag_id );

		# create issue
		$t_issue_to_create = $this->getIssueToAdd();
		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_create );
		$this->deleteAfterRun( $t_issue_id );

		# set tags
		$this->client->mc_issue_set_tags( $this->userName, $this->password, $t_issue_id, array ( array ( 'id' => $t_tag_id ) ) );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		self::assertEquals( 1, count( $t_issue->tags ) );
	}
}
