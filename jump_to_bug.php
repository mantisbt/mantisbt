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
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );

auth_ensure_user_authenticated();

# Determine which view page to redirect back to.
$f_bug_id		= gpc_get_int( 'bug_id' );

print_header_redirect_view( $f_bug_id );
