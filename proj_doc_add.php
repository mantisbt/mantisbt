<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_add.php,v 1.43 2004-08-31 13:11:50 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	if ( ! file_allow_project_upload() ) {
		access_denied();
	}

	$f_title		= gpc_get_string( 'title' );
	if ( is_blank( $f_title ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_description	= gpc_get_string( 'description' );
	$f_file		= gpc_get_file( 'file' );

	$result = 0;
	$good_upload = 0;
	$disallowed = 0;

	extract( $f_file, EXTR_PREFIX_ALL, 'v' );

	if ( !file_type_check( $v_name ) ) {
		$disallowed = 1;
	} else if ( is_uploaded_file( $v_tmp_name ) ) {
		$good_upload = 1;

		$t_project_id = helper_get_current_project();

		# grab the file path and name
		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		$t_prefix = config_get( 'document_files_prefix' );
		if ( !is_blank( $t_prefix ) ) {
			$t_prefix .= '-';
		}

		# prepare variables for insertion
		$c_title = db_prepare_string( $f_title );
		$c_description = db_prepare_string( $f_description );
		$c_file_path = db_prepare_string( $t_file_path );
		$c_file_name = db_prepare_string( $v_name );
		$c_file_type = db_prepare_string( $v_type );
		$c_file_size = db_prepare_int( $v_size );

		$t_disk_file_name = $t_file_path . file_generate_unique_name( $t_prefix . project_format_id ( $t_project_id ) . '-' . $v_name, $t_file_path );
		$c_disk_file_name = db_prepare_string( $t_disk_file_name );


		$t_method = config_get( 'file_upload_method' );		
		switch ( $t_method ) {
			case FTP:
			case DISK:	file_ensure_valid_upload_path( $t_file_path );

						if ( !file_exists( $t_disk_file_name ) ) {
							if ( FTP == $t_method ) {
								$conn_id = file_ftp_connect();
								file_ftp_put ( $conn_id, $t_disk_file_name, $v_tmp_name );
								file_ftp_disconnect ( $conn_id );
							}
							umask( 0333 );  # make read only
							move_uploaded_file( $v_tmp_name, $t_disk_file_name );
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
		
		$t_project_file_table = config_get( 'mantis_project_file_table' );
		$query = "INSERT INTO $t_project_file_table
				(project_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
				VALUES
				($t_project_id, '$c_title', '$c_description', '$t_disk_file_name', '$c_file_name', '$c_file_path', $c_file_size, '$c_file_type', " . db_now() .", '$c_content')";

		$result = db_query( $query );
	}

	$t_redirect_url = 'proj_doc_page.php';
?>
<?php html_page_top1() ?>
<?php
	if ( $result ) {
		html_meta_redirect( $t_redirect_url, $g_wait_time );
	}
?>
<?php html_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		print lang_get( 'operation_successful' ) . '<br />';
	} else {						# FAILURE
		if ( 1 == $disallowed ) {
			print error_string( ERROR_FILE_DISALLOWED ).'<br />';
		} else if ( 0 == $good_upload ) {
			print error_string( ERROR_NO_FILE_SPECIFIED ).'<br />';
		} else if ( !$result ) {
			print_sql_error( $query );
		}
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
