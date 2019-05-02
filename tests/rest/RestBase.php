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
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

# MantisBT Core API
require_mantis_core();

require_once( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../../core/constant_inc.php' );


/**
 * Base class for REST API test cases
 *
 * @requires extension curl
 * @group REST
 */
class RestBase extends PHPUnit\Framework\TestCase {
	/**
	 * @var string Base path for REST API
	 */
	protected $base_path = '';

	/**
	 * @var string Username
	 */
	protected $userName = 'administrator';

	/**
	 * @var string The API token to use for authentication
	 */
	protected $token = '';

	/**
	 * @var string Email address
	 */
	protected $email = 'root@localhost';

	/**
	 * @var int User ID
	 */
	protected $userId = '1';

	/**
	 * @var int Project ID
	 */
	protected $projectId = 1;

	/**
	 * @var array Array of Issue IDs to delete
	 */
	private $issueIdsToDelete = array();

	/**
	 * setUp
	 * @return void
	 */
	protected function setUp() {
		if( !isset( $GLOBALS['MANTIS_TESTSUITE_REST_ENABLED'] ) ||
			!$GLOBALS['MANTIS_TESTSUITE_REST_ENABLED'] ) {
			$this->markTestSkipped( 'The REST API tests are disabled.' );
		}

		$this->assertTrue( array_key_exists( 'MANTIS_TESTSUITE_REST_HOST', $GLOBALS ) &&
			!empty( $GLOBALS['MANTIS_TESTSUITE_REST_HOST'] ),
			"You must define 'MANTIS_TESTSUITE_REST_HOST' in your bootstrap file" );

		$this->base_path = trim( $GLOBALS['MANTIS_TESTSUITE_REST_HOST'], '/' );

		if( array_key_exists( 'MANTIS_TESTSUITE_USERNAME', $GLOBALS ) ) {
			$this->userName = $GLOBALS['MANTIS_TESTSUITE_USERNAME'];
		} else {
			$this->userName = 'administrator';
		}

		$this->assertTrue( array_key_exists( 'MANTIS_TESTSUITE_API_TOKEN', $GLOBALS ) &&
			!empty( $GLOBALS['MANTIS_TESTSUITE_API_TOKEN'] ),
			"You must define 'MANTIS_TESTSUITE_API_TOKEN' in your bootstrap file" );

		$this->token = $GLOBALS['MANTIS_TESTSUITE_API_TOKEN'];

		if( array_key_exists( 'MANTIS_TESTSUITE_PROJECT_ID', $GLOBALS ) ) {
			$this->projectId = $GLOBALS['MANTIS_TESTSUITE_PROJECT_ID'];
		} else {
			$this->projectId = 1;
		}
	}

	/**
	 * tearDown
	 * @return void
	 */
	protected function tearDown() {
		foreach ( $this->issueIdsToDelete as $t_issue_id_to_delete ) {
			$this->delete( '/issues', 'id=' . $t_issue_id_to_delete );
		}
	}

	protected function delete( $p_relative_path, $p_query_string ) {
		$t_client = new \GuzzleHttp\Client();
		$t_options = array(
			'allow_redirects' => false,
			'http_errors' => false,
			'headers' => array(
				'Authorization' => $this->token,
			),
		);

		return $t_client->request( 'DELETE', $this->base_path . $p_relative_path . '?' . $p_query_string, $t_options );
	}

	/**
	 * @param string $p_relative_path The relative path under `/api/rest/` e.g. `/issues`.
	 * @param mixed $p_payload The payload object, it will be json encoded before sending.
	 * @return mixed|string The response object.
	 */
	protected function post( $p_relative_path, $p_payload ) {
		$t_client = new \GuzzleHttp\Client();
		$t_options = array(
			'allow_redirects' => false,
			'http_errors' => false,
			'json' => $p_payload,
			'headers' => array(
				'Authorization' => $this->token,
			),
		);

		return $t_client->request( 'POST', $this->base_path . $p_relative_path, $t_options );
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
	 * getIssueToAdd
	 * @param string $p_test_case Test case identifier.
	 * @return array
	 */
	protected function getIssueToAdd( $p_test_case ) {
		return array(
				'summary' => $p_test_case . ': test issue: ' . rand( 1, 1000000 ),
				'description' => 'description of test issue.',
				'project' => array( 'id' => $this->getProjectId() ),
				'category' => array( 'name' => $this->getCategory() ) );
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
	 * Utility function to establish DB connection.
	 *
	 * PHPUnit seems to kill the connection after each test case execution;
	 * this allows individual test cases that need the DB to reopen it easily.
	 *
	 * @todo Copied from MantisCoreBase class - see if code duplication can be avoided
	 */
	public static function dbConnect() {
		global $g_hostname, $g_db_username, $g_db_password, $g_database_name,
			   $g_use_persistent_connections;

		db_connect(
			config_get_global( 'dsn', false ),
			$g_hostname,
			$g_db_username,
			$g_db_password,
			$g_database_name,
			$g_use_persistent_connections == ON
		);
	}
}
