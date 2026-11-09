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

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for tag cache invalidation.
 */
class TagApiTest extends MantisCoreBase {

	/**
	 * Tag ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $tagIdsToDelete = [];

	/**
	 * Issue ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $issueIdsToDelete = [];

	/**
	 * Project ids created by the tests and removed during teardown.
	 *
	 * @var int[]
	 */
	private array $projectIdsToDelete = [];

	/**
	 * Initialize the test class and authenticate the test user.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::login();
	}

	/**
	 * Re-establish the database connection and clear tag caches.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::dbConnect();
		tag_clear_cache();
		tag_clear_cache_bug_tags();
	}

	/**
	 * Delete tags created by the tests.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach( $this->issueIdsToDelete as $t_issue_id ) {
			if( bug_exists( $t_issue_id ) ) {
				bug_delete( $t_issue_id );
			}
		}
		foreach( array_reverse( $this->tagIdsToDelete ) as $t_tag_id ) {
			if( tag_exists( $t_tag_id ) ) {
				tag_delete( $t_tag_id );
			}
		}
		foreach( $this->projectIdsToDelete as $t_project_id ) {
			if( project_exists( $t_project_id ) ) {
				project_delete( $t_project_id );
			}
		}

		parent::tearDown();
	}

	/**
	 * Verify that tag updates and deletion clear the tag cache.
	 *
	 * @return void
	 */
	public function testTagUpdateAndDeleteClearCache(): void {
		$t_tag_name = 'TagApiTest-' . uniqid( '', true );
		$t_tag_id = tag_create( $t_tag_name, auth_get_current_user_id(), 'Created by TagApiTest' );
		$this->tagIdsToDelete[] = $t_tag_id;

		$t_prime_queries = db_count_queries();
		$t_tag_row = tag_get( $t_tag_id );
		$this->assertGreaterThan( $t_prime_queries, db_count_queries(), 'Initial tag lookup should hit the database.' );
		$this->assertSame( $t_tag_name, $t_tag_row['name'] );

		$t_updated_name = $t_tag_name . '-updated';
		tag_update( $t_tag_id, $t_updated_name, auth_get_current_user_id(), 'Updated by TagApiTest' );

		$t_after_update_queries = db_count_queries();
		$this->assertSame( $t_updated_name, tag_get_name( $t_tag_id ) );
		$this->assertGreaterThan( $t_after_update_queries, db_count_queries(), 'Tag update should invalidate the cached tag row.' );

		tag_delete( $t_tag_id );

		$t_after_delete_queries = db_count_queries();
		$this->assertFalse( tag_get( $t_tag_id ) );
		$this->assertGreaterThan( $t_after_delete_queries, db_count_queries(), 'Tag delete should invalidate the cached tag row.' );
	}

	/**
	 * Create a project that is deleted during teardown.
	 *
	 * @return int Project id
	 */
	private function createProject(): int {
		$t_project_id = project_create( __CLASS__ . ' ' . uniqid(), 'Test project for TagApiTest.', 30 );
		$this->projectIdsToDelete[] = $t_project_id;
		return $t_project_id;
	}

	/**
	 * Make a project a subproject of another, discarding the cached project hierarchy.
	 *
	 * @param int $p_child_id  Child project id.
	 * @param int $p_parent_id Parent project id.
	 * @return void
	 */
	private function addSubproject( int $p_child_id, int $p_parent_id ): void {
		project_hierarchy_add( $p_child_id, $p_parent_id );
		foreach( array( 'enabled', 'all' ) as $t_suffix ) {
			$GLOBALS['g_cache_project_hierarchy_' . $t_suffix] = null;
			$GLOBALS['g_cache_project_inheritance_' . $t_suffix] = null;
		}
	}

	/**
	 * Create an issue in a project that is deleted during teardown.
	 *
	 * @param int $p_project_id Project id.
	 * @return int Issue id
	 */
	private function createIssue( int $p_project_id ): int {
		$t_issue = new \BugData();
		$t_issue->project_id = $p_project_id;
		$t_issue->category_id = 1;
		$t_issue->summary = __CLASS__ . ': issue ' . uniqid();
		$t_issue->description = 'Issue used by TagApiTest.';
		$t_issue_id = $t_issue->create();
		$this->issueIdsToDelete[] = $t_issue_id;
		return $t_issue_id;
	}

