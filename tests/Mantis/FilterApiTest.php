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
 * Test cases for Filter API - user deletion filter cleanup
 *
 * @package    Tests
 * @subpackage FilterAPI
 * @copyright  Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for filter cleanup on user deletion
 */
class FilterApiTest extends MantisCoreBase {

	/**
	 * @var int Project id used for test filters.
	 */
	private static $projectId;

	/**
	 * @var array List of filter ids to delete in tearDown().
	 */
	private $filterIdsToDelete = array();

	/**
	 * Test class setup - login and initialize project id.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::login();

		if( array_key_exists( 'MANTIS_TESTSUITE_PROJECT_ID', $GLOBALS ) ) {
			self::$projectId = $GLOBALS['MANTIS_TESTSUITE_PROJECT_ID'];
		} else {
			self::$projectId = 1;
		}
	}

	/**
	 * Per-test setup - re-establish database connection.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::dbConnect();
	}

	/**
	 * Per-test teardown - delete any filters created during the test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach( $this->filterIdsToDelete as $t_filter_id ) {
			filter_db_delete_filter( $t_filter_id );
		}
	}

	/**
	 * Helper to create a test user and return its id.
	 *
	 * @return int User id
	 */
	private function createTestUser() {
		$t_cookie = user_create(
			'FilterTest' . rand(),
			'password',
			'filtertest' . rand() . '@test.test'
		);
		return user_get_id_by_cookie( $t_cookie );
	}

	/**
	 * Helper to create a filter owned by specified user.
	 *
	 * @param int    $p_user_id  Owner user id.
	 * @param bool   $p_is_public Whether the filter is shared/public.
	 * @param string $p_name     Filter name.
	 * @return int Filter id.
	 */
	private function createFilter( $p_user_id, $p_is_public, $p_name ) {
		$t_filter = filter_create_any();
		$t_filter_id = filter_db_create_filter(
			filter_serialize( $t_filter ),
			$p_user_id,
			self::$projectId,
			$p_name,
			$p_is_public
		);
		$this->filterIdsToDelete[] = $t_filter_id;
		return $t_filter_id;
	}

	/**
	 * Test that deleting a user removes their private filters.
	 *
	 * @return void
	 */
	public function testDeleteUserRemovesPrivateFilters() {
		$t_user_id = $this->createTestUser();

		$t_filter_id = $this->createFilter( $t_user_id, false, 'Private Filter' );

		user_delete( $t_user_id );

		$t_filters = filter_db_get_named_filters( null, null, null );
		$this->assertArrayNotHasKey( $t_filter_id, $t_filters,
			'Private filter should be deleted when user is deleted' );
	}

	/**
	 * Test that deleting a user preserves their shared (public) filters.
	 *
	 * @return void
	 */
	public function testDeleteUserPreservesSharedFilters() {
		$t_user_id = $this->createTestUser();

		$t_filter_id = $this->createFilter( $t_user_id, true, 'Shared Filter' );

		user_delete( $t_user_id );

		$t_filters = filter_db_get_named_filters( null, null, null );
		$this->assertArrayHasKey( $t_filter_id, $t_filters,
			'Shared filter should be preserved when user is deleted' );
	}

	/**
	 * Test that deleting a user with both private and shared filters
	 * only removes the private ones.
	 *
	 * @return void
	 */
	public function testDeleteUserWithMixedFilters() {
		$t_user_id = $this->createTestUser();

		$t_private_id_1 = $this->createFilter( $t_user_id, false, 'Private 1' );
		$t_private_id_2 = $this->createFilter( $t_user_id, false, 'Private 2' );
		$t_shared_id_1 = $this->createFilter( $t_user_id, true, 'Shared 1' );
		$t_shared_id_2 = $this->createFilter( $t_user_id, true, 'Shared 2' );

		user_delete( $t_user_id );

		$t_filters = filter_db_get_named_filters( null, null, null );

		$this->assertArrayNotHasKey( $t_private_id_1, $t_filters,
			'Private filter 1 should be deleted' );
		$this->assertArrayNotHasKey( $t_private_id_2, $t_filters,
			'Private filter 2 should be deleted' );
		$this->assertArrayHasKey( $t_shared_id_1, $t_filters,
			'Shared filter 1 should be preserved' );
		$this->assertArrayHasKey( $t_shared_id_2, $t_filters,
			'Shared filter 2 should be preserved' );
	}

	/**
	 * Test that deleting a user with no filters doesn't cause errors.
	 *
	 * @return void
	 */
	public function testDeleteUserWithNoFilters() {
		$t_user_id = $this->createTestUser();

		# Just delete - should not throw any errors
		user_delete( $t_user_id );

		# If we got here, the test passed
		$this->assertTrue( true );
	}

	/**
	 * Test that filter_db_delete_user_filters() with default $p_delete_shared
	 * only removes private filters.
	 *
	 * @return void
	 */
	public function testDeleteUserFiltersDefaultPreservesShared() {
		$t_user_id = $this->createTestUser();

		$t_private_id = $this->createFilter( $t_user_id, false, 'Private' );
		$t_shared_id = $this->createFilter( $t_user_id, true, 'Shared' );

		filter_db_delete_user_filters( $t_user_id );

		$t_filters = filter_db_get_named_filters( null, null, null );
		$this->assertArrayNotHasKey( $t_private_id, $t_filters,
			'Private filter should be deleted' );
		$this->assertArrayHasKey( $t_shared_id, $t_filters,
			'Shared filter should be preserved' );

		user_delete( $t_user_id );
	}

	/**
	 * Test that filter_db_delete_user_filters() with $p_delete_shared=true
	 * removes both private and shared filters.
	 *
	 * @return void
	 */
	public function testDeleteUserFiltersWithDeleteShared() {
		$t_user_id = $this->createTestUser();

		$t_private_id = $this->createFilter( $t_user_id, false, 'Private' );
		$t_shared_id = $this->createFilter( $t_user_id, true, 'Shared' );

		filter_db_delete_user_filters( $t_user_id, true );

		$t_filters = filter_db_get_named_filters( null, null, null );
		$this->assertArrayNotHasKey( $t_private_id, $t_filters,
			'Private filter should be deleted' );
		$this->assertArrayNotHasKey( $t_shared_id, $t_filters,
			'Shared filter should be deleted when $p_delete_shared is true' );

		user_delete( $t_user_id );
	}
}
