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
	# These 4 cases are just to avoid html errors in case of incomplete urls
	$g_app->get( '', 'rest_user_get' );
	$g_app->get( '/', 'rest_user_get' );
	$g_app->get( '/username/', 'rest_user_get' );
	$g_app->get( '/username', 'rest_user_get' );

	# This are the real cases for get users
	$g_app->get( '/me', 'rest_user_get_me' );
	$g_app->get( '/username/{username}', 'rest_user_get' );
	$g_app->get( '/{user_id}', 'rest_user_get' );

	$g_app->post( '/', 'rest_user_create' );
	$g_app->post( '', 'rest_user_create' );

	$g_app->patch( '/{id}', 'rest_user_update' );

	$g_app->post( '/me/token/', 'rest_user_create_token_for_current_user' );
	$g_app->post( '/me/token', 'rest_user_create_token_for_current_user' );
	$g_app->post( '/{user_id}/token/', 'rest_user_create_token' );
	$g_app->post( '/{user_id}/token', 'rest_user_create_token' );

	$g_app->delete( '/me/token/{token_id}/', 'rest_user_delete_token_for_current_user' );
	$g_app->delete( '/me/token/{token_id}', 'rest_user_delete_token_for_current_user' );
	$g_app->delete( '/{user_id}/token/{token_id}/', 'rest_user_delete_token' );
	$g_app->delete( '/{user_id}/token/{token_id}', 'rest_user_delete_token' );

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
	$t_select = $p_request->getParam( 'select', null );
	if( is_null( $t_select ) ) {
		$t_select = UserGetCommand::getDefaultFields();

		// The `project` field is part of this API's response, but it is not longer a default field
		// for newer APIs to get user information, so add it here for backward compatibility.
		if( !in_array( 'projects', $t_select ) ) {
			$t_select[] = 'projects';
		}
	} else {
		$t_select = explode( ',', $t_select );
	}

	$t_data = array(
		'query' => array(
			'user_id' => auth_get_current_user_id(),
			'select' => $t_select
		),
		'options' => array(
			'return_as_users' => false
		)
	);

	$t_command = new UserGetCommand( $t_data );
	$t_result = $t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}

/**
 * A method that does the work to get information about the specified user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 */
function rest_user_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_query = array();

	$t_user_id = isset( $p_args['user_id'] ) ? $p_args['user_id'] : null;
	if( !is_null( $t_user_id ) ) {
		$t_query['user_id'] = (int)$t_user_id;
	}

	$t_username = isset( $p_args['username'] ) ? $p_args['username'] : null;
	if( !is_null( $t_username ) ) {
		$t_query['username'] = $t_username;
	}

	$t_select = $p_request->getParam( 'select', null );
	if( !is_null( $t_select ) ) {
		$t_select = explode( ',', $t_select );
		$t_query['select'] = $t_select;
	}

	$t_data = array(
		'query' => $t_query,
		'options' => array(
			'return_as_users' => true
		)
	);

	$t_command = new UserGetCommand( $t_data );
	$t_result = $t_command->execute();

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
 * A method that updates a user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_query = array();

	$t_user_id = isset( $p_args['id'] ) ? $p_args['id'] : null;
	if( !is_null( $t_user_id ) ) {
		$t_query['user_id'] = (int)$t_user_id;
	}

	$t_payload = $p_request->getParsedBody();
	if( !$t_payload ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid request body or format");
	}

	$t_data = array(
		'query' => $t_query,
		'payload' => $t_payload
	);

	$t_command = new UserUpdateCommand( $t_data );
	$t_result = $t_command->execute();
	$t_user_id = $t_result['user']['id'];

	return $p_response
		->withStatus( HTTP_STATUS_SUCCESS, "User with id $t_user_id updated" )
		->withJson( $t_result );
}

/**
 * A method that creates a user token for another user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_create_token( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_user_id = $p_args['user_id'];
	return execute_create_token_command( $p_request, $p_response, $t_user_id );
}

/**
 * A method that creates a user token for current user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_create_token_for_current_user( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_user_id = auth_get_current_user_id();
	return execute_create_token_command( $p_request, $p_response, $t_user_id );
}

/**
 * Helper method for creation of user tokens
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param integer $p_user_id The id of the user to create token for.
 *
 * @return \Slim\Http\Response The augmented response.
 */
function execute_create_token_command( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, $p_user_id ) {
	// if body is empty or {} it will fail, this is acceptable for this API.
	$t_payload = $p_request->getParsedBody();
	if( !$t_payload ) {
		$t_payload = array();
	}

	$t_data = array(
		'query' => array(
			'user_id' => (int)$p_user_id
		),
		'payload' => $t_payload
	);

	$t_command = new UserTokenCreateCommand( $t_data );
	$t_result = $t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_CREATED, "User token created" )->
		withJson( $t_result );
}

/**
 * A method that deletes a user token for another user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_delete_token( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_token_id = $p_args['token_id'];
	$t_user_id = $p_args['user_id'];
	return execute_delete_token_command( $p_request, $p_response, $t_user_id, $t_token_id );
}

/**
 * A method that deletes a user token for current user.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_user_delete_token_for_current_user( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_token_id = $p_args['token_id'];
	$t_user_id = auth_get_current_user_id();
	return execute_delete_token_command( $p_request, $p_response, $t_user_id, $t_token_id );
}

/**
 * Helper method for creation of user tokens
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param integer $p_user_id The id of the user to create token for.
 *
 * @return \Slim\Http\Response The augmented response.
 */
function execute_delete_token_command( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, $p_user_id, $p_token_id ) {
	$t_data = array(
		'query' => array(
			'id' => $p_token_id,
			'user_id' => (int)$p_user_id
		)
	);

	$t_command = new UserTokenDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT, "User token deleted" );
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
