<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

# Check if the current user can download attachments for the specified bug.
function mci_file_can_download_bug_attachments( $p_bug_id, $p_user_id ) {
	$t_can_download = access_has_bug_level( config_get( 'download_attachments_threshold' ), $p_bug_id );
	if( $t_can_download ) {
		return true;
	}

	$t_reported_by_me = bug_is_user_reporter( $p_bug_id, $p_user_id );
	return( $t_reported_by_me && config_get( 'allow_download_own_attachments' ) );
}

# Read a local file and return its content.
function mci_file_read_local( $p_diskfile ) {
	$t_handle = fopen( $p_diskfile, "r" );
	$t_content = fread( $t_handle, filesize( $p_diskfile ) );
	fclose( $t_handle );
	return $t_content;
}

# Write a local file.
function mci_file_write_local( $p_diskfile, $p_content ) {
	$t_handle = fopen( $p_diskfile, "w" );
	fwrite( $t_handle, $p_content );
	fclose( $t_handle );
}

function mci_file_add( $p_id, $p_name, $p_content, $p_file_type, $p_table, $p_title = '', $p_desc = '', $p_user_id = null ) {
	if( !file_type_check( $p_name ) ) {
		return new soap_fault( 'Client', '', 'File type not allowed.' );
	}
	if( !file_is_name_unique( $p_name, $p_id ) ) {
		return new soap_fault( 'Client', '', 'Duplicate filename.' );
	}

	$t_file_size = strlen( $p_content );
	$t_max_file_size = (int) min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
	if( $t_file_size > $t_max_file_size ) {
		return new soap_fault( 'Client', '', 'File is too big.' );
	}

	if( 'bug' == $p_table ) {
		$t_project_id = bug_get_field( $p_id, 'project_id' );
		$t_issue_id = bug_format_id( $p_id );
	} else {
		$t_project_id = $p_id;
		$t_issue_id = 0;
	}

	# prepare variables for insertion
	$c_issue_id = db_prepare_int( $t_issue_id );
	$c_project_id = db_prepare_int( $t_project_id );
	$c_file_type = db_prepare_string( $p_file_type );
	$c_title = db_prepare_string( $p_title );
	$c_desc = db_prepare_string( $p_desc );
	
	if( $p_user_id === null ) {
		$c_user_id = auth_get_current_user_id();
	} else {
		$c_user_id = (int)$p_user_id;
	}
	

	if( $t_project_id == ALL_PROJECTS ) {
		$t_file_path = config_get( 'absolute_path_default_upload_folder' );
	} else {
		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		if( $t_file_path == '' ) {
			$t_file_path = config_get( 'absolute_path_default_upload_folder' );
		}
	}

	$c_file_path = db_prepare_string( $t_file_path );
	$c_new_file_name = db_prepare_string( $p_name );

	$t_file_hash = $t_issue_id;
	$t_disk_file_name = $t_file_path . file_generate_unique_name( $t_file_hash . '-' . $p_name, $t_file_path );
	$c_disk_file_name = db_prepare_string( $t_disk_file_name );

	$t_file_size = strlen( $p_content );
	$c_file_size = db_prepare_int( $t_file_size );

	$t_method = config_get( 'file_upload_method' );

	switch( $t_method ) {
		case FTP:
		case DISK:
			if( !file_exists( $t_file_path ) || !is_dir( $t_file_path ) || !is_writable( $t_file_path ) || !is_readable( $t_file_path ) ) {
				return new soap_fault( 'Server', '', "Upload folder '{$t_file_path}' doesn't exist." );
			}

			file_ensure_valid_upload_path( $t_file_path );

			if( !file_exists( $t_disk_file_name ) ) {
				mci_file_write_local( $t_disk_file_name, $p_content );

				if( FTP == $t_method ) {
					$conn_id = file_ftp_connect();
					file_ftp_put( $conn_id, $t_disk_file_name, $t_disk_file_name );
					file_ftp_disconnect( $conn_id );
					file_delete_local( $t_disk_file_name );
				} else {
					chmod( $t_disk_file_name, config_get( 'attachments_file_permissions' ) );
				}

				$c_content = "''";
			}
			break;
		case DATABASE:
			$c_content = db_prepare_binary_string( $p_content );
			break;
	}

	$t_file_table = db_get_table( 'mantis_' . $p_table . '_file_table' );
	$c_id = ( 'bug' == $p_table ) ? $c_issue_id : $c_project_id;
	$query = "INSERT INTO $t_file_table
			(" . $p_table . "_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content, user_id)
		VALUES
			($c_id, '$c_title', '$c_desc', '$c_disk_file_name', '$c_new_file_name', '$c_file_path', $c_file_size, '$c_file_type', '" . db_now() . "', $c_content, $c_user_id)";
	db_query( $query );

	# get attachment id
	$t_attachment_id = db_insert_id( $t_file_table );

	if( 'bug' == $p_table ) {

		# updated the last_updated date
		$result = bug_update_date( $c_issue_id );

		# log new bug
		history_log_event_special( $c_issue_id, FILE_ADDED, $c_new_file_name );
	}

	return $t_attachment_id;
}
/**
 * Returns the attachment contents
 *  
 * @param int $p_file_id 
 * @param string $p_type The file type, bug or doc
 * @param int $p_user_id 
 * @return string|soap_fault the string contents, or a soap_fault
 */
