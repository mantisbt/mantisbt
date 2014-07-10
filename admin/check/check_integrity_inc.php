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
 * This file contains configuration checks for file integrity issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 */

if( !defined( 'CHECK_INTEGRITY_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );

$t_this_directory = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
if( file_exists( $t_this_directory . 'integrity_release_blobs.php' ) ) {
	require_once( $t_this_directory . 'integrity_release_blobs.php' );
}
if( file_exists( $t_this_directory . 'integrity_commit_blobs.php' ) ) {
	require_once( $t_this_directory . 'integrity_commit_blobs.php' );
}

/**
 * Returns the Git Object hash for given file
 *
 * @param string $p_file Filename of file contained in git repository.
 * @return string
 */
function create_git_object_hash( $p_file ) {
	$t_hash_context = hash_init( 'sha1' );
	hash_update( $t_hash_context, 'blob ' . filesize( $p_file ) . "\x00" );
	hash_update_file( $t_hash_context, $p_file );
	$t_object_hash = hash_final( $t_hash_context );
	return $t_object_hash;
}

/**
 * Get git tag of object hash for given file
 * @param string $p_filename    Filename of file contained in git repository.
 * @param string $p_object_hash Object hash.
 * @return string
 */
function get_release_containing_object_hash( $p_filename, $p_object_hash ) {
	global $g_integrity_release_blobs;
	if( !isset( $g_integrity_release_blobs ) ) {
		return null;
	}
	foreach( $g_integrity_release_blobs as $t_tag => $t_blobs ) {
		if( array_key_exists( $p_filename, $t_blobs ) ) {
			if( $t_blobs[$p_filename] == $p_object_hash ) {
				return $t_tag;
			}
		}
	}
	return null;
}

/**
 * Get commit of object hash for given file
 * @param string $p_filename    Filename of file contained in git repository.
 * @param string $p_object_hash Object hash.
 * @return string
 */
function get_commit_containing_object_hash( $p_filename, $p_object_hash ) {
	global $g_integrity_commit_blobs;
	if( !isset( $g_integrity_commit_blobs ) ) {
		return null;
	}
	if( array_key_exists( $p_filename, $g_integrity_commit_blobs ) ) {
		$t_blobs = $g_integrity_commit_blobs[$p_filename];
		if( array_key_exists( $p_object_hash, $t_blobs ) ) {
			return $t_blobs[$p_object_hash];
		}
	}
	return null;
}

/**
 * Check File integrity of local files against release
 *
 * @param string $p_directory            Directory.
 * @param string $p_base_directory       Base directory.
 * @param string $p_relative_path_prefix Relative path prefix.
 * @param array  $p_ignore_files         Files to ignore.
 * @return void
 */
function check_file_integrity_recursive( $p_directory, $p_base_directory, $p_relative_path_prefix = '', array $p_ignore_files = array() ) {
	global $g_integrity_blobs, $g_integrity_release_blobs;
	if( $t_handle = opendir( $p_directory ) ) {
		while( false !== ( $t_file = readdir( $t_handle ) ) ) {
			if( $t_file == '.' || $t_file == '..' ) {
				continue;
			}
			$t_file_absolute = $p_directory . $t_file;
			$t_file_relative = preg_replace( '@^' . preg_quote( $p_base_directory, '@' ) . '@', '', $t_file_absolute );
			$t_file_relative = $p_relative_path_prefix . $t_file_relative;
			$t_file_relative = strtr( $t_file_relative, '\\', '/' );
			$t_file_relative = ltrim( $t_file_relative, '/' );
			if( is_dir( $t_file_absolute ) ) {
				if( in_array( $t_file_relative . '/', $p_ignore_files ) ) {
					continue;
				}
				check_file_integrity_recursive( $t_file_absolute . DIRECTORY_SEPARATOR, $p_base_directory, $p_relative_path_prefix, $p_ignore_files );
			} else if( is_file( $t_file_absolute ) ) {
				if( in_array( $t_file_relative, $p_ignore_files ) ) {
					continue;
				}
				$t_file_hash = create_git_object_hash( $t_file_absolute );
				$t_integrity_ok = false;
				$t_integrity_info = 'This file does not originate from any official MantisBT release or snapshot.';
				$t_release = get_release_containing_object_hash( $t_file_relative, $t_file_hash );
				if( $t_release !== null ) {
					$t_integrity_ok = true;
					$t_release_sanitised = htmlentities( $t_release );
					$t_integrity_info = 'Matches file from release <a href="http://git.mantisbt.org/?p=mantisbt.git;a=commit;h=release-' . $t_release_sanitised . '">' . $t_release_sanitised . '</a>.';
				} else {
					$t_commit = get_commit_containing_object_hash( $t_file_relative, $t_file_hash );
					if( $t_commit !== null ) {
						$t_integrity_ok = true;
						$t_commit_sanitised = htmlentities( $t_commit );
						$t_integrity_info = 'Matches file introduced or modified in commit <a href="http://git.mantisbt.org/?p=mantisbt.git;a=commit;h=' . $t_commit_sanitised . '">' . $t_commit_sanitised . '</a>.';
					}
				}
				check_print_test_warn_row(
					htmlentities( $t_file_absolute ),
					$t_integrity_ok,
					$t_integrity_info );
			}
		}
	}
}

check_print_section_header_row( 'Integrity' );

$t_can_perform_integrity_check = isset( $g_integrity_release_blobs ) && isset( $g_integrity_commit_blobs );
check_print_test_warn_row(
	'Reference integrity blob hashes are available for verifying the integrity of this MantisBT installation',
	$t_can_perform_integrity_check,
	array( false => 'Ensure integrity_release_blobs.php and/or integrity_commit_blobs.php are available.' )
);

if( !$t_can_perform_integrity_check ) {
	return;
}

$t_absolute_base_dir = realpath( config_get_global( 'absolute_path' ) ) . DIRECTORY_SEPARATOR;
$t_ignore_files = array(
	'.git/',
	'admin/integrity_commit_blobs.php',
	'admin/integrity_release_blobs.php',
	'core/',
	'lang/',
	'library/',
	'plugins/',
	'config/config_inc.php',
	'config/custom_constants_inc.php',
	'config/custom_functions_inc.php',
	'config/custom_relationships_inc.php',
	'config/custom_strings_inc.php',
	'mantis_offline.php'
);
check_file_integrity_recursive( $t_absolute_base_dir, $t_absolute_base_dir, '', $t_ignore_files );

$t_base_dir = realpath( config_get_global( 'core_path' ) ) . DIRECTORY_SEPARATOR;
$t_ignore_files = array(
	'core/classes/'
);
check_file_integrity_recursive( $t_base_dir, $t_base_dir, 'core/', $t_ignore_files );

$t_base_dir = realpath( config_get_global( 'class_path' ) ) . DIRECTORY_SEPARATOR;
check_file_integrity_recursive( $t_base_dir, $t_base_dir, 'core/classes/' );

$t_base_dir = realpath( config_get_global( 'library_path' ) ) . DIRECTORY_SEPARATOR;
$t_ignore_files = array(
	'library/jpgraph/',
	'library/FirePHPCore/'
);
check_file_integrity_recursive( $t_base_dir, $t_base_dir, 'library/', $t_ignore_files );

$t_base_dir = realpath( config_get_global( 'language_path' ) ) . DIRECTORY_SEPARATOR;
check_file_integrity_recursive( $t_base_dir, $t_base_dir, 'lang/' );

$t_builtin_plugins = array(
	'MantisCoreFormatting',
	'MantisGraph',
	'XmlImportExport'
);
$t_plugins_dir = $t_absolute_base_dir . 'plugins' . DIRECTORY_SEPARATOR;
foreach( $t_builtin_plugins as $t_builtin_plugin ) {
	$t_base_dir = $t_plugins_dir . $t_builtin_plugin . DIRECTORY_SEPARATOR;
	check_file_integrity_recursive( $t_base_dir, $t_base_dir, 'plugins/' . $t_builtin_plugin . DIRECTORY_SEPARATOR );
}
