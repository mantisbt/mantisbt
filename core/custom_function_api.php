<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: custom_function_api.php,v 1.15 2005-01-28 21:58:16 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'prepare_api.php' );

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
		echo '- ', string_get_bug_view_link( $p_issue_id ), ': <b>[', $t_bug->category, ']</b> ', string_attribute( $t_bug->summary );

		if ( $t_bug->handler_id != 0 ) {
			echo ' (', prepare_user_name( $t_bug->handler_id ), ')';
		}

		echo '<br />';
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

	# --------------------
	# returns an array of the column names to be displayed.
	# The column names to use are those of the field names in the bug table.
	# In addition, you can use the following:
	# - "selection" for selection checkboxes.
	# - "edit" for icon to open the edit page.
	# - "custom_xxxx" were xxxx is the name of the custom field that is valid for the
	#   current project.  In case of "All Projects, the field will be empty where it is
	#   not applicable.
	function custom_function_default_get_columns_to_view( $p_print = false ) {
		$t_columns = array();
		$t_columns[] = 'selection';

		if ( !$p_print ) {
			$t_columns[] = 'edit';
		}

		$t_columns[] = 'priority';
		$t_columns[] = 'bug_id';

		$t_enable_sponsorship = config_get( 'enable_sponsorship' );
		if ( ON == $t_enable_sponsorship ) {
			$t_columns[] = 'sponsorship';
		}

		$t_columns[] = 'bugnotes_count';

		$t_show_attachments = config_get( 'show_attachment_indicator' );
		if ( ON == $t_show_attachments ) {
			$t_columns[] = 'attachment';
		}

		$t_columns[] = 'category';
		$t_columns[] = 'severity';
		$t_columns[] = 'status';
		$t_columns[] = 'last_updated';
		$t_columns[] = 'summary';

		return $t_columns;
	}

	# --------------------
	# Print the title of a column given its name.
	# $p_column: custom_xxx for custom field xxx, or otherwise field name as in bug table.
	function custom_function_default_print_column_title( $p_column, $p_print = false ) {
		global $t_sort, $t_dir;

		if ( strpos( $p_column, 'custom_' ) === 0 ) {
			$t_custom_field = substr( $p_column, 7 );

			echo '<td>';

			$t_field_id = custom_field_get_id_from_name( $t_custom_field );
			if ( $t_field_id === false ) {
				echo '@', $t_custom_field, '@';
			} else {
				$t_def = custom_field_get_definition( $t_field_id );
				$t_custom_field = lang_get_defaulted( $t_def['name'] );

				print_view_bug_sort_link( $t_custom_field, $p_column, $t_sort, $t_dir, $p_print );
				print_sort_icon( $t_dir, $t_sort, $p_column );
			}

			echo '</td>';
		} else {
			$t_function = 'print_column_title_' . $p_column;
			$t_function( $t_sort, $t_dir, $p_print );
		}
	}

	# --------------------
	# Print the value of the custom field (if the field is applicable to the project of
	# the specified issue and the current user has read access to it.
	# see custom_function_default_print_column_title() for rules about column names.
	# $p_column: name of field to show in the column.
	# $p_row: the row from the bug table that belongs to the issue that we should print the values for.
	function custom_function_default_print_column_value( $p_column, $p_issue_row, $p_print = false ) {
		if ( strpos( $p_column, 'custom_' ) === 0 ) {
			echo '<td>';
			$t_custom_field = substr( $p_column, 7 );

			$t_field_id = custom_field_get_id_from_name( $t_custom_field );
			if ( $t_field_id === false ) {
				echo '@', $t_custom_field, '@';
			} else {
				$t_issue_id = $p_issue_row['id'];
				$t_project_id = $p_issue_row['project_id'];

				if ( custom_field_is_linked( $t_field_id, $t_project_id ) ) {
					$t_def = custom_field_get_definition( $t_field_id );
					print_custom_field_value( $t_def, $t_field_id, $t_issue_id );
				} else {
					// field is not linked to project
					echo '&nbsp;';
				}
			}
			echo '</td>';
		} else {
			$t_function = 'print_column_' . $p_column;
			$t_function( $p_issue_row, $p_print );
		}
	}

	# --------------------
	# Construct an enumeration for all versions for the current project.
	# The enumeration will be empty if current project is ALL PROJECTS.
	# Enumerations format is: "abc|lmn|xyz"
	# To use this in a custom field type "=versions_enum" in the possible values field.
	function custom_function_default_versions_enum() {
		$t_versions = version_get_all_rows( helper_get_current_project() );

		$t_enum = array();
		foreach( $t_versions as $t_version ) {
			$t_enum[] = $t_version['version'];
		}

		$t_possible_values = implode( '|', $t_enum );

		return $t_possible_values;
	}

	# --------------------
	# Construct an enumeration for all categories for the current project.
	# The enumeration will be empty if current project is ALL PROJECTS.
	# Enumerations format is: "abc|lmn|xyz"
	# To use this in a custom field type "=categories_enum" in the possible values field.
	function custom_function_default_categories_enum() {
		$t_categories = category_get_all_rows( helper_get_current_project() );

		$t_enum = array();
		foreach( $t_categories as $t_category ) {
			$t_enum[] = $t_category['category'];
		}

		$t_possible_values = implode( '|', $t_enum );

		return $t_possible_values;
	}
?>
