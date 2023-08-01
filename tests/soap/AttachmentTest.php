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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for attachment methods
 *
 * @requires extension soap
 * @group SOAP
 */
class AttachmentTest extends SoapBase {

	/**
	 * @var array project attachments to delete after tests
	 */
	private $projectAttachmentsToDelete = array();

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Adds at attachemnt
	 * 3. Get the issue.
	 * 4. Verify that the attachment is present in the issue data
	 * 5. Verify that the attachment contents is correct
	 * @return void
	 */
	public function testAttachmentIsAdded() {
		$t_issue_to_add = $this->getIssueToAdd();

		$t_attachment_contents = 'Attachment contents.';

		$t_issue_id = $this->client->mc_issue_add(	$this->userName, $this->password, $t_issue_to_add );

		$this->deleteAfterRun( $t_issue_id );

		$t_attachment_id = $this->client->mc_issue_attachment_add(
			$this->userName,
			$this->password,
			$t_issue_id,
			'sample.txt',
			'txt',
			base64_encode( $t_attachment_contents ) );

		$t_issue = $this->client->mc_issue_get( $this->userName, $this->password, $t_issue_id );

		$t_attachment = $this->client->mc_issue_attachment_get( $this->userName, $this->password, $t_attachment_id );

		$this->assertEquals( 1, count( $t_issue->attachments ), 'count($t_issue->attachments)' );
		$this->assertEquals( $t_attachment_contents, base64_decode( $t_attachment ), '$t_attachment_contents' );
		$this->assertEquals( $this->mantisPath . 'file_download.php?file_id=' . $t_issue->attachments[0]->id . '&type=bug',
			html_entity_decode( $t_issue->attachments[0]->download_url ) );
		$this->assertEquals( $this->userId, $t_issue->attachments[0]->user_id );
	}


	/**
	 * A test case that tests the following:
	 * 1. Gets a non-existing issue attachment
	 * 2. Verifies that that an error is thrown
	 * @return void
	 */
	public function testIssueAttachmentNotFound() {
		try {
			$this->client->mc_issue_attachment_get(
				$this->userName,
				$this->password,
				-1 );
			$this->fail( 'Should have failed.' );
		} catch ( SoapFault $e ) {
			$this->assertRegexp( '/Unable to find an attachment/', $e->getMessage() );
		}
	}

	/**
	 * A test case that tests the following:
	 * 1. Create an issue.
	 * 2. Adds at attachemnt
	 * 3. Get the issue.
	 * 4. Verify that the attachment is present in the issue data
	 * 5. Verify that the attachment contents is correct
	 * @return void
	 */
	public function testProjectAttachmentIsAdded() {
		$this->skipIfProjectDocumentationIsNotEnabled();

		$t_attachment_contents = 'Attachment contents.';
		$t_attachments_count = count( $this->client->mc_project_get_attachments( $this->userName, $this->password, $this->getProjectId() ) );

		$t_attachment_id = $this->client->mc_project_attachment_add(
			$this->userName,
			$this->password,
			$this->getProjectId(),
			'sample.txt',
			'title',
			'description',
			'txt',
			base64_encode( $t_attachment_contents ) );

		$this->projectAttachmentsToDelete[] = $t_attachment_id;

		$t_attachment = $this->client->mc_project_attachment_get(
			$this->userName,
			$this->password,
			$t_attachment_id );

		$this->assertEquals( $t_attachment_contents, base64_decode( $t_attachment ), '$t_attachment_contents' );

		$t_attachments = $this->client->mc_project_get_attachments( $this->userName, $this->password, $this->getProjectId() );
		$this->assertEquals( $t_attachments_count + 1, count( $t_attachments ), 'Check if we have 1 additional attachment' );

		# The attachment we just uploaded should be the last one
		$t_attachment = end( $t_attachments );
		$this->assertEquals( $this->userId, $t_attachment->user_id, "Attachment's User Id should match current user" );
		$this->assertEquals( 'description', $t_attachment->description );
	}

	/**
	 * A test case that tests the following:
	 * 1. Gets a non-existing project attachment
	 * 2. Verifies that an error is thrown
	 * @return void
	 */
	public function testProjectAttachmentNotFound() {
		$this->skipIfProjectDocumentationIsNotEnabled();

		try {
			$this->client->mc_project_attachment_get(
				$this->userName,
				$this->password,
				-1 );
			$this->fail( 'Should have failed.' );
		} catch( SoapFault $e ) {
			$this->assertRegexp( '/Unable to find an attachment/', $e->getMessage() );
		}
	}

	/**
	 * Skip test if enable_project_documentation is not enabled in the configuration
	 * @return void
	 */
	private function skipIfProjectDocumentationIsNotEnabled() {
		$t_config_enabled = $this->client->mc_config_get_string( $this->userName, $this->password, 'enable_project_documentation' );

		if( !$t_config_enabled ) {
			$this->markTestSkipped( 'Project documentation is not enabled.' );
		}
	}

	/**
	 * Tear Down: Remove project attachments added by test
	 * @return void
	 */
	protected function tearDown(): void {
		SoapBase::tearDown();

		foreach( $this->projectAttachmentsToDelete as $t_project_attachment_id ) {
			$this->client->mc_project_attachment_delete(
				$this->userName,
				$this->password,
				$t_project_attachment_id );
		}
	}
}
