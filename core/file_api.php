<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage FileAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires history_api
 */
require_once( 'history_api.php' );
/**
 * requires bug_api
 */
require_once( 'bug_api.php' );

$g_cache_file_count = array();

# ## File API ###
# Gets the filename without the bug id prefix.
function file_get_display_name( $p_filename ) {
	$t_array = explode( '-', $p_filename, 2 );

	# Check if it's a project document filename (doc-0000000-filename)
	# or a bug attachment filename (0000000-filename)
	# for newer filenames, the filename in schema is correct.
	# This is important to handle filenames with '-'s properly
	$t_doc_match = '/^' . config_get( 'document_files_prefix' ) . '-\d{7}-/';
	$t_name = preg_split( $t_doc_match, $p_filename );
	if( isset( $t_name[1] ) ) {
		return $t_name[1];
	} else {
		$t_bug_match = '/^\d{7}-/';
		$t_name = preg_split( $t_bug_match, $p_filename );
		if( isset( $t_name[1] ) ) {
			return $t_name[1];
		} else {
			return $p_filename;
		}
	}
}

# Check the number of attachments a bug has (if any)
function file_bug_attachment_count( $p_bug_id ) {
	global $g_cache_file_count;

	$c_bug_id = db_prepare_int( $p_bug_id );
	$t_bug_file_table = db_get_table( 'mantis_bug_file_table' );

	# First check if we have a cache hit
	if( isset( $g_cache_file_count[$p_bug_id] ) ) {
		return $g_cache_file_count[$p_bug_id];
	}

	# If there is no cache hit, check if there is anything in
	#   the cache. If the cache isn't empty and we didn't have
	#   a hit, then there are not attachments for this bug.
	if( count( $g_cache_file_count ) > 0 ) {
		return 0;
	}

	# Otherwise build the cache and return the attachment count
	#   for the given bug (if any).
	$query = "SELECT bug_id, COUNT(bug_id) AS attachments
				FROM $t_bug_file_table
				GROUP BY bug_id";
	$result = db_query_bound( $query );

	$t_file_count = 0;
	while( $row = db_fetch_array( $result ) ) {
		$g_cache_file_count[$row['bug_id']] = $row['attachments'];
		if( $p_bug_id == $row['bug_id'] ) {
			$t_file_count = $row['attachments'];
		}
	}

	# If no attachments are present, mark the cache to avoid
	#   repeated queries for this.
	if( count( $g_cache_file_count ) == 0 ) {
		$g_cache_file_count['_no_files_'] = -1;
	}

	return $t_file_count;
}

