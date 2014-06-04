<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Check if the current user can download attachments for the specified bug.
 * @param int $p_bug_id Bug id
 * @param int $p_user_id User id
 * @return bool
 */
function mci_file_can_download_bug_attachments( $p_bug_id, $p_user_id ) {
	$t_can_download = access_has_bug_level( config_get( 'download_attachments_threshold' ), $p_bug_id );
	if( $t_can_download ) {
		return true;
	}

	$t_reported_by_me = bug_is_user_reporter( $p_bug_id, $p_user_id );
	return( $t_reported_by_me && config_get( 'allow_download_own_attachments' ) );
}

/**
 * Read a local file and return its content.
 * @param string $p_diskfile name of file on disk
 * @return string
 */
function mci_file_read_local( $p_diskfile ) {
	$t_handle = fopen( $p_diskfile, "r" );
	$t_content = fread( $t_handle, filesize( $p_diskfile ) );
	fclose( $t_handle );
	return $t_content;
}

/**
 * Write a local file.
 * @param string $p_diskfile name of file on disk
 * @param string $p_content file content to write
 */
function mci_file_write_local( $p_diskfile, $p_content ) {
	$t_handle = fopen( $p_diskfile, "w" );
	fwrite( $t_handle, $p_content );
	fclose( $t_handle );
}

/**
 * Add a file
 * @param int $p_id file id
 * @param string $p_name file name
 * @param string $p_content file content to write
 * @param string $p_file_type file type
 * @param string $p_table database table name
 * @param string $p_title title
 * @param string $p_desc description
 * @param string $p_user_id user id
 * @return mixed
 */
function mci_file_add( $p_id, $p_name, $p_content, $p_file_type, $p_table, $p_title = '', $p_desc = '', $p_user_id = null ) {
	if( !file_type_check( $p_name ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client',  'File type not allowed.' );
	}
	if( !file_is_name_unique( $p_name, $p_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', 'Duplicate filename.' );
	}

	$t_file_size = strlen( $p_content );
	$t_max_file_size = (int) min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
	if( $t_file_size > $t_max_file_size ) {
		return SoapObjectsFactory::newSoapFault( 'Client',  'File is too big.' );
	}

	if( 'bug' == $p_table ) {
		$t_project_id = bug_get_field( $p_id, 'project_id' );
		$t_id = (int)$p_id;
		$t_issue_id = bug_format_id( $p_id );
	} else {
		$t_project_id = $p_id;
		$t_id = $t_project_id;
		$t_issue_id = 0;
	}

	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	if( $t_project_id == ALL_PROJECTS ) {
		$t_file_path = config_get( 'absolute_path_default_upload_folder' );
	} else {
		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		if( is_blank( $t_file_path ) ) {
			$t_file_path = config_get( 'absolute_path_default_upload_folder' );
		}
	}

	$t_file_hash = ( 'bug' == $p_table ) ? $t_issue_id : config_get( 'document_files_prefix' ) . '-' . $t_project_id;
	$t_unique_name = file_generate_unique_name( $t_file_hash . '-' . $p_name, $t_file_path );
	$t_disk_file_name = $t_file_path . $t_unique_name;

	$t_method = config_get( 'file_upload_method' );

	switch( $t_method ) {
		case DISK:
			if( !file_exists( $t_file_path ) || !is_dir( $t_file_path ) || !is_writable( $t_file_path ) || !is_readable( $t_file_path ) ) {
				return SoapObjectsFactory::newSoapFault( 'Server', "Upload folder '{$t_file_path}' doesn't exist.");
			}

			file_ensure_valid_upload_path( $t_file_path );

			if( !file_exists( $t_disk_file_name ) ) {
				mci_file_write_local( $t_disk_file_name, $p_content );
				chmod( $t_disk_file_name, config_get( 'attachments_file_permissions' ) );
				$c_content = "''";
			}
			break;
		case DATABASE:
			$c_content = db_prepare_binary_string( $p_content );
			break;
	}

	$t_file_table = db_get_table( $p_table . '_file' );
	$t_id_col = $p_table . "_id";

	$query = "INSERT INTO $t_file_table
				( $t_id_col, title, description, diskfile, filename, folder, filesize, file_type, date_added, content, user_id )
		VALUES
				( " . db_param() . ", " . db_param() . ", " . db_param() . ", "
				    . db_param() . ", " . db_param() . ", " . db_param() . ", "
				    . db_param() . ", " . db_param() . ", " . db_param() . ", "
				    . db_param() . ", " . db_param() . " )";
	db_query_bound( $query, array(
		$t_id,
		$p_title,
		$p_desc,
		$t_unique_name,
		$p_name,
		$t_file_path,
		$t_file_size,
		$p_file_type,
		db_now(),
		$c_content,
		(int)$p_user_id,
	) );

	# get attachment id
	$t_attachment_id = db_insert_id( $t_file_table );

	if( 'bug' == $p_table ) {
		# bump the last_updated date
		bug_update_date( $t_issue_id );

		# add history entry
		history_log_event_special( $t_issue_id, FILE_ADDED, $p_name );
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
	$t_query = '';
	switch( $p_type ) {
		case 'bug':
			$t_bug_file_table = db_get_table( 'bug_file' );
			$t_query = "SELECT * FROM $t_bug_file_table WHERE id=" . db_param();
			break;
		case 'doc':
			$t_project_file_table = db_get_table( 'project_file' );
			$t_query = "SELECT * FROM $t_project_file_table WHERE id=" . db_param();
			break;
		default:
			return SoapObjectsFactory::newSoapFault( 'Server', 'Invalid file type '.$p_type. ' .' );
	}

	$t_result = db_query_bound( $t_query, array( $p_file_id ) );

	if( $t_result->EOF ) {
		return SoapObjectsFactory::newSoapFault( 'Client', 'Unable to find an attachment with type ' . $p_type. ' and id ' . $p_file_id . ' .' );
	}

	$row = db_fetch_array( $t_result );

	if( $p_type == 'doc' ) {
		$t_project_id = $row['project_id'];
	} else if( $p_type == 'bug' ) {
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
				return SoapObjectsFactory::newSoapFault(  'Client', 'Unable to find an attachment with type ' . $p_type. ' and id ' . $p_file_id . ' .' );
			}
		case DATABASE:
			return $t_content;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}
}
