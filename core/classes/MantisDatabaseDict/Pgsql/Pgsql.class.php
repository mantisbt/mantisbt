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

/**
 * PGSQL database dictionary class.
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class MantisDatabaseDict_Pgsql extends MantisDatabaseDict {
	protected $dropIndex = 'DROP INDEX %s';
	protected $alterCol = ' ALTER COLUMN';
	protected $dropCol = ' DROP COLUMN';
	protected $dropTable = 'DROP TABLE %s CASCADE';
	protected $renameColumn = 'ALTER TABLE %s RENAME COLUMN %s TO %s';

	/**
	 * Returns SQL statements to create a database
	 * @param string $p_database_name Database Name
	 * @param string $p_options
	 * @return array
	 */
	function CreateDatabase($p_database_name,$p_options=false)
	{
		$t_sql = array();
		$t_sql[] = 'CREATE DATABASE ' . $p_database_name;
		return $t_sql;
	}

	/**
	 * Returns a SQL type from a portable type
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 */
	function ActualType($p_type)
	{
		if( $p_type[0] == 'C' && $p_type[1] == '(' ) {
			$length = substr($p_type,2, -1);
			return 'VARCHAR(' . $length . ')';
		}

		switch(strtoupper($p_type)) {
			case 'XL':
				return 'TEXT';
			case 'I':
				return 'INTEGER';
			case 'I2':
				return 'INT2';
			case 'T':
				return 'TIMESTAMP';
			case 'L':
				return 'SMALLINT';
			case 'B':
				return 'BYTEA';
			case 'X':
				return 'VARCHAR(4000)';
			default:
				echo $p_type;
		}
	}

	/**
	 * Returns a portable type from a SQL type
	 * @param string $p_type SQL type identifier
	 * @returns string Portable type
	 */
	function GetDataDictType($t_type) {
		switch( strtoupper( $t_type ) ) {
			case 'TEXT':
				return 'XL';
			case 'BYTEA':
				return 'B';
			case 'TINYINT':
				return 'L';
			case 'TIMESTAMP':
				return 'T';
			case 'SMALLINT':
				return 'I2';
			case 'INT4':
				return 'I';
			case 'VARCHAR':
				return 'C';
			default:
				echo $t_type;
		}
	}

	function TableName($name)
	{
		return $name;
	}


	/*
	 Generates the SQL to create index. Returns an array of sql strings.
	*/
	function CreateIndexSQL($idxname, $tabname, $flds, $idxoptions = false)
	{
		if (!is_array($flds)) {
			$flds = explode(',',$flds);
		}

		foreach($flds as $key => $fld) {
			# some indexes can use partial fields, eg. index first 32 chars of "name" with NAME(32)
			$flds[$key] = $fld;
		}

		return $this->_IndexSQL($idxname, $this->TableName($tabname), $flds, $this->_Options($idxoptions));
	}

	function DropIndexSQL ($idxname, $tabname = NULL)
	{
		return array(sprintf($this->dropIndex, $idxname, $this->TableName($tabname)));
	}

	function AddColumnSQL($tabname, $flds)
	{
		$tabname = $this->TableName ($tabname);
		$sql = array();
		//var_dump($flds); die;
		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		// genfields can return FALSE at times
		if ($lines  == null) $lines = array();
		$alter = 'ALTER TABLE ' . $tabname . $this->addCol . ' ';
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
	 * Change the definition of one column
	 *
	 * As some DBM's can't do that on there own, you need to supply the complete defintion of the new table,
	 * to allow, recreating the table and copying the content over to the new table
	 * @param string $tabname table-name
	 * @param string $flds column-name and type for the changed column
	 * @param string $tableflds='' complete defintion of the new table, eg. for postgres, default ''
	 * @param string $tableoptions
	 * @internal param $array /string $tableoptions='' options for the new table see CreateTableSQL, default ''
	 * @return array with SQL strings
	 */
	function AlterColumn($tabname, $flds, $tableflds='',$tableoptions='')
	{
		global $g_db;

		$tabname = $this->TableName ($tabname);
		$sql = array();

		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		if ($lines == null) $lines = array();

		$set_null = false;
		// genfields can return FALSE at times
		$alter = 'ALTER TABLE ' . $tabname . $this->alterCol . ' ';

		foreach($lines as $v) {
			foreach($lines as $v) {
				if ($not_null = preg_match('/NOT NULL/i',$v)) {
					$v = preg_replace('/NOT NULL/i','',$v);
				}
				 // this next block doesn't work - there is no way that I can see to
				 // explicitly ask a column to be null using $flds
				else if ($set_null = preg_match('/NULL/i',$v)) {
					// if they didn't specify not null, see if they explicitely asked for null
					// Lookbehind pattern covers the case 'fieldname NULL datatype DEFAULT NULL'
					// only the first NULL should be removed, not the one specifying
					// the default value
					$v = preg_replace('/(?<!DEFAULT)\sNULL/i','',$v);
				}

				if (preg_match('/^([^ ]+) .*DEFAULT (\'[^\']+\'|\"[^\"]+\"|[^ ]+)/',$v,$matches)) {
					$existing = $g_db->GetColumns($tabname);
					list(,$colname,$default) = $matches;
					$alter .= $colname;
					var_dump($existing);
					var_dump( $existing[strtolower($colname)] );
					if ($this->connection) {
						$old_coltype = $this->connection->MetaType($existing[strtoupper($colname)]);
					}
					else {
						$old_coltype = $t;
					}
					$v = preg_replace('/^' . preg_quote($colname) . '\s/', '', $v);
					$t = trim(str_replace('DEFAULT '.$default,'',$v));

					// Type change from bool to int
					if ( $old_coltype == 'L' && $t == 'INTEGER' ) {
						$sql[] = $alter . ' DROP DEFAULT';
						$sql[] = $alter . " TYPE $t USING ($colname::BOOL)::INT";
						$sql[] = $alter . " SET DEFAULT $default";
					}
					// Type change from int to bool
					else if ( $old_coltype == 'I' && $t == 'BOOLEAN' ) {
						if( strcasecmp('NULL', trim($default)) != 0 ) {
							$default = $this->connection->qstr($default);
						}
						$sql[] = $alter . ' DROP DEFAULT';
						$sql[] = $alter . " TYPE $t USING CASE WHEN $colname = 0 THEN false ELSE true END";
						$sql[] = $alter . " SET DEFAULT $default";
					}
					// Any other column types conversion
					else {
						$sql[] = $alter . " TYPE $t";
						$sql[] = $alter . " SET DEFAULT $default";
					}

				}
				else {
					// drop default?
					preg_match ('/^\s*(\S+)\s+(.*)$/',$v,$matches);
					list (,$colname,$rest) = $matches;
					$alter .= $colname;
					$sql[] = $alter . ' TYPE ' . $rest;
				}

#				list($colname) = explode(' ',$v);
				if ($not_null) {
					// this does not error out if the column is already not null
					$sql[] = $alter . ' SET NOT NULL';
				}
				if ($set_null) {
					// this does not error out if the column is already null
					$sql[] = $alter . ' DROP NOT NULL';
				}
			}
			return $sql;
		}
	}

	/**
	 * Rename one column
	 *
	 * Some DBM's can only do this together with changeing the type of the column (even if that stays the same, eg. mysql)
	 * @param string $tabname table-name
	 * @param string $oldcolumn column-name to be renamed
	 * @param string $newcolumn new column-name
	 * @param string $flds='' complete column-defintion-string like for AddColumnSQL, only used by mysql atm., default=''
	 * @return array with SQL strings
	 */
	function RenameColumn($tabname,$oldcolumn,$newcolumn,$flds='')
	{
		$tabname = $this->TableName ($tabname);
		if ($flds) {
			list($lines,$pkey,$idxs) = $this->_GenFields($flds);
			// genfields can return FALSE at times
			if ($lines == null) $lines = array();
			list(,$first) = each($lines);
			list(,$column_def) = preg_split("/[\t ]+/",$first,2);
		}
		return array(sprintf($this->renameColumn,$tabname,$oldcolumn,$newcolumn,$column_def));
	}

	/**
	 * Drop one column
	 *
	 * Some DBM's can't do that on there own, you need to supply the complete defintion of the new table,
	 * to allow, recreating the table and copying the content over to the new table
	 * @param string $tabname table-name
	 * @param string $flds column-name and type for the changed column
	 * @param string $tableflds='' complete defintion of the new table, eg. for postgres, default ''
	 * @param string $tableoptions
	 * @internal param $array /string $tableoptions='' options for the new table see CreateTableSQL, default ''
	 * @return array with SQL strings
	 */
	/*function DropColumn($tabname, $flds, $tableflds='',$tableoptions='')
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
	}*/

	function DropTable($tabname)
	{
		return array (sprintf($this->dropTable, $this->TableName($tabname)));
	}

	function RenameTable($tabname,$newname)
	{
		return array (sprintf($this->renameTable, $this->TableName($tabname),$this->TableName($newname)));
	}

	/**
	 Generate the SQL to create table. Returns an array of sql strings.
	*/
	function CreateTable($tabname, $flds, $tableoptions=array())
	{
		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		// genfields can return FALSE at times
		if ($lines == null) $lines = array();

		$taboptions = $this->_Options($tableoptions);
		$tabname = $this->TableName ($tabname);
		$sql = $this->_TableSQL($tabname,$lines,$pkey,$taboptions);

		if (is_array($idxs)) {
			foreach($idxs as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $tabname,  $idxdef['cols'], $idxdef['opts']);
				$sql = array_merge($sql, $sql_idxs);
			}
		}

		return $sql;
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
		if ($fautoinc) {
			$ftype = 'SERIAL';
			return '';
		}
		//if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fautoinc) $suffix .= ' IDENTITY(1,1)';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	function _IndexSQL($idxname, $tabname, $flds, $idxoptions)
	{
		$sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			$sql[] = sprintf ($this->dropIndex, $idxname, $tabname);
			if ( isset($idxoptions['DROP']) )
				return $sql;
		}

		if ( empty ($flds) ) {
			return $sql;
		}

		$unique = isset($idxoptions['UNIQUE']) ? ' UNIQUE' : '';

		$s = 'CREATE' . $unique . ' INDEX ' . $idxname . ' ON ' . $tabname . ' ';

		//if (isset($idxoptions['HASH']))
		//	$s .= 'USING HASH ';

		//if ( isset($idxoptions[$this->upperName]) )
		//	$s .= $idxoptions[$this->upperName];

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s .= '(' . $flds . ')';
		$sql[] = $s;

		return $sql;
	}

	function _DropAutoIncrement($tabname)
	{
		return false;
	}

	function _TableSQL($tabname,$lines,$pkey,$tableoptions)
	{
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
		$s .= " WITHOUT OIDS";

		$sql[] = $s;

		return $sql;
	}
}