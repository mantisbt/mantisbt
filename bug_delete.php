<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_delete.php,v 1.32 2003-02-11 09:08:32 jfitzell Exp $
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
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'allow_bug_delete_access_level' ) );
	bug_ensure_exists( $f_bug_id );

	helper_ensure_confirmed( lang_get( 'delete_bug_sure_msg' ), lang_get( 'delete_bug_button' ) );

	bug_delete( $f_bug_id );

	print_header_redirect( 'view_all_bug_page.php' );
?>
