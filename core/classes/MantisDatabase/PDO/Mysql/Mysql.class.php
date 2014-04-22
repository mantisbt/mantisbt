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
	 * @var string Represents required MySQL Version
	 */
	const REQUIRED_MYSQL_VERSION = '5.0.8';

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
	public function IsDriverInstalled() {
		return extension_loaded( 'pdo_mysql' );
	}

	/**
	 * Returns db type string
	 * @return string
	 */
	public function GetDbType() {
		return 'mysql';
	}

	/**
	 * Returns array of database check results for the given driver
	 * @returns array
	 */
	public function Diagnose() {
		$t_checks = array();

		$t_version_info = $this->GetServerInfo();
		$t_checks[] = array( 'Checking Mysql Version',
							  version_compare( $t_version_info['version'], self::REQUIRED_MYSQL_VERSION, '>' ),
							  'Mysql ' . self::REQUIRED_MYSQL_VERSION . ' or later is required for installation.');

		return $t_checks;
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
	 * @param string $p_sql
	 * @param int $p_limit
	 * @param int $p_offset
	 * @param array $p_params
	 * @return object
	 */
	public function SelectLimit( $p_sql, $p_limit, $p_offset, array $p_params = null) {
		$t_stroffset = ($p_offset>=0) ? ' OFFSET ' . $p_offset : '';

		if ($p_limit < 0) $p_limit = '18446744073709551615';

		return $this->Execute($p_sql . ' LIMIT ' . (int)$p_limit . $t_stroffset, $p_params);
	}

	/**
	 * Return whether database exists
	 * @param string $p_name
	 * @return bool
	 * @throws MantisBT\Exception\Database\QueryFailed
	 */
	public function DatabaseExists( $p_name ) {
		$t_sql = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?';
		try {
			$t_result = $this->Execute( $t_sql, array( $p_name ) );
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
	public function GetTables($p_use_cache=true) {
		if ($p_use_cache && $this->tables !== null) {
			return $this->tables;
		}
		$this->tables = array();
		$t_sql = 'SHOW TABLES';

		$t_result = $this->Execute( $t_sql );
		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$this->tables[] = strtolower( $t_row[0] );
			}
		}
		return $this->tables;
	}

	/**
	 * Get indexes on table
	 * @param string $p_table
	 * @return array
	 */
	public function GetIndexes($p_table) {
		$t_indexes = array();
		$t_sql = 'SHOW INDEXES FROM ' . $this->GetTableName( $p_table );
		$t_result = $this->Execute( $t_sql );

		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$t_index_name = strtolower( $t_row['key_name'] );
				if( !isset( $t_indexes[$t_index_name] ) ) {
					$t_info = new stdClass();
					$t_info->unique = ( $t_row['non_unique'] == 0 );
					$t_info->primary = ( $t_row['key_name'] == 'PRIMARY' );
					$t_info->name = $t_row['key_name'];
					$t_info->columns = array();

					$t_indexes[$t_index_name] = $t_info;
				}
				$t_indexes[$t_index_name]->columns[$t_row['seq_in_index']-1] = strtolower( $t_row['column_name'] );
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
	public function GetColumns($p_table, $p_use_cache=true) {
		if ($p_use_cache && isset($this->columns[$p_table])) {
			return $this->columns[$p_table];
		}

		$this->columns[$p_table] = array();

		$t_sql = 'SHOW COLUMNS FROM ' . $this->GetTableName( $p_table );
		$t_result = $this->Execute( $t_sql );
		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$t_info = new stdClass();
				$t_info->name = $t_row['field'];
				$t_info->not_null = ($t_row['null'] != 'YES');
				$t_info->primary = ($t_row['key'] == 'PRI');
				$t_info->auto_increment = (strpos($t_row['extra'], 'auto_increment') !== false);

				$t_type = $t_row['type'];
				$t_info->binary = (strpos($t_type, 'blob') !== false || strpos($t_type, 'binary') !== false);
				$t_info->unsigned = (strpos($t_type, 'unsigned') !== false);

				if (!$t_info->binary) {
					if ($t_row['default'] != '' && $t_row['default'] != 'NULL') {
						$t_info->has_default = true;
						$t_info->default_value = $t_row['default'];
					} else {
						$t_info->has_default = false;
						$t_info->default_value = null;
					}
				} else {
					$t_info->has_default = false;
					$t_info->default_value = null;
				}

				$t_info->scale = null;
				$t_info->type = $t_type;
				$t_info->max_length = -1;

				if (preg_match("/^(.+)\((\d+),(\d+)/", $t_type, $t_matches)) {
					$t_info->type = $t_matches[1];
					$t_info->max_length = is_numeric($t_matches[2]) ? $t_matches[2] : -1;
					$t_info->scale = is_numeric($t_matches[3]) ? $t_matches[3] : -1;
				} elseif (preg_match("/^(.+)\((\d+)/", $t_type, $t_matches)) {
					$t_info->type = $t_matches[1];
					$t_info->max_length = is_numeric($t_matches[2]) ? $t_matches[2] : -1;
				}

				$this->columns[$p_table][strtolower( $t_row['field'] )] = $t_info;
			}
		}
		return $this->columns[$p_table];
	}

	/**
	 * Returns database server info array
	 * @return array
	 */
	public function GetServerInfo() {
		$t_result = parent::GetServerInfo();

		$t_result2 = $this->Execute('SHOW VARIABLES LIKE %s', array( 'version_comment' ));
		$t_row = $t_result2->fetch();
		if( isset( $t_row['value'] ) ) {
			$t_result['description'] = $t_row['value'];
		}
		// mysql version ends with -log so...
		if( preg_match('/(([0-9\.])+)/', $t_result['version'], $t_return) ) {
			$t_result['version'] = $t_return[1];
		}
		return $t_result;
	}
}

