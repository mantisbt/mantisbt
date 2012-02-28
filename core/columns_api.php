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
 * @subpackage ColumnsAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Filters an array of columns based on configuration options.  The filtering can remove
 * columns whose features are disabled.
 *
 * @param array(string) $p_columns  The columns proposed for display.
 * @return array(string) The columns array after removing the disabled features.
 */
function columns_filter_disabled( $p_columns ) {
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
				/* don't filter */
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

			default:
				/* don't filter */
				break;
		}
		$t_columns[] = $t_column;
	} /* continued 2 */

	return $t_columns;
}

/**
 * Get a list of standard columns.
 */
function columns_get_standard() {
	$t_reflection = new ReflectionClass('BugData');
	$t_columns = $t_reflection->getDefaultProperties();

	$t_columns['selection'] = null;
	$t_columns['edit'] = null;

	# Overdue icon column (icons appears if an issue is beyond due_date)
	$t_columns['overdue'] = null;

	if( OFF == config_get( 'enable_profiles' ) ) {
		unset( $t_columns['os'] );
		unset( $t_columns['os_build'] );
		unset( $t_columns['platform'] );
	}

	if( config_get( 'enable_eta' ) == OFF ) {
		unset( $t_columns['eta'] );
	}

	if( config_get( 'enable_projection' ) == OFF ) {
		unset( $t_columns['projection'] );
	}

	if( config_get( 'enable_product_build' ) == OFF ) {
		unset( $t_columns['build'] );
	}

	# The following fields are used internally and don't make sense as columns
	unset( $t_columns['_stats'] );
	unset( $t_columns['profile_id'] );
	unset( $t_columns['sticky'] );
	unset( $t_columns['loading'] );

	return array_keys($t_columns);
}

/**
 * Allow plugins to define a set of class-based columns, and register/load
 * them here to be used by columns_api.
 * @return array Mapping of column name to column object
 */
function columns_get_plugin_columns() {
	static $s_column_array = null;

	if ( is_null( $s_column_array ) ) {
		$s_column_array = array();

		$t_all_plugin_columns = event_signal( 'EVENT_FILTER_COLUMNS' );
		foreach( $t_all_plugin_columns as $t_plugin => $t_plugin_columns ) {
			foreach( $t_plugin_columns as $t_callback => $t_plugin_column_array ) {
				if ( is_array( $t_plugin_column_array ) ) {
					foreach( $t_plugin_column_array as $t_column_class ) {
						if ( class_exists( $t_column_class ) && is_subclass_of( $t_column_class, 'MantisColumn' ) ) {
							$t_column_object = new $t_column_class();
							$t_column_name = utf8_strtolower( $t_plugin . '_' . $t_column_object->column );
							$s_column_array[ $t_column_name ] = $t_column_object;
						}
					}
				}
			}
		}
	}

	return $s_column_array;
}

/**
 * Returns true if the specified $p_column is a plugin column.
 * @param string $p_column A column name.
 */
function column_is_plugin_column( $p_column ) {
	$t_plugin_columns = columns_get_plugin_columns();
	return isset( $t_plugin_columns[ $p_column ] );
}

/**
 * Allow plugin columns to pre-cache data for a set of issues
 * rather than requiring repeated queries for each issue.
 * @param array Bug objects
 */
function columns_plugin_cache_issue_data( $p_bugs ) {
	$t_columns = columns_get_plugin_columns();

	foreach( $t_columns as $t_column_object ) {
		$t_column_object->cache( $p_bugs );
	}
}

