<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_add.php,v 1.48 2006-12-26 10:56:06 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Insert the bugnote into the database then redirect to the bug page

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_private		= gpc_get_bool( 'private' );
	$f_time_tracking	= gpc_get_string( 'time_tracking', '0:00' );
	$f_bugnote_text	= trim( gpc_get_string( 'bugnote_text', '' ) );

	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id );

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	$c_time_tracking = db_prepare_time( $f_time_tracking );

	# check for blank bugnote
	# @@@ VB: Do we want to ban adding a time without an associated note?
	# @@@ VB: Do we want to differentiate email notifications for normal notes from time tracking entries?
	if ( !is_blank( $f_bugnote_text ) || ( $c_time_tracking > 0 ) ) {
		$t_note_type = ( $c_time_tracking > 0 ) ? TIME_TRACKING : BUGNOTE;
		bugnote_add( $f_bug_id, $f_bugnote_text, $f_time_tracking, $f_private, $t_note_type );

		# only send email if the text is not blank, otherwise, it is just recording of time without a comment.
		if ( !is_blank( $f_bugnote_text ) ) {
			email_bugnote_add( $f_bug_id );
		}
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
