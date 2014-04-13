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
 * Database API
 *
 * @package CoreAPI
 * @subpackage DatabaseAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses logging_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'logging_api.php' );
require_api( 'utility_api.php' );

/**
 * An array in which all executed queries are stored.  This is used for profiling
 * @global array $g_queries_array
 */
$g_queries_array = array();

/**
 * Stores whether a database connection was succesfully opened.
 * @global bool $g_db_connected
 */
$g_db_connected = false;

/**
 * Store whether to log queries ( used for show_queries_count/query list)
 * @global bool $g_db_log_queries
 */
$g_db_log_queries = ( 0 != ( config_get_global( 'log_level' ) & LOG_DATABASE ) );

/**
 * Open a connection to the database.
 * @param string $p_dsn Database connection string ( specified instead of other params)
 * @param string $p_hostname Database server hostname
 * @param string $p_username database server username
 * @param string $p_password database server password
 * @param string $p_database_name database name
 * @param array $p_db_options Database options
 * @return bool indicating if the connection was successful
 * @throws MantisBT\Exception\Database\ConnectionFailed
 */
function db_connect( $p_dsn, $p_hostname = null, $p_username = null, $p_password = null, $p_database_name = null, $p_db_options = null ) {
	global $g_db_connected, $g_db;
	$t_db_type = config_get_global( 'db_type' );

	$g_db = MantisDatabase::GetDriverInstance($t_db_type);
	$t_result = $g_db->connect( $p_dsn, $p_hostname, $p_username, $p_password, $p_database_name, $p_db_options );

	if( !$t_result ) {
		throw new MantisBT\Exception\Database\ConnectionFailed();
	}

	$t_prefix = config_get_global( 'db_table_prefix' ) . '_';
	$t_suffix = config_get_global( 'db_table_suffix' );

	$g_db->SetPrefixes( $t_prefix, $t_suffix );
	$g_db_connected = true;
	return true;
}

/**
 * Returns whether a connection to the database exists
 * @return bool indicating if the a database connection has been made
 */
function db_is_connected() {
	global $g_db_connected;

	return $g_db_connected;
}


/**
 * Checks if the database driver is MySQL
 * @return bool true if mysql
 */
function db_is_mysql() {
	global $g_db;
	return ($g_db->GetDbType() == 'mysql');
}

/**
 * Checks if the database driver is PostgreSQL
 * @return bool true if postgres
 */
function db_is_pgsql() {
	global $g_db;
	return ($g_db->GetDbType() == 'postgres');
}

/**
 * Checks if the database driver is MS SQL
 * @return bool true if mssql
 */
function db_is_mssql() {
	global $g_db;
	return ($g_db->GetDbType() == 'mssql');
}

/**
 * execute query, requires connection to be opened
 * An error will be triggered if there is a problem executing the query.
 * @param string $p_query Parameterlised Query string to execute
 * @param array $p_params Array of parameters matching $p_query
 * @param int $p_limit Number of results to return
 * @param int $p_offset offset query results for paging
 * @return object|bool Result set or false if the query failed.
 * @throws MantisBT\Exception\Database\QueryFailed
 */
function db_query( $p_query, $p_params = null, $p_limit = -1, $p_offset = -1 ) {
	global $g_db;

	if(( $p_limit != -1 ) || ( $p_offset != -1 ) ) {
		$t_result = $g_db->SelectLimit( $p_query, $p_limit, $p_offset, $p_params );
	} else {
		$t_result = $g_db->Execute( $p_query, $p_params );
	}

	if( !$t_result ) {
		throw new MantisBT\Exception\Database\QueryFailed( array( 1,1,$p_query ) );
	} else {
		return $t_result;
	}
}

/**
 * Retrieve the last error message from the DB
 */
function db_last_error() {
	global $g_db;
	return $g_db->GetLastError();
}

/**
 * Retrieve the next row returned from a specific database query
 * @param bool|object $p_result Database Query Record Set to retrieve next result for.
 * @return array Database result
 */
function db_fetch_array( &$p_result ) {
	return $p_result->fetch();
}

/**
 * Retrieve a result returned from a specific database query
 * @param bool|object $p_result Database Query Record Set to retrieve next result for.
 * @param int $p_index1 Column to retrieve (optional)
 * @return mixed Database result
 */
function db_result( $p_result, $p_index1 = 0 ) {
	return $p_result->fetchColumn($p_index1);
}

/**
 * return the last inserted id for a specific database table
 * @param string $p_table a valid database table name
 * @param string $p_field field name - defaults to id
 * @return int last successful insert id
 */
function db_insert_id( $p_table = null, $p_field = "id" ) {
	global $g_db;

	return $g_db->GetInsertId( $p_table, $p_field );
}

/**
 * Check if the specified table exists.
 * @param string $p_table_name a valid database table name
 * @return bool indicating whether the table exists
 */
