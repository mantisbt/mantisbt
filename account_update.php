<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_update.php,v 1.38 2004-08-22 01:19:29 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	# This page updates a user's information
	# If an account is protected then changes are forbidden
	# The page gets redirected back to account_page.php
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'email_api.php' );
?>
<?php
	auth_ensure_user_authenticated();
	
	current_user_ensure_unprotected();
?>
<?php
	$f_email			= htmlentities( gpc_get_string( 'email', '' ) );
	$f_realname			= htmlentities( gpc_get_string( 'realname', '' ) );
	$f_password			= gpc_get_string( 'password', '' );
	$f_password_confirm	= gpc_get_string( 'password_confirm', '' );

	$f_email = email_append_domain( $f_email );

	# get the user id once, so that if we decide in the future to enable this for
	# admins / managers to change details of other users.
	$t_user_id = auth_get_current_user_id();

	$t_redirect = 'account_page.php';

	html_page_top1();
	html_meta_redirect( $t_redirect );
	html_page_top2();

	echo '<br /><div align="center">';

	# @@@ Listing what fields were updated is not standard behaviour of Mantis
	#     it also complicates the code.

	if ( $f_email != user_get_email( $t_user_id ) ) {
		user_set_email( $t_user_id, $f_email );
		echo lang_get( 'email_updated' ) . '<br />';
	}

	if ( $f_realname != user_get_field( $t_user_id, 'realname' ) ) {
		# checks for problems with realnames
		$t_username = user_get_field( $t_user_id, 'username' );
		switch ( user_is_realname_unique( $t_username, $f_realname ) ) {
			case 0:
				trigger_error( ERROR_USER_REAL_MATCH_USER, ERROR );
				break;
			case 1:
				break;
			default:			
				echo lang_get( 'realname_duplicated' ) . '<br />';
		}
		user_set_realname( $t_user_id, $f_realname );
		echo lang_get( 'realname_updated' ) . '<br />';
	}

	# Update password if the two match and are not empty
	if ( !is_blank( $f_password ) ) {
		if ( $f_password != $f_password_confirm ) {
			trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
		} else {
			if ( !auth_does_password_match( $t_user_id, $f_password ) ) {
				user_set_password( $t_user_id, $f_password );
				echo lang_get( 'password_updated' ) . '<br />';
			}
		}
	}

	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
	echo '</div>';
	html_page_bottom1( __FILE__ );
?>
