<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_update.php,v 1.37 2003-02-26 08:23:57 int2str Exp $
	# --------------------------------------------------------
?>
<?php
	# Update bugnote data then redirect to the appropriate viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_bugnote_id	= gpc_get_int( 'bugnote_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	# Check if the current user is allowed to edit the bugnote
	$t_user_id = current_user_get_field( 'id' );
	$t_bugnote_user = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_bugnote_user ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) )) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}
	
	# Check if the bug has been resolved
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	if ( bug_get_field( $t_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' ) ) {
		trigger_error( ERROR_BUG_RESOLVED_ACTION_DENIED, ERROR );
	}
	
	$f_bugnote_text = trim( $f_bugnote_text ) . "\n\n";
	$f_bugnote_text	.= lang_get( 'edited_on' ) . date( config_get( 'normal_date_format' ) );

	bugnote_set_text( $f_bugnote_id, $f_bugnote_text );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
