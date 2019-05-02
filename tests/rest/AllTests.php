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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Test config
 */
require_once __DIR__ . '/RestIssueAddTest.php';

/**
 * Soap Test Suite
 * @package    Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class RestAllTests extends PHPUnit\Framework\TestSuite
{
	/**
	 * setUp
	 * @return void
	 */
	protected function setUp() {
	}

	/**
	 * Initializes REST Test Suite
	 * @return RestAllTests
	 */
	public static function suite() {
		$t_suite = new RestAllTests( 'REST API' );

		$t_suite->addTestSuite( 'RestIssueAddTest' );

		return $t_suite;
	}
}
