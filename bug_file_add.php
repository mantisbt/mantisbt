<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Add file and redirect to the referring page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_bug_id );
	check_access( REPORTER );

	$c_bug_id			= (integer)$f_bug_id;
	$c_file_type	= addslashes($f_file_type);

	$result = 0;
	$good_upload = 0;
	$disallowed = 0;
	$upload_error = 0;

	if ( !isset( $HTTP_POST_FILES['f_file'] ) ) {
		print_mantis_error( ERROR_UPLOAD_FAILURE );
	}

	extract( $HTTP_POST_FILES['f_file'], EXTR_PREFIX_ALL, 'f' );

	if ( $HTTP_POST_FILES['f_file']['error'] == 1 ) {
		$upload_error = 1;
	} else if ( !file_type_check( $f_file_name ) ) {
		$disallowed = 1;
	} else if ( is_uploaded_file( $f_file ) ) {
		$good_upload = 1;

		# grab the file path
		$t_file_path = project_get_field( helper_get_current_project(), 'file_path' );

		# prepare variables for insertion
		$f_file_name = $f_bug_id.'-'.$f_file_name;
		$t_file_size = filesize( $f_file );

		switch ( $g_file_upload_method ) {
			case FTP:
			case DISK:	if ( !file_exists( $t_file_path.$f_file_name ) ) {
							if ( FTP == $g_file_upload_method ) {
								$conn_id = file_ftp_connect();
								file_ftp_put ( $conn_id, $f_file_name, $f_file );
								file_ftp_disconnect ( $conn_id );
							}

							umask( 0333 );  # make read only
							copy( $f_file, $t_file_path.$f_file_name );

							$query = "INSERT INTO $g_mantis_bug_file_table
									(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
									VALUES
									(null, $c_bug_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$c_file_type', NOW(), '')";
						} else {
							print_mantis_error( ERROR_DUPLICATE_FILE );
						}
						break;
			case DATABASE:
						$t_content = addslashes( fread ( fopen( $f_file, 'rb' ), $t_file_size ) );
						$query = "INSERT INTO $g_mantis_bug_file_table
								(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $c_bug_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$c_file_type', NOW(), '$t_content')";
						break;
		}
		$result = db_query( $query );

		# updated the last_updated date
		$result = bug_update_date( $f_bug_id );

		# log new file
		history_log_event_special( $f_bug_id, FILE_ADDED, file_get_display_name( $f_file_name ) );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_bug_id );
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

<br />
<div align="center">
<?php
	if ( 1 == $upload_error ) {
		PRINT $MANTIS_ERROR[ERROR_FILE_TOO_BIG].'<br />';
	} else if ( 1 == $disallowed ) {
		PRINT $MANTIS_ERROR[ERROR_FILE_DISALLOWED].'<br />';
	} else if ( 0 == $good_upload ) {
		PRINT $MANTIS_ERROR[ERROR_NO_FILE_SPECIFIED].'<br />';
	} else if ( !$result ) {
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
