<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: file_api.php,v 1.13 2002-10-20 23:59:49 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# File API
	###########################################################################

	# --------------------
	# Gets the filename without the bug id prefix.
	function file_get_display_name( $p_filename ) {
		$t_array = explode ('-', $p_filename, 2);
		return $t_array[1];
	}
	# --------------------
	# List the attachments belonging to the specified bug.  This is used from within
	# bug_view_page.php and bug_view_advanced_page.php
	function file_list_attachments ( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$query = "SELECT *, UNIX_TIMESTAMP(date_added) as date_added
				  FROM $t_bug_file_table
				  WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );

		$num_files = db_num_rows( $result );
		for ( $i = 0 ; $i < $num_files ; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$v_filesize = number_format( $v_filesize );
			$v_date_added = date( config_get( 'normal_date_format' ), ( $v_date_added ) );

			echo "<a href=\"file_download.php?f_file_id=$v_id&amp;f_type=bug\">".file_get_display_name($v_filename)."</a> ($v_filesize bytes) <span class=\"italic\">$v_date_added</span>";

			if ( access_level_check_greater_or_equal( config_get( 'handle_bug_threshold' ) ) ) {
				echo " [<a class=\"small\" href=\"bug_file_delete.php?f_bug_id=$p_bug_id&amp;f_file_id=$v_id\">" . lang_get('delete_link') . '</a>]';
			}
			
			if ( ( FTP == config_get( 'file_upload_method' ) ) && file_exists ( $v_diskfile ) ) {
				echo ' (' . lang_get( 'cached' ) . ')';
			}

			if ( $i != ($num_files - 1) ) {
				echo '<br />';
			}
		}
	}
	# --------------------
	# delete all files that are associated with the given bug
	function file_delete_attachments( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$t_method = config_get( 'file_upload_method' );

		if ( ( DISK == $t_method ) || ( FTP == $t_method ) ) {
			# Delete files from disk
			$query = "SELECT diskfile, filename
				FROM $t_bug_file_table
				WHERE bug_id='$c_bug_id'";
			$result = db_query( $query );

			$file_count = db_num_rows( $result );

			# there may be more than one file
			for ( $i = 0 ; $i < $file_count ; $i++ ) {
				$row = db_fetch_array( $result );

				file_delete_local ( $row['diskfile'] );

				if ( FTP == $t_method ) {
					$ftp = file_ftp_connect();
					file_ftp_delete ( $ftp, $row['filename'] );
					file_ftp_disconnect( $ftp );
				}
			}
		}

		# Delete the corresponding db records
		$query = "DELETE
			FROM $t_bug_file_table
			WHERE bug_id='$c_bug_id'";
		$result = db_query($query);

		# db_query() errors on failure so:
		return true;
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
		set_time_limit(0);
		$upload = ftp_put( $p_conn_id, $p_remote_filename, $p_local_filename, FTP_BINARY);
	}
	# --------------------
	# Get a file from the ftp server.
	function file_ftp_get ( $p_conn_id, $p_local_filename, $p_remote_filename ) {
		set_time_limit(0);
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
		# in windows replace with system("del $t_diskfile");
		if ( file_exists( $p_filename ) ) {
			chmod( $p_filename, 0775 );
			unlink( $p_filename );
		}
	}
	# --------------------
	# Return the specified field value
	function file_get_field( $p_file_id, $p_field_name ) {
		$c_file_id		= db_prepare_int( $p_file_id );
		$c_field_name	= db_prepare_string( $p_field_name );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		# get info
		$query = "SELECT $c_field_name
				  FROM $t_bug_file_table
				  WHERE id='$c_file_id'
				  LIMIT 1";
		$result = db_query( $query );

		return db_result( $result );
	}
	# --------------------
	# File type check
	function file_type_check( $p_file_name ) {
		$t_allowed_files = config_get( 'allowed_files' );
		$t_disallowed_files = config_get( 'disallowed_files' );;

		# grab extension
		$t_ext_array = explode( '.', $p_file_name );
		$last_position = count( $t_ext_array )-1;
		$t_extension = $t_ext_array[$last_position];

		# check against disallowed files
		$t_disallowed_arr =  explode_enum_string( $t_disallowed_files );
		foreach ( $t_disallowed_arr as $t_val ) {
		    if ( $t_val == $t_extension ) {
		    	return false;
		    }
		}

		# if the allowed list is note populated then the file must be allowed
		if ( empty( $t_allowed_files ) ) {
			return true;
		}
		# check against allowed files
		$t_allowed_arr = explode_enum_string( $t_allowed_files );
		foreach ( $t_allowed_arr as $t_val ) {
		    if ( $t_val == $t_extension ) {
				return true;
		    }
		}
		return false;
	}
	# --------------------
	function file_add( $p_bug_id, $p_tmp_file, $p_file_name, $p_file_type='' ) {
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_tmp_file		= db_prepare_string( $p_tmp_file );
		$c_file_name	= db_prepare_string( $p_file_name );
		$c_file_type	= db_prepare_string( $p_file_type );

		if ( !file_type_check( $p_file_name ) ) {
			trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
		} else if ( is_uploaded_file( $p_tmp_file ) ) {
			$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

			# grab the file path
			$t_file_path = project_get_field( $t_project_id, 'file_path' );

			$t_bug_id = bug_format_id( $p_bug_id );

			# prepare variables for insertion
			$t_new_file_name = $t_bug_id.'-'.$c_file_name;
			$t_file_size = filesize( $p_tmp_file );

			$t_method = config_get( 'file_upload_method' );
			$t_bug_file_table = config_get( 'mantis_bug_file_table' );

			switch ( $t_method ) {
				case FTP:
				case DISK:
					if ( !file_exists( $t_file_path . $t_new_file_name ) ) {
						if ( FTP == $t_method ) {
							$conn_id = file_ftp_connect();
							file_ftp_put ( $conn_id, $t_new_file_name, $p_tmp_file );
							file_ftp_disconnect ( $conn_id );
						}

						umask( 0333 );  # make read only
						copy( $p_tmp_file, $t_file_path . $t_new_file_name );

						$query = "INSERT INTO $t_bug_file_table
								    (id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								  VALUES
								    (null, $c_bug_id, '', '', '$t_file_path$t_new_file_name', '$t_new_file_name', '$t_file_path', $t_file_size, '$c_file_type', NOW(), '')";
						db_query( $query );
					} else {
						trigger_error( ERROR_FILE_DUPLICATE, ERROR );
					}
					break;
				case DATABASE:
					$t_content = db_prepare_string( fread ( fopen( $p_tmp_file, 'rb' ), $t_file_size ) );
					$query = "INSERT INTO $t_bug_file_table
							    (id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
							  VALUES
							    (null, $c_bug_id, '', '', '$t_file_path$t_new_file_name', '$t_new_file_name', '$t_file_path', $t_file_size, '$c_file_type', NOW(), '$t_content')";
					db_query( $query );
					break;
			}

			# log new bug
			history_log_event_special( $p_bug_id, FILE_ADDED, $p_file_name );
		}

	}
?>
