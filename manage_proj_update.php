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

	if ( !isset( $f_enabled ) ) {
		$c_enabled = 0;
	} else {
		$c_enabled = 1;
	}

	# Make sure file path has trailing slash
	if ( $f_file_path && $f_file_path[strlen($f_file_path)-1] != DIRECTORY_SEPARATOR ) {
		$f_file_path = $f_file_path.DIRECTORY_SEPARATOR;
	}

	$c_name 		= string_prepare_textarea( $f_name );
	$c_description 	= string_prepare_textarea( $f_description );
	$c_status 		= (integer)$f_status;
	$c_view_state 	= (integer)$f_view_state;
	$c_project_id 	= (integer)$f_project_id;
	$c_file_path 	= addslashes($f_file_path);

	# Update entry
	$query = "UPDATE $g_mantis_project_table
			SET name='$c_name',
				status='$c_status',
				enabled='$c_enabled',
				view_state='$c_view_state',
				file_path='$c_file_path',
				description='$c_description'
    		WHERE id='$c_project_id'";
    $result = db_query( $query );

    $t_redirect_url = 'manage_proj_menu_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
