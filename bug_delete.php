<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_delete.php,v 1.40 2005-07-25 16:34:10 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	# Deletes the bug and re-directs to view_all_bug_page.php
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'delete_bug_threshold' ), $f_bug_id );

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	helper_ensure_confirmed( lang_get( 'delete_bug_sure_msg' ), lang_get( 'delete_bug_button' ) );

	$t_bug = bug_get( $f_bug_id, true );

	helper_call_custom_function( 'issue_delete_validate', array( $f_bug_id ) );

	bug_delete( $f_bug_id );

	helper_call_custom_function( 'issue_delete_notify', array( $f_bug_id ) );

	print_successful_redirect( 'view_all_bug_page.php' );
?>
