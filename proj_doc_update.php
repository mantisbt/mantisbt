<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_update.php,v 1.22 2004-08-05 17:34:16 jlatour Exp $
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

	# @@@ Need to obtain the project_id from the file once we have an API for that	
	access_ensure_project_level( MANAGER );

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
	
	$result = 0;
	$good_upload = 0;
	$disallowed = 0;

	extract( $f_file, EXTR_PREFIX_ALL, 'v' );

	if ( !file_type_check( $v_name ) )
	{
		$disallowed = 1;
	}
	else if ( is_uploaded_file( $v_tmp_name ) )
	{
		$good_upload = 1;

		$t_project_id = helper_get_current_project();

		# grab the file path and name
		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		$t_prefix = config_get( 'document_files_prefix' );
		if ( !is_blank( $t_prefix ) ) {
			$t_prefix .= '-';
		}
		$t_file_name = $t_prefix . project_format_id ( $t_project_id ) . '-' . $v_name;

		# prepare variables for insertion
		$c_title = db_prepare_string( $f_title );
		$c_description = db_prepare_string( $f_description );
		$c_file_path = db_prepare_string( $t_file_path );
		$c_file_name = db_prepare_string( $t_file_name );
		$c_file_type = db_prepare_string( $v_type );
		$c_file_size = db_prepare_int( $v_size );

		$t_method = config_get( 'file_upload_method' );		
		switch ( $t_method ) {
			case FTP:
			case DISK:	file_ensure_valid_upload_path( $t_file_path );

						if ( !file_exists( $t_file_path.$t_file_name ) ) {
							if ( FTP == $t_method ) {
								$conn_id = file_ftp_connect();
								file_ftp_put ( $conn_id, $t_file_name, $v_tmp_name );
								file_ftp_disconnect ( $conn_id );
							}
							umask( 0333 );  # make read only
							copy( $v_tmp_name, $t_file_path . $t_file_name );
							$c_content = '';
						} else {
							trigger_error( ERROR_DUPLICATE_FILE, ERROR );
						}
						break;
			case DATABASE:
						$c_content = db_prepare_string( fread ( fopen( $v_tmp_name, 'rb' ), $v_size ) );
						break;
			default:
				# @@@ Such errors should be checked in the admin checks
				trigger_error( ERROR_GENERIC, ERROR );
		}

			
	}
	
	$t_project_file_table = config_get( 'mantis_project_file_table' );
	if ( 1 == $good_upload )
	{
		# New file
		$query = "UPDATE $t_project_file_table
			SET title='$c_title', description='$c_description', diskfile='$c_file_path$c_file_name',
			filename='$c_file_name', folder='$c_file_path', filesize=$c_file_size, file_type='$c_file_type', content='$c_content'
			WHERE id='$c_file_id'";	
	}
	else
	{
		$query = "UPDATE $t_project_file_table
				SET title='$c_title', description='$c_description'
				WHERE id='$c_file_id'";
	}
	$result = db_query( $query );

	$t_redirect_url = 'proj_doc_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
