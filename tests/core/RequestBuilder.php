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


/**
 * A builder class for test case requests.
 */
class RequestBuilder {
	/**
	 * The API base URL
	 */
	private $base_url;

	/**
	 * The authentication API token
	 */
	private $token;

	/**
	 * The HTTP method to use
	 */
	private $method;

	/**
	 * The relative path to the API endpoint
	 */
	private $relative_path;

	/**
	 * The request body
	 */
	private $body;

	/**
	 * The request headers as associative array
	 */
	private $headers;

	/**
	 * Constructor
	 *
	 * @param string $p_base_url The API base URL
	 * @param string $p_token    The authentication API token
	 */
	public function __construct( $p_base_url, $p_token ) {
		$this->base_url = $p_base_url;
		$this->token = $p_token;

		$this->method = 'GET';
		$this->relative_path = '';
		$this->body = '';
		$this->headers = array();
	}

	/**
	 * Set the authentication API token
	 *
	 * @param string $p_token The authentication API token
	 * @return RequestBuilder
	 */
	public function token( $p_token ) {
		$this->token = $p_token;
		return $this;
	}

	/**
	 * Set the authentication API token to null
	 *
	 * @return RequestBuilder
	 */
	public function anonymous() {
		$this->token = null;
		return $this;
	}

	/**
	 * Impersonates the specified user
	 *
	 * @param string $p_username The username of user to impersonate
	 * @return RequestBuilder
	 */
	public function impersonate( $p_username ) {
		$this->headers['X-Mantis-Username'] = $p_username;
		return $this;
	}

	/**
	 * Build a GET request with optional query string
	 *
	 * @param string|null $p_query_string The query string or null.
	 * @return RequestBuilder
	 */
	public function get( $p_relative_path, $p_query_string = null ) {
		$this->method = 'GET';
		$this->relative_path = $p_relative_path;
		if( !empty( $p_query_string ) ) {
			$this->relative_path .= '?' . $p_query_string;
		}

		return $this;
	}

	/**
	 * Build a PUT request with body and optional query string
	 *
	 * @param array $p_body The request body
	 * @param string|null $p_query_string The query string or null.
	 * @return RequestBuilder
	 */
	public function put( $p_relative_path, $p_body, $p_query_string = null ) {
		$this->method = 'PUT';
		$this->relative_path = $p_relative_path;

		if( !empty( $p_query_string ) ) {
			$this->relative_path .= '?' . $p_query_string;
		}

		$this->body = $p_body;
		return $this;
	}

	/**
	 * Build a POST request with body and optional query string
	 *
	 * @param array $p_body The request body
	 * @param string|null $p_query_string The query string or null.
	 * @return RequestBuilder
	 */
	public function post( $p_relative_path, $p_body, $p_query_string = null ) {
		$this->method = 'POST';
		$this->relative_path = $p_relative_path;

		if( !empty( $p_query_string ) ) {
			$this->relative_path .= '?' . $p_query_string;
		}

		$this->body = $p_body;
		return $this;
	}

	/**
	 * Build a PATCH request with body and optional query string
	 *
	 * @param array $p_body The request body
	 * @param string|null $p_query_string The query string or null.
	 * @return RequestBuilder
	 */
	public function patch( $p_relative_path, $p_body, $p_query_string = null ) {
		$this->method = 'PATCH';
		$this->relative_path = $p_relative_path;

		if( !empty( $p_query_string ) ) {
			$this->relative_path .= '?' . $p_query_string;
		}

		$this->body = $p_body;
		return $this;
	}

	/**
	 * Build a DELETE request with optional query string
	 *
	 * @param string|null $p_query_string The query string or null.
	 * @return RequestBuilder
	 */
	public function delete( $p_relative_path, $p_query_string = null ) {
		$this->method = 'DELETE';
		$this->relative_path = $p_relative_path;

		if( !empty( $p_query_string ) ) {
			$this->relative_path .= '?' . $p_query_string;
		}

		return $this;
	}

	/**
	 * Add a request header
	 *
	 * @param string $p_name  The header name
	 * @param string $p_value The header value
	 *
	 * @return RequestBuilder
	 * @noinspection PhpUnused
	 */
	public function addHeader( $p_name, $p_value ) {
		$this->headers[$p_name] = $p_value;
		return $this;
	}

	/**
	 * Send the request
	 *
	 * @return Psr\Http\Message\ResponseInterface The response
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function send() {
		$t_headers = array(
			'User-Agent' => 'MantisTests'
		);

		if( !empty( $this->token ) ) {
			$t_headers['Authorization'] = $this->token;
		}

		foreach( $this->headers as $t_key => $t_value ) {
			$t_headers[$t_key] = $t_value;
		}

		$t_options = array(
			'allow_redirects' => false,
			'http_errors' => false,
			'headers' => $t_headers
		);

		if( !empty( $this->body ) ) {
			$t_options['json'] = $this->body;
		}

		$t_url = rtrim( $this->base_url, '/' ) . '/' . ltrim( $this->relative_path, '/' );

		$t_client = new GuzzleHttp\Client();
		/** @noinspection PhpUnhandledExceptionInspection */
		return $t_client->request( $this->method, $t_url, $t_options );
	}
}
