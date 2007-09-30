<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: jump_to_bug.php,v 1.22 2007-09-30 02:56:01 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_ensure_user_authenticated();

	# Determine which view page to redirect back to.
	$f_bug_id		= gpc_get_int( 'bug_id' );

	print_header_redirect_view( $f_bug_id );
?>
