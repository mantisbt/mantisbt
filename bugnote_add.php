<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.26 $
	# $Author: jfitzell $
	# $Date: 2002-09-27 00:06:47 $
	#
	# $Id: bugnote_add.php,v 1.26 2002-09-27 00:06:47 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Insert the bugnote into the database then redirect to the bug page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_id = gpc_get_int( 'f_id' );
	$f_private = gpc_get_bool( 'f_private' );
	$f_bugnote_text = gpc_get_string( 'f_bugnote_text', '' );

	project_access_check( $f_id );
	check_access( REPORTER );
	bug_ensure_exists( $f_id );

	$f_bugnote_text = trim( $f_bugnote_text );

	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
		$result = bugnote_add( $f_id, $f_bugnote_text, (bool)$f_private );

		# notify reporter and handler
		if ( $result ) {
			email_bugnote_add( $f_id );
		}
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
