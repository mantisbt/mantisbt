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

	$c_id			= (integer)$f_id;
	$c_title 		= string_prepare_text( $f_title );
	$c_description 	= string_prepare_textarea( $f_description );

	$query = "UPDATE $g_mantis_project_file_table
			SET title='$c_title', description='$c_description'
			WHERE id='$c_id'";
	$result = db_query( $query );

	$t_redirect_url = $g_proj_doc_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>