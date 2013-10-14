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


$t_root_path = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR;

/**
 * MantisBT constants
 */
require_once ( $t_root_path . DIRECTORY_SEPARATOR . 'core/constant_inc.php' );

/**
 * Test cases for SoapEnum class.
 */
class SoapBase extends PHPUnit_Framework_TestCase {
	/**
	 * Soap Client
	 */
	protected $client;

	/**
	 * Username
	 */
	protected $userName = 'administrator';

	/**
	 * Password
	 */
	protected $password = 'root';

	/**
	 * Email address
	 */
	protected $email = 'root@localhost';

	/**
	 * User ID
	 */
	protected $userId = '1';

	/**
	 * MantisBT Path
	 */
	protected $mantisPath;

	/**
	 * Array of Issue IDs to delete
	 */
	private   $issueIdsToDelete = array();

	/**
	 * Array of Version IDs to delete
	 */
	private   $versionIdsToDelete = array();

	/**
	 * Array of Tag IDs to delete
	 */
	private   $tagIdsToDelete = array();

	/**
	 * Soal Client Options Array
	 */
	private   $defaultSoapClientOptions = array(  'trace'      => true,
								                  'exceptions' => true,
								        		  'cache_wsdl' => WSDL_CACHE_NONE,
								        		  'trace'      => true
								               );

	/**
	 * setUp
	 */
    protected function setUp()
    {
    	if (!isset($GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED']) ||
			!$GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED']) {
			$this->markTestSkipped( 'The Soap tests are disabled.' );
		}

		$this->client = new
		    SoapClient(
		       $GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'],
		       	array_merge($this->defaultSoapClientOptions, $this->extraSoapClientFlags()
				)
		    );

	    $this->mantisPath = substr($GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'], 0, -strlen('api/soap/mantisconnect.php?wsdl'));
    }

    /**
     * @return an array of extra options to be passed to the SoapClient constructor
     */
    protected function extraSoapClientFlags() {

    	return array();
    }

	/**
	 * tearDown
	 */
    protected function tearDown() {

    	foreach ( $this->versionIdsToDelete as $versionIdToDelete ) {
    		$this->client->mc_project_version_delete($this->userName, $this->password, $versionIdToDelete);
    	}

    	foreach ( $this->issueIdsToDelete as $issueIdToDelete ) {
    		$this->client->mc_issue_delete(
    			$this->userName,
    			$this->password,
    			$issueIdToDelete);
    	}

    	foreach ( $this->tagIdsToDelete as $tagIdToDelete ) {
    		$this->client->mc_tag_delete ( $this->userName, $this->password, $tagIdToDelete );
    	}
    }

	/**
	 * return default project id
	 */
    protected function getProjectId() {
    	return 1;
    }

	/**
	 * return default category
	 */
    protected function getCategory() {
 		return 'General';
    }

	/**
	 * Skip if time tracking is not enabled
	 */
    protected function skipIfTimeTrackingIsNotEnabled() {

    	$timeTrackingEnabled = $this->client->mc_config_get_string($this->userName, $this->password, 'time_tracking_enabled');
		if ( !$timeTrackingEnabled ) {
			$this->markTestSkipped('Time tracking is not enabled');
		}
    }

	/**
	 * getIssueToAdd
	 */
	protected function getIssueToAdd( $testCase ) {
		return array(
				'summary' => $testCase . ': test issue: ' . rand(1, 1000000),
				'description' => 'description of test issue.',
				'project' => array( 'id' => $this->getProjectId() ),
				'category' => $this->getCategory() );
	}

	/**
	 * Registers an issue for deletion after the test method has run
	 *
	 * @param int $issueId
	 * @return void
	 */
	protected function deleteAfterRun( $issueId ) {

		$this->issueIdsToDelete[] = $issueId;
	}

	/**
	 * Registers an version for deletion after the test method has run
	 *
	 * @param int $versionId
	 * @return void
	 */
	protected function deleteVersionAfterRun( $versionId ) {

		$this->versionIdsToDelete[] = $versionId;
	}

	/**
	 * Registers a tag for deletion after the test method has run
	 *
	 * @param int $tagId
	 * @return void
	 */
	protected function deleteTagAfterRun ( $tagId ) {

		$this->tagIdsToDelete[] = $tagId;
	}

	/**
	 * Skip if due date thesholds are too high
	 */
	protected function skipIfDueDateIsNotEnabled() {

		if ( $this->client->mc_config_get_string( $this->userName, $this->password, 'due_date_view_threshold' ) > 90  ||
			 $this->client->mc_config_get_string( $this->userName, $this->password, 'due_date_update_threshold' ) > 90 ) {
			 	$this->markTestSkipped('Due date thresholds are too high.');
			 }
	}

	/**
	 * Skip if no category is not on
	 */
	protected function skipIfAllowNoCategoryIsDisabled() {
		if ( $this->client->mc_config_get_string($this->userName, $this->password, 'allow_no_category' ) != true ) {
			$this->markTestSkipped( 'g_allow_no_category is not ON.' );
		}
	}

	/**
	 * Skip if zlib extension not found
	 */
	protected function skipIsZlibIsNotAvailable() {
		if( !extension_loaded( 'zlib' ) ) {
			$this->markTestSkipped('zlib extension not found.');
		}
	}

	/**
	 * Converts date to UTC
	 * @param $p_date date string
	 * @return DateTime object
	 * Tests creating a new version
	 */
	protected function dateToUTC($p_date) {
		$convDate = new DateTime($p_date);
		return $convDate->setTimeZone(new DateTimeZone('UTC'));
	}

}
