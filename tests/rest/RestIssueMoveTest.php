<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.

namespace Mantis\tests\rest;

/**
 * Tests for moving issues through the REST API.
 *
 * @group REST
 */
class RestIssueMoveTest extends RestBase {
	/** @var int */
	private $issue_id;

	/** @var int */
	private $target_project_id;

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
