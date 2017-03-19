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

$g_app->group('/config', function() use ( $g_app ) {
	$g_app->get( '', 'rest_config_get' );
	$g_app->get( '/', 'rest_config_get' );
});

/**
 * A method that does the work to handle getting a set of configs via REST API.
 *
 * The following query string parameters are supported:
 * - option/option[]: can be a string or an array
 * - project_id: an optional parameter for project id to get configs for. Default ALL_PROJECTS (0).
 * - user_id: an optional parameter for user id to get configs for.  Default current user.
 *   can be 0 for ALL_USERS.
 *
 * The response will include a config key that is an array of requested configs.  Configs
 * that are not public or are not defined will be filtered out, and request will still succeed.
 * This is to make it easier to request configs that maybe defined in some versions of MantisBT
 * but not others.
 *
 * Note that only users with ADMINISTRATOR access can fetch configuration for other users.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_config_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_options = $p_request->getParam( 'option' );
	if( !is_array( $t_options ) ) {
		$t_options = array( $t_options );
	}

	$t_project_id = $p_request->getParam( 'project_id' );
	if( is_null( $t_project_id ) ) {
		$t_project_id = ALL_PROJECTS;
	}

	if( $t_project_id != ALL_PROJECTS && !project_exists( $t_project_id ) ) {
		return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, "Project with id '$t_project_id' not found" );
	}

	$t_user_id = $p_request->getParam( 'user_id' );
	if( is_null( $t_user_id ) ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		if( $t_user_id != ALL_USERS &&
			$t_user_id != auth_get_current_user_id() &&
			!current_user_is_administrator() ) {
			return $p_response->withStatus( HTTP_STATUS_FORBIDDEN, 'Admin access required to get configs for other users' );
		}

		if( $t_user_id != ALL_USERS && user_exists( $t_user_id ) ) {
			return $p_response->withStatus( HTTP_STATUS_NOT_FOUND, "User with id '$t_user_id' not found" );
		}
	}

	$t_configs = array();
	foreach( $t_options as $t_option ) {
		# Filter out undefined configs rather than error, they may be valid in some MantisBT versions but not
		# others.
		if( !config_is_set( $t_option ) ) {
			continue;
		}

		# Filter out private configs, since they can be private in some configs but public in others.
		if( config_is_private( $t_option ) ) {
			continue;
		}

		$t_value = config_get( $t_option, /* default */ null, $t_user_id, $t_project_id );
		if( config_is_enum( $t_option ) ) {
			$t_value = config_get_enum_as_array( $t_option, $t_value );
		}

		$t_config_pair = array(
			"option" => $t_option,
			"value" => $t_value
		);

		$t_configs[] = $t_config_pair;
	}

	# wrap all configs into a configs attribute to allow adding other information if needed in the future
	# that belongs outside the configs response.
	$t_result = array( 'configs' => $t_configs );

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}

/**
 * Checks if the specific config option is an enum.
 *
 * @param string $p_option The option name.
 * @return bool true enum, false otherwise.
 */
function config_is_enum( $p_option ) {
	return stripos( $p_option, '_enum_string' ) !== false;
}

/**
 * Gets the enum config option as an array with the id as the key and the value
 * as an array with name and label (localized name) keys.
 *
 * @param string $p_enum_name The enum config option name.
 * @param string $p_enum_string_value The enum config option value.
 * @return array The enum array.
 */
function config_get_enum_as_array( $p_enum_name, $p_enum_string_value ) {
	$t_enum_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string_value );
	$t_localized_enum_string = lang_get( $p_enum_name );

	$t_enum_array = array();

	foreach( $t_enum_assoc_array as $t_id => $t_name ) {
		$t_label = MantisEnum::getLocalizedLabel( $p_enum_string_value, $t_localized_enum_string, $t_id );
		$t_enum_entry = array( 'id' => $t_id, 'name' => $t_name, 'label' => $t_label );
		$t_enum_array[] = $t_enum_entry;
	}

	return $t_enum_array;
}

