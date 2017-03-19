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

$g_app->group('/lang', function() use ( $g_app ) {
	$g_app->get( '', 'rest_lang_get' );
	$g_app->get( '/', 'rest_lang_get' );
});

/**
 * A method that does the work to handle getting a set of configs via REST API.
 *
 * The following query string parameters are supported:
 * - string/string[]: can be a string or an array
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
function rest_lang_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_strings = $p_request->getParam( 'string' );
	if( !is_array( $t_strings ) ) {
		$t_strings = array( $t_strings );
	}

	$t_current_language = lang_get_current();
	$t_localized_strings = array();
	foreach( $t_strings as $t_string ) {
		if( !lang_exists( $t_string, $t_current_language) ) {
			continue;
		}

		$t_localized_strings[] = array( 'name' => $t_string, 'localized' => lang_get( $t_string ) );
	}

	$t_result = array( 'strings' => $t_localized_strings );
	$t_result['language'] = $t_current_language;

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}
