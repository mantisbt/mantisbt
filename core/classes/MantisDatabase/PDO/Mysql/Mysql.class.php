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
 * MYSQL PDO driver class.
 * @package MantisBT
 * @subpackage classes
 */
class MantisDatabase_PDO_Mysql extends MantisDatabase_PDO {
    /**
     * Returns the driver-dependent DSN for PDO based on members stored by connect.
     * Must be called after connect (or after $dbname, $dbhost, etc. members have been set).
     * @return string driver-dependent DSN
     */
    protected function get_dsn() {
		return  'mysql:host=' . $this->dbhost . ';dbname=' . $this->dbname;
	}
	
	/**
	 * Returns whether driver is installed
	 * @return bool
	 */
    public function driver_installed() {
		return extension_loaded( 'pdo_mysql' );
	}	

	/**
	 * Returns db type string
	 * @return string
	 */
	public function get_dbtype() {
		return 'mysql';
	}
	
	/**
	 * Returns PDO options
	 * @return array
	 */
    protected function get_pdooptions() {
		$t_options = parent::get_pdooptions();
		$t_options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
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
	public function SelectLimit( $sql, $p_limit, $p_offset, array $arr_parms = null) {
		$t_stroffset = ($p_offset>=0) ? " OFFSET $p_offset" : '';

		if ($p_limit < 0) $p_limit = '18446744073709551615'; 

		return $this->execute($sql . ' LIMIT ' . (int)$p_limit . $t_stroffset , $arr_parms);
	}

	/**
	 * Return whether database exists
	 * @param string $p_name
	 * @return bool
     * @throws MantisBT\Exception\Database\QueryFailed
	 */	
	public function database_exists( $p_name ) {
		$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
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
	 * @param bool $p_use_cache
	 * @return array
	 */	
	public function get_tables($p_use_cache=true) {
        if ($p_use_cache and $this->tables !== null) {
            return $this->tables;
        }
        $this->tables = array();
        $sql = 'SHOW TABLES';
		
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
	 * @param string $p_table
	 * @return array
	 */
    public function get_indexes($p_table) {
        $t_indexes = array();
		$t_sql = "SHOW INDEXES FROM $p_table";
		$t_result = $this->execute( $t_sql );
		
        if ($t_result) {
            while ($arr = $t_result->fetch()) {
                $t_indexes[strtolower( $arr['key_name'] )] = array( strtolower( $arr['column_name'] ), $arr['non_unique'] );
            }
        }
		return $t_indexes;	
	}

	/**
	 * Get List of database columns for given table
	 * @param string $p_table
	 * @param bool $p_use_cache
	 * @return array
	 */	
	public function get_columns($p_table, $p_use_cache=true) {
		if ($p_use_cache and isset($this->columns[$p_table])) {
            return $this->columns[$p_table];
        }

        $this->columns[$p_table] = array();

        $sql = "SHOW COLUMNS FROM $p_table";
		$t_result = $this->execute( $sql );
        if ($t_result) {
            while ($arr = $t_result->fetch()) {
                $this->columns[$p_table][] = strtolower( $arr[0] );
            }
        }
		return $this->columns[$p_table];
	}
}