# Check if a specific bug has attachments
function file_bug_has_attachments( $p_bug_id ) {
	if( file_bug_attachment_count( $p_bug_id ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

# Check if the current user can view attachments for the specified bug.
function file_can_view_bug_attachments( $p_bug_id, $p_uploader_user_id = null ) {
	$t_uploaded_by_me = auth_get_current_user_id() === $p_uploader_user_id;
	$t_can_view = access_has_bug_level( config_get( 'view_attachments_threshold' ), $p_bug_id );
	$t_can_view = $t_can_view || ( $t_uploaded_by_me && config_get( 'allow_view_own_attachments' ) );
	return $t_can_view;
}

# Check if the current user can download attachments for the specified bug.
function file_can_download_bug_attachments( $p_bug_id, $p_uploader_user_id = null ) {
	$t_uploaded_by_me = auth_get_current_user_id() === $p_uploader_user_id;
	$t_can_download = access_has_bug_level( config_get( 'download_attachments_threshold' ), $p_bug_id );
	$t_can_download = $t_can_download || ( $t_uploaded_by_me && config_get( 'allow_download_own_attachments' ) );
	return $t_can_download;
}

# Check if the current user can delete attachments from the specified bug.
function file_can_delete_bug_attachments( $p_bug_id, $p_uploader_user_id = null ) {
	if( bug_is_readonly( $p_bug_id ) ) {
		return false;
	}
	$t_uploaded_by_me = auth_get_current_user_id() === $p_uploader_user_id;
	$t_can_delete = access_has_bug_level( config_get( 'delete_attachments_threshold' ), $p_bug_id );
	$t_can_delete = $t_can_delete || ( $t_uploaded_by_me && config_get( 'allow_delete_own_attachments' ) );
	return $t_can_delete;
}

# Get icon corresponding to the specified filename
# returns an associative array with "url" and "alt" text.
function file_get_icon_url( $p_display_filename ) {
	$t_file_type_icons = config_get( 'file_type_icons' );

	$ext = utf8_strtolower( file_get_extension( $p_display_filename ) );
	if( is_blank( $ext ) || !isset( $t_file_type_icons[$ext] ) ) {
		$ext = '?';
	}

	$t_name = $t_file_type_icons[$ext];
	return array( 'url' => config_get( 'icon_path' ) . 'fileicons/' . $t_name, 'alt' => $ext );
}

/**
 * Combines a path and a file name making sure that the separator exists.
 *
 * @param string $p_path       The path.
 * @param string $p_filename   The file name.
 *
 * @return The combined full path.
 */
function file_path_combine( $p_path, $p_filename ) {
	$t_path = $p_path;
	if ( utf8_substr( $t_path, -1 ) != '/' && utf8_substr( $t_path, -1 ) != '\\' ) {
		$t_path .= DIRECTORY_SEPARATOR;
	}

	$t_path .= $p_filename;

	return $t_path;
}

/**
 * Nomalizes the disk file path based on the following algorithm:
 * 1. If disk file exists, then return as is.
 * 2. If not, and a project path is available, then check with that, if exists return it.
 * 3. If not, then use default upload path, then check with that, if exists return it.
 * 4. If disk file doesn't include a path, then return expected path based on project path or default path.
 * 5. Otherwise return as is.
 *
 * @param string $p_diskfile  The disk file (full path or just filename).
 * @param integer The project id - shouldn't be 0 (ALL_PROJECTS).
 * @return The normalized full path.
 */
function file_normalize_attachment_path( $p_diskfile, $p_project_id ) {
	if ( file_exists( $p_diskfile ) ) {
		return $p_diskfile;
	}

	$t_basename = basename( $p_diskfile );

	$t_expected_file_path = '';

	if ( $p_project_id != ALL_PROJECTS ) {
		$t_path = project_get_field( $p_project_id, 'file_path' );
		if ( !is_blank( $t_path ) ) {
			$t_diskfile = file_path_combine( $t_path, $t_basename );

			if ( file_exists( $t_diskfile ) ) {
				return $t_diskfile;
			}

			// if we don't find the file, then this is the path we want to return.
			$t_expected_file_path = $t_diskfile;
		}
	}

	$t_path = config_get( 'absolute_path_default_upload_folder' );
	if ( !is_blank( $t_path ) ) {
		$t_diskfile = file_path_combine( $t_path, $t_basename );

		if ( file_exists( $t_diskfile ) ) {
			return $t_diskfile;
		}

		// if the expected path not set to project directory, then set it to default directory.
		if ( is_blank( $t_expected_file_path ) ) {
			$t_expected_file_path = $t_diskfile;
		}
	}

	// if diskfile doesn't include a path, then use the expected filename.
	if ( ( strstr( $p_diskfile, DIRECTORY_SEPARATOR ) === false ||
	       strstr( $p_diskfile, '\\' ) === false ) &&
	     !is_blank( $t_expected_file_path ) ) {
	    return $t_expected_file_path;
	}

	// otherwise return as is.
	return $p_diskfile;
}

# --------------------
# Gets an array of attachments that are visible to the currently logged in user.
# Each element of the array contains the following:
# display_name - The attachment display name (i.e. file name dot extension)
# size - The attachment size in bytes.
# date_added - The date where the attachment was added.
# can_download - true: logged in user has access to download the attachment, false: otherwise.
# diskfile - The name of the file on disk.  Typically this is a hash without an extension.
# download_url - The download URL for the attachment (only set if can_download is true).
# exists - Applicable for DISK attachments.  true: file exists, otherwise false.
# can_delete - The logged in user can delete the attachments.
# preview - true: the attachment should be previewable, otherwise false.
# type - Can be "image", "text" or empty for other types.
# alt - The alternate text to be associated with the icon.
# icon - array with icon information, contains 'url' and 'alt' elements.
function file_get_visible_attachments( $p_bug_id ) {
	$t_attachment_rows = bug_get_attachments( $p_bug_id );
	$t_visible_attachments = array();

	$t_attachments_count = count( $t_attachment_rows );
	if( $t_attachments_count === 0 ) {
		return $t_visible_attachments;
	}

	$t_attachments = array();

	$t_preview_text_ext = config_get( 'preview_text_extensions' );
	$t_preview_image_ext = config_get( 'preview_image_extensions' );

	$image_previewed = false;
	for( $i = 0;$i < $t_attachments_count;$i++ ) {
		$t_row = $t_attachment_rows[$i];

		if ( !file_can_view_bug_attachments( $p_bug_id, (int)$t_row['user_id'] ) ) {
			continue;
		}

		$t_id = $t_row['id'];
		$t_filename = $t_row['filename'];
		$t_filesize = $t_row['filesize'];
		$t_diskfile = file_normalize_attachment_path( $t_row['diskfile'], bug_get_field( $p_bug_id, 'project_id' ) );
		$t_date_added = $t_row['date_added'];

		$t_attachment = array();
		$t_attachment['id'] = $t_id;
		$t_attachment['display_name'] = file_get_display_name( $t_filename );
		$t_attachment['size'] = $t_filesize;
		$t_attachment['date_added'] = $t_date_added;
		$t_attachment['diskfile'] = $t_diskfile;

		$t_attachment['can_download'] = file_can_download_bug_attachments( $p_bug_id, (int)$t_row['user_id'] );
		$t_attachment['can_delete'] = file_can_delete_bug_attachments( $p_bug_id, (int)$t_row['user_id'] );

		if( $t_attachment['can_download'] ) {
			$t_attachment['download_url'] = "file_download.php?file_id=$t_id&type=bug";
		}

		if( $image_previewed ) {
			$image_previewed = false;
		}

		$t_attachment['exists'] = config_get( 'file_upload_method' ) != DISK || file_exists( $t_diskfile );
		$t_attachment['icon'] = file_get_icon_url( $t_attachment['display_name'] );

		$t_attachment['preview'] = false;
		$t_attachment['type'] = '';

		$t_ext = strtolower( file_get_extension( $t_attachment['display_name'] ) );
		$t_attachment['alt'] = $t_ext;

		if ( $t_attachment['exists'] && $t_attachment['can_download'] && $t_filesize != 0 && $t_filesize <= config_get( 'preview_attachments_inline_max_size' ) ) {
			if ( in_array( $t_ext, $t_preview_text_ext, true ) ) {
				$t_attachment['preview'] = true;
				$t_attachment['type'] = 'text';
			} else if ( in_array( $t_ext, $t_preview_image_ext, true ) ) {
				$t_attachment['preview'] = true;
				$t_attachment['type'] = 'image';
			}
		}

		$t_attachments[] = $t_attachment;
	}

	return $t_attachments;
}

# delete all files that are associated with the given bug
function file_delete_attachments( $p_bug_id ) {
	$c_bug_id = db_prepare_int( $p_bug_id );

	$t_bug_file_table = db_get_table( 'mantis_bug_file_table' );

	$t_method = config_get( 'file_upload_method' );

	# Delete files from disk
	$query = "SELECT diskfile, filename
				FROM $t_bug_file_table
				WHERE bug_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bug_id ) );

	$file_count = db_num_rows( $result );
	if( 0 == $file_count ) {
		return true;
	}

	if(( DISK == $t_method ) || ( FTP == $t_method ) ) {

		# there may be more than one file
		$ftp = 0;
		if( FTP == $t_method ) {
			$ftp = file_ftp_connect();
		}

		for( $i = 0;$i < $file_count;$i++ ) {
			$row = db_fetch_array( $result );

			$t_local_diskfile = file_normalize_attachment_path( $row['diskfile'], bug_get_field( $p_bug_id, 'project_id' ) );
			file_delete_local( $t_local_diskfile );

			if( FTP == $t_method ) {
				file_ftp_delete( $ftp, $row['diskfile'] );
			}
		}

		if( FTP == $t_method ) {
			file_ftp_disconnect( $ftp );
		}
	}

	# Delete the corresponding db records
	$query = "DELETE FROM $t_bug_file_table
				  WHERE bug_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bug_id ) );

	# db_query errors on failure so:
	return true;
}

