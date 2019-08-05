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
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses custom_field_api.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses project_api.php
 * @uses tag_api.php
 * @uses user_api.php
 */

use Mantis\Export\Cell;
use Mantis\Export\TableWriterFactory;

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );

/**
 * For exporting bug related fields, returns the suitable value type for each
 * of the bug fields.
 * @param string $p_fieldname	Bug field name
 * @return integer	One of the TYPE_ constants defined in Cell class
 */
function export_bugfield_type( $p_fieldname ) {
	# standard bug fields
	switch( $p_fieldname ) {
		case 'attachment_count':
		case 'bugnotes_count':
		case 'sponsorship_total':
			return Cell::TYPE_NUMERIC;

		case 'date_submitted':
		case 'due_date':
		case 'last_updated':
			return Cell::TYPE_DATE;

		case 'additional_information':
		case 'build':
		case 'category_id':
		case 'description':
		case 'duplicate_id':
		case 'eta':
		case 'fixed_in_version':
		case 'handler_id':
		case 'id':
		case 'notes':
		case 'os':
		case 'os_build':
		case 'platform':
		case 'priority':
		case 'project_id':
		case 'projection':
		case 'reporter_id':
		case 'reproducibility':
		case 'resolution':
		case 'severity':
		case 'status':
		case 'steps_to_reproduce':
		case 'summary':
		case 'tags':
		case 'target_version':
		case 'version':
		case 'view_state':
		default:
			return Cell::TYPE_STRING;
	}

	if( column_is_custom_field( $p_fieldname ) ) {
		$t_cf_name = column_get_custom_field_name( $p_fieldname );
		if( $t_cf_name ) {
			$t_cf_id = custom_field_get_id_from_name( $t_cf_name );
			if( $t_cf_id ) {
				$t_cf_type = custom_field_type( $t_cf_id );
				switch( $t_cf_type ) {
					case CUSTOM_FIELD_TYPE_NUMERIC:
					case CUSTOM_FIELD_TYPE_FLOAT:
						return Cell::TYPE_NUMERIC;
					case CUSTOM_FIELD_TYPE_DATE:
						return Cell::TYPE_DATE;
					default:
						return Cell::TYPE_STRING;
				}
			}
		}
	}

	# plugin fileds will be defaulted to string

	#anything else is string
	return Cell::TYPE_STRING;
}

/**
 * For exporting bug related data, translates and maps the value of each field into
 * a final representation of its value.
 * For example: translate user ids to names, enum values, ...
 *
 * @param string $p_fieldname	Bug field
 * @param BugData $p_bug		BugData object of the issue to get data from
 * @param integer $p_user_id	User id to use for access checks, and presentation options
 * @return mixed		The translated value of the requested field.
 */
