<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	# check for no users
	if ( !isset( $f_user_id ) ) {
		$f_user_id = "";
	}

	# Add user(s) to project
	$result = 0;
	$count = count( $f_user_id );
	for ($i=0;$i<$count;$i++) {
		$t_user_id = $f_user_id[$i];
		$query = "INSERT
				INTO $g_mantis_project_user_list_table
				(project_id, user_id, access_level)
				VALUES
				('$g_project_cookie_val', '$t_user_id', '$f_access_level' )";
		$result = db_query($query);
	}

	$t_redirect_url = $g_proj_user_menu_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>