function file_delete_project_files( $p_project_id ) {
	$t_project_file_table = db_get_table( 'mantis_project_file_table' );
	$t_method = config_get( 'file_upload_method' );

	# Delete the file physically (if stored via DISK or FTP)
	if(( DISK == $t_method ) || ( FTP == $t_method ) ) {

		# Delete files from disk
		$query = "SELECT diskfile, filename
					FROM $t_project_file_table
					WHERE project_id=" . db_param();
		$result = db_query_bound( $query, array( (int) $p_project_id ) );

		$file_count = db_num_rows( $result );

		$ftp = 0;
		if( FTP == $t_method ) {
			$ftp = file_ftp_connect();
		}

		for( $i = 0;$i < $file_count;$i++ ) {
			$row = db_fetch_array( $result );

			$t_local_diskfile = file_normalize_attachment_path( $row['diskfile'], $p_project_id );
			file_delete_local( $t_local_diskfile );

			if( FTP == $t_method ) {
				file_ftp_delete( $ftp, $row['diskfile'] );
			}
		}

		if( FTP == $t_method ) {
			file_ftp_disconnect( $ftp );
		}
	}

	# Delete the corresponding db records
	$query = "DELETE FROM $t_project_file_table
				WHERE project_id=" . db_param();
	$result = db_query_bound( $query, Array( (int) $p_project_id ) );
}

