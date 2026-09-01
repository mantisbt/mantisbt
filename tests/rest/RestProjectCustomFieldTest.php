<?php
# MantisBT - A PHP based bugtracking system
#
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
 * @copyright Copyright 2024 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\tests\rest;

/**
 * Test fixture for project custom field APIs.
 *
 * @group REST
 */
class RestProjectCustomFieldTest extends RestBase {
	/** @var int Custom field id used by the tests. */
	private $field_id;

	/**
	 * Set up an unlinked custom field for the test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$t_field_name = 'REST test custom field ' . rand( 1, 1000000 );
		$this->field_id = custom_field_create( $t_field_name );
		custom_field_update( $this->field_id, [
			'name' => custom_field_get_field( $this->field_id, 'name' ),
			'type' => CUSTOM_FIELD_TYPE_STRING,
			'default_value' => '',
			'access_level_r' => VIEWER,
			'access_level_rw' => REPORTER,
			'length_min' => 0,
			'length_max' => 0,
		] );
	}

	/**
	 * Test that creating a custom field invalidates the definition cache.
	 *
	 * @return void
	 */
	public function testCreateCustomFieldClearsCache(): void {
		$t_field_name = 'REST cache test custom field ' . rand( 1, 1000000 );
		custom_field_get_ids();
		$t_field_id = custom_field_create( $t_field_name );

		try {
			$this->assertSame( $t_field_name, custom_field_get_field( $t_field_id, 'name' ) );
			$this->assertEquals( $t_field_id, custom_field_get_id_from_name( $t_field_name ) );
			$this->assertContains( $t_field_id, custom_field_get_ids() );
		} finally {
			custom_field_destroy( $t_field_id );
		}
	}

	/**
	 * Test that updating a custom field invalidates its cached definition.
	 *
	 * @return void
	 */
	public function testUpdateCustomFieldClearsCache(): void {
		$t_old_name = custom_field_get_field( $this->field_id, 'name' );
		$t_new_name = 'REST updated custom field ' . rand( 1, 1000000 );
		custom_field_get_ids();
		custom_field_get_id_from_name( $t_old_name );

		custom_field_update( $this->field_id, [ 'name' => $t_new_name ] );

		$this->assertSame( $t_new_name, custom_field_get_field( $this->field_id, 'name' ) );
		$this->assertFalse( custom_field_get_id_from_name( $t_old_name ) );
		$this->assertEquals( $this->field_id, custom_field_get_id_from_name( $t_new_name ) );
	}

	/**
	 * Test that deleting a custom field invalidates its cached definition.
	 *
	 * @return void
	 */
	public function testDestroyCustomFieldClearsCache(): void {
		$t_field_name = custom_field_get_field( $this->field_id, 'name' );
		custom_field_get_ids();
		custom_field_get_id_from_name( $t_field_name );

		custom_field_destroy( $this->field_id );
		$t_field_id = $this->field_id;
		$this->field_id = 0;

		$this->assertNotContains( $t_field_id, custom_field_get_ids() );
		$this->assertFalse( custom_field_cache_row( $t_field_id, false ) );
		$this->assertFalse( custom_field_get_id_from_name( $t_field_name ) );
	}

	/**
	 * Test that linking and unlinking a custom field clears link caches.
	 *
	 * @return void
	 */
	public function testLinkAndUnlinkCustomFieldClearsCache(): void {
		$t_project_id = $this->getProjectId();
		$this->assertFalse( custom_field_is_linked( $this->field_id, $t_project_id ) );
		$this->assertNotContains( $this->field_id, custom_field_get_linked_ids( $t_project_id ) );
		$this->assertNotContains( $t_project_id, custom_field_get_project_ids( $this->field_id ) );

		custom_field_link( $this->field_id, $t_project_id );

		$this->assertTrue( custom_field_is_linked( $this->field_id, $t_project_id ) );
		$this->assertContains( $this->field_id, custom_field_get_linked_ids( $t_project_id ) );
		$this->assertContains( $t_project_id, custom_field_get_project_ids( $this->field_id ) );

		custom_field_unlink( $this->field_id, $t_project_id );

		$this->assertFalse( custom_field_is_linked( $this->field_id, $t_project_id ) );
		$this->assertNotContains( $this->field_id, custom_field_get_linked_ids( $t_project_id ) );
		$this->assertNotContains( $t_project_id, custom_field_get_project_ids( $this->field_id ) );
	}

