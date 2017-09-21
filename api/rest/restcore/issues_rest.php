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

$g_app->group('/issues', function() use ( $g_app ) {
	$g_app->get( '', 'rest_issue_get' );
	$g_app->get( '/', 'rest_issue_get' );
	$g_app->get( '/{id}', 'rest_issue_get' );
	$g_app->get( '/{id}/', 'rest_issue_get' );
	$g_app->post( '', 'rest_issue_add' );
	$g_app->post( '/', 'rest_issue_add' );
	$g_app->post( '/update', 'rest_issue_update' );
	$g_app->post( '/update/', 'rest_issue_update' );
	$g_app->delete( '', 'rest_issue_delete' );
	$g_app->delete( '/', 'rest_issue_delete' );
	$g_app->delete( '/{id}', 'rest_issue_delete' );
	$g_app->delete( '/{id}/', 'rest_issue_delete' );

	# Notes
	$g_app->post( '/{id}/notes/', 'rest_issue_note_add' );
	$g_app->post( '/{id}/notes', 'rest_issue_note_add' );
	$g_app->delete( '/{id}/notes/{note_id}/', 'rest_issue_note_delete' );
	$g_app->delete( '/{id}/notes/{note_id}', 'rest_issue_note_delete' );
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
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );

	if( !is_blank( $t_issue_id ) ) {
		# Get Issue By Id

		# Username and password below are ignored, since middleware already done the auth.
		$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );

		if( ApiObjectFactory::isFault( $t_issue ) ) {
			return $p_response->withStatus( $t_result->status_code, $t_result->fault_string );
		}

		$t_result = array( 'issues' => array( $t_issue ) );
	} else {
		$t_page_number = $p_request->getParam( 'page', 1 );
		$t_page_size = $p_request->getParam( 'page_size', 50 );

		# Get a set of issues
		$t_project_id = (int)$p_request->getParam( 'project_id', ALL_PROJECTS );
		if( $t_project_id != ALL_PROJECTS && !project_exists( $t_project_id ) ) {
			# TODO: What's best was to escape $t_project?
			$t_message = "Project '$t_project_id' doesn't exist";
			return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, $t_message );
		}

		$t_filter_id = (int)$p_request->getParam( 'filter_id', 0 );
		if( $t_filter_id !== 0 ) {
			# TODO: we should have a better way to do this.
			global $g_project_override;
			$g_project_override = $t_project_id;

			$t_issues = mc_filter_get_issues( '', '', $t_project_id, $t_filter_id, $t_page_number, $t_page_size );
		} else {
			$t_issues = mc_project_get_issues( '', '', $t_project_id, $t_page_number, $t_page_size );
		}

		$t_result = array( 'issues' => $t_issues );
	}

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
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
		return $p_response->withStatus( $t_result->status_code, $t_result->fault_string );
	}

	$t_issue_id = $t_result;

	$t_created_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Issue Created with id $t_issue_id" )->
		withJson( array( 'issue' => $t_created_issue ) );
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
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );

	# Username and password below are ignored, since middleware already done the auth.
	$t_result = mc_issue_delete( /* username */ '', /* password */ '', $t_issue_id );

	if( ApiObjectFactory::isFault( $t_result ) ) {
		return $p_response->withStatus( $t_result->status_code, $t_result->fault_string );
	}

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT );
}

function rest_issue_note_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_note_info = $p_request->getParsedBody();

	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );

	$t_note = new stdClass();
	$t_note->text = isset( $t_note_info['text'] ) ? $t_note_info['text'] : '';

	if( isset( $t_note_info['view_state'] ) ) {
		$t_note->view_state = $t_note_info['view_state'];
	}

	if( isset( $t_note_info['reporter'] ) ) {
		$t_note->reporter = $t_note_info['reporter'];
	}

	# TODO: support time tracking notes
	# TODO: support reminder notes
	# TODO: support note attachments

	$t_result = mc_issue_note_add( /* username */ '', /* password */ '', $t_issue_id, $t_note );
	if( ApiObjectFactory::isFault( $t_result ) ) {
		return $p_response->withStatus( $t_result->status_code, $t_result->fault_string );
	}

	$t_note_id = $t_result;

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	foreach( $t_issue['notes'] as $t_current_note ) {
		if( $t_current_note['id'] == $t_note_id ) {
			$t_note = $t_current_note;
			break;
		}
	}

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Issue Note Created with id $t_issue_id" )->
		withJson( array( 'note' => $t_note, 'issue' => $t_issue ) );
}

function rest_issue_note_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	$t_issue_note_id = isset( $p_args['note_id'] ) ? $p_args['note_id'] : $p_request->getParam( 'note_id' );

	$t_result = mc_issue_note_delete( '', '', $t_issue_note_id );
	if( ApiObjectFactory::isFault( $t_result ) ) {
		return $p_response->withStatus( $t_result->status_code, $t_result->fault_string );
	}

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	return $p_response->withStatus( HTTP_STATUS_SUCCESS, 'Issue Note Deleted' )->
		withJson( array( 'issue' => $t_issue ) );
}

/**
 * Update an issue from a POST to the issues/update url.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue = (object)$p_request->getParsedBody();
	$t_issue_id = $t_issue->id;

	$t_result = mc_issue_update( /* username */ '', /* password */ '', $t_issue_id, $t_issue );
	if( ApiObjectFactory::isFault( $t_result ) ) {
		return $p_response->withStatus( $t_result->status_code, $t_result->fault_string );
	}

	$t_updated_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS, "Issue with id $t_issue_id Updated" )->
		withJson( array( 'issue' => $t_updated_issue ) );
}
