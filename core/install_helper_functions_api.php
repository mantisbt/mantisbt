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
 *
 * @noinspection PhpUnused
 */

require_api( 'database_api.php' );

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
 * @return integer Unix timestamp representation of a datetime string
 */
function db_unixtimestamp( $p_date = null ) {
	global $g_db;

	if( null !== $p_date ) {
		$p_timestamp = $g_db->UnixTimeStamp( $p_date );
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
 * @return bool|string|array true if columns check OK
 *               error message string if errors occurred
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
		'user_pref'       => array( 'advanced_report', 'advanced_view', 'advanced_update', 'email_on_new', 'email_on_assigned', 'email_on_feedback', 'email_on_resolved', 'email_on_closed', 'email_on_reopened', 'email_on_bugnote', 'email_on_status', 'email_on_priority' ),
		'user'            => array( 'enabled', 'protected' ),
	);

	# Generate SQL to check columns against schema
	$t_where = '';
	foreach( $t_bool_columns as $t_table_name => $t_columns ) {
		$t_table = db_get_table( $t_table_name );
		$t_where .= 'table_name = \'' . $t_table . '\' AND column_name IN ( \''
			. implode( "', '", $t_columns )
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
 * Get pgsql column's data type
 *
 * @param string $p_table  Table name
 * @param string $p_column Column name
 *
 * @return string column data_type
 *
 * @throws Exception
 */
function pgsql_get_column_type( $p_table, $p_column ) {
	global $f_database_name;
	global $g_db;

	# Generate SQL to check columns against schema
	$t_sql = 'SELECT data_type
		FROM information_schema.columns
		WHERE table_catalog = $1 
		AND table_name = $2
		AND column_name = $3';
	$t_param = array(
		$f_database_name,
		db_get_table( $p_table ),
		$p_column,
	);

	/** @var ADORecordSet|bool $t_result */
	$t_result = @$g_db->execute( $t_sql, $t_param );
	if( $t_result === false ) {
		throw new Exception( 'Unable to check information_schema' );
	} else if( $t_result->recordCount() == 0 ) {
		throw new Exception( "Column '$p_column' not found in table '$p_table'" );
	}

	$t_rows = $t_result->getAll();
	return reset( $t_rows[0] );
}

/**
 * Set the value of $g_db_log_queries as specified
 * This is used by install callback functions to ensure that only the relevant
 * queries are logged
 * @global integer $g_db_log_queries
 * @param integer $p_new_state New value to set $g_db_log_queries to (defaults to OFF).
 * @return bool old value of $g_db_log_queries
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

	$t_data = array();

	# Find categories specified by project
	$t_query = new DbQuery(
		'SELECT project_id, category, user_id FROM {project_category} ORDER BY project_id, category'
	);
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_project_id = $t_row['project_id'];
		$t_name = $t_row['category'];
		$t_data[$t_project_id][$t_name] = $t_row['user_id'];
	}

	# Find orphaned categories from bugs
	$t_query = new DbQuery(
		'SELECT project_id, category FROM {bug} ORDER BY project_id, category'
	);
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_project_id = $t_row['project_id'];
		$t_name = $t_row['category'];

		if( !isset( $t_data[$t_project_id][$t_name] ) ) {
			$t_data[$t_project_id][$t_name] = 0;
		}
	}

	$t_insert = new DbQuery(
		'INSERT INTO {category} ( name, project_id, user_id ) VALUES ( :name, :project_id, :user_id )'
	);
	$t_update = new DbQuery(
		'UPDATE {bug} SET category_id=:id WHERE project_id=:project_id AND category=:name'
	);

	# In every project, go through all the categories found, and create them and update the bug
	foreach( $t_data as $t_project_id => $t_categories ) {
		$t_inserted = array();
		foreach( $t_categories as $t_name => $t_user_id ) {
			$t_lower_name = mb_strtolower( trim( $t_name ) );
			$t_category = array(
				'name' => $t_name,
				'project_id' => $t_project_id,
				'user_id' => $t_user_id,
			);
			if( !isset( $t_inserted[$t_lower_name] ) ) {
				$t_insert->execute( $t_category );
				$t_category['id'] = db_insert_id( db_get_table( 'category' ) );
				$t_inserted[$t_lower_name] = $t_category['id'];
			} else {
				$t_category['id'] = $t_inserted[$t_lower_name];
			}

			$t_update->execute( $t_category );
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
	# Disable query logging even if enabled in config, due to possibility of mass spam
	$t_log_queries = install_set_log_queries();

	$t_table = $p_data[0];
	$t_id_column = $p_data[1];
	$t_date_array = is_array( $p_data[2] );

	if( $t_date_array ) {
		$t_old_column = implode( ',', $p_data[2] );
		$t_cnt_fields = count( $p_data[2] );
		$t_sql = "SELECT $t_id_column, $t_old_column FROM $t_table";
		$t_first_column = true;
		$t_pairs = array();

		# In order to handle large databases where we may timeout during the upgrade, we don't
		# start from the beginning every time.  Here we will only pickup rows where at least one
		# of the datetime fields wasn't upgraded yet and upgrade them all.
		foreach ( $p_data[3] as $t_new_column_name ) {
			if( $t_first_column ) {
				$t_first_column = false;
				$t_sql .= ' WHERE ';
			} else {
				$t_sql .= ' OR ';
			}

			$t_sql .= $t_new_column_name. ' = 1';
			array_push( $t_pairs, "$t_new_column_name = :$t_new_column_name" ) ;
		}
		$t_update_columns = implode( ',', $t_pairs );
	} else {
		$t_old_column = $p_data[2];

		# The check for timestamp being = 1 is to make sure the field wasn't upgraded
		# already in a previous run - see bug #12601 for more details.
		$t_new_column_name = $p_data[3];
		$t_sql = "SELECT $t_id_column, $t_old_column FROM $t_table WHERE $t_new_column_name = 1";

		$t_update_columns = "$t_new_column_name = :$t_new_column_name";
	}

	$t_query = new DbQuery( $t_sql );
	$t_update = new DbQuery(
		"UPDATE $t_table SET $t_update_columns WHERE $t_id_column = :$t_id_column"
	);
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_values[$t_id_column] = (int)$t_row[$t_id_column];
		if( $t_date_array ) {
			for( $i = 0; $i < $t_cnt_fields; $i++ ) {
				$t_old_value = $t_row[$p_data[2][$i]];

				if( is_numeric( $t_old_value ) ) {
					return 1; # Fatal: conversion may have already been run. If it has been run, proceeding will wipe timestamps from db
				}

				$t_new_column_name = $p_data[3][$i];
				$t_new_value = db_unixtimestamp( $t_old_value );
				if( $t_new_value < 100000 ) {
					$t_new_value = 1;
				}
				$t_values[$t_new_column_name] = $t_new_value;
			}
		} else {
			$t_old_value = $t_row[$t_old_column];

			if( is_numeric( $t_old_value ) ) {
				return 1; # Fatal: conversion may have already been run. If it has been run, proceeding will wipe timestamps from db
			}

			$t_new_value = db_unixtimestamp( $t_old_value );
			if( $t_new_value < 100000 ) {
				$t_new_value = 1;
			}
			/** @noinspection PhpUndefinedVariableInspection */
			$t_values[$t_new_column_name] = $t_new_value;
		}

		$t_update->execute( $t_values );
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

	$t_update_query = new DbQuery( 'UPDATE {custom_field_string}
		SET value = :value
		WHERE field_id = :field_id AND bug_id = :bug_id'
	);

	# Ensure multilist and checkbox custom field values have a vertical pipe |
	# as a prefix and suffix.
	$t_query = new DbQuery( 'SELECT v.field_id, v.bug_id, v.value 
		FROM {custom_field_string} v
		LEFT JOIN {custom_field} c
		ON v.field_id = c.id
		WHERE (c.type = ' . CUSTOM_FIELD_TYPE_MULTILIST . ' OR c.type = ' . CUSTOM_FIELD_TYPE_CHECKBOX . ")
			AND v.value != ''
			AND v.value NOT LIKE '|%|'"
	);
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_param = array(
			'field_id' => (int)$t_row['field_id'],
			'bug_id' => (int)$t_row['bug_id'],
			'value' => '|' . rtrim( ltrim( $t_row['value'], '|' ), '|' ) . '|'
		);
		$t_update_query->execute( $t_param );
	}

	# Remove vertical pipe | prefix and suffix from radio custom field values.
	$t_query = new DbQuery( 'SELECT v.field_id, v.bug_id, v.value 
		FROM {custom_field_string} v
		LEFT JOIN {custom_field} c
		ON v.field_id = c.id
		WHERE c.type = ' . CUSTOM_FIELD_TYPE_RADIO . "
			AND v.value != ''
			AND v.value LIKE '|%|'"
	);
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_param = array(
			'field_id' => (int)$t_row['field_id'],
			'bug_id' => (int)$t_row['bug_id'],
			'value' => rtrim( ltrim( $t_row['value'], '|' ), '|' )
		);
		$t_update_query->execute( $t_param );
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

	$t_query = new DbQuery( 'SELECT * FROM {filters}' );
	$t_delete = new DbQuery( 'DELETE FROM {filters} WHERE id=:id' );
	$t_update = new DbQuery( 'UPDATE {filters} SET filter_string=:filter_string WHERE id=:id' );

	$t_errors = array();
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_filter_arr = null;
		$t_error = false;
		$t_filter_string = &$t_row['filter_string'];

		# Grab Filter Version and data into $t_setting_arr
		$t_setting_arr = explode( '#', $t_filter_string, 2 );

		switch( $t_setting_arr[0] ) {
			# Remove any non-upgradeable filters i.e. versions 1 to 4.
			case 'v1':
			case 'v2':
			case 'v3':
			case 'v4':
				$t_delete->bind_values( $t_row );
				$t_delete->execute();
				continue 2;
		}

		if( isset( $t_setting_arr[1] ) ) {
			switch( $t_setting_arr[0] ) {
				# Filter versions 5 to 8 are stored in php serialized format
				case 'v5':
				case 'v6':
				case 'v7':
				case 'v8':
					try {
						$t_filter_arr = safe_unserialize( $t_setting_arr[1] );
					}
					catch( ErrorException $e ) {
						$t_error = $e->getMessage();
					}
					break;
				default:
					$t_filter_arr = json_decode( $t_setting_arr[1], /* assoc array */ true );
					if( $t_filter_arr === null ) {
						$t_error = 'Invalid JSON';
					}
			}
			# Serialized or json encoded data in filter table is invalid.
			# Log the error for later processing.
			if( $t_error ) {
				$t_row['error'] = $t_error;
				$t_errors[] = $t_row;
				continue;
			}
		} else {
			$t_delete->bind_values( $t_row );
			$t_delete->execute();
			continue;
		}

		# If the filter version does not match the latest version,
		# pass it through filter_ensure_valid_filter to do any updates.
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
			$t_row['error'] = 'Invalid filter';
			$t_errors[] = $t_row;
			continue;
		}

		$t_filter_string = FILTER_VERSION . '#' . json_encode( $t_filter_arr );

		$t_update->bind_values( $t_row );
		$t_update->execute();
	}

	# Errors occurred, provide details and abort the upgrade to
	# let the user investigate and fix the problem before trying again.
	if( $t_errors ) {
		install_print_unserialize_errors_csv( 'filters', $t_errors );
		return 1; # Fatal: invalid data found in filters table
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
	$t_query = new DbQuery( 'SELECT name FROM {custom_field}' );
	foreach( $t_query->fetch_all() as $t_field ) {
		if( mb_strlen( $t_field['name'] ) > 32 ) {
			$t_custom_fields[mb_substr( $t_field['name'], 0, 32 )] = $t_field['name'];
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
		$t_field_list .= "'$t_field', ";
	}
	$t_field_list = rtrim( $t_field_list, ', ' );

	$t_update_query = new DbQuery(
		'UPDATE {bug_history} SET field_name = :full_name WHERE field_name = :truncated'
	);

	# Get the list of custom fields from the history table
	$t_query = new DbQuery( 'SELECT DISTINCT field_name FROM {bug_history}
		WHERE type = ' . NORMAL_TYPE . ' AND field_name NOT IN ( ' . $t_field_list . ' )'
	);

	# For each entry, update the truncated custom field name with its full name
	# if a matching custom field exists
	foreach( $t_query->fetch_all() as $t_field ) {
		$t_name = $t_field['field_name'];
		# If field name's length is 32, then likely it was truncated so we try to match
		if( mb_strlen( $t_name ) == 32 && array_key_exists( $t_name, $t_custom_fields ) ) {
			# Match found, update all history records with this field name
			$t_update_query->bind( 'truncated', $t_name );
			$t_update_query->bind( 'full_name', $t_custom_fields[$t_name] );
			$t_update_query->execute();
		}
	}

	# Re-enable query logging if we disabled it
	install_set_log_queries( $t_log_queries );

	return 2;
}

/**
 * Schema update to check that project hierarchy was valid.
 *
 * Removes possible duplicate rows in the table.
 *
 * @return integer
 */
function install_check_project_hierarchy() {
	$t_query = new DbQuery(
		'SELECT count(child_id) as count, child_id, parent_id FROM {project_hierarchy} '
		. 'GROUP BY child_id, parent_id'
	);
	$t_child_projects = new DbQuery(
		'SELECT inherit_parent, child_id, parent_id FROM {project_hierarchy} '
		. 'WHERE child_id=:child_id AND parent_id=:parent_id'
	);
	$t_delete = new DbQuery(
		'DELETE FROM {project_hierarchy} WHERE child_id=:child_id AND parent_id=:parent_id'
	);
	$t_insert = new DbQuery(
		'INSERT INTO {project_hierarchy} (child_id, parent_id, inherit_parent) '
		. 'VALUES ( :child_id, :parent_id, :inherit_parent )'
	);

	foreach( $t_query->fetch_all() as $t_project ) {
		$t_count = (int)$t_project['count'];

		if( $t_count > 1 ) {
			# get first result for inherit_parent, discard the rest
			$t_child_projects->execute( $t_project );
			$t_child_project = $t_child_projects->fetch();

			$t_delete->execute( $t_child_project );
			$t_insert->execute( $t_child_project );
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
	$t_errors = array();

	$t_update = new DbQuery(
		'UPDATE {config} SET value=:value '
		. 'WHERE config_id=:config_id AND project_id=:project_id AND user_id=:user_id'
	);

	$t_query = new DbQuery(
		'SELECT config_id, project_id, user_id, value '
		. 'FROM {config} WHERE type=3'
	);
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_value = &$t_row['value'];

		# Don't try to convert the value if it's already valid JSON
		if( $t_value === null || json_decode( $t_value ) !== null ) {
			continue;
		}

		try {
			$t_config = safe_unserialize( $t_value );
		}
		catch( ErrorException $e ) {
			$t_row['error'] = $e->getMessage();
			$t_errors[] = $t_row;
			continue;
		}

		$t_value = json_encode( $t_config );

		$t_update->bind_values( $t_row );
		$t_update->execute();
	}

	if( $t_errors ) {
		install_print_unserialize_errors_csv( 'config', $t_errors );
		return 1; # Fatal: invalid data found in config table
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
	$t_query = new DbQuery( 'SELECT * FROM {tokens} WHERE type IN (1, 2, 5)' );
	$t_update = new DbQuery( 'UPDATE {tokens} SET value=:value WHERE id=:id' );

	$t_errors = array();
	foreach( $t_query->fetch_all() as $t_row ) {
		$t_value = &$t_row['value'];

		# Don't try to convert the value if it's already valid JSON
		$t_token = json_decode( $t_value );
		if( $t_value === null || $t_token !== null ) {
			continue;
		}

		try {
			$t_token = safe_unserialize( $t_value );
		}
		catch( ErrorException $e ) {
			$t_row['error'] = $e->getMessage();
			$t_errors[] = $t_row;
			continue;
		}

		$t_value = json_encode( $t_token );

		$t_update->bind_values( $t_row );
		$t_update->execute();
	}

	if( $t_errors ) {
		install_print_unserialize_errors_csv( 'tokens', $t_errors );
		return 1; # Fatal: invalid data found in tokens table
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

/**
 * Print a friendly error message and link to download errors list as CSV.
 *
 * @param string $p_table Mantis table name
 * @param array  $p_data  Errors list
 *
 * @return void
 */
function install_print_unserialize_errors_csv( $p_table, $p_data ) {
	# Memory file handle to generate error data as CSV for download
	$f = fopen( 'php://memory', 'r+' );

	# CSV file headers
	$t_csv = implode( ',', array_keys( $p_data[0] ) ) . PHP_EOL;

	# Generate CSV data
	foreach( $p_data as $t_error ) {
		fputcsv( $f, $t_error );
	}
	$t_csv .= stream_get_contents( $f, -1,0 );
	fclose( $f );

	# Display message and download link
	printf( "<p><br>%d rows in <em>%s</em> could not be converted because of invalid data.<br>",
		count( $p_data ),
		db_get_table( $p_table )
	);
	echo "Fix the problem by manually repairing or deleting the offending row(s) "
		. "as appropriate, then try again.</p>";

	# CSV download (as data URL)
?>
	<a href="data:text/csv;charset=UTF-8,<?php echo rawurlencode( $t_csv ) ?>"
	   download="errors_<?php echo $p_table; ?>.csv"
	   class="btn btn-primary btn-white btn-round"
	>
		Download errors list as CSV
	</a>
<?php
}
