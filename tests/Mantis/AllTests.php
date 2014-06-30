<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MantisBT Core Unit Tests
 * @package    Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */


/**
 * Includes
 */
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

require_once 'EnumTest.php';
require_once 'HelperTest.php';
require_once 'StringTest.php';

/**
 * All Test Cases
 * @package    Tests
 * @subpackage UnitTests
 */
class Mantis_AllTests extends PHPUnit_Framework_TestSuite {
	/**
	 * Defines test suite
	 * @return Mantis_AllTests
	 */
	public static function suite() {
		$t_suite = new Mantis_AllTests( 'Main Code' );

		$t_suite->addTestSuite( 'MantisEnumTest' );
		$t_suite->addTestSuite( 'Mantis_HelperTest' );
		$t_suite->addTestSuite( 'Mantis_StringTest' );

		return $t_suite;
	}
}
