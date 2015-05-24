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
 * Add file to a bug and then view the bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
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
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

helper_begin_long_process();

$f_bug_id	= gpc_get_int( 'bug_id', -1 );
$f_files		= gpc_get_file( 'ufile', null );

if( $f_bug_id == -1 && $f_files === null ) {
	# _POST/_FILES does not seem to get populated if you exceed size limit so check if bug_id is -1
	trigger_error( ERROR_FILE_TOO_BIG, ERROR );
}

form_security_validate( 'bug_file_add' );

$t_bug = bug_get( $f_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( !file_allow_bug_upload( $f_bug_id ) ) {
	access_denied();
}

file_process_posted_files_for_bug( $f_bug_id, $f_files );

form_security_purge( 'bug_file_add' );

# Determine which view page to redirect back to.
$t_redirect_url = string_get_bug_view_url( $f_bug_id );

html_page_top( null, $t_redirect_url );

html_operation_successful( $t_redirect_url );

html_page_bottom();
