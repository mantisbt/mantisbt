<?php

/* 
 V5.06 16 Oct 2008   (c) 2000-2008 John Lim (jlim#natsoft.com). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence. See License.txt. 
  Set tabs to 4 for best viewing.
  
  Latest version is available at http://adodb.sourceforge.net
  
  Thanks Diogo Toscano (diogo#scriptcase.net) for the code.
*/

class ADODB_pdo_sqlite extends ADODB_pdo {
	var $metaTablesSQL = "SELECT name FROM sqlite_master WHERE type='table'";
	var $sysDate = 'current_date';
	var $sysTimeStamp = 'current_timestamp';
	var $nameQuote = '`';
    var $replaceQuote = "''";
    var $hasTransactions = true;
    var $_bndInputArray = false;
    var $hasInsertID = true;
    var $hasGenID = true;
    var $_genSeqSQL = "create table %s (id integer)";
    var $_genSeqCountSQL = 'select count(*) from %s';
    var $_genSeq2SQL = 'insert into %s values(%s)';
    var $_dropSeqSQL = 'drop table %s';
    var $_stmt = false;
    
    function _init($parentDriver)
	{
		$parentDriver->_bindInputArray = true;
	}

    function _query($sql,$inputarr=false) {
            if(strtolower(substr($sql,0,5)) != 'alter') {
                    return parent::_query($sql,$inputarr);
            } else {
                    if(!$this->sqliteDropColumn($sql))
                            return parent::_query($sql,$inputarr);
                    else
                            return true;
            }
    }

	function _connect($argDSN, $argUsername, $argPassword, $argDatabasename, $persist=false) {
	       return parent::_connect($argDatabasename,'','','',$persist);
	}

        function sqliteDropColumn($sql) {
                $queryparts = preg_split("/[\s]+/",$sql,10,PREG_SPLIT_NO_EMPTY);
                if(count($queryparts) == 6) {
                        $table = $queryparts[2];
                        $temp_table = $table.'_Temp';
                        $removeColumn = $queryparts[5];
                        $removeColumn = str_replace(';','',$removeColumn);
                        if(strtolower($queryparts[1]) != 'table' || 
                                strtolower($queryparts[3]) != 'drop' || 
                                strtolower($queryparts[4]) != 'column') {
                                return false;
                        } else {
                           // RENAME TABLE to TABLE_Temp
                           // CREATE new table without dropped column
                           // INSERT RECORDS from old table to new
                           // DROP Temp table
                           $meta = $this->MetaColumns($table);
                           $fields = Array();
			   $fieldNames = Array();
                           foreach($meta as $col) {
                                if($col->name != $removeColumn) {
                                       $colText = $col->name;
				       if(!empty($col->type))
				       		$colText .= ' '.$col->type;  
				       if($col->not_null)
						$colText .= ' NOT NULL';
				       if(!empty($col->default_value))
				       		$colText .= ' DEFAULT '.$col->default_value.'';
				       
				        $fieldNames[] = $col->name;
				        $fields[] = $colText;
				}           
                           }
                           $fieldList = implode(',',$fields);
			   $fieldNameList = implode(',',$fieldNames);
                           
                           $this->BeginTrans();
                           $sql = "SELECT sql FROM sqlite_master WHERE type = 'index' AND tbl_name = '$table'";
                           $result = $this->Execute($sql);
                           while(list($index) = $result->FetchRow()) {
                                  $indexes[] = $index;
                           }

                           $renameTempSql = 'ALTER TABLE '.$table.' RENAME TO '.$temp_table.';'; 
                           $createTableSql = 'CREATE TABLE '.$table.'('.$fieldList.');'; 
                           $copyTableContentsSql = 'INSERT INTO '.$table.' SELECT '.$fieldNameList.' FROM '.$temp_table.';';  
                           $removeTableSql = 'DROP TABLE '.$temp_table.';';
                           $vacuumSql = 'VACUUM '.$table.';'; 

                           // Do the steps to drop the column by renaming and creating new table
                           $ok = $this->Execute($renameTempSql); 
                           if($ok) $ok = $this->Execute($createTableSql);  
                           if($ok) $ok = $this->Execute($copyTableContentsSql);             
                           
                           $this->CommitTrans($ok);
                                         
                           if($ok) $ok = $this->Execute($removeTableSql);                                                       
                           if($ok) $ok = $this->Execute($vacuumSql);   
                                                      
                           // Recreate the indexes on the new table from the old
                           foreach($indexes as $indexSQL) {
			       $listStart = strpos($indexSQL,'(')+1;
			       $listEnd = strpos($indexSQL,')');
			       $indexList = substr($indexSQL,$listStart,$listEnd-$listStart);
			       $indexList = explode(',',$indexList);
			       $newIndexList = Array();
			       foreach($indexList as $listItem) {
			           $listItem = trim($listItem);
				   if($listItem != $removeColumn) {
			       		$newIndexList[] = $listItem;    
				   }
			       }
			       $indexSQL = substr($indexSQL,0,$listStart-1);
			       $indexSQL .= '('.implode(',',$newIndexList).');'; 
                               $this->Execute($indexSQL);   
                           }
                           return $ok; 
                        }
                }
                return false;    
        }

