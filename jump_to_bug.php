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
 * Given a bug id, redirect user to the view bug page for the given id
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 *
 * @noinspection PhpUnhandledExceptionInspection
 */

use Mantis\Exceptions\ClientException;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );

auth_ensure_user_authenticated();

# Retrieve the bug id to jump to as a string
$f_search_string = gpc_get_string( 'bug_id' );

# Validate input, ignoring whitespace and leading bug link tag (#).
$t_bug_link_tag = config_get( 'bug_link_tag' );
if( !preg_match( "/^$t_bug_link_tag?([0-9]+)$/", trim( $f_search_string ), $t_matches ) ) {
	throw new ClientException( 'Invalid bug id', ERROR_INVALID_FIELD_VALUE, ['bug_id'] );
}
$t_bug_id = $t_matches[1];

print_header_redirect_view( $t_bug_id );
