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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */


$t_root_path = dirname( __FILE__, 3 ) . DIRECTORY_SEPARATOR;

# MantisBT constants
require_once ( $t_root_path . DIRECTORY_SEPARATOR . 'core/constant_inc.php' );

/**
 * Test cases for SoapEnum class.
 */
class SoapBase extends PHPUnit\Framework\TestCase {
	/**
	 * @var SoapClient Soap Client
	 */
	protected $client;

	/**
	 * @var string Username
	 */
	protected $userName = 'administrator';

	/**
	 * @var string Password
	 */
	protected $password = 'root';

	/**
	 * @var string Email address
	 */
	protected $email = 'root@localhost';

	/**
	 * @var int User ID
	 */
	protected $userId = '1';

	/**
	 * @var string MantisBT Path
	 */
	protected $mantisPath;

	/**
	 * @var int Project ID
	 */
	protected $projectId = 1;

	/**
	 * Maximum number of Issues to retrieve during tests.
	 *
	 * The default value is fine when running tests on a fresh install with an
	 * empty database (like with TravisCI), but when using a persistent database
	 * (e.g. local development) some tests may be skipped or fail if there are
	 * too many Issues (
	 *
	 * @var int $maxIssues
	 */
	protected $maxIssues;

	/**
	 * @var array Array of Issue IDs to delete
	 */
	private   $issueIdsToDelete = array();

	/**
	 * @var array Array of Version IDs to delete
	 */
	private   $versionIdsToDelete = array();

	/**
	 * @var array Array of Tag IDs to delete
	 */
	private   $tagIdsToDelete = array();

	/**
	 * @var array Soap Client Options Array
	 */
	private $defaultSoapClientOptions;

