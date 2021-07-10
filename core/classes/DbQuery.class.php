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
 * DbQuery class.
 * @copyright Copyright 2017 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses logging_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'logging_api.php' );


/**
 * Database Query abstraction class.
 *
 * Allows building a query and fetching data, providing compatible functionality
 * for all supported databases. Hides the underlaying details from the ADOdb layer.
 *
 * Supports inline parameters with ":label" syntax, and anonymous parameters
 * through the param() method.
 * Supports binding arrays which will be later expanded to individual parameters.
 *
 * The internal processing steps for query string are:
 * 1) $query_string: stores the user input string, containing parameter tokens for all
 *   supported formats:
 *     - ":{string}" For labeled parameters. Binded values are stored in $query_bind_array
 *     - "${int}" For anonymous parameters. Binded values are stored in $query_autobind_array
 *     - "${string}{int}" For special constructs, eg: $in0 for late binding IN clauses
 * 2) $expanded_query_string: stores the query string after expansion of special constructs
 *   into standard "${int}" parameters
 * 3) $db_query_string: stores the query string after all parameters have been renamed,
 *   reordered and converted to the specific database parameter syntax expected by ADOdb.
 *   Binded values are stored in $db_param_array
 *
 * The steps for query string processing are performed by:
 * - process_expand_params(): converts query string (1) into (2)
 * - process_bind_params(): converts query string(2) into (3)
 * - process_sql_syntax(): modifies query string (3) performing some general and database
 *   specific modifications.
 *
 * Execution of the query is supported by:
 * - db_execute(): performs the low level, actual execution from ADOdb
 * - execute(): performs all query processing steps and then calls db_execute().
 */

class DbQuery {

	/**
	 * Stores the user input for query string.
	 * @var string
	 */
	protected $query_string;

	/**
	 * Stores the query string with "late binding" placeholders expanded into final
	 * sql syntax and parameters.
	 * @var string
	 */
	protected $expanded_query_string = null;

	/**
	 * Array of values binded to labeled parameters, indexed by label names.
	 * @var array
	 */
	protected $query_bind_array;

	/**
	 * Array of values binded to anonymous parameters, indexed by numerical index.
	 * @var array
	 */
	protected $query_autobind_array = array();

	/**
	 * Counter for assigning numerical indexes to anonymous parameters.
	 * @var integer
	 */
	protected $query_autobind_count = 0;

	/**
	 * Stores the final query string to be passed to ADOdb.
	 * @var string
	 */
	protected $db_query_string;

	/**
	 * Array of values for parameters included in the final query string for ADOdb,
	 * indexed as needed for the ADOdb driver
	 * @var array
	 */
	protected $db_param_array;

	/**
	 * Stores the adodb result set for the query after it has been executed, or false if the query failed.
	 * @var  IteratorAggregate|boolean
	 */
	protected $db_result = null;

	/**
	 * Stores execution time of the query (microseconds).
	 * @var integer
	 */
	protected $db_query_time = null;

	/**
	 * Stores the latest fetched array fromthe result set.
	 * @var array
	 */
	protected $current_row = null;

	/**
	 * Stores row limit value for query execution.
	 * Number of rows to be returned from the query result
	 * Use -1 to disable this option.
	 * @var integer
	 */
	protected $select_limit = -1;

	/**
	 * Stores the offset value for query execution.
	 * The resultset starts at this position from the query result
	 * Use -1 to disable this option.
	 * @var integer
	 */
	protected $select_offset = -1;

	/**
	 * Array to manage late binding for IN constructs
	 * @var array
	 */
	protected $late_binding_in_clause = array();

	protected static $oracle_in_limit = 1000; # this could be a constant


	/**
	 * Construct a new query object.
	 * Optional parameters are the query string, and an array of values to be
	 * binded to labeled parameters
	 * @param string $p_query_string	Query string
	 * @param array $p_bind_array		Bind values
	 * @return void
	 */
	public function __construct( $p_query_string = null, array $p_bind_array = null ) {
		# Itialization
		if( null === $p_query_string ) {
			$this->query_string = '';
		} else {
			$this->query_string = $p_query_string;
		}
		if( null === $p_bind_array ) {
			$this->query_bind_array = array();
		} else {
			$this->query_bind_array = $p_bind_array;
		}
	}

	/**
	 * Set or replaces current query string
	 * @param string $p_query_string	Query string
	 * @return void
	 */
	public function sql( $p_query_string ) {
		$this->query_string = $p_query_string;
	}

