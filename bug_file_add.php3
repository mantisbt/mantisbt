<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Add file and redirect to the referring page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	$result = 0;
	if ( is_uploaded_file( $f_file ) ) {
		### grab the file path
		$query = "SELECT file_path
				FROM $g_mantis_project_table
				WHERE id='$g_project_cookie_val'";
		$result = db_query( $query );
		$t_file_path = db_result( $result );

		### prepare variables for insertion
		$f_file_name = $f_id."-".$f_file_name;
		$t_file_size = filesize( $f_file );

		switch ( $g_file_upload_method ) {
			case DISK:	umask( 0333 );  # make read only
						copy($f_file, $t_file_path.$f_file_name);
						$query = "INSERT INTO $g_mantis_bug_file_table
								(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $f_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_file_type', NOW(), '')";
			case DATABASE:
						$t_content = addslashes( fread ( fopen( $f_file, "r" ), $t_file_size ) );
						$query = "INSERT INTO $g_mantis_bug_file_table
								(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $f_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_file_type', NOW(), '$t_content')";
		}
		$result = db_query( $query );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $HTTP_REFERER, $g_wait_time );
	}
?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {					### SUCCESS
		PRINT "$s_file_upload_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $HTTP_REFERER, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>