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

	if ( !isset( $f_enabled ) ) {
		$f_enabled = 0;
	} else {
		$f_enabled = 1;
	}

	$f_name 		= string_prepare_textarea( $f_name );
	$f_description 	= string_prepare_textarea( $f_description );
	$f_status = (integer)$f_status;
	$f_enabled = (integer)$f_enabled;
	$f_view_state = (integer)$f_view_state;
	$f_project_id = (integer)$f_project_id;
	$f_file_path = addslashes($f_file_path);

	# Make sure file path has trailing slash
	if ( $f_file_path[strlen($f_file_path)-1] != "/" ) {
		$f_file_path = $f_file_path."/";
	}

	# Update entry
	$query = "UPDATE $g_mantis_project_table
			SET name='$f_name',
				status='$f_status',
				enabled='$f_enabled',
				view_state='$f_view_state',
				file_path='$f_file_path',
				description='$f_description'
    		WHERE id='$f_project_id'";
    $result = db_query( $query );

    $t_redirect_url = $g_manage_project_menu_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>