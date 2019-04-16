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
 * This page stores the reported bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'bug_report' );

$f_master_bug_id = gpc_get_int( 'm_id', 0 );
$f_rel_type = gpc_get_int( 'rel_type', BUG_REL_NONE );
$f_copy_notes_from_parent = gpc_get_bool( 'copy_notes_from_parent', false );
$f_copy_attachments_from_parent = gpc_get_bool( 'copy_attachments_from_parent', false );
$f_report_stay = gpc_get_bool( 'report_stay', false );

$t_clone_info = array(
	'master_issue_id' => $f_master_bug_id,
	'relationship_type' => $f_rel_type,
	'copy_notes' => $f_copy_notes_from_parent,
	'copy_files' => $f_copy_attachments_from_parent
);

if( $f_master_bug_id > 0 ) {
	bug_ensure_exists( $f_master_bug_id );

	# User can view the master bug
	access_ensure_bug_level( config_get( 'view_bug_threshold' ), $f_master_bug_id );

	if( bug_is_readonly( $f_master_bug_id ) ) {
		error_parameters( $f_master_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}
	$t_master_bug = bug_get( $f_master_bug_id, true );
	$t_project_id = $t_master_bug->project_id;
} else {
	$f_project_id = gpc_get_int( 'project_id' );
	$t_project_id = $f_project_id;
}

$t_issue = array(
	'project' => array( 'id' => $t_project_id ),
	'reporter' => array( 'id' => auth_get_current_user_id() ),
	'summary' => gpc_get_string( 'summary' ),
	'description' => gpc_get_string( 'description' ),
);

$t_tag_string = '';
$f_tag_select = gpc_get_int( 'tag_select', 0 );
if( $f_tag_select != 0 ) {
	$t_tag_string = tag_get_name( $f_tag_select );
}

$f_tag_string = gpc_get_string( 'tag_string', '' );
if( !is_blank( $f_tag_string ) ) {
	$t_tag_string = is_blank( $t_tag_string ) ? $f_tag_string : ',' . $f_tag_string;
}

$t_tags = tag_parse_string( $t_tag_string );
if( !empty( $t_tags ) ) {
	$t_issue['tags'] = array();
	foreach( $t_tags as $t_tag ) {
		$t_issue['tags'][] = array( 'id' => $t_tag['id'], 'name' => $t_tag['name'] );
	}
}

$f_files = gpc_get_file( 'ufile', null );
if( $f_files !== null && !empty( $f_files ) ) {
	$t_issue['files'] = helper_array_transpose( $f_files );
}

$t_build = gpc_get_string( 'build', '' );
if( !is_blank( $t_build ) ) {
	$t_issue['build'] = $t_build;
}

$t_platform = gpc_get_string( 'platform', '' );
if( !is_blank( $t_platform ) ) {
	$t_issue['platform'] = $t_platform;
}

$t_os = gpc_get_string( 'os', '' );
if( !is_blank( $t_os ) ) {
	$t_issue['os'] = $t_os;
}

$t_os_build = gpc_get_string( 'os_build', '' );
if( !is_blank( $t_os_build ) ) {
	$t_issue['os_build'] = $t_os_build;
}

$t_version = gpc_get_string( 'product_version', '' );
if( !is_blank( $t_version ) ) {
	$t_issue['version'] = array( 'name' => $t_version );
}

$t_target_version = gpc_get_string( 'target_version', '' );
if( !is_blank( $t_target_version ) ) {
	$t_issue['target_version'] = array( 'name' => $t_target_version );
}

$t_profile_id = gpc_get_int( 'profile_id', 0 );
if( $t_profile_id != 0 ) {
	$t_issue['profile'] = array( 'id' => $t_profile_id );
}

$t_handler_id = gpc_get_int( 'handler_id', NO_USER );
if( $t_handler_id != NO_USER ) {
	$t_issue['handler'] = array( 'id' => $t_handler_id );
}

$t_view_state = gpc_get_int( 'view_state', 0 );
if( $t_view_state != 0 ) {
	$t_issue['view_state'] = array( 'id' => $t_view_state );
}

$t_category_id = gpc_get_int( 'category_id', 0 );
if( $t_category_id != 0 ) {
	$t_issue['category'] = array( 'id' => $t_category_id );
}

$t_reproducibility = gpc_get_int( 'reproducibility', 0 );
if( $t_reproducibility != 0 ) {
	$t_issue['reproducibility'] = array( 'id' => $t_reproducibility );
}

$t_severity = gpc_get_int( 'severity', 0 );
if( $t_severity != 0 ) {
	$t_issue['severity'] = array( 'id' => $t_severity );
}

$t_priority = gpc_get_int( 'priority', 0 );
if( $t_priority != 0 ) {
	$t_issue['priority'] = array( 'id' => $t_priority );
}

$t_projection = gpc_get_int( 'projection', 0 );
if( $t_projection != 0 ) {
	$t_issue['projection'] = array( 'id' => $t_projection );
}

$t_eta = gpc_get_int( 'eta', 0 );
if( $t_eta != 0 ) {
	$t_issue['eta'] = array( 'id' => $t_eta );
}

$t_resolution = gpc_get_int( 'resolution', 0 );
if( $t_resolution != 0 ) {
	$t_issue['resolution'] = array( 'id' => $t_resolution );
}

$t_status = gpc_get_int( 'status', 0 );
if( $t_status != 0 ) {
	$t_issue['status'] = array( 'id' => $t_status );
}

$t_steps_to_reproduce = gpc_get_string( 'steps_to_reproduce', null );
if( $t_steps_to_reproduce !== null ) {
	$t_issue['steps_to_reproduce'] = $t_steps_to_reproduce;
}

$t_additional_info = gpc_get_string( 'additional_info', null );
if( $t_additional_info !== null ) {
	$t_issue['additional_information'] = $t_additional_info;
}

$t_due_date = gpc_get_string( 'due_date', null );
if( $t_due_date !== null ) {
	$t_issue['due_date'] = $t_due_date;
}

# Validate the custom fields before adding the bug.
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
$t_custom_fields = array();
foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );

	# Produce an error if the field is required but wasn't posted
	if( gpc_isset_custom_field( $t_id, $t_def['type'] ) ) {
		$t_custom_fields[] = array(
			'field' => array( 'id' => $t_id ),
			'value' => gpc_get_custom_field( 'custom_field_' . $t_id, $t_def['type'], null )
		);
	}
}

