<?php
require_api( 'authentication_api.php' );

class AuthMiddleware {
	public function __invoke( \Slim\Http\Request $request, \Slim\Http\Response $response, callable $next ) {
		if( mci_is_mantis_offline() ) {
			return $response->withStatus( 503, 'Mantis Offline' );
		}

		$t_authorization_header = $request->getHeaderLine( 'Authorization' );

		if( empty( $t_authorization_header ) ) {
			# TODO: Add support for anonymous login
			return $response->withStatus( 403, 'API token required' );
		}

		$t_api_token = $t_authorization_header;

		# TODO: add an index on the token hash for the method below
		$t_user = api_token_get_user( $t_api_token );
		if( $t_user === false ) {
			return $response->withStatus( 403, 'API token not found' );
		}

		if( mci_check_login( $t_user['username'], $t_api_token ) === false ) {
			return $response->withStatus( 403, 'User disabled' );
		}

		return $next( $request, $response )->withHeader( 'X-Mantis-Username', $t_user['username'] );
	}
}
