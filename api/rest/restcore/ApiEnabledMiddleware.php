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

/**
 * A middleware class that handles checks for REST API being enabled.
 */
class ApiEnabledMiddleware {
	public function __invoke( \Slim\Http\Request $request, \Slim\Http\Response $response, callable $next ) {
		$t_force_enable = $request->getAttribute( ATTRIBUTE_FORCE_API_ENABLED );

		# If request is coming from UI, then force enable will be true, hence, request shouldn't be blocked
		# even if API is disabled.
		if( !$t_force_enable ) {
			if( config_get( 'webservice_rest_enabled' ) == OFF ) {
				return $response->withStatus( HTTP_STATUS_UNAVAILABLE, 'API disabled' );
			}
		}

		return $next( $request, $response );
	}
}