	/**
	 * Create a tag that is deleted during teardown.
	 *
	 * @param string $p_name       Tag name.
	 * @param int    $p_project_id Project id, or ALL_PROJECTS for a global tag.
	 * @param string $p_description Tag description.
	 * @return int Tag id
	 */
	private function createTag( string $p_name, int $p_project_id, string $p_description = '' ): int {
		$t_tag_id = tag_create( $p_name, auth_get_current_user_id(), $p_description, $p_project_id );
		$this->tagIdsToDelete[] = $t_tag_id;
		return $t_tag_id;
	}

	/**
	 * Attach tags to an issue, with the TagAttachCommand used by the UI and the REST API.
	 *
	 * @param int   $p_issue_id Issue id.
	 * @param array $p_tags     Tags, as arrays with an id or a name.
	 * @return void
	 */
	private function attachTags( int $p_issue_id, array $p_tags ): void {
		( new \TagAttachCommand( array(
			'query' => array( 'issue_id' => $p_issue_id ),
			'payload' => array( 'tags' => $p_tags ),
		) ) )->execute();
		tag_clear_cache_bug_tags();
	}

	/**
	 * Same tag name can exist in different scopes, but only once in each.
	 * A project tag takes precedence over the global tag with the same name.
	 *
	 * @return void
	 */
	public function testProjectTagTakesPrecedenceOverGlobalTag(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_project_id = $this->createProject();
		$t_other_project_id = $this->createProject();

		$t_global_id = $this->createTag( $t_name, ALL_PROJECTS );
		$this->assertSame( $t_global_id, (int)tag_get_by_name( $t_name, $t_project_id )['id'] );

		$t_project_tag_id = $this->createTag( strtoupper( $t_name ), $t_project_id );
		$this->assertSame( $t_project_tag_id, (int)tag_get_by_name( $t_name, $t_project_id )['id'] );
		$this->assertSame( $t_global_id, (int)tag_get_by_name( $t_name, $t_other_project_id )['id'] );
		$this->assertSame( $t_global_id, (int)tag_get_by_name( $t_name, ALL_PROJECTS )['id'] );

		try {
			tag_create( $t_name, null, '', $t_project_id );
			$this->fail( 'Duplicate name in the same project should be rejected.' );
		} catch( \Mantis\Exceptions\ClientException $e ) {
			$this->assertSame( ERROR_TAG_DUPLICATE, $e->getCode() );
		}

		# A project tag is not visible to other projects
		$t_other_names = array_column( tag_get_candidates_for_bug( 0, $t_other_project_id ), 'id' );
		$this->assertContains( $t_global_id, array_map( 'intval', $t_other_names ) );
		$this->assertNotContains( $t_project_tag_id, array_map( 'intval', $t_other_names ) );

		# Candidates of the project only list the name once, with the project tag
		$t_ids = array_map( 'intval', array_column( tag_get_candidates_for_bug( 0, $t_project_id ), 'id' ) );
		$this->assertContains( $t_project_tag_id, $t_ids );
		$this->assertNotContains( $t_global_id, $t_ids );
	}

