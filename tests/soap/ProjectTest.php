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
 * Test fixture for project webservice methods.
 */
class ProjectTest extends SoapBase {
	/**
	 * Array of project ID's to delete
	 */
	private $projectIdToDelete = array();

	/**
	 * A test case that tests the following:
	 * 1. Create a project.
	 * 2. Rename the project.
	 */
	public function testAddRenameDeleteProject() {
		$projectName = $this->getOriginalNameProject();
		$projectNewName = $this->getNewNameProject();

		$projectDataStructure = $this->newProjectAsArray($projectName);

		$projectId = $this->client->mc_project_add( $this->userName, $this->password, $projectDataStructure);

		$this->projectIdToDelete[] = $projectId;

		$projectsArray = $this->client->mc_projects_get_user_accessible( $this->userName, $this->password);

		foreach( $projectsArray as $project ) {
			if( $project->id == $projectId ) {
				$this->assertEquals($projectName, $project->name);
			}
		}

		$projectDataStructure['name'] = $projectNewName;

		$return_bool = $this->client->mc_project_update( $this->userName, $this->password, $projectId,
														$projectDataStructure);

		$projectsArray = $this->client->mc_projects_get_user_accessible( $this->userName, $this->password);

		foreach( $projectsArray as $project ) {
			if( $project->id == $projectId ) {
				$this->assertEquals($projectNewName, $project->name);
			}
		}
	}

	/**
	* A test case which does the following
	*
	* 1. Create a project
	* 2. Retrieve the project id by name
	*
	*/
	public function testGetIdFromName() {
		$projectName = 'TestProjectForIdFromName';

		$projectDataStructure = $this->newProjectAsArray($projectName);

		$projectId = $this->client->mc_project_add( $this->userName, $this->password, $projectDataStructure);

		$this->projectIdToDelete[] = $projectId;

		$projectIdFromName = $this->client->mc_project_get_id_from_name( $this->userName, $this->password,
																		$projectName);

		$this->assertEquals($projectIdFromName, $projectId);
	}

	/**
	* A test case which does the following
	*
	* 1. Create a project
	* 2. Retrieve the subproject ids. Must returns empty array.
	*
	*/
	public function testGetSubprojects() {
		$projectName = $this->getOriginalNameProject();
		$projectDataStructure = $this->newProjectAsArray($projectName);

		$projectId = $this->client->mc_project_add( $this->userName, $this->password, $projectDataStructure);

		$this->projectIdToDelete[] = $projectId;

		$projectsArray = $this->client->mc_project_get_all_subprojects( $this->userName, $this->password, $projectId);

		$this->assertEquals(0, count($projectsArray));
	}

	/**
	* A test case which validates that managers do not lock themselves out when
	* making a project Private.
	*
	* 1. Create a project
	* 2. Create a test manager user (currently not implemented, see below)
	* 3. Set project view state to private
	* 4. Ensure user can still access the project
	*
	* @TODO for this to be a truly useful test case, the project update should
	* actually be performed by a user with MANAGER role. However since the SOAP
	* API does not provide user administration functions, it is currently not
	* possible to properly implement this test.
	*/
	public function testSetProjectPrivateLockout() {
		$projectDataStructure = $this->newProjectAsArray( $this->getName() . "_" . rand() );

		# step 1
		$projectId = $this->client->mc_project_add( $this->userName, $this->password, $projectDataStructure );
		$this->projectIdToDelete[] = $projectId;

		# step 3
		$projectDataStructure['view_state'] = array( 'id' => VS_PRIVATE );
		$updateOk = $this->client->mc_project_update( $this->userName, $this->password, $projectId, $projectDataStructure );
		$this->assertTrue( $updateOk, "Project update failed");

		# step 4
		$projList = $this->client->mc_projects_get_user_accessible( $this->userName, $this->password );
		$found = false;
		foreach( $projList as $proj ) {
			if( $projectId == $proj->id ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, "User '$this->userName' no longer has access to the project" );
	}

	/**
	 * New project Array
	 */
	private function newProjectAsArray($projectName) {
		$projectDataStructure = array();
		$projectDataStructure['name'] = $projectName;
		$projectDataStructure['status'] = array( 'name' => 'development' );
		$projectDataStructure['view_state'] = array( 'id' => VS_PUBLIC );

		return $projectDataStructure;
	}

	/**
	 * Tear Down
	 */
	protected function tearDown() {
		parent::tearDown();

		foreach( $this->projectIdToDelete as $projectId )  {
			$this->client->mc_project_delete( $this->userName, $this->password, $projectId);
		}
	}

	/**
	 * Return old project name
	 */
	private function getOriginalNameProject() {
		return 'my_project_name';
	}

	/**
	 * Return new project name
	 */
	private function getNewNameProject() {
		return 'my_new_project_name';
	}
}
