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
 * Custom Function API
 *
 * @package CoreAPI
 * @subpackage CustomFunctionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

/**
 * Custom Function API
 * Checks the provided bug and determines whether it should be included in the changelog or not.
 * returns true: to include, false: to exclude.
 *
 * @param integer $p_issue_id Issue id.
 * @return boolean
 */
function custom_function_default_changelog_include_issue( $p_issue_id ) {
	$t_issue = bug_get( $p_issue_id );

	return( ( $t_issue->resolution >= config_get( 'bug_resolution_fixed_threshold' ) &&
		$t_issue->resolution < config_get( 'bug_resolution_not_fixed_threshold' ) &&
		$t_issue->status >= config_get( 'bug_resolved_status_threshold' ) ) );
}

/**
 * Prints one entry in the changelog.
 *
 * @param integer $p_issue_id    Issue id.
 * @param integer $p_issue_level Issue level.
 * @return void
 */
function custom_function_default_changelog_print_issue( $p_issue_id, $p_issue_level = 0 ) {
	static $s_status;

	$t_bug = bug_get( $p_issue_id );
	$t_current_user = auth_get_current_user_id();

	if( $t_bug->category_id ) {
		$t_category_name = category_get_name( $t_bug->category_id );
	} else {
		$t_category_name = '';
	}

	$t_category = is_blank( $t_category_name ) ? '' : '<strong>[' . string_display_line( $t_category_name ) . ']</strong> ';

	if( !isset( $s_status[$t_bug->status] ) ) {
		$s_status[$t_bug->status] = get_enum_element( 'status', $t_bug->status, $t_current_user, $t_bug->project_id );
	}

	# choose color based on status
	$t_status_css = html_get_status_css_fg( $t_bug->status, $t_current_user, $t_bug->project_id );
	$t_status_title = string_attribute( get_enum_element( 'status', bug_get_field( $t_bug->id, 'status' ), $t_bug->project_id ) );;

	echo utf8_str_pad( '', $p_issue_level * 36, '&#160;' );
	echo '<i class="fa fa-square fa-status-box ' . $t_status_css . '" title="' . $t_status_title . '"></i> ';
	echo string_get_bug_view_link( $p_issue_id, false );
	echo ': <span class="label label-light">', $t_category, '</span> ' , string_display_line_links( $t_bug->summary );
	if( $t_bug->handler_id > 0
			&& ON == config_get( 'show_assigned_names', null, $t_current_user, $t_bug->project_id )
			&& access_can_see_handler_for_bug( $t_bug ) ) {
		echo ' (', prepare_user_name( $t_bug->handler_id ), ')';
	}
	echo '<div class="space-2"></div>';
}

/**
 * Checks the provided bug and determines whether it should be included in the roadmap or not.
 * returns true: to include, false: to exclude.
 *
 * @param integer $p_issue_id Issue id.
 * @return boolean
 */
function custom_function_default_roadmap_include_issue( $p_issue_id ) {
	return true;
}

/**
 * Prints one entry in the roadmap.
 *
 * @param integer $p_issue_id    Issue id.
 * @param integer $p_issue_level Issue level.
 * @return void
 */
function custom_function_default_roadmap_print_issue( $p_issue_id, $p_issue_level = 0 ) {
	static $s_status;

	$t_bug = bug_get( $p_issue_id );
	$t_current_user = auth_get_current_user_id();

	if( bug_is_resolved( $p_issue_id ) ) {
		$t_strike_start = '<s>';
		$t_strike_end = '</s>';
	} else {
		$t_strike_start = $t_strike_end = '';
	}

	if( $t_bug->category_id ) {
		$t_category_name = category_get_name( $t_bug->category_id );
	} else {
		$t_category_name = '';
	}

	$t_category = is_blank( $t_category_name ) ? '' : '<strong>[' . string_display_line( $t_category_name ) . ']</strong> ';

	if( !isset( $s_status[$t_bug->status] ) ) {
		$s_status[$t_bug->status] = get_enum_element( 'status', $t_bug->status, $t_current_user, $t_bug->project_id );
	}

	# choose color based on status
	$t_status_css = html_get_status_css_fg( $t_bug->status, $t_current_user, $t_bug->project_id );
	$t_status_title = string_attribute( get_enum_element( 'status', bug_get_field( $t_bug->id, 'status' ), $t_bug->project_id ) );;

	echo utf8_str_pad( '', $p_issue_level * 36, '&#160;' );
	echo '<i class="fa fa-square fa-status-box ' . $t_status_css . '" title="' . $t_status_title . '"></i> ';
	echo string_get_bug_view_link( $p_issue_id, false );
	echo ': <span class="label label-light">', $t_category, '</span> ', $t_strike_start, string_display_line_links( $t_bug->summary ), $t_strike_end;
	if( $t_bug->handler_id > 0
			&& ON == config_get( 'show_assigned_names', null, $t_current_user, $t_bug->project_id )
			&& access_can_see_handler_for_bug( $t_bug ) ) {
		echo ' (', prepare_user_name( $t_bug->handler_id ), ')';
	}
	echo '<div class="space-2"></div>';
}

