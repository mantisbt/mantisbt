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
	check_access( $g_handle_bug_threshold );

	$c_file_id = (integer)$f_file_id;

	$t_file_name = file_get_field( $c_file_id, 'filename' );

	if ( ( DISK == $g_file_upload_method ) || ( FTP == $g_file_upload_method ) ) {
		# grab the file name
		$query = "SELECT diskfile, filename
				FROM $g_mantis_bug_file_table
				WHERE id='$c_file_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		if ( FTP == $g_file_upload_method ) {
			$ftp = file_ftp_connect();
			file_ftp_delete ( $ftp, $v_filename );
			file_ftp_disconnect( $ftp );
		}

		if ( file_exists( $v_diskfile ) ) {
			file_delete_local ( $v_diskfile );
		}
	}

	$query = "DELETE FROM $g_mantis_bug_file_table
			WHERE id='$c_file_id'";
	$result = db_query( $query );

	# log file deletion
	history_log_event_special( $f_bug_id, FILE_DELETED, file_get_display_name ( $t_file_name ) );

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_bug_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
