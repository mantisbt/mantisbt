<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: verify.php,v 1.6 2005-07-11 19:08:13 thraxisp Exp $
	# --------------------------------------------------------

	# ======================================================================
	# Author: Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	# ======================================================================

	require_once( 'core.php' );

	# lost password feature disabled or reset password via email disabled -> stop here!
	if( OFF == config_get( 'lost_password_feature' ) ||
		OFF == config_get( 'send_reset_password' ) ||
		OFF == config_get( 'enable_email_notification' ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
	}

	$f_user_id = gpc_get_string('id');
	$f_confirm_hash = gpc_get_string('confirm_hash');

	# force logout on the current user if already authenticated
	if( auth_is_user_authenticated() ) {
		auth_logout();
	}

	$t_calculated_confirm_hash = auth_generate_confirm_hash( $f_user_id );

	if ( $f_confirm_hash != $t_calculated_confirm_hash ) {
		trigger_error( ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID, ERROR );
	}

	# set a temporary cookie so the login information is passed between pages.
	auth_logout();
	auth_set_cookies( $f_user_id, false );

	user_reset_failed_login_count_to_zero( $f_user_id );
	user_reset_lost_password_in_progress_count_to_zero( $f_user_id );

	# fake login so the user can set their password
	auth_attempt_script_login( user_get_field( $f_user_id, 'username' ) );

	include ( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'account_page.php' );
?>