/**
 * format the bug summary.
 *
 * @param integer $p_issue_id Issue id.
 * @param integer $p_context  Context SUMMARY_CAPTION | SUMMARY_FIELD | SUMMARY_EMAIL.
 * @return string
 */
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

/**
 * Hook to validate field issue data before updating
 * Verify that the proper fields are set with the appropriate values before proceeding
 * to change the status.
 * In case of invalid data, this function should call trigger_error()
 *
 * @param integer $p_issue_id       Issue number that can be used to get the existing state.
 * @param BugData $p_new_issue_data Is an object (BugData) with the appropriate fields updated.
 * @param string  $p_bugnote_text   Bugnote text.
 * @return void
 */
function custom_function_default_issue_update_validate( $p_issue_id, BugData $p_new_issue_data, $p_bugnote_text ) {
}

/**
 * Hook to notify after an issue has been updated.
 * In case of errors, this function should call trigger_error()
 *
 * @param integer $p_issue_id The issue number that can be used to get the existing state.
 * @return void
 */
function custom_function_default_issue_update_notify( $p_issue_id ) {
}

/**
 * Hook to validate field settings before creating an issue
 * Verify that the proper fields are set before proceeding to create an issue
 * In case of errors, this function should call trigger_error()
 *
 * @param BugData $p_new_issue_data Object (BugData) with the appropriate fields updated.
 * @return void
 */
function custom_function_default_issue_create_validate( BugData $p_new_issue_data ) {
}

/**
 * Hook to notify after aa issue has been created.
 * In case of errors, this function should call trigger_error()
 *
 * @param integer $p_issue_id The issue number that can be used to get the existing state.
 * @return void
 */
function custom_function_default_issue_create_notify( $p_issue_id ) {
}

/**
 * Hook to validate field settings before deleting an issue.
 * Verify that the issue can be deleted before the actual deletion.
 * In the case that the issue should not be deleted, this function should call trigger_error().
 *
 * @param integer $p_issue_id The issue number that can be used to get the existing state.
 * @return void
 */
function custom_function_default_issue_delete_validate( $p_issue_id ) {
}

/**
 * Hook to notify after an issue has been deleted.
 *
 * Note: this actually gets called after the deletion is logged,
 * but before the actual delete so the bug data can be accessed.
 *
 * @param integer $p_issue_id The issue number that can be used to get the existing state before it is deleted.
 * @return void
 */
function custom_function_default_issue_delete_notify( $p_issue_id ) {
}

/**
 * Hook for authentication
 * can MantisBT update the password
 * @return boolean
 */
function custom_function_default_auth_can_change_password() {
	$t_can_change = array(
		PLAIN,
		CRYPT,
		CRYPT_FULL_SALT,
		MD5,
	);

	return in_array( config_get_global( 'login_method' ), $t_can_change );
}

/**
 * returns an array of the column names to be displayed.
 * The column names to use are those of the field names in the bug table.
 * In addition, you can use the following:
 * - "selection" for selection checkboxes.
 * - "edit" for icon to open the edit page.
 * - "custom_xxxx" were xxxx is the name of the custom field that is valid for the
 *   current project.  In case of "All Projects, the field will be empty where it is
 *   not applicable.
 *
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @param integer $p_user_id        The user id or null for current logged in user.
 * @return array
 */
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

/**
 * Print the title of a column given its name.
 *
 * @global type $t_sort             (deprecated) main sort column in use from filter
 * @global type $t_dir              (deprecated) main sort dir in use from filter
 * @param string  $p_column         Custom_xxx for custom field xxx, or otherwise field name as in bug table.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @param array $p_sort_properties  Array of filter sortin gproeprties, in the format returned from filter_get_visible_sort_properties_array()
 * @return void
 */
