<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: project_page.php,v 1.1 2006-03-21 12:29:58 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$f_project_id	= gpc_get_int( 'project_id' );

	$t_redirect_url = "set_project.php?project_id=$f_project_id&ref=view_all_bug_page.php";

	print_header_redirect( $t_redirect_url );
?>