	/**
	 * Tags created by tagging an issue are added to the project of the issue.
	 * Tags can only be attached to issues of their project, unless they are global.
	 *
	 * @return void
	 */
	public function testTaggingIssueCreatesProjectTag(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_project_id = $this->createProject();
		$t_other_project_id = $this->createProject();
		$t_issue_id = $this->createIssue( $t_project_id );
		$t_other_issue_id = $this->createIssue( $t_other_project_id );

		$this->attachTags( $t_issue_id, array( array( 'name' => $t_name ) ) );
		$t_tag = tag_get_by_name( $t_name, $t_project_id );
		$this->tagIdsToDelete[] = $t_tag['id'];
		$this->assertSame( $t_project_id, (int)$t_tag['project_id'] );
		$this->assertFalse( tag_get_by_name( $t_name, $t_other_project_id ) );

		# The same name in another project creates a different tag
		$this->attachTags( $t_other_issue_id, array( array( 'name' => $t_name ) ) );
		$t_other_tag = tag_get_by_name( $t_name, $t_other_project_id );
		$this->tagIdsToDelete[] = $t_other_tag['id'];
		$this->assertNotSame( (int)$t_tag['id'], (int)$t_other_tag['id'] );
		$this->assertSame( $t_other_project_id, (int)$t_other_tag['project_id'] );

		# A project tag can't be attached by id to an issue of another project
		$t_global_id = $this->createTag( $t_name . '-global', ALL_PROJECTS );
		try {
			$this->attachTags( $t_other_issue_id, array( array( 'id' => $t_tag['id'] ) ) );
			$this->fail( 'Tag of another project should be rejected.' );
		} catch( \Mantis\Exceptions\ClientException $e ) {
			$this->assertSame( ERROR_TAG_NOT_IN_PROJECT, $e->getCode() );
		}

		# Global tags can be attached anywhere, and a project tag wins over a global tag by name
		$this->attachTags( $t_issue_id, array( array( 'id' => $t_global_id ) ) );
		$this->attachTags( $t_other_issue_id, array( array( 'id' => $t_global_id ) ) );
		$t_global_same_name_id = $this->createTag( $t_name, ALL_PROJECTS );
		$t_third_issue_id = $this->createIssue( $t_project_id );
		$this->attachTags( $t_third_issue_id, array( array( 'name' => $t_name ) ) );
		$t_attached_ids = array_map( 'intval', array_column( tag_bug_get_attached( $t_third_issue_id ), 'id' ) );
		$this->assertSame( array( (int)$t_tag['id'] ), $t_attached_ids );
		$this->assertNotSame( $t_global_same_name_id, $t_attached_ids[0] );
	}

	/**
	 * A tag can only be moved to a project if no issue of another project uses it.
	 *
	 * @return void
	 */
	public function testMoveTagBetweenProjects(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_project_id = $this->createProject();
		$t_other_project_id = $this->createProject();
		$t_issue_id = $this->createIssue( $t_project_id );
		$t_other_issue_id = $this->createIssue( $t_other_project_id );
		$t_user_id = auth_get_current_user_id();

		# Unused tags can be moved anywhere
		$t_tag_id = $this->createTag( $t_name, ALL_PROJECTS );
		tag_update( $t_tag_id, $t_name, $t_user_id, '', $t_project_id );
		$this->assertSame( $t_project_id, (int)tag_get_field( $t_tag_id, 'project_id' ) );

		# Used by an issue of its project: it can go global, but not to other projects
		$this->attachTags( $t_issue_id, array( array( 'id' => $t_tag_id ) ) );
		try {
			tag_update( $t_tag_id, $t_name, $t_user_id, '', $t_other_project_id );
			$this->fail( 'Tag used in another project should not be moved.' );
		} catch( \Mantis\Exceptions\ClientException $e ) {
			$this->assertSame( ERROR_TAG_USED_IN_OTHER_PROJECTS, $e->getCode() );
		}
		$this->assertSame( $t_project_id, (int)tag_get_field( $t_tag_id, 'project_id' ) );

		tag_update( $t_tag_id, $t_name, $t_user_id, '', ALL_PROJECTS );
		$this->assertSame( ALL_PROJECTS, (int)tag_get_field( $t_tag_id, 'project_id' ) );

		# Used in two projects: can't be scoped to any of them
		$this->attachTags( $t_other_issue_id, array( array( 'id' => $t_tag_id ) ) );
		foreach( array( $t_project_id, $t_other_project_id ) as $t_target_id ) {
			try {
				tag_update( $t_tag_id, $t_name, $t_user_id, '', $t_target_id );
				$this->fail( 'Tag used in two projects should stay global.' );
			} catch( \Mantis\Exceptions\ClientException $e ) {
				$this->assertSame( ERROR_TAG_USED_IN_OTHER_PROJECTS, $e->getCode() );
			}
		}

		# Moving can't create a duplicate name in the target scope
		$t_project_tag_id = $this->createTag( $t_name . '-p', $t_project_id );
		$this->createTag( $t_name . '-p', ALL_PROJECTS );
		try {
			tag_update( $t_project_tag_id, $t_name . '-p', $t_user_id, '', ALL_PROJECTS );
			$this->fail( 'Duplicate name in the target scope should be rejected.' );
		} catch( \Mantis\Exceptions\ClientException $e ) {
			$this->assertSame( ERROR_TAG_DUPLICATE, $e->getCode() );
		}
	}