	/**
	 * Append to current query string
	 * @param string $p_query_string	Query string
	 * @return void
	 */
	public function append_sql( $p_query_string ) {
		$this->query_string .= $p_query_string;
	}

	/**
	 * Creates a string containing a parameter that can be appended to the query string
	 * The provided value is binded to the parameter and stored for use at execution time
	 * The parameters created by this method are anonymous parameters, so they can't be
	 * accessed later to have values modified or rebinded.
	 * The format of the token created is: "$n", where "n" is an incremental integer
	 * @param mixed $p_value	Value to bind for this parameter
	 * @return string			Token string
	 */
	public function param( $p_value ) {
		$t_new_id = $this->query_autobind_count++;
		$this->query_autobind_array[$t_new_id] = $p_value;
		$t_par = '$' . $t_new_id;
		return $t_par;
	}

	/**
	 * Sets the "limit" value. This value is used for all subsequent query executions
	 * Use -1 to disable this option
	 * @param integer $p_limit	Number of rows to limit
	 * @return void
	 */
	public function set_limit( $p_limit = -1 ) {
		$this->select_limit = $p_limit;
	}

	/**
	 * Sets the "offset" value. This value is used for all subsequent query executions
	 * Use -1 to disable this option
	 * @param integer $p_offset	Number of rows to offset
	 * @return void
	 */
	public function set_offset( $p_offset = -1 ) {
		$this->select_offset = $p_offset;
	}

	/**
	 * Executes the query, performing all preprocess and binding steps.
	 * A bind array can provided, which will be added to current bindings.
	 * Limit and offset options can be provided, which will affect only to this execution.
	 * @param array $p_bind_array	Array for binding values
	 * @param integer $p_limit		Limit value
	 * @param integer $p_offset		Offset value
	 * @return IteratorAggregate|boolean ADOdb result set or false if the query failed.
	 */
	public function execute( array $p_bind_array = null, $p_limit = null, $p_offset = null ) {
		# For backwards compatibility with legacy code still relying on DB API,
		# we need to save the parameters count before binding otherwise it will
		# be reset after query execution, which will cause issues on RDBMS with
		# numbered params (e.g. PostgreSQL).
		db_param_push();

		# bind values if provided
		if( null !== $p_bind_array ) {
			$this->bind_values( $p_bind_array );
		}

		# preprocess parameters
		$this->process_expand_params();
		$this->process_bind_params();
		$this->process_sql_syntax();

		$t_result = $this->db_execute($p_limit, $p_offset);
		db_param_pop();
		return $t_result;
	}

	/**
	 * Call ADOdb execution of sql string.
	 * At this point all preprocessing and value binding has been performed.
	 * @param integer $p_limit	Limit value
	 * @param integer $p_offset	Offset value
	 * @return IteratorAggregate|boolean ADOdb result set or false if the query failed.
	 */
	protected function db_execute( $p_limit = null, $p_offset = null ) {
		global $g_db;

		# get limit and offset
		if( null !== $p_limit ) {
			$t_limit = $p_limit;
		} else {
			$t_limit = $this->select_limit;
		}
		if( null !== $p_offset ) {
			$t_offset = $p_offset;
		} else {
			$t_offset = $this->select_offset;
		}

		$t_start = microtime( true );

		if( ( $t_limit != -1 ) || ( $t_offset != -1 ) ) {
			$this->db_result = $g_db->SelectLimit( $this->db_query_string, $t_limit, $t_offset, $this->db_param_array );
		} else {
			$this->db_result = $g_db->Execute( $this->db_query_string, $this->db_param_array );
		}

		$this->db_query_time = number_format( microtime( true ) - $t_start, 4 );

		$this->log_query();

		if( !$this->db_result ) {
			db_error( $this->db_query_string );
			trigger_error( ERROR_DB_QUERY_FAILED, ERROR );
			$this->db_result = false;
		}
		$this->current_row = null;
		return $this->db_result;
	}

	/**
	 * Logs data from latest execution
	 * @return void
	 */
	protected function log_query() {
		global $g_db_log_queries, $g_queries_array;
		if( ON == $g_db_log_queries ) {
			$t_query_text = db_format_query_log_msg( $this->db_query_string, $this->db_param_array );
			log_event( LOG_DATABASE, array( $t_query_text, $this->db_query_time ) );
		} else {
			# If not logging the queries the actual text is not needed
			$t_query_text = '';
		}
		array_push( $g_queries_array, array( $t_query_text, $this->db_query_time ) );
	}

