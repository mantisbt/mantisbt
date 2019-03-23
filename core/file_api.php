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
 * File API
 *
 * @package CoreAPI
 * @subpackage FileAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses antispam_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses project_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'antispam_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'project_api.php' );
require_api( 'utility_api.php' );

use Mantis\Exceptions\ClientException;
use Mantis\Exceptions\ServiceException;

$g_cache_file_count = array();

/**
 * Attached specified files to issue.
 *
 * @param int $p_bug_id    The bug id.
 * @param array $p_files   The array of files, if null, then do nothing.
 * @return array Array of file info arrays.
 */
function file_attach_files( $p_bug_id, $p_files ) {
	if( $p_files === null || count( $p_files ) == 0 ) {
		return array();
	}

	$t_file_infos = array();
	foreach( $p_files as $t_file ) {
		if( !empty( $t_file['name'] ) ) {
			$t_file_infos[] = file_add( $p_bug_id, $t_file, 'bug' );
		}
	}

	return $t_file_infos;
}

/**
 * Gets the filename without the bug id prefix.
 * @param string $p_filename Filename.
 * @return string
 */
function file_get_display_name( $p_filename ) {
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

/**
 * Fills the cache with the attachment count from a list of bugs
 * If the bug doesn't have attachments, cache its value as 0.
 * @global array $g_cache_file_count
 * @param array $p_bug_ids Array of bug ids
 * @return void
 */
function file_bug_attachment_count_cache( array $p_bug_ids ) {
	global $g_cache_file_count;

	if( empty( $p_bug_ids ) ) {
		return;
	}

	$t_ids_to_search = array();
	foreach( $p_bug_ids as $t_id ) {
		$c_id = (int)$t_id;
		$t_ids_to_search[$c_id] = $c_id;
	}

	db_param_push();
	$t_params = array();
	$t_in_values = array();
	foreach( $t_ids_to_search as $t_id ) {
		$t_params[] = (int)$t_id;
		$t_in_values[] = db_param();
	}

	$t_query = 'SELECT B.id AS bug_id, COUNT(F.bug_id) AS attachments'
			. ' FROM {bug} B JOIN {bug_file} F ON ( B.id = F.bug_id )'
			. ' WHERE B.id IN (' . implode( ',', $t_in_values ) . ')'
			. ' GROUP BY B.id';

	$t_result = db_query( $t_query, $t_params );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_bug_id = (int)$t_row['bug_id'];
		$g_cache_file_count[$c_bug_id] = (int)$t_row['attachments'];
		unset( $t_ids_to_search[$c_bug_id] );
	}

	# set bugs without result to 0
	foreach( $t_ids_to_search as $t_id ) {
		$g_cache_file_count[$t_id] = 0;
	}
}

/**
 * Check the number of attachments a bug has (if any)
 * @param integer $p_bug_id A bug identifier.
 * @return integer
 */
function file_bug_attachment_count( $p_bug_id ) {
	global $g_cache_file_count;

	# If it's not in cache, load the value
	if( !isset( $g_cache_file_count[$p_bug_id] ) ) {
		file_bug_attachment_count_cache( array( (int)$p_bug_id ) );
	}

	return $g_cache_file_count[$p_bug_id];
}

/**
 * Clear a bug from the cache or all bugs if no bug id specified.
 * @param integer $p_bug_id A bug identifier to clear (optional).
 * @return boolean
 * @access public
 */
function file_bug_attachment_count_clear_cache( $p_bug_id = null ) {
	global $g_cache_file_count;

	if( null === $p_bug_id ) {
		$g_cache_file_count = array();
	} else {
		unset( $g_cache_file_count[(int)$p_bug_id] );
	}

	return true;
}

/**
 * Check if a specific bug has attachments
 * @param integer $p_bug_id A bug identifier.
 * @return boolean
 */