	function &SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs=0) {
		$offsetStr =($offset>=0) ? "$offset," : '';
		if ($secs)
			$rs =& $this->CacheExecute($secs,$sql." LIMIT $offsetStr$nrows",$inputarr);
		else
			$rs =& $this->Execute($sql." LIMIT $offsetStr$nrows",$inputarr);
		return $rs;
	}

	function GenID($seq='adodbseq',$start=1) {
		// if you have to modify the parameter below, your database is overloaded,
		// or you need to implement generation of id's yourself!
		$MAXLOOPS = 100;
		//$this->debug=1;
		while (--$MAXLOOPS>=0) {
			@($num = array_pop($this->GetCol("select id from $seq")));
			if ($num === false || !is_numeric($num)) {
				@$this->Execute(sprintf($this->_genSeqSQL ,$seq));
				$start -= 1;
				$num = '0';
				$cnt = $this->GetOne(sprintf($this->_genSeqCountSQL,$seq));
				if (!$cnt) {
					$ok = $this->Execute(sprintf($this->_genSeq2SQL,$seq,$start));
				}
				if (!$ok) return false;
			}
			$this->Execute("update $seq set id=id+1");

			if ($this->affected_rows() > 0) {
                	        $num += 1;
                		$this->genID = intval($num);
                		return intval($num);
			}
		}
		if ($fn = $this->raiseErrorFn) {
			$fn($this->databaseType,'GENID',-32000,"Unable to generate unique id after $MAXLOOPS attempts",$seq,$num);
		}
		return false;
	}

	function BeginTrans()
	{
		if (!$this->hasTransactions) return false;
		if ($this->transOff) return true;
                if($this->transCnt < 1) {
        		$this->transCnt += 1;
        		$this->_autocommit = false;
        		return $this->_connectionID->beginTransaction();
                }
	}
	
	function CommitTrans($ok=true)
	{
		if (!$this->hasTransactions) return false;
		if ($this->transOff) return true; 
		if (!$ok) return $this->RollbackTrans();
		if ($this->transCnt) $this->transCnt -= 1;
		$this->_autocommit = true;

                if($this->transCnt == 0)
		        $ret = $this->_connectionID->commit();
		return $ret;
	}
	
	function RollbackTrans()
	{
		if (!$this->hasTransactions) return false;
		if ($this->transOff) return true; 
		if ($this->transCnt) $this->transCnt -= 1;
		$this->_autocommit = true;
		
		$ret = $this->_connectionID->rollback();
		return $ret;
	}

	function CreateSequence($seqname='adodbseq',$start=1) {
		if (empty($this->_genSeqSQL)) return false;
		$ok = $this->Execute(sprintf($this->_genSeqSQL,$seqname));
		if (!$ok) return false;
		$start -= 1;
		return $this->Execute("insert into $seqname values($start)");
	}

	function DropSequence($seqname) {
		if (empty($this->_dropSeqSQL)) return false;
		return $this->Execute(sprintf($this->_dropSeqSQL,$seqname));
	}

    // mark newnham
	function &MetaColumns($tab)
	{
	  global $ADODB_FETCH_MODE;
	  $false = false;
	  $save = $ADODB_FETCH_MODE;
	  $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	  if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);
	  $rs = $this->Execute("PRAGMA table_info('$tab')");
	  if (isset($savem)) $this->SetFetchMode($savem);
	  if (!$rs) {
	    $ADODB_FETCH_MODE = $save; 
	    return $false;
	  }
	  $arr = array();
	  while ($r = $rs->FetchRow()) {
	  	$type = explode('(',$r['type']);
	    $size = '';
	    if (sizeof($type)==2)
	    $size = trim($type[1],')');
	    $fn = strtoupper($r['name']);
	    $fld = new ADOFieldObject;
	    $fld->name = $r['name'];
	    $fld->type = $type[0];
	    $fld->max_length = $size;
	    $fld->not_null = $r['notnull'];
	    $fld->primary_key = $r['pk'];
	    $fld->default_value = $r['dflt_value'];
	    $fld->scale = 0;
	    if ($save == ADODB_FETCH_NUM) $arr[] = $fld;	
	    else $arr[strtoupper($fld->name)] = $fld;
	  }
	  $rs->Close();
	  $ADODB_FETCH_MODE = $save;
	  return $arr;
	}

    function MetaTables($ttype=false,$showSchema=false,$mask=false) {
        return $this->GetCol($this->metaTablesSQL);
    }

    function __sleep() {
        unset($this->_connectionID);
        return( array_keys( get_object_vars( $this ) ) );
    }
}

?>