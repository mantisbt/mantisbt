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
 * Abstract database driver class.
 * @package MantisBT
 * @subpackage classes
 */
abstract class MantisDatabase {
    /**
	 * array - cache of column info 
	 */
    protected $columns = array(); 
    /**
	 * array - cache of table info 
	 */
    protected $tables  = null;

    /** 
	 * string - db host name 
	 */
    protected $dbhost;
    /** 
	 * string - db host user 
	 */
    protected $dbuser;
    /** 
	 * string - db host password 
	 */
    protected $dbpass;
    /** 
	 * string - db name 
	 */
    protected $dbname;
    /** 
	 * string - db dsn
	 */
    protected $dbdsn;

    /** 
	 * string - db table prefix
	 */
    protected static $dbprefix;	
    /** 
	 * string - db table suffix
	 */
    protected static $dbsuffix;
	
    /** 
	 * Database or driver specific options, such as sockets or TCPIP db connections
	 */
    protected $dboptions;

    /**
	 * Database query counter (performance counter).
	 */
    protected $queries = 0;

    /**
	 * Debug level 
	 */
    protected $debug  = false;

    /**
     * Contructor
     */
    public function __construct() {
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->dispose();
    }

	/**
	 * Set Database Table Prefixes/suffixes
	 * @param string $p_prefix prefix
	 * @param string $p_suffix suffix
	 */
	public function SetPrefixes( $p_prefix, $p_suffix ) {
		self::$dbprefix = $p_prefix;
		self::$dbsuffix = $p_suffix;
	}

