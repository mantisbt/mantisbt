<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_update.php,v 1.23 2004-10-08 19:57:46 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'file_api.php' );
?>
<?php
	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}
	
	access_ensure_project_level( config_get( 'upload_project_file_threshold' ) );

	$f_file_id		= gpc_get_int( 'file_id' );
	$f_title		= gpc_get_string( 'title' );
	if ( is_blank( $f_title ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_description	= gpc_get_string( 'description' );

	$c_file_id		= db_prepare_int( $f_file_id );
	$c_title 		= db_prepare_string( $f_title );
	$c_description 	= db_prepare_string( $f_description );

	$f_file		= gpc_get_file( 'file' );
	$t_project_file_table = config_get( 'mantis_project_file_table' );

	#@@@ (thraxisp) this code should probably be integrated into file_api to share
	#  methods used to store files

	extract( $f_file, EXTR_PREFIX_ALL, 'v' );

	if ( !file_type_check( $v_name ) ) {
		trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
	}  

	if ( !is_readable( $v_tmp_name ) && DISK != config_get( 'file_upload_method' ) ) {
		trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
	}

	if ( is_uploaded_file( $v_tmp_name ) ) {
		$t_project_id = helper_get_current_project();

		# grab the original file path and name
		$t_disk_file_name = file_get_field( $f_file_id, 'diskfile', 'project' );
		$t_file_path = 	dirname( $t_disk_file_name );

		# prepare variables for insertion
		$c_title = db_prepare_string( $f_title );
		$c_description = db_prepare_string( $f_description );
		$c_file_name = db_prepare_string( $v_name );
		$c_file_type = db_prepare_string( $v_type );
		if ( is_readable ( $v_tmp_name ) ) { 
			$t_file_size = filesize( $v_tmp_name );
		} else {
				//try to get filesize from 'post' data
				//@@@ fixme - this should support >1 file ? 
			global $HTTP_POST_FILES;
			$t_file_size = $HTTP_POST_FILES['file']['size'];
		}
		$c_file_size = db_prepare_int( $t_file_size );

		$t_method = config_get( 'file_upload_method' );		
		switch ( $t_method ) {
			case FTP:
			case DISK:
						file_ensure_valid_upload_path( $t_file_path );

						if ( FTP == $t_method ) {
							$conn_id = file_ftp_connect();
							file_ftp_delete ( $conn_id, $t_disk_file_name );
							file_ftp_put ( $conn_id, $t_disk_file_name, $v_tmp_name );
							file_ftp_disconnect ( $conn_id );
						}
						if ( file_exists( $t_disk_file_name ) ) {
							file_delete_local( $t_disk_file_name );
						}
						umask( 0333 );  # make read only
						move_uploaded_file( $v_tmp_name, $t_disk_file_name );

						$c_content = '';
						break;
			case DATABASE:
						$c_content = db_prepare_string( fread ( fopen( $v_tmp_name, 'rb' ), $v_size ) );
						break;
			default:
				# @@@ Such errors should be checked in the admin checks
				trigger_error( ERROR_GENERIC, ERROR );
		}
		$t_now = db_now();
		$query = "UPDATE $t_project_file_table
			SET title='$c_title', description='$c_description', date_added=$t_now,
				filename='$c_file_name', filesize=$c_file_size, file_type='$c_file_type', content='$c_content'
				WHERE id='$c_file_id'";	
	}else{
		$query = "UPDATE $t_project_file_table
				SET title='$c_title', description='$c_description'
				WHERE id='$c_file_id'";
	}
	$result = db_query( $query );

	$t_redirect_url = 'proj_doc_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		trigger_error( ERROR_GENERIC, ERROR  );
	}
?>
