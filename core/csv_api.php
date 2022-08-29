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
 * CSV API
 *
 * @package CoreAPI
 * @subpackage CSVAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses project_api.php
 * @uses user_api.php
 *
 * @noinspection PhpDocMissingThrowsInspection
 */

use Mantis\Exceptions\ClientException;

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );

/**
 * Emits the headers and byte order marker.  This must be called at the beginning
 * of scripts that export CSV file before any other content is called.
 *
 * @param  string  $p_filename The csv filename and extension.
 * @return void
 */
function csv_start( $p_filename ) {
	$t_filename = urlencode( file_clean_name( $p_filename ) );

	header( 'Pragma: public' );
	header( 'Content-Encoding: UTF-8' );
	header( 'Content-Type: text/csv; name=' . $t_filename . ';charset=UTF-8' );
	header( 'Content-Transfer-Encoding: BASE64;' );
	header( 'Content-Disposition: attachment; filename="' . $t_filename . '"' );

	echo UTF8_BOM;
}

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
	static $s_separator = null;
	if( $s_separator === null ) {
		$s_separator = config_get( 'csv_separator' );
	}

	return $s_separator;
}

/**
 * if all projects selected, default to <username>.csv, otherwise default to
 * <projectname>.csv.
 * @return string filename
 * @access public
 * @throws ClientException
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
 * @param string $p_string String to escape.
 * @return string
 * @access public
 */
function csv_escape_string( $p_string ) {
	if( config_get( 'csv_injection_protection' ) ) {
		# Prevent CSV injection by escaping text that could be interpreted as a formula
		if( $p_string && strpos( '=-+@', $p_string[0] ) !== false ) {
			# Prefixing with a tab rather than single quote, as Excel does not show
			# the tab visually in the cell.
			$p_string = "\t" . $p_string;
		}
	}

	$t_escaped = str_split( '"' . csv_get_separator() . csv_get_newline() );
	$t_must_escape = false;
	while( ( $t_char = current( $t_escaped ) ) !== false && !$t_must_escape ) {
		$t_must_escape = strpos( $p_string, $t_char ) !== false;
		next( $t_escaped );
	}
	if( $t_must_escape ) {
		$p_string = '"' . str_replace( '"', '""', $p_string ) . '"';
	}

	return $p_string;
}

/**
 * An array of column names that are used to identify fields to include and in which order.
 * @return array
 * @access public
 */
function csv_get_columns() {
	return helper_get_columns_to_view( COLUMNS_TARGET_CSV_PAGE );
}

/**
 * Gets the formatted value for the specified issue id, project and custom field.
 * @param integer $p_issue_id     The issue id.
 * @param integer $p_project_id   The project id.
 * @param string  $p_custom_field The custom field name (without 'custom_' prefix).
 * @return string The custom field value.
 */
function csv_format_custom_field( $p_issue_id, $p_project_id, $p_custom_field ) {
	$t_field_id = custom_field_get_id_from_name( $p_custom_field );

	if( $t_field_id === false ) {
		$t_value = '@' . $p_custom_field . '@';
	} else if( custom_field_is_linked( $t_field_id, $p_project_id ) ) {
		$t_def = custom_field_get_definition( $t_field_id );
		$t_value = string_custom_field_value( $t_def, $t_field_id, $p_issue_id );
	} else {
		# field is not linked to project
		$t_value = '';
	}

	return csv_escape_string( $t_value );
}

/**
 * Gets the formatted value for the specified plugin column value.
 * @param string  $p_column The plugin column name.
 * @param BugData $p_bug    A bug object to print the column for - needed for
 *                          the display function of the plugin column.
 * @return string The plugin column value.
 */
function csv_format_plugin_column_value( $p_column, BugData $p_bug ) {
	$t_plugin_columns = columns_get_plugin_columns();

	if( !isset( $t_plugin_columns[$p_column] ) ) {
		$t_value = '';
	} else {
		$t_column_object = $t_plugin_columns[$p_column];
		$t_value = $t_column_object->value( $p_bug );
	}

	return csv_escape_string( $t_value );
}

/**
 * returns the formatted bug id
 * @param BugData $p_bug A BugData object.
 * @return string csv formatted bug id
 * @access public
 */
function csv_format_id( BugData $p_bug ) {
	return bug_format_id( $p_bug->id );
}

/**
 * returns the project name corresponding to the supplied bug
 * @param BugData $p_bug A BugData object.
 * @return string csv formatted project name
 * @access public
 */
function csv_format_project_id( BugData $p_bug ) {
	return csv_escape_string( project_get_name( $p_bug->project_id ) );
}

/**
 * returns the reporter name corresponding to the supplied bug
 * @param BugData $p_bug A BugData object.
 * @return string formatted user name
 * @access public
 */
function csv_format_reporter_id( BugData $p_bug ) {
	return csv_escape_string( user_get_name( $p_bug->reporter_id ) );
}

