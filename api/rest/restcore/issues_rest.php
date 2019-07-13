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

use Mantis\Exceptions\ClientException;

$g_app->group('/issues', function() use ( $g_app ) {
	$g_app->get( '', 'rest_issue_get' );
	$g_app->get( '/', 'rest_issue_get' );
	$g_app->get( '/{id}', 'rest_issue_get' );
	$g_app->get( '/{id}/', 'rest_issue_get' );
	$g_app->post( '', 'rest_issue_add' );
	$g_app->post( '/', 'rest_issue_add' );
	$g_app->delete( '', 'rest_issue_delete' );
	$g_app->delete( '/', 'rest_issue_delete' );
	$g_app->delete( '/{id}', 'rest_issue_delete' );
	$g_app->delete( '/{id}/', 'rest_issue_delete' );
	$g_app->patch( '', 'rest_issue_update' );
	$g_app->patch( '/', 'rest_issue_update' );
	$g_app->patch( '/{id}', 'rest_issue_update' );
	$g_app->patch( '/{id}/', 'rest_issue_update' );

	# Tags
	$g_app->post( '/{id}/tags', 'rest_issue_tag_attach' );
	$g_app->post( '/{id}/tags/', 'rest_issue_tag_attach' );
	$g_app->delete( '/{id}/tags/{tag_id}', 'rest_issue_tag_detach' );
	$g_app->delete( '/{id}/tags/{tag_id}/', 'rest_issue_tag_detach' );

	# Monitor
	$g_app->post( '/{id}/monitors/', 'rest_issue_monitor_add' );
	$g_app->post( '/{id}/monitors', 'rest_issue_monitor_add' );

	# Notes
	$g_app->post( '/{id}/notes/', 'rest_issue_note_add' );
	$g_app->post( '/{id}/notes', 'rest_issue_note_add' );
	$g_app->delete( '/{id}/notes/{note_id}/', 'rest_issue_note_delete' );
	$g_app->delete( '/{id}/notes/{note_id}', 'rest_issue_note_delete' );

	# Relationships
	$g_app->post( '/{id}/relationships/', 'rest_issue_relationship_add' );
	$g_app->post( '/{id}/relationships', 'rest_issue_relationship_add' );
	$g_app->delete( '/{id}/relationships/{relationship_id}/', 'rest_issue_relationship_delete' );
	$g_app->delete( '/{id}/relationships/{relationship_id}', 'rest_issue_relationship_delete' );

	# Files
	$g_app->post( '/{id}/files/', 'rest_issue_file_add' );
	$g_app->post( '/{id}/files', 'rest_issue_file_add' );
	$g_app->get( '/{id}/files/', 'rest_issue_files_get' );
	$g_app->get( '/{id}/files', 'rest_issue_files_get' );
	$g_app->get( '/{id}/files/{file_id}/', 'rest_issue_files_get' );
	$g_app->get( '/{id}/files/{file_id}', 'rest_issue_files_get' );
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
		ApiObjectFactory::throwIfFault( $t_issue );

		$t_result = array( 'issues' => array( $t_issue ) );
	} else {
		$t_page_number = $p_request->getParam( 'page', 1 );
		$t_page_size = $p_request->getParam( 'page_size', 50 );

		# Get a set of issues
		$t_project_id = (int)$p_request->getParam( 'project_id', ALL_PROJECTS );
		if( $t_project_id != ALL_PROJECTS && !project_exists( $t_project_id ) ) {
			$t_result = null;
			$t_message = "Project '$t_project_id' doesn't exist";
			$p_response = $p_response->withStatus( HTTP_STATUS_NOT_FOUND, $t_message );
		} else {
			$t_filter_id = trim( $p_request->getParam( 'filter_id', '' ) );

			if( !empty( $t_filter_id ) ) {
				$t_issues = mc_filter_get_issues(
					'', '', $t_project_id, $t_filter_id, $t_page_number, $t_page_size );
			} else {
				$t_issues = mc_filter_get_issues(
					'', '', $t_project_id, FILTER_STANDARD_ANY, $t_page_number, $t_page_size );
			}

			$t_result = array( 'issues' => $t_issues );
		}
	}

	$t_etag = mc_issue_hash( $t_issue_id, $t_result );
	if( $p_request->hasHeader( HEADER_IF_NONE_MATCH ) ) {
		$t_match_etag = $p_request->getHeaderLine( HEADER_IF_NONE_MATCH );
		if( $t_etag == $t_match_etag ) {
			return $p_response->withStatus( HTTP_STATUS_NOT_MODIFIED, 'Not Modified' )
				->withHeader( HEADER_ETAG, $t_etag );
		}
	}

	if( $t_result !== null ) {
		$p_response = $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
	}

	return $p_response->withHeader( HEADER_ETAG, $t_etag );
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

	if( isset( $t_issue['files'] ) ) {
		$t_issue['files'] = files_base64_to_temp( $t_issue['files'] );
	}

	$t_data = array( 'payload' => array( 'issue' => $t_issue ) );
	$t_command = new IssueAddCommand( $t_data );
	$t_result = $t_command->execute();
	$t_issue_id = (int)$t_result['issue_id'];

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

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	ApiObjectFactory::throwIfFault( $t_issue );

	$t_etag = mc_issue_hash( $t_issue_id, array( 'issues' => array( $t_issue ) ) );

	if( $p_request->hasHeader( HEADER_IF_MATCH ) ) {
		$t_match_etag = $p_request->getHeaderLine( HEADER_IF_MATCH );
		if( $t_etag != $t_match_etag ) {
			return $p_response->withStatus( HTTP_STATUS_PRECONDITION_FAILED, 'Precondition Failed' )
				->withHeader( HEADER_ETAG, $t_etag );
		}
	}

	$t_data = array( 'query' => array( 'id' => $t_issue_id ) );
	$t_command = new IssueDeleteCommand( $t_data );
	$t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_NO_CONTENT )
		->withHeader( HEADER_ETAG, mc_issue_hash( $t_issue_id, null ) );
}

