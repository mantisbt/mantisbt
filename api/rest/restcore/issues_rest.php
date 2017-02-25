<?php
require_once( __DIR__ . '/AuthMiddleware.php' );

$app->group('/issues', function() use ( $app ) {
	# Path with URL re-writing: http://.../mantisbt/api/rest/issues/1234
	# Path without URL re-writing: http://.../mantisbt/api/rest/index.php/issues/1234
	$app->get( '', 'rest_issue_get' );
	$app->get( '/', 'rest_issue_get' );
})->add( new AuthMiddleware() );

function rest_issue_get( \Slim\Http\Request $p_request, \Slim\Http\Response $p_response, array $p_args ) {
	# Username and password below are ignored, since middleware already done the auth.
	$t_issue = mc_issue_get( /* username */ '', /* password */ '', $p_request->getParam( 'id' ) );

	# Dependency on SoapFault can be removed by refactoring mc_* code.
	if( $t_issue instanceof SoapFault ) {
		return $p_response->withStatus( 404 );
	}

	return $p_response->withStatus( 200 )->withJson( $t_issue );
}


