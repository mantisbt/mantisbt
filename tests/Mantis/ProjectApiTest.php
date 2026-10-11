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

require_once dirname( __DIR__, 2 ) . '/api/soap/mc_project_api.php';

/**
 * PHPUnit tests for project cache invalidation.
 */
class ProjectApiTest extends MantisCoreBase {

	/**
	 * Project ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $projectIdsToDelete = [];

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
	 * Re-establish the database connection and clear project caches.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::dbConnect();
		project_clear_cache();
	}

	/**
	 * Delete projects created by the tests.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach( array_reverse( $this->projectIdsToDelete ) as $t_project_id ) {
			if( project_exists( $t_project_id ) ) {
				project_delete( $t_project_id );
			}
		}

		parent::tearDown();
	}

	/**
	 * Verify that creating a project clears the project list cache.
	 *
	 * @return void
	 */
	public function testCreateProjectClearsProjectListCache(): void {
		$t_before_queries = db_count_queries();
		$t_cached_projects = project_get_all_rows();

		$this->assertGreaterThan( $t_before_queries, db_count_queries(), 'Initial project list should hit the database.' );
		$this->assertIsArray( $t_cached_projects );

		$t_project_id = project_create(
			'ProjectApiTest-' . uniqid( '', true ),
			'Created by ProjectApiTest',
			30
		);
		$this->projectIdsToDelete[] = $t_project_id;

		$t_after_create_queries = db_count_queries();
		$t_projects = project_get_all_rows();

		$this->assertGreaterThan( $t_after_create_queries, db_count_queries(), 'Project creation must clear the project list cache.' );
		$this->assertArrayHasKey( $t_project_id, $t_projects );
	}

	/**
	 * Verify that ProjectUpdateCommand returns data written after the project
	 * row cache has been primed.
	 *
	 * @return void
	 */
	public function testProjectUpdateCommandReturnsFreshCachedData(): void {
		$t_project_id = project_create(
			'ProjectUpdateCommandTest-' . uniqid( '', true ),
			'Created by ProjectUpdateCommandTest',
			30
		);
		$this->projectIdsToDelete[] = $t_project_id;

		project_get_row( $t_project_id );
		$t_description = 'Updated by ProjectUpdateCommandTest';
		$t_result = ( new \ProjectUpdateCommand( array(
			'query' => array( 'id' => $t_project_id ),
			'payload' => array( 'description' => $t_description ),
			'options' => array( 'return_project' => true ),
		) ) )->execute();

		$this->assertSame( $t_description, $t_result['project']['description'] );
	}
}
