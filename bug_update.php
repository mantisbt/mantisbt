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

/**
 * Retrieves a version from form data and ensures it is valid.
 *
 * @param BugData $p_bug  Reference issue
 * @param string $p_field Field name (used for GPC var and BugData property)
 *
 * @return string|null
 */
function get_valid_version( BugData $p_bug, $p_field ) {
	$t_reference_version = $p_bug->$p_field;
	$t_version = gpc_get_string( $p_field, $t_reference_version );
	if( !is_blank( $t_version )
		&& $t_version != $t_reference_version
		&& version_get_id( $t_version, $p_bug->project_id ) === false
	) {
		error_parameters( $t_version );
		trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
	}
	return $t_version;
}

form_security_validate( 'bug_update' );

$f_bug_id = gpc_get_int( 'bug_id' );
$t_existing_bug = bug_get( $f_bug_id, true );
$f_update_type = gpc_get_string( 'action_type', BUG_UPDATE_TYPE_NORMAL );

$t_current_user_id = auth_get_current_user_id();

if( helper_get_current_project() != $t_existing_bug->project_id ) {
	$g_project_override = $t_existing_bug->project_id;
}

$t_updated_bug = clone $t_existing_bug;

$t_updated_bug->additional_information = gpc_get_string( 'additional_information', $t_existing_bug->additional_information );
$t_updated_bug->build = gpc_get_string( 'build', $t_existing_bug->build );
$t_updated_bug->category_id = gpc_get_int( 'category_id', $t_existing_bug->category_id );
$t_updated_bug->description = gpc_get_string( 'description', $t_existing_bug->description );
$t_due_date = gpc_get_string( 'due_date', null );
if( $t_due_date !== null ) {
	if( is_blank( $t_due_date ) ) {
		$t_updated_bug->due_date = 1;
	} else {
		$t_updated_bug->due_date = strtotime( $t_due_date );
	}
}
$t_updated_bug->duplicate_id = gpc_get_int( 'duplicate_id', 0 );
$t_updated_bug->eta = gpc_get_int( 'eta', $t_existing_bug->eta );
$t_updated_bug->handler_id = gpc_get_int( 'handler_id', $t_existing_bug->handler_id );
$t_updated_bug->last_updated = gpc_get_string( 'last_updated' );
$t_updated_bug->os = gpc_get_string( 'os', $t_existing_bug->os );
$t_updated_bug->os_build = gpc_get_string( 'os_build', $t_existing_bug->os_build );
$t_updated_bug->platform = gpc_get_string( 'platform', $t_existing_bug->platform );
$t_updated_bug->priority = gpc_get_int( 'priority', $t_existing_bug->priority );
$t_updated_bug->projection = gpc_get_int( 'projection', $t_existing_bug->projection );

$t_reporter_id = gpc_get_int( 'reporter_id', $t_existing_bug->reporter_id );
# Only validate the reporter if different from the recorded one; this avoids
# blocking the update when changing another field and the original reporter's
# account no longer exists.
if( $t_reporter_id != $t_existing_bug->reporter_id ) {
	user_ensure_exists( $t_reporter_id );
	$t_report_bug_threshold = config_get( 'report_bug_threshold',
		null,
		$t_reporter_id,
		$t_existing_bug->project_id
	);
	$t_can_report = access_has_project_level(
		$t_report_bug_threshold,
		$t_existing_bug->project_id,
		$t_reporter_id
	);
	if( !$t_can_report ) {
		trigger_error( ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS, ERROR );
	}
}
$t_updated_bug->reporter_id = $t_reporter_id;

$t_updated_bug->reproducibility = gpc_get_int( 'reproducibility', $t_existing_bug->reproducibility );
$t_updated_bug->resolution = gpc_get_int( 'resolution', $t_existing_bug->resolution );
$t_updated_bug->severity = gpc_get_int( 'severity', $t_existing_bug->severity );
$t_updated_bug->status = gpc_get_int( 'status', $t_existing_bug->status );
$t_updated_bug->steps_to_reproduce = gpc_get_string( 'steps_to_reproduce', $t_existing_bug->steps_to_reproduce );
$t_updated_bug->summary = gpc_get_string( 'summary', $t_existing_bug->summary );

