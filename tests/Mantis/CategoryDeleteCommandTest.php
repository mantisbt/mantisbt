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
 * Tests for CategoryDeleteCommand.
 *
 * @package Tests
 * @subpackage MantisCoreTests
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\ClientException;

/**
 * Test fixture for project category deletion.
 */
class CategoryDeleteCommandTest extends MantisCoreBase {
	/**
	 * Test that the configured default category cannot be deleted.
	 *
	 * @return void
	 * @throws ClientException
	 */
	public function testCannotDeleteDefaultCategory() {
		self::login();
		$t_category_id = category_add( 1, 'command default category ' . rand( 1, 1000000 ) );
		$t_had_database_value = config_is_set_in_database( 'default_category_for_moves', ALL_USERS, ALL_PROJECTS );
		$t_old_value = config_get( 'default_category_for_moves', null, ALL_USERS, ALL_PROJECTS );
		config_set( 'default_category_for_moves', $t_category_id, ALL_USERS, ALL_PROJECTS );

		try {
			$t_data = [ 'query' => [
				'project_id' => 1,
				'category_id' => $t_category_id,
			] ];
			$t_command = new \CategoryDeleteCommand( $t_data );
			$this->expectException( ClientException::class );
			$this->expectExceptionCode( ERROR_CATEGORY_CANNOT_UPDATE_DEFAULT );
			$t_command->execute();

			$this->assertTrue( category_exists( $t_category_id ) );
		} finally {
			if( $t_had_database_value ) {
				config_set( 'default_category_for_moves', $t_old_value, ALL_USERS, ALL_PROJECTS );
			} else {
				config_delete( 'default_category_for_moves', ALL_USERS, ALL_PROJECTS );
			}
			category_remove( $t_category_id );
		}
	}
}
