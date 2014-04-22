<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

 // TODO : TRIGGERS?
 // TODO - AUTONUM INTS - do they change data type in DB's
 // TODO field objects in Generatefields/GetCOlumns should be same object
 // TODO: http://uk3.php.net/manual/en/pdo.connections.php - check passwords aren't revealed
 
define( 'DB_QUERY_SUCCESS',	0 );
define( 'DB_QUERY_FAILED',	1 );

/**
 * Abstract database driver dictionary class.
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
abstract class MantisDatabaseDict {
	private $schema = false;

	/**
	 * SQL statement to Drop Table
	 */
	abstract protected function GetDropTableSQL();

	/**
	 * SQL statement to Rename Table
	 */
	abstract protected function GetRenameTableSQL();

	/**
	 * SQL statement to Drop Index
	 */
	abstract protected function GetDropIndexSQL();

	/**
	 * SQL statement to Add Column to existing Table
	 */
	abstract protected function GetAddColumnSQL();

	/**
	 * SQL statement to Alter Column in an existing Table
	 */
	abstract protected function GetAlterColumnSQL();

	/**
	 * SQL statement to Drop Column in an existing Table
	 */
	abstract protected function GetDropColumnSQL();

	/**
	 * SQL statement to Rename Column in an existing Table
	 */
	abstract protected function GetRenameColumnSQL();

	/**
	 * Loads and returns a database instance with the specified type and library.
	 * @param string $p_type database type of the driver (e.g. pdo_pgsql)
	 * @return MantisDatabase driver object or null if error
	 * @throws MantisBT\Exception\Database\InvalidDriver
	 */
	public static function GetDriverInstance($p_type) {
		$t_type = explode( '_', $p_type );
		switch( strtolower( $t_type[0] ) ) {
			case 'pdo':
				$t_driver_type = 'PDO';
				break;
			default:
				throw new MantisBT\Exception\Database\InvalidDriver( $type );
		}
		$t_classname = 'MantisDatabaseDict_' . ucfirst( $t_type[1] );
		return new $t_classname();
	}

	/**
	 * Sets SQL Schema if required
	 *
	 * @param string $p_schema Schema Name
	 * @return string
	 */
	function SetSchema($p_schema) {
		$this->schema = $p_schema;
	}

	/**
	 * Returns a SQL type from a portable type
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 * @throws MantisBT\Exception\DatabaseDict\Exception
	 */
	function ActualType($p_type) {
		if( isset( $p_type[1] ) && $p_type[1] == '(' ) {
			switch( strtoupper( $p_type[0] ) ) {
				case 'C':
					$t_length = substr( $p_type, 2, -1 );
					return array( 'VARCHAR', $t_length, null );	
				case 'N':
					$t_length = substr( $p_type, 2, -1 );
					$t_parts = explode( $t_length, ',' );
					return array( 'NUMERIC', $t_parts[0], $t_parts[1] );	
				case 'F':
					$t_length = substr( $p_type, 2, -1 );
					$t_parts = explode( $t_length, ',' );
					return array( 'FLOAT', $t_parts[0], $t_parts[1] );
			}
		}

		switch( strtoupper( $p_type ) ) {
			case 'XL':
				return 'LONGTEXT';
			case 'I':
				return 'INTEGER';
			case 'I2':
				return 'SMALLINT';
			case 'I8':
				return 'BIGINT';
			case 'T':
				return 'DATETIME';
			case 'L':
				return 'TINYINT';
			case 'B':
				return 'LONGBLOB';
			case 'X':
				return 'TEXT';
			default:
				throw new MantisBT\Exception\DatabaseDict\Exception( 'Unknown Type: ' . $p_type );
		}
	}

	/**
	 * Returns a portable type from a SQL type
	 * @param string $p_type SQL type identifier
	 * @returns string Portable type
	 * @throws MantisBT\Exception\DatabaseDict\Exception
	 */
	function GetDataDictType($p_type) {
		switch( strtoupper( $p_type ) ) {
			case 'TEXT':
				return 'X';
			case 'LONGBLOB':
				return 'B'; // may need to check if binary
			case 'TINYINT':
				return 'L'; // I1
			case 'DATETIME':
				return 'T';
			case 'SMALLINT':
				return 'I2';
			case 'INT':
				return 'I';
			case 'LONGTEXT':
				return 'XL';
			case 'VARCHAR':
				return 'C';
			case 'FLOAT':
				return 'F';
			case 'NUMERIC':
				return 'N';
			default:
				throw new MantisBT\Exception\DatabaseDict\Exception( 'Unknown Data Dict Type: ' . $p_type );
		}
	}

	/**
	 * Checks that a given string is suitable for use in an SQL identifier
	 * i.e. table name, column name etc
	 *
	 * @param string $p_name Table Name
	 * @return string
	 * @throws MantisBT\Exception\DatabaseDict\Exception
	 */
	function GetIdentifierName($p_name = null) {
		if( !is_string( $p_name ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Expected string for Identifier Name' );
		}
		
		if( preg_match( '~[^a-z0-9_.]+~i', $p_name ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Identifier Name contains invalid characters: ' . $p_name );
		}
		
		if( strlen( $p_name ) < 2 ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Identifier Name is too short: ' . $p_name );
		}
    
		if( ( strlen( $p_name ) - strlen( MantisDatabase::$dbprefix ) - strlen( MantisDatabase::$dbsuffix ) ) > 30 ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Identifier Name is too long' );
		}
		
		if( !preg_match( '~^[a-z]{1}~i', $p_name ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Identifier Name should start with a letter' );
		}

		if( MantisDatabaseDict_Keywords::isKeyword( $p_name ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Field Name may be a SQL Keyword: ' . $p_name );
		}

		$t_name = trim( $p_name );

		return $t_name;
	}
	
	/**
	 * Returns a Checked table name (including schema if necessary)
	 *
	 * @param string $p_table_name Table Name
	 * @return string
	 */
	function TableName($p_table_name) {
		$p_table_name = MantisDatabase::GetInstance()->GetTableName( $p_table_name ); // TODO

		if( $this->schema ) {
			return $this->GetIdentifierName( $this->schema ) .'.'. $this->GetIdentifierName( $p_table_name );
		}
		return $this->GetIdentifierName( $p_table_name );
	}

	/**
	 * Executes the sql array returned by GetTableSQL and GetIndexSQL
	 * @param array $p_sql Array of SQL statements
	 * @return int DB_QUERY_SUCCESS|DB_QUERY_FAILED
	 */
	function ExecuteSQLarray($p_sql) {
		// TODO -> $this->db + debug handling ?
		var_dump($p_sql);
		foreach( $p_sql as $t_query ) {
			$t_ret = MantisDatabase::GetInstance()->Execute( $t_query );
			if( $t_ret === false ) {
				var_dump($t_ret); var_dump($t_query); // TODO
				return DB_QUERY_FAILED;
			}
		}
		MantisDatabase::GetInstance()->reset_caches();
		return DB_QUERY_SUCCESS;
	}

	/**
	 * Returns SQL statements to create a database
	 * @param string $p_database_name Database Name
	 * @param string $p_options
	 * @return array
	 */
	function CreateDatabase($p_database_name,$p_options=null)
	{
		$t_sql = array();
		$t_sql[] = 'CREATE DATABASE ' . $this->GetIdentifierName( $p_database_name ) . ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci';
		return $t_sql;
	}

	/**
	 * Returns SQL statements to drop a database
	 * @param string $p_database_name Database Name
	 * @return array
	 */
	function DropDatabase($p_database_name)
	{
		$t_sql = array();
		$t_sql[] = 'DROP DATABASE ' . $this->GetIdentifierName( $p_database_name );
		return $t_sql;
	}

	/**
	 * Generates the SQL to create an index.
	 * @param string $p_index_name Index Name
	 * @param string $p_table_name table Name
	 * @param string $p_fields
	 * @param bool $p_index_options
	 * @return array returns an array of sql strings.
	 */
	function CreateIndex($p_index_name, $p_table_name, $p_fields, $p_index_options = false)
	{
		if (!is_array($p_fields)) {
			$t_fields = explode(',',$p_fields);
		} else {
			$t_fields = $p_fields;
		}

		foreach( $t_fields as $t_key => $t_field ) {
			// Trim any whitespace from identifier name
			$t_field = trim( $t_field );
			
			# TODO - PLR Check if we are trying to add an index on a partial column e.g. first 32 characters of name - NAME(32)
			$t_fields[$t_key] = $this->GetIdentifierName( $t_field, true );
		}

		return $this->_IndexSQL($this->GetIdentifierName( $p_index_name ), $this->TableName( $p_table_name ), $t_fields, $this->_Options( $p_index_options ) );
	}

	/**
	 * Generates the SQL to drop an index.
	 * @param string $p_index_name Index Name
	 * @param string $p_table_name Table Name
	 * @return array returns an array of sql strings.
	 */
	function DropIndex ($p_index_name, $p_table_name = null)
	{
		return array( sprintf( $this->GetDropIndexSQL(), $this->GetIdentifierName( $p_index_name ), $this->TableName( $p_table_name ) ) );
	}

	/**
	 * Generates the SQL to add a column to an existing table.
	 * @param string $p_table_name Table Name
	 * @param string $p_fields Field Definition
	 * @return array returns an array of sql strings.
	 */
	function AddColumn($p_table_name, $p_fields)
	{
		$t_table_name = $this->TableName( $p_table_name );
		$t_sql = array();

		$t_def = $this->GenerateFields($p_fields);

		$t_sqlfields = $this->GenerateSQLFields($t_def->fields);

		$t_alter = 'ALTER TABLE ' . $t_table_name . $this->GetAddColumnSQL() . ' ';
		foreach($t_sqlfields as $t_line) {
			$t_sql[] = $t_alter . $t_line;
		}

		if (is_array($t_def->indexes)) {
			foreach($t_def->indexes as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $t_table_name, $idxdef['cols'], $idxdef['opts']);
				$t_sql = array_merge($t_sql, $sql_idxs);
			}
		}
		return $t_sql;
	}

	/**
	 * Change the definition of one column
	 *
	 * As some DBM's can't do that on there own, you need to supply the complete defintion of the new table,
	 * to allow, recreating the table and copying the content over to the new table
	 * @param string $p_table_name Table Name
	 * @param string $p_fields Field Definition - See Mantis Database Documentation for full details
	 * @param string $tableflds='' complete defintion of the new table, eg. for postgres, default ''
	 * @param string $tableoptions
	 * @return array with SQL strings
	 */
	function AlterColumn($p_table_name, $p_fields, $tableflds='',$tableoptions='')
	{
		$tabname = $this->TableName ($p_table_name);
		$sql = array();

		$t_def = $this->GenerateFields($p_fields);
		$t_sqlfields = $this->GenerateSQLFields($t_def->fields);
		
		$alter = 'ALTER TABLE ' . $tabname . $this->GetAlterColumnSQL() . ' ';
		foreach($t_sqlfields as $v) {
			$sql[] = $alter . $v;
		}
		if (is_array($t_def->indexes)) {
			foreach($t_def->indexes as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $tabname, $idxdef['cols'], $idxdef['opts']);
				$sql = array_merge($sql, $sql_idxs);
			}
		}
		return $sql;
	}

	/**
	 * Rename one column
	 *
	 * Some DBM's can only do this together with changing the type of the column (even if that stays the same, eg. mysql)
	 * @param string $p_table_name Table Name
	 * @param string $p_old_column column-name to be renamed
	 * @param string $p_new_column new column-name
	 * @return array with SQL strings
	 */
	function RenameColumn($p_table_name,$p_old_column,$p_new_column)
	{
		$t_table_name = $this->TableName ($p_table_name);
		
		$t_columns = MantisDatabase::GetInstance()->GetColumns( $p_table_name );
		
		if( !isset( $t_columns[ $p_old_column ] ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Existing Column Must Exist' );
		}
		if( isset( $t_columns[ $p_new_column ] ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'New Column Must Not Exist' );
		}
		
		return array( sprintf( $this->GetRenameColumnSQL(), $t_table_name, $this->GetIdentifierName( $p_old_column ), $this->GetIdentifierName( $p_new_column ) ) );
	}

	/**
	 * Drop one column
	 *
	 * Some DBM's can't do that on there own, you need to supply the complete defintion of the new table,
	 * to allow, recreating the table and copying the content over to the new table
	 * @param string $p_table_name table-name
	 * @param string $p_fields column-name and type for the changed column
	 * @return array with SQL strings
	 */
	function DropColumn($p_table_name, $p_fields)
	{
		$t_table_name = $this->TableName ($p_table_name);
		if (!is_array($p_fields)) {
			$p_fields = explode(',',$p_fields);
		}
		$sql = array();
		$alter = 'ALTER TABLE ' . $t_table_name . $this->GetDropColumnSQL() . ' ';
		foreach($p_fields as $v) {
			$sql[] = $alter . $this->GetIdentifierName($v);
		}
		return $sql;
	}

	/**
	 * Generates the SQL to add an existing table.
	 * @param string $p_table_name Table Name
	 * @return array returns an array of sql strings.
	 */
	function DropTable($p_table_name)
	{
		return array (sprintf($this->GetDropTableSQL(), $this->TableName($p_table_name)));
	}

	/**
	 * Generates the SQL to rename an existing table.
	 * @param string $p_table_name Old Table Name
	 * @param string $p_new_table_name New Table Name
	 * @return array returns an array of sql strings.
	 */
	function RenameTable($p_table_name,$p_new_table_name)
	{
		return array( sprintf( $this->GetRenameTableSQL(), $this->TableName( $p_table_name ), $this->TableName( $p_new_table_name ) ) );
	}

	/**
	 Generate the SQL to create table. Returns an array of sql strings.
	*/
	function CreateTable($p_table_name, $p_fields, $p_table_options=array())
	{
		$t_def = $this->GenerateFields( $p_fields );
		$t_sqlfields = $this->GenerateSQLFields( $t_def->fields );

		$t_table_options = $this->_Options( $p_table_options );
		$t_table_name = $this->TableName( $p_table_name );
		$sql = $this->_TableSQL( $t_table_name, $t_sqlfields, $t_def->primary, $t_table_options);

		if( is_array( $t_def->indexes ) ) {
			foreach( $t_def->indexes as $idx => $idxdef ) {
				$sql_idxs = $this->CreateIndexSql( $idx, $t_table_name, $idxdef['cols'], $idxdef['opts'] );
				$sql = array_merge( $sql, $sql_idxs );
			}
		}

		return $sql;
	}

	/**
	 * Split (tables) field definition into individual lines/columns
	 * @param string $p_string
	 * @return array
	 */
	function SplitDefinition( $p_string ) {
		$t_token = strtok( trim( $p_string ), ',' );
		$t_fields = array();

		while ($t_token) {
			// find double quoted tokens
			if ($t_token{0}=='"') { $t_token .= ' '.strtok('"').'"'; }
			// find single quoted tokens
			if ($t_token{0}=="'") { $t_token .= ' '.strtok("'")."'"; }

			$t_fields[] = trim($t_token);
			$t_token = strtok(',');
		}
		return $t_fields;
	}

	/**
	 * Parse portable field definition into separate an array of components
	 *
	 * @param string $p_string SQL String
	 * @return array
	 */
	function ParseFieldsString($p_string) {
		$t_array = $this->SplitDefinition($p_string);
		$i = 0;
		$t_output = array();
		foreach ($t_array as $t_line) {
			$t_word = preg_split("/[\s,]*('[^']+')[\s,]*|[\s,]+/", $t_line, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			// remove quotes from any words that are just a number
			$t_word = preg_replace("/'(\d+)'/", '$1', $t_word);

			$t_output[$i] = $t_word;

			$i++;
		}
		return $t_output;
	}

	/**
	 * Generate Fields from Field Definition in text format
	 * @param array $p_fields_definition Array of text strings representing SQL fields
	 */
	protected function GenerateFields($p_fields_definition)
	{
		// field definition to process
		$t_input = $this->ParseFieldsString($p_fields_definition);
		if( empty( $t_input ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Invalid Field Definition' );
		}
		// output from function - Fields, Primary Key, Indexes
		$t_fields = array();
		$t_primary_key = array();
		$t_indexes = array();

		$t_names = array(); // list of field names defined

		foreach( $t_input as $t_line ) {
			$t_count = sizeof($t_line);

			$t_field = new stdClass();
			$t_field->has_default = false;
			$t_field->default_value = null;
			$t_field->notnull = false;
			$t_field->fieldname = null;
			$t_field->type = null;
			$t_field->portabletype = null;
			$t_field->primary = false;
			$t_field->autoincrement = false;
			$t_field->unsigned = false;

			for( $i=0; $i < $t_count; $i++) {
				switch( $i ) {
					case 0:
						$t_field->fieldname = $t_line[$i];
						continue 2;
					case 1:
						$t_type = $this->ActualType($t_line[$i]);
						if( is_array( $t_type ) ) {
							$t_field->type = $t_type[0];
							$t_field->size = $t_type[1];
							$t_field->precision = $t_type[2];
							$t_field->portabletype = $t_line[$i][0];
						} else {
							$t_field->type = $t_type;
							$t_field->size = null;
							$t_field->precision = null;		
							$t_field->portabletype = $t_line[$i];
						}
						continue 2;
				}

				switch( $t_line[$i] ) {
					case 'NOTNULL':
						if( $t_field->notnull == true && !( $t_field->primary == true || $t_field->autoincrement == true ) ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'Trying to set NOTNULL when already set: ' . $t_field->fieldname );
						}
						$t_field->notnull = true;
						break;
					case 'DEFAULT':
						if( $t_field->default_value !== null ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'Trying to set DEFAULT when already set' );
						}
						$t_field->has_default = true;
						$t_field->default_value = trim($t_line[++$i], "'");
						break;
					case 'PRIMARY':
						if( $t_field->primary == true ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'Trying to set PRIMARY KEY when already set' );
						}
						$t_field->primary = true;
						$t_field->notnull = true;
						$t_primary_key[] = $t_field->fieldname;
						break;
					case 'UNSIGNED':
						if( $t_field->unsigned == true ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'Trying to set UNSIGNED when already set' );
						}
						$t_field->unsigned = true;
						break;
					case 'AUTOINCREMENT':
						if( $t_field->autoincrement == true ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'Trying to set AUTOINCREMENT when already set' );
						}
						$t_field->notnull = true;
						$t_field->autoincrement = true;
						break;
					default:
						throw new MantisBT\Exception\DatabaseDict\Exception( 'Invalid Field Option: ' . $t_line[$i] );
				}
			}
					
			// Rules
			if( $t_field->fieldname == null ) {
				throw new MantisBT\Exception\DatabaseDict\Exception( 'Invalid Field Name Definition' );
			}
            if( in_array($t_field->fieldname, $t_names)) {
            	 throw new MantisBT\Exception\DatabaseDict\Exception( 'Duplicate Field Name Defined' );
            }

			// Check Field Name
			$this->GetIdentifierName( $t_field->fieldname );

			switch( $t_field->portabletype ) {
				case 'I':
				case 'I2':
				case 'I8':
					if( $t_field->default_value != null ) {
						if( !is_numeric( $t_field->default_value ) ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'INTEGER Field expects Integer Default Value' );
						}
						if( $t_field->unsigned == true && $t_field->default_value < 0 ) {
							throw new MantisBT\Exception\DatabaseDict\Exception( 'INTEGER Field expects Positive Default Value' );
						}
						switch( $t_field->portabletype ) {
							case 'I2':
								if( $t_field->default_value > (pow(2,15)-1) ) {
									throw new MantisBT\Exception\DatabaseDict\Exception( 'INTEGER Field expects Valid Default Value' );
								}					
								if( $t_field->default_value != (int)$t_field->default_value ) {
									throw new MantisBT\Exception\DatabaseDict\Exception( 'INTEGER Field expects Integer Default Values Only' );
								}
								break;
							case 'I':
								if( $t_field->default_value > ((int)(pow(2,31)-1)) ) {
									throw new MantisBT\Exception\DatabaseDict\Exception( 'INTEGER Field expects Valid Default Value' );
								}
								if( $t_field->default_value != (int)$t_field->default_value ) {
									throw new MantisBT\Exception\DatabaseDict\Exception( 'INTEGER Field expects Integer Default Values Only' );
								}
								break;
							case 'I8':
								if( $t_field->default_value > (pow(2,63)-1) ) { //note: PHP FLOAT type on 32bit platforms
									throw new MantisBT\Exception\DatabaseDict\Exception( 'BIGINT Field expects Valid Default Value' );
								}					
								if( strpos( $t_field->default_value, ',' ) !== false || strpos( $t_field->default_value, '.' ) !== false ) {
									throw new MantisBT\Exception\DatabaseDict\Exception( 'BIGINT Field expects Integer Default Values Only' );
								}
								break;
						}
					}
					break;
				case 'XL':
					if( $t_field->default_value != null ) {
						throw new MantisBT\Exception\DatabaseDict\Exception( 'BLOB/TEXT Columns can not have Default Values' );
					}
					break;
				case 'T':
					// DEFAULT?
					break;
				case 'L':
					if( $t_field->default_value != null ) {
						if( !is_numeric( $t_field->default_value ) ) {
							if( $t_field->default_value == 'NULL' ) {
								$t_field->default_value = null;
							} else {
								throw new MantisBT\Exception\DatabaseDict\Exception( 'BOOLEAN Field expects 0 or 1 Default Value' );
							}
						} else { 
							switch( (int)$t_field->default_value ) {
								case 0:
									$t_field->default_value = false;
									break;
								case 1:
									$t_field->default_value = true;
									break;
								default:
									throw new MantisBT\Exception\DatabaseDict\Exception( 'BOOLEAN Field expects 0 or 1 Default Value' );
							}
						}
					}
					break;
				case 'B':
					if( $t_field->notnull == true ) {
						//throw new MantisBT\Exception\DatabaseDict\Exception( 'TODO PLR' );
					}
					break;
				case 'X':
					if( $t_field->default_value != null ) {
						//throw new MantisBT\Exception\DatabaseDict\Exception( 'TODO PLR' );
					}
					if( $t_field->notnull == true ) {
						//throw new MantisBT\Exception\DatabaseDict\Exception( 'TODO PLR' );
					}
					break;
				case 'C':
					if( $t_field->size > 4000 ) {
						throw new MantisBT\Exception\DatabaseDict\Exception( 'VARCHAR length ( ' . $t_field->size . ') is too long' );
					}
					break;
				case 'N':
					break;
				case 'F':
					break;

			}

			$t_fields[] = $t_field;
			$t_names[] = $t_field->fieldname;
		}

		$t_result = new stdClass();
		$t_result->fields = $t_fields;
		$t_result->primary = $t_primary_key;
		$t_result->indexes = $t_indexes;
		
		return $t_result;
	}

	/**
	 * Returns SQL suffix for a given field
	 *
	 * @param string $p_field Field Definition
	 * @return string SQL string beginning with a space 
	 */
	function _CreateSuffix(&$p_field)
	{
		$t_suffix = '';
		if ($p_field->unsigned) $t_suffix .= ' UNSIGNED';
		if ($p_field->notnull) $t_suffix .= ' NOT NULL';
		if( $p_field->has_default == true ) {
			if( $p_field->default_value === false ) {
				$t_suffix .= ' DEFAULT \'0\'';
			} elseif ( $p_field->default_value === true ) {
				$t_suffix .= ' DEFAULT \'1\'';
			} elseif ( $p_field->default_value === null ) {
				$t_suffix .= ' DEFAULT NULL';
			} else {
				$t_suffix .= ' DEFAULT \'' . $p_field->default_value . '\'';
			}
		}
		if ($p_field->autoincrement) $t_suffix .= ' AUTO_INCREMENT';
		return $t_suffix;
	}

	/**
	 * Returns array of valid SQL for field columns
	 *
	 * @param array $p_fields Array of Field Definition Objects
	 * @return array
	 */
	function GenerateSQLFields( $p_fields ) {
		$t_fields = array();
		foreach( $p_fields as $t_field ) {
			$suffix = $this->_CreateSuffix($t_field);

			if( $t_field->size !== null ) {
				if( $t_field->precision !== null ) {
					$t_fields[] = $t_field->fieldname.' '.$t_field->type . '(' . $t_field->size . ',' . $t_field->precision . ')' . $suffix;
				} else {
					$t_fields[] = $t_field->fieldname.' '.$t_field->type . '(' . $t_field->size . ')' . $suffix;
				}
			} else {
				$t_fields[] = $t_field->fieldname.' '.$t_field->type . $suffix;
			}
		}
		return $t_fields;
	}

	/**
	 *
	 * @param string $p_index_name Index Name
	 * @param string $p_table_name Table Name
	 * @param string $p_fields Fields
	 * @param array $p_index_options
	 * @throws MantisBT\Exception\DatabaseDict\Exception
	 */
	function _IndexSQL($p_index_name, $p_table_name, $p_fields, $p_index_options) {
		$t_sql = array();

		if ( isset($p_index_options['REPLACE']) || isset($p_index_options['DROP']) ) {
			$t_sql = $this->DropIndex( $p_index_name, $p_table_name );
			if ( isset($p_index_options['DROP']) )
				return $t_sql;
		}

		if ( empty ($p_fields) ) {
			return $t_sql;
		}

		$unique = isset($p_index_options['UNIQUE']) ? ' UNIQUE ' : '';

		$s = 'ALTER TABLE ' . $p_table_name . ' ADD ' . $unique . ' INDEX ' . $p_index_name;

		if ( is_array($p_fields) )
			$p_fields = implode(', ',$p_fields);
		$s .= '(' . $p_fields . ')';
		$t_sql[] = $s;

		return $t_sql;
	}


	/**
	 *
	 * @param string $p_table_name Table Name
	 */
	function _DropAutoIncrement($p_table_name) {
		return false;
	}


	/**
	 *
	 * @param string $p_table_name Table Name
	 * @param array $p_fields Fields
	 * @param array $p_primary_key Primary Key Fields
	 * @param array $p_table_options
	 */
	function _TableSQL($p_table_name,$p_fields,$p_primary_key,$p_table_options) {
		$t_sql = array();

		$s = "CREATE TABLE $p_table_name (\n";
		$s .= implode(",\n", $p_fields);
		if (sizeof($p_primary_key)>0) {
			$s .= ",\n                 PRIMARY KEY (";
			$s .= implode(", ",$p_primary_key) . ")";
		}
		if (isset($p_table_options['CONSTRAINTS']))
			$s .= "\n" . $p_table_options['CONSTRAINTS'];

		$s .= "\n)";

		$s .= "ENGINE=Innodb DEFAULT CHARSET=utf8";

		$t_sql[] = $s;

		return $t_sql;
	}

	/**
	 * Sanitize options, so that array elements with no keys are promoted to keys
	 * @param array $p_options Options Array
	 * @return array
	 */
	function _Options($p_options) {
		if (!is_array($p_options)) return array();
		$newopts = array();
		foreach($p_options as $t_key => $t_value) {
			if (is_numeric($t_key)) $newopts[strtoupper($t_value)] = $t_value;
			else $newopts[strtoupper($t_key)] = $t_value;
		}
		return $newopts;
	}
}