<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'project_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );
	
	$f_project_id = gpc_get_int( 'project_id' );
	
	helper_ensure_confirmed( lang_get( 'project_delete_msg' ),
							 lang_get( 'project_delete_button' ) );

	project_delete( $f_project_id );

	# Don't leave the current project set to a deleted project - 
	#  set it to All Projects
	if ( helper_get_current_project() == $f_project_id ) {
		helper_set_current_project( 0 );
	}

    $t_redirect_url = 'manage_proj_menu_page.php';
	print_header_redirect( $t_redirect_url );
?>
