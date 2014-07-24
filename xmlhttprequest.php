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
 * This is the first page a user sees when they login to the bugtracker
 * News is displayed which can notify users of any important changes
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses logging_api.php
 * @uses xhtmlrequest_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'logging_api.php' );
require_api( 'xmlhttprequest_api.php' );

auth_ensure_user_authenticated();

$f_entrypoint = gpc_get_string( 'entrypoint' );

$t_function = 'xmlhttprequest_' . $f_entrypoint;
if( function_exists( $t_function ) ) {
	log_event( LOG_AJAX, 'Calling {' . $t_function . '}...' );
	call_user_func( $t_function );
} else {
	log_event( LOG_AJAX, 'Unknown function for entry point = ' . $t_function );
	echo 'unknown entry point';
}
