<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: file_download.php,v 1.23 2003-02-15 10:25:16 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Add file and redirect to the referring page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_file_id	= gpc_get_int( 'file_id' );
	$f_type		= gpc_get_string( 'type' );

	$c_file_id = (integer)$f_file_id;
	#access_ensure_project_level( $g_handle_bug_threshold );
	# @@@ We need a security check here but we need the API to
	#   get the project_id or bug_id from the file first.

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