/**
 * returns the handler name corresponding to the supplied bug
 * @param BugData $p_bug A BugData object.
 * @return string formatted user name
 * @access public
 */
function csv_format_handler_id( BugData $p_bug ) {
	if( $p_bug->handler_id > 0 ) {
		return csv_escape_string( user_get_name( $p_bug->handler_id ) );
	}
	return '';
}

/**
 * return the priority string
 * @param BugData $p_bug A BugData object.
 * @return string formatted priority string
 * @access public
 * @throws ClientException
 */
function csv_format_priority( BugData $p_bug ) {
	$t_priority = get_enum_element( 'priority',
		$p_bug->priority,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_priority );
}

/**
 * return the severity string
 * @param BugData $p_bug A BugData object.
 * @return string formatted severity string
 * @access public
 * @throws ClientException
 */
function csv_format_severity( BugData $p_bug ) {
	$t_severity = get_enum_element( 'severity',
		$p_bug->severity,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_severity );
}

/**
 * return the reproducibility string
 * @param BugData $p_bug A BugData object.
 * @return string formatted reproducibility string
 * @access public
 * @throws ClientException
 */
function csv_format_reproducibility( BugData $p_bug ) {
	$t_reproducibility = get_enum_element( 'reproducibility',
		$p_bug->reproducibility,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_reproducibility );
}

/**
 * return the version
 * @param BugData $p_bug A BugData object.
 * @return string formatted version string
 * @access public
 */
function csv_format_version( BugData $p_bug ) {
	return csv_escape_string( $p_bug->version );
}

/**
 * return the fixed_in_version
 * @param BugData $p_bug A BugData object.
 * @return string formatted fixed in version string
 * @access public
 */
function csv_format_fixed_in_version( BugData $p_bug ) {
	return csv_escape_string( $p_bug->fixed_in_version );
}

/**
 * return the target_version
 * @param BugData $p_bug A BugData object.
 * @return string formatted target version string
 * @access public
 */
function csv_format_target_version( BugData $p_bug ) {
	return csv_escape_string( $p_bug->target_version );
}

/**
 * return the tags
 * @param BugData $p_bug A BugData object.
 * @return string formatted tags string
 * @access public
 */
function csv_format_tags( BugData $p_bug ) {
	$t_value = '';

	if( access_has_bug_level( config_get( 'tag_view_threshold' ), $p_bug->id ) ) {
		$t_value = tag_bug_get_all( $p_bug->id );
	}

	return csv_escape_string( $t_value );
}

/**
 * return the projection
 * @param BugData $p_bug A BugData object.
 * @return string formatted projection string
 * @access public
 * @throws ClientException
 */
function csv_format_projection( BugData $p_bug ) {
	$t_projection = get_enum_element( 'projection',
		$p_bug->projection,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_projection );
}

/**
 * return the category
 * @param BugData $p_bug A BugData object.
 * @return string formatted category string
 * @access public
 */
function csv_format_category_id( BugData $p_bug ) {
	return csv_escape_string( category_full_name( $p_bug->category_id, false ) );
}

/**
 * return the date submitted
 * @param BugData $p_bug A BugData object.
 * @return string formatted date
 * @access public
 */
function csv_format_date_submitted( BugData $p_bug ) {
	static $s_date_format = null;
	if( $s_date_format === null ) {
		$s_date_format = config_get( 'short_date_format' );
	}
	return date( $s_date_format, $p_bug->date_submitted );
}

/**
 * return the eta
 * @param BugData $p_bug A BugData object.
 * @return string formatted eta
 * @access public
 * @throws ClientException
 */
function csv_format_eta( BugData $p_bug ) {
	$t_eta = get_enum_element( 'eta',
		$p_bug->eta,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_eta );
}

/**
 * return the operating system
 * @param BugData $p_bug A BugData object.
 * @return string formatted operating system
 * @access public
 */
function csv_format_os( BugData $p_bug ) {
	return csv_escape_string( $p_bug->os );
}

/**
 * return the os build (os version)
 * @param BugData $p_bug A BugData object.
 * @return string formatted operating system build
 * @access public
 */
function csv_format_os_build( BugData $p_bug ) {
	return csv_escape_string( $p_bug->os_build );
}

/**
 * return the build
 * @param BugData $p_bug A BugData object.
 * @return string formatted build
 * @access public
 */
function csv_format_build( BugData $p_bug ) {
	return csv_escape_string( $p_bug->build );
}

/**
 * return the platform
 * @param BugData $p_bug A BugData object.
 * @return string formatted platform
 * @access public
 */
function csv_format_platform( BugData $p_bug ) {
	return csv_escape_string( $p_bug->platform );
}

/**
 * return the view state (either private or public)
 * @param BugData $p_bug A BugData object.
 * @return string formatted view state
 * @access public
 * @throws ClientException
 */
