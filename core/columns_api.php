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
 * Columns API
 *
 * @package CoreAPI
 * @subpackage ColumnsAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses sponsorship_api.php
 * @uses string_api.php
 */

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'string_api.php' );

/**
 * Filters an array of columns based on configuration options.  The filtering can remove
 * columns whose features are disabled.
 *
 * @param array $p_columns The columns proposed for display.
 * @return array The columns array after removing the disabled features.
 */
function columns_filter_disabled( array $p_columns ) {
	$t_columns = array();
	$t_enable_profiles = ( config_get( 'enable_profiles' ) == ON );

	foreach ( $p_columns as $t_column ) {
		switch( $t_column ) {
			case 'os':
			case 'os_build':
			case 'platform':
				if( ! $t_enable_profiles ) {
					continue 2;
				}
				break;

			case 'eta':
				if( config_get( 'enable_eta' ) == OFF ) {
					continue 2;
				}
				break;

			case 'projection':
				if( config_get( 'enable_projection' ) == OFF ) {
					continue 2;
				}
				break;

			case 'build':
				if( config_get( 'enable_product_build' ) == OFF ) {
					continue 2;
				}
				break;

			case 'sponsorship_total':
				if( config_get( 'enable_sponsorship' ) == OFF ) {
					continue 2;
				}
				break;

				default:
				# don't filter
				break;
		}
		$t_columns[] = $t_column;
	} # continued 2

	return $t_columns;
}

/**
 * Get a list of standard columns.
 * @param boolean $p_enabled_columns_only Default true, if false returns all columns regardless of configuration settings.
 * @return array of column names
 */
function columns_get_standard( $p_enabled_columns_only = true ) {
	$t_reflection = new ReflectionClass( 'BugData' );
	$t_columns = $t_reflection->getDefaultProperties();

	$t_columns['selection'] = null;
	$t_columns['edit'] = null;
	$t_columns['notes'] = null;
	$t_columns['tags'] = null;

	# Overdue icon column (icons appears if an issue is beyond due_date)
	$t_columns['overdue'] = null;

	# The following fields are used internally and don't make sense as columns
	unset( $t_columns['_stats'] );
	unset( $t_columns['profile_id'] );
	unset( $t_columns['sticky'] );
	unset( $t_columns['loading'] );

	# legacy field
	unset( $t_columns['duplicate_id'] );

	$t_column_names = array_keys( $t_columns );

	if( $p_enabled_columns_only ) {
		$t_column_names = columns_filter_disabled( $t_column_names );
	}

	return $t_column_names;
}

/**
 * Allow plugins to define a set of class-based columns, and register/load
 * them here to be used by columns_api.
 * @return array Mapping of column name to column object
 */
function columns_get_plugin_columns() {
	static $s_column_array = null;

	if( is_null( $s_column_array ) ) {
		$s_column_array = array();

		$t_all_plugin_columns = event_signal( 'EVENT_FILTER_COLUMNS' );
		foreach( $t_all_plugin_columns as $t_plugin => $t_plugin_columns ) {
			foreach( $t_plugin_columns as $t_callback => $t_plugin_column_array ) {
				if( is_array( $t_plugin_column_array ) ) {
					foreach( $t_plugin_column_array as $t_column_item ) {
						if( is_object( $t_column_item ) && $t_column_item instanceof MantisColumn ) {
							$t_column_object = $t_column_item;
						} elseif( class_exists( $t_column_item ) && is_subclass_of( $t_column_item, 'MantisColumn' ) ) {
							$t_column_object = new $t_column_item();
						} else {
							continue;
						}
						$t_column_name = mb_strtolower( $t_plugin . '_' . $t_column_object->column );
						$s_column_array[$t_column_name] = $t_column_object;
					}
				}
			}
		}
	}

	return $s_column_array;
}

/**
 * Get all columns for existing custom_fields
 * @return string Array of column names
 */
function columns_get_custom_fields() {
	static $t_col_names = null;
	if( isset( $t_col_names ) ) {
		return $t_col_names;
	}

	$t_all_cfids = custom_field_get_ids();
	$t_col_names = array();
	foreach( $t_all_cfids as $t_id ) {
		$t_col_names[] = column_get_custom_field_column_name( $t_id );
	}
	return $t_col_names;
}

