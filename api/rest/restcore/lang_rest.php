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
 * A method that does the work to handle getting a set of localized strings via REST API.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function rest_lang_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	$t_data = array(
		'query' => array(
			'string' => $p_request->getParam( 'string' )
		)
	);

	$t_command = new LocalizedStringsGetCommand( $t_data );
	$t_result = $t_command->execute();

	return $p_response->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_result );
}
