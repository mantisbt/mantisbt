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
 * Remove the bugnote and bugnote text and redirect back to
 * the viewing page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'bugnote_delete' );

$f_bugnote_id = gpc_get_int( 'bugnote_id' );

$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

$t_bug = bug_get( $t_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# Check if the current user is allowed to delete the bugnote
$t_user_id = auth_get_current_user_id();
$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

if( $t_user_id == $t_reporter_id ) {
	access_ensure_bugnote_level( config_get( 'bugnote_user_delete_threshold' ), $f_bugnote_id );
} else {
	access_ensure_bugnote_level( config_get( 'delete_bugnote_threshold' ), $f_bugnote_id );
}

helper_ensure_confirmed( lang_get( 'delete_bugnote_sure_msg' ),
						 lang_get( 'delete_bugnote_button' ) );

bugnote_delete( $f_bugnote_id );

form_security_purge( 'bugnote_delete' );

print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
