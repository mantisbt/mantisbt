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
 * JSON API
 *
 * @package CoreAPI
 * @subpackage JSONAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses url_api.php
 */

/**
 * requires url_api
 */
require_once( 'url_api.php' );
require_once( 'database_api.php' );
require_once( 'lang_api.php' );

/**
 * Get a chunk of JSON from a given URL.
 * @param string $p_url URL
 * @param string $p_member Optional top-level member to retrieve
 * @return mixed JSON class structure, false in case of non-existent member
 */
function json_url( $p_url, $p_member = null ) {
	$t_data = url_get( $p_url );
	$t_json = json_decode( utf8_encode($t_data) );

	if( is_null( $p_member ) ) {
		return $t_json;
	} else if( property_exists( $t_json, $p_member ) ) {
		return $t_json->$p_member;
	} else {
		return false;
	}
}

/**
 * JSON error handler
 * 
 * Ensures that all necessary headers are set and terminates processing after being invoked.
 * @param int $p_type contains the level of the error raised, as an integer.
 * @param string $p_error contains the error message, as a string.
 * @param string $p_file contains the filename that the error was raised in, as a string.
 * @param int $p_line contains the line number the error was raised at, as an integer.
 * @param array $p_context to the active symbol table at the point the error occurred (optional)
 */
function json_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
	# flush any language overrides to return to user's natural default
	if( function_exists( 'db_is_connected' ) ) {
		if( db_is_connected() ) {
			lang_push( lang_get_default() );
		}
	}

	$t_error_code = ERROR_GENERIC; # default
	
	# build an appropriate error string
	switch( $p_type ) {
		case E_WARNING:
			$t_error_type = 'SYSTEM WARNING';
			$t_error_description = $p_error;
			break;
		case E_NOTICE:
			$t_error_type = 'SYSTEM NOTICE';
			$t_error_description = $p_error;
			break;
		case E_USER_ERROR:
			$t_error_type = "APPLICATION ERROR #$p_error";
			$t_error_code = $p_error;
			$t_error_description = error_string( $p_error );
			break;
		case E_USER_WARNING:
			$t_error_type = "APPLICATION WARNING #$p_error";
			$t_error_code = $p_error;
			$t_error_description = error_string( $p_error );
			break;
		case E_USER_NOTICE:
			# used for debugging
			$t_error_type = 'DEBUG';
			$t_error_description = $p_error;
			break;
		default:
			#shouldn't happen, just display the error just in case
			$t_error_type = '';
			$t_error_description = $p_error;
	}

	json_output_raw(array(
		'status' => 'ERROR',
		'error' => array(
			'code' => $t_error_code,
			'type' => $t_error_type,
			'message' => $t_error_description
		),
		'contents' => $t_error_description 
	));
}
/**
 * Outputs the specified contents inside a json response with OK status
 * 
 * <p>Ensures that all necessary headers are set and terminates processing.</p>
 * @param string $contents The contents to encode
 */
 function json_output_response ( $contents = '') {
 	json_output_raw( array(
		'status' => 'OK',
		'contents' => $contents	
	) );
}

/**
 * output json data
 * @param mixed $p_contents raw data to json encode
 */
function json_output_raw( $p_contents ) {
	header('Content-Type: application/json');
	echo json_encode( $p_contents );
	exit();
}