# Delete all cached files that are older than configured number of days.
function file_ftp_cache_cleanup() {
}

# Connect to ftp server using configured server address, user name, and password.
function file_ftp_connect() {
	$conn_id = ftp_connect( config_get( 'file_upload_ftp_server' ) );
	$login_result = ftp_login( $conn_id, config_get( 'file_upload_ftp_user' ), config_get( 'file_upload_ftp_pass' ) );

	if(( !$conn_id ) || ( !$login_result ) ) {
		trigger_error( ERROR_FTP_CONNECT_ERROR, ERROR );
	}

	return $conn_id;
}

# Put a file to the ftp server.
function file_ftp_put( $p_conn_id, $p_remote_filename, $p_local_filename ) {
	helper_begin_long_process();
	$upload = ftp_put( $p_conn_id, $p_remote_filename, $p_local_filename, FTP_BINARY );
}

# Get a file from the ftp server.
function file_ftp_get( $p_conn_id, $p_local_filename, $p_remote_filename ) {
	helper_begin_long_process();
	$download = ftp_get( $p_conn_id, $p_local_filename, $p_remote_filename, FTP_BINARY );
}

# Delete a file from the ftp server
function file_ftp_delete( $p_conn_id, $p_filename ) {
	@ftp_delete( $p_conn_id, $p_filename );
}

# Disconnect from the ftp server
function file_ftp_disconnect( $p_conn_id ) {
	ftp_quit( $p_conn_id );
}

