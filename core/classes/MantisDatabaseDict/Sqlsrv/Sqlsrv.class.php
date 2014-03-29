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
 * SQL Server database dictionary class.
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class MantisDatabaseDict_Sqlsrv extends MantisDatabaseDict {
	private $dropIndex = 'DROP INDEX %s';
	private $alterCol = ' ALTER COLUMN';
	private $dropCol = ' DROP COLUMN';
	private $renameColumn = "EXEC sp_rename '%s.%s','%s'";

	/**
	 * Returns SQL statements to create a database
	 * @param string $p_database_name Database Name
	 * @param string $p_options
	 * @return array
	 */
	public function CreateDatabase($p_database_name,$p_options=false) {
		$t_sql = array();
		$t_sql[] = 'CREATE DATABASE ' . $this->NameQuote($p_database_name);
		return $t_sql;
	}

	public function DropDatabase($dbname) {
		$sql = array();
		$sql[] = 'ALTER DATABASE ' . $this->NameQuote($dbname) . ' SET SINGLE_USER WITH ROLLBACK IMMEDIATE;';
		$sql[] = 'DROP DATABASE ' . $this->NameQuote($dbname);
		return $sql;
	}

	/**
	 * Returns a SQL type from a portable type
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 */
	public function ActualType($p_type) {
		if( $p_type[0] == 'C' && $p_type[1] == '(' ) {
			$length = substr($p_type, 2, -1);
			return 'NVARCHAR(' . $length . ')';
		}

		switch(strtoupper($p_type)) {
			case 'XL':
				return 'NVARCHAR(MAX)';
			case 'I':
				return 'INTEGER';
			case 'I2':
				return 'SMALLINT';
			case 'T':
				return 'DATETIME';
			case 'L':
				return 'TINYINT';
			case 'B':
				return 'VARBINARY(MAX)';
			case 'X':
				return 'NVARCHAR(4000)';
			default:
				echo $p_type;
		}
	}


	public function NameQuote($name = null,$allowBrackets=false) {
		return $name;
		if (!is_string($name)) {
			return false;
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

	public function TableName($name) {
		if ( $this->schema ) {
			return $this->NameQuote($this->schema) . '.' . $this->NameQuote($name);
		}
		return $this->NameQuote($name);
	}


	/*
	 Generates the SQL to create index. Returns an array of sql strings.
	*/
	function CreateIndex($idxname, $tabname, $flds, $idxoptions = false) {
		if (!is_array($flds)) {
			$flds = explode(',',$flds);
		}

		foreach($flds as $key => $fld) {
			# some indexes can use partial fields, eg. index first 32 chars of "name" with NAME(32)
			$flds[$key] = $this->NameQuote($fld,$allowBrackets=true);
		}

		return $this->_IndexSQL($this->NameQuote($idxname), $this->TableName($tabname), $flds, $this->_Options($idxoptions));
	}

	function DropIndex ($idxname, $tabname = null) {
		return array( "IF EXISTS (SELECT * FROM sysobjects WHERE name = '" . $this->NameQuote($idxname) ."')
					ALTER TABLE " . $this->TableName($tabname) . " DROP CONSTRAINT " . $this->NameQuote($idxname) . "
				ELSE
					DROP INDEX " . $this->NameQuote($idxname) . " ON " . $this->TableName($tabname));
	}

	function AddColumnSQL($tabname, $flds) {
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
	function AlterColumnSQL($tabname, $flds, $tableflds='',$tableoptions='') {
		global $g_db;

		$tabname = $this->TableName ($tabname);
		$sql = array();

		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		// genfields can return FALSE at times
		if ($lines == null) $lines = array();
		$alter = 'ALTER TABLE ' . $tabname . $this->alterCol . ' ';

		foreach($lines as $v) {

	       $not_null = false;
	        if ($not_null = preg_match('/NOT NULL/i',$v)) {
	           $v = preg_replace('/NOT NULL/i','',$v);
	        }

			if (preg_match('/^([^ ]+) .*DEFAULT (\'[^\']+\'|\"[^\"]+\"|[^ ]+)/',$v,$matches)) {
	            list(,$colname,$default) = $matches;
				//$existing = $this->MetaColumns($tabname);
				$constraintname = false;
				$rs = $g_db->Execute( "select name from sys.default_constraints WHERE object_name(parent_object_id) = '" . $tabname . "' AND col_name(parent_object_id, parent_column_id) = '" . $colname . "'");
				if ( $rs ) {
					$t_row = $rs->fetch();
					if ( $t_row['name'] !== null ) {
						$constraintname = $t_row['name'];
					} else {
						$constraintname = $t_row[0];
					}
				}
	            $v = preg_replace('/^' . preg_quote($colname) . '\s/', '', $v);
	            $t = trim(str_replace('DEFAULT ' . $default, '', $v));
				if ( $constraintname != false ) {
					$sql[] = 'ALTER TABLE ' . $tabname . ' DROP CONSTRAINT ' . $constraintname;
				}
				$sql[] = $alter . $colname . ' ' . $t ;
				if ( $constraintname != false ) {
					$sql[] = 'ALTER TABLE ' . $tabname . ' ADD CONSTRAINT ' . $constraintname . ' DEFAULT ' . $default . ' FOR ' . $colname;
				} else {
					$sql[] = 'ALTER TABLE ' . $tabname . ' ADD CONSTRAINT DF__' . $tabname . '__' . $colname . '__' . dechex(rand()) . ' DEFAULT ' . $default . ' FOR ' . $colname;
				}
				if ($not_null) {
					$sql[] = $alter . $colname . ' ' . $t  . ' NOT NULL';
				}
			} else {
				if ($not_null) {
					$sql[] = $alter . $v  . ' NOT NULL';
		         } else {
					$sql[] = $alter . $v;
				}
			}
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
	 * @param string $tabname table-name
	 * @param string $oldcolumn column-name to be renamed
	 * @param string $newcolumn new column-name
	 * @param string $flds='' complete column-defintion-string like for AddColumnSQL, only used by mysql atm., default=''
	 * @return array with SQL strings
	 */
	function RenameColumn($tabname,$oldcolumn,$newcolumn,$flds='') {
		$tabname = $this->TableName ($tabname);
		if ($flds) {
			list($lines,$pkey,$idxs) = $this->_GenFields($flds);
			// genfields can return FALSE at times
			if ($lines == null) $lines = array();
			list(,$first) = each($lines);
			list(,$column_def) = preg_split("/[\t ]+/",$first,2);
		}
		return array(sprintf($this->renameColumn,$tabname,$this->NameQuote($oldcolumn),$this->NameQuote($newcolumn),$column_def));
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
	function DropColumnSQL($tabname, $flds, $tableflds='',$tableoptions='') {
		global $g_db;
		$tabname = $this->TableName ($tabname);
		if (!is_array($flds))
			$flds = explode(',',$flds);
		$f = array();
		$s = 'ALTER TABLE ' . $tabname;
		foreach($flds as $v) {
			$rs = $g_db->Execute( "select name from sys.default_constraints WHERE object_name(parent_object_id) = '" . $tabname ."' AND col_name(parent_object_id, parent_column_id) = '" . $v . "'");
			if ( $rs ) {
				$t_row = $rs->fetch();
				if( $t_row ) {
					$constraintname = $t_row['name'];
					$sql[] = 'ALTER TABLE '.$tabname.' DROP CONSTRAINT '. $constraintname;
				}
			}
			$f[] = "\n$this->dropCol ".$this->NameQuote($v);
		}
		$s .= implode(', ',$f);
		$sql[] = $s;
		return $sql;
	}

	function DropTableSQL($tabname) {
		return array (sprintf($this->dropTable, $this->TableName($tabname)));
	}

	function RenameTableSQL($tabname, $newname) {
		return array (sprintf($this->renameTable, $this->TableName($tabname), $this->TableName($newname)));
	}

	/**
	 Generate the SQL to create table. Returns an array of sql strings.
	*/
	function CreateTableSQL($tabname, $flds, $tableoptions=array()) {
		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		// genfields can return FALSE at times
		if ($lines == null) $lines = array();

		$taboptions = $this->_Options($tableoptions);
		$tabname = $this->TableName ($tabname);
		$sql = $this->_TableSQL($tabname, $lines, $pkey, $taboptions);

		if (is_array($idxs)) {
			foreach($idxs as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $tabname, $idxdef['cols'], $idxdef['opts']);
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
	function _GetSize($ftype, $ty, $fsize, $fprec) {
		if (strlen($fsize) && $ty != 'X' && $ty != 'B' && strpos($ftype, '(') === false) {
			$ftype .= "(".$fsize;
			if (strlen($fprec)) $ftype .= ",".$fprec;
			$ftype .= ')';
		}
		return $ftype;
	}


	// return string must begin with space
	function _CreateSuffix($fname,&$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned) {
		$suffix = '';
		//if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= ' DEFAULT ' . $fdefault;
		if ($fautoinc) $suffix .= ' IDENTITY(1,1)';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	function _IndexSQL($idxname, $tabname, $flds, $idxoptions) {
		$t_sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			$t_sql[] = sprintf ($this->dropIndex, $idxname, $tabname);
			if ( isset($idxoptions['DROP']) )
				return $t_sql;
		}

		if ( empty ($flds) ) {
			return $t_sql;
		}

		$unique = isset($idxoptions['UNIQUE']) ? ' UNIQUE' : '';

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s = 'CREATE' . $unique . ' INDEX ' . $idxname . ' ON ' . $tabname . ' (' . $flds . ')';

		$t_sql[] = $s;

		return $t_sql;
	}

	public function _DropAutoIncrement($tabname) {
		return false;
	}

	public function _TableSQL($tabname,$lines,$pkey,$tableoptions) {
		$t_sql = array();

		if (isset($tableoptions['REPLACE']) || isset ($tableoptions['DROP'])) {
			$t_sql[] = sprintf($this->dropTable,$tabname);
			if ($this->autoIncrement) {
				$sInc = $this->_DropAutoIncrement($tabname);
				if ($sInc) $t_sql[] = $sInc;
			}
			if ( isset ($tableoptions['DROP']) ) {
				return $t_sql;
			}
		}
		$s = "CREATE TABLE $tabname (\n";
		$s .= implode(",\n", $lines);
		if (sizeof($pkey)>0) {
			$s .= ",\n                 PRIMARY KEY (";
			$s .= implode(', ', $pkey) . ')';
		}
		if (isset($tableoptions['CONSTRAINTS']))
			$s .= "\n" . $tableoptions['CONSTRAINTS'];

		$s .= "\n)";

		$t_sql[] = $s;

		return $t_sql;
	}
}