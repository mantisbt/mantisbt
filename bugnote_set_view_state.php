<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_set_view_state.php,v 1.15 2003-01-02 05:47:28 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Set an existing bugnote private or public.
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bugnote_id	= gpc_get_int( 'bugnote_id' );
	$f_private		= gpc_get_bool( 'private' );

	bugnote_ensure_exists( $f_bugnote_id );
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	project_access_check( $t_bug_id );
	bug_ensure_exists( $t_bug_id );

	# Check if the bug has been resolved
	if ( bug_get_field( $t_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' ) ) {
		trigger_error( ERROR_BUG_RESOLVED_ACTION_DENIED, ERROR );
	}

	# make sure the user accessing the note is valid and has proper access
	$t_bugnote_user_id	= bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( ! access_level_check_greater_or_equal( config_get( 'update_bugnote_threshold' ) ) ) &&
		 ( $t_bugnote_user_id != auth_get_current_user_id() ) ) {
		access_denied();
	}

	bugnote_set_view_state( $f_bugnote_id, $f_private );

	print_header_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
