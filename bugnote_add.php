<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.25 $
	# $Author: jfitzell $
	# $Date: 2002-09-21 10:17:13 $
	#
	# $Id: bugnote_add.php,v 1.25 2002-09-21 10:17:13 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Insert the bugnote into the database then redirect to the bug page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( REPORTER );
	bug_ensure_exists( $f_id );

	#check variables
	check_varset( $f_private, false ); #if it doesn't exist, the checkbox wasn't checked
	check_varset( $f_bugnote_text, '' );

	$f_bugnote_text = trim( $f_bugnote_text );

	# check for blank bugnote
	if ( !empty( $f_bugnote_text ) ) {
#@@@ jf - need to add string_prepare_textarea() call or something once that is resolved
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
