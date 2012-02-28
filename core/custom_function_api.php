<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage CustomFunctionAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires prepare_api
 */
require_once( 'prepare_api.php' );
/**
 * requires columns_api
 */
require_once( 'columns_api.php' );

# ## Custom Function API ###
# Checks the provided bug and determines whether it should be included in the changelog
# or not.
# returns true: to include, false: to exclude.
function custom_function_default_changelog_include_issue( $p_issue_id ) {
	$t_issue = bug_get( $p_issue_id );

	return( ( $t_issue->resolution >= config_get( 'bug_resolution_fixed_threshold' ) &&
		$t_issue->resolution < config_get( 'bug_resolution_not_fixed_threshold' ) &&
		$t_issue->status >= config_get( 'bug_resolved_status_threshold' ) ) );
}

# Prints one entry in the changelog.
function custom_function_default_changelog_print_issue( $p_issue_id, $p_issue_level = 0 ) {
	static $t_status;

	$t_bug = bug_get( $p_issue_id );

	if( $t_bug->category_id ) {
		$t_category_name = category_get_name( $t_bug->category_id );
	} else {
		$t_category_name = '';
	}

	$t_category = is_blank( $t_category_name ) ? '' : '<b>[' . string_display_line( $t_category_name ) . ']</b> ';
	echo utf8_str_pad( '', $p_issue_level * 6, '&#160;' ), '- ', string_get_bug_view_link( $p_issue_id ), ': ', $t_category, string_display_line_links( $t_bug->summary );

	if( $t_bug->handler_id != 0 ) {
		echo ' (', prepare_user_name( $t_bug->handler_id ), ')';
	}

	if( !isset( $t_status[$t_bug->status] ) ) {
		$t_status[$t_bug->status] = get_enum_element( 'status', $t_bug->status, auth_get_current_user_id(), $t_bug->project_id );
	}
	echo ' - ', $t_status[$t_bug->status], '.<br />';
}

# Checks the provided bug and determines whether it should be included in the roadmap or not.
# returns true: to include, false: to exclude.
function custom_function_default_roadmap_include_issue( $p_issue_id ) {
	return true;
}

# Prints one entry in the roadmap.
function custom_function_default_roadmap_print_issue( $p_issue_id, $p_issue_level = 0 ) {
	static $t_status;

	$t_bug = bug_get( $p_issue_id );

	if( bug_is_resolved( $p_issue_id ) ) {
		$t_strike_start = '<strike>';
		$t_strike_end = '</strike>';
	} else {
		$t_strike_start = $t_strike_end = '';
	}

	if( $t_bug->category_id ) {
		$t_category_name = category_get_name( $t_bug->category_id );
	} else {
		$t_category_name = '';
	}

	$t_category = is_blank( $t_category_name ) ? '' : '<b>[' . string_display_line( $t_category_name ) . ']</b> ';

	echo utf8_str_pad( '', $p_issue_level * 6, '&#160;' ), '- ', $t_strike_start, string_get_bug_view_link( $p_issue_id ), ': ', $t_category, string_display_line_links( $t_bug->summary );

	if( $t_bug->handler_id != 0 ) {
		echo ' (', prepare_user_name( $t_bug->handler_id ), ')';
	}

	if( !isset( $t_status[$t_bug->status] ) ) {
		$t_status[$t_bug->status] = get_enum_element( 'status', $t_bug->status, auth_get_current_user_id(), $t_bug->project_id );
	}
	echo ' - ', $t_status[$t_bug->status], $t_strike_end, '.<br />';
}

