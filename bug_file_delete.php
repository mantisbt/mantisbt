<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_file_delete.php,v 1.19 2002-12-29 10:26:07 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Delete a file from a bug and then view the bug
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_file_id = gpc_get_int( 'file_id' );

	$t_bug_id = file_get_field( $f_file_id, 'bug_id' );

	project_access_check( $t_bug_id );
	check_access( config_get( 'handle_bug_threshold' ) );

	file_delete( $f_file_id );

	print_header_redirect_view( $t_bug_id );
?>
