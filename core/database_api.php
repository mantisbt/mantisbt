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
 * @uses adodb/adodb.inc.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'logging_api.php' );
require_api( 'utility_api.php' );

define( 'ADODB_DIR', config_get( 'library_path' ) . 'adodb' );
require_lib( 'adodb' . DIRECTORY_SEPARATOR . 'adodb.inc.php' );

# An array in which all executed queries are stored.  This is used for profiling
# @global array $g_queries_array
$g_queries_array = array();


# Stores whether a database connection was succesfully opened.
# @global bool $g_db_connected
$g_db_connected = false;

# Store whether to log queries ( used for show_queries_count/query list)
# @global bool $g_db_log_queries
$g_db_log_queries = ( 0 != ( config_get_global( 'log_level' ) & LOG_DATABASE ) );

# set adodb to associative fetch mode with lowercase column names
# @global bool $ADODB_FETCH_MODE
global $ADODB_FETCH_MODE;
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
define( 'ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_LOWER );

/**
 * Mantis Database Parameters Count class
 * Stores the current parameter count, provides method to generate parameters
 * and a simple stack mechanism to enable the caller to build multiple queries
 * concurrently on RDBMS using positional parameters (e.g. PostgreSQL)
 */
class MantisDbParam {
	/**
	 * Current parameter count
	 */
	public $count = 0;

	/**
	 * Parameter count stack
	 */
	private $stack = array();

	/**
	 * Generate a string to insert a parameter into a database query string
	 * @return string 'wildcard' matching a parameter in correct ordered format for the current database.
	 */
	public function assign() {
		global $g_db;
		return $g_db->Param( $this->count++ );
	}

	/**
	 * Pushes current parameter count onto stack and resets its value to 0
	 * @return void
	 */
	public function push() {
		$this->stack[] = $this->count;
		$this->count = 0;
	}

	/**
	 * Pops the previous value of param count from the stack
	 * This function is called by {@see db_query()} and should not need
	 * to be executed directly
	 * @return void
	 */
	public function pop() {
		global $g_db;

		$this->count = (int)array_pop( $this->stack );
		if( db_is_pgsql() ) {
			# Manually reset the ADOdb param number to the value we just popped
			$g_db->_pnum = $this->count;
		}
	}
}

# Tracks the query parameter count
# @global object $g_db_param
$g_db_param = new MantisDbParam();

/**
 * Open a connection to the database.
 * @param string  $p_dsn           Database connection string ( specified instead of other params).
 * @param string  $p_hostname      Database server hostname.
 * @param string  $p_username      Database server username.
 * @param string  $p_password      Database server password.
 * @param string  $p_database_name Database name.
 * @param string  $p_db_schema     Schema name (only used if database type is DB2).
 * @param boolean $p_pconnect      Use a Persistent connection to database.
 * @return boolean indicating if the connection was successful
 */
function db_connect( $p_dsn, $p_hostname = null, $p_username = null, $p_password = null, $p_database_name = null, $p_db_schema = null, $p_pconnect = false ) {
	global $g_db_connected, $g_db;
	$t_db_type = config_get_global( 'db_type' );

	if( !db_check_database_support( $t_db_type ) ) {
		error_parameters( 0, 'PHP Support for database is not enabled' );
		trigger_error( ERROR_DB_CONNECT_FAILED, ERROR );
	}

	if( empty( $p_dsn ) ) {
		$g_db = ADONewConnection( $t_db_type );

		if( $p_pconnect ) {
			$t_result = $g_db->PConnect( $p_hostname, $p_username, $p_password, $p_database_name );
		} else {
			$t_result = $g_db->Connect( $p_hostname, $p_username, $p_password, $p_database_name );
		}
	} else {
		$g_db = ADONewConnection( $p_dsn );
		$t_result = $g_db->IsConnected();
	}

	if( $t_result ) {
		# For MySQL, the charset for the connection needs to be specified.
		if( db_is_mysql() ) {
			# @todo Is there a way to translate any charset name to MySQL format? e.g. remote the dashes?
			# @todo Is this needed for other databases?
			db_query( 'SET NAMES UTF8' );
		} else if( db_is_db2() && $p_db_schema !== null && !is_blank( $p_db_schema ) ) {
			$t_result2 = db_query( 'set schema ' . $p_db_schema );
			if( $t_result2 === false ) {
				db_error();
				trigger_error( ERROR_DB_CONNECT_FAILED, ERROR );
				return false;
			}
		}
	} else {
		db_error();
		trigger_error( ERROR_DB_CONNECT_FAILED, ERROR );
		return false;
	}

	$g_db_connected = true;

	return true;
}

/**
 * Returns whether a connection to the database exists
 * @global stores database connection state
 * @return boolean indicating if the a database connection has been made
 */
function db_is_connected() {
	global $g_db_connected;

	return $g_db_connected;
}

/**
 * Returns whether php support for a database is enabled
 * @param string $p_db_type Database type.
 * @return boolean indicating if php current supports the given database type
 */
function db_check_database_support( $p_db_type ) {
	switch( $p_db_type ) {
		case 'mysql':
			$t_support = function_exists( 'mysql_connect' );
			break;
		case 'mysqli':
			$t_support = function_exists( 'mysqli_connect' );
			break;
		case 'pgsql':
			$t_support = function_exists( 'pg_connect' );
			break;
		case 'mssql':
			$t_support = function_exists( 'mssql_connect' );
			break;
		case 'mssqlnative':
			$t_support = function_exists( 'sqlsrv_connect' );
			break;
		case 'oci8':
			$t_support = function_exists( 'OCILogon' );
			break;
		case 'db2':
			$t_support = function_exists( 'db2_connect' );
			break;
		case 'odbc_mssql':
			$t_support = function_exists( 'odbc_connect' );
			break;
		default:
			$t_support = false;
	}
	return $t_support;
}

/**
 * Checks if the database driver is MySQL
 * @return boolean true if mysql
 */
function db_is_mysql() {
	$t_db_type = config_get_global( 'db_type' );

	switch( $t_db_type ) {
		case 'mysql':
		case 'mysqli':
			return true;
	}

	return false;
}

/**
 * Checks if the database driver is PostgreSQL
 * @return boolean true if postgres
 */
function db_is_pgsql() {
	$t_db_type = config_get_global( 'db_type' );

	switch( $t_db_type ) {
		case 'postgres':
		case 'postgres7':
		case 'pgsql':
			return true;
	}

	return false;
}

/**
 * Checks if the database driver is MS SQL
 * @return boolean true if mssql
 */
function db_is_mssql() {
	$t_db_type = config_get_global( 'db_type' );

	switch( $t_db_type ) {
		case 'mssql':
		case 'mssqlnative':
		case 'odbc_mssql':
			return true;
	}

	return false;
}

/**
 * Checks if the database driver is DB2
 * @return boolean true if db2
 */
function db_is_db2() {
	$t_db_type = config_get_global( 'db_type' );

	switch( $t_db_type ) {
		case 'db2':
			return true;
	}

	return false;
}

/**
 * Checks if the database driver is Oracle (oci8)
 * @return boolean true if oracle
 */
function db_is_oracle() {
	$t_db_type = config_get_global( 'db_type' );

	return ( $t_db_type == 'oci8' );
}

/**
 * Validates that the given identifier's length is OK for the database platform
 * Triggers an error if the identifier is too long
 * @param string $p_identifier Identifier to check.
 * @return void
 */
function db_check_identifier_size( $p_identifier ) {
	# Oracle does not support long object names (30 chars max)
	if( db_is_oracle() && 30 < strlen( $p_identifier ) ) {
		error_parameters( $p_identifier );
		trigger_error( ERROR_DB_IDENTIFIER_TOO_LONG, ERROR );
	}
}

/**
 * function alias for db_query() for legacy support of plugins
 * @deprecated db_query should be used in preference to this function. This function may be removed in 2.0
 */
function db_query_bound() {
	error_parameters( __FUNCTION__ . '()', 'db_query()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	return call_user_func_array( 'db_query', func_get_args() );
}

/**
 * execute query, requires connection to be opened
 * An error will be triggered if there is a problem executing the query.
 * This will pop the database parameter stack {@see MantisDbParam} after a
 * successful execution, unless specified otherwise
 *
 * @global array of previous executed queries for profiling
 * @global adodb database connection object
 * @global boolean indicating whether queries array is populated
 * @param string  $p_query     Parameterlised Query string to execute.
 * @param array   $p_arr_parms Array of parameters matching $p_query.
 * @param integer $p_limit     Number of results to return.
 * @param integer $p_offset    Offset query results for paging.
 * @param boolean $p_pop_param Set to false to leave the parameters on the stack
 * @return IteratorAggregate|boolean adodb result set or false if the query failed.
 */
function db_query( $p_query, array $p_arr_parms = null, $p_limit = -1, $p_offset = -1, $p_pop_param = true ) {
	global $g_queries_array, $g_db, $g_db_log_queries, $g_db_param;

	$t_db_type = config_get_global( 'db_type' );

	static $s_check_params;
	if( $s_check_params === null ) {
		$s_check_params = ( db_is_pgsql() || $t_db_type == 'odbc_mssql' || $t_db_type == 'mssqlnative' );
	}

	$t_start = microtime( true );

	# This ensures that we don't get an error from ADOdb if $p_arr_parms == null,
	# as Execute() expects either an array or false if there are no parameters -
	# null actually gets treated as array( 0 => null )
	if( is_null( $p_arr_parms ) ) {
		$p_arr_parms = array();
	}

	if( !empty( $p_arr_parms ) && $s_check_params ) {
		$t_params = count( $p_arr_parms );
		for( $i = 0;$i < $t_params;$i++ ) {
			if( $p_arr_parms[$i] === false ) {
				$p_arr_parms[$i] = 0;
			} elseif( $p_arr_parms[$i] === true && $t_db_type == 'mssqlnative' ) {
				$p_arr_parms[$i] = 1;
			}
		}
	}

	static $s_prefix;
	static $s_suffix;
	if( $s_prefix === null ) {
		# Determine table prefix and suffixes including trailing and leading '_'
		$s_prefix = trim( config_get_global( 'db_table_prefix' ) );
		$s_suffix = trim( config_get_global( 'db_table_suffix' ) );

		if( !empty( $s_prefix ) && '_' != substr( $s_prefix, -1 ) ) {
			$s_prefix .= '_';
		}
		if( !empty( $s_suffix ) && '_' != substr( $s_suffix, 0, 1 ) ) {
			$s_suffix = '_' . $s_suffix;
		}
	}

	$p_query = strtr($p_query, array(
							'{' => $s_prefix,
							'}' => $s_suffix,
							) );

	# Pushing params to safeguard the ADOdb parameter count (required for pgsql)
	$g_db_param->push();

	if( db_is_oracle() ) {
		$p_query = db_oracle_adapt_query_syntax( $p_query, $p_arr_parms );
	}

	if( ( $p_limit != -1 ) || ( $p_offset != -1 ) ) {
		$t_result = $g_db->SelectLimit( $p_query, $p_limit, $p_offset, $p_arr_parms );
	} else {
		$t_result = $g_db->Execute( $p_query, $p_arr_parms );
	}

	# Restore ADOdb parameter count
	$g_db_param->pop();

	$t_elapsed = number_format( microtime( true ) - $t_start, 4 );

	if( ON == $g_db_log_queries ) {
		$t_lastoffset = 0;
		$i = 0;
		if( !empty( $p_arr_parms ) ) {
			while( preg_match( '/\?/', $p_query, $t_matches, PREG_OFFSET_CAPTURE, $t_lastoffset ) ) {
				$t_matches = $t_matches[0];
				# Realign the offset returned by preg_match as it is byte-based,
				# which causes issues with UTF-8 characters in the query string
				# (e.g. from custom fields names)
				$t_utf8_offset = utf8_strlen( substr( $p_query, 0, $t_matches[1] ), mb_internal_encoding() );
				if( $i <= count( $p_arr_parms ) ) {
					if( is_null( $p_arr_parms[$i] ) ) {
						$t_replace = 'NULL';
					} else if( is_string( $p_arr_parms[$i] ) ) {
						$t_replace = "'" . $p_arr_parms[$i] . "'";
					} else if( is_integer( $p_arr_parms[$i] ) || is_float( $p_arr_parms[$i] ) ) {
						$t_replace = (float)$p_arr_parms[$i];
					} else if( is_bool( $p_arr_parms[$i] ) ) {
						switch( $t_db_type ) {
							case 'pgsql':
								$t_replace = '\'' . $p_arr_parms[$i] . '\'';
							break;
						default:
							$t_replace = $p_arr_parms[$i];
							break;
						}
					} else {
						echo( 'Invalid argument type passed to query_bound(): ' . ( $i + 1 ) );
						exit( 1 );
					}
					$p_query = utf8_substr( $p_query, 0, $t_utf8_offset ) . $t_replace . utf8_substr( $p_query, $t_utf8_offset + utf8_strlen( $t_matches[0] ) );
					$t_lastoffset = $t_matches[1] + strlen( $t_replace ) + 1;
				} else {
					$t_lastoffset = $t_matches[1] + 1;
				}
				$i++;
			}
		}
		$t_log_msg = array( $p_query, $t_elapsed );
		log_event( LOG_DATABASE, $t_log_msg );
		array_push( $g_queries_array, $t_log_msg );
	} else {
		array_push( $g_queries_array, array( '', $t_elapsed ) );
	}

	# Restore param stack: only pop if asked to AND the query has params
	if( $p_pop_param && !empty( $p_arr_parms ) ) {
		$g_db_param->pop();
	}

	if( !$t_result ) {
		db_error( $p_query );
		trigger_error( ERROR_DB_QUERY_FAILED, ERROR );
		return false;
	} else {
		return $t_result;
	}
}

/**
 * Generate a string to insert a parameter into a database query string
 * @return string 'wildcard' matching a parameter in correct ordered format for the current database.
 */
function db_param() {
	global $g_db_param;
	return $g_db_param->assign();
}

/**
 * Pushes current parameter count onto stack and resets its value
 * Allows the caller to build multiple queries concurrently on RDBMS using
 * positional parameters (e.g. PostgreSQL)
 * @return void
 */
function db_param_push() {
	global $g_db_param;
	$g_db_param->push();
}

/**
 * Pops the previous parameter count from the stack
 * It is generally not necessary to call this, because the param count is popped
 * automatically whenever a query is executed via db_query(). There are some
 * corner cases when doing it manually makes sense, e.g. when a query is built
 * but not executed.
 * @return void
 */
function db_param_pop() {
	global $g_db_param;
	$g_db_param->pop();
}

/**
 * Retrieve number of rows returned for a specific database query
 * @param IteratorAggregate $p_result Database Query Record Set to retrieve record count for.
 * @return integer Record Count
 */
function db_num_rows( IteratorAggregate $p_result ) {
	return $p_result->RecordCount();
}

/**
 * Retrieve number of rows affected by a specific database query
 * @return integer Affected Rows
 */
function db_affected_rows() {
	global $g_db;

	return $g_db->Affected_Rows();
}

/**
 * Retrieve the next row returned from a specific database query
 * @param IteratorAggregate &$p_result Database Query Record Set to retrieve next result for.
 * @return array Database result
 */
function db_fetch_array( IteratorAggregate &$p_result ) {
	global $g_db, $g_db_type;

	if( $p_result->EOF ) {
		return false;
	}

	# Retrieve the fields from the recordset
	$t_row = $p_result->fields;

	# Additional handling for specific RDBMS
	if( db_is_pgsql() ) {
		# pgsql's boolean fields are stored as 't' or 'f' and must be converted
		static $s_current_result = null, $s_convert_needed;

		if( $s_current_result != $p_result ) {
			# Processing a new query
			$s_current_result = $p_result;
			$s_convert_needed = false;
		} elseif( !$s_convert_needed ) {
			# No conversion needed, return the row as-is
			$p_result->MoveNext();
			return $t_row;
		}

		foreach( $p_result->FieldTypesArray() as $t_field ) {
			switch( $t_field->type ) {
				case 'bool':
					switch( $t_row[$t_field->name] ) {
						case 'f':
							$t_row[$t_field->name] = false;
							break;
						case 't':
							$t_row[$t_field->name] = true;
							break;
					}
					$s_convert_needed = true;
					break;
			}
		}

	} elseif( db_is_oracle() ) {
		# oci8 returns null values for empty strings, convert them back
		foreach( $t_row as &$t_value ) {
			if( !isset( $t_value ) ) {
				$t_value = '';
			}
		}
	}

	$p_result->MoveNext();
	return $t_row;
}

/**
 * Retrieve a result returned from a specific database query
 * @param boolean|IteratorAggregate $p_result Database Query Record Set to retrieve next result for.
 * @param integer                   $p_index1 Row to retrieve (optional).
 * @param integer                   $p_index2 Column to retrieve (optional).
 * @return mixed Database result
 */
function db_result( $p_result, $p_index1 = 0, $p_index2 = 0 ) {
	if( $p_result && ( db_num_rows( $p_result ) > 0 ) ) {
		$p_result->Move( $p_index1 );
		$t_result = $p_result->GetArray();

		# Make the array numerically indexed. This is required to retrieve the
		# column ($p_index2), since we use ADODB_FETCH_ASSOC fetch mode.
		$t_result = array_values( $t_result[0] );

		return $t_result[$p_index2];
	}

	return false;
}

/**
 * return the last inserted id for a specific database table
 * @param string $p_table A valid database table name.
 * @param string $p_field A valid field name (default 'id').
 * @return integer last successful insert id
 */
function db_insert_id( $p_table = null, $p_field = 'id' ) {
	global $g_db;

	if( isset( $p_table ) ) {
		if( db_is_oracle() ) {
			$t_query = 'SELECT seq_' . $p_table . '.CURRVAL FROM DUAL';
		} elseif( db_is_pgsql() ) {
			$t_query = 'SELECT currval(\'' . $p_table . '_' . $p_field . '_seq\')';
		}
		if( isset( $t_query ) ) {
			$t_result = db_query( $t_query );
			return db_result( $t_result );
		}
	}
	if( db_is_mssql() ) {
		$t_query = 'SELECT IDENT_CURRENT(\'' . $p_table . '\')';
		$t_result = db_query( $t_query );
		return db_result( $t_result );
	}
	return $g_db->Insert_ID();
}

/**
 * Check if the specified table exists.
 * @param string $p_table_name A valid database table name.
 * @return boolean indicating whether the table exists
 */
function db_table_exists( $p_table_name ) {
	if( is_blank( $p_table_name ) ) {
		return false;
	}

	$t_tables = db_get_table_list();
	if( !is_array( $t_tables ) ) {
		return false;
	}

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
 * @param string $p_table_name A valid database table name.
 * @param string $p_index_name A valid database index name.
 * @return boolean indicating whether the index exists
 */
function db_index_exists( $p_table_name, $p_index_name ) {
	global $g_db;

	if( is_blank( $p_index_name ) || is_blank( $p_table_name ) ) {
		return false;
	}

	$t_indexes = $g_db->MetaIndexes( $p_table_name );
	if( $t_indexes === false ) {
		# no index found
		return false;
	}

	if( !empty( $t_indexes ) ) {
		# Can't use in_array() since it is case sensitive
		$t_index_name = utf8_strtolower( $p_index_name );
		foreach( $t_indexes as $t_current_index_name => $t_current_index_obj ) {
			if( utf8_strtolower( $t_current_index_name ) == $t_index_name ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Check if the specified field exists in a given table
 * @param string $p_field_name A database field name.
 * @param string $p_table_name A valid database table name.
 * @return boolean indicating whether the field exists
 */
function db_field_exists( $p_field_name, $p_table_name ) {
	$t_columns = db_field_names( $p_table_name );

	# ADOdb oci8 driver works with uppercase column names, and as of 5.19 does
	# not provide a way to force them to lowercase
	if( db_is_oracle() ) {
		$p_field_name = strtoupper( $p_field_name );
	}

	return in_array( $p_field_name, $t_columns );
}

/**
 * Retrieve list of fields for a given table
 * @param string $p_table_name A valid database table name.
 * @return array array of fields on table
 */
function db_field_names( $p_table_name ) {
	global $g_db;
	$t_columns = $g_db->MetaColumnNames( $p_table_name );
	return is_array( $t_columns ) ? $t_columns : array();
}

/**
 * Returns the last error number. The error number is reset after every call to Execute(). If 0 is returned, no error occurred.
 * @return int last error number
 * @todo Use/Behaviour of this function should be reviewed before 1.2.0 final
 */
function db_error_num() {
	global $g_db;

	return $g_db->ErrorNo();
}

/**
 * Returns the last status or error message. Returns the last status or error message. The error message is reset when Execute() is called.
 * This can return a string even if no error occurs. In general you do not need to call this function unless an ADOdb function returns false on an error.
 * @return string last error string
 * @todo Use/Behaviour of this function should be reviewed before 1.2.0 final
 */
function db_error_msg() {
	global $g_db;

	return $g_db->ErrorMsg();
}

/**
 * send both the error number and error message and query (optional) as paramaters for a triggered error
 * @param string $p_query Query that generated the error.
 * @return void
 * @todo Use/Behaviour of this function should be reviewed before 1.2.0 final
 */
function db_error( $p_query = null ) {
	if( null !== $p_query ) {
		error_parameters( db_error_num(), db_error_msg(), $p_query );
	} else {
		error_parameters( db_error_num(), db_error_msg() );
	}
}

/**
 * close the connection.
 * Not really necessary most of the time since a connection is automatically closed when a page finishes loading.
 * @return void
 */
function db_close() {
	global $g_db;

	$t_result = $g_db->Close();
}

/**
 * prepare a string before DB insertion
 * @param string $p_string Unprepared string.
 * @return string prepared database query string
 * @deprecated db_query should be used in preference to this function. This function may be removed in 1.2.0 final
 */
function db_prepare_string( $p_string ) {
	global $g_db;
	$t_db_type = config_get_global( 'db_type' );

	switch( $t_db_type ) {
		case 'mssql':
		case 'mssqlnative':
		case 'odbc_mssql':
		case 'ado_mssql':
			return addslashes( $p_string );
		case 'db2':
			$t_escaped = $g_db->qstr( $p_string, false );
			return utf8_substr( $t_escaped, 1, utf8_strlen( $t_escaped ) - 2 );
		case 'mysql':
		case 'mysqli':
			$t_escaped = $g_db->qstr( $p_string, false );
			return utf8_substr( $t_escaped, 1, utf8_strlen( $t_escaped ) - 2 );
		case 'postgres':
		case 'postgres64':
		case 'postgres7':
		case 'pgsql':
			return pg_escape_string( $p_string );
		case 'oci8':
			return $p_string;
		default:
			error_parameters( 'db_type', $t_db_type );
			trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
	}
}

/**
 * Prepare a binary string before DB insertion
 * Use of this function is required for some DB types, to properly encode
 * BLOB fields prior to calling db_query()
 * @param string $p_string Raw binary data.
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
			$t_content = unpack( 'H*hex', $p_string );
			return '0x' . $t_content['hex'];
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
 * prepare a int for database insertion.
 * @param integer $p_int Integer.
 * @return integer integer
 * @deprecated db_query should be used in preference to this function. This function may be removed in 1.2.0 final
 * @todo Use/Behaviour of this function should be reviewed before 1.2.0 final
 */
function db_prepare_int( $p_int ) {
	return (int)$p_int;
}

/**
 * prepare a double for database insertion.
 * @param float $p_double Double.
 * @return double double
 * @deprecated db_query should be used in preference to this function. This function may be removed in 1.2.0 final
 * @todo Use/Behaviour of this function should be reviewed before 1.2.0 final
 */
function db_prepare_double( $p_double ) {
	return (double)$p_double;
}

/**
 * prepare a boolean for database insertion.
 * @param boolean $p_bool Boolean value.
 * @return integer integer representing boolean
 * @deprecated db_query should be used in preference to this function. This function may be removed in 1.2.0 final
 * @todo Use/Behaviour of this function should be reviewed before 1.2.0 final
 */
function db_prepare_bool( $p_bool ) {
	global $g_db;
	if( db_is_pgsql() ) {
		return $g_db->qstr( $p_bool );
	} else {
		return (int)(bool)$p_bool;
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
 * @param integer $p_min Integer representing number of minutes.
 * @return string representing formatted duration string in hh:mm format.
 */
function db_minutes_to_hhmm( $p_min = 0 ) {
	return sprintf( '%02d:%02d', $p_min / 60, $p_min % 60 );
}

/**
 * A helper function that generates a case-sensitive or case-insensitive like phrase based on the current db type.
 * The field name and value are assumed to be safe to insert in a query (i.e. already cleaned).
 * @param string  $p_field_name     The name of the field to filter on.
 * @param boolean $p_case_sensitive True: case sensitive, false: case insensitive.
 * @return string returns (field LIKE 'value') OR (field ILIKE 'value')
 */
function db_helper_like( $p_field_name, $p_case_sensitive = false ) {
	$t_like_keyword = ' LIKE ';

	if( $p_case_sensitive === false ) {
		if( db_is_pgsql() ) {
			$t_like_keyword = ' ILIKE ';
		}
	}

	return '(' . $p_field_name . $t_like_keyword . db_param() . ')';
}

/**
 * Compare two dates against a certain number of days
 * 'val_or_col' parameters will be used "as is" in the query component,
 * allowing use of a column name. To compare against a specific date,
 * it is recommended to pass db_param() instead of a date constant.
 * @param string  $p_val_or_col_1 Value or Column to compare.
 * @param string  $p_operator     SQL comparison operator.
 * @param string  $p_val_or_col_2 Value or Column to compare.
 * @param integer $p_num_secs     Number of seconds to compare against
 * @return string Database query component to compare dates
 * @todo Check if there is a way to do that using ADODB rather than implementing it here.
 */
function db_helper_compare_time( $p_val_or_col_1, $p_operator, $p_val_or_col_2, $p_num_secs ) {
	if( $p_num_secs == 0 ) {
		return "($p_val_or_col_1 $p_operator $p_val_or_col_2)";
	} elseif( $p_num_secs > 0 ) {
		return "($p_val_or_col_1 $p_operator $p_val_or_col_2 + $p_num_secs)";
	} else {
		# Invert comparison to avoid issues with unsigned integers on MySQL
		return "($p_val_or_col_1 - $p_num_secs $p_operator $p_val_or_col_2)";
	}
}

/**
 * count queries
 * @return integer
 */
function db_count_queries() {
	global $g_queries_array;

	return count( $g_queries_array );
}

/**
 * count unique queries
 * @return integer
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
 * @return integer
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
 * get database table name
 *
 * @param string $p_name Can either be specified as 'XXX' (e.g. 'bug'), or
 *                       using the legacy style 'mantis_XXX_table'; in the
 *                       latter case, a deprecation warning will be issued.
 * @return string containing full database table name (with prefix and suffix)
 */
function db_get_table( $p_name ) {
	if( strpos( $p_name, 'mantis_' ) === 0 ) {
		$t_table = substr( $p_name, 7, strpos( $p_name, '_table' ) - 7 );
		error_parameters(
			__FUNCTION__ . "( '$p_name' )",
			__FUNCTION__ . "( '$t_table' )"
		);
		trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );
	} else {
		$t_table = $p_name;
	}

	# Determine table prefix including trailing '_'
	$t_prefix = trim( config_get_global( 'db_table_prefix' ) );
	if( !empty( $t_prefix ) && '_' != substr( $t_prefix, -1 ) ) {
		$t_prefix .= '_';
	}
	# Determine table suffix including leading '_'
	$t_suffix = trim( config_get_global( 'db_table_suffix' ) );
	if( !empty( $t_suffix ) && '_' != substr( $t_suffix, 0, 1 ) ) {
		$t_suffix = '_' . $t_suffix;
	}

	# Physical table name
	$t_table = $t_prefix . $t_table . $t_suffix;
	db_check_identifier_size( $t_table );
	return $t_table;
}

/**
 * get list database tables
 * @return array containing table names
 */
function db_get_table_list() {
	global $g_db, $g_db_schema;

	if( db_is_db2() ) {
		# must pass schema
		$t_tables = $g_db->MetaTables( 'TABLE', false, '', $g_db_schema );
	} else {
		$t_tables = $g_db->MetaTables( 'TABLE' );
	}
	return $t_tables;
}

/**
 * Updates a BLOB column
 *
 * This function is only needed for oci8; it will do nothing and return
 * false if used with another RDBMS.
 *
 * @param string $p_table  Table name.
 * @param string $p_column The BLOB column to update.
 * @param string $p_val    Data to store into the BLOB.
 * @param string $p_where  Where clause to identify which record to update
 *                         if null, defaults to the last record inserted in $p_table.
 * @return boolean
 */
function db_update_blob( $p_table, $p_column, $p_val, $p_where = null ) {
	global $g_db, $g_db_log_queries, $g_queries_array;

	if( !db_is_oracle() ) {
		return false;
	}

	if( null == $p_where ) {
		$p_where = 'id=' . db_insert_id( $p_table );
	}

	if( ON == $g_db_log_queries ) {
		$t_start = microtime( true );

		$t_backtrace = debug_backtrace();
		$t_caller = basename( $t_backtrace[0]['file'] );
		$t_caller .= ':' . $t_backtrace[0]['line'];

		# Is this called from another function?
		if( isset( $t_backtrace[1] ) ) {
			$t_caller .= ' ' . $t_backtrace[1]['function'] . '()';
		} else {
			# or from a script directly?
			$t_caller .= ' ' . $_SERVER['SCRIPT_NAME'];
		}
	}

	$t_result = $g_db->UpdateBlob( $p_table, $p_column, $p_val, $p_where );

	if( $g_db_log_queries ) {
		$t_elapsed = number_format( microtime( true ) - $t_start, 4 );
		$t_log_data = array(
			'Update BLOB in ' . $p_table . '.' . $p_column . ' where ' . $p_where,
			$t_elapsed,
			$t_caller
		);
		log_event( LOG_DATABASE, var_export( $t_log_data, true ) );
		array_push( $g_queries_array, $t_log_data );
	}

	if( !$t_result ) {
		db_error();
		trigger_error( ERROR_DB_QUERY_FAILED, ERROR );
		return false;
	}

	return $t_result;
}

/**
 * Sorts bind variable numbers and puts them in sequential order
 * e.g. input:  "... WHERE F1=:12 and F2=:97 ",
 *      output: "... WHERE F1=:0 and F2=:1 ".
 * Used in db_oracle_adapt_query_syntax().
 * @param string $p_query Query string to sort.
 * @return string Query string with sorted bind variable numbers.
 */
function db_oracle_order_binds_sequentially( $p_query ) {
	$t_new_query= '';
	$t_is_odd = true;
	$t_after_quote = false;
	$t_iter = 0;

	# Divide statement to skip processing string literals
	$t_p_query_arr = explode( '\'', $p_query );
	foreach( $t_p_query_arr as $t_p_query_part ) {
		if( $t_new_query != '' ) {
			$t_new_query .= '\'';
		}
		if( $t_is_odd ) {
			# Divide to process all bindvars
			$t_p_query_subpart_arr = explode( ':', $t_p_query_part );
			if( count( $t_p_query_subpart_arr ) > 1 ) {
				foreach( $t_p_query_subpart_arr as $t_p_query_subpart ) {
					if( ( !$t_after_quote ) && ( $t_new_query != '' ) ) {
						$t_new_query .= ':' . preg_replace( '/^(\d+?)/U', strval( $t_iter ), $t_p_query_subpart );
						$t_iter++;
					} else {
						$t_new_query .= $t_p_query_subpart;
					}
					$t_after_quote = false;
				}
			} else {
				$t_new_query .= $t_p_query_part;
			}
			$t_is_odd = false;
		} else {
			$t_after_quote = true;
			$t_new_query .= $t_p_query_part;
			$t_is_odd = true;
		}
	}
	return $t_new_query;
}

/**
 * Adapt input query string and bindvars array to Oracle DB syntax:
 * 1. Change bind vars id's to sequence beginning with 0
 *    (calls db_oracle_order_binds_sequentially() )
 * 2. Remove "AS" keyword, because it is not supported with table aliasing
 * 3. Remove null bind variables in insert statements for default values support
 * 4. Replace "tab.column=:bind" to "tab.column IS NULL" when :bind is empty string
 * 5. Replace "SET tab.column=:bind" to "SET tab.column=DEFAULT" when :bind is empty string
 * @param string $p_query      Query string to sort.
 * @param array  &$p_arr_parms Array of parameters matching $p_query, function sorts array keys.
 * @return string Query string with sorted bind variable numbers.
 */
function db_oracle_adapt_query_syntax( $p_query, array &$p_arr_parms = null ) {
	# Remove "AS" keyword, because not supported with table aliasing
	$t_is_odd = true;
	$t_query = '';
	# Divide statement to skip processing string literals
	$t_p_query_arr = explode( '\'', $p_query );
	foreach( $t_p_query_arr as $t_p_query_part ) {
		if( $t_query != '' ) {
			$t_query .= '\'';
		}
		if( $t_is_odd ) {
			$t_query .= preg_replace( '/ AS /im', ' ', $t_p_query_part );
		} else {
			$t_query .= $t_p_query_part;
			$t_is_odd = true;
		}
	}
	$p_query = $t_query;

	# Remove null bind variables in insert statements for default values support
	if( is_array( $p_arr_parms ) ) {
		preg_match( '/^[\s\n\r]*insert[\s\n\r]+(into){0,1}[\s\n\r]+(?P<table>[a-z0-9_]+)[\s\n\r]*\([\s\n\r]*[\s\n\r]*(?P<fields>[a-z0-9_,\s\n\r]+)[\s\n\r]*\)[\s\n\r]*values[\s\n\r]*\([\s\n\r]*(?P<values>[:a-z0-9_,\s\n\r]+)\)/i', $p_query, $t_matches );

		if( isset( $t_matches['values'] ) ) { #if statement is a INSERT INTO ... (...) VALUES(...)
			# iterates non-empty bind variables
			$i = 0;
			$t_fields_left = $t_matches['fields'];
			$t_values_left = $t_matches['values'];

			for( $t_arr_index = 0; $t_arr_index < count( $p_arr_parms ); $t_arr_index++ ) {
				# inserting fieldname search
				if( preg_match( '/^[\s\n\r]*([a-z0-9_]+)[\s\n\r]*,{0,1}([\d\D]*)\z/i', $t_fields_left, $t_fieldmatch ) ) {
					$t_fields_left = $t_fieldmatch[2];
					$t_fields_arr[$i] = $t_fieldmatch[1];
				}
				# inserting bindvar name search
				if( preg_match( '/^[\s\n\r]*(:[a-z0-9_]+)[\s\n\r]*,{0,1}([\d\D]*)\z/i', $t_values_left, $t_valuematch ) ) {
					$t_values_left = $t_valuematch[2];
					$t_values_arr[$i] = $t_valuematch[1];
				}
				# skip unsetting if bind array value not empty
				if( $p_arr_parms[$t_arr_index] !== '' ) {
					$i++;
				} else {
					$t_arr_index--;
					# Shift array and unset bind array element
					for( $n = $i + 1; $n < count( $p_arr_parms ); $n++ ) {
						$p_arr_parms[$n-1] = $p_arr_parms[$n];
					}
					unset( $t_fields_arr[$i] );
					unset( $t_values_arr[$i] );
					unset( $p_arr_parms[count( $p_arr_parms ) - 1] );
				}
			}

			# Combine statement from arrays
			$p_query = 'INSERT INTO ' . $t_matches['table'] . ' (' . $t_fields_arr[0];
			for( $i = 1; $i < count( $p_arr_parms ); $i++ ) {
				$p_query = $p_query . ', ' . $t_fields_arr[$i];
			}
			$p_query = $p_query . ') values (' . $t_values_arr[0];
			for( $i = 1; $i < count( $p_arr_parms ); $i++ ) {
				$p_query = $p_query . ', ' . $t_values_arr[$i];
			}
			$p_query = $p_query . ')';
		} else {
			# if input statement is NOT a INSERT INTO (...) VALUES(...)

			# "IS NULL" adoptation here
			$t_set_where_template_str = substr( md5( uniqid( rand(), true ) ), 0, 50 );
			$t_removed_set_where = '';

			# Need to order parameter array element correctly
			$p_query = db_oracle_order_binds_sequentially( $p_query );

			# Find and remove temporarily "SET var1=:bind1, var2=:bind2 WHERE" part
			preg_match( '/^(?P<before_set_where>.*)(?P<set_where>[\s\n\r]*set[\s\n\r]+[\s\n\ra-z0-9_\.=,:\']+)(?P<after_set_where>where[\d\D]*)$/i', $p_query, $t_matches );
			$t_set_where_stmt = isset( $t_matches['after_set_where'] );

			if( $t_set_where_stmt ) {
				$t_removed_set_where = $t_matches['set_where'];
				# Now work with statement without "SET ... WHERE" part
				$t_templated_query = $t_matches['before_set_where'] . $t_set_where_template_str . $t_matches['after_set_where'];
			} else {
				$t_templated_query = $p_query;
			}

			# Replace "var1=''" by "var1 IS NULL"
			while( preg_match( '/^(?P<before_empty_literal>[\d\D]*[\s\n\r(]+([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)[\s\n\r]*=[\s\n\r]*\'\'(?P<after_empty_literal>[\s\n\r]*[\d\D]*\z)/i', $t_templated_query, $t_matches ) > 0 ) {
				$t_templated_query = $t_matches['before_empty_literal'] . ' IS NULL ' . $t_matches['after_empty_literal'];
			}
			# Replace "var1!=''" and "var1<>''" by "var1 IS NOT NULL"
			while( preg_match( '/^(?P<before_empty_literal>[\d\D]*[\s\n\r(]+([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)[\s\n\r]*(![\s\n\r]*=|<[\s\n\r]*>)[\s\n\r]*\'\'(?P<after_empty_literal>[\s\n\r]*[\d\D]*\z)/i', $t_templated_query, $t_matches ) > 0 ) {
				$t_templated_query = $t_matches['before_empty_literal'] . ' IS NOT NULL ' . $t_matches['after_empty_literal'];
			}

			$p_query = $t_templated_query;
			# Process input bind variable array to replace "WHERE fld=:12"
			# by "WHERE fld IS NULL" if :12 is empty
			while( preg_match( '/^(?P<before_var>[\d\D]*[\s\n\r(]+)(?P<var_name>([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)(?P<dividers>[\s\n\r]*=[\s\n\r]*:)(?P<bind_name>[0-9]+)(?P<after_var>[\s\n\r]*[\d\D]*\z)/i', $t_templated_query, $t_matches ) > 0 ) {
				$t_bind_num = $t_matches['bind_name'];

				$t_search_substr = $t_matches['before_var'] . $t_matches['var_name'] . $t_matches['dividers'] . $t_matches['bind_name'] . $t_matches['after_var'];
				$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . '=:' . $t_matches['bind_name']. $t_matches['after_var'];

				if( $p_arr_parms[$t_bind_num] === '' ) {
					for( $n = $t_bind_num + 1; $n < count( $p_arr_parms ); $n++ ) {
						$p_arr_parms[$n - 1] = $p_arr_parms[$n];
					}
					unset( $p_arr_parms[count( $p_arr_parms ) - 1] );
					$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . ' IS NULL ' . $t_matches['after_var'];
				}
				$p_query = str_replace( $t_search_substr, $t_replace_substr, $p_query );

				$t_templated_query = $t_matches['before_var'] . $t_matches['after_var'];
			}

			if( $t_set_where_stmt ) {
				# Put temporarily removed "SET ... WHERE" part back
				$p_query = str_replace( $t_set_where_template_str, $t_removed_set_where, $p_query );
				# Need to order parameter array element correctly
				$p_query = db_oracle_order_binds_sequentially( $p_query );
				# Find and remove temporary "SET var1=:bind1, var2=:bind2 WHERE" part again
				preg_match( '/^(?P<before_set_where>.*)(?P<set_where>[\s\n\r]*set[\s\n\r]+[\s\n\ra-z0-9_\.=,:\']+)(?P<after_set_where>where[\d\D]*)$/i', $p_query, $t_matches );
				$t_removed_set_where = $t_matches['set_where'];
				$p_query = $t_matches['before_set_where'] . $t_set_where_template_str . $t_matches['after_set_where'];

				#Replace "SET fld1=:1" to "SET fld1=DEFAULT" if bind array value is empty
				$t_removed_set_where_parsing = $t_removed_set_where;

				while( preg_match( '/^(?P<before_var>[\d\D]*[\s\n\r,]+)(?P<var_name>([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)(?P<dividers>[\s\n\r]*=[\s\n\r]*:)(?P<bind_name>[0-9]+)(?P<after_var>[,\s\n\r]*[\d\D]*\z)/i', $t_removed_set_where_parsing, $t_matches ) > 0 ) {
					$t_bind_num = $t_matches['bind_name'];
					$t_search_substr = $t_matches['before_var'] . $t_matches['var_name'] . $t_matches['dividers'] . $t_matches['bind_name'] ;
					$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . $t_matches['dividers'] . $t_matches['bind_name'] ;

					if( $p_arr_parms[$t_bind_num] === '' ) {
						for( $n = $t_bind_num + 1; $n < count( $p_arr_parms ); $n++ ) {
							$p_arr_parms[$n - 1] = $p_arr_parms[$n];
						}
						unset( $p_arr_parms[count( $p_arr_parms ) - 1] );
						$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . '=DEFAULT ';
					}
					$t_removed_set_where = str_replace( $t_search_substr, $t_replace_substr, $t_removed_set_where );
					$t_removed_set_where_parsing = $t_matches['before_var'] . $t_matches['after_var'];
				}
				$p_query = str_replace( $t_set_where_template_str, $t_removed_set_where, $p_query );
			}
		}
	}
	$p_query = db_oracle_order_binds_sequentially( $p_query );
	return $p_query;
}

/**
 * Replace 4-byte UTF-8 chars
 * This is a workaround to avoid data getting truncated on MySQL databases
 * using native utf8 encoding, which only supports 3 bytes chars (see #20431)
 * @param string $p_string
 * @return string
 */
function db_mysql_fix_utf8( $p_string ) {
	if( !db_is_mysql() ) {
		return $p_string;
	}
	return preg_replace(
		# 4-byte UTF8 chars always start with bytes 0xF0-0xF7 (0b11110xxx)
		'/[\xF0-\xF7].../s',
		# replace with U+FFFD to avoid potential Unicode XSS attacks,
		# see http://unicode.org/reports/tr36/#Deletion_of_Noncharacters
		"\xEF\xBF\xBD",
		$p_string
	);
}

/**
 * Creates an empty record set, compatible with db_query() result
 * This object can be used when a query can't be performed, or is not needed,
 * and still want to return an empty result as a transparent return value.
 * @return \ADORecordSet_empty
 */
function db_empty_result() {
	return new ADORecordSet_empty();
}