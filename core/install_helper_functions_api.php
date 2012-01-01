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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses database_api.php
 */

require_api( 'database_api.php' );

/**
 * Checks a PHP version number against the version of PHP currently in use
 * @param string $p_version Version string to compare
 * @return bool true if the PHP version in use is equal to or greater than the supplied version string
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
 * @param string $p_date Formatted datetime string from a database
 * @param bool $p_gmt Whether to use UTC (true) or server timezone (false, default)
 * @return int Unix timestamp representation of a datetime string
 * @todo Review date handling
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
 * Migrate the legacy category data to the new category_id-based schema.
 */
function install_category_migrate() {
	global $g_db_log_queries;

	$t_bug_table = db_get_table( 'bug' );
	$t_category_table = db_get_table( 'category' );
	$t_project_category_table = db_get_table( 'project_category' );

	// disable query logging (even if it's enabled in config for this)
	if ( $g_db_log_queries !== 0 ) {
		$t_log_queries = $g_db_log_queries;
		$g_db_log_queries = 0;
	} else {
		$t_log_queries = null;
	}

	$query = "SELECT project_id, category, user_id FROM $t_project_category_table ORDER BY project_id, category";
	$t_category_result = db_query_bound( $query );

	$query = "SELECT project_id, category FROM $t_bug_table ORDER BY project_id, category";
	$t_bug_result = db_query_bound( $query );

	$t_data = Array();

	# Find categories specified by project
	while( $row = db_fetch_array( $t_category_result ) ) {
		$t_project_id = $row['project_id'];
		$t_name = $row['category'];
		$t_data[$t_project_id][$t_name] = $row['user_id'];
	}

	# Find orphaned categories from bugs
	while( $row = db_fetch_array( $t_bug_result ) ) {
		$t_project_id = $row['project_id'];
		$t_name = $row['category'];

		if ( !isset( $t_data[$t_project_id][$t_name] ) ) {
			$t_data[$t_project_id][$t_name] = 0;
		}
	}

	# In every project, go through all the categories found, and create them and update the bug
	foreach( $t_data as $t_project_id => $t_categories ) {
		$t_inserted = array();
		foreach( $t_categories as $t_name => $t_user_id ) {
			$t_lower_name = utf8_strtolower( trim( $t_name ) );
			if ( !isset( $t_inserted[$t_lower_name] ) ) {
				$query = "INSERT INTO $t_category_table ( name, project_id, user_id ) VALUES ( " .
					db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
				db_query_bound( $query, array( $t_name, $t_project_id, $t_user_id ) );
				$t_category_id = db_insert_id( $t_category_table );
				$t_inserted[$t_lower_name] = $t_category_id;
			} else {
				$t_category_id = $t_inserted[$t_lower_name];
			}

			$query = "UPDATE $t_bug_table SET category_id=" . db_param() . '
						WHERE project_id=' . db_param() . ' AND category=' . db_param();
			db_query_bound( $query, array( $t_category_id, $t_project_id, $t_name ) );
		}
	}

	// re-enabled query logging if we disabled it
	if ( $t_log_queries !== null ) {
		$g_db_log_queries = $t_log_queries;
	}

	# return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

function install_date_migrate( $p_data) {
	// $p_data[0] = tablename, [1] id column, [2] = old column, [3] = new column
	global $g_db_log_queries;

	// disable query logging (even if it's enabled in config for this)
	if ( $g_db_log_queries !== 0 ) {
		$t_log_queries = $g_db_log_queries;
		$g_db_log_queries = 0;
	} else {
		$t_log_queries = null;
	}

	$t_table = $p_data[0];
	$t_id_column = $p_data[1];

	if ( is_array( $p_data[2] ) ) {
		$t_old_column = implode( ',', $p_data[2] );
		$t_date_array = true;
		$t_cnt_fields = count( $p_data[2] );
		$t_pairs = array();
		foreach( $p_data[3] as $var ) {
			array_push( $t_pairs, "$var=" . db_param() ) ;
		}
		$t_new_column = implode( ',', $t_pairs );
		$query = "SELECT $t_id_column, $t_old_column FROM $t_table";

		$t_first_column = true;

		# In order to handle large databases where we may timeout during the upgrade, we don't
		# start form the beginning everytime.  Here we will only pickup rows where at least one
		# of the datetime fields wasn't upgraded yet and upgrade them all.
		foreach ( $p_data[3] as $t_new_column_name ) {
			if ( $t_first_column ) {
				$t_first_column = false;
				$query .= ' WHERE ';
			} else {
				$query .= ' OR ';
			}

			$query .= "$t_new_column_name = 1";
		}
	} else {
		$t_old_column = $p_data[2];
		$t_new_column = $p_data[3] . "=" . db_param();
		$t_date_array = false;

		# The check for timestamp being = 1 is to make sure the field wasn't upgraded
		# already in a previous run - see bug #12601 for more details.
		$t_new_column_name = $p_data[3];
		$query = "SELECT $t_id_column, $t_old_column FROM $t_table WHERE $t_new_column_name = 1";
	}

	$t_result = db_query_bound( $query );

	while( $row = db_fetch_array( $t_result ) ) {
		$t_id = (int)$row[$t_id_column];

		if( $t_date_array ) {
			for( $i=0; $i < $t_cnt_fields; $i++ ) {
				$t_old_value = $row[$p_data[2][$i]];

				$t_new_value[$i] = db_unixtimestamp($t_old_value);
				if ($t_new_value[$i] < 100000 ) {
					$t_new_value[$i] = 1;
				}
			}
			$t_values = $t_new_value;
			$t_values[] = $t_id;
		} else {
			$t_old_value = $row[$t_old_column];

			$t_new_value = db_unixtimestamp($t_old_value);
			if ($t_new_value < 100000 ) {
				$t_new_value = 1;
			}
			$t_values = array( $t_new_value, $t_id);
		}

		$query = "UPDATE $t_table SET $t_new_column
					WHERE $t_id_column=" . db_param();
		db_query_bound( $query, $t_values );
	}

	// re-enabled query logging if we disabled it
	if ( $t_log_queries !== null ) {
		$g_db_log_queries = $t_log_queries;
	}

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
 */
function install_correct_multiselect_custom_fields_db_format() {
	global $g_db_log_queries;

	# Disable query logging due to possibility of mass spam.
	if ( $g_db_log_queries !== 0 ) {
		$t_log_queries = $g_db_log_queries;
		$g_db_log_queries = 0;
	} else {
		$t_log_queries = null;
	}

	$t_value_table = db_get_table( 'custom_field_string' );
	$t_field_table = db_get_table( 'custom_field' );

	# Ensure multilist and checkbox custom field values have a vertical pipe |
	# as a prefix and suffix.
	$t_query = "SELECT v.field_id, v.bug_id, v.value from $t_value_table v
		LEFT JOIN $t_field_table c
		ON v.field_id = c.id
		WHERE (c.type = " . CUSTOM_FIELD_TYPE_MULTILIST . " OR c.type = " . CUSTOM_FIELD_TYPE_CHECKBOX . ")
			AND v.value != ''
			AND v.value NOT LIKE '|%|'";
	$t_result = db_query_bound( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_field_id = (int)$t_row['field_id'];
		$c_bug_id = (int)$t_row['bug_id'];
		$c_value = '|' . rtrim( ltrim( $t_row['value'], '|' ), '|' ) . '|';
		$t_update_query = "UPDATE $t_value_table
			SET value = '$c_value'
			WHERE field_id = $c_field_id
				AND bug_id = $c_bug_id";
		$t_update_result = db_query_bound( $t_update_query );
	}

	# Remove vertical pipe | prefix and suffix from radio custom field values.
	$t_query = "SELECT v.field_id, v.bug_id, v.value from $t_value_table v
		LEFT JOIN $t_field_table c
		ON v.field_id = c.id
		WHERE c.type = " . CUSTOM_FIELD_TYPE_RADIO . "
			AND v.value != ''
			AND v.value LIKE '|%|'";
	$t_result = db_query_bound( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_field_id = (int)$t_row['field_id'];
		$c_bug_id = (int)$t_row['bug_id'];
		$c_value = rtrim( ltrim( $t_row['value'], '|' ), '|' );
		$t_update_query = "UPDATE $t_value_table
			SET value = '$c_value'
			WHERE field_id = $c_field_id
				AND bug_id = $c_bug_id";
		$t_update_result = db_query_bound( $t_update_query );
	}

	# Re-enable query logging if we disabled it.
	if ( $t_log_queries !== null ) {
		$g_db_log_queries = $t_log_queries;
	}

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

/**
 *	The filters have been changed so the field names are the same as the database
 *	field names.  This updates any filters stored in the database to use the correct
 *	keys. The 'and_not_assigned' field is no longer used as it is replaced by the meta
 *	filter None.  This removes it from all filters.
 */
function install_stored_filter_migrate() {
	global $g_db_log_queries;

	# Disable query logging due to possibility of mass spam.
	if ( $g_db_log_queries !== 0 ) {
		$t_log_queries = $g_db_log_queries;
		$g_db_log_queries = 0;
	} else {
		$t_log_queries = null;
	}

	require_api( 'filter_api.php' );

	$t_cookie_version = config_get( 'cookie_version' );

	# convert filters to use the same value for the filter key and the form field
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

	$t_filters_table = db_get_table( 'filters' );
	$t_query = "SELECT * FROM $t_filters_table";
	$t_result = db_query_bound( $t_query );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_filter_arr = filter_deserialize( $t_row['filter_string'] );
		foreach( $t_filter_fields AS $t_old=>$t_new ) {
			if ( isset( $t_filter_arr[$t_old] ) ) {
				$t_value = $t_filter_arr[$t_old];
				unset( $t_filter_arr[$t_old] );
				if( !is_null( $t_new ) ) {
					$t_filter_arr[$t_new] = $t_value;
				}
			}
		}

		$t_filter_serialized = serialize( $t_filter_arr );
		$t_filter_string = $t_cookie_version . '#' . $t_filter_serialized;

		$t_update_query = "UPDATE $t_filters_table SET filter_string=" . db_param() . ' WHERE id=' . db_param();
		$t_update_result = db_query_bound( $t_update_query, array( $t_filter_string, $t_row['id'] ) );
	}

	# Re-enable query logging if we disabled it.
	if ( $t_log_queries !== null ) {
		$g_db_log_queries = $t_log_queries;
	}

	# Return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}

function install_do_nothing() {
	# return 2 because that's what ADOdb/DataDict does when things happen properly
	return 2;
}
