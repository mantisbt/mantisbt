<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	# Add user to project
	$query = "INSERT
			INTO $g_mantis_project_user_list_table
			(project_id, user_id, access_level)
			VALUES
			('$g_project_cookie_val', '$f_user_id', '$f_access_level' )";
	$result = db_query($query);

	$t_redirect_url = $g_proj_user_menu_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>