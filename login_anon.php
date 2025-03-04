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
 * login_anon.php logs a user in anonymously without having to enter a username
 * or password.
 *
 * Depends on global configuration variables:
 * anonymous_login - false or name of account to login with.
 *
 * TODO:
 * Check how manage account is impacted.
 * Might be extended to allow redirects for bug links etc.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

$f_return = string_sanitize_url( gpc_get_string( 'return', '' ) );

$t_params = [
	'username' => auth_anonymous_account(),
	'perm_login' => false
];

if( !is_blank( $f_return ) ) {
	$t_params['return'] = $f_return;
}

print_header_redirect( helper_url_combine( 'login.php', $t_params ) );
