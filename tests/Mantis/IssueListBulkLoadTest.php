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
 * Test cases for loading the data of a page of issues at once in
 * mc_filter_get_issues(), as done by GET /api/rest/issues.
 *
 * The issues built after the bulk load must be the same as the issues built
 * one by one, for an administrator and for a reporter who can't see private
 * notes or restricted custom fields, and the number of queries must not grow
 * with the number of issues.
 *
 * @package    Tests
 * @subpackage MantisCoreTests
 * @link       http://www.mantisbt.org
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace Mantis\tests\Mantis;

use ApiObjectFactory;
use IssueAddCommand;

require_once __DIR__ . '/MantisCoreBase.php';
require_once dirname( __DIR__, 2 ) . '/api/soap/mc_core.php';

/**
 * PHPUnit tests for the bulk load of the REST issue list
 */
class IssueListBulkLoadTest extends MantisCoreBase {

	/** @var int Test project */
	protected static $project_id;

	/** @var int Reporter in the test project */
	protected static $reporter_id;

	/** @var string Reporter username */
	protected static $reporter_name;

	/** @var int Custom field readable by everyone */
	protected static $field_id;

	/** @var int Custom field readable by developers only */
	protected static $private_field_id;

	/** @var int Tag attached to every issue */
	protected static $tag_id;

	/** @var int[] Test issues, each with notes, a tag, a monitor and custom field values */
	protected static $issue_ids = array();

	/** @var int Private note on the first issue */
	protected static $private_note_id;

	/** @var bool ApiObjectFactory::$soap before the tests */
	protected static $soap;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::login();

		self::$soap = ApiObjectFactory::$soap;
		ApiObjectFactory::$soap = false;

		self::$project_id = project_create( 'BulkLoad' . rand(), '', 10 );
		config_set( 'enable_email_notification', OFF, ALL_USERS, self::$project_id );
		$t_category_id = category_add( self::$project_id, 'General' );

		self::$reporter_name = 'BulkReporter' . rand();
		$t_cookie = user_create( self::$reporter_name, 'password', 'bulk' . rand() . '@example.test' );
		self::$reporter_id = user_get_id_by_cookie( $t_cookie );
		project_add_user( self::$project_id, self::$reporter_id, REPORTER );

		self::$field_id = self::createCustomField( 'BulkField' . rand(), VIEWER );
		self::$private_field_id = self::createCustomField( 'BulkPrivateField' . rand(), DEVELOPER );
		self::$tag_id = tag_create( 'BulkTag' . rand() );

		for( $i = 1; $i <= 4; $i++ ) {
			$t_command = new IssueAddCommand( array(
				'payload' => array(
					'issue' => array(
						'summary' => 'Bulk load issue ' . $i,
						'description' => 'Description ' . $i,
						'steps_to_reproduce' => 'Steps ' . $i,
						'project' => array( 'id' => self::$project_id ),
						'category' => array( 'id' => $t_category_id ),
					),
				),
			) );
			$t_result = $t_command->execute();
			$t_issue_id = (int)$t_result['issue_id'];
			self::$issue_ids[] = $t_issue_id;

			bugnote_add( $t_issue_id, 'Public note ' . $i );
			bugnote_add( $t_issue_id, 'Reporter note ' . $i, '0:00', false, BUGNOTE, '', self::$reporter_id );
			tag_bug_attach( self::$tag_id, $t_issue_id );
			bug_monitor( $t_issue_id, self::$reporter_id );
			custom_field_set_value( self::$field_id, $t_issue_id, 'Value ' . $i );
			custom_field_set_value( self::$private_field_id, $t_issue_id, 'Secret ' . $i );
		}

		$t_first = self::$issue_ids[0];
		self::$private_note_id = bugnote_add( $t_first, 'Private note', '0:00', true );
		self::addAttachment( $t_first, 'issue.txt', 0 );
		self::addAttachment( $t_first, 'private-note.txt', self::$private_note_id );
		relationship_add( $t_first, self::$issue_ids[1], BUG_RELATED, false );
		relationship_add( self::$issue_ids[2], $t_first, BUG_DEPENDANT, false );

