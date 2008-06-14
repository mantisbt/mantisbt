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
	# $Id: proj_doc_update.php,v 1.30.2.1 2007-10-13 22:34:24 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'file_api.php' );

	# helper_ensure_post();

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
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$c_file_id = db_prepare_int( $f_file_id );
	$c_title = db_prepare_string( $f_title );
	$c_description = db_prepare_string( $f_description );

	$t_project_file_table = config_get( 'mantis_project_file_table' );

	#@@@ (thraxisp) this code should probably be integrated into file_api to share
	#  methods used to store files

	extract( $f_file, EXTR_PREFIX_ALL, 'v' );

	if ( is_uploaded_file( $v_tmp_name ) ) {
		if ( php_version_at_least( '4.2.0' ) ) {
		    switch ( (int) $v_error ) {
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
        }
        
	    if ( ( '' == $v_tmp_name ) || ( '' == $v_name ) ) {
		    trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
        }
		if ( !file_type_check( $v_name ) ) {
			trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
		}

		if ( !is_readable( $v_tmp_name ) ) {
			trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
		}

		$t_project_id = helper_get_current_project();

		# grab the original file path and name
		$t_disk_file_name = file_get_field( $f_file_id, 'diskfile', 'project' );
		$t_file_path = dirname( $t_disk_file_name );

		# prepare variables for insertion
		$c_file_name = db_prepare_string( $v_name );
		$c_file_type = db_prepare_string( $v_type );
		$t_file_size = filesize( $v_tmp_name );
		$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
        if ( $t_file_size > $t_max_file_size ) {
            trigger_error( ERROR_FILE_TOO_BIG, ERROR );
        }
		$c_file_size = db_prepare_int( $t_file_size );

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
				if ( !move_uploaded_file( $v_tmp_name, $t_disk_file_name ) ) {
					trigger_error( FILE_MOVE_FAILED, ERROR );
				}
				chmod( $t_disk_file_name, config_get( 'attachments_file_permissions' ) );

				$c_content = '';
				break;
			case DATABASE:
				$c_content = db_prepare_string( fread ( fopen( $v_tmp_name, 'rb' ), $v_size ) );
				break;
			default:
				# @@@ Such errors should be checked in the admin checks
				trigger_error( ERROR_GENERIC, ERROR );
		}
		$t_now = db_now();
		$query = "UPDATE $t_project_file_table
			SET title='$c_title', description='$c_description', date_added=$t_now,
				filename='$c_file_name', filesize=$c_file_size, file_type='$c_file_type', content='$c_content'
				WHERE id='$c_file_id'";
	} else {
		$query = "UPDATE $t_project_file_table
				SET title='$c_title', description='$c_description'
				WHERE id='$c_file_id'";
	}

	$result = db_query( $query );
	if ( !$result ) {
		trigger_error( ERROR_GENERIC, ERROR  );
	}

	$t_redirect_url = 'proj_doc_page.php';

	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
