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
 * CSV API
 * Names for formatting functions are csv_format_*, where * corresponds to the
 * field name as return get csv_get_columns() and by the filter api.
 *
 * @package CoreAPI
 * @subpackage CSVAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * get the csv file new line, can be moved to config in the future
 * @return string containing new line character
 * @access public
 */
function csv_get_newline() {
	return "\r\n";
}

/**
 * get the csv file separator, can be moved to config in the future
 * @return string
 * @access public
 */
function csv_get_separator() {
	static $s_seperator = null;
	if ( $s_seperator === null )
		$s_seperator = config_get( 'csv_separator' );
	return $s_seperator;
}

/**
 * if all projects selected, default to <username>.csv, otherwise default to
 * <projectname>.csv.
 * @return string filename
 * @access public
 */
function csv_get_default_filename() {
	$t_current_project_id = helper_get_current_project();

	if( ALL_PROJECTS == $t_current_project_id ) {
		$t_filename = user_get_name( auth_get_current_user_id() );
	} else {
		$t_filename = project_get_field( $t_current_project_id, 'name' );
	}

	return $t_filename . '.csv';
}

/**
 * escape a string before writing it to csv file.
 * @param type $todo TODO
 * @return  TODO
 * @access public
 */
function csv_escape_string( $p_str ) {
		$t_escaped = str_split( '"' . csv_get_separator() . csv_get_newline() );
		$t_must_escape = false;
		while( ( $t_char = current( $t_escaped ) ) !== false && !$t_must_escape ) {
			$t_must_escape = strpos( $p_str, $t_char ) !== false;
			next( $t_escaped );
		}
		if ( $t_must_escape ) {
			$p_str = '"' . str_replace( '"', '""', $p_str ) . '"';
		}

		return $p_str;
}

/**
 * An array of column names that are used to identify  fields to include and in which order.
 * @param type $todo TODO
 * @return  TODO
 * @access public
 */
function csv_get_columns() {
	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_CSV_PAGE );
	return $t_columns;
}

/**
 * returns the formatted bug id
 * @param object $p_bug the bug
 * @return string csv formatted bug id
 * @access public
 */
function csv_format_id( $p_bug ) {
	return bug_format_id( $p_bug->id );
}

/**
 * returns the project name corresponding to the supplied bug
 * @param object $p_bug the bug
 * @return string csv formatted project name
 * @access public
 */
function csv_format_project_id( $p_bug ) {
	return csv_escape_string( project_get_name( $p_bug->project_id ) );
}

/**
 * returns the reporter name corresponding to the supplied bug
 * @param object $p_bug the bug
 * @return string formatted user name
 * @access public
 */
function csv_format_reporter_id( $p_bug ) {
	return csv_escape_string( user_get_name( $p_bug->reporter_id ) );
}

/**
 * returns the handler name corresponding to the supplied bug
 * @param object $p_bug the bug
 * @return string formatted user name
 * @access public
 */
function csv_format_handler_id( $p_bug ) {
	if( $p_bug->handler_id > 0 ) {
		return csv_escape_string( user_get_name( $p_bug->handler_id ) );
	}
}

/**
 * return the priority string
 * @param object $p_bug the bug
 * @return string formatted priority string
 * @access public
 */
