<?php

$filename = './logs/csp_report.log';
$logprefix = date( 'y-m-d' ) . ' - CSP Violation:' . PHP_EOL;
$valid_csp_report_data = array(
	"document-uri",
	"referrer",
	"blocked-uri",
	"violated-directive",
	"original-policy" );

// Send `204 No Content` status code
http_response_code( 204 );

// Get the raw POST data
$data = file_get_contents( 'php://input' );
// Only continue if it's valid JSON that is not just `null`, `0`, `false` or an
// empty string, i.e. if it could be a CSP violation report.
if ( $data = json_decode( $data, true ) ) {
	if ( isset( $data["csp-report"] ) && (count($data) == 1) ) {
		// removing all non-CSP-report-data
		foreach ( $data["csp-report"] as $datafield ) {
			if ( !array_key_exists( $datafield, $valid_csp_report_data ) ) {
				unset( $data["csp-report"][$datafield] );
			}
		}
		// checking for existens of all CSP-report-data
		$is_valid_csp = true;
		foreach ( $valid_csp_report_data as $datafield ) {
			if ( !isset( $data["csp-report"][$datafield] ) ) {
				$is_valid_csp = false;
				break;
			}
		}

		if ( $is_valid_csp ) {
			// Prettify the JSON-formatted data
			$data = json_encode(
				$data,
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
			);

			file_put_contents( $filename, $logprefix . $data . PHP_EOL, FILE_APPEND | LOCK_EX );
		}
	}
}

?>
