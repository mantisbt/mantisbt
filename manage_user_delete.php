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
 * User Delete
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
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );

form_security_validate( 'manage_user_delete' );

auth_reauthenticate();

$f_user_id	= gpc_get_int( 'user_id' );

$t_user = user_get_row( $f_user_id );
helper_ensure_confirmed(
	sprintf( lang_get( 'delete_account_sure_msg' ),
		string_attribute( $t_user['username'] )
	),
	lang_get( 'delete_account_button' )
);

# If an administrator is trying to delete their own account, use
# account_delete.php instead as it is handles logging out and redirection
# of users who have just deleted their own accounts.
if( auth_get_current_user_id() == $f_user_id ) {
	form_security_purge( 'manage_user_delete' );
	print_header_redirect( 'account_delete.php?account_delete_token=' . form_security_token( 'account_delete' ), true, false );
}

$t_data = array(
	'query' => array( 'id' => $f_user_id )
);

$t_command = new UserDeleteCommand( $t_data );
$t_command->execute();

form_security_purge( 'manage_user_delete' );

layout_page_header( null, 'manage_user_page.php' );

layout_page_begin( 'manage_overview_page.php' );

html_operation_successful( 'manage_user_page.php' );

layout_page_end();
