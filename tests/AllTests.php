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
 * Testing Framework for MantisBT
 *
 * Configuration by users should be performed in bootstrap.php
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'TestConfig.php';

require_once 'Mantis/AllTests.php';
require_once 'soap/AllTests.php';
require_once 'rest/AllTests.php';

/**
 * All tests
 */
class AllTests
{
	/**
	 * Test suite
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite() {
		$t_suite = new PHPUnit_Framework_TestSuite( 'Mantis Bug Tracker' );

		$t_suite->addTest( MantisAllTests::suite() );
		$t_suite->addTest( SoapAllTests::suite() );
		$t_suite->addTest( RestAllTests::suite() );

		return $t_suite;
	}
}
