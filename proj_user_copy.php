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

	# Copy all uesrs from current project over to another project ($f_project_id)
	$result = proj_user_get_all_users( $g_project_cookie_val );
	$user_count = count( $result );
	for ($i=0;$i<$user_count;$i++) {
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		# if there is no duplicate then add a new entry
		# otherwise just update the access level for the existing entry
		if ( !proj_user_is_duplicate( $f_project_id, $v_user_id ) ) {
			proj_user_add( $f_project_id, $v_user_id, $v_access_level );
		} else {
			proj_user_update( $f_project_id, $v_user_id, $v_access_level );
		}
	}

	$t_redirect_url = $g_proj_user_menu_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>