<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: file_download.php,v 1.18 2003-01-18 02:14:12 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Add file and redirect to the referring page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_file_id	= gpc_get_int( 'file_id' );
	$f_type		= gpc_get_string( 'type' );

	$c_file_id = (integer)$f_file_id;
	#check_access( $g_handle_bug_threshold );
	# @@@ - hmm... that's commented out but we need *some* security check

	# we handle the case where the file is attached to a bug
	# or attached to a project as a project doc.
	switch ( $f_type ) {
		case 'bug':	$query = "SELECT *
							FROM $g_mantis_bug_file_table
							WHERE id='$c_file_id'";
					break;
		case 'doc':	$query = "SELECT *
							FROM $g_mantis_project_file_table
							WHERE id='$c_file_id'";
					break;
	}
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	header( 'Content-type: ' . $v_file_type );
	header( 'Content-Length: ' . $v_filesize );
	header( 'Content-Disposition: filename=' . file_get_display_name( $v_filename ) );
	header( 'Content-Description: Download Data' );

	# dump file content to the connection.
	switch ( config_get( 'file_upload_method' ) ) {
		case DISK:
			if ( file_exists( $v_diskfile ) ) {
				readfile( $v_diskfile );
			}
		break;
		case FTP:
			if ( file_exists( $v_diskfile ) ) {
				readfile( $v_diskfile );
			} else {
				$ftp = file_ftp_connect();
				file_ftp_get ( $ftp, $v_diskfile, $v_filename );
				file_ftp_disconnect( $ftp );
				readfile( $v_diskfile );
			}
		break;
		default:
			echo $v_content;
	}
?>
