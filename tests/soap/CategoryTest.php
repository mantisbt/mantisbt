<?php
# MantisBT - a php based bugtracking system

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
 * @copyright Copyright (C) 2010-2013 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for category webservice methods.
 *
 * @requires extension soap
 * @group SOAP
 */
class CategoryTest extends SoapBase {
	/**
	 * @var array Category names to delete at end of test run
	 */
	private $categoryNamesToDelete = array();

	/**
	 * A test case that tests the following:
	 * 1. Get the project id.
	 * 2. Add a category.
	 * 3. Get the category list.
	 * 4. Verify the category name in the list.
	 * 5. Rename the category.
	 * 6. Get the new category list
	 * 7. Verify the cateogory name in the list
	 * 8. Delete the category
	 * 9. Verify the category is not in the list anymore
	 * @return void
	 */
	public function testAddRenameDeleteCategory() {
		$t_project_id = $this->getProjectId();
		$t_category_name = $this->getOriginalNameCategory();
		$t_category_new_name = $this->getNewNameCategory();

		$t_category_id = $this->client->mc_project_add_category(
			$this->userName,
			$this->password,
			$t_project_id,
			$t_category_name );

		$this->categoryNamesToDelete[] = $t_category_name;

		$t_category_list = $this->client->mc_project_get_categories(
			$this->userName,
			$this->password,
			$t_project_id );

		$this->assertContains( $t_category_name, $t_category_list );

		$t_return_bool = $this->client->mc_project_rename_category_by_name(
			$this->userName,
			$this->password,
			$t_project_id,
			$t_category_name,
			$t_category_new_name,
			'' );

		$this->categoryNamesToDelete = array( $t_category_new_name );

		$t_category_list = $this->client->mc_project_get_categories(
			$this->userName,
			$this->password,
			$t_project_id );

		$this->assertNotContains( $t_category_name, $t_category_list );
		$this->assertContains( $t_category_new_name, $t_category_list );

		$t_return_bool = $this->client->mc_project_delete_category(
			$this->userName,
			$this->password,
			$t_project_id,
			$t_category_new_name );

		$this->categoryNamesToDelete = array();

		$t_category_list = $this->client->mc_project_get_categories(
			$this->userName,
			$this->password,
			$t_project_id );

		$this->assertNotContains( $t_category_new_name, $t_category_list );
	}

	/**
	 * Tear Down: Remove categories created by tests
	 * @return void
	 */
	protected function tearDown(): void {
		parent::tearDown();

		foreach( $this->categoryNamesToDelete as $t_category_name )  {
			$this->client->mc_project_delete_category(
				$this->userName,
				$this->password,
				$this->getProjectId(),
				$t_category_name );
		}
	}

	/**
	 * Get an Original Category name for use in a test
	 * @return string
	 */
	private function getOriginalNameCategory() {
		return 'soaptest_' . date( 'Ymd_His' );
	}

	/**
	 * Get a Renamed Category name for use in a test
	 * @return string
	 */
	private function getNewNameCategory() {
		return 'soaptest_renamed_' . date( 'Ymd_His' );
	}

}
