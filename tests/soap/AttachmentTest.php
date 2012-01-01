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
 * Test fixture for attachment methods
 */
class AttachmentTest extends SoapBase {
	
	
	private $projectAttachmentsToDelete = array();
	
	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Adds at attachemnt
	 * 3. Get the issue.
	 * 4. Verify that the attachment is present in the issue data
	 * 5. Verify that the attachment contents is correct
	 */
	public function testAttachmentIsAdded() {
		$issueToAdd = $this->getIssueToAdd( 'AttachmentTest.testAttachmentIsAdded' );
		
		$attachmentContents = 'Attachment contents.';

		$issueId = $this->client->mc_issue_add(
			$this->userName,
			$this->password,
			$issueToAdd);
			
		$this->deleteAfterRun( $issueId );

		$attachmentId = $this->client->mc_issue_attachment_add(
			$this->userName,
			$this->password,
			$issueId,
			'sample.txt',
			'txt',
			base64_encode( $attachmentContents )
		);
		
		$issue = $this->client->mc_issue_get(
			$this->userName,
			$this->password,
			$issueId
		);

		$attachment = $this->client->mc_issue_attachment_get(
			$this->userName, 
			$this->password, 
			$attachmentId);
		
		$this->assertEquals( 1, count( $issue->attachments ), 'count($issue->attachments)' );
		$this->assertEquals( $attachmentContents, base64_decode( $attachment ), '$attachmentContents' );
		$this->assertEquals( $this->mantisPath.'file_download.php?file_id='.$issue->attachments[0]->id.'&type=bug', $issue->attachments[0]->download_url);
		$this->assertEquals( $this->userId, $issue->attachments[0]->user_id);
	}
	
	
	/**
	 * A test case that tests the following:
	 * 1. Gets a non-existing issue attachment
	 * 2. Verifies that that an error is thrown
	 */
	public function testIssueAttachmentNotFound() {
		
		try {
			$this->client->mc_issue_attachment_get(
				$this->userName, 
				$this->password, 
				-1);
			$this->fail("Should have failed.");
		} catch ( SoapFault $e) {
			$this->assertRegexp('/Unable to find an attachment/', $e->getMessage());
		}
	}	
	
	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Adds at attachemnt
	 * 3. Get the issue.
	 * 4. Verify that the attachment is present in the issue data
	 * 5. Verify that the attachment contents is correct
	 */
	public function testProjectAttachmentIsAdded() {
		$this->skipIfProjectDocumentationIsNotEnabled();
		
		$attachmentContents = 'Attachment contents.';

		$attachmentId = $this->client->mc_project_attachment_add(
			$this->userName,
			$this->password,
			$this->getProjectId(),
			'sample.txt',
			'title',
			'description',
			'txt',
			base64_encode( $attachmentContents )
		);
		
		$this->projectAttachmentsToDelete[] = $attachmentId;

		$attachment = $this->client->mc_project_attachment_get(
			$this->userName, 
			$this->password, 
			$attachmentId);
		
		$this->assertEquals( $attachmentContents, base64_decode( $attachment ), '$attachmentContents' );
		
		$attachments = $this->client->mc_project_get_attachments( $this->userName, $this->password, $this->getProjectId() );
		$this->assertEquals( 1, count( $attachments ) );
		
		$attachment = $attachments[0];
		$this->assertEquals($this->userId, $attachment->user_id);
		$this->assertEquals('description', $attachment->description);
	}

	/**
	 * A test case that tests the following:
	 * 1. Gets a non-existing project attachment
	 * 2. Verifies that an error is thrown
	 */
	public function testProjectAttachmentNotFound() {
		
		$this->skipIfProjectDocumentationIsNotEnabled();
		
		try {
			$this->client->mc_project_attachment_get(
				$this->userName, 
				$this->password, 
				-1);
			$this->fail("Should have failed.");
		} catch ( SoapFault $e) {
			$this->assertRegexp('/Unable to find an attachment/', $e->getMessage());
		}
	}
	
	private function skipIfProjectDocumentationIsNotEnabled() {
		
		$configEnabled = $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_project_documentation' );
		
		if ( ! $configEnabled  ) {
			$this->markTestSkipped('Project documentation is not enabled.');
		}
	}
		
	protected function tearDown() {
		SoapBase::tearDown();
		
		foreach ( $this->projectAttachmentsToDelete as $projectAttachmentId ) {
			$this->client->mc_project_attachment_delete(
				$this->userName,
				$this->password,
				$projectAttachmentId);
		}
	}
	
	
}
