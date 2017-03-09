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
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$app->group('/config', function() use ( $app ) {
	$app->get( '', 'rest_config_get' );
	$app->get( '/', 'rest_config_get' );
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

	$t_user_id = $p_request->getParam( 'user_id' );
	if( is_null( $t_user_id ) ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		if( $t_user_id != ALL_USERS &&
			$t_user_id != auth_get_current_user_id() &&
			!current_user_is_administrator() ) {
			return $p_response->withStatus( 403, 'Admin access required to get configs for other users' );
		}

		if( $t_user_id != ALL_USERS && user_exists( $t_user_id ) ) {
			return $p_response->withStatus( 404, "User with id '$t_user_id' not found" );
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
		$t_configs[$t_option] = $t_value;
	}

	# wrap all configs into a configs attribute to allow adding other information if needed in the future
	# that belongs outside the configs response.
	$t_result = array( 'configs' => $t_configs );

	return $p_response->withStatus( 200 )->withJson( $t_result );
}


