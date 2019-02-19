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
	$g_app->post( '/{id}/versions', 'rest_project_version_add' );
	$g_app->post( '/{id}/versions/', 'rest_project_version_add' );

	# Project hierarchy (subprojects)
	$g_app->post( '/{id}/subprojects', 'rest_project_hierarchy_add' );
	$g_app->post( '/{id}/subprojects/', 'rest_project_hierarchy_add' );
	$g_app->patch( '/{id}/subprojects/{subproject_id}', 'rest_project_hierarchy_update' );
	$g_app->patch( '/{id}/subprojects/{subproject_id}/', 'rest_project_hierarchy_update' );
	$g_app->delete( '/{id}/subprojects/{subproject_id}', 'rest_project_hierarchy_delete' );
	$g_app->delete( '/{id}/subprojects/{subproject_id}/', 'rest_project_hierarchy_delete' );
});

/**
 * A method to get list of projects accessible to user with all their related information.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_projects_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_project_id = ALL_PROJECTS;
	} else {
		$t_project_id = (int)$t_project_id;
	}

	$t_user_id = auth_get_current_user_id();
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
 * A method to add a project version.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_version_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_version_to_add = $p_request->getParsedBody();

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
		),
		'payload' => $t_version_to_add
	);

	$t_command = new VersionAddCommand( $t_data );
	$t_result = $t_command->execute();
	$t_version_id = (int)$t_result['id'];

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT, "Version created with id $t_version_id" );
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
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
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
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_subproject_id = isset( $p_args['subproject_id'] )
		? $p_args['subproject_id']
		: $p_request->getParam( 'subproject_id' );
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
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		$t_message = "Project id is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_subproject_id = isset( $p_args['subproject_id'] )
		? $p_args['subproject_id']
		: $p_request->getParam( 'subproject_id' );
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
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_payload = $p_request->getParsedBody();
	if( $t_payload === null ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Unable to parse body, specify content type" );
	}

	$t_project_id = mc_project_add( /* username */ '', /* password */ '', (object) $t_payload );
	ApiObjectFactory::throwIfFault( $t_project_id );

	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );
	$t_project = mci_project_get( $t_project_id, $t_lang, /* detail */ true );

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Project created with id $t_project_id" )->
		withJson( array( 'project' => $t_project ) );
}

/**
 * A method to update a project.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_project_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Mandatory field 'id' is missing." );
	}

	$t_project_id = (int)$t_project_id;
	if( $t_project_id == ALL_PROJECTS || $t_project_id < 1 ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid project id." );
	}

	$t_project_patch = $p_request->getParsedBody();

	if( isset( $t_project_patch['id'] ) && $t_project_patch['id'] != $t_project_id ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Project id mismatch" );
	}

	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );

	$t_project = mci_project_get( $t_project_id, $t_lang, /* detail */ true );
	$t_project = array_merge( $t_project, $t_project_patch );

	$success = mc_project_update( /* username */ '', /* password */ '', $t_project_id, (object)$t_project );
	ApiObjectFactory::throwIfFault( $success );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS, "Project with id $t_project_id Updated" )
		->withJson( array( 'project' => $t_project ) );
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
	$t_project_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_project_id ) ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Mandatory field 'id' is missing." );
	}

	$t_project_id = (int)$t_project_id;
	if( $t_project_id == ALL_PROJECTS || $t_project_id < 1 ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, "Invalid project id." );
	}

	$t_user_id = auth_get_current_user_id();
	if( !project_exists( $t_project_id ) || !access_has_project_level( config_get( 'delete_project_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
		return $p_response->withStatus( HTTP_STATUS_FORBIDDEN, "Access denied for deleting project." );
	}

	project_delete( $t_project_id );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS, "Project with id $t_project_id deleted." );
}
