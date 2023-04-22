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
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'RestBase.php';

/**
 * Test fixture for project related APIs.
 *
 * @requires extension curl
 * @group REST
 */
class RestProjectVersionTests extends RestBase {
	/**
	 * A prefix for project version APIs for the test project.
	 * @var integer
	 */
	private $ver_base_url;

	/**
	 * Setup test fixture
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->ver_base_url = '/projects/' . $this->getProjectId() . '/versions/';
	}

	/**
	 * Test add a version by name
	 */
	public function testProjectAddVersionWithName() {
		$this->createVersion();
	}

	/**
	 * Test add a version by name and desc
	 */
	public function testProjectAddVersionWithNameAndDesc() {
		$t_version_to_create = array(
			'name' => $this->versionName(),
			'description' => 'Test version description',
		);

		$t_response = $this->builder()->post( $this->ver_base_url, $t_version_to_create )->send();
		$this->assertEquals( 201, $t_response->getStatusCode() );
		$t_version = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_version['version'] ) );
		$t_version = $t_version['version'];
		$this->assertTrue( isset( $t_version['id'] ) );
		$this->deleteAfterRunVersion( $t_version['id'] );

		$this->assertEquals( $t_version_to_create['name'], $t_version['name'] );
		$this->assertTrue( isset( $t_version['description'] ) );
		$this->assertEquals( $t_version_to_create['description'], $t_version['description'] );
	}

	public function testProjectGetVersion() {
		$t_version = $this->createVersion();

		$t_response = $this->builder()->get( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		$t_result = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_result['versions'] ) );
		$this->assertEquals( 1, count( $t_result['versions'] ) );
		$t_returned_version = $t_result['versions'][0];

		$this->assertEquals( $t_version['id'], $t_returned_version['id'] );
		$this->assertEquals( $t_version['name'], $t_returned_version['name'] );
	}

	public function testProjectGetVersions() {
		$this->createVersion();

		$t_response = $this->builder()->get( $this->ver_base_url )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		$t_result = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_result['versions'] ) );
		$this->assertGreaterThanOrEqual( 1, count( $t_result['versions'] ) );
	}

	public function testProjectGetVersionsForNonExistentProject() {
		$t_response = $this->builder()->get( '/projects/1000000/versions' )->send();
		$this->assertEquals( 404, $t_response->getStatusCode() );
	}

	public function testProjectGetVersionForNonExistentVersion() {
		$t_response = $this->builder()->get( $this->ver_base_url . '1000000' )->send();
		$this->assertEquals( 404, $t_response->getStatusCode() );
	}

	public function testProjectAddVersionForNonExistentProject() {
		$t_version_to_create = array( 'name' => $this->versionName() );
		$t_response = $this->builder()->post( '/projects/1000000/versions', $t_version_to_create )->send();
		$this->assertEquals( 404, $t_response->getStatusCode() );
	}

	public function testProjectDeleteVersion() {
		$t_version = $this->createVersion();

		// Confirm that version exists
		$t_response = $this->builder()->get( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );

		// Delete a version that exists
		$t_response = $this->builder()->delete( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 204, $t_response->getStatusCode() );

		// Confirm version is deleted
		$t_response = $this->builder()->get( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 404, $t_response->getStatusCode() );

		// Delete a version that doesn't exists
		$t_response = $this->builder()->delete( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 204, $t_response->getStatusCode() );
	}

	public function testProjectVersionAnonymous() {
		$t_version = $this->createVersion();

		// anonymous users can't update a version
		$t_version_patch = array( 'name' => 'should fail' );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->anonymous()->send();
		$this->assertEquals( 401, $t_response->getStatusCode() );

		// anonymous users can't delete a version
		$t_response = $this->builder()->delete( $this->ver_base_url . $t_version['id'] )->anonymous()->send();
		$this->assertEquals( 401, $t_response->getStatusCode() );

		// Confirm that version exists
		$t_response = $this->builder()->get( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );		
	}

	/**
	 * Test adding a project version with invalid names.
	 *
	 * @dataProvider providerVersionInvalidNames
	 * @return void
	 */
	public function testProjectAddVersionWithInvalidName( $p_name ) {
		$t_version = $this->createVersion();

		$t_version_patch = array( 'name' => $p_name );

		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	/**
	 * Test updating a project version that doesn't exist.
	 *
	 * @return void
	 */
	public function testProjectUpdateDoesNotExists() {
		$t_version_patch = array( 'description' => 'whatever' );
		$t_response = $this->builder()->patch( $this->ver_base_url . '1000000', $t_version_patch )->send();
		$this->assertEquals( 404, $t_response->getStatusCode() );
	}

	/**
	 * Test updating a project version with invalid names.
	 *
	 * @dataProvider providerVersionInvalidNames
	 * @return void
	 */
	public function testProjectUpdateVersionWithInvalidName( $p_name ) {
		$t_version = $this->createVersion();
		$t_version_patch = array( 'name' => $p_name );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 400, $t_response->getStatusCode() );
	}

	/**
	 * Test update project version name with different case
	 *
	 * @return void
	 */
	public function testProjectUpdateVersionWithDifferentCase() {
		$t_version = $this->createVersion();
		$t_version_patch = array( 'name' => strtoupper( $t_version['name'] ) );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
	}

	/**
	 * Test update project version name with different case
	 *
	 * @return void
	 */
	public function testProjectUpdateVersion() {
		$t_version = $this->createVersion();

		$t_version_patch = array( 'description' => 'test description' );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		$t_version_result = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_version_result['versions'] ) );
		$t_version_result = $t_version_result['versions'][0];
		$this->assertEquals( $t_version['name'], $t_version_result['name'] );
		$this->assertEquals( $t_version_patch['description'], $t_version_result['description'] );

		$t_version_patch = array( 'obsolete' => true );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		$t_version_result = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_version_result['version'] ) );
		$t_version_result = $t_version_result['version'];
		$this->assertTrue( $t_version_result['obsolete'] );
		$this->assertFalse( $t_version_result['released'] );

		$t_version_patch = array( 'released' => true );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		$t_version_result = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_version_result['version'] ) );
		$t_version_result = $t_version_result['version'];
		$this->assertTrue( $t_version_result['obsolete'] );
		$this->assertTrue( $t_version_result['released'] );

		$t_version_patch = array( 'timestamp' => time() );
		$t_response = $this->builder()->patch( $this->ver_base_url . $t_version['id'], $t_version_patch )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		$t_version_result = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_version_result['version'] ) );
		$t_version_result = $t_version_result['version'];
		$this->assertTrue( $t_version_result['obsolete'] );
		$this->assertTrue( $t_version_result['released'] );
	}

	public function providerVersionInvalidNames() {
		return array(
			'empty' =>array( '' ),
			'blank' => array( '   ' ),
			'newline' => array( "version\nwith\nnewlines" ),
			'newline2' => array( "version\rwith\rnewlines" ),
			'newline_blank' => array( "\n\r   " ),
			'tabs' => array( "\t   " ),
			'too_long' => array( str_repeat( "v", 65 ) )
		);
	}

	private function versionName() {
		return 'Test Version ' . rand( 1, 1000000 );
	}

	private function createVersion() {
		$t_version_to_create = array( 'name' => $this->versionName() );

		$t_response = $this->builder()->post( $this->ver_base_url, $t_version_to_create )->send();
		$this->assertEquals( 201, $t_response->getStatusCode() );
		$t_version = json_decode( $t_response->getBody(), true );
		$this->assertTrue( isset( $t_version['version'] ) );
		$t_version = $t_version['version'];

		$this->assertTrue( isset( $t_version['id'] ) );
		$this->deleteAfterRunVersion( $t_version['id'] );

		$this->assertEquals( $t_version_to_create['name'], $t_version['name'] );
		$this->assertTrue( isset( $t_version['released'] ) );
		$this->assertFalse( $t_version['released'] );
		$this->assertTrue( isset( $t_version['obsolete'] ) );
		$this->assertFalse( $t_version['obsolete'] );
		$this->assertFalse( isset( $t_version['description'] ) );
		$this->assertTrue( isset( $t_version['timestamp'] ) );

		// Confirm version is created
		$t_response = $this->builder()->get( $this->ver_base_url . $t_version['id'] )->send();
		$this->assertEquals( 200, $t_response->getStatusCode() );
		
		return $t_version;
	}
}
