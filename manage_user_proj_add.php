<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	# check for no projects
	check_varset( $f_project_id, '' );

	# Add a user to project(s)
	$result = 0;
	$count = count( $f_project_id );
	for ($i=0;$i<$count;$i++) {
		$t_project_id = $f_project_id[$i];
		$result = proj_user_add( $t_project_id, $f_user_id, $f_access_level );
	}

	$t_redirect_url = $g_manage_user_page.'?f_id='.$f_user_id;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>