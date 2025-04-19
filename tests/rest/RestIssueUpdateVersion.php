<?php
namespace Mantis\tests\rest;

use Generator;
use stdClass;

require_once 'RestIssueTest.php';

class RestIssueUpdateVersion extends RestBase
{
	const VERSION_FIELDS = ['version', 'target_version', 'fixed_in_version'];

	/** @var int Version id */
	protected $version_id;

	/** @var int Issue id */
	protected $issue_id;

	/** @var string REST API endpoint for project version */
	protected string $endpoint_version;
	protected string $endpoint_issue = '/issues/';

	/**
	 * Checks that response was successful and returns JSON body as object.
	 *
	 * @param ResponseInterface $p_response
	 * @param int               $p_status_code Expected HTTP status code
	 *
	 * @return stdClass
	 */
	protected function getJson( ResponseInterface $p_response, $p_status_code = HTTP_STATUS_SUCCESS ) {
		$this->assertEquals( $p_status_code,
			$p_response->getStatusCode(),
			"REST API returned unexpected Status Code"
		);
		return json_decode( $p_response->getBody(), false );
	}

	/**
	 * Get the test Issue's current state.
	 *
	 * Checks that the Version fields are present.
	 *
	 * @return stdClass Issue Data from GET /issues endpoint
	 */
	protected function getTestIssue(): stdClass {
		$t_response = $this->builder()->get( $this->endpoint_issue )->send();
		$t_issue = $this->getJson( $t_response )->issues[0];

		foreach( self::VERSION_FIELDS as $t_version_field ) {
			$this->assertObjectHasProperty( $t_version_field, $t_issue, "'$t_version_field' is set" );
			$this->assertEquals( $this->version_id,
				$t_issue->$t_version_field->id,
				"'$t_version_field' id does not match"
			);
		}

		return $t_issue;
	}

	/**
	 * Test Setup - creates a test Version and Issue.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->endpoint_version = "/projects/$this->projectId/versions/";

		# Add a test Version
		$t_version_data = [
			"name" => self::class . "_v" . rand(1, 9) . "." . rand(0, 99),
			"description" => self::class . " test version",
			"released" => true,
		];
		$t_response = $this->builder()->post( $this->endpoint_version, $t_version_data )->send();
		$t_version = $this->getJson($t_response, HTTP_STATUS_CREATED )->version;
		$this->version_id = $t_version->id;
		$this->deleteAfterRunVersion($t_version->id);

		# Create a test Issue with the version fields set
		$t_version_data = ['id' => $this->version_id];
		$t_issue_data = array_merge(
			$this->getIssueToAdd(),
			array_fill_keys( self::VERSION_FIELDS, $t_version_data)
		);
		$t_response = $this->builder()->post( '/issues', $t_issue_data )->send();
		$t_issue = $this->getJson($t_response, HTTP_STATUS_CREATED )->issue;
		$this->issue_id = $t_issue->id;
		$this->endpoint_issue .= $t_issue->id;
		$this->deleteIssueAfterRun($t_issue->id);
	}

	/**
	 * Testing successful unsetting of version fields.
	 *
	 * @dataProvider providerUnsetVersionSuccess
	 *
	 * @param array|null|int $p_version_payload
	 * @param int            $p_expected_status_code
	 *
	 * @return void
	 */
	public function testUnsetVersion( $p_version_payload, int $p_expected_status_code ): void {
		$this->getTestIssue();

		# Update the Issue and make sure the version fields are gone
		$t_payload = array_fill_keys( self::VERSION_FIELDS, $p_version_payload );
		$t_response = $this->builder()->patch( $this->endpoint_issue, $t_payload )->send();
		$t_issue = $this->getJson( $t_response, $p_expected_status_code )->issues[0];
		foreach( self::VERSION_FIELDS as $t_version_field ) {
			$this->assertObjectNotHasProperty($t_version_field, $t_issue, "'$t_version_field' was not unset" );
		}
	}

	/**
	 * Testing failing attempts to unset Version fields.
	 *
	 * @dataProvider providerUnsetVersionFailure
	 *
	 * @param array|null|int $p_version_payload
	 * @param int            $p_expected_status_code
	 *
	 * @return void
	 */
	public function testFailToUnsetVersion( $p_version_payload, int $p_expected_status_code ): void {
		$this->getTestIssue();

		# Update the Issue and make sure the version fields are gone
		$t_payload = array_fill_keys( self::VERSION_FIELDS, $p_version_payload );
		$t_response = $this->builder()->patch( $this->endpoint_issue, $t_payload )->send();
		$this->assertEquals(
			$p_expected_status_code,
			$t_response->getStatusCode(),
			"Unexpected status code from REST API"
		);
	}

	/**
	 * Provide a series of valid Payloads to unset a Version field.
	 *
	 * Test case structure:
	 *   <case> => array( <version payload>, <expected status code> )
	 *
	 * @return Generator List of test cases
	 */
	public static function providerUnsetVersionSuccess(): Generator {
		yield 'Version with id 0' => [ ['id' => 0], HTTP_STATUS_SUCCESS ];

		# According to @vboctor these test cases should actually fail, but don't...
		# For now the tests are designed to pass anyway, reflecting current API behavior.
		# See discussion in #25407.
		yield 'Null version' => [ null, HTTP_STATUS_SUCCESS ];
		yield 'Blank version' => [ '', HTTP_STATUS_SUCCESS ];
		yield 'Empty version' => [ [], HTTP_STATUS_SUCCESS ];
		yield 'Version with null name' => [ ['name' => null], HTTP_STATUS_SUCCESS ];
		yield 'Version with blank name' => [ ['name' => ''], HTTP_STATUS_SUCCESS ];

		# @TODO should probably be BAD REQUEST (inconsistent API behavior compared to same values in version array)
		yield 'Non-existing Numeric version' => [ 99999, HTTP_STATUS_SUCCESS ];
		yield 'Invalid Numeric version' => [ -1, HTTP_STATUS_SUCCESS ];

		# @TODO not sure what the correct behavior should be in this case
		yield 'Version with neither id nor name' => [ ['xxx' => 'yyy'], HTTP_STATUS_SUCCESS ];
	}

	/**
	 * Provide a series of failing Payloads to unset a Version field.
	 * @return Generator List of test cases
	 * @see  testUnsetVersion()
	 *
	 * @TODO Currently does not match expected behavior, see comments in {@see providerUnsetVersionSuccess}
	 *
	 * Test case structure:
	 *    <case> => array( <version payload>, <expected status code> )
	 *
	 */
	public static function providerUnsetVersionFailure(): Generator {
		# This is a dummy test case, to avoid a PHPUnit warning when the
		# Provider returns nothing.
		yield 'dummy' => [ ['id' => -1], HTTP_STATUS_BAD_REQUEST ];

		//yield 'Numeric version' => [ 999, HTTP_STATUS_BAD_REQUEST ];
		//yield 'Blank version' => [ '', HTTP_STATUS_BAD_REQUEST ];
		//yield 'Version with blank name' => [ ['name' => ''], HTTP_STATUS_BAD_REQUEST ];
	}
}