		bug_clear_cache_all();
	}

	public static function tearDownAfterClass(): void {
		self::login();
		ApiObjectFactory::$soap = self::$soap;

		global $g_project_override;
		$g_project_override = null;

		try {
			project_delete( self::$project_id );
		} finally {
			custom_field_destroy( self::$field_id );
			custom_field_destroy( self::$private_field_id );
			tag_delete( self::$tag_id );
			user_delete( self::$reporter_id );
		}
	}

	protected function tearDown(): void {
		# Reset the new caches, so the order of the tests doesn't matter.
		bug_monitor_clear_cache();
		history_clear_cache();
		relationship_clear_cache();

		self::login();
		parent::tearDown();
	}

	/**
	 * An administrator gets the same issues, with all related data, after the bulk load.
	 * @return void
	 */
	public function testBulkLoadMatchesPerIssueForAdministrator() {
		$t_expected = $this->buildIssues( self::$issue_ids, null, false );
		$t_actual = $this->buildIssues( self::$issue_ids, null, true );

		$this->assertEquals( $t_expected, $t_actual );

		$t_first = $t_actual[0];
		$this->assertEquals( 'Description 1', $t_first['description'] );
		$this->assertEquals( 'Steps 1', $t_first['steps_to_reproduce'] );
		$this->assertCount( 3, $t_first['notes'] );
		$this->assertEquals( array( 'issue.txt' ), array_column( $t_first['attachments'], 'filename' ) );
		$this->assertEquals( array( 'private-note.txt' ), array_column( $t_first['notes'][2]['attachments'], 'filename' ) );
		$this->assertCount( 2, $t_first['relationships'] );
		$this->assertCount( 2, $t_first['custom_fields'] );
		$this->assertEquals( array( self::$tag_id ), array_column( $t_first['tags'], 'id' ) );
		$this->assertEquals( array( self::$reporter_id ), array_column( $t_first['monitors'], 'id' ) );
		$this->assertNotEmpty( $t_first['history'] );
	}

	/**
	 * A reporter gets the same issues after the bulk load, without private notes,
	 * their attachments or restricted custom fields.
	 * @return void
	 */
	public function testBulkLoadMatchesPerIssueForReporter() {
		auth_attempt_script_login( self::$reporter_name );
		$this->assertEquals( self::$reporter_id, auth_get_current_user_id() );

		$t_expected = $this->buildIssues( self::$issue_ids, null, false, self::$reporter_name, 'password' );
		$t_actual = $this->buildIssues( self::$issue_ids, null, true, self::$reporter_name, 'password' );

		$this->assertEquals( $t_expected, $t_actual );

		$t_first = $t_actual[0];
		$this->assertCount( 2, $t_first['notes'] );
		$this->assertNotContains( self::$private_note_id, array_column( $t_first['notes'], 'id' ) );
		$this->assertEquals( array( 'issue.txt' ), array_column( $t_first['attachments'], 'filename' ) );
		$this->assertEquals( array( self::$field_id ), array_column( array_column( $t_first['custom_fields'], 'field' ), 'id' ) );

		$t_history_json = json_encode( $t_first['history'] );
		$this->assertStringNotContainsString( 'private-note.txt', $t_history_json );
		$this->assertStringNotContainsString( 'Secret', $t_history_json );
	}

	/**
	 * The bulk loaders return the same data, with the same types, as the reads for one issue.
	 * A string id instead of an int changes the response, e.g. a user is no longer seen as themselves.
	 * @return void
	 */
	public function testBulkLoadersReturnSameDataAndTypes() {
		$t_reads = array(
			'text' => function( $p_id ) { return bug_text_cache_row( $p_id ); },
			'attachments' => function( $p_id ) { return bug_get_attachments( $p_id ); },
			'notes' => function( $p_id ) { return var_export( bugnote_get_all_bugnotes( $p_id ), true ); },
			'relationships' => function( $p_id ) {
				return var_export( array( relationship_get_all_src( $p_id ), relationship_get_all_dest( $p_id ) ), true );
			},
			'monitors' => function( $p_id ) { return bug_get_monitors( $p_id ); },
			'history' => function( $p_id ) { return history_get_raw_events_array( $p_id ); },
		);

		$this->getIssues( self::$issue_ids );
		$t_expected = array();
		foreach( self::$issue_ids as $t_issue_id ) {
			foreach( $t_reads as $t_name => $t_read ) {
				$t_expected[$t_issue_id][$t_name] = $t_read( $t_issue_id );
			}
		}

		$this->getIssues( self::$issue_ids );
		bug_text_cache_array_rows( self::$issue_ids );
		bug_attachments_cache_array_rows( self::$issue_ids );
		bugnote_cache_array_rows_by_bug_ids( self::$issue_ids );
		relationship_cache_array_rows( self::$issue_ids );
		bug_monitor_cache_array_rows( self::$issue_ids );
		history_cache_array_rows( self::$issue_ids );
		try {
			$t_queries = db_count_queries();
			foreach( self::$issue_ids as $t_issue_id ) {
				foreach( $t_reads as $t_name => $t_read ) {
					$this->assertSame( $t_expected[$t_issue_id][$t_name], $t_read( $t_issue_id ), "$t_name of issue $t_issue_id" );
				}
			}
			$this->assertEquals( 0, db_count_queries() - $t_queries, 'all reads come from the cache' );
		} finally {
			$this->getIssues( self::$issue_ids );
			bug_monitor_clear_cache();
		}
	}

	/**
	 * With a select list, only the requested data is loaded.
	 * @return void
	 */
	public function testBulkLoadLoadsOnlySelectedFields() {
		$t_fields = array( 'id', 'summary' );

		$t_actual = $this->buildIssues( self::$issue_ids, $t_fields, true );
		$this->assertEquals( $this->buildIssues( self::$issue_ids, $t_fields, false ), $t_actual );
		$this->assertEquals( array( 'id', 'summary' ), array_keys( $t_actual[0] ) );

		# Only the issue list itself is read, so a larger page costs no extra queries.
		$this->assertEquals(
			$this->countQueries( 2, $t_fields, true ),
			$this->countQueries( 4, $t_fields, true ) );
	}

	/**
	 * A larger page of the same kind of issues doesn't add queries for each issue.
	 * @return void
	 */
	public function testQueryCountDoesNotGrowWithIssues() {
		# Warm the caches that don't depend on the issues (config, project, users).
		$this->buildIssues( self::$issue_ids, null, true );

		$t_two = $this->countQueries( 2, null, true );
		$t_four = $this->countQueries( 4, null, true );
		$t_four_per_issue = $this->countQueries( 4, null, false );

		# Before the bulk load, each added issue cost about 10 queries.
		$this->assertLessThan( 2, $t_four - $t_two, "2 issues: $t_two queries, 4 issues: $t_four queries" );
		$this->assertLessThan( $t_four_per_issue, $t_four );
	}

	/**
	 * Relationship, monitor and history writes after the bulk load are seen by the next read.
	 * @return void
	 */
	public function testWritesAfterBulkLoadAreSeen() {
		$this->getIssues( self::$issue_ids );
		$t_bug_id = self::$issue_ids[3];
		$t_other_id = self::$issue_ids[1];

		relationship_cache_array_rows( self::$issue_ids );
		bug_monitor_cache_array_rows( self::$issue_ids );
		history_cache_array_rows( self::$issue_ids );
		try {
			$t_relationships = count( relationship_get_all_src( $t_bug_id ) );
			$t_history = count( history_get_raw_events_array( $t_bug_id ) );
			$this->assertEquals( array( self::$reporter_id ), bug_get_monitors( $t_bug_id ) );

			$t_relationship_id = relationship_add( $t_bug_id, $t_other_id, BUG_RELATED, false );
			$this->assertCount( $t_relationships + 1, relationship_get_all_src( $t_bug_id ) );
			$this->assertGreaterThan( $t_history, count( history_get_raw_events_array( $t_bug_id ) ) );

			relationship_delete( $t_relationship_id, false );
			$this->assertCount( $t_relationships, relationship_get_all_src( $t_bug_id ) );

			bug_unmonitor( $t_bug_id, self::$reporter_id );
			$this->assertEquals( array(), bug_get_monitors( $t_bug_id ) );
		} finally {
			relationship_clear_cache();
			bug_monitor_clear_cache();
			history_clear_cache();
			bug_monitor( $t_bug_id, self::$reporter_id );
		}
	}

	/**
	 * Build the issues one by one, or with the bulk load of mc_filter_get_issues().
	 *
	 * @param int[]      $p_issue_ids Issue ids, in the order of the result.
	 * @param array|null $p_fields    Selected fields, null for all.
	 * @param bool       $p_bulk      true to build them with mc_filter_get_issues().
	 * @param string     $p_username  Username of the logged-in user.
	 * @param string     $p_password  Password of the logged-in user.
	 * @return array The issues as arrays.
	 */
	private function buildIssues( array $p_issue_ids, $p_fields, $p_bulk, $p_username = null, $p_password = null ) {
		$t_issues = $this->getIssues( $p_issue_ids );

		if( $p_bulk ) {
			$t_result = $this->getPage( count( $p_issue_ids ), $p_fields, $p_username, $p_password );
			$t_by_id = array_column( $t_result, null, 'id' );
			$t_ordered = array();
			foreach( $p_issue_ids as $t_issue_id ) {
				$t_ordered[] = $t_by_id[$t_issue_id];
			}
			return $t_ordered;
		}

		$t_user_id = auth_get_current_user_id();
		$t_lang = mci_get_user_lang( $t_user_id );
		$t_result = array();
		foreach( $t_issues as $t_issue ) {
			$t_result[] = mci_issue_data_as_array( $t_issue, $t_user_id, $t_lang, $p_fields );
		}

		return $t_result;
	}

	/**
	 * Count the queries for one page of the test project, starting without cached issue data.
	 *
	 * @param int        $p_page_size Number of issues in the page.
	 * @param array|null $p_fields    Selected fields, null for all.
	 * @param bool       $p_bulk      true to build them with mc_filter_get_issues().
	 * @return int Number of queries.
	 */
	private function countQueries( $p_page_size, $p_fields, $p_bulk ) {
		$t_issue_ids = array_slice( self::$issue_ids, 0, $p_page_size );
		$this->getIssues( self::$issue_ids );

		$t_queries = db_count_queries();
		if( $p_bulk ) {
			$t_issues = $this->getPage( $p_page_size, $p_fields );
		} else {
			$t_issues = $this->buildIssues( $t_issue_ids, $p_fields, false );
		}
		$t_count = db_count_queries() - $t_queries;

		$this->assertCount( $p_page_size, $t_issues );

		return $t_count;
	}

	/**
	 * Get the first page of the test project's issues, as GET /api/rest/issues does.
	 *
	 * @param int        $p_page_size Number of issues in the page.
	 * @param array|null $p_fields    Selected fields, null for all.
	 * @param string     $p_username  Username of the logged-in user, null for the test suite user.
	 * @param string     $p_password  Password of the logged-in user.
	 * @return array The issues as arrays.
	 */
	private function getPage( $p_page_size, $p_fields, $p_username = null, $p_password = null ) {
		$t_result = mc_filter_get_issues(
			$p_username ?? self::$userName,
			$p_password ?? self::$password,
			self::$project_id,
			FILTER_STANDARD_ANY,
			1,
			$p_page_size,
			$p_fields );
		$this->assertIsArray( $t_result );

		return $t_result;
	}

	/**
	 * Get the issues with no issue data cached, like a new request.
	 *
	 * The issue rows stay cached, as filter_get_bug_rows() caches them too.
	 *
	 * @param int[] $p_issue_ids Issue ids.
	 * @return \BugData[]
	 */
	private function getIssues( array $p_issue_ids ) {
		$t_issues = array();
		foreach( $p_issue_ids as $t_issue_id ) {
			bug_clear_cache_all( $t_issue_id );
			history_clear_cache( $t_issue_id );
		}
		relationship_clear_cache();

		bug_cache_array_rows( $p_issue_ids );
		foreach( $p_issue_ids as $t_issue_id ) {
			$t_issues[] = bug_get( $t_issue_id );
		}

		return $t_issues;
	}

	/**
	 * Create a text custom field linked to the test project.
	 *
	 * @param string $p_name         Field name.
	 * @param int    $p_access_level Access level needed to read it.
	 * @return int Field id.
	 */
	private static function createCustomField( $p_name, $p_access_level ) {
		$t_field_id = custom_field_create( $p_name );
		$t_def = custom_field_get_definition( $t_field_id );
		$t_def['access_level_r'] = $p_access_level;
		$t_def['access_level_rw'] = max( $p_access_level, (int)$t_def['access_level_rw'] );
		# Not filterable: the default filter is cached in a static by earlier tests and
		# doesn't know new fields, so filtering by them fails with 'Undefined array key'.
		$t_def['filter_by'] = false;
		custom_field_update( $t_field_id, $t_def );
		custom_field_link( $t_field_id, self::$project_id );

		return $t_field_id;
	}

	/**
	 * Attach a small text file to an issue or one of its notes.
	 *
	 * @param int    $p_issue_id Issue id.
	 * @param string $p_name     File name.
	 * @param int    $p_note_id  Note id, 0 for the issue.
	 * @return void
	 */
	private static function addAttachment( $p_issue_id, $p_name, $p_note_id ) {
		$t_tmp_file = tempnam( sys_get_temp_dir(), 'bulk' );
		file_put_contents( $t_tmp_file, 'Bulk load test' );

		file_add( $p_issue_id, array(
				'name' => $p_name,
				'tmp_name' => $t_tmp_file,
				'type' => 'text/plain',
				'size' => filesize( $t_tmp_file ),
				'browser_upload' => false,
			), 'bug', '', '', null, 0, false, $p_note_id );
	}
}
