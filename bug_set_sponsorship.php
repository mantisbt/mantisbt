<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_set_sponsorship.php,v 1.3 2005-05-01 16:20:22 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'sponsorship_api.php' );

	if ( config_get( 'enable_sponsorship' ) == OFF ) {
		trigger_error( ERROR_SPONSORSHIP_NOT_ENABLED, ERROR );
	}

	# anonymous users are not allowed to sponsor issues
	if ( current_user_is_anonymous() ) {
		access_denied();
	}

	$f_bug_id	= gpc_get_int( 'bug_id' );
	$f_amount	= gpc_get_int( 'amount' );

	access_ensure_bug_level( config_get( 'sponsor_threshold' ), $f_bug_id );

	if ( $f_amount == 0 ) {
		# if amount == 0, delete sponsorship by current user (if any)
		$t_sponsorship_id = sponsorship_get_id( $f_bug_id );
		if ( $t_sponsorship_id !== false ) {
			sponsorship_delete( $t_sponsorship_id );
		}
	} else {
		# add sponsorship
		$t_user = auth_get_current_user_id();
		if ( is_blank( user_get_email( $t_user ) ) ) {
			trigger_error( ERROR_SPONSORSHIP_SPONSOR_NO_EMAIL, ERROR );
		} else {
			$sponsorship = new SponsorshipData;
			$sponsorship->bug_id = $f_bug_id;
			$sponsorship->user_id = $t_user;
			$sponsorship->amount = $f_amount;

			sponsorship_set( $sponsorship );
		}
	}

	$t_referrer = $_SERVER['HTTP_REFERER'];
	print_header_redirect( $t_referrer );
?>
