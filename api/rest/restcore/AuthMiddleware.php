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
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_api( 'authentication_api.php' );

/**
 * A middleware class that handles authentication and authorization to access APIs.
 */
class AuthMiddleware {
	public function __invoke( \Slim\Http\Request $request, \Slim\Http\Response $response, callable $next ) {
		if( mci_is_mantis_offline() ) {
			return $response->withStatus( 503, 'Mantis Offline' );
		}

		$t_authorization_header = $request->getHeaderLine( 'Authorization' );

		$t_password = '';
		$t_login_method = '';

		if( empty( $t_authorization_header ) ) {
			$t_username = config_get( 'anonymous_account' );

			if( config_get( 'allow_anonymous_login' ) == OFF || empty( $t_username ) ) {
				return $response->withStatus( 403, 'API token required' );
			}

			$t_login_method = 'anonymous';
		} else {
			# TODO: add an index on the token hash for the method below
			$t_user_id = api_token_get_user( $t_authorization_header );
			if( $t_user_id === false ) {
				$t_user_id = auth_user_id_from_cookie( $t_authorization_header );
				if( $t_user_id === false ) {
					return $response->withStatus( 403, 'API token not found' );
				}

				# use cookie to login, useful for calls from web API.
				$t_login_method = 'cookie';
				$t_password = $t_authorization_header;
			} else {
				# use api token
				$t_login_method = 'api-token';
				$t_password = $t_authorization_header;
			}

			$t_username = user_get_name( $t_user_id );
		}

		if( mci_check_login( $t_username, $t_password ) === false ) {
			return $response->withStatus( 403, 'Access denied' );
		}

		return $next( $request, $response )->withHeader( 'X-Mantis-Username', $t_username )->
			withHeader( 'X-Mantis-LoginMethod', $t_login_method );
	}
}
