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
	check_access( REPORTER );

	$result = 0;
	$good_upload = 0;
	if ( is_uploaded_file( $f_file ) ) {
		$good_upload = 1;
		$query = "SELECT file_path
				FROM $g_mantis_project_table
				WHERE id='$g_project_cookie_val'";
		$result = db_query( $query );
		$t_file_path = db_result( $result );

		$f_title 		= string_prepare_text( $f_title );
		$f_description 	= string_prepare_textarea( $f_description );

		$f_file_name = $g_project_cookie_val."-".$f_file_name;
		$t_file_size = filesize( $f_file );

		switch ( $g_file_upload_method ) {
			case DISK:	umask( 0333 );  # make read only
						copy($f_file, $t_file_path.$f_file_name);
						$query = "INSERT INTO mantis_project_file_table
								(id, project_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $g_project_cookie_val, '$f_title', '$f_description', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_file_type', NOW(), '')";
			case DATABASE:
						$t_content = addslashes( fread ( fopen( $f_file, "r" ), $t_file_size ) );
						$query = "INSERT INTO mantis_project_file_table
								(id, project_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $g_project_cookie_val, '$f_title', '$f_description', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_file_type', NOW(), '$t_content')";
		}
		$result = db_query( $query );
	}

	$t_redirect_url = $g_proj_doc_page;
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url, $g_wait_time );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		PRINT "$s_operation_successful<p>";
	} else {						# FAILURE
		if ( 0 == $good_upload ) {
			PRINT $MANTIS_ERROR[ERROR_NO_FILE_SPECIFIED]."<p>";
		} else {
			print_sql_error( $query );
		}
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>