/**
 * Get all columns active for the current system.
 * This includes standard, custom fields, and plugin columns.
 * Columns for disabled modules are removed from this list, according to system
 * configuration.
 *
 * This function cannot check on current user/project, so it can be used by core
 * in potential unlogged-in scenarios.
 * @return array Array of column names
 */
function columns_get_all_active_columns() {
	$t_columns = array_merge(
			columns_get_standard(),
			array_keys( columns_get_plugin_columns() ),
			columns_get_custom_fields()
			);
	return columns_filter_disabled( $t_columns );
}

/**
 * Returns true if the specified $p_column is a plugin column.
 * @param string $p_column A column name.
 * @return boolean
 */
function column_is_plugin_column( $p_column ) {
	$t_plugin_columns = columns_get_plugin_columns();
	return isset( $t_plugin_columns[$p_column] );
}

/**
 * Get all accessible columns for the current project / current user.
 * @param integer $p_project_id A project identifier.
 * @return array array of columns
 * @access public
 */
function columns_get_all( $p_project_id = null ) {
	$t_columns = columns_get_standard();

	# add plugin columns
	$t_columns = array_merge( $t_columns, array_keys( columns_get_plugin_columns() ) );

	# Add project custom fields to the array.  Only add the ones for which the current user has at least read access.
	if( $p_project_id === null ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}
	# Get custom fields from this project and sub-projects
	$t_projects = user_get_all_accessible_projects( null, $t_project_id );
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_projects );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_cfdef = custom_field_get_definition( $t_id );
		$t_projects_to_check = array_intersect( $t_projects, custom_field_get_project_ids( $t_id ) );
		if( access_has_any_project_level( (int)$t_cfdef['access_level_r'], $t_projects_to_check ) ) {
			$t_columns[] = column_get_custom_field_column_name( $t_id );
		}
	}

	return $t_columns;
}

/**
 * Checks if the specified column is an extended column.  Extended columns are native columns that are
 * associated with the issue but are saved in mantis_bug_text_table.
 * @param string $p_column The column name.
 * @return boolean true for extended; false otherwise.
 * @access public
 */
function column_is_extended( $p_column ) {
	switch( $p_column ) {
		case 'description':
		case 'steps_to_reproduce':
		case 'additional_information':
			return true;
		default:
			return false;
	}
}

/**
 * Checks if the specified column is a custom field column.
 * @param string $p_column The column name.
 * @return boolean True if its a custom field column
 */
function column_is_custom_field( $p_column ) {
	$t_cf_columns = columns_get_custom_fields();
	return in_array( $p_column, $t_cf_columns );
}

/**
 * Checks if the specified column can be sorted
 * @param string $p_column The column name.
 * @return boolean True if the column can be sorted
 */
function column_is_sortable( $p_column ) {

	# custom fields are always sortable
	if( column_is_custom_field( $p_column ) ) {
		return true;
	}

	# plugin fields contains a 'sortable' property
	if( column_is_plugin_column( $p_column ) ) {
		$t_plugin_columns = columns_get_plugin_columns();
		$t_plugin_obj = $t_plugin_columns[$p_column];
		return $t_plugin_obj->sortable;
	}

	#standard fields: define exceptions here
	switch( $p_column ) {
		case 'selection':
		case 'edit':
		case 'bugnotes_count':
		case 'attachment_count':
		case 'tags':
		case 'overdue':
		case 'additional_information':
		case 'description':
		case 'notes':
		case 'steps_to_reproduce':
			return false;
	}

	# after all exceptions, return true as default
	return true;
}

/**
 * Given a column name from the array of columns to be included in a view, this method checks if
 * the column is a custom column and if so returns its name.  Note that for custom fields, then
 * provided names will have the "custom_" prefix, where the returned ones won't have the prefix.
 *
 * @param string $p_column Column name.
 * @return string The custom field column name or null if the specific column is not a custom field or invalid column.
 * @access public
 */
function column_get_custom_field_name( $p_column ) {
	if( strncmp( $p_column, 'custom_', 7 ) === 0 ) {
		return mb_substr( $p_column, 7 );
	}

	return null;
}