/**
 * Add issue file.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_file_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );

	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id ),
		'payload' => $p_request->getParsedBody(),
	);

	if( isset( $t_data['payload']['files'] ) ) {
		$t_data['payload']['files'] = files_base64_to_temp( $t_data['payload']['files'] );
	}

	$t_command = new IssueFileAddCommand( $t_data );
	$t_command_response = $t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Issue File(s) Attached" );
}

/**
 * Add issue note.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_note_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );

	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id ),
		'payload' => $p_request->getParsedBody(),
	);

	if( isset( $t_data['payload']['files'] ) ) {
		$t_data['payload']['files'] = files_base64_to_temp( $t_data['payload']['files'] );
	}

	$t_command = new IssueNoteAddCommand( $t_data );
	$t_command_response = $t_command->execute();

	# TODO: Move construction of response to the command and add options to allow callers to
	# determine whether the response is needed.  This will need refactoring of APIs that construct
	# notes and issues in responses.
	$t_note_id = $t_command_response['id'];

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

/**
 * Delete issue note.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_note_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	$t_issue_note_id = isset( $p_args['note_id'] ) ? $p_args['note_id'] : $p_request->getParam( 'note_id' );

	$t_data = array(
		'query' => array(
			'id' => $t_issue_note_id,
			'issue_id' => $t_issue_id )
	);

	$t_command = new IssueNoteDeleteCommand( $t_data );
	$t_command->execute();

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	return $p_response->withStatus( HTTP_STATUS_SUCCESS, 'Issue Note Deleted' )->
		withJson( array( 'issue' => $t_issue ) );
}

/**
 * Add relationship to issue.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_relationship_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = $p_args['id'];

	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id ),
		'payload' => $p_request->getParsedBody(),
	);

	$t_command = new IssueRelationshipAddCommand( $t_data );
	$t_command_response = $t_command->execute();

	$t_relationship_id = $t_command_response['id'];

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );

	return $p_response->withStatus(
		HTTP_STATUS_CREATED,
		"Issue relationship created with id $t_relationship_id" )->
			withJson( array( 'issue' => $t_issue ) );
}

/**
 * Delete issue relationship.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_relationship_delete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = $p_args['id'];
	$t_relationship_id = $p_args['relationship_id'];

	$t_data = array(
		'query' => array(
			'relationship_id' => $t_relationship_id,
			'issue_id' => $t_issue_id )
	);

	$t_command = new IssueRelationshipDeleteCommand( $t_data );
	$t_command->execute();

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	return $p_response->withStatus( HTTP_STATUS_SUCCESS, 'Issue relationship deleted' )->
		withJson( array( 'issue' => $t_issue ) );
}

/**
 * Update an issue from a PATCH to the issues url.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_update( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	if( is_blank( $t_issue_id ) ) {
		$t_message = "Mandatory field 'id' is missing.";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	ApiObjectFactory::throwIfFault( $t_issue );

	$t_etag = mc_issue_hash( $t_issue_id, array( 'issues' => array( $t_issue ) ) );

	if( $p_request->hasHeader( HEADER_IF_MATCH ) ) {
		$t_match_etag = $p_request->getHeaderLine( HEADER_IF_MATCH );
		if( $t_etag != $t_match_etag ) {
			return $p_response->withStatus( HTTP_STATUS_PRECONDITION_FAILED, 'Precondition Failed' )
				->withHeader( HEADER_ETAG, $t_etag );
		}
	}

	# Construct full issue from issue from db + patched info
	$t_issue_patch = $p_request->getParsedBody();
	if( isset( $t_issue_patch['id'] ) && $t_issue_patch['id'] != $t_issue_id ) {
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, 'Issue id mismatch' );
	}

	$t_issue = (object)array_merge( $t_issue, $t_issue_patch );

	# Trigger the issue update
	$t_result = mc_issue_update( /* username */ '', /* password */ '', $t_issue_id, $t_issue );
	ApiObjectFactory::throwIfFault( $t_result );

	$t_updated_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );
	$t_result = array( 'issues' => array( $t_updated_issue ) );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS, "Issue with id $t_issue_id Updated" )
		->withHeader( HEADER_ETAG, mc_issue_hash( $t_issue_id, $t_result ) )
		->withJson( $t_result );
}

