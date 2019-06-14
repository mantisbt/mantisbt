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
 * Mantis Unit Tests
 * @package Tests
 * MantisBT Core Unit Tests
 * @subpackage Plugin
 * @copyright Copyright 2017  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once 'MantisCoreBase.php';

/**
 * Helper API tests
 * @package Tests
 * @subpackage String
 */
class MantisPluginTest extends MantisCoreBase {

	const MANTISCORE = 'MantisCore';

	/**
	 * References of version requirements to test against MantisCore versions.
	 */
	const REQ_13 = '1.3';
	const REQ_130 = '1.3.0';
	const REQ_131 = '1.3.1';
	const REQ_13_20 = '1.3, <2.0';
	const REQ_13_21 = '1.3, <2.1';
	const REQ_13_30 = '1.3, <3.0';
	const REQ_20 = '2.0';
	const REQ_21 = '2.1';
	const REQ_210 = '2.1.0';
	const REQ_210_DEV = '2.1.0-dev';
	const REQ_210_A1 = '2.1.0-alpha.1';
	const REQ_210_B1 = '2.1.0-beta.1';
	const REQ_211 = '2.1.1';

	/**
	 * Tests setup.
	 */
	public function setUp() {
		plugin_register(self::MANTISCORE);
	}


	/**
	 * Tests plugin_dependency() for MantisCore.
	 *
	 * @param string $p_core_version MantisCore version to test against
	 * @param string $p_requirement  Version requirement string
	 * @param bool   $p_expected     True if dependency OK (function returned 1)
	 *
	 * @dataProvider providerDependency
	 */
	public function testPluginDependency( $p_core_version, $p_requirement, $p_expected ) {
		global $g_plugin_cache;

		# Set MantisCore version for the test
		$g_plugin_cache[self::MANTISCORE]->version = $p_core_version;

		$t_result = plugin_dependency(
			self::MANTISCORE,
			$p_requirement
		);

		$this->assertEquals(
			$p_expected,
			$t_result == 1,
			"MantisCore '$p_core_version' should "
			. ( $p_expected ? 'meet' : 'have failed' )
			. " requirement '$p_requirement'"
		);
	}

	/**
	 * Provides a series of dependency test cases.
	 *
	 * Test case structure:
	 *   array( <core version>, <requirement>, <result> )
	 *
	 * plugin_dependency() is called to check the given <core version> against
	 * <requirement>. <result> indicates whether we expect the test to
	 * pass (i.e. function returns 1) or fail (returns value is 0 or -1).
	 *
	 * @return array List of test cases
	 */
	public function providerDependency() {
		return array(
			array( '1.3.0', self::REQ_13, true ),
			array( '1.3.0', self::REQ_130, true ),
			array( '1.3.0', self::REQ_131, false ),
			array( '1.3.0', self::REQ_13_20, true ),
			array( '1.3.0', self::REQ_13_30, true ),
			array( '1.3.0', self::REQ_20, false ),

			array( '2.0.0', self::REQ_13, false ),
			array( '2.0.0', self::REQ_13_20, false ),
			array( '2.0.0', self::REQ_13_21, true ),
			array( '2.0.0', self::REQ_13_30, true ),
			array( '2.0.0', self::REQ_20, true ),
			array( '2.0.0', self::REQ_210_DEV, false ),
			array( '2.0.0-beta.1', self::REQ_20, true ),

			array( '2.1.0', self::REQ_13, false ),
			array( '2.1.0', self::REQ_13_20, false ),
			array( '2.1.0', self::REQ_13_21, false ),
			array( '2.1.0', self::REQ_13_30, true ),
			array( '2.1.0', self::REQ_20, true ),
			array( '2.1.0', self::REQ_21, true ),
			array( '2.1.0', self::REQ_210_DEV, true ),
			array( '2.1.0', self::REQ_210_B1, true ),
			array( '2.1.0', self::REQ_211, false ),
			array( '2.1.0-dev', self::REQ_21, true ),
			array( '2.1.0-dev', self::REQ_210, false ),
			array( '2.1.0-dev', self::REQ_210_DEV, true ),
			array( '2.1.0-dev', self::REQ_210_A1, false ),
			array( '2.1.0-alpha.1', self::REQ_21, true ),
			array( '2.1.0-alpha.1', self::REQ_210_DEV, true ),

			array( '2.1.1', self::REQ_20, true ),
			array( '2.1.1', self::REQ_21, true ),
			array( '2.1.1', self::REQ_211, true ),

			array( '3.0.0', self::REQ_13_20, false ),
			array( '3.0.0', self::REQ_13_30, false ),
			array( '3.0.0', self::REQ_20, false ),
		);
	}

}
