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
 * Delete Project Documentation
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'proj_doc_delete' );

# Check if project documentation feature is enabled.
if( OFF == config_get( 'enable_project_documentation' ) ) {
	access_denied();
}

$f_file_id = gpc_get_int( 'file_id' );

$t_project_id = file_get_field( $f_file_id, 'project_id', 'project' );

access_ensure_project_level( config_get( 'upload_project_file_threshold' ), $t_project_id );

$t_query = 'SELECT title FROM {project_file} WHERE id=' . db_param();
$t_result = db_query( $t_query, array( $f_file_id ) );
$t_title = db_result( $t_result );

# Confirm with the user
helper_ensure_confirmed( lang_get( 'confirm_file_delete_msg' ) .
	'<br/>' . lang_get( 'filename_label' ) . lang_get( 'word_separator' ) . string_display( $t_title ),
	lang_get( 'file_delete_button' ) );

file_delete( $f_file_id, 'project' );

form_security_purge( 'proj_doc_delete' );

$t_redirect_url = 'proj_doc_page.php';

html_page_top( null, $t_redirect_url );

html_operation_successful( $t_redirect_url );

html_page_bottom();