/**
 * Add users to monitor an issue.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_monitor_add( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = isset( $p_args['id'] ) ? $p_args['id'] : $p_request->getParam( 'id' );
	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id ),
		'payload' => $p_request->getParsedBody(),
	);

	$t_command = new MonitorAddCommand( $t_data );
	$t_command->execute();

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );			

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Users are now monitoring issue $t_issue_id" )->
		withJson( array( 'issues' => array( $t_issue ) ) );
}

/**
 * Attach a tag to an issue.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_tag_attach( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = $p_args['id'];
	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id ),
		'payload' => $p_request->getParsedBody(),
	);

	$t_command = new TagAttachCommand( $t_data );
	$t_command->execute();

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );			

	return $p_response->withStatus( HTTP_STATUS_CREATED, "Tag attached to issue $t_issue_id" )->
		withJson( array( 'issues' => array( $t_issue ) ) );
}

/**
 * Detach a tag from the issue
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_tag_detach( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = $p_args['id'];
	$t_tag_id = $p_args['tag_id'];

	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id, 'tag_id' => $t_tag_id )
	);

	$t_command = new TagDetachCommand( $t_data );
	$t_command->execute();

	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $t_issue_id );			

	return $p_response->withStatus( HTTP_STATUS_SUCCESS, "Tag detached from issue $t_issue_id" )->
		withJson( array( 'issues' => array( $t_issue ) ) );
}

/**
 * Get files associated with an issue.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_issue_files_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_issue_id = $p_args['id'];
	$t_file_id = isset( $p_args['file_id'] ) ? $p_args['file_id'] : null;

	$t_data = array(
		'query' => array( 'issue_id' => $t_issue_id, 'file_id' => $t_file_id )
	);

	$t_command = new IssueFileGetCommand( $t_data );
	$t_internal_files = $t_command->execute();

	$t_files = array();
	foreach( $t_internal_files as $t_internal_file ) {
		$t_file = array(
			'id' => (int)$t_internal_file['id'],
			'reporter' => mci_account_get_array_by_id( $t_internal_file['user_id'] ),
			'created_at' => ApiObjectFactory::datetimeString( $t_internal_file['date_added'] ),
			'filename' => $t_internal_file['display_name'],
			'size' => (int)$t_internal_file['size'],
		);

		if( $t_internal_file['exists'] ) {
			$t_file['content_type'] = $t_internal_file['content_type'];
			$t_file['content'] = base64_encode( $t_internal_file['content'] );
		}

		$t_files[] = $t_file;
	}

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->
		withJson( array( 'files' => $t_files ) );
}

/**
 * Convert REST API base 64 files into expected format for browser file uploads.
 * 
 * @param array $p_files The files in REST API format.
 * @return array The files in browser upload format.
 */
function files_base64_to_temp( $p_files ) {
	$t_files = array();

	if( isset( $p_files ) && is_array( $p_files ) ) {
		foreach( $p_files as $t_file ) {
			if( !isset( $t_file['content'] ) ) {
				throw new ClientException(
					'File content not set',
					ERROR_INVALID_FIELD_VALUE,
					array( 'files' ) );
			}

			$t_raw_content = base64_decode( $t_file['content'] );

			do {
				$t_tmp_file = realpath( sys_get_temp_dir() ) . '/' . uniqid( 'mantisbt-file' );
			} while( file_exists( $t_tmp_file ) );

			file_put_contents( $t_tmp_file, $t_raw_content );
			$t_file['tmp_name'] = $t_tmp_file;
			$t_file['size'] = filesize( $t_tmp_file );
			$t_file['browser_upload'] = false;
			unset( $t_file['content'] );

			$t_files[] = $t_file;
		}
	}

	return $t_files;
}