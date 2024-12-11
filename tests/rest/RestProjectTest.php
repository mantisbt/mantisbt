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

/**
 * Mantis Webservice Tests
 *
 * @package   Tests
 * @copyright Copyright 2023 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link      https://mantisbt.org
 */

require_once 'RestBase.php';

/**
 * Test fixture for project APIs.
 *
 * @TODO Add test cases for unprivileged user access
 *
 * @group REST
 */
class RestProjectTest extends RestBase
{
	/**
	 * @var string API Endpoint, should start with a '/'.
	 */
	protected $endpoint;

	/** @var int[] List of project Ids to delete after test run */
	protected $projectIdsToDelete = [];

	/**
	 * Project REST API endpoint with optional Project Id.
	 *
	 * For use as relative path with RequestBuilder methods.
	 *
	 * @param int|null $p_project_id
	 *
	 * @return string
	 */
	protected function getEndpoint( ?int $p_project_id = null ): string {
		return $this->endpoint . $p_project_id;
	}

	/**
	 * Generate project data.
	 *
	 * @return array
	 */
	protected function generateProjectData() {
		return [
			'name' => 'PHPUnit Test Project ' . rand( 1, 1000000 ),
			'status' => ['name' => 'development'],
			'description' => 'Project created by PHPUnit RestProjectTest',
			'enabled' => true,
			'file_path' => '',
			'view_state' => ['name' => 'public'],
		];
	}

	/**
	 * Creates a test project (including assertion).
	 *
	 * @return stdClass Created project data
	 */
	protected function createProject() {
		$t_data = $this->generateProjectData();
		$t_response = $this->builder()->post( $this->endpoint, $t_data )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_body = json_decode( $t_response->getBody() );
		return $t_body->project;
	}

	/**
	 * Registers a Project for deletion after the test has run.
	 *
	 * @param integer $p_project_id Issue identifier.
	 * @return void
	 */
	protected function deleteProjectAfterRun( $p_project_id ) {
		$this->projectIdsToDelete[] = $p_project_id;
	}

	/**
	 * Setup test fixture
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->endpoint = '/projects/';
	}

	/**
	 * Testing Project Get operations.
	 *
	 * @return void
	 */
	public function testGetProject() {
		# Get the default project
		$t_response = $this->builder()->get( $this->getEndpoint( $this->getProjectId() ) )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Project not found"
		);

		# Get all projects
		$t_project = $this->createProject();
		$this->deleteProjectAfterRun( $t_project->id );
		$t_response = $this->builder()->get( $this->getEndpoint() )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Failed to retrieve all projects"
		);
		$t_projects = json_decode( $t_response->getBody() )->projects;
		$this->assertGreaterThanOrEqual(2, count( $t_projects ),
			"Expected to retrieve at least 2 projects"
		);

		# Non-existing project
		$t_project_id = 99999;
		while( project_exists( $t_project_id ) ) {
			$t_project_id++;
		}
		$t_response = $this->builder()->get( $this->getEndpoint( $t_project_id ) )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Found a non-existing project !"
		);
	}

	/**
	 * Testing Project Creation and Deletion.
	 *
	 * @return void
	 */
	public function testCreateDeleteProject() {
		$t_project = $this->createProject();
		$t_project_id = $t_project->id;

		# Project with same name
		$t_response = $this->builder()->post( $this->endpoint, (array)$t_project )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Project with same name already exists"
		);

		# Project without name
		$t_response = $this->builder()->post( $this->endpoint, [] )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Project created without required field 'name'"
		);

		# Delete project
		$t_response = $this->builder()->delete( $this->getEndpoint( $t_project_id ) )->send();
		$this->assertEquals( HTTP_STATUS_NO_CONTENT, $t_response->getStatusCode(),
			"Project was not deleted"
		);

		# Try to delete it again (i.e. non-existing project)
		$t_response = $this->builder()->delete( $this->getEndpoint( $t_project_id ) )->send();
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Deleting non-existing project should fail"
		);
	}

	/**
	 * Testing Project Update operations.
	 *
	 * @return void
	 */
	public function testUpdateProject() {
		$t_project = $this->createProject();
		$this->deleteProjectAfterRun( $t_project->id );

		# Updating description
		$t_data = [
			'description' => "Updated project description " . rand( 1, 100000 )
		];
		$t_response = $this->builder()->patch( $this->getEndpoint( $t_project->id ), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode(),
			"Failed updating project"
		);

		# Changing project ID
		$t_data = ['id' => $t_project->id + 1];
		$t_response = $this->builder()->patch( $this->getEndpoint( $t_project->id ), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Changing project Id should not be allowed"
		);

		# Empty project name
		$t_data = ['name' => ''];
		$t_response = $this->builder()->patch( $this->getEndpoint( $t_project->id ), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Empty project name should not be allowed"
		);

		# Existing project name
		$t_response = $this->builder()->get( $this->getEndpoint( $this->getProjectId() ) )->send();
		$t_existing_project = json_decode( $t_response->getBody() )->projects[0];
		$t_data = ['name' => $t_existing_project->name];
		$t_response = $this->builder()->patch( $this->getEndpoint( $t_project->id ), $t_data )->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Using same name as existing project should not be allowed"
		);
	}

	public function tearDown(): void {
		parent::tearDown();

		foreach( $this->projectIdsToDelete as $t_id ) {
			$this->builder()->delete( $this->getEndpoint( $t_id ) )->send();
		}
	}

}
