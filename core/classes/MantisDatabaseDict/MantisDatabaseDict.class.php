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
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
 
class MantisDatabaseDict {
	var $dropTable = 'DROP TABLE %s';
	var $renameTable = 'RENAME TABLE %s TO %s'; 
	var $dropIndex = 'DROP INDEX %s ON %s';
	var $addCol = ' ADD';
	var $alterCol = ' MODIFY';
	var $dropCol = ' DROP COLUMN';
	var $renameColumn = 'ALTER TABLE %s CHANGE COLUMN %s %s %s';	// table, old-column, new-column, column-definitions (not used by default)
	var $nameRegex = '\w';
	var $nameRegexBrackets = 'a-zA-Z0-9_\(\)';
	var $schema = false;
	var $serverInfo = array();
	var $autoIncrement = false;
	var $dataProvider;
	var $invalidResizeTypes4 = array('CLOB','BLOB','TEXT','DATE','TIME'); // for changetablesql
	var $blobSize = 100; 	/// any varchar/char field this size or greater is treated as a blob
							/// in other words, we use a text area for editting.
	

    /**
     * Loads and returns a database instance with the specified type and library.
     * @param string $type database type of the driver (e.g. pdo_pgsql)
     * @return MantisDatabase driver object or null if error
     */
    public static function get_driver_instance($type) {
		$t_type = explode( '_', $type );
		switch( strtolower( $t_type[0] ) ) {
			case 'pdo':
				$t_driver_type = 'PDO';
		}
        $classname = 'MantisDatabaseDict_' . ucfirst($t_type[1]);
        return new $classname();
    }

	function quotesplit( $s, $splitter=',', $restore_quotes=0 ) { 
		// hack because i'm a bad programmer - replace doubled "s with a ' 
		//$s = str_replace('""', "'", $s); 
    
		//First step is to split it up into the bits that are surrounded by quotes 
		//and the bits that aren't. Adding the delimiter to the ends simplifies 
		//the logic further down 
		$getstrings = explode('"', $splitter.$s.$splitter); 

		//$instring toggles so we know if we are in a quoted string or not 
		$delimlen = strlen($splitter); 
		$instring = 0; 

		while (list($arg, $val) = each($getstrings)) { 
			if ($instring==1) { 
				if( $restore_quotes ) { 
					//Add the whole string, untouched to the previous value in the array 
					$result[count($result)-1] = $result[count($result)-1].'"'.$val.'"'; 
				} else { 
					//Add the whole string, untouched to the array 
					$result[] = $val; 
				} 
				$instring = 0; 
			} else { 
				// check that we have data between multiple $splitter delimiters 
				if ((strlen($val)-$delimlen) >= 1) { 
					//Break up the string according to the delimiter character 
					//Each string has extraneous delimiters around it (inc the ones we added 
					//above), so they need to be stripped off 
					$temparray = explode($splitter, substr($val, $delimlen, strlen($val)-$delimlen-$delimlen ) ); 

					while(list($iarg, $ival) = each($temparray)) { 
						$result[] = trim($ival); 
					} 
				} 
				// else, the next element needing parsing is a quoted string and the comma 
				// here is just a single separator and contains no data, so skip it 
				$instring = 1; 
			} 
		} 
		return $result; 
	} 
	