# format the bug summary.
function custom_function_default_format_issue_summary( $p_issue_id, $p_context = 0 ) {
	switch( $p_context ) {
		case SUMMARY_CAPTION:
			$t_string = bug_format_id( $p_issue_id ) . ': ' . string_attribute( bug_get_field( $p_issue_id, 'summary' ) );
			break;
		case SUMMARY_FIELD:
			$t_string = bug_format_id( $p_issue_id ) . ': ' . string_display_line_links( bug_get_field( $p_issue_id, 'summary' ) );
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

# Register a checkin in source control by adding a history entry and a note
# This can be overriden to do extra work.
# The issue status/resolution would only be set if the issue is fixed, and hence $p_fixed is passed as true.
function custom_function_default_checkin( $p_issue_id, $p_comment, $p_file, $p_new_version, $p_fixed ) {
	if( bug_exists( $p_issue_id ) ) {
		history_log_event_special( $p_issue_id, CHECKIN, $p_file, $p_new_version );
		$t_private = false;
		if( VS_PRIVATE == config_get( 'source_control_notes_view_status' ) ) {
			$t_private = true;
		}
		bugnote_add( $p_issue_id, $p_comment, 0, $t_private );

		$t_status = config_get( 'source_control_set_status_to' );
		if(( OFF != $t_status ) && $p_fixed ) {
			bug_set_field( $p_issue_id, 'status', $t_status );
			bug_set_field( $p_issue_id, 'resolution', config_get( 'source_control_set_resolution_to' ) );
		}
	}
}

# Hook to validate field issue data before updating
# Verify that the proper fields are set with the appropriate values before proceeding
# to change the status.
# In case of invalid data, this function should call trigger_error()
# p_issue_id is the issue number that can be used to get the existing state
# p_new_issue_data is an object (BugData) with the appropriate fields updated
function custom_function_default_issue_update_validate( $p_issue_id, $p_new_issue_data, $p_bugnote_text ) {
}

# Hook to notify after an issue has been updated.
# In case of errors, this function should call trigger_error()
# p_issue_id is the issue number that can be used to get the existing state
function custom_function_default_issue_update_notify( $p_issue_id ) {
}

# Hook to validate field settings before creating an issue
# Verify that the proper fields are set before proceeding to create an issue
# In case of errors, this function should call trigger_error()
# p_new_issue_data is an object (BugData) with the appropriate fields updated
function custom_function_default_issue_create_validate( $p_new_issue_data ) {
}

# Hook to notify after aa issue has been created.
# In case of errors, this function should call trigger_error()
# p_issue_id is the issue number that can be used to get the existing state
function custom_function_default_issue_create_notify( $p_issue_id ) {
}

# Hook to validate field settings before deleting an issue.
# Verify that the issue can be deleted before the actual deletion.
# In the case that the issue should not be deleted, this function should
# call trigger_error().
# p_issue_id is the issue number that can be used to get the existing state
function custom_function_default_issue_delete_validate( $p_issue_id ) {
}

# Hook to notify after an issue has been deleted.
# p_issue_data is the issue data (BugData) that reflects the last status of the
# issue before it was deleted.
function custom_function_default_issue_delete_notify( $p_issue_data ) {
}

# Hook for authentication
# can MantisBT update the password
function custom_function_default_auth_can_change_password() {
	$t_can_change = array(
		PLAIN,
		CRYPT,
		CRYPT_FULL_SALT,
		MD5,
	);
	if( in_array( config_get( 'login_method' ), $t_can_change ) ) {
		return true;
	} else {
		return false;
	}
}

# returns an array of the column names to be displayed.
# The column names to use are those of the field names in the bug table.
# In addition, you can use the following:
# - "selection" for selection checkboxes.
# - "edit" for icon to open the edit page.
# - "custom_xxxx" were xxxx is the name of the custom field that is valid for the
#   current project.  In case of "All Projects, the field will be empty where it is
#   not applicable.
# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
# $p_user_id: The user id or null for current logged in user.
function custom_function_default_get_columns_to_view( $p_columns_target = COLUMNS_TARGET_VIEW_PAGE, $p_user_id = null ) {
	$t_project_id = helper_get_current_project();

	if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE ) {
		$t_columns = config_get( 'csv_columns', '', $p_user_id, $t_project_id );
	} else if( $p_columns_target == COLUMNS_TARGET_EXCEL_PAGE ) {
		$t_columns = config_get( 'excel_columns', '', $p_user_id, $t_project_id );
	} else if( $p_columns_target == COLUMNS_TARGET_VIEW_PAGE ) {
		$t_columns = config_get( 'view_issues_page_columns', '', $p_user_id, $t_project_id );
	} else {
		$t_columns = config_get( 'print_issues_page_columns', '', $p_user_id, $t_project_id );
	}

	$t_columns = columns_remove_invalid( $t_columns, columns_get_all( $t_project_id ) );

	return $t_columns;
}

# Print the title of a column given its name.
# $p_column: custom_xxx for custom field xxx, or otherwise field name as in bug table.
# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
function custom_function_default_print_column_title( $p_column, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_sort, $t_dir;

	$t_custom_field = column_get_custom_field_name( $p_column );
	if( $t_custom_field !== null ) {
		if( COLUMNS_TARGET_CSV_PAGE != $p_columns_target ) {
			echo '<td>';
		}

		$t_field_id = custom_field_get_id_from_name( $t_custom_field );
		if( $t_field_id === false ) {
			echo '@', $t_custom_field, '@';
		} else {
			$t_def = custom_field_get_definition( $t_field_id );
			$t_custom_field = lang_get_defaulted( $t_def['name'] );

			if( COLUMNS_TARGET_CSV_PAGE != $p_columns_target ) {
				print_view_bug_sort_link( $t_custom_field, $p_column, $t_sort, $t_dir, $p_columns_target );
				print_sort_icon( $t_dir, $t_sort, $p_column );
			} else {
				echo $t_custom_field;
			}
		}

		if( COLUMNS_TARGET_CSV_PAGE != $p_columns_target ) {
			echo '</td>';
		}
	} else {
		$t_plugin_columns = columns_get_plugin_columns();

		$t_function = 'print_column_title_' . $p_column;
		if( function_exists( $t_function ) ) {
			$t_function( $t_sort, $t_dir, $p_columns_target );

		} else if ( isset( $t_plugin_columns[ $p_column ] ) ) {
			$t_column_object = $t_plugin_columns[ $p_column ];
			print_column_title_plugin( $p_column, $t_column_object, $t_sort, $t_dir, $p_columns_target );

		} else {
			echo '<td>';
			print_view_bug_sort_link( column_get_title( $p_column ), $p_column, $t_sort, $t_dir, $p_columns_target );
			print_sort_icon( $t_dir, $t_sort, $p_column );
			echo '</td>';
		}
	}
}

# Print the value of the custom field (if the field is applicable to the project of
# the specified issue and the current user has read access to it.
# see custom_function_default_print_column_title() for rules about column names.
# $p_column: name of field to show in the column.
# $p_row: the row from the bug table that belongs to the issue that we should print the values for.
# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
function custom_function_default_print_column_value( $p_column, $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( COLUMNS_TARGET_CSV_PAGE == $p_columns_target ) {
		$t_column_start = '';
		$t_column_end = '';
		$t_column_empty = '';
	} else {
		$t_column_start = '<td>';
		$t_column_end = '</td>';
		$t_column_empty = '&#160;';
	}

	$t_custom_field = column_get_custom_field_name( $p_column );
	if( $t_custom_field !== null ) {
		echo $t_column_start;

		$t_field_id = custom_field_get_id_from_name( $t_custom_field );
		if( $t_field_id === false ) {
			echo '@', $t_custom_field, '@';
		} else {
			$t_issue_id = $p_bug->id;
			$t_project_id = $p_bug->project_id;

			if( custom_field_is_linked( $t_field_id, $t_project_id ) ) {
				$t_def = custom_field_get_definition( $t_field_id );
				print_custom_field_value( $t_def, $t_field_id, $t_issue_id );
			} else {
				// field is not linked to project
				echo $t_column_empty;
			}
		}
		echo $t_column_end;
	} else {
		$t_plugin_columns = columns_get_plugin_columns();

		if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
			$t_function = 'print_column_' . $p_column;
		} else {
			$t_function = 'csv_format_' . $p_column;
		}

		if( function_exists( $t_function ) ) {
			if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
				$t_function( $p_bug, $p_columns_target );
			} else {
				$t_function( $p_bug );
			}

		} else if ( isset( $t_plugin_columns[ $p_column ] ) ) {
			$t_column_object = $t_plugin_columns[ $p_column ];
			print_column_plugin( $t_column_object, $p_bug, $p_columns_target );

		} else {
			if( isset( $p_bug->$p_column ) ) {
				echo $t_column_start . string_display_line( $p_bug->$p_column ) . $t_column_end;
			} else {
				echo $t_column_start . '@' . $p_column . '@' . $t_column_end;
			}
		}
	}
}

