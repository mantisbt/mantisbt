<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: file_api.php,v 1.70 2005-06-27 14:07:41 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'history_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );

	$g_cache_file_count = array();

	### File API ###

	# --------------------
	# Gets the filename without the bug id prefix.
	function file_get_display_name( $p_filename ) {
		$t_array = explode( '-', $p_filename, 2 );

		# Check if it's a project document filename (doc-0000000-filename)
		# or a bug attachment filename (0000000-filename)
		# for newer filenames, the filename in schema is correct.
		# This is important to handle filenames with '-'s properly
		$t_doc_match = '/^' . config_get( 'document_files_prefix' ) . '-\d{7}-/';
		$t_name = preg_split($t_doc_match, $p_filename);
		if ( isset( $t_name[1] ) ) {
			return $t_name[1];
		} else {
			$t_bug_match = '/^\d{7}-/';
			$t_name = preg_split($t_bug_match, $p_filename);
			if ( isset( $t_name[1] ) ) {
				return $t_name[1];
			} else {
				return $p_filename;
			}
		}
	}

	# --------------------
	# Check the number of attachments a bug has (if any)
	function file_bug_attachment_count( $p_bug_id ) {
		global $g_cache_file_count;

		$c_bug_id			= db_prepare_int( $p_bug_id );
		$t_bug_file_table	= config_get( 'mantis_bug_file_table' );

		# First check if we have a cache hit
		if ( isset( $g_cache_file_count[ $p_bug_id ] ))
			return $g_cache_file_count[ $p_bug_id ];

		# If there is no cache hit, check if there is anything in
		#   the cache. If the cache isn't empty and we didn't have
		#   a hit, then there are not attachments for this bug.
		if ( count( $g_cache_file_count ) > 0 )
			return 0;

		# Otherwise build the cache and return the attachment count
		#   for the given bug (if any).
		$query = "SELECT bug_id, COUNT(bug_id) AS attachments
				FROM $t_bug_file_table
				GROUP BY bug_id";
		$result = db_query( $query );

		$t_file_count = 0;
		while( $row = db_fetch_array( $result )) {
			$g_cache_file_count[ $row['bug_id'] ] = $row['attachments'];
			if ( $p_bug_id == $row['bug_id'] )
				$t_file_count = $row['attachments'];
		}

		# If no attachments are present, mark the cache to avoid
		#   repeated queries for this.
		if ( count( $g_cache_file_count ) == 0 ) {
			$g_cache_file_count[ '_no_files_' ] = -1;
		}

		return $t_file_count;
	}

	# --------------------
	# Check if a specific bug has attachments
	function file_bug_has_attachments( $p_bug_id ) {
		if ( file_bug_attachment_count( $p_bug_id ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check if the current user can view attachments for the specified bug.
	function file_can_view_bug_attachments( $p_bug_id ) {
		$t_reported_by_me	= bug_is_user_reporter( $p_bug_id, auth_get_current_user_id() );
		$t_can_view     	= access_has_bug_level( config_get( 'view_attachments_threshold' ), $p_bug_id );
# @@@ Fix this to be readable
		$t_can_view     	= $t_can_view || ( $t_reported_by_me && config_get( 'allow_view_own_attachments' ) );

		return $t_can_view;
	}

	# --------------------
	# Check if the current user can download attachments for the specified bug.
	function file_can_download_bug_attachments( $p_bug_id ) {
		$t_reported_by_me	= bug_is_user_reporter( $p_bug_id, auth_get_current_user_id() );
		$t_can_download		= access_has_bug_level( config_get( 'download_attachments_threshold' ), $p_bug_id );
# @@@ Fix this to be readable
		$t_can_download		= $t_can_download || ( $t_reported_by_me && config_get( 'allow_download_own_attachments' ) );

		return $t_can_download;
	}

	# --------------------
	# Check if the current user can delete attachments from the specified bug.
	function file_can_delete_bug_attachments( $p_bug_id ) {
		if ( bug_is_readonly( $p_bug_id ) ) {
			return false;
		}

		$t_reported_by_me	= bug_is_user_reporter( $p_bug_id, auth_get_current_user_id() );
		$t_can_download		= access_has_bug_level( config_get( 'download_attachments_threshold' ), $p_bug_id );
# @@@ Fix this to be readable
		$t_can_download		= $t_can_download || ( $t_reported_by_me && config_get( 'allow_download_own_attachments' ) );

		return $t_can_download;
	}

	# --------------------
	# List the attachments belonging to the specified bug.  This is used from within
	# bug_view_page.php and bug_view_advanced_page.php
	function file_list_attachments( $p_bug_id ) {
		$t_attachment_rows = bug_get_attachments( $p_bug_id );

		$num_files = sizeof( $t_attachment_rows );
		if ( $num_files === 0 ) {
			return;
		}

		$t_can_download = file_can_download_bug_attachments( $p_bug_id );
		$t_can_delete   = file_can_delete_bug_attachments( $p_bug_id );

		$image_previewed = false;
		for ( $i = 0 ; $i < $num_files ; $i++ ) {
			$row = $t_attachment_rows[$i];
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$t_file_display_name = file_get_display_name( $v_filename );
			$t_filesize		= number_format( $v_filesize );
			$t_date_added	= date( config_get( 'normal_date_format' ), db_unixtimestamp( $v_date_added ) );

			if ( $image_previewed ) {
				$image_previewed = false;
				PRINT '<br />';
			}

			if ( $t_can_download ) {
				$t_href_start	= "<a href=\"file_download.php?file_id=$v_id&amp;type=bug\">";
				$t_href_end		= '</a>';

				$t_href_clicket = " [<a href=\"file_download.php?file_id=$v_id&amp;type=bug\" target=\"_blank\">^</a>]";
			} else {
				$t_href_start	= '';
				$t_href_end		= '';

				$t_href_clicket = '';
			}

			PRINT $t_href_start;
			print_file_icon ( $t_file_display_name );
			PRINT $t_href_end . '</a>&nbsp;' . $t_href_start . $t_file_display_name .
				$t_href_end . "$t_href_clicket ($t_filesize bytes) <span class=\"italic\">$t_date_added</span>";

			if ( $t_can_delete ) {
				PRINT " [<a class=\"small\" href=\"bug_file_delete.php?file_id=$v_id\">" . lang_get('delete_link') . '</a>]';
			}

			if ( ( FTP == config_get( 'file_upload_method' ) ) && file_exists ( $v_diskfile ) ) {
				PRINT ' (' . lang_get( 'cached' ) . ')';
			}

			if ( $t_can_download &&
				( $v_filesize <= config_get( 'preview_attachments_inline_max_size' ) ) &&
				( $v_filesize != 0 ) &&
				( in_array( strtolower( file_get_extension( $t_file_display_name ) ), array( 'png', 'jpg', 'jpeg', 'gif', 'bmp' ), true ) ) ) {

				PRINT "<br /><img src=\"file_download.php?file_id=$v_id&amp;type=bug\" />";
				$image_previewed = true;
			}

			if ( $i != ( $num_files - 1 ) ) {
				PRINT '<br />';
			}
		}
	}
	# --------------------
	# delete all files that are associated with the given bug
	function file_delete_attachments( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$t_method = config_get( 'file_upload_method' );

		# Delete files from disk
		$query = "SELECT diskfile, filename
				FROM $t_bug_file_table
				WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );

		$file_count = db_num_rows( $result );
		if ( 0 == $file_count ) {
			return true;
		}

		if ( ( DISK == $t_method ) || ( FTP == $t_method ) ) {
			# there may be more than one file
			$ftp = 0;
			if ( FTP == $t_method ) {
				$ftp = file_ftp_connect();
			}

			for ( $i = 0 ; $i < $file_count ; $i++ ) {
				$row = db_fetch_array( $result );

				file_delete_local ( $row['diskfile'] );

				if ( FTP == $t_method ) {
					file_ftp_delete ( $ftp, $row['diskfile'] );
				}
			}

			if ( FTP == $t_method ) {
				file_ftp_disconnect( $ftp );
			}
		}

		# Delete the corresponding db records
		$query = "DELETE FROM $t_bug_file_table
				  WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	function file_delete_project_files( $p_project_id ) {
		$t_project_file_table	= config_get( 'mantis_project_file_table' );
		$t_method				= config_get( 'file_upload_method' );

		# Delete the file physically (if stored via DISK or FTP)
		if ( ( DISK == $t_method ) || ( FTP == $t_method ) ) {
			# Delete files from disk
			$query = "SELECT diskfile, filename
					FROM $t_project_file_table
					WHERE project_id=$p_project_id";
			$result = db_query( $query );

			$file_count = db_num_rows( $result );

			$ftp = 0;
			if ( FTP == $t_method ) {
				$ftp = file_ftp_connect();
			}

			for ( $i = 0 ; $i < $file_count ; $i++ ) {
				$row = db_fetch_array( $result );

				file_delete_local ( $row['diskfile'] );

				if ( FTP == $t_method ) {
					file_ftp_delete ( $ftp, $row['diskfile'] );
				}
			}

			if ( FTP == $t_method ) {
				file_ftp_disconnect( $ftp );
			}
		}

		# Delete the corresponding db records
		$query = "DELETE FROM $t_project_file_table
				WHERE project_id=$p_project_id";
		$result = db_query($query);
	}
	# --------------------
	# Delete all cached files that are older than configured number of days.
	function file_ftp_cache_cleanup() {

	}
	# --------------------
	# Connect to ftp server using configured server address, user name, and password.
	function file_ftp_connect() {
		$conn_id = ftp_connect( config_get( 'file_upload_ftp_server' ) );
		$login_result = ftp_login( $conn_id, config_get( 'file_upload_ftp_user' ), config_get( 'file_upload_ftp_pass' ) );

		if ( ( !$conn_id ) || ( !$login_result ) ) {
			trigger_error( ERROR_FTP_CONNECT_ERROR, ERROR );
		}

		return $conn_id;
	}
	# --------------------
	# Put a file to the ftp server.
	function file_ftp_put ( $p_conn_id, $p_remote_filename, $p_local_filename ) {
		helper_begin_long_process();
		$upload = ftp_put( $p_conn_id, $p_remote_filename, $p_local_filename, FTP_BINARY);
	}
	# --------------------
	# Get a file from the ftp server.
	function file_ftp_get ( $p_conn_id, $p_local_filename, $p_remote_filename ) {
		helper_begin_long_process();
		$download = ftp_get( $p_conn_id, $p_local_filename, $p_remote_filename, FTP_BINARY);
	}
	# --------------------
	# Delete a file from the ftp server
	function file_ftp_delete ( $p_conn_id, $p_filename ) {
		@ftp_delete( $p_conn_id, $p_filename );
	}
	# --------------------
	# Disconnect from the ftp server
	function file_ftp_disconnect( $p_conn_id ) {
		ftp_quit( $p_conn_id );
	}
	# --------------------
	# Delete a local file even if it is read-only.
	function file_delete_local( $p_filename ) {
		if ( file_exists( $p_filename ) ) {
			chmod( $p_filename, 0775 );
			unlink( $p_filename );
		}
	}
	# --------------------
	# Return the specified field value
	function file_get_field( $p_file_id, $p_field_name, $p_table = 'bug' ) {
		$c_file_id			= db_prepare_int( $p_file_id );
		$c_field_name		= db_prepare_string( $p_field_name );
		$t_bug_file_table	= config_get( 'mantis_' . $p_table . '_file_table' );

		# get info
		$query = "SELECT $c_field_name
				  FROM $t_bug_file_table
				  WHERE id='$c_file_id'";
		$result = db_query( $query, 1 );

		return db_result( $result );
	}
	# --------------------
	function file_delete( $p_file_id, $p_table = 'bug' ) {
		$t_upload_method	= config_get( 'file_upload_method' );

		$c_file_id = db_prepare_int( $p_file_id );
		$t_filename = file_get_field( $p_file_id, 'filename', $p_table );
		$t_diskfile = file_get_field( $p_file_id, 'diskfile', $p_table );

		if( ( DISK == $t_upload_method ) || ( FTP == $t_upload_method ) ) {
			if ( FTP == $t_upload_method ) {
				$ftp = file_ftp_connect();
				file_ftp_delete( $ftp, $t_diskfile );
				file_ftp_disconnect( $ftp );
			}

			if ( file_exists( $t_diskfile ) ) {
				file_delete_local( $t_diskfile );
			}
		}

		if( 'bug' == $p_table ) {
			# log file deletion
			$t_bug_id			= file_get_field( $p_file_id, 'bug_id', 'bug' );
			history_log_event_special( $t_bug_id, FILE_DELETED, file_get_display_name ( $t_filename ) );
		}

		$t_file_table	= config_get( 'mantis_' . $p_table . '_file_table' );
		$query = "DELETE FROM $t_file_table
				WHERE id='$c_file_id'";
		db_query( $query );
		return true;
	}
	# --------------------
	# File type check
	function file_type_check( $p_file_name ) {
		$t_allowed_files	= config_get( 'allowed_files' );
		$t_disallowed_files	= config_get( 'disallowed_files' );;

		# grab extension
		$t_ext_array	= explode( '.', $p_file_name );
		$last_position	= count( $t_ext_array )-1;
		$t_extension	= $t_ext_array[$last_position];

		# check against disallowed files
		$t_disallowed_arr = explode_enum_string( $t_disallowed_files );
		foreach ( $t_disallowed_arr as $t_val ) {
			if ( 0 == strcasecmp( $t_val, $t_extension ) ) {
				return false;
			}
		}

		# if the allowed list is note populated then the file must be allowed
		if ( is_blank( $t_allowed_files ) ) {
			return true;
		}

		# check against allowed files
		$t_allowed_arr = explode_enum_string( $t_allowed_files );
		foreach ( $t_allowed_arr as $t_val ) {
			if ( 0 == strcasecmp( $t_val, $t_extension ) ) {
				return true;
			}
		}

		return false;
	}

	# --------------------
	# clean file name by removing sensitive characters and replacing them with underscores
	function file_clean_name( $p_filename ) {
		return preg_replace( "/[\/\\ :&]/", "_", $p_filename);
	}

	# --------------------
	# Generate a string to use as the identifier for the file
	# It is not guaranteed to be unique and should be checked
	# The string returned should be 32 characters in length
	function file_generate_name( $p_seed ) {
		$t_val = md5( $p_seed . time() );

		return substr( $t_val, 0, 32 );
	}

	# --------------------
	# Generate a UNIQUE string to use as the identifier for the file
	# The string returned should be 64 characters in length
	function file_generate_unique_name( $p_seed , $p_filepath ) {
		do {
			$t_string = file_generate_name( $p_seed );
		} while ( !diskfile_is_name_unique( $t_string , $p_filepath ) );

		return $t_string;
	}

	# --------------------
	# Return true if the diskfile name identifier is unique, false otherwise
	function diskfile_is_name_unique( $p_name , $p_filepath ) {
		$t_file_table = config_get( 'mantis_bug_file_table' );

		$c_name = db_prepare_string( $p_filepath . $p_name );

		$query = "SELECT COUNT(*)
				  FROM $t_file_table
				  WHERE diskfile='$c_name'";
		$result = db_query( $query );
		$t_count = db_result( $result );

		if ( $t_count > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Return true if the file name identifier is unique, false otherwise
	function file_is_name_unique( $p_name, $p_bug_id ) {
		$t_file_table = config_get( 'mantis_bug_file_table' );

		$c_name = db_prepare_string( $p_name );
		$c_bug = db_prepare_string( $p_bug_id );

		$query = "SELECT COUNT(*)
				  FROM $t_file_table
				  WHERE filename='$c_name' and bug_id=$c_bug";
		$result = db_query( $query );
		$t_count = db_result( $result );

		if ( $t_count > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	function file_add( $p_bug_id, $p_tmp_file, $p_file_name, $p_file_type='', $p_table = 'bug', $p_file_error = 0, $p_title = '', $p_desc = '' ) {

		if ( php_version_at_least( '4.2.0' ) ) {
		    switch ( (int) $p_file_error ) {
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

	    if ( ( '' == $p_tmp_file ) || ( '' == $p_file_name ) ) {
		    trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
        }
		if ( !is_readable( $p_tmp_file ) ) {
			trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
		}

		if ( !file_type_check( $p_file_name ) ) {
			trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
		}

		if ( !file_is_name_unique( $p_file_name, $p_bug_id ) ) {
			trigger_error( ERROR_DUPLICATE_FILE, ERROR );
		}

		if ( 'bug' == $p_table ) {
			$t_project_id	= bug_get_field( $p_bug_id, 'project_id' );
			$t_bug_id		= bug_format_id( $p_bug_id );
		} else {
			$t_project_id	= helper_get_current_project();
			$t_bug_id		= 0;
		}

		# prepare variables for insertion
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_project_id		= db_prepare_int( $t_project_id );
		$c_file_type	= db_prepare_string( $p_file_type );
		$c_title = db_prepare_string( $p_title );
		$c_desc = db_prepare_string( $p_desc );

		if( $t_project_id == ALL_PROJECTS ) {
			$t_file_path = config_get( 'absolute_path_default_upload_folder' );
		}
		else {
		    $t_file_path = project_get_field( $t_project_id, 'file_path' );
			if( $t_file_path == '' ) {
			    $t_file_path = config_get( 'absolute_path_default_upload_folder' );
			}
		}
		$c_file_path = db_prepare_string( $t_file_path );
		$c_new_file_name = db_prepare_string( $p_file_name );

		$t_file_hash = ( 'bug' == $p_table ) ? $t_bug_id : config_get( 'document_files_prefix' ) . '-' . $t_project_id;
		$t_disk_file_name = $t_file_path . file_generate_unique_name( $t_file_hash . '-' . $p_file_name, $t_file_path );
		$c_disk_file_name = db_prepare_string( $t_disk_file_name );

		$t_file_size = filesize( $p_tmp_file );
	    if ( 0 == $t_file_size ) {
		    trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
        }
		$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
        if ( $t_file_size > $t_max_file_size ) {
            trigger_error( ERROR_FILE_TOO_BIG, ERROR );
        }
		$c_file_size = db_prepare_int( $t_file_size );

		$t_method			= config_get( 'file_upload_method' );

		switch ( $t_method ) {
			case FTP:
			case DISK:
				file_ensure_valid_upload_path( $t_file_path );

				if ( !file_exists( $t_disk_file_name ) ) {
					if ( FTP == $t_method ) {
						$conn_id = file_ftp_connect();
						file_ftp_put ( $conn_id, $t_disk_file_name, $p_tmp_file );
						file_ftp_disconnect ( $conn_id );
					}

					if ( !move_uploaded_file( $p_tmp_file, $t_disk_file_name ) ) {
					    trigger_error( FILE_MOVE_FAILED, ERROR );
					}
					chmod( $t_disk_file_name, 0400 );

					$c_content = '';
				} else {
					trigger_error( ERROR_FILE_DUPLICATE, ERROR );
				}
				break;
			case DATABASE:
				$c_content = db_prepare_string( fread ( fopen( $p_tmp_file, 'rb' ), $t_file_size ) );
				break;
			default:
				trigger_error( ERROR_GENERIC, ERROR );
		}

		$t_file_table	= config_get( 'mantis_' . $p_table . '_file_table' );
		$c_id = ( 'bug' == $p_table ) ? $c_bug_id : $c_project_id;
		$query = "INSERT INTO $t_file_table
						(" . $p_table . "_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
					  VALUES
						($c_id, '$c_title', '$c_desc', '$c_disk_file_name', '$c_new_file_name', '$c_file_path', $c_file_size, '$c_file_type', " . db_now() .", '$c_content')";
		db_query( $query );

		if ( 'bug' == $p_table ) {
			# updated the last_updated date
			$result = bug_update_date( $p_bug_id );

			# log new bug
			history_log_event_special( $p_bug_id, FILE_ADDED, $p_file_name );
		}

	}

	# --------------------
	# Return true if file uploading is enabled (in our config and PHP's),
	#  false otherwise
	function file_is_uploading_enabled() {
		if ( ini_get_bool( 'file_uploads' ) && ( ON == config_get( 'allow_file_upload' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check if the user can upload files for this project
	#  return true if they can, false otherwise
	#  the project defaults to the current project and the user to the current user
	function file_allow_project_upload( $p_project_id = null, $p_user_id = null ) {
		if ( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}
		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}
		return ( file_is_uploading_enabled() &&
			 ( access_has_project_level( config_get( 'upload_project_file_threshold' ), $p_project_id, $p_user_id ) ) );
	}

	# --------------------
	# Check if the user can upload files for this bug
	#  return true if they can, false otherwise
	#  the user defaults to the current user
	#
	#  if the bug null (the default) we answer whether the user can
	#   upload a file to a new bug in the current project
	function file_allow_bug_upload( $p_bug_id = null, $p_user_id = null ) {
		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		# If uploads are disbled just return false
		if ( !file_is_uploading_enabled() ) {
			return false;
		}

		if ( null === $p_bug_id ) {		# new bug
			$t_project_id = helper_get_current_project();

			# the user must be the reporter if they're reporting a new bug
			$t_reporter = true;
		} else {						# existing bug
			$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

			# check if the user is the reporter of the bug
			$t_reporter = bug_is_user_reporter( $p_bug_id, $p_user_id );
		}

		# *** If we ever wanted to have a per-project setting enabling file
		#     uploads, we'd want to check it here before exempting the reporter

		if ( $t_reporter && ( ON == config_get( 'allow_reporter_upload' ) ) ) {
			return true;
		}

		# Check the access level against the config setting
        return access_has_project_level( config_get( 'upload_bug_file_threshold' ), $t_project_id, $p_user_id );
	}

	# --------------------
	# checks whether the specified upload path exists and is writable
	function file_ensure_valid_upload_path( $p_upload_path ) {
		if ( is_blank( $p_upload_path ) || !file_exists( $p_upload_path ) || !is_dir( $p_upload_path ) || !is_writable( $p_upload_path ) ) {
			trigger_error( ERROR_FILE_INVALID_UPLOAD_PATH, ERROR );
		}
	}

	# --------------------
	# Get extension given the filename or its full path.
	function file_get_extension( $p_filename ) {
		$ext		= '';
		$dot_found	= false;
		$i			= strlen( $p_filename ) - 1;
		while ( $i >= 0 ) {
			if ( '.' == $p_filename[$i] ) {
				$dot_found = true;
				break;
			}

			# foung a directoryarker before a period.
			if ( ( $p_filename[$i] == "/" ) || ( $p_filename[$i] == "\\" ) ) {
				return '';
			}

			$ext = $p_filename[$i] . $ext;
			$i--;
		}

		if ( $dot_found ) {
			return $ext;
		} else {
			return '';
		}
	}
?>
