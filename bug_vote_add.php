<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_vote_add.php,v 1.15 2002-10-30 10:42:07 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php die('Not in use.'); ?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( REPORTER );

	$t_votes = bug_get_field( $f_bug_id, 'votes' );

	# increase vote count and update in table
	$t_votes++;

	#@@@ should we add a bug_vote() so we can do this in an atomic query?

	bug_set_field( $f_bug_id, 'votes', $t_votes );

	print_header_redirect_view( $f_bug_id );
?>
