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
	check_access( MANAGER );

	$f_user_id		= gpc_get_int_array( 'user_id', array() );
	$f_access_level	= gpc_get_int( 'access_level' );

	# Add user(s) to the current project
	$count = count( $f_user_id );
	for ($i=0;$i<$count;$i++) {
		$t_user_id = $f_user_id[$i];
		project_add_user( helper_get_current_project(), $t_user_id, $f_access_level );
	}

	print_header_redirect( 'proj_user_menu_page.php' );
?>
