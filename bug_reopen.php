<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reopen.php,v 1.24 2002-12-29 10:26:07 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This file reopens a bug
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	project_access_check( $f_bug_id );
	if ( OFF == config_get( 'allow_reporter_reopen' )
		 || auth_get_current_user_id() != bug_get_field( $f_bug_id, 'reporter_id' ) ) {
		check_access( config_get( 'reopen_bug_threshold' ) );
	}

	bug_reopen( $f_bug_id, $f_bugnote_text );

	print_header_redirect_view( $f_bug_id );
?>
