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
 * PHPUnit tests for tag cache invalidation.
 */
class TagApiTest extends MantisCoreBase {

	/**
	 * Tag ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $tagIdsToDelete = [];

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
	 * Re-establish the database connection and clear tag caches.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::dbConnect();
		tag_clear_cache();
		tag_clear_cache_bug_tags();
	}

	/**
	 * Delete tags created by the tests.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach( array_reverse( $this->tagIdsToDelete ) as $t_tag_id ) {
			if( tag_exists( $t_tag_id ) ) {
				tag_delete( $t_tag_id );
			}
		}

		parent::tearDown();
	}

	/**
	 * Verify that tag updates and deletion clear the tag cache.
	 *
	 * @return void
	 */
	public function testTagUpdateAndDeleteClearCache(): void {
		$t_tag_name = 'TagApiTest-' . uniqid( '', true );
		$t_tag_id = tag_create( $t_tag_name, auth_get_current_user_id(), 'Created by TagApiTest' );
		$this->tagIdsToDelete[] = $t_tag_id;

		$t_prime_queries = db_count_queries();
		$t_tag_row = tag_get( $t_tag_id );
		$this->assertGreaterThan( $t_prime_queries, db_count_queries(), 'Initial tag lookup should hit the database.' );
		$this->assertSame( $t_tag_name, $t_tag_row['name'] );

		$t_updated_name = $t_tag_name . '-updated';
		tag_update( $t_tag_id, $t_updated_name, auth_get_current_user_id(), 'Updated by TagApiTest' );

		$t_after_update_queries = db_count_queries();
		$this->assertSame( $t_updated_name, tag_get_name( $t_tag_id ) );
		$this->assertGreaterThan( $t_after_update_queries, db_count_queries(), 'Tag update should invalidate the cached tag row.' );

		tag_delete( $t_tag_id );

		$t_after_delete_queries = db_count_queries();
		$this->assertFalse( tag_get( $t_tag_id ) );
		$this->assertGreaterThan( $t_after_delete_queries, db_count_queries(), 'Tag delete should invalidate the cached tag row.' );
	}
}
