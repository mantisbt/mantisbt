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
 * Add file and redirect to the referring page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses gpc_api.php
 * @uses http_api.php
 * @uses utility_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

$g_bypass_headers = true; # suppress headers as we will send our own later
define( 'COMPRESSION_DISABLED', true );

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'file_api.php' );
require_api( 'gpc_api.php' );
require_api( 'http_api.php' );
require_api( 'utility_api.php' );

auth_ensure_user_authenticated();

$f_show_inline = gpc_get_bool( 'show_inline', false );

# To prevent cross-domain inline hotlinking to attachments we require a CSRF
# token from the user to show any attachment inline within the browser.
# Without this security in place a malicious user could upload a HTML file
# attachment and direct a user to file_download.php?file_id=X&type=bug&show_inline=1
# and the malicious HTML content would be rendered in the user's browser,
# violating cross-domain security.
if( $f_show_inline ) {
	# Disable errors for form_security_validate as we need to send HTTP
	# headers prior to raising an error (the error handler
	# doesn't check that headers have been sent, it just
	# makes the assumption that they've been sent already).
	if( !@form_security_validate( 'file_show_inline' ) ) {
		http_all_headers();
		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
	}
}

$f_file_id = gpc_get_int( 'file_id' );
$f_type	= gpc_get_string( 'type' );

$c_file_id = (integer)$f_file_id;

# we handle the case where the file is attached to a bug
# or attached to a project as a project doc.
$t_query = '';
switch( $f_type ) {
	case 'bug':
		$t_query = 'SELECT * FROM {bug_file} WHERE id=' . db_param();
		break;
	case 'doc':
		$t_query = 'SELECT * FROM {project_file} WHERE id=' . db_param();
		break;
	default:
		access_denied();
}
$t_result = db_query( $t_query, array( $c_file_id ) );
$t_row = db_fetch_array( $t_result );
if( false === $t_row ) {
	# Attachment not found
	error_parameters( $c_file_id );
	trigger_error( ERROR_FILE_NOT_FOUND, ERROR );
}
/**
 * @var int    $v_bug_id
 * @var int    $v_project_id
 * @var string $v_diskfile
 * @var string $v_filename
 * @var int    $v_filesize
 * @var string $v_file_type
 * @var string $v_content
 * @var int    $v_date_added
 * @var int    $v_user_id
 * @var int    $v_bugnote_id
 */
extract( $t_row, EXTR_PREFIX_ALL, 'v' );

if( $f_type == 'bug' ) {
	$t_project_id = bug_get_field( $v_bug_id, 'project_id' );
} else {
	$t_project_id = $v_project_id;
}

# Check access rights
switch( $f_type ) {
	case 'bug':
		if( !file_can_download_bug_attachments( $v_bug_id, $v_user_id )
		|| !file_can_download_bugnote_attachments( $v_bugnote_id, $v_user_id )
		) {
			access_denied();
		}
		break;
	case 'doc':
		# Check if project documentation feature is enabled.
		if( OFF == config_get( 'enable_project_documentation' ) ) {
			access_denied();
		}

		access_ensure_project_level( config_get( 'view_proj_doc_threshold' ), $v_project_id );
		break;
}

# throw away output buffer contents (and disable it) to protect download
while( @ob_end_clean() ) {
}

if( ini_get( 'zlib.output_compression' ) && function_exists( 'ini_set' ) ) {
	ini_set( 'zlib.output_compression', false );
}

http_security_headers();

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );

# To fix an IE bug which causes problems when downloading
# attached files via HTTPS, we disable the "Pragma: no-cache"
# command when IE is used over HTTPS.
global $g_allow_file_cache;
if( http_is_protocol_https() && is_browser_internet_explorer() ) {
	# Suppress "Pragma: no-cache" header.
} else {
	if( !isset( $g_allow_file_cache ) ) {
		header( 'Pragma: no-cache' );
	}
}
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $v_date_added ) );

$t_upload_method = config_get( 'file_upload_method' );
$t_filename = file_get_display_name( $v_filename );

# Content headers
$t_content_type = $v_file_type;

$t_content_type_override = file_get_content_type_override( $t_filename );
$t_file_info_type = false;

switch( $t_upload_method ) {
	case DISK:
		$t_local_disk_file = file_normalize_attachment_path( $v_diskfile, $t_project_id );
		if( file_exists( $t_local_disk_file ) ) {
			$t_file_info_type = file_get_mime_type( $t_local_disk_file );
		}
		break;
	case DATABASE:
		$t_file_info_type = file_get_mime_type_for_content( $v_content );
		break;
	default:
		trigger_error( ERROR_GENERIC, ERROR );

}

if( $t_file_info_type !== false ) {
	$t_content_type = $t_file_info_type;
}

if( $t_content_type_override ) {
	$t_content_type = $t_content_type_override;
}

# Decide what should open inline in the browser vs. download as attachment
# https://www.thoughtco.com/mime-types-by-content-type-3469108
$t_show_inline = $f_show_inline;
$t_mime_force_inline = array(
	'image/jpeg', 'image/gif', 'image/tiff', 'image/bmp', 'image/svg+xml', 'image/png',
	'application/pdf' );
$t_mime_force_attachment = array( 'application/x-shockwave-flash', 'text/html' );

# extract mime type from content type
$t_mime_type = explode( ';', $t_content_type, 2 );
$t_mime_type = $t_mime_type[0];

if( in_array( $t_mime_type, $t_mime_force_inline ) ) {
	$t_show_inline = true;
} else if( in_array( $t_mime_type, $t_mime_force_attachment ) ) {
	$t_show_inline = false;
}

http_content_disposition_header( $t_filename, $t_show_inline );

header( 'Content-Type: ' . $t_content_type );
header( 'Content-Length: ' . $v_filesize );

# Don't let Internet Explorer second-guess our content-type [1]
# Also disable Flash content-type sniffing [2]
# [1] http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
# [2] http://50.56.33.56/blog/?p=242
header( 'X-Content-Type-Options: nosniff' );

# dump file content to the connection.
switch( $t_upload_method ) {
	case DISK:
		readfile( $t_local_disk_file );
		break;
	case DATABASE:
		echo $v_content;
}
