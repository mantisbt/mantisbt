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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */


/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

form_security_validate( 'move_attachments_project_select' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );


$f_file_type         = gpc_get( 'type' );
$f_projects_to_disk  = gpc_get( 'to_disk', null );


/**
 * Moves attachments from the specified list of projects from database to disk
 * @param string $p_type Attachment type ('bug' or 'project')
 * @param array $p_projects List of projects to process
 * @return array summary of moves per project
 */
function move_attachments_to_disk( $p_type, $p_projects ) {
	if( empty( $p_projects ) ) {
		return array();
	}

	# Build the SQL query based on attachment type
	$t_file_table = db_get_table( "mantis_${p_type}_file_table" );
	switch( $p_type ) {
		case 'project':

			$t_query = "SELECT f.*
				FROM $t_file_table f
				WHERE content <> ''
				  AND f.project_id = " . db_param() . "
				ORDER BY f.filename";
			break;

		case 'bug':
			$t_bug_table = db_get_table( 'mantis_bug_table' );

			$t_query = "SELECT f.*
				FROM $t_file_table f
				JOIN $t_bug_table b ON b.id = f.bug_id
				WHERE content <> ''
				  AND b.project_id = " . db_param() . "
				ORDER BY f.bug_id, f.filename";
			break;
	}

	# Process projects list
	foreach( $p_projects as $t_project ) {
		# Retrieve attachments for the project
		$t_result = db_query_bound( $t_query, array( $t_project ) );

		# Project upload path
		$t_upload_path = project_get_upload_path( $t_project );
		if(    is_blank( $t_upload_path )
			|| !file_exists( $t_upload_path )
			|| !is_dir( $t_upload_path )
			|| !is_writable( $t_upload_path )
		) {
			# Invalid path
			$t_failures = db_num_rows( $t_result );
			$t_data = "ERROR: Upload path '$t_upload_path' does not exist or is not writable";
		} else {
			# Process attachments
			$t_failures = 0;
			$t_data = array();

			if( $p_type == 'project' ) {
				$t_seed = config_get( 'document_files_prefix', null, ALL_USERS, $t_project ) . $t_project;
			}

			while( $t_row = db_fetch_array( $t_result ) ) {
				if( $p_type == 'bug' ) {
					$t_seed = $t_row['bug_id'] . $t_row['filename'];
				}

				$t_filename = $t_upload_path . file_generate_unique_name( $t_seed, $t_upload_path );

				# write file to disk
				if( file_put_contents( $t_filename, $t_row['content'] ) ) {
					# successful, update database
					# @todo do we want to check the size of data transfer matches here?
					$t_update_query = "UPDATE $t_file_table
						SET diskfile = " . db_param() . ",
							folder = " . db_param() . ",
							content = ''
						WHERE id = " . db_param();
					$t_update_result = db_query_bound(
						$t_update_query,
						array( $t_filename, $t_upload_path, $t_row['id'] )
					);

					if( !$t_update_result ) {
						$t_status = 'Database update failed';
						$t_failures++;
					} else {
						$t_status = "Moved to '$t_filename'";
					}
				} else {
					$t_status = "Copy to '$t_filename' failed";
					$t_failures++;
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


$t_moved = move_attachments_to_disk( $f_file_type, $f_projects_to_disk );

form_security_purge( 'move_attachments_project_select' );

$t_redirect_url = 'admin/system_utils.php';

# Page header, menu
html_page_top(
	'MantisBT Administration - Moving Attachments',
	empty( $t_result ) ? $t_redirect_url : null
);

?>

<div align="center">

<?php

# Display results
if( empty( $t_moved ) ) {
	echo "<p>Nothing to do.</p>\n";
} else {
	foreach( $t_moved as $t_row ) {
		printf(
			"<p class=\"bold\">Project '%s' : %d attachments %s.</p>\n",
			$t_row['name'],
			$t_row['rows'],
			( 0 == $t_row['failed']
				? 'moved successfully'
				: 'to move, ' . $t_row['failed'] . ' failures')
		);

		if( is_array( $t_row['data'] ) ) {
			# Display details of moved attachments
			echo '<div><table class="width75">', "\n",
				'<tr class="row-category">',
				$f_file_type == 'bug' ? '<th>Bug ID</th>' : '',
				'<th>File</th><th>Filename</th><th>Status</th>',
				'</tr>';
			foreach( $t_row['data'] as $t_data ) {
				echo '<tr ' . helper_alternate_class() .'>';
				if( $f_file_type == 'bug' ) {
					printf( '<td>%s</td>', bug_format_id( $t_data['bug_id'] ) );
				}
				printf( '<td class="right">%s</td><td>%s</td><td>%s</td></tr>' . "\n",
					$t_data['id'],
					$t_data['filename'],
					$t_data['status']
				);
			}
			echo '</table><br /></div>';
		} else {
			# No data rows - display error message
			echo '<p>' . $t_row['data'] . '</p>';
		}
		echo '<br />';
	}
}

print_bracket_link( $t_redirect_url, 'Back to System Utilities' );

html_page_bottom();