	/**
	 * Moving an issue to another project keeps the tags available there, maps the other
	 * ones by name to the tags of the target project, and removes those without a match.
	 *
	 * @return void
	 */
	public function testMovingIssueMapsOrRemovesProjectTags(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_project_id = $this->createProject();
		$t_target_project_id = $this->createProject();
		$t_issue_id = $this->createIssue( $t_project_id );
		$t_global_id = $this->createTag( $t_name . '-global', ALL_PROJECTS );
		$t_mapped_id = $this->createTag( $t_name . '-mapped', $t_project_id );
		$t_removed_id = $this->createTag( $t_name . '-removed', $t_project_id );
		$t_target_tag_id = $this->createTag( $t_name . '-mapped', $t_target_project_id );
		# a global tag with the name of a project tag also counts as a tag with the same name
		$t_shadowed_id = $this->createTag( $t_name . '-shadowed', $t_project_id );
		$t_global_shadow_id = $this->createTag( $t_name . '-shadowed', ALL_PROJECTS );

		$this->attachTags( $t_issue_id, array(
			array( 'id' => $t_global_id ),
			array( 'id' => $t_mapped_id ),
			array( 'id' => $t_removed_id ),
			array( 'id' => $t_shadowed_id ),
		) );

		bug_move( $t_issue_id, $t_target_project_id );
		tag_clear_cache_bug_tags();

		$t_attached = array();
		foreach( tag_bug_get_attached( $t_issue_id ) as $t_tag_row ) {
			$t_attached[$t_tag_row['name']] = (int)$t_tag_row['id'];
		}
		$this->assertSame( array(
			$t_name . '-global' => $t_global_id,
			$t_name . '-mapped' => $t_target_tag_id,
			$t_name . '-shadowed' => $t_global_shadow_id,
		), $t_attached );
		# the tag without match is not recreated in the target project
		$this->assertFalse( tag_get_by_name( $t_name . '-removed', $t_target_project_id ) );
	}

	/**
	 * Subprojects use the tags of their parent projects, nearest first, then global tags.
	 * Tags of subprojects are not available to the parent project.
	 *
	 * @return void
	 */
	public function testSubprojectsInheritParentProjectTags(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_parent_id = $this->createProject();
		$t_child_id = $this->createProject();
		$this->addSubproject( $t_child_id, $t_parent_id );
		$t_issue_id = $this->createIssue( $t_child_id );

		$t_global_id = $this->createTag( $t_name, ALL_PROJECTS );
		$t_parent_tag_id = $this->createTag( $t_name, $t_parent_id );
		$t_child_only_id = $this->createTag( $t_name . '-child', $t_child_id );

		$this->assertSame( $t_parent_tag_id, (int)tag_get_by_name( $t_name, $t_child_id )['id'] );
		$this->assertTrue( tag_is_available_in_project( $t_parent_tag_id, $t_child_id ) );
		$this->assertFalse( tag_is_available_in_project( $t_child_only_id, $t_parent_id ) );
		$this->assertFalse( tag_get_by_name( $t_name . '-child', $t_parent_id ) );

		# The tag of the child wins over the one of the parent
		$t_child_tag_id = $this->createTag( $t_name, $t_child_id );
		$this->assertSame( $t_child_tag_id, (int)tag_get_by_name( $t_name, $t_child_id )['id'] );
		$this->assertSame( $t_parent_tag_id, (int)tag_get_by_name( $t_name, $t_parent_id )['id'] );
		tag_delete( $t_child_tag_id );

		# Filter candidates for the parent include tags of its subprojects, those of the child include the parent's
		$t_filter_names = array_column( tag_get_candidates_for_filter( $t_parent_id ), 'name' );
		$this->assertContains( $t_name . '-child', $t_filter_names );
		$t_bug_names = array_column( tag_get_candidates_for_bug( 0, $t_parent_id ), 'name' );
		$this->assertNotContains( $t_name . '-child', $t_bug_names );
		$t_child_ids = array_map( 'intval', array_column( tag_get_candidates_for_bug( $t_issue_id ), 'id' ) );
		$this->assertContains( $t_parent_tag_id, $t_child_ids );
		$this->assertNotContains( $t_global_id, $t_child_ids );

		# A tag used by an issue of the subproject can be scoped to the parent, but not the other way around
		$this->attachTags( $t_issue_id, array( array( 'id' => $t_parent_tag_id ) ) );
		$this->assertTrue( tag_can_move_to_project( $t_parent_tag_id, $t_parent_id ) );
		$this->assertFalse( tag_can_move_to_project( $t_parent_tag_id, $this->createProject() ) );
	}

