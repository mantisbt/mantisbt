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
 * This upgrade moves attachments from the database to the disk
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

// Move type should be attachment or project.
$f_move_type = gpc_get( 'doc' );

function get_prefix( $file_path ) {
	if( substr( $file_path, 0, 1 ) == '/' ) {

		# Unix absolute
		return '';
	}
	if( substr( $file_path, 0, 1 ) == '\\' ) {

		# Windows absolute
		return '';
	}
	if( substr( $file_path, 1, 2 ) == ':\\' ) {

		# Windows absolute
		return '';
	}
	return dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;
}

# ------ move file attachments to issues from database to disk
# select non-empty data fields
# match with the project to get the file path
# store the file in the correct folder
#
# Assumptions: only supports storage in local file system (not FTP)
#              file paths are set up and working
#
# Re-running this is safe because the data
# is not removed from the database until it is successfully copied.
#
function upgrade_move_att2disk( $p_source ) {

	# $p_source is the string "attachment" or "project"
	if( $p_source == 'attachment' ) {
		$t_file_table = db_get_table( 'bug_file' );
		$t_bug_label = "Bug";
	}
	if( $p_source == 'project' ) {
		$t_file_table = db_get_table( 'project_file' );
		$t_bug_label = "Project";
	}

	# check that the source was valid
	if( !isset( $t_file_table ) ) {
		echo 'Failure: Internal Error: File source not set';
		return;
	}

	# check that the destination is set up properly
	$t_upload_method = config_get_global( 'file_upload_method' );
	if( $t_upload_method <> DISK ) {
		echo 'Failure: Upload Method is not DISK';
		return;
	}

	$query = 'SELECT * FROM ' . $t_file_table . ' WHERE content <> \'\'';

	$result = @db_query_bound( $query );

	if( false == $result ) {
		echo '<p>No attachments need to be moved.</p>';
		return;
	}

	$count = db_num_rows( $result );
	echo '<p>Found ' . $count . ' attachments to be moved.</p>';
	$t_failures = 0;

	if( $count > 0 ) {
		echo '<table width="80%" bgcolor="#222222" cellpadding="10" cellspacing="1">';

		# Headings
		echo '<tr bgcolor="#ffffff"><th width="10%">' . $t_bug_label . '</th><th width="20%">Attachment</th><th width="70%">Status</th></tr>';
	}

	for( $i = 0;$i < $count;$i++ ) {
		$t_row = db_fetch_array( $result );

		// trace bug id back to project to determine the proper file path
		if( $p_source == 'attachment' ) {
			$t_project_id = bug_get_field( $t_row['bug_id'], 'project_id' );
			$t_bug_id = $t_row['bug_id'];
		} else {
			$t_project_id = (int) $t_row['project_id'];
			$t_bug_id = $t_project_id;
		}

		$t_file_path = project_get_field( $t_project_id, 'file_path' );
		$prefix = get_prefix( $t_file_path );
		$t_real_file_path = $prefix . $t_file_path;
		$c_filename = file_clean_name( $t_row['filename'] );

		printf( "\n<tr %s><td>%8d</td><td>%s</td><td>", helper_alternate_class(), $t_bug_id, $t_row['filename'] );

		if( is_blank( $t_real_file_path ) || !file_exists( $t_real_file_path ) || !is_dir( $t_real_file_path ) || !is_writable( $t_real_file_path ) ) {
			echo 'Destination ' . $t_real_file_path . ' not writable';
			$t_failures++;
		} else {
			$t_file_name = $t_real_file_path . $c_filename;

			// write file to disk store after adjusting the path
			if( file_put_contents( $t_file_name, $t_row['content'] ) ) {
				// successful, update database
				/** @todo do we want to check the size of data transfer matches here? */
				$c_new_file_name = $t_file_path . $c_filename;
				$query2 = "UPDATE $t_file_table SET diskfile = " . db_param() . ",
						folder = " . db_param() . ", content = '' WHERE id = " . db_param();
				$update = @db_query_bound( $query2, Array( $c_new_file_name, $t_file_path, $t_row['id'] ) );
				if( !$update ) {
					echo 'database update failed';
					$t_failures++;
				} else {
					echo 'moved to ' . $t_file_name;
				}
			} else {
				echo 'copy to ' . $t_file_name . ' failed';
				$t_failures++;
			}
		}

		echo '</td></tr>';
	}

	echo '</table><br />' . $count . ' attachments processed, ' . $t_failures . ' failures';
}

# ---------------------
# main code
#
if( $f_move_type == 'attachment' ) {
	$t_type = 'Attachments';
} else {
	if( $f_move_type == 'project' ) {
		$t_type = 'Project Files';
	} else {
		echo "<p>Invalid value '$f_move_type' for parameter 'doc'.</p>";
		exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>MantisBT Administration - Move <?php echo $t_type?> to Disk</title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="system_utils.php">Back to System Utilities</a> ]
			[ <a href="move_db2disk.php">Refresh view</a> ]
		</td>
		<td class="title">
			Move <?php echo $t_type?> to Disk
		</td>
	</tr>
</table>
<br /><br />

<?php
	upgrade_move_att2disk( $f_move_type );
echo '<p>Completed...</p>';
?>
</body>
</html>
