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
 * This file contains configuration checks for attachment handling issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses utility_api.php
 */

if( !defined( 'CHECK_ATTACHMENTS_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'utility_api.php' );

check_print_section_header_row( 'Attachments' );

$t_file_uploads_allowed = config_get_global( 'allow_file_upload' );
check_print_info_row(
	'File uploads are allowed',
	$t_file_uploads_allowed ? 'Yes' : 'No'
);

if( !$t_file_uploads_allowed ) {
	return;
}

check_print_test_row(
	'file_uploads php.ini directive is enabled',
	ini_get_bool( 'file_uploads' ),
	array( false => 'The file_uploads directive in php.ini must be enabled in order for file uploads to work with MantisBT.' )
);

check_print_info_row(
	'Maximum file upload size (per file)',
	check_format_number( config_get_global( 'max_file_size' ) )
);

check_print_test_row(
	'max_file_size MantisBT option is less than or equal to the upload_max_filesize directive in php.ini',
	config_get_global( 'max_file_size' ) <= ini_get_number( 'upload_max_filesize' ),
	array( false => 'max_file_size is currently '
		. check_format_number( config_get_global( 'max_file_size' ) )
		. ' which is greater than the limit of '
		. check_format_number( ini_get_number( 'upload_max_filesize' ) )
		. ' imposed by the php.ini directive upload_max_filesize.' )
);

$t_use_xsendfile = config_get_global( 'file_download_xsendfile_enabled' );
check_print_info_row(
	'<a href="http://www.google.com/search?q=x-sendfile">X-Sendfile</a> file download technique enabled',
	$t_use_xsendfile ? 'Yes' : 'No'
);

if( $t_use_xsendfile ) {
	check_print_test_row(
		'file_download_xsendfile_enabled = ON requires file_upload_method = DISK',
		config_get_global( 'file_upload_method' ) == DISK,
		array( false => 'X-Sendfile file downloading only works when files are stored on a disk.' )
	);

	$t_xsendfile_header_name = config_get_global( 'file_download_xsendfile_header_name' );
	if( $t_xsendfile_header_name !== 'X-Sendfile' ) {
		check_print_info_row(
			'Alternative header name to use for X-Sendfile-like functionality',
			$t_xsendfile_header_name );
	}
}

$t_finfo_exists = class_exists( 'finfo' );
check_print_test_warn_row(
	'Fileinfo extension is available for determining file MIME types',
	$t_finfo_exists,
	array( false => 'Web clients may struggle to download files without knowing the MIME type of each attachment.' )
);

if( $t_finfo_exists ) {
	$t_fileinfo_magic_db_file = config_get_global( 'fileinfo_magic_db_file' );
	if( $t_fileinfo_magic_db_file ) {
		check_print_info_row(
			'Name of magic.db file set with the fileinfo_magic_db_file configuration value',
			config_get_global( 'fileinfo_magic_db_file' ) );
		check_print_test_row(
			'fileinfo_magic_db_file configuration value points to an existing magic.db file',
			file_exists( $t_fileinfo_magic_db_file ) );
		$t_finfo = new finfo( FILEINFO_MIME, $t_fileinfo_magic_db_file );
	} else {
		$t_finfo = new finfo( FILEINFO_MIME );
	}
	check_print_test_row(
		'Fileinfo extension can find and load a valid magic.db file',
		$t_finfo !== false,
		array( false => 'Ensure that the fileinfo_magic_db_file configuration value points to a valid magic.db file.' )
	);
}