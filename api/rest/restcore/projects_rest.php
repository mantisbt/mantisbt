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

use Mantis\Exceptions\ClientException;

/**
 * @var \Slim\App $g_app
 */
$g_app->group('/projects', function() use ( $g_app ) {
	$g_app->get( '', 'rest_projects_get' );
	$g_app->get( '/', 'rest_projects_get' );
	$g_app->get( '/{id}', 'rest_projects_get' );
	$g_app->get( '/{id}/', 'rest_projects_get' );

	$g_app->post( '', 'rest_project_add' );
	$g_app->post( '/', 'rest_project_add' );
	$g_app->patch( '/{id}', 'rest_project_update' );
	$g_app->patch( '/{id}/', 'rest_project_update' );

	$g_app->delete( '/{id}', 'rest_project_delete' );
	$g_app->delete( '/{id}/', 'rest_project_delete' );

	# Project versions
	$g_app->get( '/{id}/versions', 'rest_project_version_get' );
	$g_app->get( '/{id}/versions/', 'rest_project_version_get' );
	$g_app->get( '/{id}/versions/{version_id}', 'rest_project_version_get' );
	$g_app->get( '/{id}/versions/{version_id}/', 'rest_project_version_get' );
	$g_app->post( '/{id}/versions', 'rest_project_version_add' );
	$g_app->post( '/{id}/versions/', 'rest_project_version_add' );
	$g_app->patch( '/{id}/versions/{version_id}', 'rest_project_version_update' );
	$g_app->patch( '/{id}/versions/{version_id}/', 'rest_project_version_update' );
	$g_app->delete( '/{id}/versions/{version_id}', 'rest_project_version_delete' );
	$g_app->delete( '/{id}/versions/{version_id}/', 'rest_project_version_delete' );	

	# Project hierarchy (subprojects)
	$g_app->post( '/{id}/subprojects', 'rest_project_hierarchy_add' );
	$g_app->post( '/{id}/subprojects/', 'rest_project_hierarchy_add' );
	$g_app->patch( '/{id}/subprojects/{subproject_id}', 'rest_project_hierarchy_update' );
	$g_app->patch( '/{id}/subprojects/{subproject_id}/', 'rest_project_hierarchy_update' );
	$g_app->delete( '/{id}/subprojects/{subproject_id}', 'rest_project_hierarchy_delete' );
	$g_app->delete( '/{id}/subprojects/{subproject_id}/', 'rest_project_hierarchy_delete' );

	# Project Users
	$g_app->group( '/{id}/users', function() use ( $g_app ) {
		$g_app->post( '[/]', 'rest_project_user_add' );
		$g_app->put( '[/]', 'rest_project_user_add' );
		$g_app->get( '[/]', 'rest_project_users' );
		$g_app->delete( '/{user_id}[/]', 'rest_project_user_delete' );
	});

	# Project Users that can handle issues
	$g_app->get( '/{id}/handlers', 'rest_project_handlers' );
});

/**
 * A helper function to get project users with the specified access level or above.
 * The function will extract the parameters from the request and args.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @param int $p_access_level access level to use or null to extract from request.
 * @return \Slim\Http\Response The augmented response.
 */
function project_users( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args, $p_access_level = null ) {
	$t_project_id = (int)$p_args['id'];
	$t_page_size = $p_request->getParam( 'page_size' );
	$t_page = $p_request->getParam( 'page' );
	$t_include_access_levels = $p_request->getParam( 'include_access_levels' );

	if( is_null( $p_access_level ) ) {
		$t_access_level = (int)$p_request->getParam( 'access_level' );
	} else {
		$t_access_level = (int)$p_access_level;
	}

	$t_data = array(
		'query' => array(
			'id'        => $t_project_id,
			'page_size' => $t_page_size,
			'page'      => $t_page,
			'access_level' => $t_access_level,
			'include_access_levels' => $t_include_access_levels
		)
	);

	$t_command = new ProjectUsersGetCommand( $t_data );
	$t_result = $t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}

