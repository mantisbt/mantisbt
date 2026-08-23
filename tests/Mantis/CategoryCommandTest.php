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
			$this->assertAssignedUserRejected( new \CategoryAddCommand( array(
				'query' => array( 'project_id' => 1 ),
				'payload' => array(
					'name' => 'command disabled handler ' . rand( 1, 1000000 ),
					'assigned_to' => $t_user_id,
				),
			) ) );
			$this->assertAssignedUserRejected( new \CategoryUpdateCommand( array(
				'query' => array( 'project_id' => 1, 'category_id' => $t_category_id ),
				'payload' => array( 'assigned_to' => $t_user_id ),
			) ) );
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
