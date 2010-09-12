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
 * Excel API
 * @package CoreAPI
 * @subpackage ExcelAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * A method that returns the header for an Excel Xml file.
 *
 * @param $p_worksheet_title  The worksheet title.
 * @returns the header Xml.
 */
function excel_get_header( $p_worksheet_title ) {
	$p_worksheet_title = preg_replace( '/[\/:*?"<>|]/', '', $p_worksheet_title );
	return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><?mso-application progid=\"Excel.Sheet\"?>
 <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n<Worksheet ss:Name=\"" . urlencode( $p_worksheet_title ) . "\">\n<Table>\n<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
}

/**
 * A method that returns the footer for an Excel Xml file.
 * @returns the footer xml.
 */
function excel_get_footer() {
	return "</Table>\n</Worksheet></Workbook>\n";
}

/**
 * Generates a cell XML for a column title.
 * @returns The cell xml.
 */
function excel_format_column_title( $p_column_title ) {
	return '<Cell><Data ss:Type="String">' . $p_column_title . '</Data></Cell>';
}

/**
 * Generates the xml for the start of an Excel row.
 * @returns The Row tag.
 */
function excel_get_start_row() {
	return '<Row>';
}

/**
 * Generates the xml for the end of an Excel row.
 * @returns The Row end tag.
 */
function excel_get_end_row() {
	return '</Row>';
}

/**
 * Gets an Xml Row that contains all column titles.
 * @returns The xml row.
 */
function excel_get_titles_row() {
	$t_columns = excel_get_columns();
	$t_ret = '<Row>';

	foreach( $t_columns as $t_column ) {
		$t_custom_field = column_get_custom_field_name( $t_column );
		if( $t_custom_field !== null ) {
			$t_ret .= excel_format_column_title( lang_get_defaulted( $t_custom_field ) );
		} else {
			$t_column_title = column_get_title( $t_column );
			$t_ret .= excel_format_column_title( $t_column_title );
		}
	}

	$t_ret .= '</Row>';

	return $t_ret;
}

/**
 * Gets the download file name for the Excel export.  If 'All Projects' selected, default to <username>,
 * otherwise default to <projectname>.
* @returns file name without extension
*/
function excel_get_default_filename() {
	$t_current_project_id = helper_get_current_project();

	if( ALL_PROJECTS == $t_current_project_id ) {
		$t_filename = user_get_name( auth_get_current_user_id() );
	} else {
		$t_filename = project_get_field( $t_current_project_id, 'name' );
	}

	return $t_filename;
}

/**
 * Escapes the specified column value and includes it in a Cell Xml.
 * @param $p_value The value
 * @returns The Cell Xml.
 */
function excel_prepare_string( $p_value ) {
	$t_type = is_numeric( $p_value ) ? 'Number' : 'String';

	$t_value = str_replace( array ( '&', "\n", '<', '>'), array ( '&amp;', '&#10;', '&lt;', '&gt;' ),  $p_value );
	$t_ret = "<Cell><Data ss:Type=\"$t_type\">" . $t_value . "</Data></Cell>\n";

	return $t_ret;
}

/**
 * Gets the columns to be included in the Excel Xml export.
 * @returns column names.
 */
function excel_get_columns() {
	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE );
	return $t_columns;
}

#
# Formatting Functions
#
# Names for formatting functions are excel_format_*, where * corresponds to the
# field name as return get excel_get_columns() and by the filter api.
#
/**
 * Gets the formatted bug id value.
 * @param $p_bug_id  The bug id to be formatted.
 * @returns The bug id prefixed with 0s.
 */
function excel_format_id( $p_bug_id ) {
	return excel_prepare_string( bug_format_id( $p_bug_id ) );
}

/**
 * Gets the formatted project id value.
 * @param $p_project_id The project id.
 * @returns The project name.
 */
function excel_format_project_id( $p_project_id ) {
	return excel_prepare_string( project_get_name( $p_project_id ) );
}

/**
 * Gets the formatted reporter id value.
 * @param $p_reporter_id The reporter id.
 * @returns The reporter user name.
 */
function excel_format_reporter_id( $p_reporter_id ) {
	return excel_prepare_string( user_get_name( $p_reporter_id ) );
}

/**
 * Gets the formatted number of bug notes.
 * @param $p_bugnotes_count  The number of bug notes.
 * @returns The number of bug notes.
 */
function excel_format_bugnotes_count( $p_bugnotes_count ) {
	return excel_prepare_string( $p_bugnotes_count );
}

/**
 * Gets the formatted handler id.
 * @param $p_handler_id The handler id.
 * @returns The handler user name or empty string.
 */
function excel_format_handler_id( $p_handler_id ) {
	if( $p_handler_id > 0 ) {
		return excel_prepare_string( user_get_name( $p_handler_id ) );
	} else {
		return excel_prepare_string( '' );
	}
}

/**
 * Gets the formatted priority.
 * @param $p_priority priority id.
 * @returns the priority text.
 */
function excel_format_priority( $p_priority ) {
	return excel_prepare_string( get_enum_element( 'priority', $p_priority ) );
}

/**
 * Gets the formatted severity.
 * @param $p_severity severity id.
 * @returns the severity text.
 */
function excel_format_severity( $p_severity ) {
	return excel_prepare_string( get_enum_element( 'severity', $p_severity ) );
}

/**
 * Gets the formatted reproducibility.
 * @param $p_reproducibility reproducibility id.
 * @returns the reproducibility text.
 */
function excel_format_reproducibility( $p_reproducibility ) {
	return excel_prepare_string( get_enum_element( 'reproducibility', $p_reproducibility ) );
}

/**
 * Gets the formatted view state,
 * @param $p_view_state The view state (e.g. public vs. private)
 * @returns The view state
 */
