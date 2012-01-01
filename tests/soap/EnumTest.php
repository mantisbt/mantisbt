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
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for enum related webservice method.
 */
class EnumTest extends SoapBase {
	/**
	 * Tests mc_enum_access_levels method.
	 */
	public function testAccessLevel() {
		$accessLevelsObjectRefs = $this->client->mc_enum_access_levels( $this->userName, $this->password);
		
		$accessLevels = EnumTest::ObjectRefsToAssoc( $accessLevelsObjectRefs );

		// '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator'

		$this->assertEquals( 6, count( $accessLevels ) );
		$this->assertEquals( 'viewer', $accessLevels[10] );
		$this->assertEquals( 'reporter', $accessLevels[25] );
		$this->assertEquals( 'updater', $accessLevels[40] );
		$this->assertEquals( 'developer', $accessLevels[55] );
		$this->assertEquals( 'manager', $accessLevels[70] );
		$this->assertEquals( 'administrator', $accessLevels[90] );
	}

	/**
	 * Tests the mc_enum_access_levels with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testAccessLevelAccessDenied() {
		$this->client->mc_enum_access_levels( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_status method.
	 */
	public function testStatus() {
		$statusesObjectRefs = $this->client->mc_enum_status($this->userName, $this->password);
		
		$statuses = EnumTest::ObjectRefsToAssoc( $statusesObjectRefs );

		// '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'

		$this->assertEquals( 7, count( $statuses ) );
		$this->assertEquals( 'new', $statuses[10] );
		$this->assertEquals( 'feedback', $statuses[20] );
		$this->assertEquals( 'acknowledged', $statuses[30] );
		$this->assertEquals( 'confirmed', $statuses[40] );
		$this->assertEquals( 'assigned', $statuses[50] );
		$this->assertEquals( 'resolved', $statuses[80] );
		$this->assertEquals( 'closed', $statuses[90] );
	}

	/**
	 * Tests mc_enum_status method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testStatusAccessDenied() {
		$this->client->mc_enum_status( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_priorities method.
	 */
	public function testPriority() {
		$prioritiesObjectRefs = $this->client->mc_enum_priorities($this->userName, $this->password);
		
		$priorities = EnumTest::ObjectRefsToAssoc( $prioritiesObjectRefs );

		// '10:none,20:low,30:normal,40:high,50:urgent,60:immediate'

		$this->assertEquals( 6, count( $priorities ) );
		$this->assertEquals( 'none', $priorities[10] );
		$this->assertEquals( 'low', $priorities[20] );
		$this->assertEquals( 'normal', $priorities[30] );
		$this->assertEquals( 'high', $priorities[40] );
		$this->assertEquals( 'urgent', $priorities[50] );
		$this->assertEquals( 'immediate', $priorities[60] );
	}

	/**
	 * Tests mc_enum_priorities method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testPriorityAccessDenied() {
		$this->client->mc_enum_priorities( 'administrator', '' );
	}
	
	/**
	 * Tests mc_enum_reproducibilities method.
	 */
	public function testReproducibility() {
		$reproducibilityObjectRefs = $this->client->mc_enum_reproducibilities($this->userName, $this->password);
		
		$reproducibilities = EnumTest::ObjectRefsToAssoc( $reproducibilityObjectRefs );

		// '10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A'

		$this->assertEquals( 6, count( $reproducibilities ) );
		$this->assertEquals( 'always', $reproducibilities[10] );
		$this->assertEquals( 'sometimes', $reproducibilities[30] );
		$this->assertEquals( 'random', $reproducibilities[50] );
		$this->assertEquals( 'have not tried', $reproducibilities[70] );
		$this->assertEquals( 'unable to reproduce', $reproducibilities[90] );
		$this->assertEquals( 'N/A', $reproducibilities[100] );
	}

