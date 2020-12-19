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
 * Remove User from Project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );

form_security_validate( 'manage_proj_user_remove' );
auth_reauthenticate();

$f_project_id = gpc_get_int( 'project_id' );
$f_user_id = gpc_get_int( 'user_id', 0 );

# We should check both since we are in the project section and an
#  admin might raise the first threshold and not realize they need
#  to raise the second
access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

if( 0 == $f_user_id ) {
	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'remove_all_users_sure_msg' ), lang_get( 'remove_all_users_button' ) );

	project_remove_all_users( $f_project_id, access_get_project_level( $f_project_id ) );
} else {
	# Don't allow removal of users from the project who have a higher access level than the current user
	access_ensure_project_level( access_get_project_level( $f_project_id, $f_user_id ), $f_project_id );

	$t_user = user_get_row( $f_user_id );

	# Confirm with the user
	helper_ensure_confirmed(
		sprintf( lang_get( 'remove_user_sure_msg' ),
			string_attribute( $t_user['username'] ),
			string_attribute( project_get_name( $f_project_id ) )
		),
		lang_get( 'remove_user_button' )
	);

	project_remove_user( $f_project_id, $f_user_id );
}

form_security_purge( 'manage_proj_user_remove' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

layout_page_header( null, $t_redirect_url );

layout_page_begin( 'manage_overview_page.php' );

html_operation_successful( $t_redirect_url );

layout_page_end();
