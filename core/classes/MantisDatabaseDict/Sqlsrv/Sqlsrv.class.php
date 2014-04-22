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

	/**
	 * {@inheritDoc}
	 */
	protected function GetRenameTableSQL() {
		return "EXEC sp_rename '%s','%s'";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetRenameColumnSQL() {
		return "EXEC sp_rename '%s.%s','%s'";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetDropTableSQL() {
		return 'DROP TABLE %s';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetDropIndexSQL() {
		return 'DROP INDEX %s ON %s';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetAddColumnSQL() {
		return ' ADD';
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
	 * @param string $p_database_name Database Name
	 * @param string $p_options
	 * @return array
	 */
	public function CreateDatabase($p_database_name,$p_options=false) {
		$t_sql = array();
		$t_sql[] = 'CREATE DATABASE ' . $this->GetIdentifierName($p_database_name);
		return $t_sql;
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_database_name Database Name
	 * @return array
	 */
	public function DropDatabase($p_database_name) {
		$sql = array();
		$sql[] = 'USE MASTER';
		$sql[] = 'ALTER DATABASE ' . $this->GetIdentifierName($p_database_name) . ' SET SINGLE_USER WITH ROLLBACK IMMEDIATE;';
		$sql[] = 'DROP DATABASE ' . $this->GetIdentifierName($p_database_name);
		return $sql;
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 */
	public function ActualType($p_type) {
		if( isset( $p_type[1] ) && $p_type[1] == '(' ) {
			switch( strtoupper( $p_type[0] ) ) {
				case 'C':
					$t_length = substr( $p_type, 2, -1 );
					return array( 'NVARCHAR', $t_length, null );	
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
				return 'BIT';
			case 'B':
				return 'VARBINARY(MAX)';
			case 'X':
				return 'NVARCHAR(4000)';
			default:
				echo $p_type;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	function GetDataDictType($p_type) {
		switch( strtoupper( $p_type ) ) {
			case 'NVARCHAR':
				return 'X';
			case 'VARBINARY':
				return 'B'; // may need to check if binary
			case 'BIT':
				return 'L';
			case 'DATETIME':
				return 'T';
			case 'SMALLINT':
				return 'I2';
			case 'INT':
				return 'I';
			case 'NVARCHAR':
				return 'XL';
			case 'VARCHAR':
				return 'C';
			case 'FLOAT':
				return 'F';
			case 'NUMERIC':
				return 'N';
			default:
				echo $p_type;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	function DropIndex ($idxname, $tabname = null) {
		return array( "IF EXISTS (SELECT * FROM sysobjects WHERE name = '" . $this->GetIdentifierName($idxname) ."')
					ALTER TABLE " . $this->TableName($tabname) . " DROP CONSTRAINT " . $this->GetIdentifierName($idxname) . "
				ELSE
					DROP INDEX " . $this->GetIdentifierName($idxname) . " ON " . $this->TableName($tabname));
	}

	/**
	 * {@inheritDoc}
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

			//if ($p_field->autoincrement) $t_suffix .= ' IDENTITY(1,1)';
						
			if( $t_field->has_default == true ) {
				$constraintname = false;
				$rs = MantisDatabase::GetInstance()->Execute( "select name from sys.default_constraints WHERE object_name(parent_object_id) = '" . $tabname . "' AND col_name(parent_object_id, parent_column_id) = '" . $t_field->fieldname . "'");
				if ( $rs ) {
					$t_row = $rs->fetch();
					if ( $t_row['name'] !== null ) {
						$constraintname = $t_row['name'];
					} else {
						$constraintname = $t_row[0];
					}
				}
				
				if ( $constraintname != false ) {
					$sql[] = 'ALTER TABLE ' . $tabname . ' DROP CONSTRAINT ' . $constraintname;
				}

				if( $t_field->default_value === false ) {
					$default = ' \'0\'';
				} elseif ( $t_field->default_value === true ) {
					$default = '\'1\'';
				} elseif ( $t_field->default_value === null ) {
					$default = 'NULL';
				} else {
					$default = '\'' . $t_field->default_value . '\'';
				}
				
				$sql[] = $alter . $t_field->fieldname . ' ' . $t_type ;
				if ( $constraintname != false ) {
					$sql[] = 'ALTER TABLE ' . $tabname . ' ADD CONSTRAINT ' . $constraintname . ' DEFAULT ' . $default . ' FOR ' . $t_field->fieldname;
				} else {
					$sql[] = 'ALTER TABLE ' . $tabname . ' ADD CONSTRAINT DF__' . $tabname . '__' . $t_field->fieldname . '__' . dechex(rand()) . ' DEFAULT ' . $default . ' FOR ' . $colname;
				}
				if ($t_field->notnull) {
					$sql[] = $alter . $t_field->fieldname . ' ' . $t_type  . ' NOT NULL';
				}
			} else {
				if ($t_field->notnull) {
					$sql[] = $alter . $t_field->fieldname . ' ' . $t_type . ' NOT NULL';
		         } else {
					$sql[] = $alter . $t_field->fieldname . ' ' . $t_type;
				}
			}
			
			//if( $t_field->notnull ) {
			//	$sql[] = $alter . $t_field->fieldname . ' NOT NULL';
			//}
		}
		if (is_array($t_def->indexes)) {
			foreach($t_def->indexes as $idx => $idxdef) {
				$sql_idxs = $this->CreateIndexSql($idx, $tabname, $idxdef['cols'], $idxdef['opts']);
				$sql = array_merge($sql, $sql_idxs);
			}
		}
		//var_dump($sql); die;
		return $sql;
	}

	/**
	 * {@inheritDoc}
	 */
	function DropColumn($tabname, $flds) {
		$tabname = $this->TableName ($tabname);
		if (!is_array($flds))
			$flds = explode(',',$flds);
		$f = array();
		$s = 'ALTER TABLE ' . $tabname;
		foreach($flds as $v) {
			$rs = MantisDatabase::GetInstance()->Execute( "select name from sys.default_constraints WHERE object_name(parent_object_id) = '" . $tabname ."' AND col_name(parent_object_id, parent_column_id) = '" . $v . "'");
			if ( $rs ) {
				$t_row = $rs->fetch();
				if( $t_row ) {
					$constraintname = $t_row['name'];
					$sql[] = 'ALTER TABLE '.$tabname.' DROP CONSTRAINT '. $constraintname;
				}
			}
			$f[] = "\n" . $this->GetDropColumnSQL() . ' ' . $this->GetIdentifierName($v);
		}
		$s .= implode(', ',$f);
		$sql[] = $s;
		return $sql;
	}

	/**
	 * {@inheritDoc}
	 */
	function DropTableSQL($tabname) {
		return array (sprintf($this->dropTable, $this->TableName($tabname)));
	}

	/**
	 * {@inheritDoc}
	 */
	function RenameTableSQL($tabname, $newname) {
		return array (sprintf($this->renameTable, $this->TableName($tabname), $this->TableName($newname)));
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 *
	 * @param string $p_field Field Definition
	 * @return string SQL string beginning with a space 
	 */
	function _CreateSuffix(&$p_field)
	{
		$t_suffix = '';
		//f ($p_field->unsigned) $t_suffix .= ' UNSIGNED';
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
		if ($p_field->autoincrement) $t_suffix .= ' IDENTITY(1,1)';
		return $t_suffix;
	}

	/**
	 * {@inheritDoc}
	 */
	function _IndexSQL($idxname, $tabname, $flds, $idxoptions) {
		$t_sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			$t_sql = $this->DropIndex( $idxname, $tabname );
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

	/**
	 * {@inheritDoc}
	 * @param string $p_table_name Table Name
	 * @param array $p_fields Fields
	 * @param array $p_primary_key Primary Key Fields
	 * @param array $p_table_options
	 */
	public function _TableSQL($p_table_name,$p_fields,$p_primary_key,$p_table_options) {
		$t_sql = array();

		if (isset($p_table_options['REPLACE']) || isset ($p_table_options['DROP'])) {
			$t_sql[] = sprintf($this->dropTable,$p_table_name);
			if ($this->autoIncrement) {
				$sInc = $this->_DropAutoIncrement($p_table_name);
				if ($sInc) $t_sql[] = $sInc;
			}
			if ( isset ($p_table_options['DROP']) ) {
				return $t_sql;
			}
		}
		$s = "CREATE TABLE $p_table_name (\n";
		$s .= implode(",\n", $p_fields);
		if (sizeof($p_primary_key)>0) {
			$s .= ",\n                 PRIMARY KEY (";
			$s .= implode(', ', $p_primary_key) . ')';
		}
		if (isset($p_table_options['CONSTRAINTS']))
			$s .= "\n" . $p_table_options['CONSTRAINTS'];

		$s .= "\n)";

		$t_sql[] = $s;

		return $t_sql;
	}
}