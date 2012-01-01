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
 * Test fixture for project webservice methods.
 */
class ProjectTest extends SoapBase {

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

               $projectId = $this->client->mc_project_add(
                       $this->userName,
                       $this->password,
                       $projectDataStructure);

               $this->projectIdToDelete[] = $projectId;

               $projectsArray = $this->client->mc_projects_get_user_accessible(
                       $this->userName,
                       $this->password);

               foreach ( $projectsArray as $project ) {
                       if ( $project->id == $projectId ) {
                               $this->assertEquals($projectName, $project->name);
                       }
               }

               $projectDataStructure['name'] = $projectNewName;

               $return_bool = $this->client->mc_project_update(
                       $this->userName,
                       $this->password,
                       $projectId,
                       $projectDataStructure);

               $projectsArray = $this->client->mc_projects_get_user_accessible(
                       $this->userName,
                       $this->password);

               foreach ( $projectsArray as $project ) {
                       if ( $project->id == $projectId ) {
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

               $projectId = $this->client->mc_project_add(
                       $this->userName,
                       $this->password,
                       $projectDataStructure);

               $this->projectIdToDelete[] = $projectId;
               
               $projectIdFromName = $this->client->mc_project_get_id_from_name(
                       $this->userName,
                       $this->password,
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

               $projectId = $this->client->mc_project_add(
                       $this->userName,
                       $this->password,
                       $projectDataStructure);

               $this->projectIdToDelete[] = $projectId;

               $projectsArray = $this->client->mc_project_get_all_subprojects(
                       $this->userName,
                       $this->password,
                       $projectId);

                $this->assertEquals(0, count($projectsArray));
       }

       private function newProjectAsArray($projectName) {
       	
       	       $projectDataStructure = array();
               $projectDataStructure['name'] = $projectName;
               $projectDataStructure['status'] = "development";
               $projectDataStructure['view_state'] = 10;

               return $projectDataStructure;
       }

       protected function tearDown() {

               parent::tearDown();

               foreach ( $this->projectIdToDelete as $projectId )  {
                       $this->client->mc_project_delete(
                               $this->userName,
                               $this->password,
                               $projectId);
               }
       }

       private function getOriginalNameProject() {
               return 'my_project_name';
       }

       private function getNewNameProject() {
               return 'my_new_project_name';
       }

}
