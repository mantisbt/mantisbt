<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.24 $
	# $Author: jfitzell $
	# $Date: 2002-08-30 08:36:50 $
	#
	# $Id: bug_close.php,v 1.24 2002-08-30 08:36:50 jfitzell Exp $
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
	bug_ensure_exists( $f_id );

	# check variables
	check_varset( $f_bugnote_text, '' );

	$result = bug_close( $f_id, $f_bugnote_text );
	if ( $result ) {
		print_header_redirect( 'view_all_bug_page.php' );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
