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

require_api( 'helper_api.php' );

/**
 * WARNING: All APIs under the internal route are considered private and can break anytime.
 */
$g_app->group('/internal', function() use ( $g_app ) {
	$g_app->any( '/autocomplete', 'rest_internal_autocomplete' );
	$g_app->any( '/config_display', 'rest_internal_config_display' );
});

/**
 * A method that gets the auto-complete result for given field and prefix.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_internal_autocomplete( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_field = $p_request->getParam( 'field' );;
	$t_prefix = $p_request->getParam( 'prefix' );

	switch( $t_field ) {
		case 'platform':
			$t_unique_entries = profile_get_field_all_for_user( 'platform' );
			$t_matches = helper_filter_by_prefix( $t_unique_entries, $t_prefix );
			break;
		case 'os':
			$t_unique_entries = profile_get_field_all_for_user( 'os' );
			$t_matches = helper_filter_by_prefix( $t_unique_entries, $t_prefix );
			break;
		case 'os_build':
			$t_unique_entries = profile_get_field_all_for_user( 'os_build' );
			$t_matches = helper_filter_by_prefix( $t_unique_entries, $t_prefix );
			break;
		default:
			return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, "Field '$t_field' doesn't have auto-complete." );
	}

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_matches );
}

function rest_internal_config_display( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_config_id = $p_request->getParam( 'config_id' );
	$t_user_id = $p_request->getParam( 'user_id' );
	$t_project_id = $p_request->getParam( 'project_id' );

	if( !access_has_global_level( config_get( 'view_configuration_threshold' ), auth_get_current_user_id() ) ) {
		return $p_response->withStatus( HTTP_STATUS_FORBIDDEN );
	}
	if( null === $t_user_id || null === $t_project_id || null === $t_config_id ) {
		$t_message = "Missing parameters";
		return $p_response->withStatus( HTTP_STATUS_BAD_REQUEST, $t_message );
	}

	$t_sql = 'SELECT config_id, user_id, project_id, type, value, access_reqd FROM {config}'
			. ' WHERE user_id = :user_id AND project_id = :project_id AND config_id = :config_id';
	$t_params = array(
		'user_id' => $t_user_id,
		'project_id' => $t_project_id,
		'config_id' => $t_config_id
		);
	$t_query = new DbQuery( $t_sql, $t_params );
	$t_row = $t_query->fetch();

	if( !$t_row ) {
		$t_message = "Requested option/project/user doesn't exist";
		return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, $t_message );
	}

	$t_output = config_get_value_as_string( $t_row['type'], $t_row['value'], true );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->write( $t_output );
}