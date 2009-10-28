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
	 * Add file and redirect to the referring page
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	$g_bypass_headers = true; # suppress headers as we will send our own later
	define( 'COMPRESSION_DISABLED', true );
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'file_api.php' );

	auth_ensure_user_authenticated();

	$f_file_id	= gpc_get_int( 'file_id' );
	$f_type		= gpc_get_string( 'type' );

	$c_file_id = (integer)$f_file_id;

	# we handle the case where the file is attached to a bug
	# or attached to a project as a project doc.
	$query = '';
	switch ( $f_type ) {
		case 'bug':
			$t_bug_file_table = db_get_table( 'bug_file' );
			$query = "SELECT *
				FROM $t_bug_file_table
				WHERE id=" . db_param();
			break;
		case 'doc':
			$t_project_file_table = db_get_table( 'project_file' );
			$query = "SELECT *
				FROM $t_project_file_table
				WHERE id=" . db_param();
			break;
		default:
			access_denied();
	}
	$result = db_query_bound( $query, Array( $c_file_id ) );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	if ( $f_type == 'bug' ) {
		$t_project_id = bug_get_field( $v_bug_id, 'project_id' );
	} else {
		$t_project_id = $v_project_id;
	}

	# Check access rights
	switch ( $f_type ) {
		case 'bug':
			if ( !file_can_download_bug_attachments( $v_bug_id ) ) {
				access_denied();
			}
			break;
		case 'doc':
			# Check if project documentation feature is enabled.
			if ( OFF == config_get( 'enable_project_documentation' ) ) {
				access_denied();
			}

			access_ensure_project_level( config_get( 'view_proj_doc_threshold' ), $v_project_id );
			break;
	}

	# throw away output buffer contents (and disable it) to protect download
	while ( @ob_end_clean() );

	if ( ini_get( 'zlib.output_compression' ) && function_exists( 'ini_set' ) ) {
		ini_set( 'zlib.output_compression', false );
	}

	# Make sure that IE can download the attachments under https.
	header( 'Pragma: public' );

	# To fix an IE bug which causes problems when downloading
	# attached files via HTTPS, we disable the "Pragma: no-cache"
	# command when IE is used over HTTPS.
	global $g_allow_file_cache;
	if ( ( isset( $_SERVER["HTTPS"] ) && ( "on" == utf8_strtolower( $_SERVER["HTTPS"] ) ) ) && is_browser_internet_explorer() ) {
		# Suppress "Pragma: no-cache" header.
	} else {
		if ( !isset( $g_allow_file_cache ) ) {
		    header( 'Pragma: no-cache' );
		}
	}
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $v_date_added ) );

	$t_filename = file_get_display_name( $v_filename );
	$t_show_inline = false;
	$t_inline_files = explode( ',', config_get( 'inline_file_exts' ) );
	if ( $t_inline_files !== false && !is_blank( $t_inline_files[0] ) ) {
		if ( in_array( utf8_strtolower( file_get_extension( $t_filename ) ), $t_inline_files ) ) {
			$t_show_inline = true;
		}
	}

	http_content_disposition_header( $t_filename, $t_show_inline );

	header( 'Content-Length: ' . $v_filesize );

	# If finfo is available (always true for PHP >= 5.3.0) we can use it to determine the MIME type of files
	$finfo_available = false;
	if ( class_exists( 'finfo' ) ) {
		$t_info_file = config_get( 'fileinfo_magic_db_file' );

		if ( is_blank( $t_info_file ) ) {
			$finfo = new finfo( FILEINFO_MIME );
		} else {
			$finfo = new finfo( FILEINFO_MIME, $t_info_file );
		}

		if ( $finfo ) {
			$finfo_available = true;
		}
	}

	$t_content_type = $v_file_type;

	# dump file content to the connection.
	switch ( config_get( 'file_upload_method' ) ) {
		case DISK:
			$t_local_disk_file = file_normalize_attachment_path( $v_diskfile, $t_project_id );

			if ( file_exists( $t_local_disk_file ) ) {
				if ( $finfo_available ) {
					$t_file_info_type = $finfo->file( $t_local_disk_file );

					if ( $t_file_info_type !== false ) {
						$t_content_type = $t_file_info_type;
					}
				}

				header( 'Content-Type: ' . $t_content_type );
				file_send_chunk( $t_local_disk_file );
			}
			break;
		case FTP:
			$t_local_disk_file = file_normalize_attachment_path( $v_diskfile, $t_project_id );

			if ( !file_exists( $t_local_disk_file ) ) {
				$ftp = file_ftp_connect();
				file_ftp_get ( $ftp, $t_local_disk_file, $v_diskfile );
				file_ftp_disconnect( $ftp );
			}

			if ( $finfo_available ) {
				$t_file_info_type = $finfo->file( $t_local_disk_file );

				if ( $t_file_info_type !== false ) {
					$t_content_type = $t_file_info_type;
				}
			}

			header( 'Content-Type: ' . $t_content_type );
			readfile( $t_local_disk_file );
			break;
		default:
			if ( $finfo_available ) {
				$t_file_info_type = $finfo->buffer( $v_content );

				if ( $t_file_info_type !== false ) {
					$t_content_type = $t_file_info_type;
				}
			}

			header( 'Content-Type: ' . $t_content_type );
			echo $v_content;
	}
	exit();

function file_send_chunk($filename, $start = 0, $maxlength = 0 ) {
    static $s_safe_mode = null;
    $chunksize = 4*131072;
    $buffer = '';
	$offset = $start;
	
	if( $s_safe_mode == null ) {
		$s_safe_mode = ini_get('safe_mode');
	}
	
    while (true) {
		if ( $s_safe_mode == false) {
			@set_time_limit(60*60); //reset time limit to 60 min - should be enough for 1 MB chunk
		}
        $buffer = file_get_contents($filename, 0, null, $offset, ( ($maxlength > 0 && $maxlength < $chunksize) ? $maxlength : $chunksize ) );
        if ( $buffer === false ) {
			$buffer = file_get_contents($filename, 0, null, $offset, ( $maxlength > 0 ? $maxlength : -1 ) );
			echo $buffer;
			flush();
        	exit(); // end of file
        }
        echo $buffer;
        flush();
        $offset += $chunksize;
        $maxlength -= $chunksize;
        unset($buffer);
        $buffer = null;
    }
    return;
}