	/**
	 * Tests mc_enum_reproducibilities method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testReproducibilityAccessDenied() {
		$this->client->mc_enum_reproducibilities( 'administrator', '' );
	}
	
	/**
	 * Tests mc_enum_severities method.
	 */
	public function testSeverity() {
		$severityObjectRefs = $this->client->mc_enum_severities($this->userName, $this->password);
		
		$severities = EnumTest::ObjectRefsToAssoc( $severityObjectRefs );

		// '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block'

		$this->assertEquals( 8, count( $severities ) );
		$this->assertEquals( 'feature', $severities[10] );
		$this->assertEquals( 'trivial', $severities[20] );
		$this->assertEquals( 'text', $severities[30] );
		$this->assertEquals( 'tweak', $severities[40] );
		$this->assertEquals( 'minor', $severities[50] );
		$this->assertEquals( 'major', $severities[60] );
		$this->assertEquals( 'crash', $severities[70] );
		$this->assertEquals( 'block', $severities[80] );
	}

	/**
	 * Tests mc_enum_severities method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testSeverityAccessDenied() {
		$this->client->mc_enum_severities( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_projections method.
	 */
	public function testProjection() {
		$projectionObjectRefs = $this->client->mc_enum_projections($this->userName, $this->password);
		
		$projections = EnumTest::ObjectRefsToAssoc( $projectionObjectRefs );

		// '10:none,30:tweak,50:minor fix,70:major rework,90:redesign'

		$this->assertEquals( 5, count( $projections ) );
		$this->assertEquals( 'none', $projections[10] );
		$this->assertEquals( 'tweak', $projections[30] );
		$this->assertEquals( 'minor fix', $projections[50] );
		$this->assertEquals( 'major rework', $projections[70] );
		$this->assertEquals( 'redesign', $projections[90] );
	}

	/**
	 * Tests mc_enum_projections method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testProjectionAccessDenied() {
		$this->client->mc_enum_projections( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_etas method.
	 */
	public function testEta() {
		$etaObjectRefs = $this->client->mc_enum_etas($this->userName, $this->password);

		$etas = EnumTest::ObjectRefsToAssoc( $etaObjectRefs );

		// '10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month'

		$this->assertEquals( 6, count( $etas ) );
		$this->assertEquals( 'none', $etas[10] );
		$this->assertEquals( '< 1 day', $etas[20] );
		$this->assertEquals( '2-3 days', $etas[30] );
		$this->assertEquals( '< 1 week', $etas[40] );
		$this->assertEquals( '< 1 month', $etas[50] );
		$this->assertEquals( '> 1 month', $etas[60] );
	}

	/**
	 * Tests mc_enum_etas method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testEtaAccessDenied() {
		$this->client->mc_enum_etas( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_resolutions method.
	 */ 
	public function testResolution() {
		$resolutionObjectRefs = $this->client->mc_enum_resolutions($this->userName, $this->password);
		
		$resolutions = EnumTest::ObjectRefsToAssoc( $resolutionObjectRefs );

		// '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix'

		$this->assertEquals( 9, count( $resolutions ) );
		$this->assertEquals( 'open', $resolutions[10] );
		$this->assertEquals( 'fixed', $resolutions[20] );
		$this->assertEquals( 'reopened', $resolutions[30] );
		$this->assertEquals( 'unable to reproduce', $resolutions[40] );
		$this->assertEquals( 'not fixable', $resolutions[50] );
		$this->assertEquals( 'duplicate', $resolutions[60] );
		$this->assertEquals( 'no change required', $resolutions[70] );
		$this->assertEquals( 'suspended', $resolutions[80] );
		$this->assertEquals( 'won\'t fix', $resolutions[90] );
	}

	/**
	 * Tests mc_enum_resolutions method with invalid credentials.
	 * 
	 * @expectedException SoapFault
	 */
	public function testResolutionAccessDenied() {
		$this->client->mc_enum_resolutions( 'administrator', '' );
	}

	// TODO: mc_enum_project_status
	// TODO: mc_enum_project_view_states
	// TODO: mc_enum_custom_field_types

	/**
	 * Converts an array of ObjectRefs array into an associate
	 * array with the enum id as the key and the enum label as
	 * the value.
	 */
	private static function ObjectRefsToAssoc( $objectRefs ) {
		$assocArray = array();
		
		foreach ( $objectRefs as $objectRef ) {
			$assocArray[$objectRef->id] = $objectRef->name;
		}
		
		return $assocArray;
	}
	
	/**
	 * Tests mc_enum_get with severities parameter
	 */
	public function testEnumGet() {
		
		$result = $this->client->mc_enum_get($this->userName, $this->password, 'severity');
		
		$this->assertEquals( '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block', $result);
	}
}
