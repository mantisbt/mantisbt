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
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture which verifies login mechanisms
 */
class LoginTest extends SoapBase {
	
	private $dummyUser = 'no';
	private $dummyPassword = 'user';
	
	public function testGetIssueGetLoginFailed() {
		try {
			$this->client->mc_issue_get( $this->dummyUser , $this->dummyPassword, 1 );
			$this->fail( "Should have failed." );
		} catch ( SoapFault $e) {
			$this->assertIsLoginFailure( $e );
		}
	}
	
	public function testProjectGetUsersLoginFailed() {
		try {
			$this->client->mc_project_get_users( $this->dummyUser , $this->dummyPassword, $this->getProjectId(), 0 );
			$this->fail( "Should have failed." );
		} catch ( SoapFault $e) {
			$this->assertIsLoginFailure( $e );
		}
	}
	
	public function testGetEnumStatusLoginFailed() {
		try {
			$this->client->mc_enum_status( $this->dummyUser , $this->dummyPassword);
			$this->fail( "Should have failed." );
		} catch ( SoapFault $e) {
			$this->assertIsLoginFailure( $e );
		}
	}
	
	public function testProjectGetIssuesLoginFailed() {
		try {
			$this->client->mc_project_get_issues( $this->dummyUser , $this->dummyPassword, $this->getProjectId(), 0, 15 );
			$this->fail( "Should have failed." );
		} catch ( SoapFault $e) {
			$this->assertIsLoginFailure( $e );
		}
	}
	
	public function testFilterGetIssuesLoginFailed() {
		try {
			$this->client->mc_filter_get_issues( $this->dummyUser , $this->dummyPassword, $this->getProjectId(), 1, 0, 15 );
			$this->fail( "Should have failed." );
		} catch ( SoapFault $e) {
			$this->assertIsLoginFailure( $e );
		}
	}
	
	/**
	 * @param $e SoapFault
	 * @return void
	 */
	private function assertIsLoginFailure($e) {
		$this->assertRegexp( '/Access denied/i' , $e->getMessage() );
	}
}