	/**
	 * Remove the custom field after the test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if( $this->field_id && custom_field_is_linked( $this->field_id, $this->getProjectId() ) ) {
			custom_field_unlink( $this->field_id, $this->getProjectId() );
		}

		if( $this->field_id ) {
			custom_field_destroy( $this->field_id );
		}

		parent::tearDown();
	}

	/**
	 * Test linking a custom field with an explicit sequence and relinking it.
	 *
	 * @return void
	 */
	public function testLinkCustomFieldWithSequence(): void {
		$this->assertFalse( custom_field_is_linked( $this->field_id, $this->getProjectId() ) );
		$this->assertNotContains( $this->field_id, custom_field_get_linked_ids( $this->getProjectId() ) );
		$this->assertNotContains( $this->getProjectId(), custom_field_get_project_ids( $this->field_id ) );
		$t_endpoint = '/projects/' . $this->getProjectId() . '/fields/' . $this->field_id;

		$t_response = $this->builder()->post( $t_endpoint, [ 'sequence' => 37 ] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		custom_field_clear_cache( $this->field_id );
		$this->assertTrue( custom_field_is_linked( $this->field_id, $this->getProjectId() ) );
		$this->assertContains( $this->field_id, custom_field_get_linked_ids( $this->getProjectId() ) );
		$this->assertContains( $this->getProjectId(), custom_field_get_project_ids( $this->field_id ) );
		$this->assertEquals( 37, custom_field_get_sequence( $this->field_id, $this->getProjectId() ) );

		$t_response = $this->builder()->post( $t_endpoint, [ 'sequence' => 42 ] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$this->assertEquals( 42, custom_field_get_sequence( $this->field_id, $this->getProjectId() ) );
	}

	/**
	 * Test linking a custom field without a sequence appends it after existing fields.
	 *
	 * @return void
	 */
	public function testLinkCustomFieldWithAutomaticSequence(): void {
		$t_highest_sequence = 0;
		foreach( custom_field_get_linked_ids( $this->getProjectId() ) as $t_field_id ) {
			$t_highest_sequence = max( $t_highest_sequence, (int)custom_field_get_sequence( $t_field_id, $this->getProjectId() ) );
		}

		$t_endpoint = '/projects/' . $this->getProjectId() . '/fields/' . $this->field_id;
		$t_response = $this->builder()->post( $t_endpoint, [] )->send();

		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		custom_field_clear_cache( $this->field_id );
		$this->assertEquals( $t_highest_sequence + 10, custom_field_get_sequence( $this->field_id, $this->getProjectId() ) );
	}

	/**
	 * Test unlinking a linked custom field.
	 *
	 * @return void
	 */
	public function testUnlinkCustomField(): void {
		$t_endpoint = '/projects/' . $this->getProjectId() . '/fields/' . $this->field_id;
		$this->assertNotContains( $this->field_id, custom_field_get_linked_ids( $this->getProjectId() ) );
		$this->assertNotContains( $this->getProjectId(), custom_field_get_project_ids( $this->field_id ) );
		$t_response = $this->builder()->post( $t_endpoint, [] )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		custom_field_clear_cache( $this->field_id );
		$this->assertTrue( custom_field_is_linked( $this->field_id, $this->getProjectId() ) );
		$this->assertContains( $this->field_id, custom_field_get_linked_ids( $this->getProjectId() ) );
		$this->assertContains( $this->getProjectId(), custom_field_get_project_ids( $this->field_id ) );

		$t_response = $this->builder()->delete( $t_endpoint )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		custom_field_clear_cache( $this->field_id );
		$this->assertFalse( custom_field_is_linked( $this->field_id, $this->getProjectId() ) );
		$this->assertNotContains( $this->field_id, custom_field_get_linked_ids( $this->getProjectId() ) );
		$this->assertNotContains( $this->getProjectId(), custom_field_get_project_ids( $this->field_id ) );
	}
}