function custom_function_default_print_column_title( $p_column, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE, array $p_sort_properties = null ) {
	global $t_sort, $t_dir;

	# if no sort properties are provided, resort to deprecated golbal vars, to keep compatibility
	if( null === $p_sort_properties ) {
		$t_main_sort_column = $t_sort;
		$t_main_sort_dir = $t_dir;
	} else {
		# we use only the first ordered column
		$t_main_sort_column = reset( $p_sort_properties[FILTER_PROPERTY_SORT_FIELD_NAME] );
		$t_main_sort_dir = reset( $p_sort_properties[FILTER_PROPERTY_SORT_DIRECTION] );
	}

	$t_custom_field = column_get_custom_field_name( $p_column );
	if( $t_custom_field !== null ) {
		if( COLUMNS_TARGET_CSV_PAGE != $p_columns_target ) {
			echo '<th class="column-custom-' . $t_custom_field . '">';
		}

		$t_field_id = custom_field_get_id_from_name( $t_custom_field );
		if( $t_field_id === false ) {
			echo '@', $t_custom_field, '@';
		} else {
			$t_def = custom_field_get_definition( $t_field_id );
			$t_custom_field = lang_get_defaulted( $t_def['name'] );

			if( COLUMNS_TARGET_CSV_PAGE != $p_columns_target ) {
				print_view_bug_sort_link( $t_custom_field, $p_column, $t_main_sort_column, $t_main_sort_dir, $p_columns_target );
				if( $p_column == $t_main_sort_column ) {
					print_sort_icon( $t_main_sort_dir, $t_main_sort_column, $p_column );
				}
			} else {
				echo $t_custom_field;
			}
		}

		if( COLUMNS_TARGET_CSV_PAGE != $p_columns_target ) {
			echo '</th>';
		}
	} else {
		$t_plugin_columns = columns_get_plugin_columns();

		$t_function = 'print_column_title_' . $p_column;
		if( function_exists( $t_function ) ) {
			$t_function( $t_main_sort_column, $t_main_sort_dir, $p_columns_target );

		} else if( isset( $t_plugin_columns[$p_column] ) ) {
			$t_column_object = $t_plugin_columns[$p_column];
			print_column_title_plugin( $p_column, $t_column_object, $t_main_sort_column, $t_main_sort_dir, $p_columns_target );

		} else {
			echo '<th>';
			print_view_bug_sort_link( column_get_title( $p_column ), $p_column, $t_main_sort_column, $t_main_sort_dir, $p_columns_target );
			print_sort_icon( $t_main_sort_dir, $t_main_sort_column, $p_column );
			echo '</th>';
		}
	}
}

/**
 * Print the value of the custom field (if the field is applicable to the project of
 * the specified issue and the current user has read access to it.
 * see custom_function_default_print_column_title() for rules about column names.
 * @param string  $p_column         Name of field to show in the column.
 * @param BugData $p_bug            Bug object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 */
function custom_function_default_print_column_value( $p_column, BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( COLUMNS_TARGET_CSV_PAGE == $p_columns_target ) {
		$t_column_start = '';
		$t_column_end = '';
		$t_column_empty = '';
	} else {
		$t_column_start = '<td class="column-%s">';
		$t_column_end = '</td>';
		$t_column_empty = '&#160;';
	}

	$t_custom_field = column_get_custom_field_name( $p_column );
	if( $t_custom_field !== null ) {
		printf( $t_column_start, 'custom-' . $t_custom_field );

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
				# field is not linked to project
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

		} else if( isset( $t_plugin_columns[$p_column] ) ) {
			$t_column_object = $t_plugin_columns[$p_column];
			print_column_plugin( $t_column_object, $p_bug, $p_columns_target );

		} else {
			printf( $t_column_start, $p_column );
			if( isset( $p_bug->$p_column ) ) {
				echo string_display_line( $p_bug->$p_column ) . $t_column_end;
			} else {
				echo '@' . $p_column . '@' . $t_column_end;
			}
		}
	}
}

/**
 * Construct an enumeration for all versions for the current project.
 * The enumeration will be empty if current project is ALL PROJECTS.
 * Enumerations format is: "abc|lmn|xyz"
 * To use this in a custom field type "=versions" in the possible values field.
 * @return string
 */
function custom_function_default_enum_versions() {
	$t_versions = version_get_all_rows( helper_get_current_project() );

	$t_enum = array();
	foreach( $t_versions as $t_version ) {
		$t_enum[] = $t_version['version'];
	}

	$t_possible_values = implode( '|', $t_enum );

	return $t_possible_values;
}

/**
 * Construct an enumeration for released versions for the current project.
 * The enumeration will be empty if current project is ALL PROJECTS.
 * Enumerations format is: "abc|lmn|xyz"
 * To use this in a custom field type "=released_versions" in the possible values field.
 * @return string
 */
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

/**
 * Construct an enumeration for future versions for the current project.
 * The enumeration will be empty if current project is ALL PROJECTS.
 * Enumerations format is: "abc|lmn|xyz"
 * To use this in a custom field type "=future_versions" in the possible values field.
 * @return string
 */
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

/**
 * Construct an enumeration for all categories for the current project.
 * The enumeration will be empty if current project is ALL PROJECTS.
 * Enumerations format is: "abc|lmn|xyz"
 * To use this in a custom field type "=categories" in the possible values field.
 * @return string
 */
function custom_function_default_enum_categories() {
	$t_categories = category_get_all_rows( helper_get_current_project() );

	$t_enum = array();
	foreach( $t_categories as $t_category ) {
		$t_enum[] = $t_category['name'];
	}

	$t_possible_values = implode( '|', $t_enum );

	return $t_possible_values;
}

/**
 * This function prints the custom buttons on the current view page based on specified bug id
 * and the context.  The printing of the buttons will typically call html_button() from
 * html_api.php.  For each button, this function needs to generate the enclosing '<td>' and '</td>'.
 *
 * @param integer $p_bug_id A bug identifier.
 * @return void
 */
function custom_function_default_print_bug_view_page_custom_buttons( $p_bug_id ) {
}
