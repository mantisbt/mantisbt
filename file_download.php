<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * Add file and redirect to the referring page
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	$g_bypass_headers = true; # suppress headers as we will send our own later
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'file_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_file_id	= gpc_get_int( 'file_id' );
	$f_type		= gpc_get_string( 'type' );

	$c_file_id = (integer)$f_file_id;

	# we handle the case where the file is attached to a bug
	# or attached to a project as a project doc.
	$query = '';
	switch ( $f_type ) {
		case 'bug':
			$t_bug_file_table = db_get_table( 'mantis_bug_file_table' );
			$query = "SELECT *
				FROM $t_bug_file_table
				WHERE id=" . db_param();
			break;
		case 'doc':
			$t_project_file_table = db_get_table( 'mantis_project_file_table' );
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

	# flush output buffer to protect download
	if ( ob_get_length() ) {
		@ob_end_clean();
	}

	# Make sure that IE can download the attachments under https.
	header( 'Pragma: public' );

	header( 'Content-Type: ' . $v_file_type );
	header( 'Content-Length: ' . $v_filesize );
	$t_filename = file_get_display_name( $v_filename );
	$t_inline_files = explode(',', config_get('inline_file_exts', 'gif'));
	if ( in_array( strtolower( file_get_extension($t_filename) ), $t_inline_files ) ) {
		$t_disposition = ''; //'inline;';
	} else {
		$t_disposition = ' attachment;';
	}

	header( 'Content-Disposition:' . $t_disposition . ' filename="' . urlencode( $t_filename ) . '"' );
	header( 'Content-Description: Download Data' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', db_unixtimestamp( $v_date_added ) ) );

	# To fix an IE bug which causes problems when downloading
	# attached files via HTTPS, we disable the "Pragma: no-cache"
	# command when IE is used over HTTPS.
	global $g_allow_file_cache;
	if ( ( isset( $_SERVER["HTTPS"] ) && ( "on" == strtolower( $_SERVER["HTTPS"] ) ) ) && preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
		# Suppress "Pragma: no-cache" header.
	} else {
		if ( !isset( $g_allow_file_cache ) ) {
		    header( 'Pragma: no-cache' );
		}
	}
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

	# dump file content to the connection.
	switch ( config_get( 'file_upload_method' ) ) {
		case DISK:
			$t_local_disk_file = file_normalize_attachment_path( $v_diskfile, $t_project_id );

			if ( file_exists( $t_local_disk_file ) ) {
				readfile( $t_local_disk_file );
			}
			break;
		case FTP:
			$t_local_disk_file = file_normalize_attachment_path( $v_diskfile, $t_project_id );

			if ( file_exists( $t_local_disk_file ) ) {
				readfile( $t_local_disk_file );
			} else {
				$ftp = file_ftp_connect();
				file_ftp_get ( $ftp, $t_local_disk_file, $v_diskfile );
				file_ftp_disconnect( $ftp );
				readfile( $t_local_disk_file );
			}
			break;
		default:
			echo $v_content;
	}
	exit();
?>
