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
 * DBTempData class.
 * @copyright Copyright 2016 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * DBTempData class
 *
 * @package MantisBT
 * @subpackage classes
 */

require_api( 'database_api.php' );

/**
 * Class that manages insertion for temporary data.
 * It manages storage of a set of up to 3 integer wide rows and helper functions
 * to be used a part of JOIN and WHERE/IN clauses on other SQL queries
 */
class DBTempData {
	protected $id = null;
	protected static $header_table = 'temp_header';
	protected static $value_table = 'temp_int';
	protected static $expiry_secs = 3600;
	protected static $insert_size = MAX_SQL_IN_COUNT;
	protected static $column_names = array('i1','i2','i3');
	protected $value_count = null;

	/**
	 * Checks if an array is valid to be processed by this class
	 * The array has to be 1 level of integers, or 2 levels with rows not having
	 * more elements than fields in the temporary table.
	 * @param array $p_array	The array to check
	 * @return boolean	Whether this array is supported by the class
	 */
	public static function is_supported_array( array $p_array ) {
		$t_first = reset( $p_array );
		$t_valid = false;
		if( is_array( $t_first ) ) {
			if( count( $t_first ) <= count( static::$column_names ) ) {
				$t_valid = true;
				foreach( $t_first as $t_elem ) {
					$t_valid = $t_valid && is_int( $t_elem );
				}
			}
		} else {
			$t_valid = is_int( $t_first );
		}
		return $t_valid;
	}

	/**
	 * Clean outdated temp data for the whole table
	 *
	 * @TODO cproensa	This function is not called automatically. It can be used
	 * to clean old data that may be left from previous sessions, which for any
	 * reason didn't get cleaned up properly.
	 */
	public static function clean_expired() {
		db_param_push();
		$t_query = 'DELETE FROM ' . db_get_table( static::$value_table ) . ' WHERE id IN '
				. ' (SELECT id FROM ' . db_get_table( static::$header_table ) . ' WHERE expiry<' . db_param() . ')';
		db_query( $t_query, array( db_now() ) );
		db_param_push();
		$t_query = 'DELETE FROM ' . db_get_table( static::$header_table ) . ' WHERE expiry<' .  db_param();
		db_query( $t_query, array( db_now() ) );
	}

	/**
	 * Create a new instance of temporary data
	 * Reserves an id in header temp table
	 *
	 * @param array $p_data	Array of data to be inserted
	 */
	public function __construct( array $p_data = null ) {
		db_param_push();
		# Create a header id for this dataset
		$t_query = 'INSERT INTO ' . db_get_table( static::$header_table ) . ' (expiry) VALUES (' . db_param() . ')';
		db_query( $t_query, array( (int)db_now() + static::$expiry_secs ) );
		$this->id = (int)db_insert_id( db_get_table( static::$header_table ), 'id' );

		# Register deletion of this dataset at script finalization
		register_shutdown_function(
				function() {
					db_query( 'DELETE FROM ' . db_get_table( static::$value_table ) . ' WHERE id=' . $this->id );
					db_query( 'DELETE FROM ' . db_get_table( static::$header_table ) . ' WHERE id=' . $this->id );
				}
				);

				# If a data array was provided, insert it
		if( null !== $p_data ) {
			$this->insert( $p_data );
		}
	}

	/**
	 * Returns an array of the "values table" column names
	 * @return array	Array of column names
	 */
	public function get_column_names() {
		return static::$column_names;
	}

	/**
	 * Return a string containing sql string for comma separated column names
	 * return the number of fields accounted by latest insert operation
	 * @return string	string with column names
	 */
	public function sql_column_names() {
		if( $this->value_count !== null ) {
			return implode( ',', array_slice( static::$column_names, 0, $this->value_count ) );
		}
	}

	/**
	 * Returns temp table name, to be used in external queries
	 * @return string	Table name
	 */
	public function sql_table() {
		return db_get_table( static::$value_table );
	}

	/**
	 * Return the name of the "id column" name for current instance
	 * @return string	name of the id column
	 */
	public function sql_column_id() {
		return 'id';
	}

	/**
	 * Return the id key for this instance
	 * @return integer	id key
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns a built select statement to fetch all data inserted
	 * for current instance, with the number of columns returned
	 * implied from last insert operation
	 * @return string	SQL select statement
	 */
	public function sql_select() {
		if( $this->value_count !== null ) {
			$t_sql = 'SELECT ' . $this->sql_column_names() . ' FROM ' . $this->sql_table()
				. ' WHERE ' . $this->sql_column_id() . '=' . $this->id;
			return $t_sql;
		}
	}

	/**
	 * Bulk insert of data into the temporary table.
	 * Data structure may be:
	 * - An array of int: to insert simple value array
	 * - An array of arrays of int: to insert tuples of values
	 *     Each sub-array must have the same number of elements
	 *     Sub-array element count can't be higher than available value fields for temp table
	 * - Number of values per row is computed for the first sub-array received
	 *     all succesive insert opertaions must have the same value count
	 * @param array $p_data	Array of data
	 */
	public function insert( array $p_data ) {
		$t_first = reset( $p_data );
		if( !$t_first ) {
			# array was empty
			return;
		}
		if(is_array( $t_first ) ) {
			$this->value_count = count( $t_first );
		} else {
			$this->value_count = 1;
		}
		$this->exec_insert_parametrized( $p_data );
	}

