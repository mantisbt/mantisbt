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
 * Insert the bugnote into the database then redirect to the bug page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses file_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'file_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'bugnote_add' );

$f_bug_id		= gpc_get_int( 'bug_id' );
$f_private		= gpc_get_bool( 'private' );
$f_time_tracking	= gpc_get_string( 'time_tracking', '0:00' );
$f_bugnote_text	= trim( gpc_get_string( 'bugnote_text', '' ) );
$f_files		= gpc_get_file( 'ufile', null );

$t_bug = bug_get( $f_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( bug_is_readonly( $t_bug->id ) ) {
	error_parameters( $t_bug->id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $t_bug->id );

if( $f_private ) {
	access_ensure_bug_level( config_get( 'set_view_status_threshold' ), $t_bug->id );
}

# Handle the file upload
if( $f_files !== null ) {
	if( !file_allow_bug_upload( $f_bug_id ) ) {
		access_denied();
	}

	file_process_posted_files_for_bug( $f_bug_id, $f_files );
}

# We always set the note time to BUGNOTE, and the API will overwrite it with TIME_TRACKING
# if $f_time_tracking is not 0 and the time tracking feature is enabled.
$t_bugnote_id = bugnote_add( $t_bug->id, $f_bugnote_text, $f_time_tracking, $f_private, BUGNOTE );
if( !$t_bugnote_id ) {
	error_parameters( lang_get( 'bugnote' ) );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

# Process the mentions in the added note
bugnote_process_mentions( $t_bug->id, $t_bugnote_id, $f_bugnote_text );

# Handle the reassign on feedback feature. Note that this feature generally
# won't work very well with custom workflows as it makes a lot of assumptions
# that may not be true. It assumes you don't have any statuses in the workflow
# between 'bug_submit_status' and 'bug_feedback_status'. It assumes you only
# have one feedback, assigned and submitted status.
if( config_get( 'reassign_on_feedback' ) &&
	 $t_bug->status === config_get( 'bug_feedback_status' ) &&
	 $t_bug->handler_id !== auth_get_current_user_id() &&
	 $t_bug->reporter_id === auth_get_current_user_id() ) {
	if( $t_bug->handler_id !== NO_USER ) {
		bug_set_field( $t_bug->id, 'status', config_get( 'bug_assigned_status' ) );
	} else {
		bug_set_field( $t_bug->id, 'status', config_get( 'bug_submit_status' ) );
	}
}

form_security_purge( 'bugnote_add' );

print_successful_redirect_to_bug( $t_bug->id );
