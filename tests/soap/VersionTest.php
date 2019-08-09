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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for version methods.
 *
 * @requires extension soap
 * @group SOAP
 */
class VersionTest extends SoapBase {

	/**
	 * @var string $date_order Version date order
	 */
	protected $date_order;

	/**
	 * VersionTest constructor.
	 */
	public function __construct() {
		$this->date_order = date( 'c' );
	}

	/**
	 * Test Version
	 * @return array
	 */
	private function getTestVersion() {
		return array (
			'project_id' => $this->getProjectId(),
			'name' => '1.0',
			'released' => true,
			'description' => 'Test version',
			'obsolete' => false,
			'date_order'=> $this->date_order,
		);
	}

	/**
	 * Tests creating a new version
	 * @return void
	 */
	public function testAddVersion() {
		$t_initial_versions = $this->countVersions();

		$t_version_id = $this->client->mc_project_version_add( $this->userName, $this->password, $this->getTestVersion() );

		$this->assertNotNull( $t_version_id );

		$this->deleteVersionAfterRun( $t_version_id );

		$t_versions = $this->client->mc_project_get_versions( $this->userName, $this->password, $this->getProjectId() );

		$this->assertEquals( 1, count( $t_versions ) - $t_initial_versions );

		$t_version = $t_versions[0];
		$t_version_date = $this->dateToUTC( $t_version->date_order );

		$this->assertEquals( '1.0', $t_version->name );
		$this->assertEquals( true, $t_version->released );
		$this->assertEquals( 'Test version', $t_version->description );
		$this->assertEquals( $this->getProjectId(), $t_version->project_id );
		$this->assertEquals( $this->dateToUTC( $this->date_order ), $t_version_date );
		$this->assertEquals( false, $t_version->obsolete );
	}


	/**
	 * Tests updating a version
	 * @return void
	 */
	public function testUpdateVersion() {
		$t_initial_versions = $this->countVersions();

		$t_version_id = $this->client->mc_project_version_add( $this->userName, $this->password, $this->getTestVersion() );

		$this->assertNotNull( $t_version_id );

		$this->deleteVersionAfterRun( $t_version_id );

		$t_updated_version = $this->getTestVersion();
		$t_updated_version['name'] = '1.1';

		$this->client->mc_project_version_update( $this->userName, $this->password, $t_version_id, $t_updated_version );

		$t_versions = $this->client->mc_project_get_versions( $this->userName, $this->password, $this->getProjectId() );

		$this->assertEquals( 1, count( $t_versions ) - $t_initial_versions );

		foreach ( $t_versions as $t_version ) {
			if( $t_version->id == $t_version_id ) {
				$t_version_date = $this->dateToUTC( $t_version->date_order );

				$this->assertEquals( '1.1', $t_version->name );
				$this->assertEquals( $this->dateToUTC( $this->date_order ), $t_version_date );
				return;
			}
		}

		self::fail( 'Did not find version with id ' . $t_version_id . ' in the reply' );
	}

	/**
	 * Return Number of versions
	 * @return integer
	 */
	private function countVersions() {
		return count( $this->client->mc_project_get_versions( $this->userName, $this->password, $this->getProjectId() ) );
	}
}