function csv_format_view_state( BugData $p_bug ) {
	$t_view_state = get_enum_element( 'view_state',
		$p_bug->view_state,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_view_state );
}

/**
 * return the last updated date
 * @param BugData $p_bug A BugData object.
 * @return string formatted last updated string
 * @access public
 */
function csv_format_last_updated( BugData $p_bug ) {
	static $s_date_format = null;
	if( $s_date_format === null ) {
		$s_date_format = config_get( 'short_date_format' );
	}
	return date( $s_date_format, $p_bug->last_updated );
}

/**
 * return the summary
 * @param BugData $p_bug A BugData object.
 * @return string formatted summary
 * @access public
 */
function csv_format_summary( BugData $p_bug ) {
	return csv_escape_string( $p_bug->summary );
}

/**
 * return the description
 * @param BugData $p_bug A BugData object.
 * @return string formatted description
 * @access public
 */
function csv_format_description( BugData $p_bug ) {
	return csv_escape_string( $p_bug->description );
}

/**
 * Return the notes associated with the specified bug as a string.
 *
 * @param BugData $p_bug A BugData object.
 * @return string The notes formatted as a string.
 * @access public
 */
function csv_format_notes( BugData $p_bug ) {
	$t_notes = bugnote_get_all_visible_as_string( $p_bug->id, 'DESC',0 );
	return csv_escape_string( $t_notes );
}

/**
 * return the steps to reproduce
 * @param BugData $p_bug A BugData object.
 * @return string formatted steps to reproduce
 * @access public
 */
function csv_format_steps_to_reproduce( BugData $p_bug ) {
	return csv_escape_string( $p_bug->steps_to_reproduce );
}

/**
 * return the additional information
 * @param BugData $p_bug A BugData object.
 * @return string formatted additional information
 * @access public
 */
function csv_format_additional_information( BugData $p_bug ) {
	return csv_escape_string( $p_bug->additional_information );
}

/**
 * return the status string
 * @param BugData $p_bug A BugData object.
 * @return string formatted status
 * @access public
 * @throws ClientException
 */
function csv_format_status( BugData $p_bug ) {
	$t_status = get_enum_element( 'status',
		$p_bug->status,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_status );
}

/**
 * return the resolution string
 * @param BugData $p_bug A BugData object.
 * @return string formatted resolution string
 * @access public
 * @throws ClientException
 */
function csv_format_resolution( BugData $p_bug ) {
	$t_resolution = get_enum_element( 'resolution',
		$p_bug->resolution,
		auth_get_current_user_id(),
		$p_bug->project_id
	);
	return csv_escape_string( $t_resolution );
}

/**
 * return the duplicate bug id
 * @param BugData $p_bug A BugData object.
 * @return string formatted bug id
 * @access public
 */
function csv_format_duplicate_id( BugData $p_bug ) {
	return bug_format_id( $p_bug->duplicate_id );
}

/**
 * return the selection
 * @param BugData $p_bug A BugData object.
 * @return string
 * @access public
 * @noinspection PhpUnusedParameterInspection
 */
function csv_format_selection( BugData $p_bug ) {
	return csv_escape_string( '' );
}

/**
 * return the due date column
 * @param BugData $p_bug A BugData object.
 * @return string
 * @access public
 */
function csv_format_due_date( BugData $p_bug ) {
	static $s_date_format = null;
	if( $s_date_format === null ) {
		$s_date_format = config_get( 'short_date_format' );
	}
	
	$t_value = '';
	if ( !date_is_null( $p_bug->due_date )
		&& access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id )
	) {
		$t_value = date( $s_date_format, $p_bug->due_date );
	}
	return csv_escape_string( $t_value );
}

/**
 * return the sponsorship total for an issue
 * @param BugData $p_bug A BugData object.
 * @return string
 * @access public
 */
function csv_format_sponsorship_total( BugData $p_bug ) {
	return csv_escape_string( $p_bug->sponsorship_total );
}

/**
 * return the attachment count for an issue
 * @param BugData $p_bug A BugData object.
 * @return string
 * @access public
 */
function csv_format_attachment_count( BugData $p_bug ) {
	# Check for attachments
	$t_attachment_count = 0;
	if( file_can_view_bug_attachments( $p_bug->id ) ) {
		$t_attachment_count = file_bug_attachment_count( $p_bug->id );
	}
	return csv_escape_string( $t_attachment_count );
}

/**
 * return the bug note count for an issue
 * @param BugData $p_bug A BugData object.
 * @return string
 * @access public
 */
function csv_format_bugnotes_count( BugData $p_bug ) {
	# grab the bugnote count
	$t_bugnote_stats = bug_get_bugnote_stats( $p_bug->id );
	if( $t_bugnote_stats ) {
		$t_bugnote_count = $t_bugnote_stats['count'];
	} else {
		$t_bugnote_count = 0;
	}
	return csv_escape_string( $t_bugnote_count );
}