    /**
     * Detects if all needed PHP stuff installed.
     * Note: can be used before connect()
     * @return mixed true if ok, string if something
     */
    public abstract function driver_installed();

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
        $classname = 'MantisDatabase_' . $t_driver_type . '_' . ucfirst($t_type[1]);
        return new $classname();
    }

    /**
     * Returns database driver type
     * Note: can be used before connect()
     * @return string db type mysql, pgsql, sqlsrv
     */
    protected abstract function get_dbtype();


    /**
     * Diagnose database and tables, this function is used
     * to verify database and driver settings, db engine types, etc.
     *
     * @return string null means everything ok, string means problem found.
     */
    public function diagnose() {
        return null;
    }

    /**
     * Connect to db
     * Must be called before other methods.
	 * @param string $p_dsn Database DSN
     * @param string $p_db_host Database host name
     * @param string $p_db_user Database Username
     * @param string $p_db_pass Database Password
     * @param string $p_db_name Database name
     * @param array $p_db_options driver specific options
     * @return bool true
     * @throws MantisDatabaseException if error
     */
    public abstract function connect($p_dsn, $p_db_host, $p_db_user, $p_db_pass, $p_db_name, array $p_db_options=null);


    /**
     * Attempt to create the database
     * @param string $p_db_host Database host name
     * @param string $p_db_user Database Username
     * @param string $p_db_pass Database Password
     * @param string $p_db_name Database name
     * @param array $p_db_options driver specific options
     * @return bool success
     */
    public function create_database($p_db_host, $p_db_user, $p_db_pass, $p_db_name, array $p_db_options=null) {
        return false;
    }

    /**
     * Close database connection and release all resources
     * and memory (especially circular memory references).
     * Do NOT use connect() again, create a new instance if needed.
     * @return void
     */
    public function dispose() {
        $this->columns = array();
        $this->tables  = null;
    }

    /**
     * Called before each db query.
     * @param string $p_sql SQL Query
     * @param array $p_params array of parameters
     * @return void
     */
    protected function query_start($p_sql, array $p_params=null ) {
        $this->last_sql       = $p_sql;
        $this->last_params    = $p_params;
        $this->last_time      = microtime(true);

		$this->queries++;
    }

    /**
     * Called immediately after each db query.
     * @param mixed $p_result db specific result
     * @return void
     */
    protected function query_end($p_result) {
        if ($p_result !== false) {
            return;
        }
    }

    /**
     * Returns database server info array
     * @return array
     */
    public abstract function get_server_info();

    /**
     * Returns last error reported by database engine.
     * @return string error message
     */
    public abstract function get_last_error();

    /**
     * Return tables in database WITHOUT current prefix
	 * @param bool $p_use_cache whether to use internal cache of tables
     * @return array of table names in lowercase and without prefix
     */
    public abstract function get_tables($p_use_cache=true);

    /**
     * Return table indexes - everything lowercased
	 * @param string $p_table table name
     * @return array of arrays
     */
    public abstract function get_indexes($p_table);

    /**
     * Returns detailed information about columns in table. This information is cached internally.
     * @param string $p_table table name
	 * @param bool $p_use_cache whether to use internal cache of tables
     * @return array of database_column_info objects indexed with column names
     */
    public abstract function get_columns($p_table, $p_use_cache=true);


    /**
     * Reset internal column details cache
     * @return void
     */
    public function reset_caches() {
        $this->columns = array();
        $this->tables  = null;
    }

    /**
     * Attempt to change db encoding toUTF-8 if possible
     * @return bool success
     */
    public function change_db_encoding() {
        return false;
    }

	/**
	 * Get Last insert ID for given table
	 * @param string $p_table table name
     */
	abstract public function get_insert_id( $p_table );

    /**
     * Enable/disable debugging mode
     * @param bool $state
     * @return void
     */
    public function set_debug($state) {
        $this->debug = $state;
    }

    /**
     * Returns debug status
     * @return bool $state
     */
    public function get_debug() {
        return $this->debug;
    }

    /**
     * Execute general sql query. Should be used only when no other method suitable.
     * Do NOT use this to make changes in db structure, use database_manager::execute_sql() instead!
     * @param string $sql query
     * @param array $params query parameters
     * @return bool true
     * @throws MantisDatabaseException if error
     */
    public abstract function execute($sql, array $params=null);

    /**
	 * Perform SQL SELECT query
     * @param string $p_sql SQL query
	 * @param int $p_limit Number of results to return
	 * @param int $p_offset offset query results for paging
     * @param array $p_params query parameters
     * @return bool true
     * @throws MantisDatabaseException if error
     */
    public abstract function SelectLimit( $p_sql, $p_limit, $p_offset, array $p_params = null );
	
    /**
     * Returns number of queries done by this database
     * @return int
     */
    public function perf_get_queries() {
        return $this->queries;
    }

    /**
     * Returns whether database is connected
     * @return bool
     */	
    public abstract function IsConnected();

	/**
	 * Given a short table name e.g. {table} returns the full sql table name
	 * @param string $p_table table name
	 * @return string
	 */
	public function GetTableName($p_table) {
		return strtr($p_table, array(
							'{' => self::$dbprefix,
							'}' => self::$dbsuffix
							)
					);
	}

	/**
	 * Prepare SQL string - expand tablenames and types
	 * @param string $p_sql SQL Query
	 * @return string
	 */
	protected function PrepareSQLString($p_sql) {
		return strtr($p_sql, array(
							'{' => self::$dbprefix, 
							'}' => self::$dbsuffix,
							'%s' => '?',
							'%d' => '?',
							'%b' => '?',
							)
					);
	}

	/**
	 * Prepare SQL parameters. This is called for each param after PrepareSQLString.
	 * @param string $p_param SQL Query
	 * @return string
	 */
	protected function PrepareSQLParam($p_param) {
		if( is_bool( $p_param ) ) {
			return (int)$p_param;
		}
		return $p_param;
	}

	/**
	 * Legacy function - DO NOT USE
	 * Returns a 'Null' Datetime [for installer]
     * @return string
	 */
	public function legacy_null_date() {
        return "1970-01-01 00:00:01";
    }
	
	/**
	 * Legacy function - DO NOT USE
	 * Converts a legacy datetime to a timestamp [for installer]
	 * @param string $p_date Date
     * @throws MantisBT\Exception\UnknownException
     * @return int
	 */
	public function legacy_timestamp( $p_date ) {
		if( $p_date == '0000-00-00 00:00:00' ) {
			return 0;
		}

		$p_timestamp = strtotime( $p_date );
		if ( $p_timestamp == false ) {
			throw new MantisBT\Exception\UnknownException();
		}
		return $p_timestamp;
	}
}