# Delete a local file even if it is read-only.
function file_delete_local( $p_filename ) {
	if( file_exists( $p_filename ) ) {
		chmod( $p_filename, 0775 );
		unlink( $p_filename );
	}
}

# Return the specified field value
function file_get_field( $p_file_id, $p_field_name, $p_table = 'bug' ) {
	$c_field_name = db_prepare_string( $p_field_name );
	$t_bug_file_table = db_get_table( 'mantis_' . $p_table . '_file_table' );

	# get info
	$query = "SELECT $c_field_name
				  FROM $t_bug_file_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( (int) $p_file_id ), 1 );

	return db_result( $result );
}

function file_delete( $p_file_id, $p_table = 'bug' ) {
	$t_upload_method = config_get( 'file_upload_method' );

	$c_file_id = db_prepare_int( $p_file_id );
	$t_filename = file_get_field( $p_file_id, 'filename', $p_table );
	$t_diskfile = file_get_field( $p_file_id, 'diskfile', $p_table );

	if ( $p_table == 'bug' ) {
		$t_bug_id = file_get_field( $p_file_id, 'bug_id', $p_table );
		$t_project_id = bug_get_field( $t_bug_id, 'project_id' );
	} else {
		$t_project_id = file_get_field( $p_file_id, 'project_id', $p_table );
	}

	if(( DISK == $t_upload_method ) || ( FTP == $t_upload_method ) ) {
		if( FTP == $t_upload_method ) {
			$ftp = file_ftp_connect();
			file_ftp_delete( $ftp, $t_diskfile );
			file_ftp_disconnect( $ftp );
		}

		$t_local_disk_file = file_normalize_attachment_path( $t_diskfile, $t_project_id );
		if ( file_exists( $t_local_disk_file ) ) {
			file_delete_local( $t_local_disk_file );
		}
	}

	if( 'bug' == $p_table ) {
		# log file deletion
		history_log_event_special( $t_bug_id, FILE_DELETED, file_get_display_name( $t_filename ) );
	}

	$t_file_table = db_get_table( 'mantis_' . $p_table . '_file_table' );
	$query = "DELETE FROM $t_file_table
				WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_file_id ) );
	return true;
}

# File type check
function file_type_check( $p_file_name ) {
	$t_allowed_files = config_get( 'allowed_files' );
	$t_disallowed_files = config_get( 'disallowed_files' );;

	# grab extension
	$t_extension = file_get_extension( $p_file_name );

	# check against disallowed files
	if( !is_blank( $t_disallowed_files ) ) {
		$t_disallowed_arr = explode( ',', $t_disallowed_files );
		foreach( $t_disallowed_arr as $t_val ) {
			if( 0 == strcasecmp( $t_val, $t_extension ) ) {
				return false;
			}
		}
	}

	# if the allowed list is note populated then the file must be allowed
	if( is_blank( $t_allowed_files ) ) {
		return true;
	}

	# check against allowed files
	$t_allowed_arr = explode( ',', $t_allowed_files );
	foreach( $t_allowed_arr as $t_val ) {
		if( 0 == strcasecmp( $t_val, $t_extension ) ) {
			return true;
		}
	}

	return false;
}

# clean file name by removing sensitive characters and replacing them with underscores
function file_clean_name( $p_filename ) {
	return preg_replace( '/[\/*?"<>|\\ :&]/', "_", $p_filename );
}

# Generate a string to use as the identifier for the file
# It is not guaranteed to be unique and should be checked
# The string returned should be 32 characters in length
function file_generate_name( $p_seed ) {
	return md5( $p_seed . time() );
}

# Generate a UNIQUE string to use as the identifier for the file
# The string returned should be 64 characters in length
function file_generate_unique_name( $p_seed, $p_filepath ) {
	do {
		$t_string = file_generate_name( $p_seed );
	}
	while( !diskfile_is_name_unique( $t_string, $p_filepath ) );

	return $t_string;
}