	/**
	 * When the subprojects don't inherit tags, only the tags of the project and global ones are available.
	 *
	 * @return void
	 */
	public function testSubprojectsDoNotInheritTagsWhenDisabled(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_parent_id = $this->createProject();
		$t_child_id = $this->createProject();
		$this->addSubproject( $t_child_id, $t_parent_id );
		$t_parent_tag_id = $this->createTag( $t_name, $t_parent_id );

		config_set( 'subprojects_inherit_tags', OFF, NO_USER, ALL_PROJECTS );
		try {
			$this->assertFalse( tag_get_by_name( $t_name, $t_child_id ) );
			$this->assertFalse( tag_is_available_in_project( $t_parent_tag_id, $t_child_id ) );
		} finally {
			config_delete( 'subprojects_inherit_tags', NO_USER, ALL_PROJECTS );
		}
		$this->assertSame( $t_parent_tag_id, (int)tag_get_by_name( $t_name, $t_child_id )['id'] );
	}

	/**
	 * Run TagCopyCommand and register the created tags for cleanup.
	 *
	 * @param int  $p_source_project_id Project to copy the tags from.
	 * @param int  $p_target_project_id Project to copy the tags to.
	 * @param bool $p_inherited         Whether to also copy the tags inherited by the source project.
	 * @return array Command response
	 */
	private function copyTags( int $p_source_project_id, int $p_target_project_id, bool $p_inherited = true ): array {
		$t_result = ( new \TagCopyCommand( array(
			'query' => array( 'source_project_id' => $p_source_project_id, 'target_project_id' => $p_target_project_id ),
			'payload' => array( 'inherited' => $p_inherited ),
		) ) )->execute();
		foreach( $t_result['tag_ids'] as $t_id ) {
			$this->tagIdsToDelete[] = $t_id;
		}
		tag_clear_cache();
		return $t_result;
	}

	/**
	 * Copying tags between projects copies the tags scoped to the source project, in either direction.
	 *
	 * @return void
	 */
	public function testCopyTagsBetweenProjects(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_source_id = $this->createProject();
		$t_target_id = $this->createProject();
		$this->createTag( $t_name . '-a', $t_source_id );
		$this->createTag( $t_name . '-global', ALL_PROJECTS );
		$this->createTag( $t_name . '-b', $t_target_id );

		$t_result = $this->copyTags( $t_source_id, $t_target_id );
		$this->assertCount( 1, $t_result['tag_ids'] );
		$this->assertSame( array(), $t_result['skipped'] );
		$this->assertSame( $t_target_id, (int)tag_get_by_name( $t_name . '-a', $t_target_id )['project_id'] );
		$this->assertSame( ALL_PROJECTS, (int)tag_get_by_name( $t_name . '-global', $t_target_id )['project_id'], 'Global tags are not copied.' );

		$t_result = $this->copyTags( $t_target_id, $t_source_id );
		$this->assertCount( 1, $t_result['tag_ids'] );
		$this->assertSame( $t_source_id, (int)tag_get_by_name( $t_name . '-b', $t_source_id )['project_id'] );
	}

