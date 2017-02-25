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
 * A RESTful webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( dirname( __FILE__) ) ) . '/vendor/autoload.php' );
require_once( dirname( dirname( dirname( __FILE__) ) ) . '/core.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/soap/mc_core.php' );

$app = new \Slim\App();

# Path with URL re-writing: http://.../mantisbt/api/rest/issues/1234
# Path without URL re-writing: http://.../mantisbt/api/rest/index.php/issues/1234
$app->get('/issues/{id:[0-9]+}', function ($request, $response, $args) {
	# TODO: get username and API tokens from headers
	$t_issue = mc_issue_get( 'vboctor', 'root', $args['id'] );

	# Dependency on SoapFault can be removed by refactoring mc_* code.
	if( $t_issue instanceof SoapFault ) {
		return $response->withStatus(404);
	}

	# Once we refactor mantisconnect code this step will not be needed.  For now this is
	# needed since mc_* code emits SOAP structures and not just an array for types like
	# DateTime.
	$t_issue = soap2rest_issue( $t_issue );

	return $response->withStatus(200)->withJson( $t_issue );
});

function soap2rest_issue( $p_issue ) {
	$t_issue = array(
		'id' => $p_issue['id'],
		'summary' => $p_issue['summary'],
	);

	return $t_issue;
}

$app->run();

