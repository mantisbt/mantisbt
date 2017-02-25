<?php
class VersionMiddleware {
	public function __invoke( \Slim\Http\Request $request, \Slim\Http\Response $response, callable $next )
	{
		return $next( $request, $response )->withHeader( 'X-Mantis-Version', mc_version() );
	}
}