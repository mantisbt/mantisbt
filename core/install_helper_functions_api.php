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
 * Install Helper Functions API
 *
 * @package CoreAPI
 * @subpackage InstallHelperFunctionsAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses database_api.php
 */

require_api( 'database_api.php' );

/**
 * Checks a PHP version number against the version of PHP currently in use
 * @param string $p_version Version string to compare.
 * @return boolean true if the PHP version in use is equal to or greater than the supplied version string
 */
function check_php_version( $p_version ) {
	if( $p_version == PHP_MIN_VERSION ) {
		return true;
	} else {
		if( function_exists( 'version_compare' ) ) {
			if( version_compare( phpversion(), PHP_MIN_VERSION, '>=' ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

/**
 * Legacy pre-1.2 date function used for upgrading from datetime to integer
 * representation of dates in the database.
 * @return string Formatted date representing unixtime(0) + 1 second, ready for database insertion
 */
function db_null_date() {
	global $g_db;

	return $g_db->BindTimestamp( $g_db->UserTimeStamp( 1, 'Y-m-d H:i:s', true ) );
}

/**
 * Legacy pre-1.2 date function used for upgrading from datetime to integer
 * representation of dates in the database. This function converts a formatted
 * datetime string to an that represents the number of seconds elapsed since
 * the Unix epoch.
 * @param string  $p_date Formatted datetime string from a database.
 * @param boolean $p_gmt  Whether to use UTC (true) or server timezone (false, default).
 * @return integer Unix timestamp representation of a datetime string
 */
function db_unixtimestamp( $p_date = null, $p_gmt = false ) {
	global $g_db;

	if( null !== $p_date ) {
		$p_timestamp = $g_db->UnixTimeStamp( $p_date, $p_gmt );
	} else {
		$p_timestamp = time();
	}
	return $p_timestamp;
}

/**
 * Legacy date function for installer backwards compatibility
 * @return string
 */
function installer_db_now() {
	global $g_db;

	# Timezone must not be set to UTC prior to calling BindTimestamp(), as
	# ADOdb assumes a local timestamp and does the UTC conversion itself.
	return $g_db->BindTimeStamp( time() );
}

/**
 * Check PostgreSQL boolean columns' type in the DB
 * Verifies that columns defined as type "L" (logical) in the Mantis schema
 * have the correct type in the underlying database.
 * The ADOdb library bundled with MantisBT releases prior to 1.1.0 (schema
 * version 51) created type "L" columns in PostgreSQL as SMALLINT, whereas later
 * versions created them as BOOLEAN.
 * @return mixed true if columns check OK
 *               error message string if errors occured
 *               array of invalid columns otherwise (empty if all columns check OK)
 */
function check_pgsql_bool_columns() {
	global $f_db_type, $f_database_name;
	global $g_db;

	# Only applies to PostgreSQL
	if( $f_db_type != 'pgsql' ) {
		return true;
	}

	# Build the list of "L" type columns as of schema version 51
	$t_bool_columns = array(
		'bug'             => array( 'sticky' ),
		'custom_field'    => array( 'advanced', 'require_report', 'require_update', 'display_report', 'display_update', 'require_resolved', 'display_resolved', 'display_closed', 'require_closed' ),
		'filters'         => array( 'is_public' ),
		'news'            => array( 'announcement' ),
		'project'         => array( 'enabled' ),
		'project_version' => array( 'released' ),
		'sponsorship'     => array( 'paid' ),
		'user_pref'       => array( 'advanced_report', 'advanced_view', 'advanced_update', 'redirect_delay', 'email_on_new', 'email_on_assigned', 'email_on_feedback', 'email_on_resolved', 'email_on_closed', 'email_on_reopened', 'email_on_bugnote', 'email_on_status', 'email_on_priority' ),
		'user'            => array( 'enabled', 'protected' ),
	);

	# Generate SQL to check columns against schema
	$t_where = '';
	foreach( $t_bool_columns as $t_table_name => $t_columns ) {
		$t_table = db_get_table( $t_table_name );
		$t_where .= 'table_name = \'' . $t_table . '\' AND column_name IN ( \''
			. implode( $t_columns, '\', \'' )
			. '\' ) OR ';
	}
	$t_sql = 'SELECT table_name, column_name, data_type, column_default, is_nullable
		FROM information_schema.columns
		WHERE
			table_catalog = \'' . $f_database_name . '\' AND
			data_type <> \'boolean\' AND
			(' . rtrim( $t_where, ' OR' ) . ')';

	$t_result = @$g_db->Execute( $t_sql );
	if( $t_result === false ) {
		return 'Unable to check information_schema';
	} else if( $t_result->RecordCount() == 0 ) {
		return array();
	}

	# Some columns are not BOOLEAN type, return the list
	return $t_result->GetArray();
}

/**
 * Set the value of $g_db_log_queries as specified
 * This is used by install callback functions to ensure that only the relevant
 * queries are logged
 * @global integer $g_db_log_queries
 * @param integer $p_new_state New value to set $g_db_log_queries to (defaults to OFF).
 * @return integer old value of $g_db_log_queries
 */
function install_set_log_queries( $p_new_state = OFF ) {
	global $g_db_log_queries;

	$t_log_queries = $g_db_log_queries;

	if( $g_db_log_queries !== $p_new_state ) {
		$g_db_log_queries = $p_new_state;
	}

	# Return the old value of $g_db_log_queries
	return $t_log_queries;
}

/**
 * Migrate the legacy category data to the new category_id-based schema.
 * @return integer
 */
function install_category_migrate() {
	# Disable query logging even if enabled in config, due to possibility of mass spam
	$t_log_queries = install_set_log_queries();

	$t_query = 'SELECT project_id, category, user_id FROM {project_category} ORDER BY project_id, category';
	$t_category_result = db_query( $t_query );

	$t_query = 'SELECT project_id, category FROM {bug} ORDER BY project_id, category';
	$t_bug_result = db_query( $t_query );

	$t_data = array();

	# Find categories specified by project
	while( $t_row = db_fetch_array( $t_category_result ) ) {
		$t_project_id = $t_row['project_id'];
		$t_name = $t_row['category'];
		$t_data[$t_project_id][$t_name] = $t_row['user_id'];
	}

	# Find orphaned categories from bugs
	while( $t_row = db_fetch_array( $t_bug_result ) ) {
		$t_project_id = $t_row['project_id'];
		$t_name = $t_row['category'];

		if( !isset( $t_data[$t_project_id][$t_name] ) ) {
			$t_data[$t_project_id][$t_name] = 0;
		}
	}

	# In every project, go through all the categories found, and create them and update the bug
	foreach( $t_data as $t_project_id => $t_categories ) {
		$t_inserted = array();
		foreach( $t_categories as $t_name => $t_user_id ) {
			$t_lower_name = utf8_strtolower( trim( $t_name ) );
			if( !isset( $t_inserted[$t_lower_name] ) ) {
				db_param_push();
				$t_query = 'INSERT INTO {category} ( name, project_id, user_id ) VALUES ( ' .
					db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
				db_query( $t_query, array( $t_name, $t_project_id, $t_user_id ) );
				$t_category_id = db_insert_id( db_get_table( 'category' ) );
				$t_inserted[$t_lower_name] = $t_category_id;
			} else {
				$t_category_id = $t_inserted[$t_lower_name];
			}

			db_param_push();
			$t_query = 'UPDATE {bug} SET category_id=' . db_param() . '
						WHERE project_id=' . db_param() . ' AND category=' . db_param();
			db_query( $t_query, array( $t_category_id, $t_project_id, $t_name ) );
		}
	}

	# Re-enable query logging if we disabled it
	install_set_log_queries( $t_log_queries );

	# return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 * Migrate the legacy date format.
 * @param array $p_data Array: [0] = tablename, [1] id column, [2] = old column, [3] = new column.
 * @return integer
 */
function install_date_migrate( array $p_data ) {
	# $p_data[0] = tablename, [1] id column, [2] = old column, [3] = new column

	# Disable query logging even if enabled in config, due to possibility of mass spam
	$t_log_queries = install_set_log_queries();

	$t_table = $p_data[0];
	$t_id_column = $p_data[1];
	$t_date_array = is_array( $p_data[2] );

	if( $t_date_array ) {
		$t_old_column = implode( ',', $p_data[2] );
		$t_cnt_fields = count( $p_data[2] );
		$t_query = 'SELECT ' . $t_id_column . ', ' . $t_old_column . ' FROM ' . $t_table;

		$t_first_column = true;

		# In order to handle large databases where we may timeout during the upgrade, we don't
		# start from the beginning everytime.  Here we will only pickup rows where at least one
		# of the datetime fields wasn't upgraded yet and upgrade them all.
		foreach ( $p_data[3] as $t_new_column_name ) {
			if( $t_first_column ) {
				$t_first_column = false;
				$t_query .= ' WHERE ';
			} else {
				$t_query .= ' OR ';
			}

			$t_query .= $t_new_column_name. ' = 1';
		}
	} else {
		$t_old_column = $p_data[2];

		# The check for timestamp being = 1 is to make sure the field wasn't upgraded
		# already in a previous run - see bug #12601 for more details.
		$t_new_column_name = $p_data[3];
		$t_query = 'SELECT ' . $t_id_column . ', ' . $t_old_column . ' FROM ' . $t_table . ' WHERE ' . $t_new_column_name . ' = 1';
	}

	$t_result = db_query( $t_query );

	if( db_num_rows( $t_result ) > 0 ) {
		db_param_push();
		# Build the update query
		if( $t_date_array ) {
			$t_pairs = array();
			foreach( $p_data[3] as $t_var ) {
				array_push( $t_pairs, $t_var . '=' . db_param() ) ;
			}
			$t_new_column = implode( ',', $t_pairs );
		} else {
			$t_new_column = $p_data[3] . '=' . db_param();
		}
		$t_query = 'UPDATE ' . $t_table . ' SET ' . $t_new_column . ' WHERE ' . $t_id_column . '=' . db_param();

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_id = (int)$t_row[$t_id_column];

			if( $t_date_array ) {
				for( $i=0; $i < $t_cnt_fields; $i++ ) {
					$t_old_value = $t_row[$p_data[2][$i]];

					if( is_numeric( $t_old_value ) ) {
						return 1; # Fatal: conversion may have already been run. If it has been run, proceeding will wipe timestamps from db
					}

					$t_new_value[$i] = db_unixtimestamp( $t_old_value );
					if( $t_new_value[$i] < 100000 ) {
						$t_new_value[$i] = 1;
					}
				}
				$t_values = $t_new_value;
				$t_values[] = $t_id;
			} else {
				$t_old_value = $t_row[$t_old_column];

				if( is_numeric( $t_old_value ) ) {
					return 1; # Fatal: conversion may have already been run. If it has been run, proceeding will wipe timestamps from db
				}

				$t_new_value = db_unixtimestamp( $t_old_value );
				if( $t_new_value < 100000 ) {
					$t_new_value = 1;
				}
				$t_values = array( $t_new_value, $t_id );
			}

			# Don't pop params since we're in a loop
			db_query( $t_query, $t_values, -1, -1, false );
		}
		db_param_pop();
		
	}

	# Re-enable query logging if we disabled it
	install_set_log_queries( $t_log_queries );

	# return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;

}

/**
 * Once upon a time multi-select custom field types (checkbox and multiselect)
 * were stored in the database in the format of "option1|option2|option3" where
 * they should have been stored in a format of "|option1|option2|option3|".
 * Additionally, radio custom field types were being stored in the database
 * with an unnecessary vertical pipe prefix and suffix when there is only ever
 * one possible value that can be assigned to a radio field.
 * @return integer
 */
function install_correct_multiselect_custom_fields_db_format() {
	# Disable query logging even if enabled in config, due to possibility of mass spam
	$t_log_queries = install_set_log_queries();

	# Ensure multilist and checkbox custom field values have a vertical pipe |
	# as a prefix and suffix.
	$t_query = 'SELECT v.field_id, v.bug_id, v.value from {custom_field_string} v
		LEFT JOIN {custom_field} c
		ON v.field_id = c.id
		WHERE (c.type = ' . CUSTOM_FIELD_TYPE_MULTILIST . ' OR c.type = ' . CUSTOM_FIELD_TYPE_CHECKBOX . ")
			AND v.value != ''
			AND v.value NOT LIKE '|%|'";
	$t_result = db_query( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_field_id = (int)$t_row['field_id'];
		$c_bug_id = (int)$t_row['bug_id'];
		$c_value = '|' . rtrim( ltrim( $t_row['value'], '|' ), '|' ) . '|';
		$t_update_query = 'UPDATE {custom_field_string}
			SET value = \'' . $c_value . '\'
			WHERE field_id = ' . $c_field_id . '
				AND bug_id = ' . $c_bug_id;
		db_query( $t_update_query );
	}

	# Remove vertical pipe | prefix and suffix from radio custom field values.
	$t_query = 'SELECT v.field_id, v.bug_id, v.value from {custom_field_string} v
		LEFT JOIN {custom_field} c
		ON v.field_id = c.id
		WHERE c.type = ' . CUSTOM_FIELD_TYPE_RADIO . "
			AND v.value != ''
			AND v.value LIKE '|%|'";
	$t_result = db_query( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_field_id = (int)$t_row['field_id'];
		$c_bug_id = (int)$t_row['bug_id'];
		$c_value = rtrim( ltrim( $t_row['value'], '|' ), '|' );
		$t_update_query = 'UPDATE {custom_field_string}
			SET value = \'' . $c_value . '\'
			WHERE field_id = ' . $c_field_id . '
				AND bug_id = ' . $c_bug_id;
		db_query( $t_update_query );
	}

	# Re-enable query logging if we disabled it
	install_set_log_queries( $t_log_queries );

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 *	The filters have been changed so the field names are the same as the database
 *	field names.  This updates any filters stored in the database to use the correct
 *	keys. The 'and_not_assigned' field is no longer used as it is replaced by the meta
 *	filter None.  This removes it from all filters.
 *
 * Filter Versions:
 * v1,2,3,4 - Legacy Filters that can not be migrated (not used since 2004)
 * v5 - https://github.com/mantisbt/mantisbt/commit/eb1b93057e470e40727bc75a85f436ab35b84a74
 * v6 - https://github.com/mantisbt/mantisbt/commit/de2e2931f993c3b6fc82781eff051f9037fdc6b5
 * v7 - https://github.com/mantisbt/mantisbt/commit/0450981225647544083d21576dfb2bae044b3e98
 * v8 - https://github.com/mantisbt/mantisbt/commit/5cb368796528bcb35aa3935bf431b08a29cb1e90
 * v9 - https://github.com/mantisbt/mantisbt/commit/9dfc5fb6edb6da1e0324ceac3a27a727f2b23ba7
 *
 * Filters are stored within the database as vX#FILTER, where vX is a raw version string and
 * FILTER is a serialized string in php serialization or json format.
 *
 * This function is used to upgrade any previous filters to the latest version, and should be
 * updated when bouncing filter version number. Schema.php should be updated to call do_nothing
 * for the existing filter schema update and the updated version of this function called in a
 * new schema update step
 *
 * @return integer
 */
function install_stored_filter_migrate() {
	# Disable query logging even if enabled in config, due to possibility of mass spam
	$t_log_queries = install_set_log_queries();

	require_api( 'filter_api.php' );

	# convert filters to use the same value for the filter key and the form field
	# Note: This list should only be updated for basic renames i.e. data + type of data remain the same
	# before and after the rename.
	$t_filter_fields['show_category'] = 'category_id';
	$t_filter_fields['show_severity'] = 'severity';
	$t_filter_fields['show_status'] = 'status';
	$t_filter_fields['show_priority'] = 'priority';
	$t_filter_fields['show_resolution'] = 'resolution';
	$t_filter_fields['show_build'] = 'build';
	$t_filter_fields['show_version'] = 'version';
	$t_filter_fields['user_monitor'] = 'monitor_user_id';
	$t_filter_fields['show_profile'] = 'profile_id';
	$t_filter_fields['do_filter_by_date'] = 'filter_by_date';
	$t_filter_fields['and_not_assigned'] = null;
	$t_filter_fields['sticky_issues'] = 'sticky';

	$t_query = 'SELECT * FROM {filters}';
	$t_result = db_query( $t_query );
	while( $t_row = db_fetch_array( $t_result ) ) {
		# Grab Filter Version and data into $t_setting_arr
		$t_setting_arr = explode( '#', $t_row['filter_string'], 2 );

		switch( $t_setting_arr[0] ) {
			# Remove any non-upgradeable filters i.e. versions 1 to 4.
			case 'v1':
			case 'v2':
			case 'v3':
			case 'v4':
				db_param_push();
				$t_delete_query = 'DELETE FROM {filters} WHERE id=' . db_param();
				$t_delete_result = db_query( $t_delete_query, array( $t_row['id'] ) );
				continue;
		}

		if( isset( $t_setting_arr[1] ) ) {
			switch( $t_setting_arr[0] ) {
				# Filter versions 5 to 8 are stored in php serialized format
				case 'v5':
				case 'v6':
				case 'v7':
				case 'v8':
					$t_filter_arr = unserialize( $t_setting_arr[1] );
					break;
				default:
					$t_filter_arr = json_decode( $t_setting_arr[1], /* assoc array */ true );
			}
		} else {
			db_param_push();
			$t_delete_query = 'DELETE FROM {filters} WHERE id=' . db_param();
			$t_delete_result = db_query( $t_delete_query, array( $t_row['id'] ) );
			continue;
		}

		# serialized or json encoded data in filter table is invalid - abort upgrade as this should not be possible
		# so we should investigate and fix underlying data issue first if necessary
		if( $t_filter_arr === false ) {
			return 1; # Fatal: invalid data found in filters table
		}

		# Ff the filter version does not match the latest version, pass it through filter_ensure_valid_filter to do any updates
		# This will set default values for filter fields
		if( $t_filter_arr['_version'] != FILTER_VERSION ) {
			$t_filter_arr = filter_ensure_valid_filter( $t_filter_arr );
		}

		# For any fields that are being renamed, we can now perform the rename and migrate existing data.
		# We unset the old field when done to ensure the filter contains only current optimised data.
		foreach( $t_filter_fields AS $t_old=>$t_new ) {
			if( isset( $t_filter_arr[$t_old] ) ) {
				$t_value = $t_filter_arr[$t_old];
				unset( $t_filter_arr[$t_old] );
				if( !is_null( $t_new ) ) {
					$t_filter_arr[$t_new] = $t_value;
				}
			}
		}

		# We now have a valid filter in with updated version number (version is updated by filter_ensure_valid_filter)
		# Check that this is the case, to before storing the updated filter values.
		# Abort if the filter is invalid as this should not be possible
		if( $t_filter_arr['_version'] != FILTER_VERSION ) {
			return 1; # Fatal: invalid data found in filters table
		}

		$t_filter_serialized = json_encode( $t_filter_arr );
		$t_filter_string = FILTER_VERSION . '#' . $t_filter_serialized;

		db_param_push();
		$t_update_query = 'UPDATE {filters} SET filter_string=' . db_param() . ' WHERE id=' . db_param();
		$t_update_result = db_query( $t_update_query, array( $t_filter_string, $t_row['id'] ) );
	}

	# Re-enable query logging if we disabled it
	install_set_log_queries( $t_log_queries );

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 * History table's field name column used to be 32 chars long (before 1.1.0a4),
 * while custom field names can be up to 64. This function updates history
 * records related to long custom fields to store the complete field name
 * instead of the truncated version
 *
 *
 * @return integer 2, because that's what ADOdb/DataDict does when things happen properly
 */
function install_update_history_long_custom_fields() {
	# Disable query logging even if enabled in config, due to possibility of mass spam
	$t_log_queries = install_set_log_queries();

	# Build list of custom field names longer than 32 chars for reference
	$t_query = 'SELECT name FROM {custom_field}';
	$t_result = db_query( $t_query );
	while( $t_field = db_fetch_array( $t_result ) ) {
		if( utf8_strlen( $t_field['name'] ) > 32 ) {
			$t_custom_fields[utf8_substr( $t_field['name'], 0, 32 )] = $t_field['name'];
		}
	}
	if( !isset( $t_custom_fields ) ) {
		# There are no custom fields longer than 32, nothing to do

		# Re-enable query logging if we disabled it
		install_set_log_queries( $t_log_queries );
		return 2;
	}

	# Build list of standard fields to filter out from history
	# This is as per result of columns_get_standard() at the time of this schema update
	# Fields mapping: category_id is actually logged in history as 'category'
	$t_standard_fields = array( 'id', 'project_id', 'reporter_id', 'handler_id', 'priority', 'severity', 'eta', 'os',
								'reproducibility', 'status', 'resolution', 'projection', 'category', 'date_submitted',
								'last_updated', 'os_build', 'platform', 'version', 'fixed_in_version', 'target_version',
								'build', 'view_state', 'summary', 'sponsorship_total', 'due_date', 'description',
								'steps_to_reproduce', 'additional_information', 'attachment_count', 'bugnotes_count',
								'selection', 'edit', 'overdue' );
	$t_field_list = '';
	foreach( $t_standard_fields as $t_field ) {
		$t_field_list .= '\'' . $t_field . '\', ';
	}
	$t_field_list = rtrim( $t_field_list, ', ' );

	# Get the list of custom fields from the history table
	$t_query = 'SELECT DISTINCT field_name
		FROM {bug_history}
		WHERE type = ' . NORMAL_TYPE . '
		AND field_name NOT IN ( ' . $t_field_list . ' )';
	$t_result = db_query( $t_query );

	# For each entry, update the truncated custom field name with its full name
	# if a matching custom field exists
	while( $t_field = db_fetch_array( $t_result ) ) {
		# If field name's length is 32, then likely it was truncated so we try to match
		if( utf8_strlen( $t_field['field_name'] ) == 32 && array_key_exists( $t_field['field_name'], $t_custom_fields ) ) {
			# Match found, update all history records with this field name
			db_param_push();
			$t_update_query = 'UPDATE {bug_history}
				SET field_name = ' . db_param() . '
				WHERE field_name = ' . db_param();
			db_query( $t_update_query, array( $t_custom_fields[$t_field['field_name']], $t_field['field_name'] ) );
		}
	}

	# Re-enable query logging if we disabled it
	install_set_log_queries( $t_log_queries );

	return 2;
}

/**
 * Schema update to check that project hierarchy was valid
 * @return integer
 */
function install_check_project_hierarchy() {
	$t_query = 'SELECT count(child_id) as count, child_id, parent_id FROM {project_hierarchy} GROUP BY child_id, parent_id';

	$t_result = db_query( $t_query );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_count = (int)$t_row['count'];
		$t_child_id = (int)$t_row['child_id'];
		$t_parent_id = (int)$t_row['parent_id'];

		if( $t_count > 1 ) {
			db_param_push();
			$t_query = 'SELECT inherit_parent, child_id, parent_id FROM {project_hierarchy} WHERE child_id=' . db_param() . ' AND parent_id=' . db_param();
			$t_result2 = db_query( $t_query, array( $t_child_id, $t_parent_id ) );

			# get first result for inherit_parent, discard the rest
			$t_row2 = db_fetch_array( $t_result2 );

			$t_inherit = $t_row2['inherit_parent'];

			db_param_push();
			$t_query_delete = 'DELETE FROM {project_hierarchy} WHERE child_id=' . db_param() . ' AND parent_id=' . db_param();
			db_query( $t_query_delete, array( $t_child_id, $t_parent_id ) );

			db_param_push();
			$t_query_insert = 'INSERT INTO {project_hierarchy} (child_id, parent_id, inherit_parent) VALUES (' . db_param() . ',' . db_param() . ',' . db_param() . ')';
			db_query( $t_query_insert, array( $t_child_id, $t_parent_id, $t_inherit ) );
		}
	}

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 * Schema update to migrate config data from php serialization to json.
 * This ensures it is not possible to execute code during un-serialization
 */
function install_check_config_serialization() {
	$query = 'SELECT * FROM {config} WHERE type=3';

	$t_result = db_query( $query );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$config_id = $t_row['config_id'];
		$project_id = (int)$t_row['project_id'];
		$user_id = (int)$t_row['user_id'];
		$value = $t_row['value'];

		$t_config = unserialize( $value );
		if( $t_config === false ) {
			return 1; # Fatal: invalid data found in config table
		}

		$t_json_config = json_encode( $t_config );

		db_param_push();
		$t_query = 'UPDATE {config} SET value=' .db_param() . ' WHERE config_id=' .db_param() . ' AND project_id=' .db_param() . ' AND user_id=' .db_param();
		db_query( $t_query, array( $t_json_config, $config_id, $project_id, $user_id ) );
	}

	# flush config here as we've changed the format of the configuration table
	config_flush_cache();

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 * Schema update to migrate token data from php serialization to json.
 * This ensures it is not possible to execute code during un-serialization
 */
function install_check_token_serialization() {
	$query = 'SELECT * FROM {tokens} WHERE type=1 or type=2 or type=5';

	$t_result = db_query( $query );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_id = $t_row['id'];
		$t_value = $t_row['value'];

		if ( $t_value === null ) {
			$t_token = null;
		} else {
			$t_token = @unserialize( $t_value );
			if( $t_token === false ) {
				# If user hits a page other than install, tokens may be created using new code.
				$t_token = json_decode( $t_value );
				if( $t_token !== null ) {
					continue;
				}

				return 1; # Fatal: invalid data found in tokens table
			}
		}

		$t_json_token = json_encode( $t_token );

		db_param_push();
		$t_query = 'UPDATE {tokens} SET value=' .db_param() . ' WHERE id=' .db_param();
		db_query( $t_query, array( $t_json_token, $t_id ) );
	}

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 * Schema update to install and configure new Gravatar plugin.
 * If the instance has enabled use of avatars, then we register the plugin
 * @return int 2 if successful
 */
function install_gravatar_plugin() {
	if( config_get_global( 'show_avatar' ) ) {
		$t_avatar_plugin = 'Gravatar';

		# Register and install the plugin
		$t_plugin = plugin_register( $t_avatar_plugin, true );
		if( !is_null( $t_plugin ) ) {
			plugin_install( $t_plugin );
		} else {
			error_parameters( $t_avatar_plugin );
			echo '<br>' . error_string( ERROR_PLUGIN_INSTALL_FAILED );
			return 1;
		}
	}

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 * create an SQLArray to insert data
 *
 * @param string $p_table Table.
 * @param string $p_data  Data.
 * @return array
 */
function InsertData( $p_table, $p_data ) {
	$t_query = 'INSERT INTO ' . $p_table . $p_data;
	return array( $t_query );
}
