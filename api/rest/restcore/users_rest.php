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
 *
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

/**
 * @var \Slim\App $g_app
 */
$g_app->group('/users', function() use ( $g_app ) {
	$g_app->get( '/me', 'rest_user_get_me' );

	$g_app->post( '/', 'rest_user_create' );
	$g_app->post( '', 'rest_user_create' );

	$g_app->delete( '/{id}', 'rest_user_delete' );
	$g_app->delete( '/{id}/', 'rest_user_delete' );

	$g_app->put( '/{id}/reset', 'rest_user_reset_password' );
});

/**
 * A method that does the work to get information about current logged in user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_get_me( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_result = mci_user_get( auth_get_current_user_id() );
	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}

/**
 * A method that creates a user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_create( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_payload = $p_request->getParsedBody();
	if( !$t_payload ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid request body or format");
	}

	$t_data = array( 'payload' => $t_payload );
	$t_command = new UserCreateCommand( $t_data );
	$t_result = $t_command->execute();
	$t_user_id = $t_result['id'];

	return $p_response->withStatus( HTTP_STATUS_CREATED, "User created with id $t_user_id" )->
		withJson( array( 'user' => mci_user_get( $t_user_id ) ) );
}

/**
 * Delete a user given its id.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_user_id = $p_args['id'];

	$t_data = array(
		'query' => array( 'id' => $t_user_id )
	);

	$t_command = new UserDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT );
}

/**
 * Reset a user's password given its id.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_reset_password( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_user_id = $p_args['id'];

	$t_data = array(
		'query' => array( 'id' => $t_user_id )
	);

	$t_command = new UserResetPasswordCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT );
}
