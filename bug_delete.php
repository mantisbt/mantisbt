<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_delete.php,v 1.25 2002-10-23 00:50:53 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Deletes the bug and re-directs to view_all_bug_page.php 
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'allow_bug_delete_access_level' ) );
	bug_ensure_exists( $f_bug_id );

	bug_delete( $f_bug_id );

	print_header_redirect( 'view_all_bug_page.php' );
?>
