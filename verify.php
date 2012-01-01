<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	 * @link http://www.mantisbt.org
	 */

	# don't auto-login when trying to verify new user
	$g_login_anonymous = false;

	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	# check if at least one way to get here is enabled
	if ( OFF == config_get( 'allow_signup' ) &&
		OFF == config_get( 'lost_password_feature' ) &&
		OFF == config_get( 'send_reset_password' ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
	}

	$f_user_id = gpc_get_string('id');
	$f_confirm_hash = gpc_get_string('confirm_hash');

	# force logout on the current user if already authenticated
	if( auth_is_user_authenticated() ) {
		auth_logout();

		# reload the page after logout
		print_header_redirect( "verify.php?id=$f_user_id&confirm_hash=$f_confirm_hash" );
	}

	$t_calculated_confirm_hash = auth_generate_confirm_hash( $f_user_id );

	if ( $f_confirm_hash != $t_calculated_confirm_hash ) {
		trigger_error( ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID, ERROR );
	}

	# set a temporary cookie so the login information is passed between pages.
	auth_set_cookies( $f_user_id, false );

	user_reset_failed_login_count_to_zero( $f_user_id );
	user_reset_lost_password_in_progress_count_to_zero( $f_user_id );

	# fake login so the user can set their password
	auth_attempt_script_login( user_get_field( $f_user_id, 'username' ) );

	user_increment_failed_login_count( $f_user_id );

	include ( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'account_page.php' );

