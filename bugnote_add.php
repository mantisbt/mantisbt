<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.29 $
	# $Author: vboctor $
	# $Date: 2002-12-23 01:51:55 $
	#
	# $Id: bugnote_add.php,v 1.29 2002-12-23 01:51:55 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Insert the bugnote into the database then redirect to the bug page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );
	$f_private = gpc_get_bool( 'f_private' );
	$f_bugnote_text = gpc_get_string( 'f_bugnote_text', '' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'add_bugnote_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	$f_bugnote_text = trim( $f_bugnote_text );

	# check for blank bugnote
	if ( !is_blank( $f_bugnote_text ) ) {
		$result = bugnote_add( $f_bug_id, $f_bugnote_text, (bool)$f_private );

		# notify reporter and handler
		if ( $result ) {
			email_bugnote_add( $f_bug_id );
		}
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_bug_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
