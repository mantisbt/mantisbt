<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Add file and redirect to the referring page
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );

	$c_id = (integer)$f_id;
	$c_file_type = addslashes($f_file_type);

	$result = 0;
	$good_upload = 0;
	$disallowed = 0;
	extract( $HTTP_POST_FILES['f_file'], EXTR_PREFIX_ALL, "f" );

	if ( !file_type_check( $f_file_name ) ) {
		$disallowed = 1;
	} else if ( is_uploaded_file( $f_file ) ) {
		$good_upload = 1;

		# grab the file path
		$t_file_path = get_current_project_field( "file_path" );

		# prepare variables for insertion
		$f_file_name = $f_id."-".$f_file_name;
		$t_file_size = filesize( $f_file );

		switch ( $g_file_upload_method ) {
			case DISK:	if ( !file_exists( $t_file_path.$f_file_name ) ) {
							umask( 0333 );  # make read only
							copy( $f_file, $t_file_path.$f_file_name );
							$query = "INSERT INTO $g_mantis_bug_file_table
									(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
									VALUES
									(null, $c_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$c_file_type', NOW(), '')";
						} else {
							print_mantis_error( ERROR_DUPLICATE_FILE );
						}
						break;
			case DATABASE:
						$t_content = addslashes( fread ( fopen( $f_file, "rb" ), $t_file_size ) );
						$query = "INSERT INTO $g_mantis_bug_file_table
								(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $c_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$c_file_type', NOW(), '$t_content')";
						break;
		}
		$result = db_query( $query );

		# updated the last_updated date
		$result = bug_date_update( $f_id );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	}
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( 1 == $disallowed ) {
		PRINT $MANTIS_ERROR[ERROR_FILE_DISALLOWED]."<p>";
	} else if ( 0 == $good_upload ) {
		PRINT $MANTIS_ERROR[ERROR_NO_FILE_SPECIFIED]."<p>";
	} else if ( !$result ) {
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>