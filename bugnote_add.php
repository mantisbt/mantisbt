<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.20 $
	# $Author: jfitzell $
	# $Date: 2002-08-16 06:46:28 $
	#
	# $Id: bugnote_add.php,v 1.20 2002-08-16 06:46:28 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Insert the bugnote into the database then redirect to the bug page
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$c_id = (integer)$f_id;
	project_access_check( $c_id );
	check_access( REPORTER );
	check_bug_exists( $c_id );

	#check variables
	check_varset( $f_private, false );
	check_varset( $f_bugnote_text, '' );

	#clean variables
	$c_private = (bool)$f_private;
	$c_bugnote_text = string_prepare_textarea( trim( $f_bugnote_text ) );

	# check for blank bugnote
	if ( !empty( $c_bugnote_text ) ) {
		$result = add_bugnote( $c_id, $c_bugnote_text, $c_private );

		# notify reporter and handler
		if ( $result ) {
			email_bugnote_add( $c_id );
		}
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $c_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
