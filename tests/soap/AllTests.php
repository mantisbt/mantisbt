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
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Test config
 */
require_once dirname( __FILE__ ) . '/../TestConfig.php';

require_once 'EnumTest.php';
require_once 'IssueAddTest.php';
require_once 'IssueMonitorTest.php';
require_once 'IssueNoteTest.php';
require_once 'IssueUpdateTest.php';
require_once 'IssueHistoryTest.php';
require_once 'FilterTest.php';
require_once 'AttachmentTest.php';
require_once 'LoginTest.php';
require_once 'CategoryTest.php';
require_once 'CompressionTest.php';
require_once 'ProjectTest.php';
require_once 'VersionTest.php';
require_once 'RelationshipTest.php';
require_once 'UserTest.php';
require_once 'TagTest.php';
require_once 'MentionTest.php';

/**
 * Soap Test Suite
 * @package    Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class SoapAllTests extends PHPUnit_Framework_TestSuite
{
	/**
	 * setUp
	 * @return void
	 */
	protected function setUp() {
		if( ! extension_loaded( 'soap' ) ) {
			$this->markTestSuiteSkipped( 'The SOAP extension is not available.' );
		}
	}

	/**
	 * Initialises Soap Test Suite
	 * @return SoapAllTests
	 */
	public static function suite() {
		$t_suite = new SoapAllTests( 'SOAP Interface' );

		$t_suite->addTestSuite( 'EnumTest' );
		$t_suite->addTestSuite( 'IssueAddTest' );
		$t_suite->addTestSuite( 'IssueHistoryTest' );
		$t_suite->addTestSuite( 'IssueMonitorTest' );
		$t_suite->addTestSuite( 'IssueNoteTest' );
		$t_suite->addTestSuite( 'IssueUpdateTest' );
		$t_suite->addTestSuite( 'FilterTest' );
		$t_suite->addTestSuite( 'AttachmentTest' );
		$t_suite->addTestSuite( 'LoginTest' );
		$t_suite->addTestSuite( 'CategoryTest' );
		$t_suite->addTestSuite( 'CompressionTest' );
		$t_suite->addTestSuite( 'ProjectTest' );
		$t_suite->addTestSuite( 'VersionTest' );
		$t_suite->addTestSuite( 'RelationshipTest' );
		$t_suite->addTestSuite( 'UserTest' );
		$t_suite->addTestSuite( 'TagTest' );
		$t_suite->addTestSuite( 'MentionTest' );

		return $t_suite;
	}
}
