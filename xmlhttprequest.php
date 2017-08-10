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
 * WARNING: This approach for doing AJAX calls is now deprecated in favor of the
 * new REST API.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses gpc_api.php
 * @uses logging_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'gpc_api.php' );
require_api( 'logging_api.php' );

auth_ensure_user_authenticated();

$f_entrypoint = gpc_get_string( 'entrypoint' );

$t_function = 'xmlhttprequest_' . $f_entrypoint;
if( function_exists( $t_function ) ) {
	log_event( LOG_AJAX, 'DEPRECATED: Calling {' . $t_function . '}. Use REST API instead.' );
	call_user_func( $t_function );
} else {
	$t_msg = 'Unknown function for entry point: ' . $t_function;
	log_event( LOG_AJAX, $t_msg );
	header(
		'HTTP/1.1 ' . HTTP_STATUS_BAD_REQUEST . ' ' . $t_msg,
		false,
		HTTP_STATUS_BAD_REQUEST
	);
	echo string_attribute( $t_msg );
}
