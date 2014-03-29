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
	 * @var string Represents required MSSQL Version
	 */
	const REQUIRED_MSSQL_VERSION = '1.0';

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
	public function IsDriverInstalled() {
		return extension_loaded( 'pdo_sqlsrv' );
	}

	/**
	 * Returns db type string
	 * @return string
	 */
	public function GetDbType() {
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
	 * Returns array of database check results for the given driver
	 * @returns array
	 */
	public function Diagnose() {
		$t_checks = array();

		$t_version_info = $this->GetServerInfo();
		$t_checks[] = array( 'Checking MSSQL Version',
							  version_compare( $t_version_info['version'], REQUIRED_MSSQL_VERSION, '>' ),
							  'MSSQL ' . REQUIRED_MSSQL_VERSION . ' or later is required for installation.');

		return $t_checks;
	}

	/**
	 * Execute query with a limit
	 * @param string $p_sql
	 * @param int $p_limit
	 * @param int $p_offset
	 * @param array $p_params
	 * @return object
	 */
	public function SelectLimit( $p_sql, $p_limit = -1, $p_offset = -1, array $p_params = null) {
		//if ( $p_offset != -1 ) {
		//	echo 'offset not supported';
		//	die;
		//}
		//$t_stroffset = ($p_offset>=0) ? " OFFSET $p_offset" : '';

		if ($p_limit > 0 && $p_offset <= 0) {
			$p_sql = preg_replace( '/(^\s*select\s+(distinctrow|distinct)?)/i', '\\1 top ' . $p_limit . ' ', $p_sql );
		}
		//if ($p_limit < 0) $p_limit = '18446744073709551615';
		if( $p_offset > 0 ) {
			$t_ret = $this->Execute($p_sql, $p_params);
			for( $i = 0; $i < $p_offset; $i++ ) {
				$t_ret->fetch();
			}
			return $t_ret;
		}
		return $this->Execute($p_sql, $p_params);
	}

	/**
	 * Return whether database exists
	 * @param string $p_name
	 * @return bool
	 * @throws MantisBT\Exception\Database\QueryFailed
	 */
	public function DatabaseExists( $p_name ) {
		$t_sql = 'select name from sys.databases where name = ?';
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
		$sql = "select name from sysobjects where (type='U' or type='V')";

		$t_result = $this->Execute( $sql );
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
		$t_sql = "SELECT idx.name AS key_name,
					   col.name AS column_name,
					   col.column_id,
					   ~idx.is_unique as isunique,
					   idx.is_primary_key,
					   CASE idx.type
						   WHEN '1' THEN 'clustered'
						   WHEN '2' THEN 'nonclustered'
						   ELSE NULL
					   END AS flags
				FROM sys.tables AS tbl
				JOIN sys.schemas AS scm ON tbl.schema_id = scm.schema_id
				JOIN sys.indexes AS idx ON tbl.object_id = idx.object_id
				JOIN sys.index_columns AS idxcol ON idx.object_id = idxcol.object_id AND idx.index_id = idxcol.index_id
				JOIN sys.columns AS col ON idxcol.object_id = col.object_id AND idxcol.column_id = col.column_id
				WHERE tbl.name=%s
				ORDER BY idx.index_id ASC, idxcol.index_column_id ASC";
		$t_result = $this->Execute( $t_sql, array($p_table) );

		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$t_index_name = strtolower( $t_row['key_name'] );

				if( !isset( $t_indexes[$t_index_name] ) ) {
					$t_info = new stdClass();

					$t_info->name = $t_row['key_name'];
					$t_info->unique = ($t_row['isunique'] == 0);
					$t_info->primary = ($t_row['is_primary_key'] == 1);
					$t_info->columns = array();

					$t_indexes[$t_index_name] = $t_info;
				}
				$t_indexes[$t_index_name]->columns[$t_row['column_id'] - 1] = strtolower( $t_row['column_name'] );
			}
		}
		return $t_indexes;
	}

	/**
	 * Get List of database columns for given table
	 * @param string $p_table
	 * @param bool $usecache
	 * @return array
	 */
	public function GetColumns($p_table, $usecache=true) {
		if ($usecache && isset($this->columns[$p_table])) {
			return $this->columns[$p_table];
		}

		$this->columns[$p_table] = array();

		$sql = "SELECT    col.name,
						  type.name AS type,
						  col.max_length AS length,
						  ~col.is_nullable AS notnull,
						  def.definition AS [default],
						  col.scale,
						  col.precision,
						  col.is_identity AS autoincrement,
						  (SELECT idx.is_primary_key AS [primary] FROM sys.indexes AS idx
							JOIN sys.index_columns AS idxcol ON idx.object_id = idxcol.object_id
															AND idx.index_id = idxcol.index_id
							JOIN sys.columns AS col2 ON idxcol.object_id = col2.object_id
															AND idxcol.column_id = col2.column_id
							WHERE obj.object_id = idx.object_id
															AND col2.column_id = col.column_id) as pkey
				FROM      sys.columns AS col
				JOIN      sys.types AS type
				ON        col.user_type_id = type.user_type_id
				JOIN      sys.objects AS obj
				ON        col.object_id = obj.object_id
				JOIN      sys.schemas AS scm
				ON        obj.schema_id = scm.schema_id
				LEFT JOIN sys.default_constraints def
				ON        col.default_object_id = def.object_id
				AND       col.object_id = def.parent_object_id
				LEFT JOIN sys.extended_properties AS prop
				ON        obj.object_id = prop.major_id
				AND       col.column_id = prop.minor_id
				AND       prop.name = 'MS_Description'
				WHERE     obj.type = 'U'
				AND obj.name=%s";
		$t_result = $this->Execute( $sql, array( $p_table ) );
		if ($t_result) {
			while ($t_row = $t_result->fetch()) {
				$t_info = new stdClass();
				$t_info->name = $t_row['name'];
				$t_info->not_null = ($t_row['notnull'] == '1');
				$t_info->primary = ($t_row['pkey'] == '1');
				$t_info->auto_increment = ($t_row['autoincrement'] == '1');

				$t_type = $t_row['type'];
				$t_info->binary = (strpos( $t_type, 'varbinary' ) !== false);
				$t_info->unsigned = (strpos( $t_type, 'unsigned' ) !== false);

				if (!$t_info->binary) {
					if ($t_row['default'] != '' && $t_row['default'] != 'NULL') {
						$t_info->has_default = true;
						$t_info->default_value = substr( $t_row['default'], 2, -2);
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

				$t_info->max_length = $t_row['length'];

				$this->columns[$p_table][strtolower( $t_row['name'] )] = $t_info;
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

		$t_result2 = $this->Execute('select @@VERSION as version' );
		$t_row = $t_result2->fetch();
		if( isset( $t_row['version'] ) ) {
			$t_result['description'] = $t_row['version'];
		}
		return $t_result;
	}
}