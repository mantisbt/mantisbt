<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_resolve.php,v 1.29 2002-12-30 09:44:44 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state and adds a
	#  bugnote giving a reason for the resolution
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );
	$f_resolution	= gpc_get_int( 'resolution', FIXED );
	$f_duplicate_id	= gpc_get_int( 'duplicate_id', null );
	$f_close_now	= gpc_get_bool( 'close_now' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'handle_bug_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	# make sure the bug is not being marked as a duplicate of itself
	if ( $f_duplicate_id === $f_bug_id ) {
		trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
	}

	bug_resolve( $f_bug_id, $f_resolution, $f_bugnote_text, $f_duplicate_id );

	if ( $f_close_now ) {
		bug_set_field( $f_bug_id, 'status', CLOSED );
	}

	print_header_redirect_view( $f_bug_id );
?>
