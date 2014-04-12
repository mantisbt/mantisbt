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
 * PGSQL PDO driver class.
 * @package MantisBT
 * @subpackage classes
 */
class MantisDatabase_PDO_Pgsql extends MantisDatabase_PDO {

	/**
	 * @var string Represents required PGSQL Version
	 */
	const REQUIRED_PGSQL_VERSION = '8.0';

	/**
	 * Returns the driver-dependent DSN for PDO based on members stored by connect.
	 * Must be called after connect (or after $dbname, $dbhost, etc. members have been set).
	 * @return string driver-dependent DSN
	 */
	protected function get_dsn() {
		return  'pgsql:host=' . $this->dbhost . ';dbname=' . $this->dbname;
	}

	/**
	 * Returns whether driver is installed
	 * @return bool
	 */
	public function IsDriverInstalled() {
		return extension_loaded( 'pdo_pgsql' );
	}

	/**
	 * Returns db type string
	 * @return string
	 */
	public function GetDbType() {
		return 'pgsql';
	}

	/**
	 * Returns array of database check results for the given driver
	 * @returns array
	 */
	public function Diagnose() {
		$t_checks = array();

		$t_version_info = $this->GetServerInfo();
		$t_checks[] = array( 'Checking Postgresql Version',
							  version_compare( $t_version_info['version'], self::REQUIRED_PGSQL_VERSION, '>' ),
							  'Postgresql ' . self::REQUIRED_PGSQL_VERSION . ' or later is required for installation.');

		return $t_checks;
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
	public function SelectLimit( $sql, $p_limit, $p_offset, array $arr_parms = null) {
		$t_stroffset = ($p_offset>=0) ? ' OFFSET ' . $p_offset : '';

		if ($p_limit < 0) $p_limit = '18446744073709551615';

		return $this->Execute( $sql . ' LIMIT ' . (int)$p_limit . $t_stroffset, $arr_parms );
	}

	/**
	 * Return whether database exists
	 * @param string $p_name
	 * @return bool
	 * @throws MantisBT\Exception\Database\QueryFailed
	 */
	public function DatabaseExists( $p_name ) {
		$sql = 'select * from pg_database where datname = ?';
		try {
			$t_result = $this->Execute( $sql, array( $p_name ) );
		} catch (PDOException $ex) {
			throw new MantisBT\Exception\Database\QueryFailed( array( $ex->getCode(), $ex->getMessage(), $sql ) );
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
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";

		$t_result = $this->Execute( $sql );
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
	public function GetIndexes($p_table) {
		$t_indexes = array();
		$t_sql = 'SELECT c.relname, i.indisunique, i.indisprimary, ARRAY(
					   SELECT pg_get_indexdef(i.indexrelid, k + 1, true)
					   FROM generate_subscripts(i.indkey, 1) as k
					   ORDER BY k
					   ) as indkey_names
					FROM pg_catalog.pg_class c
					JOIN pg_catalog.pg_index i ON i.indexrelid=c.oid
					JOIN pg_catalog.pg_class c2 ON c2.oid=i.indrelid
					,pg_namespace n
					WHERE (c2.relname=lower(%s)) and c.relnamespace=c2.relnamespace and c.relnamespace=n.oid';
		$t_result = $this->Execute( $t_sql, array( $p_table ) );

		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$t_index_name = strtolower( $t_row['relname'] );

				if( !isset( $t_indexes[$t_index_name] ) ) {
					$t_info = new stdClass();

					$t_info->name = $t_row['relname'];
					$t_info->unique = $t_row['indisunique'];
					$t_info->primary = $t_row['indisprimary'];
					$t_info->columns = array();

					$t_indexes[$t_index_name] = $t_info;
				}
				$t_indexes[$t_index_name]->columns = explode( ',', substr( $t_row['indkey_names'], 1, -1 ) );
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

		$t_table = self::GetTableName($p_table);

		$sql = "SELECT a.attname AS field,
					a.attnotnull AS null,
					a.atthasdef as has_default,
					(SELECT 't'
					 FROM pg_index
					 WHERE c.oid = pg_index.indrelid
						AND pg_index.indkey[0] = a.attnum
						AND pg_index.indisprimary = 't'
					) AS key,
					t.typname AS type,
					a.atttypmod as max_length,
					(SELECT pg_attrdef.adsrc
					 FROM pg_attrdef
					 WHERE c.oid = pg_attrdef.adrelid
						AND pg_attrdef.adnum=a.attnum
					) AS default
					FROM pg_attribute a, pg_class c, pg_type t
					WHERE relkind in ('r','v') AND (lower(c.relname) = lower(%s))
						AND a.attnum > 0
						AND a.attrelid = c.oid
						AND a.atttypid = t.oid
					ORDER BY a.attnum";
		$t_result = $this->Execute( $sql, array( $t_table ) );

		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$t_info = new stdClass();
				$t_info->name = $t_row['field'];
				$t_info->not_null = ($t_row['null'] == true);
				$t_info->primary = ($t_row['key'] == true);
				$t_info->auto_increment = (substr( $t_row['default'], 0, 7 ) == 'nextval');

				$t_type = $t_row['type'];
				$t_info->binary = (strpos( $t_type, 'bytea' ) !== false );
				$t_info->unsigned = null; // NOT SUPPORTED BY PGSQL
				if( $t_info->auto_increment ) {
					$t_info->has_default = false;
					$t_info->default_value = '';
				} else {
					$t_info->has_default = $t_row['has_default'];

					if( substr( $t_row['default'], 0, 1 ) == "'" ) {
						$t_default = substr( $t_row['default'], 1, strpos($t_row['default'], "'::" )-1 );
					} else {
						$t_default = $t_row['default'];
					}

					$t_info->default_value = $t_default;
					/*if (!$t_info->binary) {
						if ($t_row['default'] != '' && $t_row['default'] != 'NULL') {
							//$t_info->has_default = true;
							$t_info->default_value = $t_row['default'];
						} else {
							//$t_info->has_default = false;
							$t_info->default_value = null;
						}
					} else {
						//$t_info->has_default = false;
						$t_info->default_value = null;
					}*/

				}



				$t_info->scale = null;
				$t_info->type = $t_type;
				$t_info->max_length = ($t_row['max_length'] == -1 ? -1 : $t_row['max_length']-4);

				/*if (preg_match("/^(.+)\((\d+),(\d+)/", $t_type, $t_matches)) {
					$t_info->type = $t_matches[1];
					$t_info->max_length = is_numeric($t_matches[2]) ? $t_matches[2] : -1;
					$t_info->scale = is_numeric($t_matches[3]) ? $t_matches[3] : -1;
				} elseif (preg_match("/^(.+)\((\d+)/", $t_type, $t_matches)) {
					$t_info->type = $t_matches[1];
					$t_info->max_length = is_numeric($t_matches[2]) ? $t_matches[2] : -1;
				}	*/

				$this->columns[$p_table][strtolower( $t_row['field'] )] = $t_info;
			}
		}
		var_dump( $this->columns );
		return $this->columns[$p_table];
	}

	/**
	 * Returns database server info array
	 * @return array
	 */
	public function GetServerInfo() {
		$t_result = parent::GetServerInfo();

		$t_result2 = $this->Execute('select version()' );
		$t_row = $t_result2->fetch();
		if( isset( $t_row['version'] ) ) {
			$t_result['description'] = $t_row['version'];
		}
		return $t_result;
	}
}