function db_table_exists( $p_table_name ) {
	global $g_db;
	if( is_blank( $p_table_name ) ) {
		return false;
	}

	$p_table_name = $g_db->GetTableName($p_table_name);

	$t_tables = db_get_table_list();

	# Can't use in_array() since it is case sensitive
	$t_table_name = utf8_strtolower( $p_table_name );
	foreach( $t_tables as $t_current_table ) {
		if( utf8_strtolower( $t_current_table ) == $t_table_name ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if the specified table index exists.
 * @param string $p_table_name a valid database table name
 * @param string $p_index_name a valid database index name
 * @return bool indicating whether the index exists
 */
function db_index_exists( $p_table_name, $p_index_name ) {
	global $g_db;

	if( is_blank( $p_index_name ) || is_blank( $p_table_name ) ) {
		return false;
	}

	return $g_db->IndexExists( $p_table_name, $p_index_name );
}

/**
 * Check if the specified field exists in a given table
 * @param string $p_field_name a database field name
 * @param string $p_table_name a valid database table name
 * @return bool indicating whether the field exists
 */
function db_field_exists( $p_field_name, $p_table_name ) {
	$columns = db_field_names( $p_table_name );
	return array_key_exists( $p_field_name, $columns );
}

/**
 * Retrieve list of fields for a given table
 * @param string $p_table_name a valid database table name
 * @return array array of fields on table
 */
function db_field_names( $p_table_name ) {
	global $g_db;
	$columns = $g_db->GetColumns( $p_table_name );
	return is_array( $columns ) ? $columns : array();
}


/**
 * Prepare a binary string before DB insertion
 * Use of this function is required for some DB types, to properly encode
 * BLOB fields prior to calling db_query()
 * @param string $p_string raw binary data
 * @return string prepared database query string
 */
function db_prepare_binary_string( $p_string ) {
	global $g_db;
	$t_db_type = config_get_global( 'db_type' );

	switch( $t_db_type ) {
		case 'mssql':
		case 'mssqlnative':
		case 'odbc_mssql':
		case 'ado_mssql':
			$content = unpack( "H*hex", $p_string );
			return '0x' . $content['hex'];
			break;
		case 'postgres':
		case 'postgres64':
		case 'postgres7':
		case 'pgsql':
			return $g_db->BlobEncode( $p_string );
			break;
		case 'oci8':
			# Fall through, oci8 stores raw data in BLOB
		default:
			return $p_string;
			break;
	}
}

/**
 * return current timestamp for DB
 * @todo add param bool $p_gmt whether to use GMT or current timezone (default false)
 * @return string Formatted Date for DB insertion e.g. 1970-01-01 00:00:00 ready for database insertion
 */
function db_now() {
	return time();
}

/**
 * convert minutes to a time format [h]h:mm
 * @param int $p_min integer representing number of minutes
 * @return string representing formatted duration string in hh:mm format.
 */
function db_minutes_to_hhmm( $p_min = 0 ) {
	return sprintf( '%02d:%02d', $p_min / 60, $p_min % 60 );
}

/**
 * A helper function that generates a case-sensitive or case-insensitive like phrase based on the current db type.
 * The field name and value are assumed to be safe to insert in a query (i.e. already cleaned).
 * @param string $p_field_name The name of the field to filter on.
 * @param bool $p_case_sensitive true: case sensitive, false: case insensitive
 * @return string returns (field LIKE 'value') OR (field ILIKE 'value')
 */
function db_helper_like( $p_field_name, $p_case_sensitive = false ) {
	$t_like_keyword = 'LIKE';

	if( $p_case_sensitive === false ) {
		if( db_is_pgsql() ) {
			$t_like_keyword = 'ILIKE';
		}
	}

	return "($p_field_name $t_like_keyword %s)";
}

/**
 * A helper function to compare two dates against a certain number of days
 * @param $p_date1_id_or_column
 * @param $p_date2_id_or_column
 * @param $p_limitstring
 * @return string returns database query component to compare dates
 */
function db_helper_compare_days( $p_date1_id_or_column, $p_date2_id_or_column, $p_limitstring ) {
	$t_db_type = config_get_global( 'db_type' );

	$p_date1 = $p_date1_id_or_column;
	$p_date2 = $p_date2_id_or_column;
	if( is_int( $p_date1_id_or_column ) ) {
		$p_date1 = '%d';
	}
	if( is_int( $p_date2_id_or_column ) ) {
		$p_date2 = '%d';
	}

	return '((' . $p_date1 . ' - ' . $p_date2 .')' . $p_limitstring . ')';
}

/**
 * count queries
 * @return int
 */
function db_count_queries() {
	global $g_queries_array;

	return count( $g_queries_array );
}

/**
 * count unique queries
 * @return int
 */
function db_count_unique_queries() {
	global $g_queries_array;

	$t_unique_queries = 0;
	$t_shown_queries = array();
	foreach( $g_queries_array as $t_val_array ) {
		if( !in_array( $t_val_array[0], $t_shown_queries ) ) {
			$t_unique_queries++;
			array_push( $t_shown_queries, $t_val_array[0] );
		}
	}
	return $t_unique_queries;
}

/**
 * get total time for queries
 * @return int
 */
function db_time_queries() {
	global $g_queries_array;
	$t_count = count( $g_queries_array );
	$t_total = 0;
	for( $i = 0;$i < $t_count;$i++ ) {
		$t_total += $g_queries_array[$i][1];
	}
	return $t_total;
}

/**
 * get list database tables
 * @return array containing table names
 */
function db_get_table_list() {
	global $g_db;
	return $g_db->GetTables();
}