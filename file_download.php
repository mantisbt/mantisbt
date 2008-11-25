<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: file_download.php,v 1.41.2.1 2007-10-13 22:33:14 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# Add file and redirect to the referring page
?>
<?php
	$g_bypass_headers = true; # suppress headers as we will send our own later
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
			$t_bug_file_table = config_get( 'mantis_bug_file_table' );
			$query = "SELECT *
				FROM $t_bug_file_table
				WHERE id='$c_file_id'";
			break;
		case 'doc':
			$t_project_file_table = config_get( 'mantis_project_file_table' );
			$query = "SELECT *
				FROM $t_project_file_table
				WHERE id='$c_file_id'";
			break;
		default:
			access_denied();
	}
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

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
		if ( ! isset( $g_allow_file_cache ) ) {
		    header( 'Pragma: no-cache' );
		}
	}
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

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
				file_ftp_get ( $ftp, $v_diskfile, $v_diskfile );
				file_ftp_disconnect( $ftp );
				readfile( $v_diskfile );
			}
			break;
		default:
			echo $v_content;
	}
	exit();
?>
