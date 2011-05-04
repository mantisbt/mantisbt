<?php

global $g_json_file_tmp_name;
$g_json_file_tmp_name = '';

access_ensure_project_level( config_get('report_bug_threshold' ) , $project_id, $user_id);

$t_bug_data = new BugData;
$t_bug_data->build				= json_get_string( 'build', '' );
$t_bug_data->platform				= json_get_string( 'platform', '' );
$t_bug_data->os					= json_get_string( 'os', '' );
$t_bug_data->os_build				= json_get_string( 'os_build', '' );
$t_bug_data->version			= json_get_string( 'product_version', '' );
$t_bug_data->profile_id			= json_get_int( 'profile_id', 0 );
$t_bug_data->handler_id			= json_get_int( 'handler_id', 0 );
$t_bug_data->view_state			= json_get_int( 'view_state', config_get( 'default_bug_view_status' ) );

$t_bug_data->category_id			= json_get_int( 'category_id', 0 );
$t_bug_data->reproducibility		= json_get_int( 'reproducibility', config_get( 'default_bug_reproducibility' ) );
$t_bug_data->severity				= json_get_int( 'severity', config_get( 'default_bug_severity' ) );
$t_bug_data->priority				= json_get_int( 'priority', config_get( 'default_bug_priority' ) );
$t_bug_data->projection				= json_get_int( 'projection', config_get( 'default_bug_projection' ) );
$t_bug_data->eta					= json_get_int( 'eta', config_get( 'default_bug_eta' ) );
$t_bug_data->resolution				= config_get( 'default_bug_resolution' );
$t_bug_data->status					= config_get( 'bug_submit_status' );
$t_bug_data->summary				= json_get_string( 'summary' );
$t_bug_data->description			= json_get_string( 'description' );
$t_bug_data->steps_to_reproduce	= json_get_string( 'steps_to_reproduce', config_get( 'default_bug_steps_to_reproduce' ) );
$t_bug_data->additional_information	= json_get_string( 'additional_info', config_get ( 'default_bug_additional_info' ) );
$t_bug_data->due_date 				= json_get_string( 'due_date', '');

if ( is_blank ( $t_bug_data->due_date ) ) {
	$t_bug_data->due_date = date_get_null();
} else {
	$t_bug_data->due_date = $t_bug_data->due_date;
}

$f_file					= json_get_file( 'file', null ); /** @todo (thraxisp) Note that this always returns a structure */

$f_report_stay			= json_get_bool( 'report_stay', false );
$t_bug_data->project_id			= json_get_int( 'project_id' );

$t_bug_data->reporter_id		= auth_get_current_user_id();

$t_bug_data->summary			= trim( $t_bug_data->summary );

if ( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id ) ) {
	$t_bug_data->target_version = json_get_string( 'target_version', '' );
}

# if a profile was selected then let's use that information
if ( 0 != $t_bug_data->profile_id ) {
	if ( profile_is_global( $t_bug_data->profile_id ) ) {
		$row = user_get_profile_row( ALL_USERS, $t_bug_data->profile_id );
	} else {
		$row = user_get_profile_row( $t_bug_data->reporter_id, $t_bug_data->profile_id );
	}

	if ( is_blank( $t_bug_data->platform ) ) {
		$t_bug_data->platform = $row['platform'];
	}
	if ( is_blank( $t_bug_data->os ) ) {
		$t_bug_data->os = $row['os'];
	}
	if ( is_blank( $t_bug_data->os_build ) ) {
		$t_bug_data->os_build = $row['os_build'];
	}
}

helper_call_custom_function( 'issue_create_validate', array( $t_bug_data ) );

# Validate the custom fields before adding the bug.
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );

	# Produce an error if the field is required but wasn't posted
	if ( !json_isset_custom_field( $t_id, $t_def['type'] ) &&
		( $t_def['require_report'] ||
			$t_def['type'] == CUSTOM_FIELD_TYPE_ENUM ||
			$t_def['type'] == CUSTOM_FIELD_TYPE_LIST ||
			$t_def['type'] == CUSTOM_FIELD_TYPE_MULTILIST ||
			$t_def['type'] == CUSTOM_FIELD_TYPE_RADIO ) ) {
		error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}
	if ( !custom_field_validate( $t_id, json_get_custom_field( $t_id, $t_def['name'], $t_def['type'], NULL ) ) ) {
		error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
	}
}

# Allow plugins to pre-process bug data
$t_bug_data = event_signal( 'EVENT_REPORT_BUG_DATA', $t_bug_data );

# Create the bug
$t_bug_id = $t_bug_data->create();

# Mark the added issue as visited so that it appears on the last visited list.
last_visited_issue( $t_bug_id );

# Handle the file upload
if ( !is_blank( $f_file['tmp_name'] ) && ( 0 < $f_file['size'] ) ) {
	file_add( $t_bug_id, $f_file, 'bug' );
}

# Handle custom field submission
foreach( $t_related_custom_field_ids as $t_id ) {
	# Do not set custom field value if user has no write access.
	if( !custom_field_has_write_access( $t_id, $t_bug_id ) ) {
		continue;
	}

	$t_def = custom_field_get_definition( $t_id );
	if( !custom_field_set_value( $t_id, $t_bug_id, json_get_custom_field( $t_id, $t_def['name'], $t_def['type'], '' ), false ) ) {
		error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
	}
}

$f_master_bug_id = json_get_int( 'm_id', 0 );
$f_rel_type = json_get_int( 'rel_type', -1 );

if ( $f_master_bug_id > 0 ) {
	# it's a child generation... let's create the relationship and add some lines in the history

	# update master bug last updated
	bug_update_date( $f_master_bug_id );

	# Add log line to record the cloning action
	history_log_event_special( $t_bug_id, BUG_CREATED_FROM, '', $f_master_bug_id );
	history_log_event_special( $f_master_bug_id, BUG_CLONED_TO, '', $t_bug_id );

	if ( $f_rel_type >= 0 ) {
		# Add the relationship
		relationship_add( $t_bug_id, $f_master_bug_id, $f_rel_type );

		# Add log line to the history (both issues)
		history_log_event_special( $f_master_bug_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $f_rel_type ), $t_bug_id );
		history_log_event_special( $t_bug_id, BUG_ADD_RELATIONSHIP, $f_rel_type, $f_master_bug_id );

		# Send the email notification
		email_relationship_added( $f_master_bug_id, $t_bug_id, relationship_get_complementary_type( $f_rel_type ) );
	}
}

helper_call_custom_function( 'issue_create_notify', array( $t_bug_id ) );

# Allow plugins to post-process bug data with the new bug ID
event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );

email_new_bug( $t_bug_id );

form_security_purge( 'bug_report' );


/*
if ( !$f_report_stay ) {
	html_meta_redirect( 'view_all_bug_page.php' );
}
 */

$t_output['result'] =  lang_get( 'operation_successful' ) ;
$t_output['bug_id'] =  $t_bug_id;

echo json_encode($t_output);

json_exit();
