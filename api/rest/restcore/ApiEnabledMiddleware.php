<?php
class ApiEnabledMiddleware {
	public function __invoke( \Slim\Http\Request $request, \Slim\Http\Response $response, callable $next ) {
		if( config_get( 'webservice_rest_enabled' ) == OFF ) {
			return $response->withStatus( 503, 'Mantis REST API disabled.' );
		}

		return $next( $request, $response );
	}
}
