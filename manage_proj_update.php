<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	$f_project_id 	= gpc_get_int( 'f_project_id' );
	$f_name 		= gpc_get_string( 'f_name' );
	$f_description 	= gpc_get_string( 'f_description' );
	$f_status 		= gpc_get_int( 'f_status' );
	$f_view_state 	= gpc_get_int( 'f_view_state' );
	$f_file_path 	= gpc_get_string( 'f_file_path', '' );
	$f_enabled	 	= gpc_get_bool( 'f_enabled' );

	project_update( $f_project_id, $f_name, $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled );

    $t_redirect_url = 'manage_proj_menu_page.php';
	print_header_redirect( $t_redirect_url );
?>
