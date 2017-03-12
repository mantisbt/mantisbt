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

$app->group('/issues', function() use ( $app ) {
	$app->get( '', 'rest_issue_get' );
	$app->get( '/', 'rest_issue_get' );
	$app->post( '', 'rest_issue_add' );
	$app->post( '/', 'rest_issue_add' );
	$app->delete( '', 'rest_issue_delete' );
	$app->delete( '/', 'rest_issue_delete' );
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

/**
 * Create an issue from a POST to the issues url.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue = $p_request->getParsedBody();

	$t_result = mc_issue_add( /* username */ '', /* password */ '', $t_issue );
	if( ApiObjectFactory::isFault( $t_result ) ) {
		return $p_response->withStatus( 400, $t_result->faultstring );
	}

	$t_issue_id = $t_result;

	$t_created_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );

	return $p_response->withStatus( 201, "Issue Created with id $t_issue_id" )->withJson( array( 'issue' => $t_created_issue ) );
}

/**
 * Delete an issue given its id.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = $p_request->getParam( 'id' );

	# Username and password below are ignored, since middleware already done the auth.
	$t_result = mc_issue_delete( /* username */ '', /* password */ '', $t_issue_id );

	# Dependency on SoapFault can be removed by refactoring mc_* code.
	if( ApiObjectFactory::isFault( $t_result ) ) {
		if( !bug_exists( $t_issue_id ) ) {
			return $p_response->withStatus( 404, "Issue '$t_issue_id' doesn't exist." );
		}

		return $p_response->withStatus( 403, $t_result->faultstring );
	}

	return $p_response->withStatus( 200 );
}

