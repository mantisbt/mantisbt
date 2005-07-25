<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_set_sponsorship.php,v 1.5 2005-07-25 16:34:10 thraxisp Exp $
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

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	access_ensure_bug_level( config_get( 'sponsor_threshold' ), $f_bug_id );

	helper_ensure_confirmed( 
		sprintf( lang_get( 'confirm_sponsorship' ), $f_bug_id, sponsorship_format_amount( $f_amount ) ),
		lang_get( 'sponsor_issue' ) );
			
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

	print_header_redirect_view( $f_bug_id );
?>
