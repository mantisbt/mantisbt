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
 * PHPUnit tests for sponsorship cache invalidation.
 */
class SponsorshipApiTest extends MantisCoreBase {

	/**
	 * Sponsorship ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $sponsorshipIdsToDelete = [];

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
	 * Re-establish the database connection and clear sponsorship caches.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::dbConnect();
		sponsorship_clear_cache();
	}

	/**
	 * Delete sponsorships created by the tests.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach( array_reverse( $this->sponsorshipIdsToDelete ) as $t_sponsorship_id ) {
			if( sponsorship_exists( $t_sponsorship_id ) ) {
				sponsorship_delete( $t_sponsorship_id );
			}
		}

		parent::tearDown();
	}

	/**
	 * Verify that sponsorship creation, updates, and deletion clear caches.
	 *
	 * @return void
	 */
	public function testSponsorshipCreateUpdateAndDeleteClearCaches(): void {
		if( !bug_exists( 1 ) ) {
			$this->markTestSkipped( 'Seed bug #1 is required for sponsorship cache tests.' );
		}

		$t_bug_id = 1;
		$t_user_id = auth_get_current_user_id();
		$t_min_amount = (int)config_get( 'minimum_sponsorship_amount' );
		$t_amount = max( 1, $t_min_amount );
		$t_initial_total = (int)bug_get_field( $t_bug_id, 'sponsorship_total' );

		$t_before_queries = db_count_queries();
		sponsorship_get_all_ids( $t_bug_id );
		$this->assertGreaterThan( $t_before_queries, db_count_queries(), 'Initial sponsorship lookup should hit the database.' );

		$t_sponsorship = new SponsorshipData();
		$t_sponsorship->id = 0;
		$t_sponsorship->bug_id = $t_bug_id;
		$t_sponsorship->user_id = $t_user_id;
		$t_sponsorship->amount = $t_amount;
		$t_sponsorship->logo = '';
		$t_sponsorship->url = '';

		$t_sponsorship_id = sponsorship_set( $t_sponsorship );
		$this->sponsorshipIdsToDelete[] = $t_sponsorship_id;

		$t_ids_after_create = sponsorship_get_all_ids( $t_bug_id );
		$this->assertContains( $t_sponsorship_id, $t_ids_after_create );
		$this->assertSame( $t_initial_total + $t_amount, (int)bug_get_field( $t_bug_id, 'sponsorship_total' ) );

		$t_loaded_sponsorship = sponsorship_get( $t_sponsorship_id );
		$this->assertSame( 0, (int)$t_loaded_sponsorship->paid );

		sponsorship_update_paid( $t_sponsorship_id, 1 );

		$t_after_paid_queries = db_count_queries();
		$this->assertSame( 1, (int)sponsorship_get( $t_sponsorship_id )->paid );
		$this->assertGreaterThan( $t_after_paid_queries, db_count_queries(), 'Updating sponsorship paid state should invalidate the cached sponsorship row.' );

		sponsorship_delete( $t_sponsorship_id );
		$this->sponsorshipIdsToDelete = [];

		$this->assertSame( $t_initial_total, (int)bug_get_field( $t_bug_id, 'sponsorship_total' ) );
		$this->assertNotContains( $t_sponsorship_id, sponsorship_get_all_ids( $t_bug_id ) );
	}
}
