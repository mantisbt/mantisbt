<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 or later.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests for project category commands.
 *
 * @package Tests
 * @subpackage MantisCoreTests
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\ClientException;

/**
 * Test fixture for category command validation.
 */
class CategoryCommandTest extends MantisCoreBase {
	/**
	 * Test that a disabled category handler is not auto-assigned to new issues.
	 *
	 * @return void
	 */
	public function testDisabledCategoryHandlerIsNotAutoAssigned() {
		self::login();
		$t_handle_bug_threshold = config_get( 'handle_bug_threshold', null, null, 1 );
		$t_username = 'category_disabled_handler_' . rand( 1, 1000000 );
		user_create( $t_username, 'password', $t_username . '@localhost.localdomain', $t_handle_bug_threshold, false, true );
		$t_user_id = user_get_id_by_name( $t_username );
		$t_category_id = category_add( 1, 'disabled handler category ' . rand( 1, 1000000 ) );
		category_update( $t_category_id, category_get_name( $t_category_id ), $t_user_id );
		user_set_field( $t_user_id, 'enabled', false );

		try {
			$t_issue_id = $this->createIssueWithCategory( $t_category_id );
			$this->assertEquals( NO_USER, bug_get_field( $t_issue_id, 'handler_id' ) );
		} finally {
			if( isset( $t_issue_id ) ) {
				bug_delete( $t_issue_id );
			}
			category_remove( $t_category_id );
			user_delete( $t_user_id );
		}
	}

	/**
	 * Test that a category handler without project access is not auto-assigned.
	 *
	 * @return void
	 */
	public function testCategoryHandlerWithoutAccessIsNotAutoAssigned() {
		self::login();
		$t_handle_bug_threshold = config_get( 'handle_bug_threshold', null, null, 1 );
		if( $t_handle_bug_threshold <= ANYBODY ) {
			$this->markTestSkipped( 'The handler threshold cannot be lowered in this test configuration.' );
		}

		$t_username = 'category_inaccessible_handler_' . rand( 1, 1000000 );
		user_create( $t_username, 'password', $t_username . '@localhost.localdomain', $t_handle_bug_threshold, false, true );
		$t_user_id = user_get_id_by_name( $t_username );
		$t_category_id = category_add( 1, 'inaccessible handler category ' . rand( 1, 1000000 ) );
		category_update( $t_category_id, category_get_name( $t_category_id ), $t_user_id );
		user_set_field( $t_user_id, 'access_level', $t_handle_bug_threshold - 1 );

		try {
			$t_issue_id = $this->createIssueWithCategory( $t_category_id );
			$this->assertEquals( NO_USER, bug_get_field( $t_issue_id, 'handler_id' ) );
		} finally {
			if( isset( $t_issue_id ) ) {
				bug_delete( $t_issue_id );
			}
			category_remove( $t_category_id );
			user_delete( $t_user_id );
		}
	}

	/**
	 * Test that updating other fields does not revalidate an unchanged handler.
	 *
	 * @return void
	 */
	public function testUpdateAllowsUnchangedInvalidHandler() {
		self::login();
		$t_handle_bug_threshold = config_get( 'handle_bug_threshold', null, null, 1 );
		$t_username = 'category_stale_handler_' . rand( 1, 1000000 );
		user_create( $t_username, 'password', $t_username . '@localhost.localdomain', $t_handle_bug_threshold, false, true );
		$t_user_id = user_get_id_by_name( $t_username );
		$t_disabled_category_id = category_add( 1, 'disabled stale category ' . rand( 1, 1000000 ) );
		category_update( $t_disabled_category_id, category_get_name( $t_disabled_category_id ), $t_user_id );
		category_cache_flush( 1 );
		user_set_field( $t_user_id, 'enabled', false );

		try {
			$t_data = [
				'query' => [ 'project_id' => 1, 'category_id' => $t_disabled_category_id ],
				'payload' => [
					'name' => 'disabled stale category updated',
					'assigned_to' => $t_user_id,
				],
			];
			$t_command = new \CategoryUpdateCommand( $t_data );
			$t_result = $t_command->execute();
			$this->assertEquals( 'disabled stale category updated', $t_result['category']['name'] );
		} finally {
			category_remove( $t_disabled_category_id );
			user_delete( $t_user_id );
		}

		$t_deleted_username = 'category_deleted_handler_' . rand( 1, 1000000 );
		user_create( $t_deleted_username, 'password', $t_deleted_username . '@localhost.localdomain', $t_handle_bug_threshold, false, true );
		$t_deleted_user_id = user_get_id_by_name( $t_deleted_username );
		$t_deleted_category_id = category_add( 1, 'deleted stale category ' . rand( 1, 1000000 ) );
		category_update( $t_deleted_category_id, category_get_name( $t_deleted_category_id ), $t_deleted_user_id );
		category_cache_flush( 1 );
		user_delete( $t_deleted_user_id );

		try {
			$t_data = [
				'query' => [ 'project_id' => 1, 'category_id' => $t_deleted_category_id ],
				'payload' => [
					'name' => 'deleted stale category updated',
					'assigned_to' => $t_deleted_user_id,
				],
			];
			$t_command = new \CategoryUpdateCommand( $t_data );
			$t_result = $t_command->execute();
			$t_category = $t_result['category'];
			$this->assertEquals( 'deleted stale category updated', $t_category['name'] );
			$this->assertEquals( $t_deleted_user_id, $t_category['default_handler']['id'] );
		} finally {
			category_remove( $t_deleted_category_id );
		}
	}

