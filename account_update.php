<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_update.php,v 1.30 2003-02-11 09:08:31 jfitzell Exp $
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
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_email			= gpc_get_string( 'email', '' );
	$f_password			= gpc_get_string( 'password', '' );
	$f_password_confirm	= gpc_get_string( 'password_confirm', '' );

	$f_email = email_append_domain( $f_email );

	user_set_email( auth_get_current_user_id(), $f_email );

	$t_redirect = 'account_page.php';

	print_page_top1();
	print_meta_redirect( $t_redirect );
	print_page_top2();

	echo '<br /><div align="center">';

	echo lang_get( 'operation_successful' );
	echo '<br /><ul>';
	echo '<li>' . lang_get( 'email_updated' ) . '</li>';

	# Update password if the two match and are not empty
	if ( !is_blank( $f_password ) ) {
		if ( $f_password != $f_password_confirm ) {
			trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
		} else {
			user_set_password( auth_get_current_user_id(), $f_password );

			echo '<li>' . lang_get( 'password_updated' ) . '</li>';
		}
	}

	echo '</ul>';

	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
	echo '</div>';
	print_page_bot1( __FILE__ );
?>
