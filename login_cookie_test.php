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
 * Check to see if cookies are working
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

$f_return = gpc_get_string( 'return', config_get( 'default_home_page' ) );

$c_return = string_prepare_header( $f_return );

if ( auth_is_user_authenticated() ) {
	$t_redirect_url = $c_return;
} else {
	$t_redirect_url = 'login_page.php?cookie_error=1';
}

print_header_redirect( $t_redirect_url, true, true );