$t_updated_bug->fixed_in_version = get_valid_version( $t_existing_bug, 'fixed_in_version' );
$t_updated_bug->target_version = get_valid_version( $t_existing_bug, 'target_version' );
$t_updated_bug->version = get_valid_version( $t_existing_bug, 'version' );

$t_updated_bug->view_state = gpc_get_int( 'view_state', $t_existing_bug->view_state );

$t_bug_note = new BugNoteData();
$t_bug_note->note = gpc_get_string( 'bugnote_text', '' );
$t_bug_note->view_state = gpc_get_bool( 'private' ) ? VS_PRIVATE : VS_PUBLIC;
$t_bug_note->time_tracking = gpc_get_string( 'time_tracking', '0:00' );

if( $t_existing_bug->last_updated != $t_updated_bug->last_updated ) {
	trigger_error( ERROR_BUG_CONFLICTING_EDIT, ERROR );
}

# Determine whether the new status will reopen, resolve or close the issue.
# Note that multiple resolved or closed states can exist and thus we need to
# look at a range of statuses when performing this check.
$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
$t_closed_status = config_get( 'bug_closed_status_threshold' );
$t_reopen_resolution = config_get( 'bug_reopen_resolution' );
$t_resolve_issue = false;
$t_close_issue = false;
$t_reopen_issue = false;
if( $t_existing_bug->status < $t_resolved_status &&
	$t_updated_bug->status >= $t_resolved_status &&
	$t_updated_bug->status < $t_closed_status
) {
	$t_resolve_issue = true;
} else if( $t_existing_bug->status < $t_closed_status &&
		   $t_updated_bug->status >= $t_closed_status
) {
	$t_close_issue = true;
} else if( $t_existing_bug->status >= $t_resolved_status &&
		   $t_updated_bug->status <= config_get( 'bug_reopen_status' )
) {
	$t_reopen_issue = true;
}

$t_reporter_closing =
	( $f_update_type == BUG_UPDATE_TYPE_CLOSE ) &&
	bug_is_user_reporter( $f_bug_id, $t_current_user_id ) &&
	access_can_close_bug( $t_existing_bug, $t_current_user_id );

$t_reporter_reopening =
	( ( $f_update_type == BUG_UPDATE_TYPE_REOPEN ) || $t_reopen_issue ) &&
	bug_is_user_reporter( $f_bug_id, $t_current_user_id ) &&
	access_can_reopen_bug( $t_existing_bug, $t_current_user_id );

if ( !$t_reporter_reopening && !$t_reporter_closing ) {
	switch( $f_update_type ) {
		case BUG_UPDATE_TYPE_ASSIGN:
			$t_threshold = 'update_bug_assign_threshold';
			$t_check_readonly = true;
			break;
		case BUG_UPDATE_TYPE_CLOSE:
		case BUG_UPDATE_TYPE_REOPEN:
			$t_threshold = 'update_bug_status_threshold';
			$t_check_readonly = false;
			break;
		case BUG_UPDATE_TYPE_CHANGE_STATUS:
			$t_threshold = 'update_bug_status_threshold';
			$t_check_readonly = true;
			break;
		case BUG_UPDATE_TYPE_NORMAL:
		default:
			$t_threshold = 'update_bug_threshold';
			$t_check_readonly = true;
			break;
	}
	access_ensure_bug_level( config_get( $t_threshold ), $f_bug_id );

	if( $t_check_readonly ) {
		# Check if the bug is in a read-only state and whether the current user has
		# permission to update read-only bugs.
		if( bug_is_readonly( $f_bug_id ) ) {
			error_parameters( $f_bug_id );
			trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
		}
	}
}

# If resolving or closing, ensure that all dependent issues have been resolved
# unless config option enables closing parents with open children.
if( ( $t_resolve_issue || $t_close_issue ) &&
	!relationship_can_resolve_bug( $f_bug_id ) &&
	OFF == config_get( 'allow_parent_of_unresolved_to_close' ) ) {
	trigger_error( ERROR_BUG_RESOLVE_DEPENDANTS_BLOCKING, ERROR );
}

