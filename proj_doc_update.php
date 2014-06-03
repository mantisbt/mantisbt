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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'file_api.php' );

	form_security_validate( 'proj_doc_update' );

	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ||
		!file_is_uploading_enabled() ||
		!file_allow_project_upload() ) {
		access_denied();
	}

	$f_file_id = gpc_get_int( 'file_id' );
	$f_title = gpc_get_string( 'title' );
	$f_description	= gpc_get_string( 'description' );
	$f_file = gpc_get_file( 'file' );

	$t_project_id = file_get_field( $f_file_id, 'project_id', 'project' );

	access_ensure_project_level( config_get( 'upload_project_file_threshold' ), $t_project_id );

	if ( is_blank( $f_title ) ) {
		error_parameters( lang_get( 'title' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_project_file_table = db_get_table( 'mantis_project_file_table' );

	/** @todo (thraxisp) this code should probably be integrated into file_api to share methods used to store files */

	extract( $f_file, EXTR_PREFIX_ALL, 'v' );

	if ( isset( $f_file['tmp_name'] ) && is_uploaded_file( $f_file['tmp_name'] ) ) {
		file_ensure_uploaded( $f_file );

		$t_project_id = helper_get_current_project();

		# grab the original file path and name
		$t_disk_file_name = file_get_field( $f_file_id, 'diskfile', 'project' );
		$t_file_path = dirname( $t_disk_file_name );

		# prepare variables for insertion
		$t_file_size = filesize( $f_file['tmp_name'] );
		$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
		if( $t_file_size > $t_max_file_size ) {
			trigger_error( ERROR_FILE_TOO_BIG, ERROR );
		}

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
				if ( !move_uploaded_file( $f_file['tmp_name'], $t_disk_file_name ) ) {
					trigger_error( ERROR_FILE_MOVE_FAILED, ERROR );
				}
				chmod( $t_disk_file_name, config_get( 'attachments_file_permissions' ) );

				$c_content = '';
				break;
			case DATABASE:
				$c_content = db_prepare_binary_string( fread ( fopen( $f_file['tmp_name'], 'rb' ), $f_file['size'] ) );
				break;
			default:
				/** @todo Such errors should be checked in the admin checks */
				trigger_error( ERROR_GENERIC, ERROR );
		}
		$query = "UPDATE $t_project_file_table
			SET title=" . db_param() . ", description=" . db_param() . ", date_added=" . db_param() . ",
				filename=" . db_param() . ", filesize=" . db_param() . ", file_type=" .db_param() . ", content=" .db_param() . "
				WHERE id=" . db_param();
		$result = db_query_bound( $query, array( $f_title, $f_description, db_now(), $f_file['name'], $t_file_size, $f_file['type'], $c_content, $f_file_id ) );
	} else {
		$query = "UPDATE $t_project_file_table
				SET title=" . db_param() . ", description=" . db_param() . "
				WHERE id=" . db_param();
		$result = db_query_bound( $query, Array( $f_title, $f_description, $f_file_id ) );
	}

	if ( !$result ) {
		trigger_error( ERROR_GENERIC, ERROR  );
	}

	form_security_purge( 'proj_doc_update' );

	$t_redirect_url = 'proj_doc_page.php';

	html_page_top( null, $t_redirect_url );
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
