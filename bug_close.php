<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.20 $
	# $Author: prescience $
	# $Date: 2002-08-19 02:54:09 $
	#
	# $Id: bug_close.php,v 1.20 2002-08-19 02:54:09 prescience Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( UPDATER );
	check_bug_exists( $f_id );

	# check variables
	check_varset( $f_bugnote_text, '' );

	$result = bug_close( $f_id, $f_bugnote_text );

	$t_redirect_url = 'view_all_bug_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