function excel_format_view_state( $p_view_state ) {
	return excel_prepare_string( get_enum_element( 'view_state', $p_view_state ) );
}

/**
 * Gets the formatted projection.
 * @param $p_projection projection id.
 * @returns the projection text.
 */
function excel_format_projection( $p_projection ) {
	return excel_prepare_string( get_enum_element( 'projection', $p_projection ) );
}

/**
 * Gets the formatted eta.
 * @param $p_eta eta id.
 * @returns the eta text.
 */
function excel_format_eta( $p_eta ) {
	return excel_prepare_string( get_enum_element( 'eta', $p_eta ) );
}

/**
 * Gets the status field.
 * @param $p_status The status field.
 * @returns the formatted status.
 */
function excel_format_status( $p_status ) {
	return excel_prepare_string( get_enum_element( 'status', $p_status ) );
}

/**
 * Gets the resolution field.
 * @param $p_resolution The resolution field.
 * @returns the formatted resolution.
 */
function excel_format_resolution( $p_resolution ) {
	return excel_prepare_string( get_enum_element( 'resolution', $p_resolution ) );
}

/**
 * Gets the formatted version.
 * @param $p_version The product version
 * @returns the product version.
 */
function excel_format_version( $p_version ) {
	return excel_prepare_string( $p_version );
}

/**
 * Gets the formatted fixed in version.
 * @param $p_fixed_in_version The product fixed in version
 * @returns the fixed in version.
 */
function excel_format_fixed_in_version( $p_fixed_in_version ) {
	return excel_prepare_string( $p_fixed_in_version );
}

/**
 * Gets the formatted target version.
 * @param $p_target_version The target version
 * @returns the target version.
 */
function excel_format_target_version( $p_target_version ) {
	return excel_prepare_string( $p_target_version );
}

/**
 * Gets the formatted category.
 * @param $p_category The category
 * @returns the category.
 */
function excel_format_category_id( $p_category_id ) {
	return excel_prepare_string( category_full_name( $p_category_id, false ) );
}

/**
 * Gets the formatted operating system.
 * @param $p_os The operating system
 * @returns the operating system.
 */
function excel_format_os( $p_os ) {
	return excel_prepare_string( $p_os );
}

/**
 * Gets the formatted operating system build (version).
 * @param $p_os The operating system build (version)
 * @returns the operating system build (version)
 */
function excel_format_os_build( $p_os_build ) {
	return excel_prepare_string( $p_os_build );
}

/**
 * Gets the formatted product build,
 * @param $p_build The product build
 * @returns the product build.
 */
function excel_format_build( $p_build ) {
	return excel_prepare_string( $p_build );
}

/**
 * Gets the formatted platform,
 * @param $p_platform The platform
 * @returns the platform.
 */
function excel_format_platform( $p_platform ) {
	return excel_prepare_string( $p_platform );
}

/**
 * Gets the formatted date submitted.
 * @param $p_date_submitted The date submitted
 * @returns the date submitted in short date format.
 */
function excel_format_date_submitted( $p_date_submitted ) {
	return excel_prepare_string( date( config_get( 'short_date_format' ), $p_date_submitted ) );
}

/**
 * Gets the formatted date last updated.
 * @param $p_last_updated The date last updated.
 * @returns the date last updated in short date format.
 */
function excel_format_last_updated( $p_last_updated ) {
	return excel_prepare_string( date( config_get( 'short_date_format' ), $p_last_updated ) );
}

/**
 * Gets the summary field.
 * @param $p_summary The summary.
 * @returns the formatted summary.
 */
function excel_format_summary( $p_summary ) {
	return excel_prepare_string( $p_summary );
}

/**
 * Gets the formatted selection.
 * @param $p_selection The selection value
 * @returns An formatted empty string.
 */
function excel_format_selection( $p_param ) {
	return excel_prepare_string( '' );
}

/**
 * Gets the formatted description field.
 * @param $p_description The description.
 * @returns The formatted description (multi-line).
 */
function excel_format_description( $p_description ) {
	return excel_prepare_string( $p_description );
}

/**
 * Gets the formatted 'steps to reproduce' field.
 * @param $p_steps_to_reproduce The steps to reproduce.
 * @returns The formatted steps to reproduce (multi-line).
 */
function excel_format_steps_to_reproduce( $p_steps_to_reproduce ) {
	return excel_prepare_string( $p_steps_to_reproduce );
}

/**
 * Gets the formatted 'additional information' field.
 * @param $p_additional_information The additional information field.
 * @returns The formatted additional information (multi-line).
 */
function excel_format_additional_information( $p_additional_information ) {
	return excel_prepare_string( $p_additional_information );
}

/**
 * Gets the formatted value for the specified issue id, project and custom field.
 * @param $p_issue_id The issue id.
 * @param $p_project_id The project id.
 * @param $p_custom_field The custom field name (without 'custom_' prefix).
 * @returns The custom field value.
 */
function excel_format_custom_field( $p_issue_id, $p_project_id, $p_custom_field ) {
	$t_field_id = custom_field_get_id_from_name( $p_custom_field );

	if( $t_field_id === false ) {
		return excel_prepare_string( '@' . $p_custom_field . '@' );
	}

	if( custom_field_is_linked( $t_field_id, $p_project_id ) ) {
		$t_def = custom_field_get_definition( $t_field_id );
		return excel_prepare_string( string_custom_field_value( $t_def, $t_field_id, $p_issue_id ) );
	}

	// field is not linked to project
	return excel_prepare_string( '' );
}

/**
 * Gets the formatted due date.
 * @param $p_due_date The due date.
 * @returns The formatted due date.
 */
function excel_format_due_date( $p_due_date ) {
	return excel_prepare_string( date( config_get( 'short_date_format' ), $p_due_date ) );
}