/**
 * Get all accessible columns for the current project / current user..
 * @param int $p_project_id project id
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

	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		if( !custom_field_has_read_access_by_project_id( $t_id, $t_project_id ) ) {
			continue;
		}

		$t_def = custom_field_get_definition( $t_id );
		$t_columns[] = 'custom_' . $t_def['name'];
	}

	return $t_columns;
}

/**
 * Checks if the specified column is an extended column.  Extended columns are native columns that are
 * associated with the issue but are saved in mantis_bug_text_table.
 * @param string $p_column The column name
 * @return bool true for extended; false otherwise.
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
		return utf8_substr( $p_column, 7 );
	}

	return null;
}

/**
 * Converts a string of comma separate column names to an array.
 * @param string $p_string - Comma separate column name (not case sensitive)
 * @return array The array with all column names lower case.
 * @access public
 */
function columns_string_to_array( $p_string ) {
	$t_string = utf8_strtolower( $p_string );

	$t_columns = explode( ',', $t_string );
	$t_count = count($t_columns);

	for($i = 0; $i < $t_count; $i++) {
		$t_columns[$i] = trim($t_columns[$i]);
	}

	return $t_columns;
}

/**
 * Gets the localized title for the specified column.  The column can be native or custom.
 * The custom fields must contain the 'custom_' prefix.
 * @param string $p_column - The column name.
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
	if ( isset( $t_plugin_columns[ $p_column ] ) ) {
		$t_column_object = $t_plugin_columns[ $p_column ];
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
 * @param string $p_field_name - The logic name of the array being validated.  Used when triggering errors.
 * @param array $p_columns_to_validate - The array of columns to validate.
 * @param array $p_columns_all - The list of all valid columns.
 * @return bool
 * @access public
 */
