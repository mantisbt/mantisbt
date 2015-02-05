<?php

$filename = './logs/csp_report.log';
$logprefix = date( 'y-m-d' ) . ' - CSP Violation:' . PHP_EOL;

// Send `204 No Content` status code
http_response_code( 204 );

// Get the raw POST data
$data = file_get_contents( 'php://input' );
// Only continue if it's valid JSON that is not just `null`, `0`, `false` or an
// empty string, i.e. if it could be a CSP violation report.
if ( $data = json_decode( $data ) ) {
	// Prettify the JSON-formatted data
	$data = json_encode(
		$data,
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
	);
}

file_put_contents( $filename, $logprefix . $data . PHP_EOL, FILE_APPEND | LOCK_EX );

?>