	/**
	 * Tags that already exist in the target project are skipped, whatever the case of the name,
	 * and left unchanged, while the other tags are still copied. Copying again changes nothing.
	 *
	 * @return void
	 */
	public function testCopyTagsSkipsTagsThatExistInTargetProject(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_source_id = $this->createProject();
		$t_target_id = $this->createProject();

		$this->createTag( $t_name . '-new', $t_source_id );
		$this->createTag( $t_name . '-same', $t_source_id );
		$this->createTag( $t_name . '-case', $t_source_id );
		$t_target_same_id = $this->createTag( $t_name . '-same', $t_target_id );
		$t_target_case_id = $this->createTag( strtoupper( $t_name ) . '-CASE', $t_target_id );
		tag_update( $t_target_same_id, $t_name . '-same', auth_get_current_user_id(), 'Target description' );

		$t_result = $this->copyTags( $t_source_id, $t_target_id );

		$this->assertCount( 1, $t_result['tag_ids'], 'Only the tag that does not exist yet is created.' );
		$this->assertEqualsCanonicalizing( array( $t_name . '-same', $t_name . '-case' ), $t_result['skipped'] );
		$t_new = tag_get( $t_result['tag_ids'][0] );
		$this->assertSame( $t_name . '-new', $t_new['name'] );
		$this->assertSame( $t_target_id, (int)$t_new['project_id'] );

		# Existing tags keep their identity and content
		$this->assertSame( $t_target_same_id, (int)tag_get_by_name( $t_name . '-same', $t_target_id )['id'] );
		$this->assertSame( 'Target description', tag_get( $t_target_same_id )['description'] );
		$this->assertSame( $t_target_case_id, (int)tag_get_by_name( $t_name . '-case', $t_target_id )['id'] );

		# The target project has exactly one tag of each name
		$t_target_names = array_map( 'mb_strtolower', array_column( tag_get_candidates_for_bug( 0, $t_target_id ), 'name' ) );
		$this->assertSame( count( $t_target_names ), count( array_unique( $t_target_names ) ) );

		# Copying again creates nothing and skips everything
		$t_again = $this->copyTags( $t_source_id, $t_target_id );
		$this->assertSame( array(), $t_again['tag_ids'] );
		$this->assertCount( 3, $t_again['skipped'] );

		# A tag with the same name in the parent or in the global scope does not prevent the copy
		$t_other_id = $this->createProject();
		$this->createTag( $t_name . '-new', ALL_PROJECTS );
		$t_to_other = $this->copyTags( $t_source_id, $t_other_id );
		$this->assertCount( 3, $t_to_other['tag_ids'] );
		$this->assertSame( array(), $t_to_other['skipped'] );
	}

	/**
	 * Copying the tags of a subproject also copies the ones it inherits from its parent projects,
	 * the nearest one if a name is used twice, unless the inherited tags are excluded.
	 * Global tags are never copied.
	 *
	 * @return void
	 */
	public function testCopyTagsIncludesInheritedTagsUnlessExcluded(): void {
		$t_name = 'TagApiTest-' . uniqid();
		$t_parent_id = $this->createProject();
		$t_child_id = $this->createProject();
		$this->addSubproject( $t_child_id, $t_parent_id );
		$this->createTag( $t_name . '-parent', $t_parent_id );
		$this->createTag( $t_name . '-both', $t_parent_id, 'Parent description' );
		$this->createTag( $t_name . '-both', $t_child_id, 'Child description' );
		$this->createTag( $t_name . '-child', $t_child_id );
		$this->createTag( $t_name . '-global', ALL_PROJECTS );

		$t_excluded_id = $this->createProject();
		$t_result = $this->copyTags( $t_child_id, $t_excluded_id, false );
		$this->assertCount( 2, $t_result['tag_ids'] );
		$this->assertFalse( tag_get_by_name( $t_name . '-parent', $t_excluded_id ) );

		$t_included_id = $this->createProject();
		$t_result = $this->copyTags( $t_child_id, $t_included_id );
		$this->assertCount( 3, $t_result['tag_ids'] );
		$this->assertSame( array(), $t_result['skipped'] );
		$this->assertSame( $t_included_id, (int)tag_get_by_name( $t_name . '-parent', $t_included_id )['project_id'] );
		$t_both = tag_get_by_name( $t_name . '-both', $t_included_id );
		$this->assertSame( $t_included_id, (int)$t_both['project_id'] );
		$this->assertSame( 'Child description', $t_both['description'], 'The nearest tag is copied.' );
		$this->assertSame( ALL_PROJECTS, (int)tag_get_by_name( $t_name . '-global', $t_included_id )['project_id'] );
	}
}
