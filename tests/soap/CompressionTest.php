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
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright (C) 2010-2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for calls with compression enabled
 */
class CompressionTest extends SoapBase {

	/**
	 * A test case that tests the following:
	 * 
	 * <ol>
	 *   <li>Creating an issue.</li>
	 *   <li>Retrieving an issue.</li>
	 * </ol>
	 * 
	 * <p>If any of the calls performed with compression enabled will
	 * fail, the test will fail in turn with a SoapFault.</p>
	 */
	public function testGetIssueWithCompressionEnabled() {
		$issueToAdd = $this->getIssueToAdd( 'CompressionTest.testUpdateSummary' );
		
		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$createdIssue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
	}
	
    protected function extraSoapClientFlags() {
    	
    	return array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
    }
}