# Return true if the diskfile name identifier is unique, false otherwise
function diskfile_is_name_unique( $p_name, $p_filepath ) {
	$t_file_table = db_get_table( 'mantis_bug_file_table' );

	$c_name = $p_filepath . $p_name;

	$query = "SELECT COUNT(*)
				  FROM $t_file_table
				  WHERE diskfile=" . db_param();
	$result = db_query_bound( $query, Array( $c_name ) );
	$t_count = db_result( $result );

	if( $t_count > 0 ) {
		return false;
	} else {
		return true;
	}
}

# Return true if the file name identifier is unique, false otherwise
function file_is_name_unique( $p_name, $p_bug_id ) {
	$t_file_table = db_get_table( 'mantis_bug_file_table' );

	$query = "SELECT COUNT(*)
				  FROM $t_file_table
				  WHERE filename=" . db_param() . " AND bug_id=" . db_param();
	$result = db_query_bound( $query, Array( $p_name, $p_bug_id ) );
	$t_count = db_result( $result );

	if( $t_count > 0 ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Add a file to the system using the configured storage method
 *
 * @param integer $p_bug_id the bug id
 * @param array $p_file the uploaded file info, as retrieved from gpc_get_file()
 */
function file_add( $p_bug_id, $p_file, $p_table = 'bug', $p_title = '', $p_desc = '', $p_user_id = null ) {

	file_ensure_uploaded( $p_file );
	$t_file_name = $p_file['name'];
	$t_tmp_file = $p_file['tmp_name'];

	if( !file_type_check( $t_file_name ) ) {
		trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
	}

	if( !file_is_name_unique( $t_file_name, $p_bug_id ) ) {
		trigger_error( ERROR_FILE_DUPLICATE, ERROR );
	}

	if( 'bug' == $p_table ) {
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
		$t_bug_id = bug_format_id( $p_bug_id );
	} else {
		$t_project_id = helper_get_current_project();
		$t_bug_id = 0;
	}

	if( $p_user_id === null ) {
		$c_user_id = auth_get_current_user_id();
	} else {
		$c_user_id = (int)$p_user_id;
	}

	# prepare variables for insertion
	$c_bug_id = db_prepare_int( $p_bug_id );
	$c_project_id = db_prepare_int( $t_project_id );
	$c_file_type = db_prepare_string( $p_file['type'] );
	$c_title = db_prepare_string( $p_title );
	$c_desc = db_prepare_string( $p_desc );

	if( $t_project_id == ALL_PROJECTS ) {
		$t_file_path = config_get( 'absolute_path_default_upload_folder' );
	} else {
		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		if( is_blank( $t_file_path ) ) {
			$t_file_path = config_get( 'absolute_path_default_upload_folder' );
		}
	}

	$c_file_path = db_prepare_string( $t_file_path );
	$c_new_file_name = db_prepare_string( $t_file_name );

	$t_file_hash = ( 'bug' == $p_table ) ? $t_bug_id : config_get( 'document_files_prefix' ) . '-' . $t_project_id;
	$t_unique_name = file_generate_unique_name( $t_file_hash . '-' . $t_file_name, $t_file_path );
	$t_disk_file_name = $t_file_path . $t_unique_name;
	$c_unique_name = db_prepare_string( $t_unique_name );

	$t_file_size = filesize( $t_tmp_file );
	if( 0 == $t_file_size ) {
		trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
	}
	$t_max_file_size = (int) min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
	if( $t_file_size > $t_max_file_size ) {
		trigger_error( ERROR_FILE_TOO_BIG, ERROR );
	}
	$c_file_size = db_prepare_int( $t_file_size );

	$t_method = config_get( 'file_upload_method' );

	switch( $t_method ) {
		case FTP:
		case DISK:
			file_ensure_valid_upload_path( $t_file_path );

			if( !file_exists( $t_disk_file_name ) ) {
				if( FTP == $t_method ) {
					$conn_id = file_ftp_connect();
					file_ftp_put( $conn_id, $t_disk_file_name, $t_tmp_file );
					file_ftp_disconnect( $conn_id );
				}

				if( !move_uploaded_file( $t_tmp_file, $t_disk_file_name ) ) {
					trigger_error( ERROR_FILE_MOVE_FAILED, ERROR );
				}

				chmod( $t_disk_file_name, config_get( 'attachments_file_permissions' ) );

				$c_content = "''";
			} else {
				trigger_error( ERROR_FILE_DUPLICATE, ERROR );
			}
			break;
		case DATABASE:
			$c_content = db_prepare_binary_string( fread( fopen( $t_tmp_file, 'rb' ), $t_file_size ) );
			break;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_file_table = db_get_table( 'mantis_' . $p_table . '_file_table' );
	$c_id = ( 'bug' == $p_table ) ? $c_bug_id : $c_project_id;

	$query = "INSERT INTO $t_file_table
						(" . $p_table . "_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content, user_id)
					  VALUES
						($c_id, '$c_title', '$c_desc', '$c_unique_name', '$c_new_file_name', '$c_file_path', $c_file_size, '$c_file_type', '" . db_now() . "', $c_content, $c_user_id)";
	db_query( $query );

	if( 'bug' == $p_table ) {

		# updated the last_updated date
		$result = bug_update_date( $p_bug_id );

		# log new bug
		history_log_event_special( $p_bug_id, FILE_ADDED, $t_file_name );
	}
}

# --------------------
# Return true if file uploading is enabled (in our config and PHP's),
#  false otherwise
function file_is_uploading_enabled() {
	if( ini_get_bool( 'file_uploads' ) && ( ON == config_get( 'allow_file_upload' ) ) ) {
		return true;
	} else {
		return false;
	}
}

# Check if the user can upload files for this project
#  return true if they can, false otherwise
#  the project defaults to the current project and the user to the current user
function file_allow_project_upload( $p_project_id = null, $p_user_id = null ) {
	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	return( file_is_uploading_enabled() && ( access_has_project_level( config_get( 'upload_project_file_threshold' ), $p_project_id, $p_user_id ) ) );
}

# --------------------
# Check if the user can upload files for this bug
#  return true if they can, false otherwise
#  the user defaults to the current user
#
#  if the bug null (the default) we answer whether the user can
#   upload a file to a new bug in the current project
function file_allow_bug_upload( $p_bug_id = null, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	# If uploads are disbled just return false
	if( !file_is_uploading_enabled() ) {
		return false;
	}

	if( null === $p_bug_id ) {

		# new bug
		$t_project_id = helper_get_current_project();

		# the user must be the reporter if they're reporting a new bug
		$t_reporter = true;
	} else {

		# existing bug
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		# check if the user is the reporter of the bug
		$t_reporter = bug_is_user_reporter( $p_bug_id, $p_user_id );
	}

	# *** If we ever wanted to have a per-project setting enabling file
	#     uploads, we'd want to check it here before exempting the reporter

	if( $t_reporter && ( ON == config_get( 'allow_reporter_upload' ) ) ) {
		return true;
	}

	# Check the access level against the config setting
	return access_has_project_level( config_get( 'upload_bug_file_threshold' ), $t_project_id, $p_user_id );
}

# --------------------
# checks whether the specified upload path exists and is writable
function file_ensure_valid_upload_path( $p_upload_path ) {
	if( !file_exists( $p_upload_path ) || !is_dir( $p_upload_path ) || !is_writable( $p_upload_path ) || !is_readable( $p_upload_path ) ) {
		trigger_error( ERROR_FILE_INVALID_UPLOAD_PATH, ERROR );
	}
}

/**
 * Ensure a file was uploaded
 *
 * This function perform various checks for determining if the upload
 * was successful
 *
 * @param array $p_file the uploaded file info, as retrieved from gpc_get_file()
 */
function file_ensure_uploaded( $p_file ) {
	switch( $p_file['error'] ) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			trigger_error( ERROR_FILE_TOO_BIG, ERROR );
			break;
		case UPLOAD_ERR_PARTIAL:
		case UPLOAD_ERR_NO_FILE:
			trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
			break;
		default:
			break;
	}

	if(( '' == $p_file['tmp_name'] ) || ( '' == $p_file['name'] ) ) {
		trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
	}
	if( !is_readable( $p_file['tmp_name'] ) ) {
		trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
	}
}

# Get extension given the filename or its full path.
function file_get_extension( $p_filename ) {
	$t_extension = '';
	$t_basename = $p_filename;
	if( utf8_strpos( $t_basename, '/' ) !== false ) {
		// Note that we can't use end(explode(...)) on a single line because
		// end() expects a reference to a variable and thus we first need to
		// copy the result of explode() into a variable that end() can modify.
		$t_components = explode( '/', $t_basename );
		$t_basename = end( $t_components );
	}
	if( utf8_strpos( $t_basename, '\\' ) !== false ) {
		$t_components = explode( '\\', $t_basename );
		$t_basename = end( $t_components );
	}
	if( utf8_strpos( $t_basename, '.' ) !== false ) {
		$t_components = explode( '.', $t_basename );
		$t_extension = end( $t_components );
	}
	return $t_extension;
}

/**
 *
 * Copies all attachments from the source bug to the destination bug
 *
 * <p>Does not perform history logging and does not perform access checks.</p>
 *
 * @param int $p_source_bug_id
 * @param int $p_dest_bug_id
 */
function file_copy_attachments( $p_source_bug_id, $p_dest_bug_id ) {

    $t_mantis_bug_file_table = db_get_table( 'mantis_bug_file_table' );

    $query = 'SELECT * FROM ' . $t_mantis_bug_file_table . ' WHERE bug_id = ' . db_param();
    $result = db_query_bound( $query, Array( $p_source_bug_id ) );
    $t_count = db_num_rows( $result );

    $t_bug_file = array();
    for( $i = 0;$i < $t_count;$i++ ) {
        $t_bug_file = db_fetch_array( $result );

        # prepare the new diskfile name and then copy the file
        $t_file_path = $t_bug_file['folder'];
        $t_new_diskfile_name = $t_file_path . file_generate_unique_name( 'bug-' . $t_bug_file['filename'], $t_file_path );
        $t_new_file_name = file_get_display_name( $t_bug_file['filename'] );
        if(( config_get( 'file_upload_method' ) == DISK ) ) {
            copy( $t_file_path.$t_bug_file['diskfile'], $t_new_diskfile_name );
            chmod( $t_new_diskfile_name, config_get( 'attachments_file_permissions' ) );
        }

        $query = "INSERT INTO $t_mantis_bug_file_table
    						( bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content )
    						VALUES ( " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ",
    								 " . db_param() . ");";
        db_query_bound( $query, Array( $p_dest_bug_id, $t_bug_file['title'], $t_bug_file['description'], $t_new_diskfile_name, $t_new_file_name, $t_bug_file['folder'], $t_bug_file['filesize'], $t_bug_file['file_type'], $t_bug_file['date_added'], $t_bug_file['content'] ) );
    }
}

/**
 * Returns a possibly override content type for a file name
 *
 * @param string $p_filename the filename of the file which will be downloaded
 * @return string the content type, or empty if it should not be overriden
 */
function file_get_content_type_override( $p_filename ) {

	global $g_file_download_content_type_overrides;

	$t_extension = pathinfo( $p_filename, PATHINFO_EXTENSION );

	return $g_file_download_content_type_overrides[$t_extension];
}