# Validate any change to the status of the issue.
if( $t_existing_bug->status != $t_updated_bug->status ) {
	if( !bug_check_workflow( $t_existing_bug->status, $t_updated_bug->status ) ) {
		error_parameters( lang_get( 'status' ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
	}
	if( !access_has_bug_level( access_get_status_threshold( $t_updated_bug->status, $t_updated_bug->project_id ), $f_bug_id ) ) {
		# The reporter may be allowed to close or reopen the issue regardless.
		$t_can_bypass_status_access_thresholds = false;
		if( $t_close_issue &&
			$t_existing_bug->status >= $t_resolved_status &&
			$t_existing_bug->reporter_id == $t_current_user_id &&
			config_get( 'allow_reporter_close' )
		) {
			$t_can_bypass_status_access_thresholds = true;
		} else if( $t_reopen_issue &&
				   $t_existing_bug->status >= $t_resolved_status &&
				   $t_existing_bug->status <= $t_closed_status &&
				   $t_existing_bug->reporter_id == $t_current_user_id &&
				   config_get( 'allow_reporter_reopen' ) ) {
			$t_can_bypass_status_access_thresholds = true;
		}
		if( !$t_can_bypass_status_access_thresholds ) {
			trigger_error( ERROR_ACCESS_DENIED, ERROR );
		}
	}
	if( $t_reopen_issue ) {
		# for everyone allowed to reopen an issue, set the reopen resolution
		$t_updated_bug->resolution = $t_reopen_resolution;
	}
}

# Validate any change to the handler of an issue.
# The new handler is checked at project level.
if( $t_existing_bug->handler_id != $t_updated_bug->handler_id ) {
	$t_issue_is_sponsored = config_get( 'enable_sponsorship' )
		&& sponsorship_get_amount( sponsorship_get_all_ids( $f_bug_id ) ) > 0;
	access_ensure_bug_level( config_get( 'update_bug_assign_threshold' ), $f_bug_id );
	if( $t_issue_is_sponsored && !access_has_project_level( config_get( 'handle_sponsored_bugs_threshold' ),  $t_updated_bug->project_id, $t_updated_bug->handler_id ) ) {
		trigger_error( ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW, ERROR );
	}
	if( $t_updated_bug->handler_id != NO_USER ) {
		if( !access_has_project_level( config_get( 'handle_bug_threshold' ),  $t_updated_bug->project_id, $t_updated_bug->handler_id ) ) {
			trigger_error( ERROR_HANDLER_ACCESS_TOO_LOW, ERROR );
		}
		if( $t_issue_is_sponsored && !access_has_bug_level( config_get( 'assign_sponsored_bugs_threshold' ), $f_bug_id ) ) {
			trigger_error( ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW, ERROR );
		}
	}
}

# Check whether the category has been undefined when it's compulsory.
if( $t_existing_bug->category_id != $t_updated_bug->category_id ) {
	if( $t_updated_bug->category_id == 0 &&
		!config_get( 'allow_no_category' )
	) {
		error_parameters( lang_get( 'category' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# Make sure the category belongs to the given project's hierarchy
	category_ensure_exists_in_project(
		$t_updated_bug->category_id,
		$t_updated_bug->project_id
	);
}

# Don't allow changing the Resolution in the following cases:
# - new status < RESOLVED and resolution denoting completion (>= fixed_threshold)
# - new status >= RESOLVED and resolution < fixed_threshold
# - resolution = REOPENED and current status < RESOLVED and new status >= RESOLVED
# Refer to #15653 for further details (particularly note 37180)
$t_resolution_fixed_threshold = config_get( 'bug_resolution_fixed_threshold' );
if( $t_existing_bug->resolution != $t_updated_bug->resolution && (
	   (  $t_updated_bug->resolution >= $t_resolution_fixed_threshold
	   && $t_updated_bug->resolution != $t_reopen_resolution
	   && $t_updated_bug->status < $t_resolved_status
	   )
	|| (  $t_updated_bug->resolution == $t_reopen_resolution
	   && (  $t_existing_bug->status < $t_resolved_status
		  || $t_updated_bug->status >= $t_resolved_status
	   ) )
	|| (  $t_updated_bug->resolution < $t_resolution_fixed_threshold
	   && $t_updated_bug->status >= $t_resolved_status
	   )
) ) {
	error_parameters(
		get_enum_element( 'resolution', $t_updated_bug->resolution ),
		get_enum_element( 'status', $t_updated_bug->status )
	);
	trigger_error( ERROR_INVALID_RESOLUTION, ERROR );
}

# Ensure that the user has permission to change the target version of the issue.
if( $t_existing_bug->target_version !== $t_updated_bug->target_version ) {
	access_ensure_bug_level( config_get( 'roadmap_update_threshold' ), $f_bug_id );
}

# Ensure that the user has permission to change the view status of the issue.
if( $t_existing_bug->view_state != $t_updated_bug->view_state ) {
	access_ensure_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id );
}

# Determine the custom field "require check" to use for validating
# whether fields can be undefined during this bug update.
if( $t_close_issue ) {
	$t_cf_require_check = 'require_closed';
} else if( $t_resolve_issue ) {
	$t_cf_require_check = 'require_resolved';
} else {
	$t_cf_require_check = 'require_update';
}

$t_related_custom_field_ids = custom_field_get_linked_ids( $t_existing_bug->project_id );
$t_custom_fields_to_set = array();
foreach ( $t_related_custom_field_ids as $t_cf_id ) {
	$t_cf_def = custom_field_get_definition( $t_cf_id );

	# If the custom field is not set and is required, then complain!
	if( !gpc_isset_custom_field( $t_cf_id, $t_cf_def['type'] ) ) {
		if( $t_cf_def[$t_cf_require_check] &&
			custom_field_is_present( $t_cf_id ) &&
			custom_field_has_write_access( $t_cf_id, $f_bug_id ) ) {
			# A value for the custom field was expected however
			# no value was given by the user.
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_cf_id, 'name' ) ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
	}

	# Otherwise, if not present then skip it.
	if ( !custom_field_is_present( $t_cf_id ) ) {
		continue;
	}

	if( !custom_field_has_write_access( $t_cf_id, $f_bug_id ) ) {
		trigger_error( ERROR_ACCESS_DENIED, ERROR );
	}

	$t_new_custom_field_value = gpc_get_custom_field( 'custom_field_' . $t_cf_id, $t_cf_def['type'], '' );
	$t_old_custom_field_value = custom_field_get_value( $t_cf_id, $f_bug_id );

	# Validate the value of the field against current validation rules.
	# This may cause an error if validation rules have recently been
	# modified such that old values that were once OK are now considered
	# invalid.
	if( !custom_field_validate( $t_cf_id, $t_new_custom_field_value ) ) {
		error_parameters( lang_get_defaulted( custom_field_get_field( $t_cf_id, 'name' ) ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
	}

	# Remember the new custom field values so we can set them when updating
	# the bug (done after all data passed to this update page has been
	# validated).
	$t_custom_fields_to_set[] = array( 'id' => $t_cf_id, 'value' => $t_new_custom_field_value );
}

# Perform validation of the duplicate ID of the bug.
if( $t_updated_bug->duplicate_id != 0 ) {
	if( $t_updated_bug->duplicate_id == $f_bug_id ) {
		trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
	}

	bug_ensure_exists( $t_updated_bug->duplicate_id );

	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $t_updated_bug->duplicate_id ) ) {
		trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
	}
}

# Validate the new bug note (if any is provided).
if( $t_bug_note->note ||
	( config_get( 'time_tracking_enabled' ) &&
	  helper_duration_to_minutes( $t_bug_note->time_tracking ) > 0 )
) {
	access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id );
	if( !$t_bug_note->note &&
		!config_get( 'time_tracking_without_note' )
	) {
		error_parameters( lang_get( 'bugnote' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}
	if( $t_bug_note->view_state != config_get( 'default_bugnote_view_status' ) ) {
		access_ensure_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id );
	}
}

# Handle the reassign on feedback feature. Note that this feature generally
# won't work very well with custom workflows as it makes a lot of assumptions
# that may not be true. It assumes you don't have any statuses in the workflow
# between 'bug_submit_status' and 'bug_feedback_status'. It assumes you only
# have one feedback, assigned and submitted status.
if( $t_bug_note->note &&
	config_get( 'reassign_on_feedback' ) &&
	$t_existing_bug->status == config_get( 'bug_feedback_status' ) &&
	$t_updated_bug->status == $t_existing_bug->status &&
	$t_updated_bug->handler_id != $t_current_user_id &&
	$t_updated_bug->reporter_id == $t_current_user_id
) {
	if( $t_updated_bug->handler_id != NO_USER ) {
		$t_updated_bug->status = config_get( 'bug_assigned_status' );
	} else {
		$t_updated_bug->status = config_get( 'bug_submit_status' );
	}
}

# Handle automatic assignment of issues.
$t_updated_bug->status = bug_get_status_for_assign( $t_existing_bug->handler_id, $t_updated_bug->handler_id, $t_existing_bug->status, $t_updated_bug->status );

# Allow a custom function to validate the proposed bug updates. Note that
# custom functions are being deprecated in MantisBT. You should migrate to
# the new plugin system instead.
helper_call_custom_function( 'issue_update_validate', array( $f_bug_id, $t_updated_bug, $t_bug_note->note ) );

# Allow plugins to validate/modify the update prior to it being committed.
$t_updated_bug = event_signal( 'EVENT_UPDATE_BUG_DATA', $t_updated_bug, $t_existing_bug );

# Commit the bug updates to the database.
$t_text_field_update_required = ( $t_existing_bug->description != $t_updated_bug->description ) ||
								( $t_existing_bug->additional_information != $t_updated_bug->additional_information ) ||
								( $t_existing_bug->steps_to_reproduce != $t_updated_bug->steps_to_reproduce );
$t_updated_bug->update( $t_text_field_update_required, true );

# Update custom field values.
foreach ( $t_custom_fields_to_set as $t_custom_field_to_set ) {
	custom_field_set_value( $t_custom_field_to_set['id'], $f_bug_id, $t_custom_field_to_set['value'] );
}

# Add a bug note if there is one.
if( $t_bug_note->note || helper_duration_to_minutes( $t_bug_note->time_tracking ) > 0 ) {
	$t_bugnote_id = bugnote_add( $f_bug_id, $t_bug_note->note, $t_bug_note->time_tracking, $t_bug_note->view_state == VS_PRIVATE, 0, '', null, false );
	bugnote_process_mentions( $f_bug_id, $t_bugnote_id, $t_bug_note->note );
}

# Add a duplicate relationship if requested.
if( $t_updated_bug->duplicate_id != 0 ) {
	relationship_upsert( $f_bug_id, $t_updated_bug->duplicate_id, BUG_DUPLICATE, /* email_for_source */ false );

	if( user_exists( $t_existing_bug->reporter_id ) ) {
		bug_monitor( $t_updated_bug->duplicate_id, $t_existing_bug->reporter_id );
	}
	if( user_exists( $t_existing_bug->handler_id ) ) {
		bug_monitor( $t_updated_bug->duplicate_id, $t_existing_bug->handler_id );
	}

	bug_monitor_copy( $f_bug_id, $t_updated_bug->duplicate_id );
}

event_signal( 'EVENT_UPDATE_BUG', array( $t_existing_bug, $t_updated_bug ) );

# Allow a custom function to respond to the modifications made to the bug. Note
# that custom functions are being deprecated in MantisBT. You should migrate to
# the new plugin system instead.
helper_call_custom_function( 'issue_update_notify', array( $f_bug_id ) );

# Send a notification of changes via email.
if( $t_resolve_issue ) {
	email_resolved( $f_bug_id );
	email_relationship_child_resolved( $f_bug_id );
} else if( $t_close_issue ) {
	email_close( $f_bug_id );
	email_relationship_child_closed( $f_bug_id );
} else if( $t_reopen_issue ) {
	email_bug_reopened( $f_bug_id );
} else if( $t_existing_bug->handler_id != $t_updated_bug->handler_id ) {
	email_owner_changed( $f_bug_id, $t_existing_bug->handler_id, $t_updated_bug->handler_id );
} else if( $t_existing_bug->status != $t_updated_bug->status ) {
	$t_new_status_label = MantisEnum::getLabel( config_get( 'status_enum_string' ), $t_updated_bug->status );
	$t_new_status_label = str_replace( ' ', '_', $t_new_status_label );
	email_bug_status_changed( $f_bug_id, $t_new_status_label );
} else {
	email_bug_updated( $f_bug_id );
}

form_security_purge( 'bug_update' );

print_successful_redirect_to_bug( $f_bug_id );