if( !empty( $t_custom_fields ) ) {
	$t_issue['custom_fields'] = $t_custom_fields;
}

$t_data = array(
	'payload' => array( 'issue' => $t_issue ),
);

if( $f_master_bug_id > 0 ) {
	$t_data['options'] = array( 'clone_info' => $t_clone_info );
}

$t_command = new IssueAddCommand( $t_data );
$t_result = $t_command->execute();
$t_issue_id = (int)$t_result['issue_id'];

form_security_purge( 'bug_report' );

layout_page_header_begin();

if( $f_report_stay ) {
	$t_fields = array(
		'category_id', 'severity', 'reproducibility', 'profile_id', 'platform',
		'os', 'os_build', 'target_version', 'build', 'view_state', 'due_date'
	);

	$t_issue = bug_get( $t_issue_id );

	$t_data = array();
	foreach( $t_fields as $t_field ) {
		$t_data[$t_field] = $t_issue->$t_field;
	}

	$t_data['product_version'] = $t_issue->version;
	$t_data['report_stay'] = 1;

	$t_report_more_bugs_url = string_get_bug_report_url() . '?' . http_build_query( $t_data );

	html_meta_redirect( $t_report_more_bugs_url );
} else {
	html_meta_redirect( string_get_bug_view_url( $t_issue_id ) );
}

layout_page_header_end();

layout_page_begin( 'bug_report_page.php' );

$t_buttons = array(
	array( string_get_bug_view_url( $t_issue_id ), sprintf( lang_get( 'view_submitted_bug_link' ), $t_issue_id ) ),
	array( 'view_all_bug_page.php', lang_get( 'view_bugs_link' ) ),
);

if( $f_report_stay ) {
	$t_buttons[] = array( $t_report_more_bugs_url, lang_get( 'report_more_bugs' ) );
}

html_operation_confirmation( $t_buttons, '', CONFIRMATION_TYPE_SUCCESS );

layout_page_end();
