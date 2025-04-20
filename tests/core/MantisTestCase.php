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
 * MantisBT Tests
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright  Copyright 2025 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://mantisbt.org
 */

namespace Mantis\tests\core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version as PHPUnitVersion;
use ReflectionClass;

/**
 * Base class for the MantisBT test suites.
 */
abstract class MantisTestCase extends TestCase {

	/**
	 * Test case name to use as data.
	 *
	 * This allows identifying which test case created data, which is useful when
	 * troubleshooting failing tests.
	 *
	 * This is similar to {@see TestCase::toString()}, which was used previously
	 * but caused problem as the generated string is sometimes too big for the
	 * table columns where it should be stored.
	 *
	 * Note that {@see TestCase::getName()}/{@see TestCase::name()} are PHPUnit
	 * internal methods which are not covered by the backwards-compatibility
	 * promise, so this could break in the future.
	 *
	 * @return string TestClass::testMethod
	 *
	 * @noinspection PhpUndefinedMethodInspection
	 */
	function getTestName(): string {
		$t_class_name = (new ReflectionClass($this))->getShortName();

		# getName() method was renamed in PHPUnit 10.2 (commit d0dbaafb)
		if( version_compare( PHPUnitVersion::id(), '10.2', '<' )) {
			$t_test_method = $this->getName();
		} else {
			$t_test_method = $this->name();
		}

		return $t_class_name . '::' . $t_test_method;
	}

	/**
	 * Sets the given configuration and returns its old value.
	 *
	 * @param string $p_config Configuration option name.
	 * @param mixed  $p_value  Configuration option value.
	 *
	 * @return mixed The config's old value, false if it was not set in the database.
	 */
	public function setConfig( string $p_config, $p_value ) {
		$t_old = config_is_set_in_database( $p_config ) ? config_get( $p_config ) : false;
		config_set( $p_config, $p_value );

		return $t_old;
	}

	/**
	 * Restores a configuration to its initial state.
	 *
	 * @param string $p_config Configuration option name.
	 * @param mixed  $p_value  Configuration option value. If false, the config
	 *                         will be deleted.
	 */
	public function restoreConfig( string $p_config, $p_value ) {
		if( $p_value === false ) {
			config_delete( $p_config );
		} else {
			config_set( $p_config, $p_value );
		}
	}

}