/**
 * A method to get list of users with the specified access level in the specified project.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_users( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	return project_users( $p_request, $p_response, $p_args );
}

/**
 * A method to get list of users with the handler access level in the specified project.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_handlers(\Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = (int)$p_args['id'];
	$t_access_level = config_get( 'handle_bug_threshold', null, null, $t_project_id );
	return project_users( $p_request, $p_response, $p_args, $t_access_level );
}

/**
 * A method to add user to a project with specified access level. If user already has access to the project,
 * their access level will be updated.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_user_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = (int)$p_args['id'];

	$t_payload = $p_request->getParsedBody();
	if( !$t_payload ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid request body or format");
	}

	$t_payload['project'] = array( 'id' => $t_project_id );

	$t_data = array(
		'payload' => $t_payload
	);

	$t_command = new ProjectUsersAddCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT );
}

/**
 * A method to remove user access to a project.
 *
 * @param \Slim\Http\Request  $p_request  The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array               $p_args     Arguments
 * @return \Slim\Http\Response The augmented response.
 *
 * @throws ClientException
 * @noinspection PhpUnusedParameterInspection
 */
function rest_project_user_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = (int)$p_args['id'];

	# a user id or 0 to delete all users, don't cast right away, just in case an invalid value is passed
	# that can cast to 0.
	$t_user = $p_args['user_id'];
	if( !is_numeric( $t_user ) ) {
		throw new ClientException( 'Invalid user id', ERROR_INVALID_FIELD_VALUE, array( 'user_id' ) );
	}

	$t_user_id = (int)$t_user;

	$t_data = array(
		'payload' => array(
			'project' => array( 'id' => $t_project_id ),
			'user' => array( 'id' => $t_user_id )
		)
	);

	$t_command = new ProjectUsersDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT );
}

/**
 * A method to get list of projects accessible to user with all their related information.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_projects_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_project_id = ALL_PROJECTS;
	} else {
		$t_project_id = (int)$t_project_id;
	}

	$t_user_id = auth_get_current_user_id();

	if( $t_project_id != ALL_PROJECTS ) {
		$t_message = "Project '$t_project_id' doesn't exist";

		if (!project_exists( $t_project_id ) ) {
			return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, $t_message );
		}

		if( !access_has_project_level( VIEWER, $t_project_id, $t_user_id ) ) {
			return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, $t_message );
		}
	}

	$t_lang = mci_get_user_lang( $t_user_id );

	$t_project_ids = user_get_all_accessible_projects( $t_user_id, $t_project_id );
	$t_projects = array();

	foreach( $t_project_ids as $t_project_id ) {
		$t_project = mci_project_get( $t_project_id, $t_lang, /* detail */ true );
		$t_subproject_ids = user_get_accessible_subprojects( $t_user_id, $t_project_id );
		if( !empty( $t_subproject_ids ) ) {
			$t_subprojects = array();
			foreach( $t_subproject_ids as $t_subproject_id ) {
				$t_subprojects[] = mci_project_as_array_by_id( $t_subproject_id );
			}

			$t_project['subProjects'] = $t_subprojects;
		}

		$t_projects[] = $t_project;
	}

	$t_result = array( 'projects' => $t_projects );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}