function mci_file_get( $p_file_id, $p_type, $p_user_id ) {

	# we handle the case where the file is attached to a bug
	# or attached to a project as a project doc.
	$query = '';
	switch( $p_type ) {
		case 'bug':
			$t_bug_file_table = db_get_table( 'mantis_bug_file_table' );
			$query = "SELECT *
				FROM $t_bug_file_table
				WHERE id='$p_file_id'";
			break;
		case 'doc':
			$t_project_file_table = db_get_table( 'mantis_project_file_table' );
			$query = "SELECT *
				FROM $t_project_file_table
				WHERE id='$p_file_id'";
			break;
		default:
			return new soap_fault( 'Server', '', 'Invalid file type '.$p_type. ' .' );
	}

	$result = db_query( $query );
	
	if ( $result->EOF ) {
		return new soap_fault( 'Client', '', 'Unable to find an attachment with type ' . $p_type. ' and id ' . $p_file_id . ' .' );
	}
	
	$row = db_fetch_array( $result );

	if ( $p_type == 'doc' ) {
		$t_project_id = $row['project_id'];
	} else if ( $p_type == 'bug' ) {
		$t_bug_id = $row['bug_id'];
		$t_project_id = bug_get_field( $t_bug_id, 'project_id' );
	}

	$t_diskfile = file_normalize_attachment_path( $row['diskfile'], $t_project_id );
	$t_content = $row['content'];

	# Check access rights
	switch( $p_type ) {
		case 'bug':
			if( !mci_file_can_download_bug_attachments( $t_bug_id, $p_user_id ) ) {
				return mci_soap_fault_access_denied( $p_user_id );
			}
			break;
		case 'doc':
			# Check if project documentation feature is enabled.
			if( OFF == config_get( 'enable_project_documentation' ) ) {
				return mci_soap_fault_access_denied( $p_user_id );
			}
			if( !access_has_project_level( config_get( 'view_proj_doc_threshold' ), $t_project_id, $p_user_id ) ) {
				return mci_soap_fault_access_denied( $p_user_id );
			}
			break;
	}

	# dump file content to the connection.
	switch( config_get( 'file_upload_method' ) ) {
		case DISK:
			if( file_exists( $t_diskfile ) ) {
				return mci_file_read_local( $t_diskfile ) ;
			} else {
				return new soap_fault(  'Client', '', 'Unable to find an attachment with type ' . $p_type. ' and id ' . $p_file_id . ' .' );
			}
		case FTP:
			if( file_exists( $t_diskfile ) ) {
				return mci_file_read_local( $t_diskfile );
			} else {
				$ftp = file_ftp_connect();
				file_ftp_get( $ftp, $t_diskfile, $t_diskfile );
				file_ftp_disconnect( $ftp );
				return mci_file_read_local( $t_diskfile );
			}
		default:
			return $t_content;
	}
}
