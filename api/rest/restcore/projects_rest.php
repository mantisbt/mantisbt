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

	# Project versions
	$g_app->post( '/{id}/versions', 'rest_project_version_add' );
	$g_app->post( '/{id}/versions/', 'rest_project_version_add' );
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
