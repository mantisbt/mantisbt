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
 * This file contains configuration checks for path issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 */

if( !defined( 'CHECK_PATHS_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );

check_print_section_header_row( 'Paths' );

$t_path_config_names = array(
	'absolute_path',
	'core_path',
	'class_path',
	'library_path',
	'config_path',
	'language_path'
);

# Handle file upload default path only if attachments stored on disk
if( DISK == config_get_global( 'file_upload_method' ) ) {
	$t_path_config_names[] = 'absolute_path_default_upload_folder';
}

# Build paths for all configs
$t_paths = array();
foreach( $t_path_config_names as $t_path_config_name ) {
	$t_new_path = array();
	$t_new_path['config_value'] = config_get_global( $t_path_config_name );
	$t_new_path['real_path'] = realpath( $t_new_path['config_value'] );
	$t_paths[$t_path_config_name] = $t_new_path;
}

# Trailing directory separator
foreach( $t_paths as $t_path_config_name => $t_path ) {
	check_print_test_row(
		$t_path_config_name . ' configuration option has a trailing directory separator',
		substr( $t_path['config_value'], -1, 1 ) == DIRECTORY_SEPARATOR,
		array( false =>
			'You must provide a trailing directory separator (' . DIRECTORY_SEPARATOR .
			') to the end of \'' . htmlspecialchars( $t_path['config_value'] ) . '\'.'
		)
	);
}

# Is a directory
foreach( $t_paths as $t_path_config_name => $t_path ) {
	check_print_test_row(
		$t_path_config_name . ' configuration option points to a valid directory',
		is_dir( $t_path['config_value'] ),
		array( false =>
			"The path '" . htmlspecialchars( $t_path['config_value'] ) .
			"' is not a valid directory."
		)
	);
}

# Is readable
foreach( $t_paths as $t_path_config_name => $t_path ) {
	check_print_test_row(
		$t_path_config_name . ' configuration option points to an accessible directory',
		is_readable( $t_path['config_value'] ),
		array( false =>
			"The path '" . htmlspecialchars( $t_path['config_value'] ) .
			"' is not accessible."
		)
	);
}

# File upload default path must be writeable
if( DISK == config_get_global( 'file_upload_method' ) ) {
	$t_path_config_name = 'absolute_path_default_upload_folder';
	$t_path = $t_paths[$t_path_config_name];
	check_print_test_row(
		$t_path_config_name . ' configuration option points to a writable directory',
		is_writable( $t_path['config_value'] ),
		array( false =>
			"The path '" . htmlspecialchars( $t_path['config_value'] ) . "' must be writable."
		)
	);
}

if( $g_failed_test ) {
	return;
}

$t_moveable_paths = array(
	'core_path',
	'class_path',
	'library_path',
	'config_path',
	'language_path'
);

if( $t_paths['absolute_path']['real_path'] !== false ) {
	$t_absolute_path_regex_safe = preg_quote( $t_paths['absolute_path']['real_path'], '/' );
} else {
	$t_absolute_path_regex_safe = preg_quote( $t_paths['absolute_path']['config_value'], '/' );
}
foreach( $t_moveable_paths as $t_moveable_path ) {
	if( $t_paths[$t_moveable_path]['real_path'] !== false ) {
		$t_moveable_real_path = $t_paths[$t_moveable_path]['real_path'];
	} else {
		$t_moveable_real_path = $t_paths[$t_moveable_path]['config_value'];
	}
	check_print_test_warn_row(
		$t_moveable_path . ' configuration option is set to a path outside the web root',
		!preg_match( '/^' . $t_absolute_path_regex_safe . '/', $t_moveable_real_path ),
		array( false => 'For increased security it is recommended that you move the ' . $t_moveable_path . ' directory outside the web root.' )
	);
}

$t_removeable_directories = array(
	'doc',
);

foreach( $t_removeable_directories as $t_removeable_directory ) {
	check_print_test_warn_row(
		'Directory <em><a href="' . htmlentities( config_get_global( 'short_path' ) ) . $t_removeable_directory . '">' . $t_removeable_directory . '</a></em> does not need to exist within the MantisBT root',
		!is_dir( $t_paths['absolute_path']['config_value'] . $t_removeable_directory ),
		array( false => 'The ' . $t_removeable_directory . ' directory within the MantisBT root should be removed as it is not needed for the live operation of MantisBT.' )
	);
}

$t_developer_directories = array(
	'docbook',
	'tests',
);

foreach( $t_developer_directories as $t_developer_directory ) {
	check_print_test_warn_row(
		'Directory <em><a href="' . htmlentities( config_get_global( 'short_path' ) ) . $t_developer_directory . '">' . $t_developer_directory . '</a></em> exists. These files are not included in MantisBT builds. For production use, please use a release build/snapshot, and not the developer git code.',
		!is_dir( $t_paths['absolute_path']['config_value'] . $t_developer_directory ),
		array( false => 'The ' . $t_developer_directory . ' directory within the MantisBT root is for development use and is not included in official releases of MantisBT.' )
	);
}

check_print_test_warn_row(
	'Directory <em><a href="' . htmlentities( config_get_global( 'short_path' ) ) . 'api">api</a></em> should be removed from the MantisBT root if you do not plan on using <a href="http://en.wikipedia.org/wiki/SOAP">SOAP</a>',
	!is_dir( $t_paths['absolute_path']['config_value'] . 'api' )
);
