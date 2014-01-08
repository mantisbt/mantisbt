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
	 * This page updates a user's information
	 * If an account is protected then changes are forbidden
	 * The page gets redirected back to account_page.php
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'email_api.php' );

	form_security_validate('account_update');

	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	$f_email           	= gpc_get_string( 'email', '' );
	$f_realname        	= gpc_get_string( 'realname', '' );
	$f_password        	= gpc_get_string( 'password', '' );
	$f_password_confirm	= gpc_get_string( 'password_confirm', '' );

	// get the user id once, so that if we decide in the future to enable this for
	// admins / managers to change details of other users.
	$t_user_id = auth_get_current_user_id();

	$t_redirect = 'account_page.php';

	/** @todo Listing what fields were updated is not standard behaviour of MantisBT - it also complicates the code. */
	$t_email_updated = false;
	$t_password_updated = false;
	$t_realname_updated = false;

	$t_ldap = ( LDAP == config_get( 'login_method' ) );

	# Update email (but only if LDAP isn't being used)
	if ( !( $t_ldap && config_get( 'use_ldap_email' ) ) ) {
		$f_email = email_append_domain( $f_email );
		email_ensure_valid( $f_email );
		email_ensure_not_disposable( $f_email );

		if ( $f_email != user_get_email( $t_user_id ) ) {
			user_set_email( $t_user_id, $f_email );
			$t_email_updated = true;
		}
	}

	# Update real name (but only if LDAP isn't being used)
	if ( !( $t_ldap && config_get( 'use_ldap_realname' ) ) ) {
		# strip extra spaces from real name
		$t_realname = string_normalize( $f_realname );
		if ( $t_realname != user_get_field( $t_user_id, 'realname' ) ) {
			# checks for problems with realnames
			user_ensure_realname_valid( $t_realname );
			$t_username = user_get_field( $t_user_id, 'username' );
			user_ensure_realname_unique( $t_username, $t_realname );
			user_set_realname( $t_user_id, $t_realname );
			$t_realname_updated = true;
		}
	}

	# Update password if the two match and are not empty
	if ( !is_blank( $f_password ) ) {
		if ( $f_password != $f_password_confirm ) {
			trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
		} else {
			if ( !auth_does_password_match( $t_user_id, $f_password ) ) {
				user_set_password( $t_user_id, $f_password );
				$t_password_updated = true;
			}
		}
	}

	form_security_purge('account_update');

	html_page_top( null, $t_redirect );

	echo '<br /><div align="center">';

	if ( $t_email_updated ) {
		echo lang_get( 'email_updated' ) . '<br />';
	}

	if ( $t_password_updated ) {
		echo lang_get( 'password_updated' ) . '<br />';
	}

	if ( $t_realname_updated ) {
		echo lang_get( 'realname_updated' ) . '<br />';
	}

	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
	echo '</div>';
	html_page_bottom();