function columns_ensure_valid( $p_field_name, $p_columns_to_validate, $p_columns_all ) {
	$t_columns_all_lower = array_map( 'utf8_strtolower', $p_columns_all );

	# Check for invalid fields
	foreach( $p_columns_to_validate as $t_column ) {
		if( !in_array( utf8_strtolower( $t_column ), $t_columns_all_lower ) ) {
			error_parameters( $p_field_name, $t_column );
			trigger_error( ERROR_COLUMNS_INVALID, ERROR );
			return false;
		}
	}

	# Check for duplicate fields
	$t_columns_no_duplicates = array();
	foreach( $p_columns_to_validate as $t_column ) {
		$t_column_lower = utf8_strtolower( $t_column );
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
 * @param array $p_columns - The array of column names to be validated.
 * @param array $p_columns_all - The array of all valid column names.
 * @return array The array of valid column names found in $p_columns.
 * @access public
 */
function columns_remove_invalid( $p_columns, $p_columns_all ) {
	$t_columns_all_lower = array_values( array_map( 'utf8_strtolower', $p_columns_all ) );
	$t_columns = array();

	foreach( $p_columns as $t_column ) {
		if( in_array( utf8_strtolower( $t_column ), $t_columns_all_lower ) ) {
			$t_columns[] = $t_column;
		}
	}

	return $t_columns;
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_selection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td> &#160; </td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_edit( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td> &#160; </td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'id' ), 'id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'id' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_project_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'email_project' ), 'project_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'project_id' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_reporter_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'reporter' ), 'reporter_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'reporter_id' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_handler_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'assigned_to' ), 'handler_id', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'handler_id' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_priority( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'priority_abbreviation' ), 'priority', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'priority' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_reproducibility( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'reproducibility' ), 'reproducibility', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'reproducibility' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_projection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'projection' ), 'projection', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'projection' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_eta( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'eta' ), 'eta', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'eta' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_resolution( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'resolution' ), 'resolution', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'resolution' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_fixed_in_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'fixed_in_version' ), 'fixed_in_version', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'fixed_in_version' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_target_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'target_version' ), 'target_version', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'target_version' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_view_state( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path;
	echo '<td>';
	$t_view_state_text = lang_get( 'view_status' );
	$t_view_state_icon = '<img src="' . $t_icon_path . 'protected.gif" alt="' . $t_view_state_text . '" title="' . $t_view_state_text . '" />';
	print_view_bug_sort_link( $t_view_state_icon, 'view_state', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'view_state' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_os( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'os' ), 'os', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'os' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_os_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'os_version' ), 'os_build', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'os_build' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'build' ), 'build', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'build' );
		echo '</td>';
	} else {
		echo lang_get( 'build' );
	}
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_platform( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'platform' ), 'platform', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'platform' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'product_version' ), 'version', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'version' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_date_submitted( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'date_submitted' ), 'date_submitted', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'date_submitted' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_attachment_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path;
	$t_attachment_count_text = lang_get( 'attachment_count' );
	$t_attachment_count_icon = "<img src=\"${t_icon_path}attachment.png\" alt=\"$t_attachment_count_text\" title=\"$t_attachment_count_text\" />";
	echo "\t<td>$t_attachment_count_icon</td>\n";
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_category( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'category' ), 'category', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'category' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_sponsorship_total( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo "\t<td>";
	print_view_bug_sort_link( sponsorship_get_currency(), 'sponsorship_total', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'sponsorship_total' );
	echo "</td>\n";
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_severity( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'severity' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_status( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'status' ), 'status', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'status' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_last_updated( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'last_updated' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_summary( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'summary' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_bugnotes_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td> # </td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_description( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	echo lang_get( 'description' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_steps_to_reproduce( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	echo lang_get( 'steps_to_reproduce' );
	echo '</td>';
}

/**
 *
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_title_additional_information( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	echo lang_get( 'additional_information' );
	echo '</td>';
}

function print_column_title_overdue( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path;
	echo '<td>';
	$t_overdue_text = lang_get( 'overdue' );
	$t_overdue_icon = '<img src="' . $t_icon_path . 'overdue.png" alt="' . $t_overdue_text . '" title="' . $t_overdue_text . '" />';
	print_view_bug_sort_link( $t_overdue_icon, 'due_date', $p_sort, $p_dir, $p_columns_target );
	print_sort_icon( $p_dir, $p_sort, 'due_date' );
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_selection( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $g_checkboxes_exist;

	echo '<td>';
	if( access_has_any_project( config_get( 'report_bug_threshold', null, null, $p_bug->project_id ) ) ||
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
		printf( "<input type=\"checkbox\" name=\"bug_arr[]\" value=\"%d\" />", $p_bug->id );
	} else {
		echo "&#160;";
	}
	echo '</td>';
}

/**
 * Print column title for a specific custom column.
 * @param object Column object
 * @param string sort
 * @param string direction
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @access public
 */
function print_column_title_plugin( $p_column, $p_column_object, $p_sort, $p_dir, $p_columns_target=COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	if ( $p_column_object->sortable ) {
		print_view_bug_sort_link( string_display_line( $p_column_object->title ), $p_column, $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, $p_column );
	} else {
		echo string_display_line( $p_column_object->title );
	}
	echo '</td>';
}

/**
 * Print custom column content for a specific bug.
 * @param object Column object
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @access public
 */
function print_column_plugin( $p_column_object, $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if ( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
		echo '<td>';
		$p_column_object->display( $p_bug, $p_columns_target );
		echo '</td>';
	} else {
		$p_column_object->display( $p_bug, $p_columns_target );
	}
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_edit( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path, $t_update_bug_threshold;

	echo '<td>';

	if( !bug_is_readonly( $p_bug->id ) && access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug->id ) ) {
		echo '<a href="' . string_get_bug_update_url( $p_bug->id ) . '">';
		echo '<img border="0" width="16" height="16" src="' . $t_icon_path . 'update.png';
		echo '" alt="' . lang_get( 'update_bug_button' ) . '"';
		echo ' title="' . lang_get( 'update_bug_button' ) . '" /></a>';
	} else {
		echo '&#160;';
	}

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_priority( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	if( ON == config_get( 'show_priority_text' ) ) {
		print_formatted_priority_string( $p_bug );
	} else {
		print_status_icon( $p_bug->priority );
	}
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_id( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';
	print_bug_link( $p_bug->id, false );
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_sponsorship_total( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo "\t<td class=\"right\">";

	if( $p_bug->sponsorship_total > 0 ) {
		$t_sponsorship_amount = sponsorship_format_amount( $p_bug->sponsorship_total );
		echo string_no_break( $t_sponsorship_amount );
	}

	echo "</td>\n";
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_bugnotes_count( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_filter;

	# grab the bugnote count
	$t_bugnote_stats = bug_get_bugnote_stats( $p_bug->id );
	if( NULL !== $t_bugnote_stats ) {
		$bugnote_count = $t_bugnote_stats['count'];
		$v_bugnote_updated = $t_bugnote_stats['last_modified'];
	} else {
		$bugnote_count = 0;
	}

	echo '<td class="center">';
	if( $bugnote_count > 0 ) {
		$t_show_in_bold = $v_bugnote_updated > strtotime( '-' . $t_filter['highlight_changed'] . ' hours' );
		if( $t_show_in_bold ) {
			echo '<span class="bold">';
		}
		print_link( string_get_bug_view_url( $p_bug->id ) . "&nbn=$bugnote_count#bugnotes", $bugnote_count );
		if( $t_show_in_bold ) {
			echo '</span>';
		}
	} else {
		echo '&#160;';
	}

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_attachment_count( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path;

	# Check for attachments
	$t_attachment_count = 0;
	if( file_can_view_bug_attachments( $p_bug->id, null ) ) {
		$t_attachment_count = file_bug_attachment_count( $p_bug->id );
	}

	echo '<td class="center">';

	if ( $t_attachment_count > 0 ) {
		$t_href = string_get_bug_view_url( $p_bug->id ) . '#attachments';
		$t_href_title = sprintf( lang_get( 'view_attachments_for_issue' ), $t_attachment_count, $p_bug->id );
		echo "<a href=\"$t_href\" title=\"$t_href_title\">$t_attachment_count</a>";
	} else {
		echo ' &#160; ';
	}

	echo "</td>\n";
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_category_id( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_sort, $t_dir;

	# grab the project name
	$t_project_name = project_get_field( $p_bug->project_id, 'name' );

	echo '<td class="center">';

	# type project name if viewing 'all projects' or if issue is in a subproject
 	if( ON == config_get( 'show_bug_project_links' ) && helper_get_current_project() != $p_bug->project_id ) {
		echo '<small>[';
		print_view_bug_sort_link( string_display_line( $t_project_name ), 'project_id', $t_sort, $t_dir, $p_columns_target );
		echo ']</small><br />';
	}

	echo string_display_line( category_full_name( $p_bug->category_id, false ) );

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_severity( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">';
	print_formatted_severity_string( $p_bug );
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_eta( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">', get_enum_element( 'eta', $p_bug->eta, auth_get_current_user_id(), $p_bug->project_id ), '</td>';
}

/**
 *
 * @param BugData $p_bug bug object
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_projection( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">', get_enum_element( 'projection', $p_bug->projection, auth_get_current_user_id(), $p_bug->project_id ), '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_reproducibility( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">', get_enum_element( 'reproducibility', $p_bug->reproducibility, auth_get_current_user_id(), $p_bug->project_id ), '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_resolution( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">',
		get_enum_element( 'resolution', $p_bug->resolution, auth_get_current_user_id(), $p_bug->project_id ),
		'</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_status( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">';
	printf( '<span class="issue-status" title="%s">%s</span>',
		get_enum_element( 'resolution', $p_bug->resolution, auth_get_current_user_id(), $p_bug->project_id ),
		get_enum_element( 'status', $p_bug->status, auth_get_current_user_id(), $p_bug->project_id )
	);

	# print username instead of status
	if(( ON == config_get( 'show_assigned_names' ) ) && ( $p_bug->handler_id > 0 ) && ( access_has_project_level( config_get( 'view_handler_threshold' ), $p_bug->project_id ) ) ) {
		printf( ' (%s)', prepare_user_name( $p_bug->handler_id ) );
	}
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_handler_id( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">';

	# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
	# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
	if( $p_bug->handler_id > 0 && ( helper_get_current_project() != ALL_PROJECTS || access_has_project_level( config_get( 'view_handler_threshold' ), $p_bug->project_id ) ) ) {
		echo prepare_user_name( $p_bug->handler_id );
	}

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_reporter_id( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">';
	echo prepare_user_name( $p_bug->reporter_id );
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_project_id( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td class="center">';
	echo string_display_line( project_get_name( $p_bug->project_id ) );
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_last_updated( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_filter;

	$t_last_updated = string_display_line( date( config_get( 'short_date_format' ), $p_bug->last_updated ) );

	echo '<td class="center">';
	if( $p_bug->last_updated > strtotime( '-' . $t_filter['highlight_changed'] . ' hours' ) ) {
		printf( '<span class="bold">%s</span>', $t_last_updated );
	} else {
		echo $t_last_updated;
	}
	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_date_submitted( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_date_submitted = string_display_line( date( config_get( 'short_date_format' ), $p_bug->date_submitted ) );

	echo '<td class="center">', $t_date_submitted, '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_summary( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE ) {
		$t_summary = string_attribute( $p_bug->summary );
	} else {
		$t_summary = string_display_line_links( $p_bug->summary );
	}

	echo '<td class="left">' . $t_summary . '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_description( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_description = string_display_links( $p_bug->description );

	echo '<td class="left">', $t_description, '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_steps_to_reproduce( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_steps_to_reproduce = string_display_links( $p_bug->steps_to_reproduce );

	echo '<td class="left">', $t_steps_to_reproduce, '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_additional_information( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	$t_additional_information = string_display_links( $p_bug->additional_information );

	echo '<td class="left">', $t_additional_information, '</td>';
}

/**
 *
 * @param BugData $p_bug bug obect
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_target_version( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	echo '<td>';

	# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
	# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
	if( helper_get_current_project() != ALL_PROJECTS || access_has_project_level( config_get( 'roadmap_view_threshold' ), $p_bug->project_id ) ) {
		echo string_display_line( $p_bug->target_version );
	}

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug object
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_view_state( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path;

	echo '<td>';

	if( VS_PRIVATE == $p_bug->view_state ) {
		$t_view_state_text = lang_get( 'private' );
		echo '<img src="' . $t_icon_path . 'protected.gif" alt="' . $t_view_state_text . '" title="' . $t_view_state_text . '" />';
	} else {
		echo '&#160;';
	}

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug object
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_due_date( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if ( !access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id ) ||
		date_is_null( $p_bug->due_date ) ) {
		echo '<td>&#160;</td>';
		return;
	}

	if ( bug_is_overdue( $p_bug->id ) ) {
		echo '<td class="overdue">';
	} else {
		echo '<td>';
	}

	echo string_display_line( date( config_get( 'short_date_format' ), $p_bug->due_date ) );

	echo '</td>';
}

/**
 *
 * @param BugData $p_bug bug object
 * @param int $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
 * @return null
 * @access public
 */
function print_column_overdue( $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	global $t_icon_path;

	echo '<td>';

	if ( access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id ) &&
		!date_is_null( $p_bug->due_date ) &&
		bug_is_overdue( $p_bug->id ) ) {
		$t_overdue_text = lang_get( 'overdue' );
		$t_overdue_text_hover = $t_overdue_text . '. Due date was: ' . string_display_line( date( config_get( 'short_date_format' ), $p_bug->due_date ) );
		echo '<img src="' . $t_icon_path . 'overdue.png" alt="' . $t_overdue_text . '" title="' . $t_overdue_text_hover . '" />';
	} else {
		echo '&#160;';
	}

	echo '</td>';
}