/**
 * A method to get project version(s).
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_version_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_version_id = $p_args['version_id'] ?? $p_request->getParam( 'version_id' );

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'version_id' => $t_version_id
		)
	);

	$t_command = new VersionGetCommand( $t_data );
	$t_result = $t_command->execute();

	return $p_response->
		withStatus( HTTP_STATUS_SUCCESS, "OK" )->
		withJson( $t_result );
}

/**
 * A method to add a project version.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_version_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_version_to_add = $p_request->getParsedBody();

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id
		),
		'payload' => $t_version_to_add
	);

	$t_command = new VersionAddCommand( $t_data );
	$t_result = $t_command->execute();
	$t_version_id = (int)$t_result['version']['id'];

	return $p_response->
		withStatus( HTTP_STATUS_CREATED, "Version created with id $t_version_id" )->
		withJson( $t_result );
}

/**
 * A method to update a project version.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_version_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_version_id = $p_args['version_id'] ?? $p_request->getParam( 'version_id' );
	$t_version_to_update = $p_request->getParsedBody();

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'version_id' => $t_version_id
		),
		'payload' => $t_version_to_update
	);

	$t_command = new VersionUpdateCommand( $t_data );
	$t_result = $t_command->execute();

	return $p_response
		->withStatus( HTTP_STATUS_SUCCESS, "Version updated" )
		->withJson( $t_result );
}

/**
 * A method to delete a project version.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_version_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_version_id = $p_args['version_id'] ?? $p_request->getParam( 'version_id' );

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'version_id' => $t_version_id,
		)
	);

	$t_command = new VersionDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT, "Version deleted" );
}

/**
 * A method to add a project to the project hierarchy (subproject).
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_hierarchy_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id
		),
		'payload' => $p_request->getParsedBody()
	);

	$t_command = new ProjectHierarchyAddCommand( $t_data );
	$t_command->execute();
	$t_subproject_id = mci_get_project_id( $t_data['payload'][ 'project'], false );
	
	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT,
		"Subproject '$t_subproject_id' added to project '$t_project_id'" );
}

/**
 * A method to update a project in the project hierarchy (subproject).
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_hierarchy_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_subproject_id = $p_args['subproject_id'] ?? $p_request->getParam( 'subproject_id' );
	if( is_blank( $t_subproject_id ) ) {
		$t_message = "Subproject id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_subproject_update = $p_request->getParsedBody();	

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'subproject_id' => $t_subproject_id
		),
		'payload' => $t_subproject_update

	);

	$t_command = new ProjectHierarchyUpdateCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT, "Subproject '$t_subproject_id' updated" );
}

/**
 * A method to delete a project from the project hierarchy (subproject).
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_hierarchy_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_subproject_id = $p_args['subproject_id'] ?? $p_request->getParam( 'subproject_id' );
	if( is_blank( $t_subproject_id ) ) {
		$t_message = "Subproject id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'subproject_id' => $t_subproject_id
		)
	);

	$t_command = new ProjectHierarchyDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT,
		"Subproject '$t_subproject_id' deleted from project '$t_project_id'" );
}

/**
 * A method to add a new project.
 *
 * @param \Slim\Http\Request  $p_request  The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array               $p_args     Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 *
 * @noinspection PhpUnusedParameterInspection
 */
function rest_project_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_payload = $p_request->getParsedBody();
	if( !$t_payload ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid request body or format");
	}

	$t_data = array(
		'payload' => $t_payload,
		'options' => array(
			'return_project' => true
		)
	);

	$t_command = new ProjectAddCommand( $t_data );
	$t_result = $t_command->execute();
	$t_project_id = $t_result['project']['id'];

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Project created with id $t_project_id" )->
		withJson( array( 'project' => $t_result['project'] ) );
}

/**
 * A method to update a project.
 *
 * @param \Slim\Http\Request  $p_request  The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array               $p_args     Arguments
 *
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Mandatory field 'id' is missing." );
	}

	$t_project_id = (int)$t_project_id;

	$t_payload = $p_request->getParsedBody();
	if( !$t_payload ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid request body or format");
	}

	$t_data = array(
		'query' => array(
			'id' => $t_project_id
		),
		'payload' => $t_payload,
		'options' => array(
			'return_project' => true
		)
	);

	$t_command = new ProjectUpdateCommand( $t_data );
	$t_result = $t_command->execute();
	$t_project_id = $t_result['project']['id'];

	return $p_response->withStatus( HTTP_STATUS_SUCCESS, "Project with id $t_project_id Updated" )
		->withJson( array( 'project' => $t_result['project'] ) );
}

/**
 * A method to delete a project.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = $p_args['id'] ?? $p_request->getParam( 'id' );

	$t_data = array( 'query' => array( 'id' => $t_project_id ) );
	$t_command = new ProjectDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT, "Project with id $t_project_id deleted." );
}
