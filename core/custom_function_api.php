<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: custom_function_api.php,v 1.9 2004-10-08 17:23:35 thraxisp Exp $
	# --------------------------------------------------------

	### Custom Function API ###

	# --------------------
	# Checks the provided bug and determines whether it should be included in the changelog 
	# or not.
	# returns true: to include, false: to exclude.
	function custom_function_default_changelog_include_issue( $p_issue_id ) {
		$t_issue = bug_get( $p_issue_id );

		return ( ( $t_issue->duplicate_id == 0 ) && ( $t_issue->resolution == FIXED ) &&
			( $t_issue->status >= config_get( 'bug_resolved_status_threshold' ) ) );
	}


	# --------------------
	# Prints one entry in the changelog.
	function custom_function_default_changelog_print_issue( $p_issue_id ) {
		$t_bug = bug_get( $p_issue_id );
		echo '- ', string_get_bug_view_link( $p_issue_id ), ': <b>[', $t_bug->category, ']</b> ', string_display( $t_bug->summary ), ' (', user_get_name( $t_bug->handler_id ), ')<br />';
	}
	
	# --------------------
	# format the bug summary.
	function custom_function_default_format_issue_summary( $p_issue_id, $p_context=0 ) {
		switch ( $p_context ) {
			case SUMMARY_CAPTION:
				$t_string = bug_format_id( $p_issue_id ) . ': ' . string_attribute( bug_get_field( $p_issue_id, 'summary' ) );
				break;
			case SUMMARY_FIELD:
				$t_string = bug_format_id( $p_issue_id ) . ': ' . string_attribute( bug_get_field( $p_issue_id, 'summary' ) );
				break;
			case SUMMARY_EMAIL:
				$t_string = bug_format_id( $p_issue_id ) . ': ' . string_attribute( bug_get_field( $p_issue_id, 'summary' ) );
				break;
			default:
				$t_string = string_attribute( bug_get_field( $p_issue_id, 'summary' ) );
				break;
		}
		return $t_string;
	}
	
	# --------------------
	# Register a checkin in source control by adding a history entry and a note
	# This can be overriden to do extra work like changing the issue status to 
	# config_get( 'bug_readonly_status_threshold' );
	function custom_function_default_checkin( $p_issue_id, $p_comment, $p_file, $p_new_version ) {
		if ( bug_exists( $p_issue_id ) ) {
			history_log_event_special( $p_issue_id, CHECKIN, $p_file, $p_new_version );
			bugnote_add( $p_issue_id, $p_comment, VS_PRIVATE == config_get( 'source_control_notes_view_status' ) );

			$t_status = config_get( 'source_control_set_status_to' );
			if ( OFF != $t_status ) {
				bug_set_field( $p_issue_id, 'status', $t_status );
			}
		}
	}

	# --------------------
	# Hook to validate field issue data before updating
	# Verify that the proper fields are set with the appropriate values before proceeding 
	# to change the status.
	# In case of invalid data, this function should call trigger_error()
	# p_issue_id is the issue number that can be used to get the existing state
	# p_new_issue_data is an object (BugData) with the appropriate fields updated
	function custom_function_default_issue_update_validate( $p_issue_id, $p_new_issue_data, $p_bugnote_text ) {
	}

	# --------------------
	# Hook to notify after an issue has been updated. 
	# In case of errors, this function should call trigger_error()
	# p_issue_id is the issue number that can be used to get the existing state
	function custom_function_default_issue_update_notify( $p_issue_id ) {
	}

	# --------------------
	# Hook to validate field settings before creating an issue
	# Verify that the proper fields are set before proceeding to create an issue
	# In case of errors, this function should call trigger_error()
	# p_new_issue_data is an object (BugData) with the appropriate fields updated
	function custom_function_default_issue_create_validate( $p_new_issue_data ) {
	}

	# --------------------
	# Hook to notify after aa issue has been created.
	# In case of errors, this function should call trigger_error()
	# p_issue_id is the issue number that can be used to get the existing state
	function custom_function_default_issue_create_notify( $p_issue_id ) {
	}

	# --------------------
	# Hook to validate field settings before deleting an issue.
	# Verify that the issue can be deleted before the actual deletion.
	# In the case that the issue should not be deleted, this function should
	# call trigger_error().
	# p_issue_id is the issue number that can be used to get the existing state
	function custom_function_default_issue_delete_validate( $p_issue_id ) {
	}

	# --------------------
	# Hook to notify after an issue has been deleted.
	# p_issue_data is the issue data (BugData) that reflects the last status of the
	# issue before it was deleted.
	function custom_function_default_issue_delete_notify( $p_issue_data ) {
	}

	# --------------------
	# Hook for authentication
	# can Mantis update the password
	function custom_function_default_auth_can_change_password( ) {
		$t_can_change = array( PLAIN, CRYPT, CRYPT_FULL_SALT, MD5 );
		if ( in_array( config_get( 'login_method' ), $t_can_change ) ) {
			return true;
		}else{
			return false;
		}
	}

?>