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
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright (C) 2010-2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for category webservice methods.
 */
class CategoryTest extends SoapBase {

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
	 */
	public function testAddRenameDeleteCategory() {
		$projectId = $this->getProjectId();
		$categoryName = $this->getOriginalNameCategory();
		$categoryNewName = $this->getNewNameCategory();

		$categoryId = $this->client->mc_project_add_category(
			$this->userName,
			$this->password,
			$projectId,
			$categoryName);

		$this->categoryNamesToDelete[] = $categoryName;
			
		$categoryList = $this->client->mc_project_get_categories(
			$this->userName,
			$this->password,
			$projectId);

		$this->assertContains($categoryName, $categoryList);

		$return_bool = $this->client->mc_project_rename_category_by_name (
			$this->userName,
			$this->password,
			$projectId,
			$categoryName,
			$categoryNewName,
			'');

		$this->categoryNamesToDelete = array( $categoryNewName );

		$categoryList = $this->client->mc_project_get_categories(
			$this->userName,
			$this->password,
			$projectId);

		$this->assertNotContains($categoryName, $categoryList);
		$this->assertContains($categoryNewName, $categoryList);

		$return_bool = $this->client->mc_project_delete_category (
			$this->userName,
			$this->password,
			$projectId,
			$categoryNewName);

		$this->categoryNamesToDelete = array() ;

		$categoryList = $this->client->mc_project_get_categories(
			$this->userName,
			$this->password,
			$projectId);

		$this->assertNotContains($categoryNewName, $categoryList);
	}

	protected function tearDown() {

		parent::tearDown();

		foreach ( $this->categoryNamesToDelete as $categoryName )  {
			$this->client->mc_project_delete_category(
				$this->userName,
				$this->password,
				$this->getProjectId(),
				$categoryName);
		}
	}
    
	private function getOriginalNameCategory() {
 		return 'my_category_name';
	}
    
	private function getNewNameCategory() {
 		return 'my_new_category_name';
	}
 
}