function file_bug_has_attachments( $p_bug_id ) {
	if( file_bug_attachment_count( $p_bug_id ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the current user can view attachments for the specified bug.
 * @param integer $p_bug_id           A bug identifier.
 * @param integer $p_uploader_user_id An user identifier.
 * @return boolean
 */
function file_can_view_bug_attachments( $p_bug_id, $p_uploader_user_id = null ) {
	$t_uploaded_by_me = auth_get_current_user_id() === $p_uploader_user_id;
	$t_can_view = access_has_bug_level( config_get( 'view_attachments_threshold' ), $p_bug_id );
	$t_can_view = $t_can_view || ( $t_uploaded_by_me && config_get( 'allow_view_own_attachments' ) );
	return $t_can_view;
}

/**
 * Check if the current user can download attachments for the specified bug.
 * @param integer $p_bug_id           A bug identifier.
 * @param integer $p_uploader_user_id An user identifier.
 * @return boolean
 */
function file_can_download_bug_attachments( $p_bug_id, $p_uploader_user_id = null ) {
	$t_uploaded_by_me = auth_get_current_user_id() === $p_uploader_user_id;
	$t_can_download = access_has_bug_level( config_get( 'download_attachments_threshold', null, null, bug_get_field( $p_bug_id, 'project_id' ) ), $p_bug_id );
	$t_can_download = $t_can_download || ( $t_uploaded_by_me && config_get( 'allow_download_own_attachments', null, null, bug_get_field( $p_bug_id, 'project_id' ) ) );
	return $t_can_download;
}

/**
 * Check if the current user can delete attachments from the specified bug.
 * @param integer $p_bug_id           A bug identifier.
 * @param integer $p_uploader_user_id An user identifier.
 * @return boolean
 */
function file_can_delete_bug_attachments( $p_bug_id, $p_uploader_user_id = null ) {
	if( bug_is_readonly( $p_bug_id ) ) {
		return false;
	}
	$t_uploaded_by_me = auth_get_current_user_id() === $p_uploader_user_id;
	$t_can_delete = access_has_bug_level( config_get( 'delete_attachments_threshold' ), $p_bug_id );
	$t_can_delete = $t_can_delete || ( $t_uploaded_by_me && config_get( 'allow_delete_own_attachments' ) );
	return $t_can_delete;
}

/**
 * Get icon corresponding to the specified filename
 * returns an associative array with "url" and "alt" text.
 * @param string $p_display_filename Filename.
 * @return array
 */
function file_get_icon_url( $p_display_filename ) {
	$t_file_type_icons = config_get_global( 'file_type_icons' );

	$t_ext = mb_strtolower( pathinfo( $p_display_filename, PATHINFO_EXTENSION ) );
	if( is_blank( $t_ext ) || !isset( $t_file_type_icons[$t_ext] ) ) {
		$t_ext = '?';
	}

	$t_name = $t_file_type_icons[$t_ext];
	return array( 'url' => $t_name, 'alt' => $t_ext );
}

/**
 * Combines a path and a file name making sure that the separator exists.
 *
 * @param string $p_path     The path.
 * @param string $p_filename The file name.
 * @return string The combined full path.
 */
function file_path_combine( $p_path, $p_filename ) {
	$t_path = rtrim( $p_path, '/\\' ) . DIRECTORY_SEPARATOR;

	$t_path .= $p_filename;

	return $t_path;
}

/**
 * Normalizes the disk file path based on the following algorithm:
 * 1. If disk file exists, then return as is.
 * 2. If not, and a project path is available, then check with that, if exists return it.
 * 3. If not, then use default upload path, then check with that, if exists return it.
 * 4. If disk file does not include a path, then return expected path based on project path or default path.
 * 5. Otherwise return as is.
 *
 * @param string  $p_diskfile   The disk file (full path or just filename).
 * @param integer $p_project_id The project id - shouldn't be 0 (ALL_PROJECTS).
 * @return string The normalized full path.
 */
function file_normalize_attachment_path( $p_diskfile, $p_project_id ) {
	if( file_exists( $p_diskfile ) ) {
		return $p_diskfile;
	}

	$t_basename = basename( $p_diskfile );

	$t_expected_file_path = '';

	if( $p_project_id != ALL_PROJECTS ) {
		$t_path = project_get_field( $p_project_id, 'file_path' );
		if( !is_blank( $t_path ) ) {
			$t_diskfile = file_path_combine( $t_path, $t_basename );

			if( file_exists( $t_diskfile ) ) {
				return $t_diskfile;
			}

			# if we don't find the file, then this is the path we want to return.
			$t_expected_file_path = $t_diskfile;
		}
	}

	$t_path = config_get_global( 'absolute_path_default_upload_folder' );
	if( !is_blank( $t_path ) ) {
		$t_diskfile = file_path_combine( $t_path, $t_basename );

		if( file_exists( $t_diskfile ) ) {
			return $t_diskfile;
		}

		# if the expected path not set to project directory, then set it to default directory.
		if( is_blank( $t_expected_file_path ) ) {
			$t_expected_file_path = $t_diskfile;
		}
	}

	# if diskfile doesn't include a path, then use the expected filename.
	if( ( strstr( $p_diskfile, DIRECTORY_SEPARATOR ) === false ||
	       strstr( $p_diskfile, '\\' ) === false ) &&
	     !is_blank( $t_expected_file_path ) ) {
	    return $t_expected_file_path;
	}

	# otherwise return as is.
	return $p_diskfile;
}

/**
 * Gets an array of attachments that are visible to the currently logged in user.
 * Each element of the array contains the following:
 * display_name - The attachment display name (i.e. file name dot extension)
 * size - The attachment size in bytes.
 * date_added - The date where the attachment was added.
 * can_download - true: logged in user has access to download the attachment, false: otherwise.
 * diskfile - The name of the file on disk.  Typically this is a hash without an extension.
 * download_url - The download URL for the attachment (only set if can_download is true).
 * exists - Applicable for DISK attachments.  true: file exists, otherwise false.
 * can_delete - The logged in user can delete the attachments.
 * preview - true: the attachment should be previewable, otherwise false.
 * type - Can be "image", "text" or empty for other types.
 * alt - The alternate text to be associated with the icon.
 * icon - array with icon information, contains 'url' and 'alt' elements.
 * @param integer $p_bug_id A bug identifier.
 * @return array
 */
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

	$t_image_previewed = false;
	for( $i = 0;$i < $t_attachments_count;$i++ ) {
		$t_row = $t_attachment_rows[$i];
		$t_user_id = (int)$t_row['user_id'];

		if( !file_can_view_bug_attachments( $p_bug_id, $t_user_id ) ) {
			continue;
		}

		$t_id = $t_row['id'];
		$t_filename = $t_row['filename'];
		$t_filesize = $t_row['filesize'];
		$t_diskfile = file_normalize_attachment_path( $t_row['diskfile'], bug_get_field( $p_bug_id, 'project_id' ) );
		$t_date_added = $t_row['date_added'];

		$t_attachment = array();
		$t_attachment['id'] = $t_id;
		$t_attachment['user_id'] = $t_user_id;
		$t_attachment['display_name'] = file_get_display_name( $t_filename );
		$t_attachment['size'] = $t_filesize;
		$t_attachment['date_added'] = $t_date_added;
		$t_attachment['diskfile'] = $t_diskfile;
		$t_attachment['file_type'] = $t_row['file_type'];

		$t_attachment['can_download'] = file_can_download_bug_attachments( $p_bug_id, (int)$t_row['user_id'] );
		$t_attachment['can_delete'] = file_can_delete_bug_attachments( $p_bug_id, (int)$t_row['user_id'] );

		if( $t_attachment['can_download'] ) {
			$t_attachment['download_url'] = 'file_download.php?file_id=' . $t_id . '&type=bug';
		}

		if( $t_image_previewed ) {
			$t_image_previewed = false;
		}

		$t_attachment['exists'] = config_get( 'file_upload_method' ) != DISK || file_exists( $t_diskfile );
		$t_attachment['icon'] = file_get_icon_url( $t_attachment['display_name'] );

		$t_attachment['preview'] = false;
		$t_attachment['type'] = '';

		$t_ext = strtolower( pathinfo( $t_attachment['display_name'], PATHINFO_EXTENSION ) );
		$t_attachment['alt'] = $t_ext;

		if( $t_attachment['exists'] && $t_attachment['can_download'] && $t_filesize != 0 && $t_filesize <= config_get( 'preview_attachments_inline_max_size' ) ) {
			if( in_array( $t_ext, $t_preview_text_ext, true ) ) {
				$t_attachment['preview'] = true;
				$t_attachment['type'] = 'text';
			} else if( in_array( $t_ext, $t_preview_image_ext, true ) ) {
				$t_attachment['preview'] = true;
				$t_attachment['type'] = 'image';
			}
		}

		$t_attachments[] = $t_attachment;
	}

	return $t_attachments;
}

/**
 * delete all files that are associated with the given bug
 * @param integer $p_bug_id A bug identifier.
 * @return boolean
 */
function file_delete_attachments( $p_bug_id ) {
	$t_method = config_get( 'file_upload_method' );

	# Delete files from disk
	db_param_push();
	$t_query = 'SELECT diskfile, filename FROM {bug_file} WHERE bug_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_bug_id ) );

	$t_file_count = db_num_rows( $t_result );
	if( 0 == $t_file_count ) {
		return true;
	}

	if( DISK == $t_method ) {
		for( $i = 0; $i < $t_file_count; $i++ ) {
			$t_row = db_fetch_array( $t_result );

			$t_local_diskfile = file_normalize_attachment_path( $t_row['diskfile'], bug_get_field( $p_bug_id, 'project_id' ) );
			file_delete_local( $t_local_diskfile );
		}
	}

	# Delete the corresponding db records
	db_param_push();
	$t_query = 'DELETE FROM {bug_file} WHERE bug_id=' . db_param();
	db_query( $t_query, array( $p_bug_id ) );

	# db_query() errors on failure so:
	return true;
}

/**
 * Delete files by project
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function file_delete_project_files( $p_project_id ) {
	$t_method = config_get( 'file_upload_method' );

	# Delete the file physically (if stored via DISK)
	if( DISK == $t_method ) {
		# Delete files from disk
		db_param_push();
		$t_query = 'SELECT diskfile, filename FROM {project_file} WHERE project_id=' . db_param();
		$t_result = db_query( $t_query, array( (int)$p_project_id ) );

		$t_file_count = db_num_rows( $t_result );

		for( $i = 0;$i < $t_file_count;$i++ ) {
			$t_row = db_fetch_array( $t_result );

			$t_local_diskfile = file_normalize_attachment_path( $t_row['diskfile'], $p_project_id );
			file_delete_local( $t_local_diskfile );
		}
	}

	# Delete the corresponding database records
	db_param_push();
	$t_query = 'DELETE FROM {project_file} WHERE project_id=' . db_param();
	db_query( $t_query, array( (int)$p_project_id ) );
}

/**
 * Delete a local file even if it is read-only.
 * @param string $p_filename File name.
 * @return void
 */
function file_delete_local( $p_filename ) {
	if( file_exists( $p_filename ) ) {
		chmod( $p_filename, 0775 );
		unlink( $p_filename );
	}
}

/**
 * Return the specified field value
 * @param integer $p_file_id    File identifier.
 * @param string  $p_field_name Database field name to retrieve.
 * @param string  $p_table      Database table name.
 * @return string
 */
function file_get_field( $p_file_id, $p_field_name, $p_table = 'bug' ) {
	$t_bug_file_table = db_get_table( $p_table . '_file' );
	if( !db_field_exists( $p_field_name, $t_bug_file_table ) ) {
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, ERROR );
	}

	db_param_push();
	$t_query = 'SELECT ' . $p_field_name . ' FROM ' . $t_bug_file_table . ' WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( (int)$p_file_id ), 1 );

	return db_result( $t_result );
}

/**
 * Delete File
 * @param integer $p_file_id File identifier.
 * @param string  $p_table   Table identifier.
 * @return boolean
 */
function file_delete( $p_file_id, $p_table = 'bug' ) {
	$t_upload_method = config_get( 'file_upload_method' );

	$c_file_id = (int)$p_file_id;
	$t_filename = file_get_field( $p_file_id, 'filename', $p_table );
	$t_diskfile = file_get_field( $p_file_id, 'diskfile', $p_table );

	if( $p_table == 'bug' ) {
		$t_bug_id = file_get_field( $p_file_id, 'bug_id', $p_table );
		$t_project_id = bug_get_field( $t_bug_id, 'project_id' );
	} else {
		$t_project_id = file_get_field( $p_file_id, 'project_id', $p_table );
	}

	if( DISK == $t_upload_method ) {
		$t_local_disk_file = file_normalize_attachment_path( $t_diskfile, $t_project_id );
		if( file_exists( $t_local_disk_file ) ) {
			file_delete_local( $t_local_disk_file );
		}
	}

	if( 'bug' == $p_table ) {
		# log file deletion
		history_log_event_special( $t_bug_id, FILE_DELETED, file_get_display_name( $t_filename ) );
	}

	$t_file_table = db_get_table( $p_table . '_file' );
	db_param_push();
	$t_query = 'DELETE FROM ' . $t_file_table . ' WHERE id=' . db_param();
	db_query( $t_query, array( $c_file_id ) );
	return true;
}

/**
 * File type check
 * @param string $p_file_name File name.
 * @return boolean
 */
function file_type_check( $p_file_name ) {
	$t_allowed_files = config_get( 'allowed_files' );
	$t_disallowed_files = config_get( 'disallowed_files' );

	# grab extension
	$t_extension = pathinfo( $p_file_name, PATHINFO_EXTENSION );

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

/**
 * clean file name by removing sensitive characters and replacing them with underscores
 * @param string $p_filename File name.
 * @return string
 */
function file_clean_name( $p_filename ) {
	return preg_replace( '/[\/*?"<>|\\ :&]/', '_', $p_filename );
}

/**
 * Generate a UNIQUE string for a given file path to use as the identifier for the file
 * The string returned should be 32 characters in length
 * @param string $p_filepath File path.
 * @return string
 */
function file_generate_unique_name( $p_filepath ) {
	do {
		$t_string = md5( crypto_generate_random_string( 32, false ) );
	} while( !diskfile_is_name_unique( $t_string, $p_filepath ) );

	return $t_string;
}

/**
 * Validates that the given disk file name identifier is unique, checking both
 * in the DB tables (bug and project) and on disk.
 * This ensures that in case a file has been deleted from disk but its record
 * remains in the DB, we never get in a situation where the DB points to a file
 * which is not the originally uploaded one.
 * @param string $p_name     File name.
 * @param string $p_filepath File path.
 * @return boolean true if unique
 */
function diskfile_is_name_unique( $p_name, $p_filepath ) {
	$c_name = $p_filepath . $p_name;

	db_param_push();
	$t_query = 'SELECT count(*)
		FROM (
			SELECT diskfile FROM {bug_file} WHERE diskfile=' . db_param() . '
			UNION
			SELECT diskfile FROM {project_file} WHERE diskfile=' . db_param() . '
			) f';
	$t_result = db_query( $t_query, array( $c_name, $c_name) );
	$t_count = db_result( $t_result );

	return ( $t_count == 0 ) && !file_exists( $c_name );
}

/**
 * Validates that the given file name is unique in the given context (we don't
 * allow multiple attachments with the same name for a given bug or project)
 * @param string  $p_name   File name.
 * @param integer $p_bug_id A bug identifier (not used for project files).
 * @param string  $p_table  Optional file table to check: 'project' or 'bug' (default).
 * @return boolean true if unique
 */
function file_is_name_unique( $p_name, $p_bug_id, $p_table = 'bug' ) {
	$t_file_table = db_get_table( "${p_table}_file" );

	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM ' . $t_file_table . ' WHERE filename=' . db_param();
	$t_param = array( $p_name );
	if( $p_table == 'bug' ) {
		$t_query .= ' AND bug_id=' . db_param();
		$t_param[] = $p_bug_id;
	}

	$t_result = db_query( $t_query, $t_param );
	$t_count = db_result( $t_result );

	return ( $t_count == 0 );
}

/**
 * Add a file to the system using the configured storage method
 *
 * If file was not uploaded by the browser standard POST method, set value
 * for key `browser_upload` on $p_file to false.  Otherwise, the file_add()
 * operation will fail.
 *
 * @param integer $p_bug_id          The bug id (should be 0 when adding project doc).
 * @param array   $p_file            The uploaded file info, as retrieved from gpc_get_file().
 * @param string  $p_table           Either 'bug' or 'project' depending on attachment type.
 * @param string  $p_title           File title.
 * @param string  $p_desc            File description.
 * @param integer $p_user_id         User id (defaults to current user).
 * @param integer $p_date_added      Date added.
 * @param boolean $p_skip_bug_update Skip bug last modification update (useful when importing bug attachments).
 * @return array The file info array (keys: name, size)
 */
function file_add( $p_bug_id, array $p_file, $p_table = 'bug', $p_title = '', $p_desc = '', $p_user_id = null, $p_date_added = 0, $p_skip_bug_update = false ) {
	$t_file_info = array();

	if( !isset( $p_file['error'] ) ) {
		$p_file['error'] = UPLOAD_ERR_OK;
	}

	if( !isset( $p_file['browser_upload'] ) ) {
		$p_file['browser_upload'] = true;
	}

	$t_tmp_file = $p_file['tmp_name'];

	# Override passed value with one detected by PHP (if available).
	# If PHP can't detect it, then use supplied value.
	# If no value supplied, then default to a reasonable value.
	# The value will be overridden by PHP anyway if content type is
	# known at rendering time.
	$t_type = file_get_mime_type( $t_tmp_file );
	if( $t_type !== false ) {
		$p_file['type'] = $t_type;
	} else if( !isset( $p_file['type'] ) ) {
		$p_file['type'] = 'application/octet-stream';
	}

	file_ensure_uploaded( $p_file );
	$t_file_name = $p_file['name'];

	if( !file_type_check( $t_file_name ) ) {
		throw new ClientException(
			sprintf( "File '%s' type not allowed", $t_file_name ),
			ERROR_FILE_NOT_ALLOWED
		);
	}

	$t_org_filename = $t_file_name;
	$t_suffix_id = 1;

	while( !file_is_name_unique( $t_file_name, $p_bug_id ) ) {
		$t_suffix_id++;

		$t_dot_index = strripos( $t_org_filename, '.' );
		if( $t_dot_index === false ) {
			$t_file_name = $t_org_filename . '-' . $t_suffix_id;
		} else {
			$t_extension = substr( $t_org_filename, $t_dot_index, strlen( $t_org_filename ) - $t_dot_index );
			$t_file_name = substr( $t_org_filename, 0, $t_dot_index ) . '-' . $t_suffix_id . $t_extension;
		}
	}

	$t_file_info['name'] = $t_file_name;
	antispam_check();

	$t_file_size = filesize( $t_tmp_file );
	if( 0 == $t_file_size ) {
		throw new ClientException(
			sprintf( "File '%s' not uploaded", $t_file_name ),
			ERROR_FILE_NO_UPLOAD_FAILURE );
	}

	$t_file_info['size'] = $t_file_size;

	$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
	if( $t_file_size > $t_max_file_size ) {
		throw new ClientException(
			sprintf( "File '%s' too big", $t_file_name ),
			ERROR_FILE_TOO_BIG );
	}

	if( 'bug' == $p_table ) {
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
		$t_id = (int)$p_bug_id;
	} else {
		$t_project_id = helper_get_current_project();
		$t_id = $t_project_id;
	}

	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	if( $p_date_added <= 0 ) {
		$p_date_added = db_now();
	}

	if( $t_project_id == ALL_PROJECTS ) {
		$t_file_path = config_get_global( 'absolute_path_default_upload_folder' );
	} else {
		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		if( is_blank( $t_file_path ) ) {
			$t_file_path = config_get_global( 'absolute_path_default_upload_folder' );
		}
	}

	$t_unique_name = file_generate_unique_name( $t_file_path );
	$t_method = config_get( 'file_upload_method' );

	switch( $t_method ) {
		case DISK:
			file_ensure_valid_upload_path( $t_file_path );

			$t_disk_file_name = $t_file_path . $t_unique_name;
			if( !file_exists( $t_disk_file_name ) ) {
				if( $p_file['browser_upload'] ) {
					if( !move_uploaded_file( $t_tmp_file, $t_disk_file_name ) ) {
						throw new ServiceException(
							'Unable to move uploaded file',
							ERROR_FILE_MOVE_FAILED
						);
					}
				} else {
					if( !copy( $t_tmp_file, $t_disk_file_name ) || !unlink( $t_tmp_file ) ) {
						throw new ServiceException(
							'Unable to move uploaded file',
							ERROR_FILE_MOVE_FAILED
						);
					}
				}

				chmod( $t_disk_file_name, config_get( 'attachments_file_permissions' ) );

				$c_content = '';
			} else {
				throw new ClientException( 'Duplicate file', ERROR_FILE_DUPLICATE );
			}
			break;
		case DATABASE:
			$c_content = db_prepare_binary_string( fread( fopen( $t_tmp_file, 'rb' ), $t_file_size ) );
			$t_file_path = '';
			break;
		default:
			throw new ServiceException( 'Unknown file upload method', ERROR_GENERIC );
	}

	$t_file_table = db_get_table( $p_table . '_file' );
	$t_id_col = $p_table . '_id';

	db_param_push();

	$t_param = array(
		$t_id_col     => $t_id,
		'title'       => $p_title,
		'description' => $p_desc,
		'diskfile'    => $t_unique_name,
		'filename'    => $t_file_name,
		'folder'      => $t_file_path,
		'filesize'    => $t_file_size,
		'file_type'   => $p_file['type'],
		'date_added'  => $p_date_added,
		'user_id'     => (int)$p_user_id,
	);
	# Oracle has to update BLOBs separately
	if( !db_is_oracle() ) {
		$t_param['content'] = $c_content;
	}
	$t_query_param = db_param();
	for( $i = 1; $i < count( $t_param ); $i++ ) {
		$t_query_param .= ', ' . db_param();
	}

	$t_query = 'INSERT INTO ' . $t_file_table . '
		( ' . implode(', ', array_keys( $t_param ) ) . ' )
	VALUES
		( ' . $t_query_param . ' )';
	db_query( $t_query, array_values( $t_param ) );

	if( db_is_oracle() ) {
		db_update_blob( $t_file_table, 'content', $c_content, "diskfile='$t_unique_name'" );
	}

	if( 'bug' == $p_table ) {
		# update the last_updated date
		if( !$p_skip_bug_update ) {
			bug_update_date( $p_bug_id );
		}

		# log file added to bug history
		history_log_event_special( $p_bug_id, FILE_ADDED, $t_file_name );
	}

	return $t_file_info;
}

/**
 * Return true if file uploading is enabled (in our config and PHP's), false otherwise
 * @return boolean
 */
function file_is_uploading_enabled() {
	if( ini_get_bool( 'file_uploads' ) && ( ON == config_get( 'allow_file_upload' ) ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the user can upload files for this project
 * return true if they can, false otherwise
 * the project defaults to the current project and the user to the current user
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user identifier.
 * @return boolean
 */
function file_allow_project_upload( $p_project_id = null, $p_user_id = null ) {
	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	return( file_is_uploading_enabled() && ( access_has_project_level( config_get( 'upload_project_file_threshold' ), $p_project_id, $p_user_id ) ) );
}

/**
 * Check if the user can upload files for this bug
 * return true if they can, false otherwise
 * the user defaults to the current user
 *
 * if the bug null (the default) we answer whether the user can
 * upload a file to a new bug in the current project
 * @param integer $p_bug_id  A bug identifier.
 * @param integer $p_user_id A user identifier.
 * @param integer $p_project_id The project id to check for or null for bug project or current project.
 * @return boolean
 */
function file_allow_bug_upload( $p_bug_id = null, $p_user_id = null, $p_project_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	# If uploads are disabled just return false
	if( !file_is_uploading_enabled() ) {
		return false;
	}

	if( null === $p_bug_id ) {
		# new bug
		$t_project_id = $p_project_id === null ? helper_get_current_project() : $p_project_id;

		# the user must be the reporter if they're reporting a new bug
		$t_reporter = true;
	} else {
		# existing bug
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		# check if the user is the reporter of the bug
		$t_reporter = bug_is_user_reporter( $p_bug_id, $p_user_id );
	}

	if( $t_reporter && ( ON == config_get( 'allow_reporter_upload' ) ) ) {
		return true;
	}

	# Check the access level against the config setting
	return access_has_project_level( config_get( 'upload_bug_file_threshold' ), $t_project_id, $p_user_id );
}

/**
 * checks whether the specified upload path exists and is writable
 * @param string $p_upload_path Upload path.
 * @return void
 */
function file_ensure_valid_upload_path( $p_upload_path ) {
	if( !file_exists( $p_upload_path ) || !is_dir( $p_upload_path ) || !is_writable( $p_upload_path ) || !is_readable( $p_upload_path ) ) {
		throw new ServiceException(
			'Upload folder not valid',
			ERROR_FILE_INVALID_UPLOAD_PATH );
	}
}

/**
 * Ensure a file was uploaded
 *
 * This function perform various checks for determining if the upload was successful
 *
 * @param array $p_file The uploaded file info, as retrieved from gpc_get_file().
 * @return void
 */
function file_ensure_uploaded( array $p_file ) {
	switch( $p_file['error'] ) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new ClientException(
				sprintf( "File '%s' too big", $p_file['name'] ),
				ERROR_FILE_TOO_BIG );

		case UPLOAD_ERR_PARTIAL:
		case UPLOAD_ERR_NO_FILE:
			throw new ClientException(
				sprintf( "File '%s' upload failure", $p_file['name'] ),
				ERROR_FILE_NO_UPLOAD_FAILURE );
	}

	if( ( '' == $p_file['tmp_name'] ) || ( '' == $p_file['name'] ) ) {
		throw new ClientException(
			'File name or path is empty',
			ERROR_FILE_NO_UPLOAD_FAILURE );
	}

	if( !is_readable( $p_file['tmp_name'] ) ) {
		throw new ClientException( 'File is not readable', ERROR_UPLOAD_FAILURE );
	}
}

/**
 * Return instance of fileinfo class
 * @return finfo instance of finfo class.
 */
function file_create_finfo() {
	$t_info_file = config_get_global( 'fileinfo_magic_db_file' );

	if( is_blank( $t_info_file ) ) {
		$t_finfo = new finfo( FILEINFO_MIME );
	} else {
		$t_finfo = new finfo( FILEINFO_MIME, $t_info_file );
	}

	return $t_finfo;
}

/**
 * Get mime type for the specified file.
 *
 * @param string $p_file_path The file path.
 * @return boolean|string The mime type or false on failure.
 */
function file_get_mime_type( $p_file_path ) {
	if( !file_exists( $p_file_path ) ) {
		return false;
	}

	$t_finfo = file_create_finfo();
	return $t_finfo->file( $p_file_path );
}

/**
 * Get mime type for the specified content.
 *
 * @param string $p_content The content.
 * @return boolean|string The mime type or false on failure.
 */
function file_get_mime_type_for_content( $p_content ) {
	$t_finfo = file_create_finfo();
	return $t_finfo->buffer( $p_content );
}

/**
 * Get file content
 *
 * @param integer $p_file_id File identifier.
 * @param string  $p_type    File type (either 'bug' or 'doc').
 * @return array|boolean array containing file type and content or false on failure to retrieve file
 */
function file_get_content( $p_file_id, $p_type = 'bug' ) {
	# we handle the case where the file is attached to a bug
	# or attached to a project as a project doc.
	db_param_push();
	switch( $p_type ) {
		case 'bug':
			$t_query = 'SELECT * FROM {bug_file} WHERE id=' . db_param();
			break;
		case 'doc':
			$t_query = 'SELECT * FROM {project_file} WHERE id=' . db_param();
			break;
		default:
			return false;
	}

	$t_result = db_query( $t_query, array( $p_file_id ) );
	$t_row = db_fetch_array( $t_result );

	if( $p_type == 'bug' ) {
		$t_project_id = bug_get_field( $t_row['bug_id'], 'project_id' );
	} else {
		$t_project_id = $t_row['bug_id'];
	}

	$t_content_type = $t_row['file_type'];

	switch( config_get( 'file_upload_method' ) ) {
		case DISK:
			$t_local_disk_file = file_normalize_attachment_path( $t_row['diskfile'], $t_project_id );

			if( file_exists( $t_local_disk_file ) ) {
				$t_file_info_type = file_get_mime_type( $t_local_disk_file );

				if( $t_file_info_type !== false ) {
					$t_content_type = $t_file_info_type;
				}

				return array( 'type' => $t_content_type, 'content' => file_get_contents( $t_local_disk_file ) );
			}
			return false;
			break;
		case DATABASE:
			$t_file_info_type = file_get_mime_type_for_content( $t_row['content'] );

			if( $t_file_info_type !== false ) {
				$t_content_type = $t_file_info_type;
			}

			return array( 'type' => $t_content_type, 'content' => $t_row['content'] );
			break;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}
}

/**
 * Move any attachments as needed when a bug is moved from project to project.
 *
 * @param integer $p_bug_id        ID of bug containing attachments to be moved.
 * @param integer $p_project_id_to Destination project ID for the bug.
 * @return void
 *
 * @todo: this function can't cope with source or target storing attachments in DB
 */
function file_move_bug_attachments( $p_bug_id, $p_project_id_to ) {
	$t_project_id_from = bug_get_field( $p_bug_id, 'project_id' );
	if( $t_project_id_from == $p_project_id_to ) {
		return;
	}

	$t_method = config_get( 'file_upload_method' );
	if( $t_method != DISK ) {
		return;
	}

	if( !file_bug_has_attachments( $p_bug_id ) ) {
		return;
	}

	$t_path_from = project_get_field( $t_project_id_from, 'file_path' );
	if( is_blank( $t_path_from ) ) {
		$t_path_from = config_get_global( 'absolute_path_default_upload_folder' );
	}
	file_ensure_valid_upload_path( $t_path_from );
	$t_path_to = project_get_field( $p_project_id_to, 'file_path' );
	if( is_blank( $t_path_to ) ) {
		$t_path_to = config_get_global( 'absolute_path_default_upload_folder' );
	}
	file_ensure_valid_upload_path( $t_path_to );
	if( $t_path_from == $t_path_to ) {
		return;
	}

	# Initialize the update query to update a single row
	$c_bug_id = (int)$p_bug_id;
	db_param_push();
	$t_query_disk_attachment_update = 'UPDATE {bug_file}
	                                 SET folder=' . db_param() . '
	                                 WHERE bug_id=' . db_param() . '
	                                 AND id =' . db_param();

	$t_attachment_rows = bug_get_attachments( $p_bug_id );
	$t_attachments_count = count( $t_attachment_rows );
	for( $i = 0; $i < $t_attachments_count; $i++ ) {
		$t_row = $t_attachment_rows[$i];
		$t_basename = basename( $t_row['diskfile'] );

		$t_disk_file_name_from = file_path_combine( $t_path_from, $t_basename );
		$t_disk_file_name_to = file_path_combine( $t_path_to, $t_basename );

		if( !file_exists( $t_disk_file_name_to ) ) {
			chmod( $t_disk_file_name_from, 0775 );
			if( !rename( $t_disk_file_name_from, $t_disk_file_name_to ) ) {
				if( !copy( $t_disk_file_name_from, $t_disk_file_name_to ) ) {
					trigger_error( ERROR_FILE_MOVE_FAILED, ERROR );
				}
				file_delete_local( $t_disk_file_name_from );
			}
			chmod( $t_disk_file_name_to, config_get( 'attachments_file_permissions' ) );
			# Don't pop the parameters after query execution since we're in a loop
			db_query( $t_query_disk_attachment_update, array( db_prepare_string( $t_path_to ), $c_bug_id, (int)$t_row['id'] ), -1, -1, false );
		} else {
			trigger_error( ERROR_FILE_DUPLICATE, ERROR );
		}
	}
	db_param_pop();
}

/**
 * Copies all attachments from the source bug to the destination bug
 *
 * Does not perform history logging and does not perform access checks.
 *
 * @param integer $p_source_bug_id Source Bug.
 * @param integer $p_dest_bug_id   Destination Bug.
 * @return void
 */
function file_copy_attachments( $p_source_bug_id, $p_dest_bug_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {bug_file} WHERE bug_id = ' . db_param();
	$t_result = db_query( $t_query, array( $p_source_bug_id ) );
	$t_count = db_num_rows( $t_result );

	$t_project_id = bug_get_field( $p_source_bug_id, 'project_id' );

	for( $i = 0;$i < $t_count;$i++ ) {
		$t_bug_file = db_fetch_array( $t_result );

		# prepare the new diskfile name and then copy the file
		$t_source_file = $t_bug_file['folder'] . $t_bug_file['diskfile'];
		if( ( config_get( 'file_upload_method' ) == DISK ) ) {
			$t_source_file = file_normalize_attachment_path( $t_source_file, $t_project_id );
			$t_file_path = dirname( $t_source_file ) . DIRECTORY_SEPARATOR;
		} else {
			$t_file_path = $t_bug_file['folder'];
		}
		$t_new_diskfile_name = file_generate_unique_name( $t_file_path );
		$t_new_diskfile_location = $t_file_path . $t_new_diskfile_name;
		$t_new_file_name = file_get_display_name( $t_bug_file['filename'] );
		if( ( config_get( 'file_upload_method' ) == DISK ) ) {
			# Skip copy operation if file does not exist (i.e. target bug will have missing attachment)
			# @todo maybe we should trigger an error instead in this case ?
			if( file_exists( $t_source_file ) ) {
				copy( $t_source_file, $t_new_diskfile_location );
				chmod( $t_new_diskfile_location, config_get( 'attachments_file_permissions' ) );
			}
		}

		db_param_push();
		$t_query = 'INSERT INTO {bug_file} (
				bug_id, title, description, diskfile, filename, folder,
				filesize, file_type, date_added, user_id, content
			)
			VALUES ( '
			. db_param() . ', ' . db_param() . ', ' . db_param() . ', '
			. db_param() . ', ' . db_param() . ', ' . db_param() . ', '
			. db_param() . ', ' . db_param() . ', ' . db_param() . ', '
			. db_param() . ', ' . db_param() .
			')';
		db_query( $t_query, array(
			$p_dest_bug_id, $t_bug_file['title'], $t_bug_file['description'],
			$t_new_diskfile_name, $t_new_file_name, $t_file_path,
			$t_bug_file['filesize'], $t_bug_file['file_type'], $t_bug_file['date_added'],
			$t_bug_file['user_id'], $t_bug_file['content']
		) );
	}
}

/**
 * Returns a possibly override content type for a file name
 *
 * @param string $p_filename The filename of the file which will be downloaded.
 * @return string the content type, or empty if it should not be overridden
 */
function file_get_content_type_override( $p_filename ) {
	global $g_file_download_content_type_overrides;

	$t_extension = pathinfo( $p_filename, PATHINFO_EXTENSION );

	if( isset( $g_file_download_content_type_overrides[$t_extension] ) ) {
		return $g_file_download_content_type_overrides[$t_extension];
	}

	return null;
}

/**
 * Return the maximum file size that can be uploaded, based on mantis and php
 * configured setting.
 * @return integer	File size in bytes
 */
function file_get_max_file_size() {
	return (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
}