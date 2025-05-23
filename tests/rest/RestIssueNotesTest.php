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
 * @package    Tests
 * @subpackage UnitTests
 * @copyright  Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 */

namespace Mantis\tests\rest;

use Psr\Http\Message\ResponseInterface;
use RestBase;
use function PHPUnit\Framework\assertEquals;

require_once 'RestBase.php';

/**
 * Test fixture for Issue notes webservice methods.
 *
 * @TODO This is just a minimal suite covering adding plain notes
 *
 * @requires extension curl
 * @group    REST
 */
class RestIssueNotesTest extends RestBase
{
	/**
	 * @var int $issueId;
	 */
	private int $issueId;

	/**
	 * Test for adding plain, public notes via POST /issue/{id}/notes
	 *
	 * Covers empty payload and note text and maximum length cases.
	 *
	 * @return void
	 */
	public function testAddNote() {
		$this->createTestIssue();

		# Plain bug note
		$t_response = $this->addNote( $this->generateNoteData( 'Test Note' ) );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
			"Creating a note should succeed"
		);

		# Maximum length
		$t_long_text = str_repeat( 'x', config_get_global( 'max_textarea_length' ) );
		$t_response = $this->addNote( $this->generateNoteData( $t_long_text ) );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode(),
		"Creating a note with maximum size should succeed"
		);

		# Too long
		$t_response = $this->addNote( $this->generateNoteData( $t_long_text . ' TOO LONG' ) );
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Creating a note longer than max size should fail"
		);

		# Empty note text and payload
		$t_response = $this->addNote( [] );
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Creating a note with an empty payload should fail"
		);
		$t_response = $this->addNote( $this->generateNoteData( '' ) );
		$this->assertEquals( HTTP_STATUS_BAD_REQUEST, $t_response->getStatusCode(),
			"Creating a empty note should fail"
		);
	}

	/**
	 * Test adding notes to a non-existing Issue.
	 *
	 * @return void
	 */
	public function testAddNoteToNonExistingIssue() {
		$this->issueId = 99999999;
		$t_response = $this->addNote( $this->generateNoteData( 'Test Note' ) );
		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_response->getStatusCode(),
			"Creating a note on missing issue should fail"
		);
	}

	public function testAddNoteWithTimeTracking() {
		$this->markTestIncomplete( 'Not implemented yet' );
	}

	public function testAddNoteWithAttachment() {
		$this->markTestIncomplete( 'Not implemented yet' );
	}

	public function testDeleteNote() {
		$this->createTestIssue();

		# Add test bugnote
		$t_response = $this->addNote( $this->generateNoteData( 'Note to be deleted' ) );
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody() );
		$t_notes_count = count( $t_body->issue->notes );

		# Delete it
		$t_response = $this->builder()->delete( $this->endPoint( $t_body->note->id ) )->send();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$t_issue = json_decode( $t_response->getBody() );
		$this->assertCount( $t_notes_count - 1,
			$t_issue->issue->notes ?? [],
			"There should be one less note than before"
		);
	}

	/**
	 * Create a test issue to add notes to.
	 *
	 * Sets $issueId property with the Id.
	 */
	private function createTestIssue(): void {
		$t_issue_to_add = $this->getIssueToAdd();
		$t_response = $this->builder()->post( '/issues', $t_issue_to_add )->send();
		$this->assertEquals( HTTP_STATUS_CREATED, $t_response->getStatusCode() );
		$t_body = json_decode( $t_response->getBody(), true );
		$this->issueId = $t_body['issue']['id'];
		$this->deleteIssueAfterRun( $this->issueId );
	}

	/**
	 * REST API endpoint for Bugnotes.
	 *
	 * @param int $p_note Optional Note ID.
	 *
	 * @return string
	 */
	private function endPoint( int $p_note = 0 ): string {
		return '/issues/' . $this->issueId . '/notes/' . ( $p_note ?: '' );
	}

	/**
	 * Generates Bugnote data payload.
	 *
	 * @param string|null $p_text The text of the note to be included.
	 *                            If null, the text key is not set.
	 *
	 * @return array An associative array containing the Bugnote data.
	 */
	private function generateNoteData( ?string $p_text ): array {
		$t_data = [];
		if( $p_text !== null ) {
			$t_data['text'] = $p_text;
		}
		return $t_data;
	}

	/**
	 * Sends a REST API POST Bugnotes request.
	 *
	 * @param array $p_note_data Bugnote payload.
	 *
	 * @return ResponseInterface
	 */
	private function addNote( $p_note_data ): ResponseInterface {
		return $this->builder()->post( $this->endPoint(), $p_note_data )->send();
	}
}
