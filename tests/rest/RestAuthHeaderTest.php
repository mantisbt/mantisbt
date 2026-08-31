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

/**
 * End-to-end tests for the Authorization header formats accepted by the
 * REST API.
 *
 * The API token may be sent bare ('Authorization: <token>') or using the
 * RFC 6750 bearer scheme ('Authorization: Bearer <token>'). Both must be
 * equivalent, and neither must open a distinct failure path for bad tokens.
 *
 * @requires extension curl
 * @group REST
 */
class RestAuthHeaderTest extends RestBase {

	/**
	 * An endpoint that requires authentication and is cheap to call.
	 */
	const ENDPOINT = '/issues';

	/**
	 * The bare token form must keep working (no regression).
	 * @return void
	 */
	public function testBareTokenIsAccepted() {
		$t_response = $this->builder()
			->token( $this->token )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * The bearer form must be accepted, case-insensitively and tolerating
	 * extra whitespace after the scheme.
	 *
	 * @dataProvider providerBearerPrefixes
	 * @param string $p_prefix The scheme prefix to prepend to the token.
	 * @return void
	 */
	public function testBearerPrefixedTokenIsAccepted( $p_prefix ) {
		$t_response = $this->builder()
			->token( $p_prefix . $this->token )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * Bearer scheme spellings that must all be accepted.
	 * @return array
	 */
	public static function providerBearerPrefixes() {
		return array(
			'Bearer' => array( 'Bearer ' ),
			'bearer lowercase' => array( 'bearer ' ),
			'BEARER uppercase' => array( 'BEARER ' ),
			'BeArEr mixed case' => array( 'BeArEr ' ),
			'extra spaces' => array( 'Bearer    ' ),
		);
	}

	/**
	 * A bearer-prefixed token must still be found when the Authorization
	 * header carries multiple comma separated credentials, whether or not the
	 * comma is followed by a space (RFC 7230 allows optional whitespace).
	 *
	 * @dataProvider providerMultipleCredentials
	 * @param string $p_separator The separator placed between the two credentials.
	 * @return void
	 */
	public function testBearerTokenAmongMultipleCredentials( $p_separator ) {
		$t_response = $this->builder()
			->token( 'Basic dXNlcjpwYXNz' . $p_separator . 'Bearer ' . $this->token )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$this->assertEquals( HTTP_STATUS_SUCCESS, $t_response->getStatusCode() );
	}

	/**
	 * Credential separators that must all be tolerated.
	 * @return array
	 */
	public static function providerMultipleCredentials() {
		return array(
			'comma space' => array( ', ' ),
			'comma only' => array( ',' ),
			'comma multiple spaces' => array( ',   ' ),
		);
	}

	/**
	 * An invalid token must be handled identically whether or not it carries a
	 * bearer prefix, so the prefixed form cannot be used to probe for a
	 * different code path.
	 *
	 * The outcome itself depends on whether anonymous access is enabled on the
	 * instance under test, so this asserts equivalence rather than a fixed
	 * status code.
	 *
	 * @return void
	 */
	public function testInvalidTokenBehavesIdenticallyWithAndWithoutBearer() {
		# Same length as a real token, so both requests reach the same lookup.
		$t_invalid_token = str_repeat( 'z', API_TOKEN_LENGTH );

		$t_bare = $this->builder()
			->token( $t_invalid_token )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$t_bearer = $this->builder()
			->token( 'Bearer ' . $t_invalid_token )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$this->assertEquals( $t_bare->getStatusCode(), $t_bearer->getStatusCode() );
		$this->assertEquals( $t_bare->getReasonPhrase(), $t_bearer->getReasonPhrase() );
		$this->assertEquals( (string)$t_bare->getBody(), (string)$t_bearer->getBody() );
	}

	/**
	 * A request carrying no credentials at all must behave according to the
	 * instance's anonymous access configuration.
	 *
	 * - {@see $g_allow_anonymous_login} ON: the request is served as the
	 *   anonymous user, so no 401 is produced and no challenge is issued.
	 * - {@see $g_allow_anonymous_login} OFF: a 401 is produced, and RFC 7235
	 *   requires it to advertise the accepted scheme via 'WWW-Authenticate'.
	 *
	 * Both scenarios are asserted, so the test is meaningful whichever way the
	 * instance under test is configured.
	 *
	 * @return void
	 */
	public function testAnonymousRequestHonoursAnonymousLoginConfig() {
		$t_response = $this->builder()
			->anonymous()
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		if( auth_anonymous_enabled() ) {
			# The anonymous account may still lack access to the endpoint, which
			# is a 403; the point is that no authentication challenge is issued.
			$this->assertNotEquals( HTTP_STATUS_UNAUTHORIZED, $t_response->getStatusCode() );
			$this->assertEmpty( $t_response->getHeaderLine( 'WWW-Authenticate' ) );
		} else {
			$this->assertEquals( HTTP_STATUS_UNAUTHORIZED, $t_response->getStatusCode() );
			$this->assertEquals( 'Bearer', $t_response->getHeaderLine( 'WWW-Authenticate' ) );
		}
	}

	/**
	 * Credentials using another scheme are not treated as an API token: a
	 * 'Basic' header is handled exactly like an unrecognised token.
	 * @return void
	 */
	public function testOtherSchemeIsNotTreatedAsToken() {
		$t_basic = $this->builder()
			->token( 'Basic ' . base64_encode( $this->userName . ':' . $this->password ) )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$t_invalid = $this->builder()
			->token( str_repeat( 'z', API_TOKEN_LENGTH ) )
			->get( self::ENDPOINT, 'page_size=1' )
			->send();

		$this->assertEquals( $t_invalid->getStatusCode(), $t_basic->getStatusCode() );
		$this->assertEquals( $t_invalid->getReasonPhrase(), $t_basic->getReasonPhrase() );
	}
}