function csv_format_priority( $p_bug ) {
	return csv_escape_string( get_enum_element( 'priority', $p_bug->priority, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the severity string
 * @param object $p_bug the bug
 * @return string formatted severity string
 * @access public
 */
function csv_format_severity( $p_bug ) {
	return csv_escape_string( get_enum_element( 'severity', $p_bug->severity, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the reproducibility string
 * @param object $p_bug the bug
 * @return string formatted reproducibility string
 * @access public
 */
function csv_format_reproducibility( $p_bug ) {
	return csv_escape_string( get_enum_element( 'reproducibility', $p_bug->reproducibility, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the version
 * @param object $p_bug the bug
 * @return string formatted version string
 * @access public
 */
function csv_format_version( $p_bug ) {
	return csv_escape_string( $p_bug->version );
}

/**
 * return the fixed_in_version
 * @param object $p_bug the bug
 * @return string formatted fixed in version string
 * @access public
 */
function csv_format_fixed_in_version( $p_bug ) {
	return csv_escape_string( $p_bug->fixed_in_version );
}

/**
 * return the target_version
 * @param object $p_bug the bug
 * @return string formatted target version string
 * @access public
 */
function csv_format_target_version( $p_bug ) {
	return csv_escape_string( $p_bug->target_version );
}

/**
 * return the projection
 * @param object $p_bug the bug
 * @return string formatted projection string
 * @access public
 */
function csv_format_projection( $p_bug ) {
	return csv_escape_string( get_enum_element( 'projection', $p_bug->projection, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the category
 * @param object $p_bug the bug
 * @return string formatted category string
 * @access public
 */
function csv_format_category_id( $p_bug ) {
	return csv_escape_string( category_full_name( $p_bug->category_id, false ) );
}

/**
 * return the date submitted
 * @param object $p_bug the bug
 * @return string formatted date
 * @access public
 */
function csv_format_date_submitted( $p_bug ) {
	static $s_date_format = null;
	if ( $s_date_format === null )
		$s_date_format = config_get( 'short_date_format' );
	return date( $s_date_format, $p_bug->date_submitted );
}

/**
 * return the eta
 * @param object $p_bug the bug
 * @return string formatted eta
 * @access public
 */
function csv_format_eta( $p_bug ) {
	return csv_escape_string( get_enum_element( 'eta', $p_bug->eta, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the operating system
 * @param object $p_bug the bug
 * @return string formatted operating system
 * @access public
 */
function csv_format_os( $p_bug ) {
	return csv_escape_string( $p_bug->os );
}

/**
 * return the os build (os version)
 * @param object $p_bug the bug
 * @return string formatted operating system build
 * @access public
 */
function csv_format_os_build( $p_bug ) {
	return csv_escape_string( $p_bug->os_build );
}

/**
 * return the build
 * @param object $p_bug the bug
 * @return string formatted build
 * @access public
 */
function csv_format_build( $p_bug ) {
	return csv_escape_string( $p_bug->build );
}

/**
 * return the platform
 * @param object $p_bug the bug
 * @return string formatted platform
 * @access public
 */
function csv_format_platform( $p_bug ) {
	return csv_escape_string( $p_bug->platform );
}

/**
 * return the view state (eg: private / public)
 * @param object $p_bug the bug
 * @return string formatted view state
 * @access public
 */
function csv_format_view_state( $p_bug ) {
	return csv_escape_string( get_enum_element( 'view_state', $p_bug->view_state, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the last updated date
 * @param object $p_bug the bug
 * @return string formated last updated string
 * @access public
 */
function csv_format_last_updated( $p_bug ) {
	static $s_date_format = null;
	if ( $s_date_format === null )
		$s_date_format = config_get( 'short_date_format' );
	return date( $s_date_format, $p_bug->last_updated );
}

/**
 * return the summary
 * @param object $p_bug the bug
 * @return string formatted summary
 * @access public
 */
function csv_format_summary( $p_bug ) {
	return csv_escape_string( $p_bug->summary );
}

/**
 * return the description
 * @param object $p_bug the bug
 * @return string formatted description
 * @access public
 */
function csv_format_description( $p_bug ) {
	return csv_escape_string( $p_bug->description );
}

/**
 * return the steps to reproduce
 * @param object $p_bug the bug
 * @return string formatted steps to reproduce
 * @access public
 */
function csv_format_steps_to_reproduce( $p_bug ) {
	return csv_escape_string( $p_bug->steps_to_reproduce );
}

/**
 * return the additional information
 * @param object $p_bug the bug
 * @return string formatted additional information
 * @access public
 */
function csv_format_additional_information( $p_bug ) {
	return csv_escape_string( $p_bug->additional_information );
}

/**
 * return the status string
 * @param object $p_bug the bug
 * @return string formatted status
 * @access public
 */
function csv_format_status( $p_bug ) {
	return csv_escape_string( get_enum_element( 'status', $p_bug->status, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the resolution string
 * @param object $p_bug the bug
 * @return string formatted resolution string
 * @access public
 */
function csv_format_resolution( $p_bug ) {
	return csv_escape_string( get_enum_element( 'resolution', $p_bug->resolution, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * return the duplicate bug id
 * @param object $p_bug the bug
 * @return string formatted bug id
 * @access public
 */
function csv_format_duplicate_id( $p_bug ) {
	return bug_format_id( $p_bug->duplicate_id );
}

/**
 * return the selection
 * @param object $p_bug the bug
 * @return string
 * @access public
 */
function csv_format_selection( $p_bug ) {
	return csv_escape_string( '' );
}

/**
 * return the due date column
 * @param object $p_bug the bug
 * @return string
 * @access public
 */
function csv_format_due_date( $p_bug ) {
	static $s_date_format = null;
	if ( $s_date_format === null )
		$s_date_format = config_get( 'short_date_format' );
	return csv_escape_string( date( $s_date_format, $p_bug->due_date ) );
}

/**
 * return the sponsorship total for an issue
 * @param object $p_bug the bug
 * @return string
 * @access public
 */
function csv_format_sponsorship_total( $p_bug ) {
	return csv_escape_string( $p_bug->sponsorship_total );
}
