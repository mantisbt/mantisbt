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
 * Test cases for IssueNoteAddCommand
 *
 * @package    Tests
 * @subpackage IssueNoteAddCommand
 * @copyright  Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for IssueNoteAddCommand
 */
class IssueNoteAddCommandTest extends MantisCoreBase {
	/**
	 * @var int Test issue id
	 */
	private $issueId;

	/**
	 * Create a test issue to add notes to.
	 */
	protected function setUp(): void {
		parent::setUp();
		self::login();

		$t_issue_data = new \BugData();
		$t_issue_data->project_id = 1;
		$t_issue_data->summary = __CLASS__ . ': test issue ' . rand( 1, 1000000 );
		$t_issue_data->description = 'Test issue for IssueNoteAddCommand tests';
		$t_issue_data->category_id = 1;

		$this->issueId = $t_issue_data->create();
	}

	/**
	 * Clean up test issue.
	 */
	protected function tearDown(): void {
		bug_delete( $this->issueId );
	}

	/**
	 * Test adding a note with mute option set to true.
	 *
	 * When mute is true, the note should be created but no emails should be queued.
	 */
	public function testAddNoteWithMuteTrue() {
		$t_old_config = $this->setConfig( 'enable_email_notification', ON );

		try {
			$t_email_count_before = count( email_queue_get_ids() );

			$t_data = $this->buildCommandData( 'Note with mute=true', array( 'mute' => true ) );
			$t_command = new \IssueNoteAddCommand( $t_data );
			$t_result = $t_command->execute();

			$this->assertArrayHasKey( 'id', $t_result, 'Command should return note id' );
			$this->assertGreaterThan( 0, $t_result['id'], 'Note id should be positive' );
			$this->assertTrue( bugnote_exists( $t_result['id'] ), 'Note should exist in database' );

			$t_email_count_after = count( email_queue_get_ids() );
			$this->assertEquals( $t_email_count_before, $t_email_count_after,
				'No emails should be queued when mute is true'
			);
		} finally {
			$this->restoreConfig( 'enable_email_notification', $t_old_config );
		}
	}

	/**
	 * Test adding a note with mute option set to false.
	 *
	 * Note should be created successfully, email sending should not be suppressed.
	 */
	public function testAddNoteWithMuteFalse() {
		$t_old_config = $this->setConfig( 'enable_email_notification', ON );

		try {
			$t_email_count_before = count( email_queue_get_ids() );

			$t_data = $this->buildCommandData( 'Note with mute=false', array( 'mute' => false ) );
			$t_command = new \IssueNoteAddCommand( $t_data );
			$t_result = $t_command->execute();

			$this->assertArrayHasKey( 'id', $t_result, 'Command should return note id' );
			$this->assertGreaterThan( 0, $t_result['id'], 'Note id should be positive' );
			$this->assertTrue( bugnote_exists( $t_result['id'] ), 'Note should exist in database' );

			$t_email_count_after = count( email_queue_get_ids() );
			$this->assertGreaterThanOrEqual( $t_email_count_before, $t_email_count_after,
				'Email queue count should not decrease when mute is false'
			);
		} finally {
			$this->restoreConfig( 'enable_email_notification', $t_old_config );
		}
	}

	/**
	 * Test adding a note without specifying the mute option.
	 *
	 * Should behave the same as mute=false (backward compatible).
	 */
	public function testAddNoteWithoutMuteOption() {
		$t_old_config = $this->setConfig( 'enable_email_notification', ON );

		try {
			$t_email_count_before = count( email_queue_get_ids() );

			$t_data = $this->buildCommandData( 'Note without mute option' );
			$t_command = new \IssueNoteAddCommand( $t_data );
			$t_result = $t_command->execute();

			$this->assertArrayHasKey( 'id', $t_result, 'Command should return note id' );
			$this->assertGreaterThan( 0, $t_result['id'], 'Note id should be positive' );
			$this->assertTrue( bugnote_exists( $t_result['id'] ), 'Note should exist in database' );

			$t_email_count_after = count( email_queue_get_ids() );
			$this->assertGreaterThanOrEqual( $t_email_count_before, $t_email_count_after,
				'Email queue count should not decrease when mute option is not specified'
			);
		} finally {
			$this->restoreConfig( 'enable_email_notification', $t_old_config );
		}
	}

	/**
	 * Build command data for IssueNoteAddCommand.
	 *
	 * @param string $p_text    The note text.
	 * @param array  $p_options Command options (e.g., mute).
	 * @return array Command data array.
	 */
	private function buildCommandData( $p_text, array $p_options = array() ) {
		$t_data = array(
			'query' => array( 'issue_id' => $this->issueId ),
			'payload' => array( 'text' => $p_text ),
		);

		if( !empty( $p_options ) ) {
			$t_data['options'] = $p_options;
		}

		return $t_data;
	}
}
