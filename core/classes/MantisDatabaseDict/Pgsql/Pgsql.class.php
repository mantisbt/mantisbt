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
	/**
	 * {@inheritDoc}
	 */
	protected function GetDropTableSQL() {
		return 'DROP TABLE %s CASCADE';
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function GetRenameTableSQL() {
		return 'ALTER TABLE %s RENAME TO %s';
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function GetDropIndexSQL() {
		return 'DROP INDEX %s';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetAddColumnSQL() {
		return ' ADD COLUMN';
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function GetAlterColumnSQL(){
		return ' ALTER COLUMN';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetDropColumnSQL(){
		return ' DROP COLUMN';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetRenameColumnSQL() {
		return 'ALTER TABLE %s RENAME COLUMN %s TO %s';	
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_database_name Database Name
	 * @param string $p_options
	 * @return array
	 */
	function CreateDatabase($p_database_name,$p_options=false) {
		$t_sql = array();
		$t_sql[] = 'CREATE DATABASE ' . $p_database_name;
		return $t_sql;
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 * @throws MantisBT\Exception\DatabaseDict\Exception
	 */
	function ActualType($p_type) {
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
				return 'BOOLEAN';
			case 'B':
				return 'BYTEA';
			case 'X':
				return 'VARCHAR(4000)';
			case 'I8':
				return 'INT8';
			case 'F':
				return 'FLOAT8';
			case 'N':
				return 'NUMERIC';
			default:
				echo $p_type;
		}
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_type SQL type identifier
	 * @returns string Portable type
	 * @throws MantisBT\Exception\DatabaseDict\Exception
	 */
	function GetDataDictType($t_type) {
	/*
			$is_serial = is_object($fieldobj) && !empty($fieldobj->primary_key) && !empty($fieldobj->unique) &&
			!empty($fieldobj->has_default) && substr($fieldobj->default_value,0,8) == 'nextval(';
	*/
	
		switch( strtoupper( $t_type ) ) {
			case 'TEXT':
				return 'XL';
			case 'BYTEA':
				return 'B';
			case 'BOOLEAN':
				return 'L';
			case 'TIMESTAMP':
				return 'T';
			case 'INT2':
				return 'I2';
			case 'INT4':
				return 'I';
			case 'INT8':
				return 'I8';
			case 'VARCHAR':
				return 'C';
			case 'FLOAT8':
				return 'F';
			case 'NUMERIC':
				return 'N';
//			case 'SERIAL':
//				return 'R';
			default:
				echo $t_type;
		}
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_table_name Table Name
	 * @param string $p_fields Field Definition
	 * @return array returns an array of sql strings.
	 */
	function AddColumn($p_table_name, $p_fields) {
		$t_table_name = $this->TableName ($p_table_name);
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

		/*list($lines,$pkey,$idxs) = $this->_GenFields($p_fields);
		// genfields can return FALSE at times
		if ($lines  == null) $lines = array();
		$alter = 'ALTER TABLE ' . $t_table_name . $this->addCol . ' ';
		foreach($lines as $v) {
			if (($not_null = preg_match('/NOT NULL/i',$v))) {
				$v = preg_replace('/NOT NULL/i','',$v);
			}
			if (preg_match('/^([^ ]+) .*DEFAULT (\'[^\']+\'|\"[^\"]+\"|[^ ]+)/',$v,$matches)) {
				list(,$colname,$default) = $matches;
				$sql[] = $alter . str_replace('DEFAULT '.$default,'',$v);
				$sql[] = 'UPDATE '.$t_table_name.' SET '.$colname.'='.$default;
				$sql[] = 'ALTER TABLE '.$t_table_name.' ALTER COLUMN '.$colname.' SET DEFAULT ' . $default;
			} else {
				$sql[] = $alter . $v;
			}
			if ($not_null) {
				list($colname) = explode(' ',$v);
				$sql[] = 'ALTER TABLE '.$t_table_name.' ALTER COLUMN '.$colname.' SET NOT NULL';
			}
		}
		if (is_array($idxs)) {
			foreach($idxs as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $t_table_name, $idxdef['cols'], $idxdef['opts']);
				$sql = array_merge($sql, $sql_idxs);
			}
		}
		return $sql;*/
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_table_name Table Name
	 * @param string $p_fields Field Definition - See Mantis Database Documentation for full details
	 * @param string $tableflds='' complete defintion of the new table, eg. for postgres, default ''
	 * @param string $tableoptions
	 */
	function AlterColumn($p_table_name, $p_fields, $tableflds='',$tableoptions='') {
		$tabname = $this->TableName ($p_table_name);
		$sql = array();

		$t_def = $this->GenerateFields($p_fields);
		//$t_sqlfields = $this->GenerateSQLFields($t_def->fields);

		$alter = 'ALTER TABLE ' . $tabname . $this->GetAlterColumnSQL() . ' ';

		foreach($t_def->fields as $t_field) {
			$suffix = $this->_CreateSuffix($t_field);

			// Get New Type
			$t_type = $t_field->type;
			if( $t_field->size !== null ) {
				if( $t_field->precision !== null ) {
					$t_type = $t_field->type . '(' . $t_field->size . ',' . $t_field->precision . ')';
				} else {
					$t_type = $t_field->type . '(' . $t_field->size . ')';
				}
			}
			
			$t_existing = MantisDatabase::GetInstance()->GetColumns($p_table_name);
			
			if( !isset( $t_existing[$t_field->fieldname] ) )
				throw new MantisBT\Exception\DatabaseDict\Exception( 'Existing Column not found in ALTER COLUMN' );
			$t_db_field = $t_existing[$t_field->fieldname];
			
			var_dump($t_db_field);
			
			// check if type changes
			if( $t_field->type != $t_db_field->type || $t_field->size != $t_db_field->size /* todo also size / precision */ ) {
				
				if( $t_db_field->type == 'bool' && $t_field->type == 'INTEGER' ) {
					$sql[] = $alter . $t_field->fieldname . ' DROP DEFAULT'; // will be re-added below
					$sql[] = $alter . $t_field->fieldname . ' TYPE ' . $t_type . ' USING (' . $t_field->fieldname . '::BOOL)::INT';
				}
				if( $t_db_field->type == 'int4' && $t_field->type == 'BOOLEAN' ) {
					$sql[] = $alter . $t_field->fieldname . ' DROP DEFAULT'; // will be re-added below
					$sql[] = $alter . $t_field->fieldname . ' TYPE ' . $t_type . ' USING CASE WHEN ' . $t_field->fieldname . ' = 0 THEN false ELSE true END';
				}				
				
				$sql[] = $alter . $t_field->fieldname . ' TYPE ' . $t_type;
			} else {
				throw new MantisBT\Exception\DatabaseDict\Exception("TODO - PLR 1" );
			}
			
			if( $t_field->has_default == true) {
				if( $t_field->default_value === false ) {
					$sql[] = $alter . $t_field->fieldname . ' SET DEFAULT \'0\'';
				} elseif ( $t_field->default_value === true ) {
					$sql[] = $alter . $t_field->fieldname . ' SET DEFAULT \'1\'';
				} elseif ( $t_field->default_value === null ) {
					$sql[] = $alter . $t_field->fieldname . ' SET DEFAULT NULL';
				} else {
					$sql[] = $alter . $t_field->fieldname . ' SET DEFAULT \'' . $t_field->default_value . '\'';
				}
			} else {
				$sql[] = $alter . $t_field->fieldname . ' DROP DEFAULT';
			}
		
			if( $t_field->notnull ) {
				$sql[] = $alter . $t_field->fieldname . ' SET NOT NULL';
			} else {
				$sql[] = $alter . $t_field->fieldname . ' DROP NOT NULL';			
			}
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
	 * {@inheritDoc}
	 * @param string $p_table_name Table Name
	 * @return array returns an array of sql strings.
	 */
	function DropTable($p_table_name) {
		$t_sql = array (sprintf($this->GetDropTableSQL(), $this->TableName($p_table_name)));
		
		$drop_seq = $this->_DropAutoIncrement($p_table_name); //TODO
		if ($drop_seq) {
			$t_sql[] = $drop_seq;
		}

		return $t_sql;
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_field Field Definition
	 * @return string SQL string beginning with a space 
	 */
	function _CreateSuffix(&$p_field)
	{
		$t_suffix = '';
		if ($p_field->unsigned){ // TODO
		}
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
		if ($p_field->autoincrement){
			$t_suffix .= '';
			$p_field->type = 'SERIAL';
		}
		return $t_suffix;
	}

	/**
	 * {@inheritDoc}
	 */
	function _IndexSQL($p_index_name, $p_table_name, $flds, $idxoptions) {
		$sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			$sql = $this->DropIndex( $p_index_name, $p_table_name );
			if ( isset($idxoptions['DROP']) )
				return $sql;
		}

		if ( empty ($flds) ) {
			return $sql;
		}

		$unique = isset($idxoptions['UNIQUE']) ? ' UNIQUE' : '';

		$s = 'CREATE' . $unique . ' INDEX ' . $p_index_name . ' ON ' . $p_table_name . ' ';

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s .= '(' . $flds . ')';
		$sql[] = $s;

		return $sql;
	}

	/**
	 * {@inheritDoc}
	 */
	/*// search for a sequece for the given table (asumes the seqence-name contains the table-name!)
	// if yes return sql to drop it
	// this is still necessary if postgres < 7.3 or the SERIAL was created on an earlier version!!!
	function _DropAutoIncrement($tabname)
	{
		$tabname = $this->connection->quote('%'.$tabname.'%');

		$seq = $this->connection->GetOne("SELECT relname FROM pg_class WHERE NOT relname ~ 'pg_.*' AND relname LIKE $tabname AND relkind='S'");

		// check if a tables depends on the sequenz and it therefor cant and dont need to be droped separatly
		if (!$seq || $this->connection->GetOne("SELECT relname FROM pg_class JOIN pg_depend ON pg_class.relfilenode=pg_depend.objid WHERE relname='$seq' AND relkind='S' AND deptype='i'")) {
			return False;
		}
		return "DROP SEQUENCE ".$seq;
	}*/

	/**
	 * {@inheritDoc}
	 */
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
		$s .= " WITHOUT OIDS";

		$sql[] = $s;

		return $sql;
	}
}