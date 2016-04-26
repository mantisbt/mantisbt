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
 * This page allows an authorized user to send a reminder by email to another user
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
 * @uses email_api.php
 * @uses error_api.php
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
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'bug_reminder' );

$f_bug_id		= gpc_get_int( 'bug_id' );
$f_to			= gpc_get_int_array( 'to' );
$f_body			= gpc_get_string( 'body' );

$t_bug = bug_get( $f_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( bug_is_readonly( $f_bug_id ) ) {
	error_parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

access_ensure_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );

# Automatically add recipients to monitor list if they are above the monitor
# threshold, option is enabled, and not reporter or handler.
$t_reminder_recipients_monitor_bug = config_get( 'reminder_recipients_monitor_bug' );
$t_monitor_bug_threshold = config_get( 'monitor_bug_threshold' );
$t_handler = bug_get_field( $f_bug_id, 'handler_id' );
$t_reporter = bug_get_field( $f_bug_id, 'reporter_id' );
foreach ( $f_to as $t_recipient ) {
	if( ON == $t_reminder_recipients_monitor_bug
		&& access_has_bug_level( $t_monitor_bug_threshold, $f_bug_id )
		&& $t_recipient != $t_handler
		&& $t_recipient != $t_reporter
	) {
		bug_monitor( $f_bug_id, $t_recipient );
	}
}

$t_result = email_bug_reminder( $f_to, $f_bug_id, $f_body );

# Add reminder as bugnote if store reminders option is ON.
if( ON == config_get( 'store_reminders' ) ) {
	# Build list of recipients, truncated to note_attr fields's length
	$t_attr = '|';
	$t_length = 0;
	foreach( $t_result as $t_id ) {
		$t_recipient = $t_id . '|';
		$t_length += strlen( $t_recipient );
		if( $t_length > 250 ) {
			# Remove trailing delimiter to indicate truncation
			$t_attr = rtrim( $t_attr, '|' );
			break;
		}
		$t_attr .= $t_recipient;
	}

	bugnote_add( $f_bug_id, $f_body, 0, config_get( 'default_reminder_view_status' ) == VS_PRIVATE, REMINDER, $t_attr, null, false );

	# Note: we won't trigger mentions here since reminders are triggered.
}

form_security_purge( 'bug_reminder' );

html_page_top( null, string_get_bug_view_url( $f_bug_id ) );

$t_redirect = string_get_bug_view_url( $f_bug_id );
html_operation_successful( $t_redirect );

html_page_bottom();
