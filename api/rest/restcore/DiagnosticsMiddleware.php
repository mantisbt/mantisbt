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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

use \Slim\Http\Request as SlimRequest;
use \Slim\Http\Response as SlimResponse;

/**
 * A middleware class for diagnostics.
 */
class DiagnosticsMiddleware {
	public function __invoke( SlimRequest $request, SlimResponse $response, callable $next ) {
		$t_response = $next( $request, $response );

		if( auth_is_user_authenticated() && config_get_global( 'show_queries_count' ) ) {
			global $g_queries_array, $g_request_time, $g_db_log_queries;
			$t_response = $t_response->withHeader( HEADER_QUERIES_COUNT, count( $g_queries_array ) );
			$t_response = $t_response->withHeader( HEADER_EXECUTION_TIME, number_format( microtime( true ) - $g_request_time, 4 ) );

			if( $g_db_log_queries && current_user_is_administrator() ) {
				$t_response = $t_response->withHeader( HEADER_QUERIES, json_encode( $g_queries_array ) );
			}
		}

		return $t_response;
	}
}