/**
 * Returns the name of a column corresponding to a custom field, providing the id as parameter.
 *
 * @param integer $p_cf_id	Custom field id
 * @return string	The column name
 */
function column_get_custom_field_column_name( $p_cf_id ) {
	$t_def = custom_field_get_definition( $p_cf_id );
	if( $t_def ) {
		return 'custom_' . $t_def['name'];
	} else {
		return null;
	}
}

/**
 * Converts a string of comma separate column names to an array.
 *
 * @param string $p_string Comma separate column name (not case sensitive).
 * @return array The array with all column names lower case.
 * @access public
 */
function columns_string_to_array( $p_string ) {
	$t_columns = explode( ',', $p_string );
	$t_count = count( $t_columns );

	for( $i = 0; $i < $t_count; $i++ ) {
		$t_columns[$i] = trim( $t_columns[$i] );
	}

	return $t_columns;
}

/**
 * Gets the localized title for the specified column.  The column can be native or custom.
 * The custom fields must contain the 'custom_' prefix.
 *
 * @param string $p_column The column name.
 * @return string The column localized name.
 * @access public
 */
function column_get_title( $p_column ) {
	$t_custom_field = column_get_custom_field_name( $p_column );
	if( $t_custom_field !== null ) {
		$t_field_id = custom_field_get_id_from_name( $t_custom_field );

		if( $t_field_id === false ) {
			$t_custom_field = '@' . $t_custom_field . '@';
		} else {
			$t_def = custom_field_get_definition( $t_field_id );
			$t_custom_field = lang_get_defaulted( $t_def['name'] );
		}

		return $t_custom_field;
	}

	$t_plugin_columns = columns_get_plugin_columns();
	if( isset( $t_plugin_columns[$p_column] ) ) {
		$t_column_object = $t_plugin_columns[$p_column];
		return $t_column_object->title;
	}

	switch( $p_column ) {
		case 'attachment_count':
			return lang_get( 'attachments' );
		case 'bugnotes_count':
			return '#';
		case 'category_id':
			return lang_get( 'category' );
		case 'edit':
			return '';
		case 'handler_id':
			return lang_get( 'assigned_to' );
		case 'last_updated':
			return lang_get( 'updated' );
		case 'os_build':
			return lang_get( 'os_version' );
		case 'project_id':
			return lang_get( 'email_project' );
		case 'reporter_id':
			return lang_get( 'reporter' );
		case 'selection':
			return '';
		case 'sponsorship_total':
			return sponsorship_get_currency();
		case 'version':
			return lang_get( 'product_version' );
		case 'view_state':
			return lang_get( 'view_status' );
		default:
			return lang_get_defaulted( $p_column );
	}
}

/**
 * Checks an array of columns for duplicate or invalid fields.
 *
 * @param string $p_field_name          The logic name of the array being validated.  Used when triggering errors.
 * @param array  $p_columns_to_validate The array of columns to validate.
 * @param array  $p_columns_all         The list of all valid columns.
 * @return boolean
 * @access public
 */
function columns_ensure_valid( $p_field_name, array $p_columns_to_validate, array $p_columns_all ) {
	$t_columns_all_lower = array_map( 'mb_strtolower', $p_columns_all );

	# Check for invalid fields
	foreach( $p_columns_to_validate as $t_column ) {
		if( !in_array( mb_strtolower( $t_column ), $t_columns_all_lower ) ) {
			error_parameters( $p_field_name, $t_column );
			trigger_error( ERROR_COLUMNS_INVALID, ERROR );
			return false;
		}
	}

	# Check for duplicate fields
	$t_columns_no_duplicates = array();
	foreach( $p_columns_to_validate as $t_column ) {
		$t_column_lower = mb_strtolower( $t_column );
		if( in_array( $t_column, $t_columns_no_duplicates ) ) {
			error_parameters( $p_field_name, $t_column );
			trigger_error( ERROR_COLUMNS_DUPLICATE, ERROR );
		} else {
			$t_columns_no_duplicates[] = $t_column_lower;
		}
	}

	return true;
}