	function parsesql($str) {
		$str = str_replace('" \'\' "', '\'\'', $str);

		$a= $this->quotesplit($str);
		$i = 0;
		$output = array();
		foreach ($a as $line) {
			$output[$i] = array();
			// trim whitespace
			$line = trim($line);

			$word = preg_split("/[\s,]*('[^']+')[\s,]*|[\s,]+/", $line, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			$word = preg_replace("/'(\d+)'/", '$1', $word);
 
			$output[$i] = $word;
 
			$i++;
		}
  
		//if( sizeof($output) == 1 ) {
		//	return $output[0];
		//}
		return $output;
	}
	
	function ActualType($meta)
{
	if( $meta[0] == 'C' && $meta[1] == '(' ) {
		$length = substr($meta,2, -1); 
		return 'VARCHAR(' . $length . ')';
	}
	switch(strtoupper($meta)) {
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
			echo $meta;
		
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
	
	function TableName($name)
	{
		if ( $this->schema ) {
			return $this->NameQuote($this->schema) .'.'. $this->NameQuote($name);
		}
		return $this->NameQuote($name);
	}
	
	// Executes the sql array returned by GetTableSQL and GetIndexSQL
	function ExecuteSQLarray($sql)
	{
		global $g_db;
		foreach($sql as $line) {
			$t_ret = $g_db->execute($line);
			if( $t_ret === false ) {
				return 1;
			}
		}
		return 2;
	}
		
	function CreateDatabase($dbname,$options=false)
	{
		$sql = array();		
		$sql[] = 'CREATE DATABASE ' . $this->NameQuote($dbname) . ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci';
		return $sql;
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
			$flds[$key] = $this->NameQuote($fld,$allowBrackets=true);
		}
		
		return $this->_IndexSQL($this->NameQuote($idxname), $this->TableName($tabname), $flds, $this->_Options($idxoptions));
	}
	
	function DropIndexSQL ($idxname, $tabname = NULL)
	{
		return array(sprintf($this->dropIndex, $this->NameQuote($idxname), $this->TableName($tabname)));
	}
		
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
	function AlterColumnSQL($tabname, $flds, $tableflds='',$tableoptions='')
	{
		$tabname = $this->TableName ($tabname);
		$sql = array();

		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		
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
	 * @param string $tabname table-name
	 * @param string $oldcolumn column-name to be renamed
	 * @param string $newcolumn new column-name
	 * @param string $flds='' complete column-defintion-string like for AddColumnSQL, only used by mysql atm., default=''
	 * @return array with SQL strings
	 */
	function RenameColumnSQL($tabname,$oldcolumn,$newcolumn,$flds='')
	{
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
	function DropColumnSQL($tabname, $flds, $tableflds='',$tableoptions='')
	{
		$tabname = $this->TableName ($tabname);
		if (!is_array($flds)) $flds = explode(',',$flds);
		$sql = array();
		$alter = 'ALTER TABLE ' . $tabname . $this->dropCol . ' ';
		foreach($flds as $v) {
			$sql[] = $alter . $this->NameQuote($v);
		}
		return $sql;
	}
	
	function DropTableSQL($tabname)
	{
		return array (sprintf($this->dropTable, $this->TableName($tabname)));
	}
	
	function RenameTableSQL($tabname,$newname)
	{
		return array (sprintf($this->renameTable, $this->TableName($tabname),$this->TableName($newname)));
	}	
	
	/**
	 Generate the SQL to create table. Returns an array of sql strings.
	*/
	function CreateTableSQL($tabname, $flds, $tableoptions=array())
	{
		list($lines,$pkey,$idxs) = $this->_GenFields($flds, true);
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
	
	function _GenFields($data)
	{
		$t_fields = $this->parsesql($data);
		$id=0;
		$primary_key = array();

		foreach( $t_fields as $t_line ) {
			$t_count = sizeof($t_line);
	
			$sql_default = null;
			$sql_notnull = null;
			$sql_fieldname = null;
			$sql_type = null;
			$sql_primary = null;
			$sql_unsigned = null;
			$sql_autoincrement = null;
	
			for( $i=0; $i < $t_count; $i++) {
				switch( $i ) {
					case 0:
						$sql_fieldname = $t_line[$i];
						continue 2;
					case 1:
						$sql_type = $this->ActualType($t_line[$i]);
						continue 2;
				}

				switch( $t_line[$i] ) {
					case 'NOTNULL':
						$sql_notnull = true;
						break;
					case 'DEFAULT':
						$sql_default = $t_line[++$i];
						break;
					case 'PRIMARY':
						$sql_primary = true;
						$sql_notnull = true;
						$primary_key[] = $sql_fieldname;
						break;
					case 'UNSIGNED':
						$sql_unsigned = true;
						break;
					case 'AUTOINCREMENT':
						$sql_autoincrement = true;
						break;
					default:
						echo $t_line[$i];
						break;
				}
			}
			
			$suffix = $this->_CreateSuffix($sql_fieldname,$sql_type,$sql_notnull,$sql_default,$sql_autoincrement,/*TODO@@@$fconstraint*/ null,$sql_unsigned);
			
			$lines[$id++] = $sql_fieldname.' '.$sql_type . $suffix;
		}
			
			
		
		return array($lines,$primary_key,null);
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
	function _CreateSuffix($fname,$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{	
		$suffix = '';
		if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fautoinc) $suffix .= ' AUTO_INCREMENT';
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
		
		$unique = isset($idxoptions['UNIQUE']) ? ' UNIQUE ' : '';
		
		$s = 'ALTER TABLE ' . $tabname . ' ADD ' . $unique . ' INDEX ' . $idxname;
		
//		if ( isset($idxoptions[$this->upperName]) )
	//		$s .= $idxoptions[$this->upperName];
		
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
		
		
		//if (isset($tableoptions[$this->upperName.'_CONSTRAINTS'])) 
		//	$s .= "\n".$tableoptions[$this->upperName.'_CONSTRAINTS'];
		
		$s .= "\n)";
		
		$s .= "ENGINE=MyISAM DEFAULT CHARSET=utf8";
		//if (isset($tableoptions[$this->upperName])) $s .= $tableoptions[$this->upperName];
		$sql[] = $s;
		
		return $sql;
	}
	

	
	/**
		Sanitize options, so that array elements with no keys are promoted to keys
	*/
	function _Options($opts)
	{
		if (!is_array($opts)) return array();
		$newopts = array();
		foreach($opts as $k => $v) {
			if (is_numeric($k)) $newopts[strtoupper($v)] = $v;
			else $newopts[strtoupper($k)] = $v;
		}
		return $newopts;
	}
	
	/**
	"Florian Buzin [ easywe ]" <florian.buzin#easywe.de>
	
	This function changes/adds new fields to your table. You don't
	have to know if the col is new or not. It will check on its own.
	*/
	function ChangeTableSQL($tablename, $flds, $tableoptions = false, $dropOldFlds=false)
	{
	global $ADODB_FETCH_MODE;
	
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		if ($this->connection->fetchMode !== false) $savem = $this->connection->SetFetchMode(false);
		
		// check table exists
		$save_handler = $this->connection->raiseErrorFn;
		$this->connection->raiseErrorFn = '';
		$cols = $this->MetaColumns($tablename);
		$this->connection->raiseErrorFn = $save_handler;
		
		if (isset($savem)) $this->connection->SetFetchMode($savem);
		$ADODB_FETCH_MODE = $save;
		
		if ( empty($cols)) { 
			return $this->CreateTableSQL($tablename, $flds, $tableoptions);
		}
		
		if (is_array($flds)) {
		// Cycle through the update fields, comparing
		// existing fields to fields to update.
		// if the Metatype and size is exactly the
		// same, ignore - by Mark Newham
			$holdflds = array();
			foreach($flds as $k=>$v) {
				if ( isset($cols[$k]) && is_object($cols[$k]) ) {
					// If already not allowing nulls, then don't change
					$obj = $cols[$k];
					if (isset($obj->not_null) && $obj->not_null)
						$v = str_replace('NOT NULL','',$v);
					if (isset($obj->auto_increment) && $obj->auto_increment && empty($v['AUTOINCREMENT'])) 
					    $v = str_replace('AUTOINCREMENT','',$v);
					
					$c = $cols[$k];
					$ml = $c->max_length;
					$mt = $this->MetaType($c->type,$ml);
					if ($ml == -1) $ml = '';
					if ($mt == 'X') $ml = $v['SIZE'];
					if (($mt != $v['TYPE']) ||  $ml != $v['SIZE'] || (isset($v['AUTOINCREMENT']) && $v['AUTOINCREMENT'] != $obj->auto_increment)) {
						$holdflds[$k] = $v;
					}
				} else {
					$holdflds[$k] = $v;
				}		
			}
			$flds = $holdflds;
		}
	

		// already exists, alter table instead
		list($lines,$pkey,$idxs) = $this->_GenFields($flds);
		// genfields can return FALSE at times
		if ($lines == null) $lines = array();
		$alter = 'ALTER TABLE ' . $this->TableName($tablename);
		$sql = array();

		foreach ( $lines as $id => $v ) {
			if ( isset($cols[$id]) && is_object($cols[$id]) ) {
			
				$flds = Lens_ParseArgs($v,',');
				
				//  We are trying to change the size of the field, if not allowed, simply ignore the request.
				// $flds[1] holds the type, $flds[2] holds the size -postnuke addition
				if ($flds && in_array(strtoupper(substr($flds[0][1],0,4)),$this->invalidResizeTypes4)
				 && (isset($flds[0][2]) && is_numeric($flds[0][2]))) {
					if ($this->debug) ADOConnection::outp(sprintf("<h3>%s cannot be changed to %s currently</h3>", $flds[0][0], $flds[0][1]));
					#echo "<h3>$this->alterCol cannot be changed to $flds currently</h3>";
					continue;	 
	 			}
				$sql[] = $alter . $this->alterCol . ' ' . $v;
			} else {
				$sql[] = $alter . $this->addCol . ' ' . $v;
			}
		}
		
		if ($dropOldFlds) {
			foreach ( $cols as $id => $v )
			    if ( !isset($lines[$id]) ) 
					$sql[] = $alter . $this->dropCol . ' ' . $v->name;
		}
		return $sql;
	}
}