	/**
	 * Bind values for labeled parameters.
	 * An array indexed by label names must be provided.
	 * @param array $p_values_array	Array of values
	 * @return void
	 */
	public function bind_values( array $p_values_array ) {
		$this->query_bind_array = $p_values_array + $this->query_bind_array;
	}

	/**
	 * Binds values to a labeled parameters
	 * When using:
	 * bind(string, value) binds one value to one parameter
	 * bind(array) is an alias for method "bind_values(array)"
	 * @param mixed $p_label_or_values	A labeled parameter name, or array for value bindings
	 * @param mixed $p_value			A value to bind
	 * @return void
	 */
	public function bind( $p_label_or_values, $p_value = null ) {
		if( is_array( $p_label_or_values ) ) {
			# is a values array
			$this->bind_values( $p_label_or_values );
		} else {
			# is a label string
			$this->query_bind_array[$p_label_or_values] = $p_value;
		}
	}

	/**
	 * Performs some specific preprocessing on sql string
	 * - replace table names from abbreviated syntax
	 * - replaces boolean values to fix some db driver inconsistencies
	 * - process oracle syntax fixes for compatibility
	 * @return void
	 */
	protected function process_sql_syntax() {
		global $g_db;

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

		$this->db_query_string = strtr( $this->db_query_string,
			array( '{' => $s_prefix, '}' => $s_suffix )
			);

		# check parameters for special treatment of boolean types
		# use the native values provided by the ADOdb driver
		foreach( $this->db_param_array as $t_key => $t_value ) {
			if( $t_value === false ) {
				$this->db_param_array[$t_key] = $g_db->false;
			} elseif( $t_value === true ) {
				$this->db_param_array[$t_key] = $g_db->true;
			}
		}

		if( db_is_oracle() ) {
			$this->process_sql_syntax_oracle();
		}
	}