/**
 * Validates an array of column names and removes ones that are not valid.  The validation
 * is not case sensitive.
 *
 * @param array $p_columns     The array of column names to be validated.
 * @param array $p_columns_all The array of all valid column names.
 * @return array The array of valid column names found in $p_columns.
 * @access public
 */
function columns_remove_invalid( array $p_columns, array $p_columns_all ) {
	$t_columns_all_lower = array_values( array_map( 'mb_strtolower', $p_columns_all ) );
	$t_columns = array();

	foreach( $p_columns as $t_column ) {
		if( in_array( mb_strtolower( $t_column ), $t_columns_all_lower ) ) {
			$t_columns[] = $t_column;
		}
	}

	return $t_columns;
}

/**
 * Print table header for selection column
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_selection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-selection"> &#160; </th>';
}

/**
 * Print table header for edit column
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_edit( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-edit"> &#160; </th>';
}

/**
 * Print table header for column ID
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-id">';
	print_view_bug_sort_link( lang_get( 'id' ), 'id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'id' );
	echo '</th>';
}

/**
 * Print table header for column project id
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_project_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-project-id">';
	print_view_bug_sort_link( lang_get( 'email_project' ), 'project_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'project_id' );
	echo '</th>';
}

/**
 * Print table header for column reporter id
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_reporter_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-reporter">';
	print_view_bug_sort_link( lang_get( 'reporter' ), 'reporter_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'reporter_id' );
	echo '</th>';
}

/**
 * Print table header for column handler id
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_handler_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-assigned-to">';
	print_view_bug_sort_link( lang_get( 'assigned_to' ), 'handler_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'handler_id' );
	echo '</th>';
}

/**
 * Print table header for column priority
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_priority( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-priority">';
	# Use short label only when displaying icons
	$t_label = lang_get( config_get( 'show_priority_text' ) ? 'priority' : 'priority_abbreviation' );
	print_view_bug_sort_link( $t_label, 'priority', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'priority' );
	echo '</th>';
}

/**
 * Print table header for column reproducibility
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_reproducibility( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-reproducibility">';
	print_view_bug_sort_link( lang_get( 'reproducibility' ), 'reproducibility', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'reproducibility' );
	echo '</th>';
}

/**
 * Print table header for column projection
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_projection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-projection">';
	print_view_bug_sort_link( lang_get( 'projection' ), 'projection', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'projection' );
	echo '</th>';
}

/**
 * Print table header for column ETA
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_eta( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-eta">';
	print_view_bug_sort_link( lang_get( 'eta' ), 'eta', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'eta' );
	echo '</th>';
}

/**
 * Print table header for column resolution
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_resolution( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-resolution">';
	print_view_bug_sort_link( lang_get( 'resolution' ), 'resolution', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'resolution' );
	echo '</th>';
}

/**
 * Print table header for column fixed in version
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_fixed_in_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-fixed-in-version">';
	print_view_bug_sort_link( lang_get( 'fixed_in_version' ), 'fixed_in_version', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'fixed_in_version' );
	echo '</th>';
}

/**
 * Print table header for column tags
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_tags( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-tags">' . lang_get('tags') . '</th>';
}

/**
 * Print table header for column target version
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_target_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-target-version">';
	print_view_bug_sort_link( lang_get( 'target_version' ), 'target_version', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'target_version' );
	echo '</th>';
}

/**
 * Print table header for column view state
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_view_state( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-view-state">';
	$t_view_state_text = lang_get( 'view_status' );
	$t_view_state_icon = ' <i class="fa fa-lock" title="' . $t_view_state_text . '"></i>';
	print_view_bug_sort_link( $t_view_state_icon, 'view_state', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'view_state' );
	echo '</th>';
}

/**
 * Print table header for column OS
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_os( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-os">';
	print_view_bug_sort_link( lang_get( 'os' ), 'os', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'os' );
	echo '</th>';
}

/**
 * Print table header for column OS Build
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_os_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-os-build">';
	print_view_bug_sort_link( lang_get( 'os_version' ), 'os_build', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'os_build' );
	echo '</th>';
}

/**
 * Print table header for column Build
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
		echo '<th class="column-build">';
		print_view_bug_sort_link( lang_get( 'build' ), 'build', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'build' );
		echo '</th>';
	} else {
		echo lang_get( 'build' );
	}
}

/**
 * Print table header for column Platform
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_platform( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-platform">';
	print_view_bug_sort_link( lang_get( 'platform' ), 'platform', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'platform' );
	echo '</th>';
}

/**
 * Print table header for column version
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-version">';
	print_view_bug_sort_link( lang_get( 'product_version' ), 'version', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'version' );
	echo '</th>';
}

/**
 * Print table header for column date submitted
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_date_submitted( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-date-submitted">';
	print_view_bug_sort_link( lang_get( 'date_submitted' ), 'date_submitted', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'date_submitted' );
	echo '</th>';
}

/**
 * Print table header for column attachment count
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_attachment_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_attachment_count_text = lang_get( 'attachment_count' );
	$t_attachment_count_icon = "<i class=\"fa fa-paperclip blue\" title=\"$t_attachment_count_text\" ></i>";
	echo "\t" . '<th class="column-attachments">' . $t_attachment_count_icon . '</th>' . "\n";
}

/**
 * Print table header for column category
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_category_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-category">';
	print_view_bug_sort_link( lang_get( 'category' ), 'category_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'category_id' );
	echo '</th>';
}

/**
 * Prints Category column header
 * The actual column is 'category_id', this function is just here for backwards
 * compatibility
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_category( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	trigger_error( ERROR_GENERIC, WARNING );
	print_column_title_category_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE );
}

/**
 * Print table header for column sponsorship total
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_sponsorship_total( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo "\t<th class=\"column-sponsorship\">";
	print_view_bug_sort_link( sponsorship_get_currency(), 'sponsorship_total', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'sponsorship_total' );
	echo "</th>\n";
}

/**
 * Print table header for column severity
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_severity( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-severity">';
	print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'severity' );
	echo '</th>';
}

/**
 * Print table header for column status
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_status( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-status">';
	print_view_bug_sort_link( lang_get( 'status' ), 'status', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'status' );
	echo '</th>';
}

/**
 * Print table header for column last updated
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_last_updated( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-last-modified">';
	print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'last_updated' );
	echo '</th>';
}

/**
 * Print table header for column summary
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_summary( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-summary">';
	print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'summary' );
	echo '</th>';
}

/**
 * Print table header for column bugnotes count
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_bugnotes_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-bugnotes-count"> <i class="fa fa-comments blue"></i> </th>';
}

/**
 * Print table header for column description
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_description( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-description">';
	echo lang_get( 'description' );
	echo '</th>';
}

/**
 * Print table header for notes column.
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_notes( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-notes">';
	echo lang_get( 'bug_notes_title' );
	echo '</th>';
}

/**
 * Print table header for column steps to reproduce
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_steps_to_reproduce( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-steps-to-reproduce">';
	echo lang_get( 'steps_to_reproduce' );
	echo '</th>';
}

/**
 * Print table header for column additional information
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_additional_information( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-additional-information">';
	echo lang_get( 'additional_information' );
	echo '</th>';
}

/**
 * Prints Due Date column header
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_due_date( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-due-date">';
	print_view_bug_sort_link( lang_get( 'due_date' ), 'due_date', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'due_date' );
	echo '</th>';
}

/**
 * Print table header for column overdue
 *
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_overdue( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-overdue">';
	$t_overdue_text = lang_get( 'overdue' );
	$t_overdue_icon = ' <i class="fa fa-times-circle-o" title="' . $t_overdue_text . '"></i>';
	print_view_bug_sort_link( $t_overdue_icon, 'due_date', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'due_date' );
	echo '</th>';
}

/**
 * Print table data for column selection
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_selection( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $g_checkboxes_exist;

	echo '<td class="column-selection">';
	if( # check report_bug_threshold for the actions "copy" or "move" into any other project
		access_has_any_project_level( 'report_bug_threshold' ) ||
		# !TODO: check if any other projects actually exist for the bug to be moved to
		access_has_project_level( config_get( 'move_bug_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		# !TODO: factor in $g_auto_set_status_to_assigned == ON
		access_has_project_level( config_get( 'update_bug_assign_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'update_bug_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'delete_bug_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		# !TODO: check to see if the bug actually has any different selectable workflow states
		access_has_project_level( config_get( 'update_bug_status_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'set_bug_sticky_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'change_view_status_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'add_bugnote_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'tag_attach_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
		access_has_project_level( config_get( 'roadmap_update_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ) {
		$g_checkboxes_exist = true;
		echo '<div class="checkbox no-padding no-margin"><label>';
		printf( '<input type="checkbox" name="bug_arr[]" value="%d" class="ace" />', $p_bug->id );
		echo '<span class="lbl"></span>';
		echo '</label></div>';
	} else {
		echo '&#160;';
	}
	echo '</td>';
}

/**
 * Print column title for a specific custom column.
 *
 * @param string  $p_column         Active column.
 * @param object  $p_column_object  Column object.
 * @param string  $p_sort           Sort.
 * @param string  $p_dir            Direction.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_title_plugin( $p_column, $p_column_object, $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<th class="column-plugin">';
	if( $p_column_object->sortable ) {
		print_view_bug_sort_link( string_display_line( $p_column_object->title ), $p_column, $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, $p_column );
	} else {
		echo string_display_line( $p_column_object->title );
	}
	echo '</th>';
}

/**
 * Print custom column content for a specific bug.
 * @param object  $p_column_object  Column object.
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_plugin( $p_column_object, BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
		echo '<td class="column-plugin">';
		$p_column_object->display( $p_bug, $p_columns_target );
		echo '</td>';
	} else {
		$p_column_object->display( $p_bug, $p_columns_target );
	}
}

/**
 * Print column content for column edit
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_edit( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {

	echo '<td class="column-edit">';

	if( !bug_is_readonly( $p_bug->id ) && access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug->id ) ) {
		echo '<a href="' . string_get_bug_update_url( $p_bug->id ) . '">';
		echo '<i class="fa fa-pencil bigger-130 padding-2 grey"';
		echo ' title="' . lang_get( 'update_bug_button' ) . '"></i></a>';
	} else {
		echo '&#160;';
	}

	echo '</td>';
}

/**
 * Print column content for column priority
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_priority( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-priority">';
	if( ON == config_get( 'show_priority_text' ) ) {
		print_formatted_priority_string( $p_bug );
	} else {
		print_status_icon( $p_bug->priority );
	}
	echo '</td>';
}

/**
 * Print column content for column id
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_id( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-id">';
	print_bug_link( $p_bug->id, false );
	echo '</td>';
}

/**
 * Print column content for column sponsorship total
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_sponsorship_total( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo "\t<td class=\"right column-sponsorship\">";

	if( $p_bug->sponsorship_total > 0 ) {
		$t_sponsorship_amount = sponsorship_format_amount( $p_bug->sponsorship_total );
		echo string_no_break( $t_sponsorship_amount );
	}

	echo "</td>\n";
}

/**
 * Print column content for column bugnotes count
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_bugnotes_count( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $g_filter;

	# grab the bugnote count
	$t_bugnote_stats = bug_get_bugnote_stats( $p_bug->id );
	if( null !== $t_bugnote_stats ) {
		$t_bugnote_count = $t_bugnote_stats['count'];
		$v_bugnote_updated = $t_bugnote_stats['last_modified'];
	} else {
		$t_bugnote_count = 0;
	}

	echo '<td class="column-bugnotes-count">';
	if( $t_bugnote_count > 0 ) {
		$t_show_in_bold = $v_bugnote_updated > strtotime( '-' . $g_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' );
		if( $t_show_in_bold ) {
			echo '<span class="bold">';
		}
		print_link( string_get_bug_view_url( $p_bug->id ) . '&nbn=' . $t_bugnote_count . '#bugnotes', $t_bugnote_count );
		if( $t_show_in_bold ) {
			echo '</span>';
		}
	} else {
		echo '&#160;';
	}

	echo '</td>';
}

/**
 * Print column content for column attachment count
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_attachment_count( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {

	# Check for attachments
	$t_attachment_count = 0;
	if( file_can_view_bug_attachments( $p_bug->id, null ) ) {
		$t_attachment_count = file_bug_attachment_count( $p_bug->id );
	}

	echo '<td class="column-attachments">';

	if( $t_attachment_count > 0 ) {
		$t_href = string_get_bug_view_url( $p_bug->id ) . '#attachments';
		$t_href_title = sprintf( lang_get( 'view_attachments_for_issue' ), $t_attachment_count, $p_bug->id );
		echo '<a href="' . $t_href . '" title="' . $t_href_title . '">' . $t_attachment_count . '</a>';
	} else {
		echo ' &#160; ';
	}

	echo "</td>\n";
}

/**
 * Print column content for column category id
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_category_id( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_sort, $t_dir;

	# grab the project name
	$t_project_name = project_get_field( $p_bug->project_id, 'name' );

	echo '<td class="column-category">';
	echo '<div class="align-left">';

	# type project name if viewing 'all projects' or if issue is in a subproject
	if( ON == config_get( 'show_bug_project_links' ) && helper_get_current_project() != $p_bug->project_id ) {
		echo '<span class="small project">[';
		print_view_bug_sort_link( string_display_line( $t_project_name ), 'project_id', $t_sort, $t_dir, $p_columns_target );
		echo ']</span>&#160;&#160;';
	}

	echo string_display_line( category_full_name( $p_bug->category_id, false ) );
	echo '</div>';
	echo '</td>';
}

/**
 * Print column content for column severity
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_severity( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-severity">';
	print_formatted_severity_string( $p_bug );
	echo '</td>';
}

/**
 * Print column content for column eta
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_eta( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-eta">', get_enum_element( 'eta', $p_bug->eta, auth_get_current_user_id(), $p_bug->project_id ), '</td>';
}

/**
 * Print column content for column projection
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_projection( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-projection">', get_enum_element( 'projection', $p_bug->projection, auth_get_current_user_id(), $p_bug->project_id ), '</td>';
}

/**
 * Print column content for column reproducibility
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_reproducibility( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-reproducibility">', get_enum_element( 'reproducibility', $p_bug->reproducibility, auth_get_current_user_id(), $p_bug->project_id ), '</td>';
}

/**
 * Print column content for column resolution
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_resolution( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-resolution">',
		get_enum_element( 'resolution', $p_bug->resolution, auth_get_current_user_id(), $p_bug->project_id ),
		'</td>';
}

/**
 * Print column content for column status
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_status( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_current_user = auth_get_current_user_id();
	# choose color based on status
	$t_status_css = html_get_status_css_fg( $p_bug->status, $t_current_user, $p_bug->project_id );
	echo '<td class="column-status">';
	echo '<div class="align-left">';
	echo '<i class="fa fa-square fa-status-box ' . $t_status_css . '"></i> ';
	printf( '<span title="%s">%s</span>',
		get_enum_element( 'resolution', $p_bug->resolution, $t_current_user, $p_bug->project_id ),
		get_enum_element( 'status', $p_bug->status, $t_current_user, $p_bug->project_id )
	);

	# print handler user next to status
	if( $p_bug->handler_id > 0
			&& ON == config_get( 'show_assigned_names', null, $t_current_user, $p_bug->project_id )
			&& access_can_see_handler_for_bug( $p_bug ) ) {
		printf( ' (%s)', prepare_user_name( $p_bug->handler_id ) );
	}
	echo '</div></td>';
}

/**
 * Print column content for column handler id
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_handler_id( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-assigned-to">';

	# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
	# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
	if( $p_bug->handler_id > 0 && ( helper_get_current_project() != ALL_PROJECTS || access_has_project_level( config_get( 'view_handler_threshold' ), $p_bug->project_id ) ) ) {
		echo prepare_user_name( $p_bug->handler_id );
	}

	echo '</td>';
}

/**
 * Print column content for column reporter id
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_reporter_id( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-reporter">';
	echo prepare_user_name( $p_bug->reporter_id );
	echo '</td>';
}

/**
 * Print column content for column project id
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_project_id( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-project-id">';
	echo string_display_line( project_get_name( $p_bug->project_id ) );
	echo '</td>';
}

/**
 * Print column content for column last updated
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_last_updated( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $g_filter;

	$t_last_updated = string_display_line( date( config_get( 'short_date_format' ), $p_bug->last_updated ) );

	echo '<td class="column-last-modified">';
	if( $p_bug->last_updated > strtotime( '-' . $g_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' ) ) {
		printf( '<span class="bold">%s</span>', $t_last_updated );
	} else {
		echo $t_last_updated;
	}
	echo '</td>';
}

/**
 * Print column content for column date submitted
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_date_submitted( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_date_submitted = string_display_line( date( config_get( 'short_date_format' ), $p_bug->date_submitted ) );

	echo '<td class="column-date-submitted">', $t_date_submitted, '</td>';
}

/**
 * Print column content for column summary
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_summary( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE ) {
		$t_summary = string_attribute( $p_bug->summary );
	} else {
		$t_summary = string_display_line_links( $p_bug->summary );
	}

	echo '<td class="column-summary">' . $t_summary . '</td>';
}

/**
 * Print column content for column description
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_description( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_description = string_display_links( $p_bug->description );

	echo '<td class="column-description">', $t_description, '</td>';
}

/**
 * Print column content for notes column
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_notes( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_notes = bugnote_get_all_visible_as_string( $p_bug->id, /* user_bugnote_order */ 'DESC', /* user_bugnote_limit */ 0 );

	echo '<td class="column-notes">', string_display_links( $t_notes ), '</td>';
}