# Construct an enumeration for all versions for the current project.
# The enumeration will be empty if current project is ALL PROJECTS.
# Enumerations format is: "abc|lmn|xyz"
# To use this in a custom field type "=versions" in the possible values field.
function custom_function_default_enum_versions() {
	$t_versions = version_get_all_rows( helper_get_current_project() );

	$t_enum = array();
	foreach( $t_versions as $t_version ) {
		$t_enum[] = $t_version['version'];
	}

	$t_possible_values = implode( '|', $t_enum );

	return $t_possible_values;
}

# Construct an enumeration for released versions for the current project.
# The enumeration will be empty if current project is ALL PROJECTS.
# Enumerations format is: "abc|lmn|xyz"
# To use this in a custom field type "=released_versions" in the possible values field.
function custom_function_default_enum_released_versions() {
	$t_versions = version_get_all_rows( helper_get_current_project() );

	$t_enum = array();
	foreach( $t_versions as $t_version ) {
		if( $t_version['released'] == 1 ) {
			$t_enum[] = $t_version['version'];
		}
	}

	$t_possible_values = implode( '|', $t_enum );

	return $t_possible_values;
}

# Construct an enumeration for released versions for the current project.
# The enumeration will be empty if current project is ALL PROJECTS.
# Enumerations format is: "abc|lmn|xyz"
# To use this in a custom field type "=future_versions" in the possible values field.
function custom_function_default_enum_future_versions() {
	$t_versions = version_get_all_rows( helper_get_current_project() );

	$t_enum = array();
	foreach( $t_versions as $t_version ) {
		if( $t_version['released'] == 0 ) {
			$t_enum[] = $t_version['version'];
		}
	}

	$t_possible_values = implode( '|', $t_enum );

	return $t_possible_values;
}

# Construct an enumeration for all categories for the current project.
# The enumeration will be empty if current project is ALL PROJECTS.
# Enumerations format is: "abc|lmn|xyz"
# To use this in a custom field type "=categories" in the possible values field.
function custom_function_default_enum_categories() {
	$t_categories = category_get_all_rows( helper_get_current_project() );

	$t_enum = array();
	foreach( $t_categories as $t_category ) {
		$t_enum[] = $t_category['category'];
	}

	$t_possible_values = implode( '|', $t_enum );

	return $t_possible_values;
}

# This function prints the custom buttons on the current view page based on specified bug id
# and the context.  The printing of the buttons will typically call html_button() from
# html_api.php.  For each button, this function needs to generate the enclosing '<td>' and '</td>'.
function custom_function_default_print_bug_view_page_custom_buttons( $p_bug_id ) {
}
