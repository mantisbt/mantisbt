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
 *
 * Avoid PHPStorm warnings caused by MantisConnect methods not found in SoapClient
 * @noinspection PhpUndefinedMethodInspection
 */

require_once 'SoapBase.php';

/**
 * Test fixture for project webservice methods.
 *
 * @requires extension soap
 * @group SOAP
 */
class ProjectTest extends SoapBase {
	/**
	 * @var array List of project IDs to delete
	 */
	private $projectIdToDelete = array();

	/**
	 * A test case that tests the following:
	 * 1. Create a project.
	 * 2. Rename the project.
	 * @return void
	 */
	public function testAddRenameDeleteProject() {
		$t_project_name = $this->getNewProjectName();
		$t_project_new_name = $t_project_name . '_new';

		$t_project_data_structure = $this->newProjectAsArray( $t_project_name );

		$t_project_id = $this->client->mc_project_add( $this->userName, $this->password, $t_project_data_structure );

		$this->projectIdToDelete[] = $t_project_id;

		$t_projects_array = $this->client->mc_projects_get_user_accessible( $this->userName, $this->password );

		foreach( $t_projects_array as $t_project ) {
			if( $t_project->id == $t_project_id ) {
				$this->assertEquals( $t_project_name, $t_project->name );
			}
		}

		$t_project_data_structure['name'] = $t_project_new_name;

		$this->client->mc_project_update( $this->userName, $this->password,
			$t_project_id,
			$t_project_data_structure
		);

		$t_projects_array = $this->client->mc_projects_get_user_accessible( $this->userName, $this->password );

		foreach( $t_projects_array as $t_project ) {
			if( $t_project->id == $t_project_id ) {
				$this->assertEquals( $t_project_new_name, $t_project->name );
			}
		}
	}

	/**
	 * A test case which does the following
	 *
	 * 1. Create a project
	 * 2. Retrieve the project id by name
	 * @return void
	 */
	public function testGetIdFromName() {
		$t_project_name = $this->getNewProjectName();

		$t_project_data_structure = $this->newProjectAsArray( $t_project_name );

		$t_project_id = $this->client->mc_project_add( $this->userName, $this->password, $t_project_data_structure );

		$this->projectIdToDelete[] = $t_project_id;

		$t_project_idFromName = $this->client->mc_project_get_id_from_name(
			$this->userName, $this->password,
			$t_project_name
		);

		$this->assertEquals( $t_project_idFromName, $t_project_id );
	}

	/**
	 * A test case which does the following
	 *
	 * 1. Create a project
	 * 2. Retrieve the subproject ids. Must return empty array.
	 * @return void
	 */
	public function testGetSubprojects() {
		$t_project_name = $this->getNewProjectName();
		$t_project_data_structure = $this->newProjectAsArray( $t_project_name );

		$t_project_id = $this->client->mc_project_add( $this->userName, $this->password, $t_project_data_structure );

		$this->projectIdToDelete[] = $t_project_id;

		$t_projects_array = $this->client->mc_project_get_all_subprojects( $this->userName, $this->password, $t_project_id );

		$this->assertCount( 0, $t_projects_array );
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
	 * @return void
	 */
	public function testSetProjectPrivateLockout() {
		$t_project_data_structure = $this->newProjectAsArray( $this->getNewProjectName() );

		# step 1
		$t_project_id = $this->client->mc_project_add( $this->userName, $this->password, $t_project_data_structure );
		$this->projectIdToDelete[] = $t_project_id;

		# step 3
		$t_project_data_structure['view_state'] = array( 'id' => VS_PRIVATE );
		$t_update_ok = $this->client->mc_project_update( $this->userName, $this->password,
			$t_project_id,
			$t_project_data_structure
		);
		$this->assertTrue( $t_update_ok, 'Project update failed' );

		# step 4
		$t_project_list = $this->client->mc_projects_get_user_accessible( $this->userName, $this->password );
		$t_found = false;
		foreach( $t_project_list as $t_project ) {
			if( $t_project_id == $t_project->id ) {
				$t_found = true;
				break;
			}
		}
		$this->assertTrue( $t_found, "User '$this->userName' no longer has access to the project" );
	}

	/**
	 * New project Array
	 * @param string $p_project_name Project Name.
	 * @return array
	 */
	private function newProjectAsArray( $p_project_name ) {
		$t_project_data_structure = array();
		$t_project_data_structure['name'] = $p_project_name;
		$t_project_data_structure['status'] = array( 'name' => 'development' );
		$t_project_data_structure['view_state'] = array( 'id' => VS_PUBLIC );

		return $t_project_data_structure;
	}

	/**
	 * Tear Down
	 * @return void
	 */
	protected function tearDown(): void {
		parent::tearDown();

		foreach( $this->projectIdToDelete as $t_project_id )  {
			$this->client->mc_project_delete( $this->userName, $this->password, $t_project_id );
		}
	}

	/**
	 * Helper function to generate a unique project name for a test.
	 *
	 * Project Name will be the Test Case name followed by a random number.
	 *
	 * @return string
	 */
	protected function getNewProjectName() {
		do {
			$t_name = $this->getName() . '_' . rand();
			$t_id = $this->client->mc_project_get_id_from_name( $this->userName, $this->password, $t_name );
		} while( $t_id != 0 );
		return $t_name;
	}
}
