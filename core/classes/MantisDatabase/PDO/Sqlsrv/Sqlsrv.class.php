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
 * SQLSRV PDO driver class.
 * @package MantisBT
 * @subpackage classes
 */
class MantisDatabase_PDO_Sqlsrv extends MantisDatabase_PDO {
    /**
     * Returns the driver-dependent DSN for PDO based on members stored by connect.
     * Must be called after connect (or after $dbname, $dbhost, etc. members have been set).
     * @return string driver-dependent DSN
     */
    protected function get_dsn() {
		return  'sqlsrv:Server=' . $this->dbhost . ';Database=' . $this->dbname;
	}
	
	/**
	 * Returns whether driver is installed
	 * @return bool
	 */
    public function driver_installed() {
		return extension_loaded( 'pdo_sqlsrv' );
	}	

	/**
	 * Returns db type string
	 * @return string
	 */
	public function get_dbtype() {
		return 'sqlsrv';
	}
	
	/**
	 * Returns PDO options
	 * @return array
	 */
    protected function get_pdooptions() {
		$t_options = parent::get_pdooptions();
        return $t_options;
    }
	
	/**
	 * Execute query with a limit
	 * @param string $sql
	 * @param int $p_limit
	 * @param int $p_offset
	 * @param array $arr_parms
	 * @return object
	 */
	public function SelectLimit( $sql, $p_limit = -1, $p_offset = -1, array $arr_parms = null) {
		//if ( $p_offset != -1 ) {
		//	echo 'offset not supported';
		//	die;
		//}
		//$t_stroffset = ($p_offset>=0) ? " OFFSET $p_offset" : '';

		if ($p_limit > 0 && $p_offset <= 0) {
			$sql = preg_replace( '/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 top'." $p_limit ",$sql);
		}
		//if ($p_limit < 0) $p_limit = '18446744073709551615'; 
		if( $p_offset > 0 ) {
			$ret = $this->execute($sql, $arr_parms);
			for( $i = 0; $i < $p_offset; $i++ ) {
				$ret->fetch();
			}
			return $ret;
		}
		return $this->execute($sql, $arr_parms);
	}

	/**
	 * Return whether database exists
	 * @param string $p_name
	 * @return bool
     * @throws MantisBT\Exception\Database\QueryFailed
	 */	
	public function database_exists( $p_name ) {
		$sql = "select name from sys.databases where name = ?";
		try {
			$t_result = $this->execute( $sql, array( $p_name ) );
		} catch (PDOException $ex) {
			throw new MantisBT\Exception\Database\QueryFailed($ex->getMessage());
		}
		if ($t_result) {
			$t_value = $t_result->fetch();
			if( $t_value !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get list of tables in database
	 * @param bool $usecache
	 * @return array
	 */	
	public function get_tables($usecache=true) {
        if ($usecache and $this->tables !== null) {
            return $this->tables;
        }
        $this->tables = array();
        $sql = "select name from sysobjects where (type='U' or type='V')";
		
		$t_result = $this->execute( $sql );
        if ($t_result) {
            while ($arr = $t_result->fetch()) {
                $this->tables[] = $arr[0];
            }
        }
        return $this->tables;	
	}

	/**
	 * Get indexes on table
	 * @param string $table
	 * @return array
	 */
    public function get_indexes($table) {
        $t_indexes = array();
		$sql = "SELECT i.name AS ind_name, C.name AS col_name, USER_NAME(O.uid) AS Owner, c.colid, k.Keyno, 
			CASE WHEN I.indid BETWEEN 1 AND 254 AND (I.status & 2048 = 2048 OR I.Status = 16402 AND O.XType = 'V') THEN 1 ELSE 0 END AS IsPK,
			CASE WHEN I.status & 2 = 2 THEN 1 ELSE 0 END AS IsUnique
			FROM dbo.sysobjects o INNER JOIN dbo.sysindexes I ON o.id = i.id 
			INNER JOIN dbo.sysindexkeys K ON I.id = K.id AND I.Indid = K.Indid 
			INNER JOIN dbo.syscolumns c ON K.id = C.id AND K.colid = C.Colid
			WHERE LEFT(i.name, 8) <> '_WA_Sys_' AND o.status >= 0 AND O.Name LIKE %s
			ORDER BY O.name, I.Name, K.keyno";
		$t_result = $this->execute( $sql, array($table) );
		
        if ($t_result) {
            while ($arr = $t_result->fetch()) {
                $t_indexes[strtolower( $arr['ind_name'] )] = array( strtolower( $arr['col_name'] ), $arr['IsUnique'] );
            }
        }
		return $t_indexes;	
	}

	/**
	 * Get List of database columns for given table
	 * @param string $table
	 * @param bool $usecache
	 * @return array
	 */	
	public function get_columns($table, $usecache=true) {
		if ($usecache and isset($this->columns[$table])) {
            return $this->columns[$table];
        }

        $this->columns[$table] = array();

        $sql = "select c.name,t.name,c.length from syscolumns c join systypes t on t.xusertype=c.xusertype join sysobjects o on o.id=c.id where o.name=%s";
		$t_result = $this->execute( $sql, array( $table ) );
        if ($t_result) {
            while ($arr = $t_result->fetch()) {
                $this->columns[$table][] = strtolower( $arr[0] );
            }
        }
		return $this->columns[$table];
	}
}

