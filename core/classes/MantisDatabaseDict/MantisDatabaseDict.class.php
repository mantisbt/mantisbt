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

define( 'DB_QUERY_SUCCESS',	0 );
define( 'DB_QUERY_FAILED',	1 );

/**
 * Abstract database driver dictionary class.
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class MantisDatabaseDict {
	private $dropTable = 'DROP TABLE %s';
	private $renameTable = 'RENAME TABLE %s TO %s';
	private $dropIndex = 'DROP INDEX %s ON %s';
	private $addCol = ' ADD';
	private $alterCol = ' MODIFY';
	private $dropCol = ' DROP COLUMN';
	private $renameColumn = 'ALTER TABLE %s CHANGE COLUMN %s %s %s';	// table, old-column, new-column, column-definitions (not used by default)
	private $nameRegex = '\w';
	private $nameRegexBrackets = 'a-zA-Z0-9_\(\)';
	private $schema = false;
	private $serverInfo = array();
	private $autoIncrement = false;

	/**
	 * Loads and returns a database instance with the specified type and library.
	 * @param string $p_type database type of the driver (e.g. pdo_pgsql)
	 * @return MantisDatabase driver object or null if error
	 */
	public static function GetDriverInstance($p_type) {
		$t_type = explode( '_', $p_type );
		switch( strtolower( $t_type[0] ) ) {
			case 'pdo':
				$t_driver_type = 'PDO';
				break;
			default:
				throw new MantisBT\Exception\Database\InvalidDriver($type);
		}
		$t_classname = 'MantisDatabaseDict_' . ucfirst($t_type[1]);
		return new $t_classname();
	}

	/**
	 * Returns a SQL type from a portable type
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 */
	function ActualType($p_type)
	{
		if( $p_type[0] == 'C' && $p_type[1] == '(' ) {
			$length = substr( $p_type, 2, -1 );
			return 'VARCHAR(' . $length . ')';
		}

		switch(strtoupper($p_type)) {
			case 'XL':
				return 'LONGTEXT';
			case 'I':
				return 'INTEGER';
			case 'I2':
				return 'SMALLINT';
			case 'T':
				return 'DATETIME';
			case 'L':
				return 'TINYINT';
			case 'B':
				return 'LONGBLOB';
			case 'X':
				return 'TEXT';
			default:
				echo $p_type;
		}
	}

	/**
	 * Returns a portable type from a SQL type
	 * @param string $p_type SQL type identifier
	 * @returns string Portable type
	 */
	function GetDataDictType($p_type) {
		switch( strtoupper( $p_type ) ) {
			case 'TEXT':
				return 'X';
			case 'LONGBLOB':
				return 'B';
			case 'TINYINT':
				return 'L';
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
			default:
				echo $p_type;
		}
	}

	function NameQuote($name = NULL,$allowBrackets=false)
	{
	return $name;
		if (!is_string($name)) {
			return FALSE;
		}

		$name = trim($name);

		if ( !is_object($this->connection) ) {
			return $name;
		}

		$quote = $this->connection->nameQuote;

		// if name is of the form `name`, quote it
		if ( preg_match('/^`(.+)`$/', $name, $matches) ) {
			return $quote . $matches[1] . $quote;
		}

		// if name contains special characters, quote it
		$regex = ($allowBrackets) ? $this->nameRegexBrackets : $this->nameRegex;

		if ( !preg_match('/^[' . $regex . ']+$/', $name) ) {
			return $quote . $name . $quote;
		}

		return $name;
	}

	function TableName($p_table_name)
	{
		if ( $this->schema ) {
			return $this->NameQuote($this->schema) .'.'. $this->NameQuote($p_table_name);
		}
		return $this->NameQuote($p_table_name);
	}

	/**
	 * Executes the sql array returned by GetTableSQL and GetIndexSQL
	 * @param array $p_sql Array of SQL statements
	 * @return int DB_QUERY_SUCCESS|DB_QUERY_FAILED
	 */
	function ExecuteSQLarray($p_sql)
	{
		global $g_db;
		foreach($p_sql as $t_query) {
			$t_ret = $g_db->Execute($t_query);
			if( $t_ret === false ) {
				var_dump($t_ret); var_dump($t_query);
				return DB_QUERY_FAILED;
			}
		}
		$g_db->reset_caches();
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
		$t_sql[] = 'CREATE DATABASE ' . $this->NameQuote($p_database_name) . ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci';
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
		$t_sql[] = 'DROP DATABASE ' . $this->NameQuote($p_database_name);
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

		foreach($t_fields as $t_key => $t_field) {
			# Check if we are trying to add an index on a partial column e.g. first 32 characters of name - NAME(32)
			$t_fields[$t_key] = $this->NameQuote( $t_field, true );
		}

		return $this->_IndexSQL($this->NameQuote($p_index_name), $this->TableName($p_table_name), $t_fields, $this->_Options($p_index_options));
	}

	/**
	 * Generates the SQL to drop an index.
	 * @param string $p_index_name Index Name
	 * @param string $p_table_name Table Name
	 * @return array returns an array of sql strings.
	 */
	function DropIndex ($p_index_name, $p_table_name = null)
	{
		return array(sprintf($this->dropIndex, $this->NameQuote($p_index_name), $this->TableName($p_table_name)));
	}

	/**
	 * Generates the SQL to add a column to an existing table.
	 * @param string $p_index_name Index Name
	 * @param string $p_fields Field Definition
	 * @return array returns an array of sql strings.
	 */
	function AddColumn($p_table_name, $p_fields)
	{
		$tabname = $this->TableName ($p_table_name);
		$t_sql = array();

		list($lines,$pkey,$idxs) = $this->_GenFields($p_fields);
		// genfields can return FALSE at times
		if ($lines  == null) $lines = array();
		$alter = 'ALTER TABLE ' . $tabname . $this->addCol . ' ';
		foreach($lines as $v) {
			$t_sql[] = $alter . $v;
		}
		if (is_array($idxs)) {
			foreach($idxs as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $tabname, $idxdef['cols'], $idxdef['opts']);
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

		list($lines,$pkey,$idxs) = $this->_GenFields($p_fields);

		// genfields can return FALSE at times
		if ($lines == null) $lines = array();
		$alter = 'ALTER TABLE ' . $tabname . $this->alterCol . ' ';
		foreach($lines as $v) {
			$sql[] = $alter . $v;
		}
		if (is_array($idxs)) {
			foreach($idxs as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $tabname, $idxdef['cols'], $idxdef['opts']);
				$sql = array_merge($sql, $sql_idxs);
			}

		}
		return $sql;
	}

	/**
	 * Rename one column
	 *
	 * Some DBM's can only do this together with changeing the type of the column (even if that stays the same, eg. mysql)
	 * @param string $p_table_name Table Name
	 * @param string $oldcolumn column-name to be renamed
	 * @param string $newcolumn new column-name
	 * @param string $flds='' complete column-defintion-string like for AddColumnSQL, only used by mysql atm., default=''
	 * @return array with SQL strings
	 */
	function RenameColumn($p_table_name,$oldcolumn,$newcolumn,$flds='')
	{
		$t_table_name = $this->TableName ($p_table_name);
		if ($flds) {
			list($lines,$pkey,$idxs) = $this->_GenFields($flds);
			// genfields can return FALSE at times
			if ($lines == null) $lines = array();
			list(,$first) = each($lines);
			list(,$column_def) = preg_split("/[\t ]+/",$first,2);
		}
		return array(sprintf($this->renameColumn,$t_table_name,$this->NameQuote($oldcolumn),$this->NameQuote($newcolumn),$column_def));
	}

	/**
	 * Drop one column
	 *
	 * Some DBM's can't do that on there own, you need to supply the complete defintion of the new table,
	 * to allow, recreating the table and copying the content over to the new table
	 * @param string $p_table_name table-name
	 * @param string $p_fields column-name and type for the changed column
	 * @param string $tableflds='' complete defintion of the new table, eg. for postgres, default ''
	 * @param string $tableoptions
	 * @internal param $array /string $tableoptions='' options for the new table see CreateTableSQL, default ''
	 * @return array with SQL strings
	 */
	function DropColumn($p_table_name, $p_fields)
	{
		$t_table_name = $this->TableName ($p_table_name);
		if (!is_array($p_fields)) {
			$p_fields = explode(',',$p_fields);
		}
		$sql = array();
		$alter = 'ALTER TABLE ' . $t_table_name . $this->dropCol . ' ';
		foreach($p_fields as $v) {
			$sql[] = $alter . $this->NameQuote($v);
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
		return array (sprintf($this->dropTable, $this->TableName($p_table_name)));
	}

	/**
	 * Generates the SQL to rename an existing table.
	 * @param string $p_table_name Old Table Name
	 * @param string $p_new_table_name New Table Name
	 * @return array returns an array of sql strings.
	 */
	function RenameTable($p_table_name,$p_new_table_name)
	{
		return array (sprintf($this->renameTable, $this->TableName($p_table_name),$this->TableName($p_new_table_name)));
	}

	/**
	 Generate the SQL to create table. Returns an array of sql strings.
	*/
	function CreateTable($p_table_name, $p_fields, $p_table_options=array())
	{
		list($lines,$pkey,$idxs) = $this->_GenFields($p_fields);

		// genfields can return FALSE at times
		if ($lines == null) $lines = array();

		$taboptions = $this->_Options($p_table_options);
		$t_table_name = $this->TableName ($p_table_name);
		$sql = $this->_TableSQL($t_table_name,$lines,$pkey,$taboptions);

		if (is_array($idxs)) {
			foreach($idxs as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $t_table_name,  $idxdef['cols'], $idxdef['opts']);
				$sql = array_merge($sql, $sql_idxs);
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
	 */
	function _GenFields($p_fields_definition)
	{
		// field definition to process
		$t_input = $this->ParseFieldsString($p_fields_definition);

		// output from function - Fields, Primary Key, Indexes
		$t_fields = array();
		$t_primary_key = array();
		$t_indexes = array();

		foreach( $t_input as $t_line ) {
			$t_count = sizeof($t_line);

			$t_field = new stdClass();
			$t_field->default_value = null;
			$t_field->notnull = null;
			$t_field->fieldname = null;
			$t_field->type = null;
			$t_field->primary = null;
			$t_field->unsigned = null;
			$t_field->autoincrement = null;

			for( $i=0; $i < $t_count; $i++) {
				switch( $i ) {
					case 0:
						$t_field->fieldname = $t_line[$i];
						continue 2;
					case 1:
						$t_field->type = $this->ActualType($t_line[$i]);
						continue 2;
				}

				switch( $t_line[$i] ) {
					case 'NOTNULL':
						$t_field->notnull = true;
						break;
					case 'DEFAULT':
						$t_field->default_value = $t_line[++$i];
						break;
					case 'PRIMARY':
						$t_field->primary = true;
						$t_field->notnull = true;
						$t_primary_key[] = $t_field->fieldname;
						break;
					case 'UNSIGNED':
						$t_field->unsigned = true;
						break;
					case 'AUTOINCREMENT':
						$t_field->autoincrement = true;
						break;
					default:
						echo $t_line[$i];
						break;
				}
			}

			$suffix = $this->_CreateSuffix($t_field->fieldname,$t_field->type,$t_field->notnull,$t_field->default_value,$t_field->autoincrement,/*TODO@@@$fconstraint*/ null,$t_field->unsigned);

			$t_fields[] = $t_field->fieldname.' '.$t_field->type . $suffix;
		}

		return array($t_fields,$t_primary_key,$t_indexes);
	}

	/**
		 GENERATE THE SIZE PART OF THE DATATYPE
			$ftype is the actual type
			$ty is the type defined originally in the DDL
	*/
	function _GetSize($ftype, $ty, $fsize, $fprec)
	{
		if (strlen($fsize) && $ty != 'X' && $ty != 'B' && strpos($ftype,'(') === false) {
			$ftype .= "(".$fsize;
			if (strlen($fprec)) $ftype .= ",".$fprec;
			$ftype .= ')';
		}
		return $ftype;
	}


	// return string must begin with space
	function _CreateSuffix($fname,&$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{
		$suffix = '';
		if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fautoinc) $suffix .= ' AUTO_INCREMENT';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	function _IndexSQL($idxname, $tabname, $flds, $idxoptions) {
		$sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			$sql[] = sprintf ($this->dropIndex, $idxname, $tabname);
			if ( isset($idxoptions['DROP']) )
				return $sql;
		}

		if ( empty ($flds) ) {
			return $sql;
		}

		$unique = isset($idxoptions['UNIQUE']) ? ' UNIQUE ' : '';

		$s = 'ALTER TABLE ' . $tabname . ' ADD ' . $unique . ' INDEX ' . $idxname;

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s .= '(' . $flds . ')';
		$sql[] = $s;

		return $sql;
	}

	function _DropAutoIncrement($tabname) {
		return false;
	}

	function _TableSQL($tabname,$lines,$pkey,$tableoptions) {
		$sql = array();

		if (isset($tableoptions['REPLACE']) || isset ($tableoptions['DROP'])) {
			$sql[] = sprintf($this->dropTable,$tabname);
			if ($this->autoIncrement) {
				$sInc = $this->_DropAutoIncrement($tabname);
				if ($sInc) $sql[] = $sInc;
			}
			if ( isset ($tableoptions['DROP']) ) {
				return $sql;
			}
		}
		$s = "CREATE TABLE $tabname (\n";
		$s .= implode(",\n", $lines);
		if (sizeof($pkey)>0) {
			$s .= ",\n                 PRIMARY KEY (";
			$s .= implode(", ",$pkey).")";
		}
		if (isset($tableoptions['CONSTRAINTS']))
			$s .= "\n".$tableoptions['CONSTRAINTS'];

		$s .= "\n)";

		$s .= "ENGINE=Innodb DEFAULT CHARSET=utf8";

		$sql[] = $s;

		return $sql;
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