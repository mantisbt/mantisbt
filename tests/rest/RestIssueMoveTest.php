<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MantisBT Tests
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright  Copyright 2026 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://mantisbt.org
 */

namespace Mantis\tests\rest;

/**
 * Tests for moving issues through the REST API.
 *
 * @group REST
 */
class RestIssueMoveTest extends RestBase {
	/** @var int Created issue identifier. */
	private $issue_id;

	/** @var int Created target-project identifier. */
	private $target_project_id;

	/**
	 * Creates a target project and source issue for the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->target_project_id = project_create(
			__CLASS__ . ' ' . rand( 1, 1000000 ),
			'Test project for the issue move REST API.',
			STATUS_RELEASED
		);

		$t_response = $this->builder()->post( '/issues', $this->getIssueToAdd() )->send();
		$t_issue = $this->getJson( $t_response, HTTP_STATUS_CREATED )->issue;
		$this->issue_id = (int)$t_issue->id;
		$this->deleteIssueAfterRun( $this->issue_id );
	}

	/**
	 * Removes the target project and delegates issue cleanup to the base fixture.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		parent::tearDown();

		if( $this->target_project_id && project_exists( $this->target_project_id ) ) {
			project_delete( $this->target_project_id );
		}
	}

	/**
	 * Moves an issue to another project and returns the moved issue.
	 *
	 * @return void
	 */
	public function testMoveIssue(): void {
		$t_response = $this->builder()->post(
			'/issues/' . $this->issue_id . '/move',
			array( 'project' => array( 'id' => $this->target_project_id ) )
		)->send();

		$t_issue = $this->getJson( $t_response, HTTP_STATUS_SUCCESS )->issue;

		$this->assertSame( $this->issue_id, (int)$t_issue->id );
		$this->assertSame( $this->target_project_id, (int)$t_issue->project->id );
	}
}
