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

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for version cache invalidation.
 */
class VersionApiTest extends MantisCoreBase {

	/**
	 * Project ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $projectIdsToDelete = [];

	/**
	 * Version ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $versionIdsToDelete = [];

	/**
	 * Initialize the test class and authenticate the test user.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::login();
	}

	/**
	 * Re-establish the database connection and clear project and version caches.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::dbConnect();
		version_cache_clear();
		project_clear_cache();
	}

	/**
	 * Delete versions and projects created by the tests.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach( array_reverse( $this->versionIdsToDelete ) as $t_version_id ) {
			if( version_exists( $t_version_id ) ) {
				version_remove( $t_version_id );
			}
		}

		foreach( array_reverse( $this->projectIdsToDelete ) as $t_project_id ) {
			if( project_exists( $t_project_id ) ) {
				project_delete( $t_project_id );
			}
		}

		parent::tearDown();
	}

	/**
	 * Verify that version creation, updates, and deletion clear caches.
	 *
	 * @return void
	 */
	public function testVersionCreateUpdateDeleteClearsCaches(): void {
		$t_project_id = project_create(
			'VersionApiTest-' . uniqid( '', true ),
			'Created by VersionApiTest',
			30
		);
		$this->projectIdsToDelete[] = $t_project_id;

		$t_before_queries = db_count_queries();
		$t_versions = version_get_all_rows( $t_project_id );
		$this->assertGreaterThan( $t_before_queries, db_count_queries(), 'Initial version list should hit the database.' );
		$this->assertSame( [], $t_versions );

		$t_version_name = '1.0-' . uniqid( '', true );
		$t_version_id = version_add(
			$t_project_id,
			$t_version_name,
			VERSION_FUTURE,
			'Initial version'
		);
		$this->versionIdsToDelete[] = $t_version_id;

		$t_after_create_queries = db_count_queries();
		$t_versions = version_get_all_rows( $t_project_id );

		$this->assertGreaterThan( $t_after_create_queries, db_count_queries(), 'Version creation must clear the project version cache.' );
		$this->assertContains( $t_version_id, array_column( $t_versions, 'id' ) );
		$this->assertSame( $t_version_name, version_get_field( $t_version_id, 'version' ) );

		$t_updated_version_name = $t_version_name . '-updated';

		$t_version = version_get( $t_version_id );
		$t_version->version = $t_updated_version_name;
		$t_version->description = 'Updated version';
		$t_version->released = VERSION_RELEASED;
		version_update( $t_version );

		$t_after_update_queries = db_count_queries();
		$this->assertSame( $t_updated_version_name, version_get_field( $t_version_id, 'version' ) );
		$this->assertGreaterThan( $t_after_update_queries, db_count_queries(), 'Version update must clear the version row cache.' );

		$t_after_row_prime_queries = db_count_queries();
		$t_updated_versions = version_get_all_rows( $t_project_id );
		$this->assertGreaterThan( $t_after_row_prime_queries, db_count_queries(), 'Version update must clear the project version list cache.' );

		$t_found = false;
		foreach( $t_updated_versions as $t_row ) {
			if( (int)$t_row['id'] === (int)$t_version_id ) {
				$this->assertSame( $t_updated_version_name, $t_row['version'] );
				$t_found = true;
				break;
			}
		}
		$this->assertTrue( $t_found, 'Updated version must still be returned.' );

		version_remove( $t_version_id );

		$t_after_delete_queries = db_count_queries();
		$this->assertFalse( version_exists( $t_version_id ) );
		$this->assertGreaterThan( $t_after_delete_queries, db_count_queries(), 'Version delete must clear the version cache.' );

		$t_versions_after_delete = version_get_all_rows( $t_project_id );
		foreach( $t_versions_after_delete as $t_row ) {
			$this->assertNotSame( (int)$t_version_id, (int)$t_row['id'] );
		}
	}

	/**
	 * Verify that removing all project versions clears the version cache.
	 *
	 * @return void
	 */
	public function testVersionRemoveAllClearsCaches(): void {
		$t_project_id = project_create(
			'VersionApiTest-remove-all-' . uniqid( '', true ),
			'Created by VersionApiTest',
			30
		);
		$this->projectIdsToDelete[] = $t_project_id;

		$t_version_ids = [
			version_add( $t_project_id, '2.0-' . uniqid( '', true ), VERSION_FUTURE, 'First' ),
			version_add( $t_project_id, '3.0-' . uniqid( '', true ), VERSION_FUTURE, 'Second' ),
		];
		$this->versionIdsToDelete = array_merge( $this->versionIdsToDelete, $t_version_ids );

		$t_before_queries = db_count_queries();
		$t_versions = version_get_all_rows( $t_project_id );
		$this->assertGreaterThan( $t_before_queries, db_count_queries(), 'Version list should be cached before remove-all.' );
		$this->assertCount( 2, $t_versions );

		version_remove_all( $t_project_id );

		$t_after_delete_queries = db_count_queries();
		$t_versions = version_get_all_rows( $t_project_id );
		$this->assertGreaterThan( $t_after_delete_queries, db_count_queries(), 'Version remove-all must clear the project version cache.' );
		$this->assertSame( [], $t_versions );
	}

	/**
	 * Verify that VersionAddCommand and VersionUpdateCommand return data
	 * written after the relevant caches have been primed.
	 *
	 * @return void
	 */
	public function testVersionCommandsReturnFreshCachedData(): void {
		$t_project_id = project_create(
			'VersionCommandTest-' . uniqid( '', true ),
			'Created by VersionCommandTest',
			30
		);
		$this->projectIdsToDelete[] = $t_project_id;

		version_get_all_rows( $t_project_id );
		$t_version_name = '1.0-' . uniqid( '', true );
		$t_add_result = ( new \VersionAddCommand( array(
			'query' => array( 'project_id' => $t_project_id ),
			'payload' => array(
				'name' => $t_version_name,
				'description' => 'Created by VersionAddCommand',
			),
		) ) )->execute();
		$t_version_id = (int)$t_add_result['version']['id'];
		$this->versionIdsToDelete[] = $t_version_id;

		$this->assertSame( $t_version_name, $t_add_result['version']['name'] );
		$this->assertContains( $t_version_id, array_column( version_get_all_rows( $t_project_id ), 'id' ) );

		version_get( $t_version_id );
		$t_description = 'Updated by VersionUpdateCommand';
		$t_update_result = ( new \VersionUpdateCommand( array(
			'query' => array(
				'project_id' => $t_project_id,
				'version_id' => $t_version_id,
			),
			'payload' => array( 'description' => $t_description ),
		) ) )->execute();

		$this->assertSame( $t_description, $t_update_result['version']['description'] );
	}
}