	/**
	 * Inserts the array data in temp values table. Data array can be 1 or 2 dimension,
	 * with a maximum number of elements per row as defined in this class
	 *
	 * (Implementation with parametrized query)
	 *
	 * @param array $p_data	Array of data to be inserted
	 */
	protected function exec_insert_parametrized( array $p_data ) {
		# test first element to see if is nested array
		$t_first = reset( $p_data );
		$t_nested = is_array( $t_first );
		$t_count = 0;

		# Function to build query string
		$value_param_builder = function( $p_count ) {
			if( $p_count == 0 ) { return ''; }
			$t_str = '';
			for( $row = 0; $row < $p_count; $row++ ) {
				if( $row > 0 ) { $t_str .= ','; }
				$t_str .= '(';
				# loop ncols+2 times to account for 'id' and 'seq' fields
				for( $col = 0; $col < 2 + $this->value_count; $col++ ) {
					if( $col > 0 ) { $t_str .= ','; }
					$t_str .= db_param();
				}
				$t_str .= ')';
			}
			return $t_str;
		};

		# Function to build the parameter array
		# Relies on source array internal pointer for tracking offset.
		$value_param_filler = function( array &$p_data, $p_count ) use ( $t_nested, &$t_count ) {
			$t_params = array();
			$t_row = current( $p_data );
			for( $t_i = 0; $t_i < $p_count; $t_i++ ) {
				$t_params[] = $this->id;
				$t_params[] = $t_count++;
				if( $t_nested ) {
					# row is an array
					$t_col = reset( $t_row );
					for( $t_c = 0; $t_c < $this->value_count; $t_c++ ) {
						$t_params[] = (int)$t_col;
						$t_col = next( $t_row );
					}
				} else {
					# row is a simple value
					$t_params[] = (int)$t_row;
				}
				$t_row = next( $p_data );
			}
			return $t_params;
		};

		$t_query_main = 'INSERT INTO ' . db_get_table( static::$value_table ) . ' (id,seq,' . $this->sql_column_names() . ') VALUES ';
		$t_nrows = count( $p_data );
		# pre-build a full block insert query
		if( $t_nrows >= static::$insert_size ) {
			db_param_push();
			$t_full_block_params = $value_param_builder( static::$insert_size );
		}

		$t_rem = $t_nrows;
		# loop on full blocks first. query string is reused
		while( $t_rem > static::$insert_size ) {
			$t_params = $value_param_filler( $p_data, static::$insert_size );
			db_query( $t_query_main . $t_full_block_params, $t_params );
			$t_rem = $t_rem - static::$insert_size;
		}
		# remaining elements are less than a block size
		if( $t_rem > 0 ) {
			db_param_push();
			$t_values_str = $value_param_builder( $t_rem );
			$t_params = $value_param_filler( $p_data, $t_rem );
			db_query( $t_query_main . $t_values_str, $t_params );
		}
	}

	/**
	 * Inserts the array data in temp values table. Data array can be 1 or 2 dimension,
	 * with a maximum number of elements per row as defined in this class
	 *
	 * (Implementation with hardcoded query)
	 *
	 * @param array $p_data	Array of data to be inserted
	 */
	protected function exec_insert_hardcoded( array $p_data ) {
		$t_first = reset( $p_data );
		$t_nested = is_array( $t_first );
		$t_query_main = 'INSERT INTO ' . db_get_table( static::$value_table ) . ' (id,seq,' . $this->sql_column_names() . ') VALUES ';
		$t_nrows = count( $p_data );
		$t_count = 0;
		$t_row = reset( $p_data );
		while( $t_count < $t_nrows ) {
			# build a new query until block size is reached
			$t_block_count = 0;
			$t_query = $t_query_main;
			while( $t_count < $t_nrows && $t_block_count < static::$insert_size ) {
				if( $t_nested ) {
					$t_values = (int)reset( $t_row );
					for( $i = 1; $i < $this->value_count; $i++ ) {
						$t_values .= ',' . (int)next( $t_row );
					}
				} else {
					$t_values = (int)$t_row;
				}
				$t_query .= $t_block_count > 0 ? ',' : '';
				$t_query .= '(' . $this->id . ',' . $t_count . ',' . $t_values . ')';
				$t_row = next( $p_data );
				$t_count++;
				$t_block_count++;
			}
			db_query( $t_query );
		}
	}

	/**
	 * Provides insertion from a prebuilt sql select statement
	 *
	 * @param array $p_columns  Array of columns to be selected from subquery
	 * @param type $p_query     Subquery for data selection to be inserted
	 * @param type $p_params    Optional, parameters passed through for subquery execution
	 */
	public function insert_select( array $p_columns, $p_query, $p_params = array() ) {
		$this->value_count = count( $p_columns );
		$t_query_insert = 'INSERT INTO ' . db_get_table( static::$value_table ) . '(id,' . $this->sql_column_names() . ')'
				. ' SELECT ' . $this->id . ',' . implode( ',', $p_columns )
				. ' FROM (' . $p_query . ') SUB';
		db_query( $t_query_insert, $p_params );
	}
}