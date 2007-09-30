<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_delete.php,v 1.42 2007-09-30 02:55:32 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );

	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'delete_bug_threshold' ), $f_bug_id );

	$t_bug = bug_get( $f_bug_id, true );

	if ( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	helper_ensure_confirmed( lang_get( 'delete_bug_sure_msg' ), lang_get( 'delete_bug_button' ) );

	bug_delete( $f_bug_id );

	print_successful_redirect( 'view_all_bug_page.php' );
?>
