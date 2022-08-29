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
 * This upgrade moves attachments from the database to the disk
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

form_security_validate( 'move_attachments_project_select' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

$f_file_type         = gpc_get( 'type' );
$f_project_to_move  = gpc_get( 'to_move', null );

/**
 * Moves attachments from the specified list of projects from disk to database
 * @param string $p_type Attachment type ('bug' or 'project')
 * @param array $p_projects List of projects to process
 * @return array summary of moves per project
 */
function move_attachments_to_db( $p_type, $p_projects ) {
	if( empty( $p_projects ) ) {
		return array();
	}

	# Build the SQL query based on attachment type
	$t_file_table = '{' . $p_type . '_file}';
	switch( $p_type ) {
		case 'project':
			$t_query = "SELECT f.*
				FROM {project_file} f
				WHERE content = ''
				  AND f.project_id = " . db_param() . "
				ORDER BY f.filename";
			break;
		case 'bug':
			$t_query = "SELECT f.*
				FROM {bug_file} f
				JOIN {bug} b ON b.id = f.bug_id
				WHERE content = ''
				  AND b.project_id = " . db_param() . "
				ORDER BY f.bug_id, f.filename";
			break;
	}

	# Process projects list
	foreach( $p_projects as $t_project ) {
		# Retrieve attachments for the project
		$t_result = db_query( $t_query, array( $t_project ) );

		# Project upload path
		$t_upload_path = project_get_field( $t_project, 'file_path' );
		if( is_blank( $t_upload_path ) ) {
			$t_upload_path = config_get_global( 'absolute_path_default_upload_folder' );
		}

		if( is_blank( $t_upload_path )
			|| !file_exists( $t_upload_path )
			|| !is_dir( $t_upload_path )
		) {
			# Invalid path
			$t_failures = db_num_rows( $t_result );
			$t_data = "ERROR: Upload path '$t_upload_path' does not exist or is not accessible";
		} else {
			# Process attachments
			$t_failures = 0;
			$t_data = array();

			while( $t_row = db_fetch_array( $t_result ) ) {
				# read file from disk
				$t_filename = $t_row['folder'] . $t_row['diskfile'];

				if ( !file_exists( $t_filename ) ) {
					$t_status = "Original File Not Found '$t_filename'";
					$t_failures++;
				} else {
					$c_content = db_prepare_binary_string( fread( fopen( $t_filename, 'rb' ), $t_row['filesize'] ) );

					# write file to db
					if( db_is_oracle() ) {
						db_update_blob( $t_file_table, 'content', $c_content, "id=" . (int)$t_row['id'] );
						$t_query = "UPDATE $t_file_table SET folder='' WHERE id = " . db_param();
						$t_result2 = db_query( $t_query, array( (int)$t_row['id'] ) );
					} else {
						$t_update_query = "UPDATE $t_file_table
										SET folder = " . db_param() . ",
										content = " . db_param() . "
										WHERE id = " . db_param();
						$t_result2 = db_query( $t_update_query,
							array( '', $c_content, (int)$t_row['id'] )
						);
					}

					if( !$t_result2 ) {
						$t_status = 'Database update failed';
						$t_failures++;
					} else {
						$t_status = "'$t_filename' moved to database";
					}
				}

				# Add the file and status to the list of processed attachments
				$t_file = array(
					'id' => $t_row['id'],
					'filename' => $t_row['filename'],
					'status' => $t_status,
				);
				if( $p_type == 'bug' ) {
					$t_file['bug_id'] = $t_row['bug_id'];
				}
				$t_data[] = $t_file;
			}
		}

		$t_moved[] = array(
			'name'       => project_get_name( $t_project ),
			'path'       => $t_upload_path,
			'rows'       => db_num_rows( $t_result ),
			'failed'     => $t_failures,
			'data'       => $t_data,
		);

	}
	return $t_moved;
}

/**
 * Moves attachments from the specified list of projects from database to disk
 * @param string $p_type     Attachment type ('bug' or 'project').
 * @param array  $p_projects List of projects to process.
 * @return array summary of moves per project
 */
function move_attachments_to_disk( $p_type, array $p_projects ) {
	if( empty( $p_projects ) ) {
		return array();
	}

	# Build the SQL query based on attachment type
	switch( $p_type ) {
		case 'project':
			$t_query = 'SELECT f.*
				FROM {project_file} f
				WHERE content <> \'\'
				  AND f.project_id = ' . db_param() . '
				ORDER BY f.filename';
			break;
		case 'bug':
			$t_query = 'SELECT f.*
				FROM {bug_file} f
				JOIN {bug} b ON b.id = f.bug_id
				WHERE content <> \'\'
				  AND b.project_id = ' . db_param() . '
				ORDER BY f.bug_id, f.filename';
			break;
	}

	# Process projects list
	foreach( $p_projects as $t_project ) {
		# Retrieve attachments for the project
		$t_result = db_query( $t_query, array( $t_project ) );

		# Project upload path
		$t_upload_path = project_get_upload_path( $t_project );
		if( is_blank( $t_upload_path )
			|| !file_exists( $t_upload_path )
			|| !is_dir( $t_upload_path )
			|| !is_writable( $t_upload_path )
		) {
			# Invalid path
			$t_failures = db_num_rows( $t_result );
			$t_data = 'ERROR: Upload path \'' . $t_upload_path . '\' does not exist or is not writeable';
		} else {
			# Process attachments
			$t_failures = 0;
			$t_data = array();

			while( $t_row = db_fetch_array( $t_result ) ) {
				$t_disk_filename = $t_upload_path . $t_row['diskfile'];
				if ( file_exists( $t_disk_filename ) ) {
					$t_status = 'Disk File Already Exists \'' . $t_disk_filename . '\'';
					$t_failures++;
				} else {
					# write file to disk
					if( file_put_contents( $t_disk_filename, $t_row['content'] ) ) {
						# successful, update database
						# @todo do we want to check the size of data transfer matches here?
						switch( $p_type ) {
							case 'project':
								$t_update_query = 'UPDATE {project_file}
									SET folder = ' . db_param() . ', content = \'\'
									WHERE id = ' . db_param();
								break;
							case 'bug':
								$t_update_query = 'UPDATE {bug_file}
									SET folder = ' . db_param() . ', content = \'\'
									WHERE id = ' . db_param();
								break;
						}
						$t_update_result = db_query(
							$t_update_query,
							array( $t_upload_path, $t_row['id'] )
						);

						if( !$t_update_result ) {
							$t_status = 'Database update failed';
							$t_failures++;
						} else {
							$t_status = 'Moved to \'' . $t_disk_filename . '\'';
						}
					} else {
						$t_status = 'Copy to \'' . $t_disk_filename . '\' failed';
						$t_failures++;
					}
				}

				# Add the file and status to the list of processed attachments
				$t_file = array(
					'id' => $t_row['id'],
					'filename' => $t_row['filename'],
					'status' => $t_status,
				);
				if( $p_type == 'bug' ) {
					$t_file['bug_id'] = $t_row['bug_id'];
				}
				$t_data[] = $t_file;
			}
		}

		$t_moved[] = array(
			'name'       => project_get_name( $t_project ),
			'path'       => $t_upload_path,
			'rows'       => db_num_rows( $t_result ),
			'failed'     => $t_failures,
			'data'       => $t_data,
		);

	}
	return $t_moved;
}

form_security_purge( 'move_attachments_project_select' );

# Page header, menu
layout_page_header( 'MantisBT Administration - Moving Attachments' );

layout_admin_page_begin();

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

<?php

if( null == $f_project_to_move ) {
	echo '<div class="alert alert-danger">';
	echo '<p class="lead"><strong>Opps!</strong> Please select the project you want to move the attachment.</p>';
	echo '</div>';
} else {

	$t_moved = array();
	
	foreach( $f_project_to_move as $t_project_to_move ) {
		
		$t_array = explode( ':', $t_project_to_move );

		if( isset( $t_array[1] ) ) {
			$t_project_id = $t_array[1];

			switch( $t_array[0] ) {
				case 'disk':
					$t_moved[] = move_attachments_to_disk( $f_file_type, array( $t_project_id ) );
					break;
				case 'db':
					$t_moved[] = move_attachments_to_db( $f_file_type, array( $t_project_id ) );
					break;
			}
		}
	}
	
	# Display results
	if( empty( $t_moved ) ) {
		echo '<div class="alert alert-danger">';
		echo '<p class="lead">Nothing to do.</p>';
		echo '</div>';
	} else {
		foreach( $t_moved as $t_row ) {
			$t_row = $t_row[0];

			echo '<div class="widget-box widget-color-blue2">';
			echo '<div class="widget-header widget-header-small">';
			echo '<h4 class="widget-title lighter">';
			print_icon( 'fa-paperclip', 'ace-icon' );
			printf(
				"Project '%s' : %d attachments %s",
				$t_row['name'],
				$t_row['rows'],
				( 0 == $t_row['failed']
					? 'moved successfully'
					: 'to move, ' . $t_row['failed'] . ' failures')
			);
			echo '</h4>';
			echo '</div>';
			echo '<div class="widget-body">';
			echo '<div class="widget-main no-padding">';
			if( is_array( $t_row['data'] ) ) {
				# Display details of moved attachments
				echo '<div class="table-responsive">';
				echo '<table class="table table-bordered table-condensed table-hover table-striped">';
				echo '<thead>';
				echo '<tr>',
					$f_file_type == 'bug' ? '<td width="5%">Bug ID</td>' : '',
					'<td width="3%">File ID</td><th width="15%">Filename</td><td width="25%">Status</td>',
					'</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach( $t_row['data'] as $t_data ) {
					echo '<tr>';
					if( $f_file_type == 'bug' ) {
						printf( '<td>%s</td>', bug_format_id( $t_data['bug_id'] ) );
					}
					printf( '<td>%s</td><td>%s</td><td>%s</td></tr>',
						$t_data['id'],
						$t_data['filename'],
						$t_data['status'] );
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';

			} else {
				# No data rows - display error message
				echo '<div class="alert alert-danger">';
				echo '<p>' . $t_row['data'] . '</p>';
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '<br/>';
		}
	}
}
echo '<br/>';
print_link_button( 'system_utils.php', 'Back to System Utilities' );

echo '</div>';

layout_admin_page_end();
