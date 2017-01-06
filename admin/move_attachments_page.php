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
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

# Page header, menu
layout_page_header( 'MantisBT Administration - Moving Attachments' );

layout_admin_page_begin();
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<?php

# File type should be 'bug' (default) or 'project'
$f_file_type = gpc_get( 'type', 'bug' );

function get_attachment_stats( $p_file_type, $p_in_db ) {
	if( $p_in_db ) {
		$t_compare = "<> ''";
	} else {
		$t_compare = " = ''";
	}
	switch( $p_file_type ) {
		case 'project':
			$t_query = "SELECT p.id, p.name, COUNT(f.id) stats
				FROM {project_file} f
				LEFT JOIN {project} p ON p.id = f.project_id
				WHERE content $t_compare
				GROUP BY p.id, p.name
				ORDER BY p.name";
			break;
		case 'bug':
		default:
			$t_query = "SELECT p.id, p.name, COUNT(f.id) stats
				FROM {bug_file} f
				JOIN {bug} b ON b.id = f.bug_id
				JOIN {project} p ON p.id = b.project_id
				WHERE content $t_compare
				GROUP BY p.id, p.name
				ORDER BY p.name";
			break;
	}
	$t_result = db_query( $t_query );
	$t_stats = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_project_id = (int) $t_row['id'];
		$t_stats[$t_project_id] = $t_row['stats'];
	}
	return $t_stats;
}

switch( $f_file_type ) {
	case 'project':
		$t_type = 'Project Files';
		break;
	case 'bug':
	default:
		$t_type = 'Attachments';
		break;
}

# Build list, excluding projects having upload method other than DISK
$t_db_stats = get_attachment_stats( $f_file_type, true );
$t_disk_stats = get_attachment_stats( $f_file_type, false );
$t_projects = project_get_all_rows();

# Display name for All Projects
if( isset( $t_projects[ALL_PROJECTS] ) ) {
	$t_projects[ALL_PROJECTS]['name'] = 'All Projects';
}

# Display table of projects for user selection
?>
<div>
<p>
	<?php print_link_button( helper_mantis_url( 'admin/system_utils.php' ), 'Back to System Utilities' ); ?>
</p>
</div>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-paperclip"></i>
		<?php echo "$t_type to move"; ?>
	</h4>
</div>
<div class="widget-body">
	<div class="widget-main no-padding">

<form name="move_attachments_project_select" method="post" action="move_attachments.php">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-hover table-striped">
	<thead>
		<tr>
			<th>Project name</th>
			<th width="28%">File Path</th>
			<th width="5%">Disk</th>
			<th width="5%">Database</th>
			<th width="5%">Attachments</th>
			<th width="5%">Storage</th>
			<th width="7%">To Disk</th>
			<th width="7%">To Database</th>
		</tr>
	</thead>
	
<?php
	echo '<tbody>';
	# Printing rows of projects with attachments to move
	foreach( $t_projects as $t_id => $t_project ) {
		$t_db_count = 0;
		$t_disk_count = 0;

		if( isset( $t_db_stats[$t_id] ) ) {
			$t_db_count = $t_db_stats[$t_id];
		}
		if( isset( $t_disk_stats[$t_id] ) ) {
			$t_disk_count = $t_disk_stats[$t_id];
		}

		$t_upload_method = config_get( 'file_upload_method', null, ALL_USERS, $t_id );
		if( $t_upload_method == DISK ) {
			$t_method = 'Disk';
		} else {
			# Must be DATABASE
			$t_method = 'Database';
		}

		$t_file_path = $t_project['file_path'];
		if( is_blank( $t_file_path ) ) {
			$t_file_path = config_get( 'absolute_path_default_upload_folder' );
		}

		echo '<tr>';
		echo '<td>' . $t_project['name'] . '</td>';
		echo '<td class="left">' . $t_file_path . '</td>';
		echo '<td class="center">' . $t_disk_count . '</td>';
		echo '<td class="center">' . $t_db_count . '</td>';
		echo '<td class="center">' . ( $t_db_count + $t_disk_count ) . '</td>';
		echo '<td class="center">' . $t_method . '</td>';

		if( $t_upload_method == DISK ) {
			if ( !is_blank( $t_file_path ) && $t_db_count > 0 ) {
				echo '<td class="center"><input type="checkbox" name="to_move[]" value="disk:' . $t_id . '" /></td>';
			} else {
				echo '<td class="center">-</td>';
			}
			echo '<td class="center">-</td>';
		} else {
			echo '<td class="center">-</td>';
			if ( $t_disk_count > 0 ) {
				echo '<td class="center"><input type="checkbox" name="to_move[]" value="db:' . $t_id . '" /></td>';
			} else {
				echo '<td class="center">-</td>';
			}
		}
		echo "</tr>\n";
	}
	echo '</tbody>';
	echo form_security_field( 'move_attachments_project_select' );
?>
	
</table>
<div class="widget-toolbox padding-8 clearfix">
	<input name="type" type="hidden" value="<?php echo $f_file_type ?>" />
	<input type="submit" class="btn btn-primary btn-white btn-round" value="Move Attachments" />
</div>
</div>
</form>
</div>
</div>
</div>
</div>

<?php
layout_admin_page_end();
