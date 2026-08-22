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

namespace Mantis\tests\rest;

use Mantis\tests\core\Faker;

require_once 'RestBase.php';

/**
 * Test fixture for issue file webservice methods.
 *
 * @requires extension curl
 * @group REST
 */
class RestIssueFilesTest extends RestBase {
	/**
	 * An uploader may delete their own attachment only when configured to do so.
	 */
	public function testOwnAttachmentDeletionRequiresConfiguration() {
		$t_user = $this->createReporter();
		$t_issue_id = $this->createIssue();
		$t_file_id = $this->addFileAs( $t_issue_id, $t_user['name'] );
		$t_old_config = $this->setConfig( 'allow_delete_own_attachments', OFF );

		try {
			$t_response = $this->deleteFileAs( $t_issue_id, $t_file_id, $t_user['name'] );
			$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );

			$this->setConfig( 'allow_delete_own_attachments', ON );
			$t_response = $this->deleteFileAs( $t_issue_id, $t_file_id, $t_user['name'] );
			$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		} finally {
			$this->restoreConfig( 'allow_delete_own_attachments', $t_old_config );
		}
	}

	/**
	 * Deleting another user's attachment requires the configured access level.
	 */
	public function testOtherUserAttachmentDeletionRequiresAccessLevel() {
		$t_user = $this->createReporter();
		$t_issue_id = $this->createIssue();
		$t_file_id = $this->addFile( $t_issue_id );

		$t_response = $this->deleteFileAs( $t_issue_id, $t_file_id, $t_user['name'] );
		$this->assertEquals( HTTP_STATUS_FORBIDDEN, $t_response->getStatusCode() );

		$t_response = $this->deleteFileAs( $t_issue_id, $t_file_id );
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * Test adding an attachment to an issue.
	 */
	public function testAddIssueFile() {
		$t_issue_id = $this->createIssue();
		$t_file_id = $this->addFile( $t_issue_id );

		$t_response = $this->builder()->get(
			'/issues/' . $t_issue_id . '/files/' . $t_file_id
		)->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_file = json_decode( $t_response->getBody(), true )['files'][0];
		$this->assertEquals( 'delete-test.txt', $t_file['filename'] );
		$this->assertEquals( base64_encode( 'attachment to delete' ), $t_file['content'] );
	}

	/**
	 * Test deleting an attachment from its issue.
	 */
	public function testDeleteIssueFile() {
		$t_issue_id = $this->createIssue();
		$t_file_id = $this->addFile( $t_issue_id );

		$t_response = $this->builder()->delete(
			'/issues/' . $t_issue_id . '/files/' . $t_file_id
		)->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$this->assertEquals( $t_issue_id, json_decode( $t_response->getBody() )->issue->id );

		$t_response = $this->builder()->get( '/issues/' . $t_issue_id . '/files' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$this->assertCount( 0, json_decode( $t_response->getBody() )->files );
	}

	/**
	 * An attachment must belong to the issue in the URL.
	 */
	public function testCannotDeleteIssueFileFromAnotherIssue() {
		$t_issue_id = $this->createIssue();
		$t_other_issue_id = $this->createIssue();
		$t_file_id = $this->addFile( $t_issue_id );

		$t_response = $this->builder()->delete(
			'/issues/' . $t_other_issue_id . '/files/' . $t_file_id
		)->send();
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/issues/' . $t_issue_id . '/files/' . $t_file_id )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * Create an issue and register it for cleanup.
	 *
	 * @return int
	 */
	private function createIssue() {
		$t_response = $this->builder()->post( '/issues', $this->getIssueToAdd() )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_issue_id = json_decode( $t_response->getBody(), true )['issue']['id'];
		$this->deleteIssueAfterRun( $t_issue_id );
		return $t_issue_id;
	}

	/**
	 * Add a small attachment to an issue.
	 *
	 * @param int $p_issue_id Issue identifier.
	 * @return int Attachment identifier.
	 */
	private function addFile( $p_issue_id ) {
		return $this->addFileAs( $p_issue_id );
	}

	/**
	 * Add a small attachment to an issue as an optionally impersonated user.
	 *
	 * @param int    $p_issue_id Issue identifier.
	 * @param string $p_username Username to impersonate.
	 * @return int Attachment identifier.
	 */
	private function addFileAs( $p_issue_id, $p_username = null ) {
		$t_builder = $this->builder()->post(
			'/issues/' . $p_issue_id . '/files',
			array( 'files' => array( array(
				'name' => 'delete-test.txt',
				'content' => base64_encode( 'attachment to delete' ),
			) ) )
		);
		if( $p_username !== null ) {
			$t_builder->impersonate( $p_username );
		}
		$t_response = $t_builder->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );

		$t_response = $this->builder()->get( '/issues/' . $p_issue_id . '/files' )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_files = json_decode( $t_response->getBody(), true )['files'];
		$this->assertCount( 1, $t_files );
		return $t_files[0]['id'];
	}

	/**
	 * Delete an attachment as an optionally impersonated user.
	 *
	 * @param int    $p_issue_id Issue identifier.
	 * @param int    $p_file_id Attachment identifier.
	 * @param string $p_username Username to impersonate.
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	private function deleteFileAs( $p_issue_id, $p_file_id, $p_username = null ) {
		$t_builder = $this->builder()->delete( '/issues/' . $p_issue_id . '/files/' . $p_file_id );
		if( $p_username !== null ) {
			$t_builder->impersonate( $p_username );
		}
		return $t_builder->send();
	}

	/**
	 * Create an enabled reporter user and register it for cleanup.
	 *
	 * @return array User data.
	 */
	private function createReporter() {
		$t_response = $this->builder()->post( '/users', array(
			'name' => Faker::username(),
			'access_level' => array( 'name' => 'reporter' ),
			'enabled' => true,
		) )->send();
		$this->deleteAfterRunUserIfCreated( $t_response );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		return json_decode( $t_response->getBody(), true )['user'];
	}
}
