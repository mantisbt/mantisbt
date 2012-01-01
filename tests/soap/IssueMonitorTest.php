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
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for issue monitoring webservice methods.
 */
class IssueMonitorTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue
	 * 2. validates that the monitor list is empty
	 */
	public function testCreateIssueHasEmptyMonitorList() {

	    $issueToAdd = $this->getIssueToAdd( 'IssueMonitorTest.testCreateIssueHasEmptyMonitorList' );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);

		// no monitors on new issue
		$this->assertEquals(0, sizeof($issue->monitors));
	}
	
	/**
	 * A test case that tests the following
	 * 
	 * 1. Creates a new issue
	 * 2. Adds a monitor to it
	 * 3. Retrieves the issue and verifies that the user is in the monitor list
	 * 4. Removes a monitor from the issue
	 * 5. Retrieves the issue and verifies that the user is not in the monitor list
	 */
	public function testAddMonitorWhenCreatingAnIssue() {

	    $issueToAdd = $this->getIssueToAdd( 'IssueMonitorTest.testAddMonitorWhenCreatingAnIssue' );
	    $issueToAdd['monitors'] = array(
	        array ('id' =>  $this->userId )
        );

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );
		
		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);		
		
		self::assertEquals(1, sizeof($issue->monitors));
		
		$monitor = $issue->monitors[0];
		
		self::assertEquals( $this->userId, $monitor->id );
		self::assertEquals( $this->userName, $monitor->name );
		
	}
	
	/**
	 * A test case that tests the following
	 * 
	 * 1. Creates a new issue
	 * 2. Adds a monitor to it
	 * 3. Retrieves the issue and verifies that the user is in the monitor list
	 */
	public function testAddMonitorToExistingIssue() {
	    
	    $issueToAdd = $this->getIssueToAdd( 'IssueMonitorTest.testAddMonitorToExistingIssue' );

	    $issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );
		
		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);		
		
        $issue->monitors = array(
	        array ('id' =>  $this->userId )
        );
        
        $this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue );
			
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId);
		
		self::assertEquals(1, sizeof($issue->monitors));
		
		$monitor = $issue->monitors[0];
		
		self::assertEquals( $this->userId, $monitor->id );
		self::assertEquals( $this->userName, $monitor->name );
	}
	
	/**
	 * A test case that tests the following
	 * 
	 * 1. Creates a new issue with a monitor
	 * 2. Retrieves the issue
	 * 3. Updates the monitor list to be empty
	 * 4. Retrieves the issue and verifies that the monitors list is empty
	 */
	public function testRemoveMonitor() {
	    
	    $issueToAdd = $this->getIssueToAdd( 'IssueMonitorTest.testAddRemoveMonitorFromIssue' );
	    $issueToAdd['monitors'] = array(
	        array ('id' =>  $this->userId )
        );

	    $issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );
		
		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
		
        $issue->monitors = array();
        
        $this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue );
			
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId);
		
		self::assertEquals(0, sizeof($issue->monitors));
	}
	
	/**
	 * A test case that tests the following
	 * 
	 * 1. Creates a new issue with a monitor
	 * 2. Retrieves the issue
	 * 3. Updates the issue ( no actual changes )
	 * 4. Retrieves the issue and verifies that the monitors list is unchanged
	 */
	public function testUpdateKeepsMonitor() {
	    
	    $issueToAdd = $this->getIssueToAdd( 'IssueMonitorTest.testUpdateKeepsMonitor' );
	    $issueToAdd['monitors'] = array(
	        array ('id' =>  $this->userId )
        );

	    $issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );
		
		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId);
		
        $this->client->mc_issue_update( $this->userName, $this->password, $issueId, $issue );
			
		$issue = $this->client->mc_issue_get( $this->userName, $this->password, $issueId);
		
		self::assertEquals(1, sizeof($issue->monitors));
	}		
}