	/**
	 * Process current query string converting all internal parameter placeholders
	 * to final ADOdb parameter syntax.
	 * Will convert all labeled ":xxx", and anonymous "$n" parameters, and build
	 * a values array suitable for ADOdb.
	 * @param integer $p_counter_start
	 * @return integer	Number of parameters created
	 */
	protected function process_bind_params( $p_counter_start = 0) {
		global $g_db;

		# shortcut, if no values are binded, skip parameter replacement
		if( empty( $this->query_autobind_array ) && empty( $this->query_bind_array ) ) {
			$this->db_query_string = $this->query_string;
			$this->db_param_array = array();
		}

		$t_query_string = $this->expanded_query_string;

		$t_new_query = '';
		$t_new_binds = array();
		$t_par_index = $p_counter_start;
		$t_par_count = 0;
		$t_parts = preg_split( '/(:[a-z0-9_]+)|(\$[0-9]+)/mi', $t_query_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		foreach( $t_parts as $t_part ) {

			$t_first = substr( $t_part, 0, 1 );

			if( $t_first === '$' || $t_first === ':' ) {

				$t_label = substr( $t_part, 1 );
				switch( $t_first ) {
					case '$':
						$t_value = $this->query_autobind_array[(int)$t_label];
						break;
					case ':':
						$t_value = $this->query_bind_array[$t_label];
						break;
				}

				if( is_array( $t_value ) ) {
					$t_params_for_array = array();
					foreach( $t_value as $t_array_item ) {
						$t_params_for_array[] = $g_db->Param( $t_par_index );
						$t_new_binds[$t_par_index] = $t_array_item;
						$t_par_count++;
						$t_par_index++;
					}
					$t_new_query .= '(' . implode( ',', $t_params_for_array ) . ')';
				} elseif( $t_value instanceof DbQuery ) {
					# preprocess subquery object
					$t_value->process_expand_params();
					$t_sub_params = $t_value->process_bind_params( $t_par_index );
					$t_par_index += $t_sub_params;
					$t_par_count += $t_sub_params;
					# append subquery
					$t_new_binds = $t_new_binds + $t_value->db_param_array;
					$t_new_query .= '(' . $t_value->db_query_string . ')';
				} else {
					$t_new_query .= $g_db->Param( $t_par_index );
					$t_new_binds[$t_par_index] = $t_value;
					$t_par_count++;
					$t_par_index++;
				}

				continue;
			}

			# default
			$t_new_query .= $t_part;
		}

		$this->db_query_string = $t_new_query;
		$this->db_param_array = $t_new_binds;

		return $t_par_count;
	}

	/**
	 * Builds an alternative IN expression to work around Oracle limits.
	 * (X, field) IN ( (X,V1), (X,V2), ... ) where X is a constant value
	 * is equivalent to: field IN (V1,V2,..)
	 * @param string $p_alias
	 * @param array $p_values
	 * @return string
	 */
	protected function helper_in_oracle_fix( $p_alias, array $p_values ) {
		$t_in_tuples = array();
		foreach( $p_values as $t_value ) {
			$t_in_tuples[] = '(1,' . $this->param( $t_value ) . ')';
		}
		$t_sql = '(1,' . $p_alias . ') IN (' . implode( ',', $t_in_tuples ) . ')';
		return $t_sql;
	}

	/**
	 * Process query string to expand late binding constructs
	 * @return void
	 */
	protected function process_expand_params() {
		# original query_string should not be modified to allow for rebinding
		$this->expanded_query_string = $this->query_string;

		if( !empty( $this->late_binding_in_clause ) ) {
			$this->process_expand_params_in();
		}
	}

	/**
	 * Process query string to expand late binding constructs for IN clauses
	 * @return void
	 */
	protected function process_expand_params_in() {
		$t_new_query = '';
		$t_parts = preg_split( '/(\$in[0-9]+)/m', $this->expanded_query_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		foreach( $t_parts as $t_part ) {
			$t_is_token = substr( $t_part, 0, 3 ) === '$in';
			if( $t_is_token ) {
				$t_index = (int)substr( $t_part, 3 );
				$t_label =  $this->late_binding_in_clause[$t_index]['label'];
				$t_alias =  $this->late_binding_in_clause[$t_index]['alias'];
				$t_values = $this->query_bind_array[$t_label];
				if( count( $t_values ) > self::$oracle_in_limit ) {
					$t_new_query .= $this->helper_in_oracle_fix( $t_alias, $t_values );
				} elseif( count( $t_values ) == 1 ) {
					$t_new_query .= $t_alias . ' = ' . $this->param( reset( $t_values ) );
				} else {
					$t_new_query .= $t_alias . ' IN ' . $this->param( $t_values );
				}
				continue;
			}

			$t_new_query .= $t_part;
		}
		$this->expanded_query_string = $t_new_query;
	}

	/**
	 * Creates a string construction for an IN expression, providing:
	 * - alias: is the name of the column as a valid identifier in the final sql query
	 * - a label, or an array of values
	 * If a label is provided, the values must be binded as separate calls to binding methods.
	 *
	 * The returned string would replace the sql part for: "alias IN (x,x,x)"
	 *
	 * To work around Oracle limit of 1000 elements in IN clauses, using a label
	 * is recommended in situations that this number may be reached.
	 * This construct will then automatically deal with a compatible syntax fix.
	 * Using a label in this scenario is needed because the fix must be implemented
	 * with a late binding of the array at execution time (this also allows correctly
	 * rebinding the elements for the IN clause)
	 *
	 * @param string $p_alias			A valid sql column identifier
	 * @param mixed $p_label_or_values	Label or values array
	 * @return string	Constructed string to be added to query
	 */
	public function sql_in( $p_alias, $p_label_or_values ) {
		if( is_array( $p_label_or_values ) ) {
			if( db_is_oracle() && count( $p_label_or_values ) > self::$oracle_in_limit ) {
				$t_sql = $this->helper_in_oracle_fix( $p_alias, $p_label_or_values );
			} elseif( count( $p_label_or_values ) == 1 ) {
				$t_sql = $p_alias . ' = ' . $this->param( reset( $p_label_or_values ) );
			} else {
				$t_sql = $p_alias . ' IN ' . $this->param( $p_label_or_values );
			}
		} else {
			# is a label
			# create placeholder for late binding
			$t_new_index = count( $this->late_binding_in_clause );
			$this->late_binding_in_clause[$t_new_index] = array();
			$this->late_binding_in_clause[$t_new_index]['alias'] = $p_alias;
			$this->late_binding_in_clause[$t_new_index]['label'] = $p_label_or_values;
			$t_sql = '$in' . $t_new_index;
		}

		return $t_sql;
	}

	protected function process_sql_syntax_oracle() {
		# Remove "AS" keyword, because not supported with table aliasing
		# - Do not remove text literal within "'" quotes
		# - Will remove all "AS", except when it's part of a "CAST(x AS y)" expression
		#   To do so, we will assume that the "AS" following a "CAST", is safe to be kept.
		#   Using a counter for "CAST" appearances to allow nesting: CAST(CAST(x AS y) AS z)

		$t_query = $this->db_query_string;

		# split the string by the relevant delimiters. The delimiters will be part of the split array
		$t_parts = preg_split("/(')|( AS )|(CAST\s*\()/mi", $t_query, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$t_is_literal = false;
		$t_cast = 0;
		$t_new_query = '';
		foreach( $t_parts as $t_part ) {
			# if quotes, switch literal flag
			if( $t_part == '\'' ) {
				$t_is_literal = !$t_is_literal;
				$t_new_query .= $t_part;
				continue;
			}
			# if this part is litereal, do not change
			if( $t_is_literal ) {
				$t_new_query .= $t_part;
				continue;
			} else {
				# if there is "CAST" delimiter, flag the counter
				if( preg_match( '/^CAST\s*\($/i', $t_part ) ) {
					$t_cast++;
					$t_new_query .= $t_part;
					continue;
				}
				# if there is "AS"
				if( strcasecmp( $t_part, ' AS ' ) == 0 ) {
					# if there's a previous CAST, keep the AS
					if( $t_cast > 0 ) {
						$t_cast--;
						$t_new_query .= $t_part;
					} else {
						# otherwise, remove the " AS ", replace by a space
						$t_new_query .= ' ';
					}
					continue;
				}
				$t_new_query .= $t_part;
				continue;
			}
		}
		$t_query = $t_new_query;

		# Remove null bind variables in insert statements for default values support
		if( is_array( $this->db_param_array ) ) {
			preg_match( '/^[\s\n\r]*insert[\s\n\r]+(into){0,1}[\s\n\r]+(?P<table>[a-z0-9_]+)[\s\n\r]*\([\s\n\r]*[\s\n\r]*(?P<fields>[a-z0-9_,\s\n\r]+)[\s\n\r]*\)[\s\n\r]*values[\s\n\r]*\([\s\n\r]*(?P<values>[:a-z0-9_,\s\n\r]+)\)/i', $t_query, $t_matches );

			if( isset( $t_matches['values'] ) ) { #if statement is a INSERT INTO ... (...) VALUES(...)
				# iterates non-empty bind variables
				$i = 0;
				$t_fields_left = $t_matches['fields'];
				$t_values_left = $t_matches['values'];

				//for( $t_arr_index = 0; $t_arr_index < count( $this->db_param_array ); $t_arr_index++ ) {
				foreach( $this->db_param_array as $t_arr_index => $t_arr_value ) {
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
					//if( $this->db_param_array[$t_arr_index] !== '' ) {
					if( $t_arr_value !== '' ) {
						$i++;
					} else {
						unset( $t_fields_arr[$i] );
						unset( $t_values_arr[$i] );
						unset( $this->db_param_array[$t_arr_index] );
						/*
						$t_arr_index--;
						# Shift array and unset bind array element
						for( $n = $i + 1; $n < count( $this->db_param_array ); $n++ ) {
							$this->db_param_array[$n-1] = $this->db_param_array[$n];
						}
						unset( $t_fields_arr[$i] );
						unset( $t_values_arr[$i] );
						unset( $this->db_param_array[count( $this->db_param_array ) - 1] );
						 */
					}
				}

				# Combine statement from arrays
				$t_query = 'INSERT INTO ' . $t_matches['table'] . ' (' . implode( ',', $t_fields_arr ) . ')'
					. ' VALUES (' . implode( ',', $t_values_arr ) . ')';
				/*
				$t_query = 'INSERT INTO ' . $t_matches['table'] . ' (' . $t_fields_arr[0];
				for( $i = 1; $i < count( $this->db_param_array ); $i++ ) {
					$t_query = $t_query . ', ' . $t_fields_arr[$i];
				}
				$t_query = $t_query . ') values (' . $t_values_arr[0];
				for( $i = 1; $i < count( $this->db_param_array ); $i++ ) {
					$t_query = $t_query . ', ' . $t_values_arr[$i];
				}
				$t_query = $t_query . ')';
				 */
			} else {
				# if input statement is NOT a INSERT INTO (...) VALUES(...)

				# "IS NULL" adoptation here
				$t_set_where_template_str = substr( md5( uniqid( rand(), true ) ), 0, 50 );
				$t_removed_set_where = '';

				# Find and remove temporarily "SET var1=:bind1, var2=:bind2 WHERE" part
				preg_match( '/^(?P<before_set_where>.*)(?P<set_where>[\s\n\r]*set[\s\n\r]+[\s\n\ra-z0-9_\.=,:\']+)(?P<after_set_where>where[\d\D]*)$/i', $t_query, $t_matches );
				$t_set_where_stmt = isset( $t_matches['after_set_where'] );

				if( $t_set_where_stmt ) {
					$t_removed_set_where = $t_matches['set_where'];
					# Now work with statement without "SET ... WHERE" part
					$t_templated_query = $t_matches['before_set_where'] . $t_set_where_template_str . $t_matches['after_set_where'];
				} else {
					$t_templated_query = $t_query;
				}

				# Replace "var1=''" by "var1 IS NULL"
				while( preg_match( '/^(?P<before_empty_literal>[\d\D]*[\s\n\r(]+([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)[\s\n\r]*=[\s\n\r]*\'\'(?P<after_empty_literal>[\s\n\r]*[\d\D]*\z)/i', $t_templated_query, $t_matches ) > 0 ) {
					$t_templated_query = $t_matches['before_empty_literal'] . ' IS NULL ' . $t_matches['after_empty_literal'];
				}
				# Replace "var1!=''" and "var1<>''" by "var1 IS NOT NULL"
				while( preg_match( '/^(?P<before_empty_literal>[\d\D]*[\s\n\r(]+([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)[\s\n\r]*(![\s\n\r]*=|<[\s\n\r]*>)[\s\n\r]*\'\'(?P<after_empty_literal>[\s\n\r]*[\d\D]*\z)/i', $t_templated_query, $t_matches ) > 0 ) {
					$t_templated_query = $t_matches['before_empty_literal'] . ' IS NOT NULL ' . $t_matches['after_empty_literal'];
				}

				$t_query = $t_templated_query;
				# Process input bind variable array to replace "WHERE fld=:12"
				# by "WHERE fld IS NULL" if :12 is empty
				while( preg_match( '/^(?P<before_var>[\d\D]*[\s\n\r(]+)(?P<var_name>([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)(?P<dividers>[\s\n\r]*=[\s\n\r]*:)(?P<bind_name>[0-9]+)(?P<after_var>[\s\n\r]*[\d\D]*\z)/i', $t_templated_query, $t_matches ) > 0 ) {
					$t_bind_num = $t_matches['bind_name'];

					$t_search_substr = $t_matches['before_var'] . $t_matches['var_name'] . $t_matches['dividers'] . $t_matches['bind_name'] . $t_matches['after_var'];
					$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . '=:' . $t_matches['bind_name']. $t_matches['after_var'];

					if( $this->db_param_array[$t_bind_num] === '' ) {
						unset( $this->db_param_array[$t_bind_num] );
						/*
						for( $n = $t_bind_num + 1; $n < count( $this->db_param_array ); $n++ ) {
							$this->db_param_array[$n - 1] = $this->db_param_array[$n];
						}
						unset( $this->db_param_array[count( $this->db_param_array ) - 1] );
						 */
						$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . ' IS NULL ' . $t_matches['after_var'];
					}
					$t_query = str_replace( $t_search_substr, $t_replace_substr, $t_query );

					$t_templated_query = $t_matches['before_var'] . $t_matches['after_var'];
				}

				if( $t_set_where_stmt ) {
					# Put temporarily removed "SET ... WHERE" part back
					$t_query = str_replace( $t_set_where_template_str, $t_removed_set_where, $t_query );
					# Find and remove temporary "SET var1=:bind1, var2=:bind2 WHERE" part again
					preg_match( '/^(?P<before_set_where>.*)(?P<set_where>[\s\n\r]*set[\s\n\r]+[\s\n\ra-z0-9_\.=,:\']+)(?P<after_set_where>where[\d\D]*)$/i', $t_query, $t_matches );
					$t_removed_set_where = $t_matches['set_where'];
					$t_query = $t_matches['before_set_where'] . $t_set_where_template_str . $t_matches['after_set_where'];

					#Replace "SET fld1=:1" to "SET fld1=DEFAULT" if bind array value is empty
					$t_removed_set_where_parsing = $t_removed_set_where;

					while( preg_match( '/^(?P<before_var>[\d\D]*[\s\n\r,]+)(?P<var_name>([a-z0-9_]*[\s\n\r]*\.){0,1}[\s\n\r]*[a-z0-9_]+)(?P<dividers>[\s\n\r]*=[\s\n\r]*:)(?P<bind_name>[0-9]+)(?P<after_var>[,\s\n\r]*[\d\D]*\z)/i', $t_removed_set_where_parsing, $t_matches ) > 0 ) {
						$t_bind_num = $t_matches['bind_name'];
						$t_search_substr = $t_matches['before_var'] . $t_matches['var_name'] . $t_matches['dividers'] . $t_matches['bind_name'] ;
						$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . $t_matches['dividers'] . $t_matches['bind_name'] ;

						if( $this->db_param_array[$t_bind_num] === '' ) {
							unset( $this->db_param_array[$t_bind_num] );
							/*
							for( $n = $t_bind_num + 1; $n < count( $this->db_param_array ); $n++ ) {
								$this->db_param_array[$n - 1] = $this->db_param_array[$n];
							}
							unset( $this->db_param_array[count( $this->db_param_array ) - 1] );
							 */
							$t_replace_substr = $t_matches['before_var'] . $t_matches['var_name'] . '=DEFAULT ';
						}
						$t_removed_set_where = str_replace( $t_search_substr, $t_replace_substr, $t_removed_set_where );
						$t_removed_set_where_parsing = $t_matches['before_var'] . $t_matches['after_var'];
					}
					$t_query = str_replace( $t_set_where_template_str, $t_removed_set_where, $t_query );
				}
			}
		}

		$this->db_query_string = $t_query;
	}

	/**
	 * Compatibility method to support execution of legacy query syntax through db_query(...)
	 * @param string $p_query		Query string
	 * @param array $p_arr_parms	Values array for parameters
	 * @param integer $p_limit		Query limit
	 * @param integer $p_offset		Query offset
	 * @param boolean $p_pop_param  Set to false to leave the parameters on the stack
	 * @return IteratorAggregate|boolean ADOdb result set or false if the query failed
	 */
	public static function compat_db_query( $p_query, array $p_arr_parms = null, $p_limit = -1, $p_offset = -1, $p_pop_param = true ) {
		global $g_db_param;

		if( !is_array( $p_arr_parms ) ) {
			$p_arr_parms = array();
		}

		$t_query = new DbQuery();
		$t_query->db_query_string = $p_query;
		$t_query->db_param_array = $p_arr_parms;

		$t_query->process_sql_syntax();

		# Pushing params to safeguard the ADOdb parameter count (required for pgsql)
		$g_db_param->push();

		$t_query->db_execute( $p_limit, $p_offset );

		# Restore ADOdb parameter count
		$g_db_param->pop();

		if( $p_pop_param && !empty( $p_arr_parms ) ) {
			$g_db_param->pop();
		}

		return $t_query->db_result;
	}

	/**
	 * Returns next row of values from current resultset, or false if empty or
	 * the pointer has reached the end.
	 * This method will execute current query if it hasn't been executed yet.
	 * @return array|boolean	Next row from result
	 */
	public function fetch() {
		if( null === $this->db_result ) {
			$this->execute();
		}
		if( !$this->db_result ) {
			return false;
		}
		$this->current_row = db_fetch_array( $this->db_result );
		return $this->current_row;
	}

	/**
	 * Returns all rows as an array
	 * @return array|boolean	Array with all rows from the result, false if result is empty.
	 */
	public function fetch_all() {
		if( null === $this->db_result ) {
			$this->execute();
		}
		if( !$this->db_result ) {
			return false;
		}
		$t_all_rows = array();
		while( $t_row = db_fetch_array( $this->db_result ) ) {
			$t_all_rows[] = $t_row;
		}
		return $t_all_rows;
	}

	/**
	 * Returns one value from current row from resultset
	 * Provided parameter can be a name of the column referenced in the query,
	 * or a numerical index (zero-based)
	 * Without column parameters,first column value will be returned.
	 *
	 * Current row is the latest one fetched, if none was fetched previously, an
	 * automatic fetch() is performed for first row
	 *
	 * @param integer|string $p_index_or_name	Column name or numeric index
	 * @return string|boolean	Value, or false if end of result or index is not valid
	 */
	public function value( $p_index_or_name = 0) {
		if( !$this->current_row ) {
			$this->fetch();
		}
		if( is_numeric( $p_index_or_name ) ) {
			if( count( $this->current_row ) > $p_index_or_name ) {
				# get the element at that numerical position
				$t_keys = array_keys( $this->current_row );
				$t_value = $this->current_row[$t_keys[$p_index_or_name]];
			} else {
				$t_value = false;
			}
		} else {
			if( isset( $this->current_row[$p_index_or_name] ) ) {
				# get the value by column name
				$t_value = $this->current_row[$p_index_or_name];
			} else {
				$t_value = false;
			}
		}
		return $t_value;
	}

	/**
	 * Alias for value()
	 * @param integer|string $p_index_or_name	Column name or numeric index
	 */
	public function field( $p_index_or_name = 0) {
		return $this->value( $p_index_or_name );
	}

	/**
	 * Creates a string construction for a case-insensitive LIKE expression
	 * This is an alias for sql_like() with the force_ci parameter set to true.
	 * @param string $p_alias		A valid sql column identifier
	 * @param string $p_pattern		Pattern string
	 * @param string $p_escape		Escape character
	 * @return string	Constructed string to be added to query
	 */
	public function sql_ilike( $p_alias, $p_pattern, $p_escape = null ) {
		return $this->sql_like( $p_alias, $p_pattern, $p_escape, true );
	}

	/**
	 * Creates a string construction for a LIKE expression, providing:
	 * - alias: is the name of the column as a valid identifier in the final sql query
	 * - value: is the string used as pattern for the like expression.
	 * - escape: optionally, a character used as escape character in the pattern string
	 * Optionally, the expression can be forced to be case insensitive, otherwise the default
	 * behaviour from the database is used.
	 *
	 * The returned string would replace the sql part for: "alias LIKE 'xxx'"
	 *
	 * For portability reasons, the supported wildcards are '%' and '_'. Other special tokens
	 * are automatically escaped:
	 * - [] syntax in mssql are treated as literal characters.
	 * - \ as default escape char in mysql is treated as a literal character.
	 * Note that the pattern string uses c-style escaping, so a "\" character must be written as "\\"
	 *
	 * The pattern string must be prepared by the caller, with proper wildcards and character escaping.
	 *
	 * @param string $p_alias		A valid sql column identifier
	 * @param string $p_pattern		Pattern string
	 * @param string $p_escape		Escape character
	 * @param boolean $p_force_ci	If true, force a case-insensitive expression
	 * @return string	Constructed string to be added to query
	 */
	public function sql_like( $p_alias, $p_pattern, $p_escape = null, $p_force_ci = false ) {
		# for mssql replace "[" as this is a special non portable token
		if( db_is_mssql() && strpos( $p_pattern, '[' ) !== false ) {
			if( null === $p_escape = null ) {
				$p_escape = '\\';
			}
			$p_pattern = str_replace( '[', $p_escape . '[', $p_pattern );
		}

		# for mysql replace "\\" if this char is nor already a explicit escape char
		# because mysql uses \ as default escape char if ESCAPE caluse is not used
		if( db_is_mysql() && $p_escape != '\\' && strpos( $p_pattern, '\\' ) !== false ) {
			if( null === $p_escape = null ) {
					$p_escape = '\\';
			}
			$p_pattern = str_replace( '\\', $p_escape . '\\', $p_pattern );
		}

		$t_expr = $p_alias;
		$t_operator = 'LIKE';
		$t_pattern = $p_pattern;

		# Here we assume that by default:
		# mysql, mssql: have case-insensitive collations
		# pgsql, oracle: have case-sensitive collations
		# Otherwise, a more complicated discovery should be implemented.
		if( $p_force_ci ) {
			global $g_db_functional_type;
			switch( $g_db_functional_type ) {
				case DB_TYPE_PGSQL:
					$t_operator = 'ILIKE';
					break;
				case DB_TYPE_ORACLE:
					$t_expr = 'upper(' . $t_expr . ')';
					$t_pattern = strtoupper( $t_pattern );
					break;
			}
		}

		$t_sql = $t_expr . ' ' . $t_operator . ' ' . $this->param( $t_pattern );
		if( null !== $p_escape ) {
			$t_sql .= ' ESCAPE ' . $this->param( $p_escape );
		}
		return $t_sql;
	}
}
