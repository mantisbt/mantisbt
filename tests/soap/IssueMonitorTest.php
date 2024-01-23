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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for issue monitoring webservice methods.
 *
 * @requires extension soap
 * @group SOAP
 */
class IssueMonitorTest extends SoapBase {
	/**
	 * A test case that tests the following:
	 * 1. Creates a new issue
	 * 2. validates that the monitor list is empty
	 * @return void
	 */
	public function testCreateIssueHasEmptyMonitorList() {
	    $t_issue_to_add = $this->getIssueToAdd();

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		# no monitors on new issue
		$this->assertEquals( 0, sizeof( $t_issue->monitors ) );
	}

	/**
	 * A test case that tests the following
	 *
	 * 1. Creates a new issue
	 * 2. Adds a monitor to it
	 * 3. Retrieves the issue and verifies that the user is in the monitor list
	 * 4. Removes a monitor from the issue
	 * 5. Retrieves the issue and verifies that the user is not in the monitor list
	 * @return void
	 */
	public function testAddMonitorWhenCreatingAnIssue() {
	    $t_issue_to_add = $this->getIssueToAdd();
	    $t_issue_to_add['monitors'] = array(
	        array ('id' =>  $this->userId )
		);

		$t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		self::assertEquals( 1, sizeof( $t_issue->monitors ) );

		$t_monitor = $t_issue->monitors[0];

		self::assertEquals( $this->userId, $t_monitor->id );
		self::assertEquals( $this->userName, $t_monitor->name );

	}

	/**
	 * A test case that tests the following
	 *
	 * 1. Creates a new issue
	 * 2. Adds a monitor to it
	 * 3. Retrieves the issue and verifies that the user is in the monitor list
	 * @return void
	 */
	public function testAddMonitorToExistingIssue() {
	    $t_issue_to_add = $this->getIssueToAdd();

	    $t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->monitors = array(
	        array ('id' =>  $this->userId )
		);

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		self::assertEquals( 1, sizeof( $t_issue->monitors ) );

		$t_monitor = $t_issue->monitors[0];

		self::assertEquals( $this->userId, $t_monitor->id );
		self::assertEquals( $this->userName, $t_monitor->name );
	}

	/**
	 * A test case that tests the following
	 *
	 * 1. Creates a new issue with a monitor
	 * 2. Retrieves the issue
	 * 3. Updates the monitor list to be empty
	 * 4. Retrieves the issue and verifies that the monitors list is empty
	 * @return void
	 */
	public function testRemoveMonitor() {
	    $t_issue_to_add = $this->getIssueToAdd();
	    $t_issue_to_add['monitors'] = array(
	        array ('id' =>  $this->userId )
		);

	    $t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_issue->monitors = array();

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		self::assertEquals( 0, sizeof( $t_issue->monitors ) );
	}

	/**
	 * A test case that tests the following
	 *
	 * 1. Creates a new issue with a monitor
	 * 2. Retrieves the issue
	 * 3. Updates the issue ( no actual changes )
	 * 4. Retrieves the issue and verifies that the monitors list is unchanged
	 * @return void
	 */
	public function testUpdateKeepsMonitor() {
	    $t_issue_to_add = $this->getIssueToAdd();
	    $t_issue_to_add['monitors'] = array(
	        array ('id' =>  $this->userId )
		);

	    $t_issue_id = $this->client->mc_issue_add( $this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$this->client->mc_issue_update( $this->userName, $this->password, $t_issue_id, $t_issue );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		self::assertEquals( 1, sizeof( $t_issue->monitors ) );
	}
}
