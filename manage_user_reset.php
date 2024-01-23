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
 * Reset a Users Password
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

form_security_validate( 'manage_user_reset' );

auth_reauthenticate();

$f_user_id = gpc_get_int( 'user_id' );

$t_data = array(
	'query' => array( 'id' => $f_user_id )
);

$t_command = new UserResetPasswordCommand( $t_data );
# The case of trying to reset a protected account now causes the Command to
# trigger an exception, so we do not need any special handling here.
$t_result = $t_command->execute();

$t_redirect_url = 'manage_user_page.php';

form_security_purge( 'manage_user_reset' );

layout_page_header( null, $t_redirect_url );
layout_page_begin( 'manage_overview_page.php' );

switch( $t_result['action'] ) {
	case UserResetPasswordCommand::RESULT_RESET:
		if(    ( ON == config_get( 'send_reset_password' ) )
			&& ( ON == config_get( 'enable_email_notification' ) )
		) {
			# Password reset confirmation sent by email
			html_operation_successful( $t_redirect_url, lang_get( 'account_reset_msg' ) );
		} else {
			# Email notification disabled, password set to blank
			html_operation_successful( $t_redirect_url, lang_get( 'account_reset_msg2' ) );
		}
		break;
	case UserResetPasswordCommand::RESULT_UNLOCK:
		html_operation_successful( $t_redirect_url, lang_get( 'account_unlock_msg' ) );
		break;
}

layout_page_end();