/**
 * Print column content for column steps to reproduce
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_steps_to_reproduce( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_steps_to_reproduce = string_display_links( $p_bug->steps_to_reproduce );

	echo '<td class="column-steps-to-reproduce">', $t_steps_to_reproduce, '</td>';
}

/**
 * Print column content for column additional information
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_additional_information( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_additional_information = string_display_links( $p_bug->additional_information );

	echo '<td class="column-additional-information">', $t_additional_information, '</td>';
}

/**
 * Print column content for column target version
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_target_version( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-target-version">';

	# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
	# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
	if( helper_get_current_project() != ALL_PROJECTS || access_has_project_level( config_get( 'roadmap_view_threshold' ), $p_bug->project_id ) ) {
		echo string_display_line( $p_bug->target_version );
	}

	echo '</td>';
}

/**
 * Print column content for view state column
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_view_state( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {

	echo '<td class="column-view-state">';

	if( VS_PRIVATE == $p_bug->view_state ) {
		$t_view_state_text = lang_get( 'private' );
		echo ' <i class="fa fa-lock" title="' . $t_view_state_text . '"></i>';
	} else {
		echo '&#160;';
	}

	echo '</td>';
}

/**
 * Print column content for column tags
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_tags( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="column-tags">';

	if( access_has_bug_level( config_get( 'tag_view_threshold' ), $p_bug->id ) ) {
		echo string_display_line( tag_bug_get_all( $p_bug->id ) );
	}

	echo '</td>';
}

/**
 * Print column content for column due date
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_due_date( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_overdue = '';

	if( !access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id ) ||
		date_is_null( $p_bug->due_date )
	) {
		$t_value = '&#160;';
	} else {
		if( bug_is_overdue( $p_bug->id ) ) {
			$t_overdue = ' overdue';
		}
		$t_value = string_display_line( date( config_get( 'short_date_format' ), $p_bug->due_date ) );
	}

	printf( '<td class="column-due-date%s">%s</td>', $t_overdue, $t_value );
}

/**
 * Print column content for column overdue
 *
 * @param BugData $p_bug            BugData object.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 * @access public
 */
function print_column_overdue( BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {

	echo '<td class="column-overdue">';

	if( access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id ) &&
		!date_is_null( $p_bug->due_date ) &&
		bug_is_overdue( $p_bug->id ) ) {
		$t_overdue_text = lang_get( 'overdue' );
		$t_overdue_text_hover = sprintf( lang_get( 'overdue_since' ), date( config_get( 'short_date_format' ), $p_bug->due_date ) );
		echo '<i class="fa fa-times-circle-o" title="' . string_display_line( $t_overdue_text_hover ) . '"></i>';
	} else {
		echo '&#160;';
	}

	echo '</td>';
}