function export_bugfield_prepare_value( $p_fieldname, BugData $p_bug, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	# Standard fields
	switch( $p_fieldname ) {
		# standard enum id
		case 'priority':
		case 'severity':
		case 'reproducibility':
		case 'view_state':
		case 'projection':
		case 'eta':
		case 'status':
		case 'resolution':
			return get_enum_element( $p_fieldname, $p_bug->$p_fieldname, $p_user_id, $p_bug->project_id );

		# user id
		case 'handler_id':
		case 'reporter_id':
			if( $p_bug->$p_fieldname > 0 ) {
				return user_get_name( $p_bug->$p_fieldname );
			}
			break;

		# date
		case 'due_date':
			if( access_has_bug_level( config_get( 'due_date_view_threshold', null, $p_user_id, $p_bug->project_id ), $p_bug->id ) ) {
				return $p_bug->$p_fieldname;
			}
			break;
		case 'date_submitted':
		case 'last_updated':
			return $p_bug->$p_fieldname;

		# value as is,pass through
		case 'additional_information':
		case 'build':
		case 'description':
		case 'fixed_in_version':
		case 'os':
		case 'os_build':
		case 'platform':
		case 'sponsorship_total':
		case 'steps_to_reproduce':
		case 'summary':
		case 'target_version':
		case 'version':
			return $p_bug->$p_fieldname;

		case 'attachment_count':
			$t_attachment_count = 0;
			if( file_can_view_bug_attachments( $p_bug->id, null ) ) {
				$t_attachment_count = file_bug_attachment_count( $p_bug->id );
			}
			return (int)$t_attachment_count;

		case 'bugnotes_count':
			$t_bugnote_count = 0;
			$t_bugnote_stats = bug_get_bugnote_stats( $p_bug->id );
			if( $t_bugnote_stats ) {
				$t_bugnote_count = $t_bugnote_stats['count'];
			}
			return (int)$t_bugnote_count;

		case 'category_id':
			return category_full_name( $p_bug->category_id, false );

		case 'duplicate_id':
		case 'id':
			return bug_format_id( $p_bug->id );

		case 'notes':
			return bugnote_get_all_visible_as_string( $p_bug->id, /* user_bugnote_order */ 'DESC', /* user_bugnote_limit */ 0 );

		case 'project_id':
			return project_get_name( $p_bug->project_id );

		case 'tags':
			$t_tags = '';
			if( access_has_bug_level( config_get( 'tag_view_threshold', null, $p_user_id, $p_bug->project_id ), $p_bug->id ) ) {
				$t_tags = tag_bug_get_all( $p_bug->id );
			}
			return $t_tags;
	}

	# custom fields
	if( column_is_custom_field( $p_fieldname ) ) {
		$t_cf_id = custom_field_get_id_from_name( $p_fieldname );
		if( $t_cf_id ) {
			$t_def = custom_field_get_definition( $t_cf_id );
			$t_value = string_custom_field_value( $t_def, $t_cf_id, $p_bug->id );
			return $t_value;
		}
	}

	# plugin fields
	if(column_is_plugin_column( $p_fieldname ) ) {
		$t_plugin_columns = columns_get_plugin_columns();
		$t_object = $t_plugin_columns[$p_fieldname];
		return $t_object->value( $p_bug );
	}

	# anything else
	return '';
}

/**
 * Returns the set of columns configured by the user for each type of export.
 *
 * @param string $p_type	The export type, possible values as "csv" or "excel"
 * @return array	Array of column names
 */
function export_get_columns ( $p_type = null ) {
	if( 'csv' == $p_type ) {
		$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_CSV_PAGE );
	} else {
		$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE );
	}
	return $t_columns;
}

/**
 * Builds a filename (without extension) for an export result.
 * @return string	The composed file name
 */
function export_get_default_filename() {
	$t_current_project_id = helper_get_current_project();

	if( ALL_PROJECTS == $t_current_project_id ) {
		$t_filename = user_get_name( auth_get_current_user_id() );
	} else {
		$t_filename = project_get_field( $t_current_project_id, 'name' );
	}

	return $t_filename;
}

/**
 * Build and print the html option list for an export selection form
 * @param string $p_selected	The provider id which should be marked as selected by default.
 */
function export_print_format_option_list( $p_selected = null ) {
	$t_providers = TableWriterFactory::getProviders();
	$fn_sort = function ( $p1, $p2 ) {
		return strcmp($p1->short_name, $p2->short_name);
	};
	usort( $t_providers, $fn_sort );

	foreach( $t_providers as $t_provider ) {
		$t_line = $t_provider->short_name . ' (.' . $t_provider->file_extension . ') [' . $t_provider->provider_name . ']';
		echo '<option value="',  $t_provider->unique_id, '" ', check_selected( $t_provider->unique_id, $p_selected, false ) . ' >', $t_line, '</option>';
	}
}

/**
 * Helper function to check if the user is authorized to change global configurations
 * related to the expoort system.
 * @param type $p_user_id
 * @return type
 */
function export_can_manage_global_config( $p_user_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	return access_has_global_level( config_get( 'manage_configuration_threshold' , null, ALL_USERS, ALL_PROJECTS ), $p_user_id );
}