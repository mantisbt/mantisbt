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
 * Update bug data then redirect to the appropriate viewing page
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
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'relationship_api.php' );

form_security_validate( 'bug_update' );

$f_bug_id      = gpc_get_int( 'bug_id' );
$f_update_type = gpc_get_string( 'action_type', BUG_UPDATE_TYPE_NORMAL );

if( helper_get_current_project() != bug_get_field( $f_bug_id, 'project_id' ) ) {
    $g_project_override = bug_get_field( $f_bug_id, 'project_id' );
}

$t_issue = array(
                          'last_updated' => gpc_get_string( 'last_updated' ),
);

$t_additional_info = gpc_get_string( 'additional_information', '' );
if( !is_blank( $t_additional_info ) ) {
    $t_issue['additional_information'] = $t_additional_info;
}

$t_build = gpc_get_string( 'build', null );
if( !is_blank( $t_build ) ) {
    $t_issue['build'] = $t_build;
}

$t_category_id = gpc_get_int( 'category_id', 0 );
if( $t_category_id != 0 ) {
    $t_issue['category'] = array( 'id' => $t_category_id );
}

$t_description = gpc_get_string( 'description', null );
if( !is_blank( $t_description ) ) {
    $t_issue['description'] = $t_description;
}

$t_due_date = gpc_get_string( 'due_date', null );
if( !is_blank( $t_due_date ) ) {
    $t_issue['due_date'] = $t_due_date;
}

$t_duplicate_id = gpc_get_int( 'duplicate_id', 0 );
if( $t_duplicate_id != 0 ) {
    $t_issue['duplicate_id'] = array( 'id' => $t_duplicate_id );
}

$t_eta = gpc_get_int( 'eta', 0 );
if( $t_eta != 0 ) {
    $t_issue['eta'] = array( 'id' => $t_eta );
}

$t_fixed_in_version = gpc_get_string( 'fixed_in_version', null );
if( !is_blank( $t_fixed_in_version ) ) {
    $t_issue['fixed_in_version'] = $t_fixed_in_version;
}

$t_handler_id = gpc_get_int( 'handler_id', NO_USER );
if( $t_handler_id != NO_USER ) {
    $t_issue['handler'] = array( 'id' => $t_handler_id );
}

$t_os = gpc_get_string( 'os', '' );
if( !is_blank( $t_os ) ) {
    $t_issue['os'] = $t_os;
}

$t_os_build = gpc_get_string( 'os_build', '' );
if( !is_blank( $t_os_build ) ) {
    $t_issue['os_build'] = $t_os_build;
}

$t_platform = gpc_get_string( 'platform', '' );
if( !is_blank( $t_platform ) ) {
    $t_issue['platform'] = $t_platform;
}

$t_priority = gpc_get_int( 'priority', 0 );
if( $t_priority != 0 ) {
    $t_issue['priority'] = array( 'id' => $t_priority );
}

$t_projection = gpc_get_int( 'projection', 0 );
if( $t_projection != 0 ) {
    $t_issue['projection'] = array( 'id' => $t_projection );
}

$t_reporter_id = gpc_get_int( 'reporter_id', 0 );
if( $t_reporter_id != 0 ) {
    $t_issue['reporter'] = array( 'id' => $t_reporter_id );
}

$t_reproducibility = gpc_get_int( 'reproducibility', 0 );
if( $t_reproducibility != 0 ) {
    $t_issue['reproducibility'] = array( 'id' => $t_reproducibility );
}

$t_resolution_id = gpc_get_int( 'resolution', 0 );
if( $t_resolution_id != 0 ) {
    $t_issue['resolution'] = array( 'id' => $t_resolution_id );
}

$t_severity = gpc_get_int( 'severity', 0 );
if( $t_severity != 0 ) {
    $t_issue['severity'] = array( 'id' => $t_severity );
}

$t_status = gpc_get_int( 'status', 0 );
if( $t_status != 0 ) {
    $t_issue['status'] = array( 'id' => $t_status );
}

$t_steps_to_reproduce = gpc_get_string( 'steps_to_reproduce', '' );
if( !is_blank( $t_steps_to_reproduce ) ) {
    $t_issue['steps_to_reproduce'] = $t_steps_to_reproduce;
}

$t_summary = gpc_get_string( 'summary', '' );
if( !is_blank( $t_summary ) ) {
    $t_issue['summary'] = $t_summary;
}

$t_target_version = gpc_get_string( 'target_version', '' );
if( !is_blank( $t_target_version ) ) {
    $t_issue['target_version'] = array( 'name' => $t_target_version );
}

$t_version = gpc_get_string( 'product_version', '' );
if( !is_blank( $t_version ) ) {
    $t_issue['version'] = array( 'name' => $t_version );
}

$t_view_state = gpc_get_int( 'view_state', 0 );
if( $t_view_state != 0 ) {
    $t_issue['view_state'] = array( 'id' => $t_view_state );
}

$t_bugnote = array();

$t_bugnote_text = gpc_get_string( 'bugnote_text', '' );
if( !is_blank( $t_bugnote_text ) ) {
    $t_bugnote['bugnote_text'] = $t_bugnote_text;
}

$t_private = gpc_get_bool( 'private' );
if( $t_private != 0 ) {
    $t_bugnote['private'] = VS_PRIVATE;
}

$t_time_tracking = gpc_get_string( 'time_tracking', '' );
if( !is_blank( $t_time_tracking ) ) {
    $t_bugnote['time_tracking'] = $t_time_tracking;
}

# Validate the custom fields before adding the bug.
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
$t_custom_fields            = array();
foreach( $t_related_custom_field_ids as $t_id ) {
    $t_def = custom_field_get_definition( $t_id );

    # Produce an error if the field is required but wasn't posted

    $t_custom_fields[] = array(
                              'field' => array( 'id' => $t_id ),
                              'value' => gpc_get_custom_field( 'custom_field_' . $t_id, $t_def['type'], null )
    );
}

if( !empty( $t_custom_fields ) ) {
    $t_issue['custom_fields'] = $t_custom_fields;
}

$t_data            = array(
                          'payload' => array(
                                                    'issue'   => $t_issue,
                                                    'bugnote' => $t_bugnote,
                          ),
);
$t_data['options'] = array(
                          'action_type' => $f_update_type,
                          'bug_id'      => $f_bug_id,
);

$t_command = new IssueUpdateCommand( $t_data );
$t_command->execute();

form_security_purge( 'bug_update' );

print_successful_redirect_to_bug( $f_bug_id );
