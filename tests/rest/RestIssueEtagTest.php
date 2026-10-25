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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\tests\rest;

use Psr\Http\Message\ResponseInterface;

/**
 * Test conditional REST issue requests using ETags.
 *
 * @requires extension curl
 * @group REST
 */
class RestIssueEtagTest extends RestBase {
	/** @var int */
	private $issue_id;

	/**
	 * Create an issue that each test can safely update or delete.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$t_response = $this->builder()->post( '/issues', $this->getIssueToAdd() )->send();
		$this->issue_id = $this->getJson( $t_response, HTTP_STATUS_CREATED )->issue->id;
		$this->deleteIssueAfterRun( $this->issue_id );
	}

	/**
	 * Get the test issue and assert that the server provided an entity tag.
	 *
	 * @return ResponseInterface
	 */
	private function getIssue(): ResponseInterface {
		$t_response = $this->builder()->get( '/issues/' . $this->issue_id )->send();
		$this->assertSame( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$this->assertNotEmpty( $t_response->getHeaderLine( 'ETag' ) );

		return $t_response;
	}

	/**
	 * Update the test issue summary without an entity tag.
	 *
	 * @param string $p_summary Summary to apply.
	 * @return ResponseInterface
	 */
	private function updateIssue( $p_summary ): ResponseInterface {
		return $this->builder()->patch(
			'/issues/' . $this->issue_id,
			array( 'summary' => $p_summary )
		)->send();
	}

	/**
	 * Issue collection reads provide an ETag as well.
	 *
	 * @return void
	 */
	public function testGetIssuesReturnsEtag(): void {
		$t_response = $this->builder()
			->get( '/issues', 'project_id=' . $this->getProjectId() )
			->send();

		$this->assertSame( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
		$this->assertNotEmpty( $t_response->getHeaderLine( 'ETag' ) );
	}

	/**
	 * A matching If-None-Match value returns an empty 304 response.
	 *
	 * @return void
	 */
	public function testGetIssueWithMatchingIfNoneMatchReturnsNotModified(): void {
		$t_etag = $this->getIssue()->getHeaderLine( 'ETag' );

		$t_response = $this->builder()
			->addHeader( 'If-None-Match', $t_etag )
			->get( '/issues/' . $this->issue_id )
			->send();

		$this->assertSame( HTTP_STATUS_NOT_MODIFIED, $t_response->getStatusCode() );
		$this->assertSame( $t_etag, $t_response->getHeaderLine( 'ETag' ) );
		$this->assertSame( '', (string)$t_response->getBody() );
	}

	/**
	 * A matching If-Match value allows an issue update and returns a new ETag.
	 *
	 * @return void
	 */
	public function testUpdateIssueWithMatchingIfMatchReturnsNewEtag(): void {
		$t_etag = $this->getIssue()->getHeaderLine( 'ETag' );
		$t_summary = $this->getTestName() . ' updated';

		$t_response = $this->builder()
			->addHeader( 'If-Match', $t_etag )
			->patch( '/issues/' . $this->issue_id, array( 'summary' => $t_summary ) )
			->send();

		$t_result = $this->getJson( $t_response );
		$this->assertSame( $t_summary, $t_result->issues[0]->summary );
		$this->assertNotEmpty( $t_response->getHeaderLine( 'ETag' ) );
		$this->assertNotSame( $t_etag, $t_response->getHeaderLine( 'ETag' ) );
	}

	/**
	 * A stale If-Match value rejects an update and leaves the issue unchanged.
	 *
	 * @return void
	 */
	public function testUpdateIssueWithStaleIfMatchReturnsPreconditionFailed(): void {
		$t_stale_etag = $this->getIssue()->getHeaderLine( 'ETag' );
		$t_current_summary = $this->getTestName() . ' current';
		$t_response = $this->updateIssue( $t_current_summary );
		$this->assertSame( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_response = $this->builder()
			->addHeader( 'If-Match', $t_stale_etag )
			->patch( '/issues/' . $this->issue_id, array( 'summary' => $this->getTestName() . ' stale' ) )
			->send();

		$this->assertSame( HTTP_STATUS_PRECONDITION_FAILED, $t_response->getStatusCode() );
		$this->assertNotSame( $t_stale_etag, $t_response->getHeaderLine( 'ETag' ) );

		$t_issue = $this->getJson( $this->getIssue() )->issues[0];
		$this->assertSame( $t_current_summary, $t_issue->summary );
	}

	/**
	 * A stale If-Match value rejects a delete and leaves the issue available.
	 *
	 * @return void
	 */
	public function testDeleteIssueWithStaleIfMatchReturnsPreconditionFailed(): void {
		$t_stale_etag = $this->getIssue()->getHeaderLine( 'ETag' );
		$t_response = $this->updateIssue( $this->getTestName() . ' current' );
		$this->assertSame( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );

		$t_response = $this->builder()
			->addHeader( 'If-Match', $t_stale_etag )
			->delete( '/issues/' . $this->issue_id )
			->send();

		$this->assertSame( HTTP_STATUS_PRECONDITION_FAILED, $t_response->getStatusCode() );
		$this->assertNotSame( $t_stale_etag, $t_response->getHeaderLine( 'ETag' ) );
		$this->getIssue();
	}

	/**
	 * A matching If-Match value allows an issue delete.
	 *
	 * @return void
	 */
	public function testDeleteIssueWithMatchingIfMatchReturnsNoContent(): void {
		$t_etag = $this->getIssue()->getHeaderLine( 'ETag' );

		$t_response = $this->builder()
			->addHeader( 'If-Match', $t_etag )
			->delete( '/issues/' . $this->issue_id )
			->send();

		$this->assertSame( HTTP_STATUS_NO_CONTENT, $t_response->getStatusCode() );
		$this->assertNotEmpty( $t_response->getHeaderLine( 'ETag' ) );
	}
}
