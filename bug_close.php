<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.23 $
	# $Author: vboctor $
	# $Date: 2002-08-28 14:10:11 $
	#
	# $Id: bug_close.php,v 1.23 2002-08-28 14:10:11 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_close_bug_threshold );
	check_bug_exists( $f_id );

	# check variables
	check_varset( $f_bugnote_text, '' );

	$result = bug_close( $f_id, $f_bugnote_text );
	if ( $result ) {
		print_header_redirect( 'view_all_bug_page.php' );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