	/**
	 * Create an issue with no explicit handler in the specified category.
	 *
	 * @param int $p_category_id Category identifier.
	 *
	 * @return int Issue identifier.
	 */
	private function createIssueWithCategory( $p_category_id ) {
		$t_issue = new \BugData();
		$t_issue->project_id = 1;
		$t_issue->reporter_id = auth_get_current_user_id();
		$t_issue->summary = __CLASS__ . ': test issue ' . rand( 1, 1000000 );
		$t_issue->description = 'Test issue for category handler assignment.';
		$t_issue->category_id = $p_category_id;

		return $t_issue->create();
	}
	/**
	 * Test that a category can be added to the global project.
	 *
	 * @return void
	 */
	public function testCanAddGlobalCategory() {
		self::login();
		$t_data = [
			'query' => [ 'project_id' => ALL_PROJECTS ],
			'payload' => [ 'name' => 'command global category ' . rand( 1, 1000000 ) ],
		];
		$t_command = new \CategoryAddCommand( $t_data );
		$t_result = $t_command->execute();
		$t_category = $t_result['category'];

		try {
			$this->assertEquals( ALL_PROJECTS, $t_category['project']['id'] );
		} finally {
			category_remove( $t_category['id'] );
		}
	}

	/**
	 * Test that disabled users cannot be assigned when adding or updating.
	 *
	 * @return void
	 */
	public function testAssignedUserMustBeEnabled() {
		self::login();
		$t_username = 'category_disabled_' . rand( 1, 1000000 );
		user_create( $t_username, 'password', $t_username . '@localhost.localdomain', REPORTER, false, false );
		$t_user_id = user_get_id_by_name( $t_username );
		$t_category_id = category_add( 1, 'command handler category ' . rand( 1, 1000000 ) );

		try {
			$t_data = [
				'query' => [ 'project_id' => 1 ],
				'payload' => [
					'name' => 'command disabled handler ' . rand( 1, 1000000 ),
					'assigned_to' => $t_user_id,
				],
			];
			$t_command = new \CategoryAddCommand( $t_data );
			$this->assertAssignedUserRejected( $t_command );

			$t_data = [
				'query' => [ 'project_id' => 1, 'category_id' => $t_category_id ],
				'payload' => [ 'assigned_to' => $t_user_id ],
			];
			$t_command = new \CategoryUpdateCommand( $t_data );
			$this->assertAssignedUserRejected( $t_command );
		} finally {
			category_remove( $t_category_id );
			user_delete( $t_user_id );
		}
	}

	/**
	 * Assert that a command rejects an assigned user.
	 *
	 * @param \Command $p_command Category command to execute.
	 *
	 * @return void
	 */
	private function assertAssignedUserRejected( \Command $p_command ) {
		try {
			$p_command->execute();
			$this->fail( 'A disabled user must not be assigned to a category.' );
		} catch( ClientException $e ) {
			$this->assertEquals( ERROR_ACCESS_DENIED, $e->getCode() );
		}
	}
}
