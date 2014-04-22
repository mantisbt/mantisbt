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
 * Oracle database dictionary class.
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class MantisDatabaseDict_Oci extends MantisDatabaseDict {
	var $dropTable = "DROP TABLE %s CASCADE CONSTRAINTS";
	var $trigPrefix = 'TRIG_';
	var $seqField = false;
	var $seqPrefix = 'SEQ_';
	var $dropIndex = 'DROP INDEX %s';
	var $alterCol = ' MODIFY '; // TODO may need brackets ()
	var $dropCol = ' DROP COLUMN';

	/**
	 * {@inheritDoc}
	 * @param string $p_database_name Database Name
	 * @param string $p_options
	 * @return array
	 */
	function CreateDatabase($p_database_name,$p_options=false)
	{
		/*
		$options = $this->_Options($options);
		$password = isset($options['PASSWORD']) ? $options['PASSWORD'] : 'tiger';
		$tablespace = isset($options["TABLESPACE"]) ? " DEFAULT TABLESPACE ".$options["TABLESPACE"] : '';
		$sql[] = "CREATE USER ".$dbname." IDENTIFIED BY ".$password.$tablespace;
		$sql[] = "GRANT CREATE SESSION, CREATE TABLE,UNLIMITED TABLESPACE,CREATE SEQUENCE TO $dbname";
		*/
		
		$t_sql = array();
		$t_sql[] = 'CREATE USER ' . $p_database_name . ' IDENTIFIED BY tiger';
		$t_sql[] = "GRANT CREATE SESSION, CREATE TABLE,UNLIMITED TABLESPACE,CREATE SEQUENCE TO $p_database_name";
		return $t_sql;
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_type portable type identifier
	 * @returns string SQL type
	 */
	function ActualType($p_type) {
		if( isset( $p_type[1] ) && $p_type[1] == '(' ) {
			switch( strtoupper( $p_type[0] ) ) {
				case 'C':
					$t_length = substr( $p_type, 2, -1 );
					return array( 'NVARCHAR2', $t_length, null );	
				case 'N':
					$t_length = substr( $p_type, 2, -1 );
					$t_parts = explode( $t_length, ',' );
					return array( 'NUMBER', $t_parts[0], $t_parts[1] );	
				case 'F':
					$t_length = substr( $p_type, 2, -1 );
					$t_parts = explode( $t_length, ',' );
					return array( 'NUMBER', $t_parts[0], $t_parts[1] );
			}
		}

		switch(strtoupper($p_type)) {
			case 'XL':
				return 'CLOB';
			case 'I':
				return 'NUMBER(10)';
			case 'I2':
				return 'NUMBER(5)';
			case 'T':
				return 'DATE';
			case 'L':
				return 'NUMBER(1)';
			case 'B':
				return 'BLOB';
			case 'X':
				return 'NVARCHAR2(4000)';
			case 'I8':
				return 'NUMBER(20)';
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
			case 'BLOB':
				return 'B'; // may need to check if binary
			case 'BIT':
				return 'L';
			case 'DATETIME':
				return 'T';
			case 'SMALLINT':
				return 'N';
			case 'INT':
				return 'I';
			case 'CLOB':
				return 'XL';
			case 'NVARCHAR':
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
	function TableName($name) {
		if ( $this->schema ) {
			return schema .'.'. $name;
		}
		return $name;
	}

	/**
	 * {@inheritDoc}
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

	/**
	 * {@inheritDoc}
	 */
	function DropIndexSQL ($idxname, $tabname = NULL)
	{
		return array(sprintf($this->dropIndex, $idxname, $this->TableName($tabname)));
	}

	/**
	 * {@inheritDoc}
	 */
	function AddColumnSQL($tabname, $flds)
	{
		$tabname = $this->TableName ($tabname);
		$sql = array();

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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	function DropColumn($tabname, $flds, $tableflds='',$tableoptions='')
	{
		$tabname = $this->TableName ($tabname);
		if (!is_array($flds))
			$flds = explode(',',$flds);
		$f = array();
		$s = "ALTER TABLE $tabname DROP(";
		$s .= implode(', ',$flds).') CASCADE CONSTRAINTS';
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
	function RenameTableSQL($tabname,$newname) {
		return array (sprintf($this->renameTable, $this->TableName($tabname),$this->TableName($newname)));
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
	 * {@inheritDoc}
	 */
	function _CreateSuffix($fname,&$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned) {
		$suffix = '';
		if ($fautoinc) {
			$ftype = 'SERIAL'; // TODO ??
			return '';
		}
		
		if ($fdefault == "''" && $fnotnull) {// this is null in oracle
			$fnotnull = false;
			//if ($this->debug) ADOConnection::outp("NOT NULL and DEFAULT='' illegal in Oracle");
		}

		//if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		// if ($fautoinc) $suffix .= ' IDENTITY(1,1)'; // TODO $this->seqField = $fname;
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	/**
	 * {@inheritDoc}
	 */
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

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s = 'CREATE' . $unique . ' INDEX ' . $idxname . ' ON ' . $tabname . ' (' . $flds . ')';

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s .= '(' . $flds . ')';
		$sql[] = $s;

		return $sql;
	}

	/**
	 * {@inheritDoc}
	 */
	function _DropAutoIncrement($tabname) {
		if (strpos($tabname,'.') !== false) {
			$tarr = explode('.',$tabname);
			return "drop sequence ".$tarr[0].".seq_".$tarr[1];
		}
		return "drop sequence seq_".$tabname;
	}

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


		//if (isset($tableoptions[$this->upperName.'_CONSTRAINTS']))
		//	$s .= "\n".$tableoptions[$this->upperName.'_CONSTRAINTS'];

		$s .= "\n)";

		$sql[] = $s;

		return $sql;
	}


/*
CREATE or replace TRIGGER jaddress_insert
before insert on jaddress
for each row
begin
select seqaddress.nextval into :new.A_ID from dual;
end;
*/
	/**
	 * {@inheritDoc}
	 */
	function _Triggers($tabname,$tableoptions) {
		if (!$this->seqField) return array();

		if ($this->schema) {
			$t = strpos($tabname,'.');
			if ($t !== false) $tab = substr($tabname,$t+1);
			else $tab = $tabname;
			$seqname = $this->schema.'.'.$this->seqPrefix.$tab;
			$trigname = $this->schema.'.'.$this->trigPrefix.$this->seqPrefix.$tab;
		} else {
			$seqname = $this->seqPrefix.$tabname;
			$trigname = $this->trigPrefix.$seqname;
		}

		if (strlen($seqname) > 30) {
			$seqname = $this->seqPrefix.uniqid('');
		} // end if
		if (strlen($trigname) > 30) {
			$trigname = $this->trigPrefix.uniqid('');
		} // end if

		if (isset($tableoptions['REPLACE'])) $sql[] = "DROP SEQUENCE $seqname";
		$seqCache = '';
		if (isset($tableoptions['SEQUENCE_CACHE'])){$seqCache = $tableoptions['SEQUENCE_CACHE'];}
		$seqIncr = '';
		if (isset($tableoptions['SEQUENCE_INCREMENT'])){$seqIncr = ' INCREMENT BY '.$tableoptions['SEQUENCE_INCREMENT'];}
		$seqStart = '';
		if (isset($tableoptions['SEQUENCE_START'])){$seqIncr = ' START WITH '.$tableoptions['SEQUENCE_START'];}
		$sql[] = "CREATE SEQUENCE $seqname $seqStart $seqIncr $seqCache";
		$sql[] = "CREATE OR REPLACE TRIGGER $trigname BEFORE insert ON $tabname FOR EACH ROW WHEN (NEW.$this->seqField IS NULL OR NEW.$this->seqField = 0) BEGIN select $seqname.nextval into :new.$this->seqField from dual; END";

		$this->seqField = false;
		return $sql;
	}
}