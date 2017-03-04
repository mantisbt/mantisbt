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

require_once( __DIR__ . '/AuthMiddleware.php' );

$app->group('/issues', function() use ( $app ) {
	# Path with URL re-writing: http://.../mantisbt/api/rest/issues/1234
	# Path without URL re-writing: http://.../mantisbt/api/rest/index.php/issues/1234
	$app->get( '', 'rest_issue_get' );
	$app->get( '/', 'rest_issue_get' );
});

/**
 * A method that does the work to handle getting an issue via REST API.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	# Username and password below are ignored, since middleware already done the auth.
	$t_result = mc_issue_get( /* username */ '', /* password */ '', $p_request->getParam( 'id' ) );

	# Dependency on SoapFault can be removed by refactoring mc_* code.
	if( ApiObjectFactory::isFault( $t_result ) ) {
		return $p_response->withStatus( 404, $t_result->faultstring );
	}

	return $p_response->withStatus( 200 )->withJson( $t_result );
}