	/**
	 * setUp
	 * @return void
	 */
	protected function setUp(): void {
		if( empty( $GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED'] ) ) {
			$this->markTestSkipped( 'The Soap tests are disabled.' );
		}

		$t_wsdl = $GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'] ?? '';
		$this->assertTrue( !empty( $t_wsdl ),
			"You must define 'MANTIS_TESTSUITE_SOAP_HOST' in your bootstrap file"
		);

		$this->defaultSoapClientOptions = array(
			'trace'      => true,
			'exceptions' => true,
			'cache_wsdl' => WSDL_CACHE_NONE,
		);

		$this->client = new SoapClient( $t_wsdl,
			array_merge( $this->defaultSoapClientOptions, $this->extraSoapClientFlags() )
		);

		$this->mantisPath = substr( $t_wsdl, 0, -strlen( 'api/soap/mantisconnect.php?wsdl' ) );

		$this->userName = $GLOBALS['MANTIS_TESTSUITE_USERNAME'] ?? 'administrator';
		$this->password = $GLOBALS['MANTIS_TESTSUITE_PASSWORD'] ?? 'root';
		$this->email = $GLOBALS['MANTIS_TESTSUITE_EMAIL'] ?? 'root@localhost';
		$this->projectId = $GLOBALS['MANTIS_TESTSUITE_PROJECT_ID'] ?? 1;
		$this->maxIssues = $GLOBALS['MANTIS_TESTSUITE_MAX_ISSUES'] ?? 50;
	}

	/**
	 * Returns an array of extra options to be passed to the Soap Client
	 * @return array an array of extra options to be passed to the SoapClient constructor
	 */
	protected function extraSoapClientFlags() {
		return array();
	}

	/**
	 * tearDown
	 * @return void
	 */
	protected function tearDown(): void {
		foreach ( $this->versionIdsToDelete as $t_version_id_to_delete ) {
			$this->client->mc_project_version_delete( $this->userName, $this->password, $t_version_id_to_delete );
		}

		foreach ( $this->issueIdsToDelete as $t_issue_id_to_delete ) {
			$this->client->mc_issue_delete(
				$this->userName,
				$this->password,
				$t_issue_id_to_delete );
		}

		foreach ( $this->tagIdsToDelete as $t_tag_id_to_delete ) {
			$this->client->mc_tag_delete( $this->userName, $this->password, $t_tag_id_to_delete );
		}
	}

	/**
	 * return integer The default project id.
	 * @return integer
	 */
	protected function getProjectId() {
		return $this->projectId;
	}

	/**
	 * return string The default category.
	 * @return string
	 */
	protected function getCategory() {
		return 'General';
	}

	/**
	 * Skip if time tracking is not enabled
	 * @return void
	 */
	protected function skipIfTimeTrackingIsNotEnabled() {
		$t_time_tracking_enabled = $this->client->mc_config_get_string( $this->userName, $this->password, 'time_tracking_enabled' );
		if( !$t_time_tracking_enabled ) {
			$this->markTestSkipped( 'Time tracking is not enabled' );
		}
	}

	/**
	 * Returns a minimal data structure for tests to create a new Issue.
	 *
	 * The Issue Summary is set to TestClass::TestCase with an optional
	 * suffix, followed by a random number.
	 *
	 * @param string $p_suffix Optional Test case suffix.
	 *
	 * @return array
	 */
	protected function getIssueToAdd( $p_suffix = '' ) {
		$t_summary = static::class . '::' . $this->getName();
		if( $p_suffix ) {
			$t_summary .= '-' . $p_suffix;
		}
		return array(
			'summary' => $t_summary . ': test issue ' . rand( 1, 1000000 ),
			'description' => 'description of test issue.',
			'project' => array( 'id' => $this->getProjectId() ),
			'category' => $this->getCategory()
		);
	}

	/**
	 * Registers an issue for deletion after the test method has run
	 *
	 * @param integer $p_issue_id Issue identifier.
	 * @return void
	 */
	protected function deleteAfterRun( $p_issue_id ) {
		$this->issueIdsToDelete[] = $p_issue_id;
	}

	/**
	 * Registers an version for deletion after the test method has run
	 *
	 * @param integer $p_version_id A version identifier number.
	 * @return void
	 */
	protected function deleteVersionAfterRun( $p_version_id ) {
		$this->versionIdsToDelete[] = $p_version_id;
	}

	/**
	 * Registers a tag for deletion after the test method has run
	 *
	 * @param integer $p_tag_id A tag identifier number.
	 * @return void
	 */
	protected function deleteTagAfterRun ( $p_tag_id ) {
		$this->tagIdsToDelete[] = $p_tag_id;
	}

	/**
	 * Skip if due date thresholds are too high
	 * @return void
	 */
	protected function skipIfDueDateIsNotEnabled() {
		if( $this->client->mc_config_get_string( $this->userName, $this->password, 'due_date_view_threshold' ) > 90  ||
			 $this->client->mc_config_get_string( $this->userName, $this->password, 'due_date_update_threshold' ) > 90 ) {
			 	$this->markTestSkipped( 'Due date thresholds are too high.' );
			 }
	}

	/**
	 * Skip if no category is not on
	 * @return void
	 */
	protected function skipIfAllowNoCategoryIsDisabled() {
		if( $this->client->mc_config_get_string( $this->userName, $this->password, 'allow_no_category' ) != true ) {
			$this->markTestSkipped( 'g_allow_no_category is not ON.' );
		}
	}

	/**
	 * Skip if zlib extension not found
	 * @return void
	 */
	protected function skipIsZlibIsNotAvailable() {
		if( !extension_loaded( 'zlib' ) ) {
			$this->markTestSkipped( 'zlib extension not found.' );
		}
	}

	/**
	 * Converts date to UTC
	 * @param string $p_date A valid date string.
	 * @return DateTime object
	 */
	protected function dateToUTC( $p_date ) {
		$t_conv_date = new DateTime( $p_date );
		return $t_conv_date->setTimeZone( new DateTimeZone( 'UTC' ) );
	}

}
