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
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for version methods
 */
class VersionTest extends SoapBase {

    private function getTestVersion() {

        return array (
			'project_id' => $this->getProjectId(),
			'name' => '1.0',
			'released' => true,
			'description' => 'Test version',
			'date_order' => '',
            'obsolete' => false
		);
    }

    /**
     * Tests creating a new version
     */
    public function testAddVersion() {
    	
    	$initialVersions  = $this->countVersions();
        
        $versionId = $this->client->mc_project_version_add($this->userName, $this->password, $this->getTestVersion() );
        
        $this->assertNotNull( $versionId );
        
        $this->deleteVersionAfterRun( $versionId );
        
        $versions = $this->client->mc_project_get_versions( $this->userName, $this->password, $this->getProjectId() );
        
        $this->assertEquals(1, count($versions) - $initialVersions);
        
        $version = $versions[0];
        
        $this->assertEquals('1.0', $version->name);
        $this->assertEquals(true, $version->released);
        $this->assertEquals('Test version', $version->description);
        $this->assertEquals($this->getProjectId(), $version->project_id);
        $this->assertNotNull($version->date_order);
        $this->assertEquals(false, $version->obsolete);
    }


    /**
     * Tests updating a version
     */
    public function testUpdateVersion() {
    	
    	$initialVersions  = $this->countVersions();
        
        $versionId = $this->client->mc_project_version_add($this->userName, $this->password, $this->getTestVersion() );
        
        $this->assertNotNull( $versionId );
        
        $this->deleteVersionAfterRun( $versionId );
        
        $updatedVersion = $this->getTestVersion();
        $updatedVersion['name'] = '1.1';
        
        $this->client->mc_project_version_update ( $this->userName, $this->password, $versionId, $updatedVersion );
        
        $versions = $this->client->mc_project_get_versions( $this->userName, $this->password, $this->getProjectId() );
        
        $this->assertEquals(1, count($versions) - $initialVersions);
        
        foreach ( $versions as $version ) {
        	if ( $version->id == $versionId ) { 
        		$this->assertEquals('1.1', $version->name);
         		return;
        	}
        }
        
        self::fail('Did not find version with id ' . $versionId . ' in the reply');
    }
    
    private function countVersions() {
    	
    	return count ( $this->client->mc_project_get_versions( $this->userName, $this->password, $this->getProjectId() ) );
    }
}
