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
 * Test fixture for enum related webservice method.
 *
 * @requires extension soap
 * @group SOAP
 */
class EnumTest extends SoapBase {
	/**
	 * Tests mc_enum_access_levels method.
	 *
	 * @return void
	 */
	public function testAccessLevel() {
		$t_access_levels_object_refs = $this->client->mc_enum_access_levels( $this->userName, $this->password );

		$t_accessLevels = EnumTest::ObjectRefsToAssoc( $t_access_levels_object_refs );

		# '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator'

		$this->assertEquals( 6, count( $t_accessLevels ) );
		$this->assertEquals( 'viewer', $t_accessLevels[10] );
		$this->assertEquals( 'reporter', $t_accessLevels[25] );
		$this->assertEquals( 'updater', $t_accessLevels[40] );
		$this->assertEquals( 'developer', $t_accessLevels[55] );
		$this->assertEquals( 'manager', $t_accessLevels[70] );
		$this->assertEquals( 'administrator', $t_accessLevels[90] );
	}

	/**
	 * Tests the mc_enum_access_levels with invalid credentials.
	 *
	 * @return void
	 */
	public function testAccessLevelAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_access_levels( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_status method.
	 *
	 * @return void
	 */
	public function testStatus() {
		$t_statuses_object_refs = $this->client->mc_enum_status( $this->userName, $this->password );

		$t_statuses = EnumTest::ObjectRefsToAssoc( $t_statuses_object_refs );

		# '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'

		$this->assertEquals( 7, count( $t_statuses ) );
		$this->assertEquals( 'new', $t_statuses[10] );
		$this->assertEquals( 'feedback', $t_statuses[20] );
		$this->assertEquals( 'acknowledged', $t_statuses[30] );
		$this->assertEquals( 'confirmed', $t_statuses[40] );
		$this->assertEquals( 'assigned', $t_statuses[50] );
		$this->assertEquals( 'resolved', $t_statuses[80] );
		$this->assertEquals( 'closed', $t_statuses[90] );
	}

	/**
	 * Tests mc_enum_status method with invalid credentials.
	 *
	 * @return void
	 */
	public function testStatusAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_status( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_priorities method.
	 * @return void
	 */
	public function testPriority() {
		$t_prioritiesObjectRefs = $this->client->mc_enum_priorities( $this->userName, $this->password );

		$t_priorities = EnumTest::ObjectRefsToAssoc( $t_prioritiesObjectRefs );

		# '10:none,20:low,30:normal,40:high,50:urgent,60:immediate'

		$this->assertEquals( 6, count( $t_priorities ) );
		$this->assertEquals( 'none', $t_priorities[10] );
		$this->assertEquals( 'low', $t_priorities[20] );
		$this->assertEquals( 'normal', $t_priorities[30] );
		$this->assertEquals( 'high', $t_priorities[40] );
		$this->assertEquals( 'urgent', $t_priorities[50] );
		$this->assertEquals( 'immediate', $t_priorities[60] );
	}

	/**
	 * Tests mc_enum_priorities method with invalid credentials.
	 *
	 * @return void
	 */
	public function testPriorityAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_priorities( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_reproducibilities method.
	 * @return void
	 */
	public function testReproducibility() {
		$t_reproducibility_object_refs = $this->client->mc_enum_reproducibilities( $this->userName, $this->password );

		$t_reproducibilities = EnumTest::ObjectRefsToAssoc( $t_reproducibility_object_refs );

		# '10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A'

		$this->assertEquals( 6, count( $t_reproducibilities ) );
		$this->assertEquals( 'always', $t_reproducibilities[10] );
		$this->assertEquals( 'sometimes', $t_reproducibilities[30] );
		$this->assertEquals( 'random', $t_reproducibilities[50] );
		$this->assertEquals( 'have not tried', $t_reproducibilities[70] );
		$this->assertEquals( 'unable to reproduce', $t_reproducibilities[90] );
		$this->assertEquals( 'N/A', $t_reproducibilities[100] );
	}

	/**
	 * Tests mc_enum_reproducibilities method with invalid credentials.
	 *
	 * @return void
	 */
	public function testReproducibilityAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_reproducibilities( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_severities method.
	 *
	 * @return void
	 */
	public function testSeverity() {
		$t_severity_object_refs = $this->client->mc_enum_severities( $this->userName, $this->password );

		$t_severities = EnumTest::ObjectRefsToAssoc( $t_severity_object_refs );

		# '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block'

		$this->assertEquals( 8, count( $t_severities ) );
		$this->assertEquals( 'feature', $t_severities[10] );
		$this->assertEquals( 'trivial', $t_severities[20] );
		$this->assertEquals( 'text', $t_severities[30] );
		$this->assertEquals( 'tweak', $t_severities[40] );
		$this->assertEquals( 'minor', $t_severities[50] );
		$this->assertEquals( 'major', $t_severities[60] );
		$this->assertEquals( 'crash', $t_severities[70] );
		$this->assertEquals( 'block', $t_severities[80] );
	}

	/**
	 * Tests mc_enum_severities method with invalid credentials.
	 *
	 * @return void
	 */
	public function testSeverityAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_severities( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_projections method.
	 *
	 * @return void
	 */
	public function testProjection() {
		$t_projection_object_refs = $this->client->mc_enum_projections( $this->userName, $this->password );

		$t_projections = EnumTest::ObjectRefsToAssoc( $t_projection_object_refs );

		# '10:none,30:tweak,50:minor fix,70:major rework,90:redesign'

		$this->assertEquals( 5, count( $t_projections ) );
		$this->assertEquals( 'none', $t_projections[10] );
		$this->assertEquals( 'tweak', $t_projections[30] );
		$this->assertEquals( 'minor fix', $t_projections[50] );
		$this->assertEquals( 'major rework', $t_projections[70] );
		$this->assertEquals( 'redesign', $t_projections[90] );
	}

	/**
	 * Tests mc_enum_projections method with invalid credentials.
	 *
	 * @return void
	 */
	public function testProjectionAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_projections( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_etas method.
	 *
	 * @return void
	 */
	public function testEta() {
		$t_eta_object_refs = $this->client->mc_enum_etas( $this->userName, $this->password );

		$t_etas = EnumTest::ObjectRefsToAssoc( $t_eta_object_refs );

		# '10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month'

		$this->assertEquals( 6, count( $t_etas ) );
		$this->assertEquals( 'none', $t_etas[10] );
		$this->assertEquals( '< 1 day', $t_etas[20] );
		$this->assertEquals( '2-3 days', $t_etas[30] );
		$this->assertEquals( '< 1 week', $t_etas[40] );
		$this->assertEquals( '< 1 month', $t_etas[50] );
		$this->assertEquals( '> 1 month', $t_etas[60] );
	}

	/**
	 * Tests mc_enum_etas method with invalid credentials.
	 *
	 * @return void
	 */
	public function testEtaAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_etas( 'administrator', '' );
	}

	/**
	 * Tests mc_enum_resolutions method.
	 *
	 * @return void
	 */
	public function testResolution() {
		$t_resolution_object_refs = $this->client->mc_enum_resolutions( $this->userName, $this->password );

		$t_resolutions = EnumTest::ObjectRefsToAssoc( $t_resolution_object_refs );

		# '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix'

		$this->assertEquals( 9, count( $t_resolutions ) );
		$this->assertEquals( 'open', $t_resolutions[10] );
		$this->assertEquals( 'fixed', $t_resolutions[20] );
		$this->assertEquals( 'reopened', $t_resolutions[30] );
		$this->assertEquals( 'unable to reproduce', $t_resolutions[40] );
		$this->assertEquals( 'not fixable', $t_resolutions[50] );
		$this->assertEquals( 'duplicate', $t_resolutions[60] );
		$this->assertEquals( 'no change required', $t_resolutions[70] );
		$this->assertEquals( 'suspended', $t_resolutions[80] );
		$this->assertEquals( 'won\'t fix', $t_resolutions[90] );
	}

	/**
	 * Tests mc_enum_resolutions method with invalid credentials.
	 *
	 * @return void
	 */
	public function testResolutionAccessDenied() {
		$this->expectException( SoapFault::class );
		$this->client->mc_enum_resolutions( 'administrator', '' );
	}

	/**
	 * Converts an array of ObjectRefs array into an associate
	 * array with the enum id as the key and the enum label as
	 * the value.
	 * @param object $p_object_refs Object Reference.
	 * @return array
	 */
	private static function ObjectRefsToAssoc( $p_object_refs ) {
		$t_assoc_array = array();

		foreach ( $p_object_refs as $t_object_ref ) {
			$t_assoc_array[$t_object_ref->id] = $t_object_ref->name;
		}

		return $t_assoc_array;
	}

	/**
	 * Tests mc_enum_get with severities parameter
	 *
	 * @return void
	 */
	public function testEnumGet() {
		$t_result = $this->client->mc_enum_get( $this->userName, $this->password, 'severity' );

		$this->assertEquals( '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block', $t_result );
	}
}
