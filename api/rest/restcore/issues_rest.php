<?php
require_once( __DIR__ . '/AuthMiddleware.php' );

$app->group('/issues', function() use ( $app ) {
	# Path with URL re-writing: http://.../mantisbt/api/rest/issues/1234
	# Path without URL re-writing: http://.../mantisbt/api/rest/index.php/issues/1234
	$app->get('/{id:[0-9]+}', function ($request, $response, $args) {
		# Username and password below are ignored, since middleware already done the auth.
		$t_issue = mc_issue_get( /* username */ '', /* password */ '', $args['id'] );

		# Dependency on SoapFault can be removed by refactoring mc_* code.
		if( $t_issue instanceof SoapFault ) {
			return $response->withStatus( 404 );
		}

		# Once we refactor mantisconnect code this step will not be needed.  For now this is
		# needed since mc_* code emits SOAP structures and not just an array for types like
		# DateTime.
		$t_issue = soap2rest_issue( $t_issue );

		return $response->withStatus( 200 )->withJson( $t_issue );
	});
})->add( new AuthMiddleware() );

function soap2rest_issue( $p_issue ) {
	$t_issue = array(
		'id' => $p_issue['id'],
		'summary' => $p_issue['summary'],
	);

	return $t_issue;
}

