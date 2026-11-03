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
 * Relocates attachments already on disk to match the configured subdirectory
 * layout.
 *
 * Changing $g_file_upload_subdirectory_depth only affects new uploads;
 * attachments already on disk keep working where they are. This script brings
 * them into line with the current configuration, in either direction: raising
 * the depth moves files down into subdirectories, and setting it back to 0
 * moves them up into the upload directory again.
 *
 * Each file is moved and its folder column updated one at a time, so the run
 * can be interrupted and restarted without leaving the database and the disk
 * disagreeing.
 *
 * Usage:
 *   php admin/reorganize_attachments.php [--dry-run] [--quiet]
 *
 * @package MantisBT
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( __DIR__ ) . '/core.php' );

if( php_sapi_name() != 'cli' ) {
	echo basename( __FILE__ ) . " must be run from the command line.\n";
	exit( 1 );
}

set_time_limit( 0 );

$g_dry_run = in_array( '--dry-run', $argv, true );
$g_quiet = in_array( '--quiet', $argv, true );

foreach( array_slice( $argv, 1 ) as $t_arg ) {
	if( !in_array( $t_arg, array( '--dry-run', '--quiet' ), true ) ) {
		echo "Unknown option '$t_arg'.\n";
		echo "Usage: php " . basename( __FILE__ ) . " [--dry-run] [--quiet]\n";
		exit( 1 );
	}
}

if( DISK != config_get_global( 'file_upload_method' ) ) {
	echo "file_upload_method is not DISK; there is nothing on disk to move.\n";
	exit( 1 );
}

/**
 * Writes a progress message unless running quietly.
 *
 * @param string $p_message The message.
 *
 * @return void
 */
function reorg_log( $p_message ) {
	global $g_quiet;
	if( !$g_quiet ) {
		echo $p_message . "\n";
	}
}

/**
 * Determines the upload directory configured for a project.
 *
 * @param int $p_project_id The project id.
 *
 * @return string The upload path, with a trailing directory separator.
 */
function reorg_project_path( $p_project_id ) {
	static $s_paths = array();

	if( !isset( $s_paths[$p_project_id] ) ) {
		$t_path = '';
		if( $p_project_id != ALL_PROJECTS ) {
			$t_path = project_get_field( $p_project_id, 'file_path' );
		}
		if( is_blank( $t_path ) ) {
			$t_path = config_get_global( 'absolute_path_default_upload_folder' );
		}
		$s_paths[$p_project_id] = $t_path;
	}

	return $s_paths[$p_project_id];
}

/**
 * Relocates the attachments held in one table.
 *
 * @param string $p_table 'bug' or 'project'.
 *
 * @return array Counts keyed 'moved', 'skipped' and 'missing'.
 */
function reorg_table( $p_table ) {
	global $g_dry_run;

	$t_counts = array( 'moved' => 0, 'skipped' => 0, 'missing' => 0 );

	if( $p_table == 'bug' ) {
		$t_query = new DbQuery(
			'SELECT f.id, f.folder, f.diskfile, b.project_id
			FROM {bug_file} f JOIN {bug} b ON b.id = f.bug_id
			ORDER BY f.id'
		);
	} else {
		$t_query = new DbQuery(
			'SELECT id, folder, diskfile, project_id FROM {project_file} ORDER BY id'
		);
	}
	$t_query->execute();

	while( $t_row = $t_query->fetch() ) {
		$t_basename = basename( $t_row['diskfile'] );
		if( is_blank( $t_basename ) ) {
			continue;
		}

		$t_upload_path = reorg_project_path( (int)$t_row['project_id'] );
		$t_target_dir = $t_upload_path . file_subdirectory_path( $t_basename );
		$t_target = $t_target_dir . $t_basename;

		$t_source = file_get_disk_path( $t_row, (int)$t_row['project_id'] );

		if( $t_source == $t_target && $t_row['folder'] == $t_target_dir ) {
			$t_counts['skipped']++;
			continue;
		}

		if( !file_exists( $t_source ) ) {
			reorg_log( "  MISSING  $t_source (attachment {$t_row['id']})" );
			$t_counts['missing']++;
			continue;
		}

		if( $g_dry_run ) {
			reorg_log( "  would move $t_source -> $t_target" );
			$t_counts['moved']++;
			continue;
		}

		# Create the destination first: if the run is interrupted between the
		# rename and the update, the file is found again on the next pass
		# because file_get_disk_path() falls back to deriving the location.
		if( $t_source != $t_target ) {
			file_ensure_upload_subdirectory( $t_upload_path, $t_basename );
			if( file_exists( $t_target ) ) {
				reorg_log( "  CONFLICT $t_target already exists (attachment {$t_row['id']})" );
				$t_counts['skipped']++;
				continue;
			}
			if( !rename( $t_source, $t_target ) ) {
				reorg_log( "  FAILED   $t_source -> $t_target (attachment {$t_row['id']})" );
				$t_counts['missing']++;
				continue;
			}
		}

		$t_update = new DbQuery(
			'UPDATE {' . $p_table . '_file} SET folder = :folder WHERE id = :id'
		);
		$t_update->bind( 'folder', $t_target_dir );
		$t_update->bind( 'id', (int)$t_row['id'] );
		$t_update->execute();

		$t_counts['moved']++;
	}

	return $t_counts;
}

$t_depth = (int)config_get_global( 'file_upload_subdirectory_depth' );
$t_width = (int)config_get_global( 'file_upload_subdirectory_width' );

reorg_log( 'Target layout: '
	. ( $t_depth < 1
		? 'flat (no subdirectories)'
		: "$t_depth level(s) of $t_width character(s)" )
);
if( $g_dry_run ) {
	reorg_log( 'Dry run: no files will be moved.' );
}

$t_total = array( 'moved' => 0, 'skipped' => 0, 'missing' => 0 );
foreach( array( 'bug', 'project' ) as $t_table ) {
	reorg_log( "Processing {$t_table}_file" );
	$t_counts = reorg_table( $t_table );
	foreach( $t_counts as $t_key => $t_value ) {
		$t_total[$t_key] += $t_value;
	}
	reorg_log( "  moved {$t_counts['moved']}, "
		. "already in place {$t_counts['skipped']}, "
		. "unresolved {$t_counts['missing']}"
	);
}

reorg_log( '' );
reorg_log( "Total: moved {$t_total['moved']}, "
	. "already in place {$t_total['skipped']}, "
	. "unresolved {$t_total['missing']}"
);

exit( $t_total['missing'] > 0 ? 2 : 0 );
