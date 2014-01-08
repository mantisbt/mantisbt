<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package CoreAPI
 * @subpackage VersionAPI
 */

/**
 * Version Data Structure Definition
 * @package MantisBT
 * @subpackage classes
 */
class VersionData {
	protected $id = 0;
	protected $project_id = 0;
	protected $version = '';
	protected $description = '';
	protected $released = VERSION_FUTURE;
	protected $date_order = 1;
	protected $obsolete = 0;

	/**
	 * @param string $name
	 * @param string $value
	 * @private
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'date_order':
				if( !is_numeric($value) ) {
					if( $value == '' ) {
						$value = date_get_null();
					}  else {
						$value = strtotime( $value );
						if ( $value === false ) {
							trigger_error( ERROR_INVALID_DATE_FORMAT, ERROR );
						}
					}
				}
		}
		$this->$name = $value;
	}

	/**
	 * @param string $p_string
	 * @private
	 */
	public function __get( $p_name ) {
		return $this->{$p_name};
	}
}

$g_cache_versions = array();

/**
 * Cache a version row if necessary and return the cached copy
 * If the second parameter is true (default), trigger an error
 * if the version can't be found.  If the second parameter is
 * false, return false if the version can't be found.
 * @param int $p_version_id
 * @param bool $p_trigger_errors
 * @return array
 */
function version_cache_row( $p_version_id, $p_trigger_errors = true ) {
	global $g_cache_versions;

	$c_version_id = db_prepare_int( $p_version_id );
	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	if( isset( $g_cache_versions[$c_version_id] ) ) {
		return $g_cache_versions[$c_version_id];
	}

	$query = "SELECT *
				  FROM $t_project_version_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_version_id ) );

	if( 0 == db_num_rows( $result ) ) {
		$g_cache_versions[$c_version_id] = false;

		if( $p_trigger_errors ) {
			error_parameters( $p_version_id );
			trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$row = db_fetch_array( $result );
	$g_cache_versions[$c_version_id] = $row;

	return $row;
}

/**
 * Check whether the version exists
 * $p_project_id : null will use the current project, otherwise the specified project
 * Returns true if the version exists, false otherwise
 * @param int $p_version_id
 * @return bool
 */
function version_exists( $p_version_id ) {
	return version_cache_row( $p_version_id, false ) !== false;
}

/**
 * Check whether the version name is unique
 * Returns true if the name is unique, false otherwise
 * @param string $p_version
 * @param int $p_project_id
 * @return bool
 */
function version_is_unique( $p_version, $p_project_id = null ) {
	return version_get_id( $p_version, $p_project_id ) === false;
}

/**
 * Check whether the version exists
 * Trigger an error if it does not
 * @param int $p_version_id
 */
function version_ensure_exists( $p_version_id ) {
	if( !version_exists( $p_version_id ) ) {
		error_parameters( $p_version_id );
		trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
	}
}

/**
 * Check whether the version is unique within a project
 * Trigger an error if it is not
 * @param string $p_version
 * @param int $p_project_id
 */
function version_ensure_unique( $p_version, $p_project_id = null ) {
	if( !version_is_unique( $p_version, $p_project_id ) ) {
		trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
	}
}

/**
 * Add a version to the project
 * @param int $p_project_id
 * @param string $p_version
 * @param int $p_released
 * @param string $p_description
 * @param int $p_date_order
 * @param bool $p_obsolete
 * @return int
 */
function version_add( $p_project_id, $p_version, $p_released = VERSION_FUTURE, $p_description = '', $p_date_order = null, $p_obsolete = false ) {
	$c_project_id = db_prepare_int( $p_project_id );
	$c_released = db_prepare_int( $p_released );
	$c_obsolete = db_prepare_bool( $p_obsolete );

	if( null === $p_date_order ) {
		$c_date_order = db_now();
	} else {
		$c_date_order = $p_date_order;
	}

	version_ensure_unique( $p_version, $p_project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$query = "INSERT INTO $t_project_version_table
					( project_id, version, date_order, description, released, obsolete )
				  VALUES
					(" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query_bound( $query, Array( $c_project_id, $p_version, $c_date_order, $p_description, $c_released, $c_obsolete ) );

	# db_query errors on failure so:
	return db_insert_id( $t_project_version_table );
}

/**
 * Update the definition of a version
 * @param VersionData @p_version_info
 * @return true
 */
function version_update( $p_version_info ) {
	version_ensure_exists( $p_version_info->id );

	$t_old_version_name = version_get_field( $p_version_info->id, 'version' );

	# check for duplicates
	if(( utf8_strtolower( $t_old_version_name ) != utf8_strtolower( $p_version_info->version ) ) && !version_is_unique( $p_version_info->version, $p_version_info->project_id ) ) {
		trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
	}

	$c_version_id = db_prepare_int( $p_version_info->id );
	$c_version_name = $p_version_info->version;
	$c_old_version_name = $t_old_version_name;
	$c_description = $p_version_info->description;
	$c_released = db_prepare_int( $p_version_info->released );
	$c_obsolete = db_prepare_bool( $p_version_info->obsolete );
	$c_date_order = $p_version_info->date_order;
	$c_project_id = db_prepare_int( $p_version_info->project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_history_table = db_get_table( 'mantis_bug_history_table' );

	$query = "UPDATE $t_project_version_table
				  SET version=" . db_param() . ",
					description=" . db_param() . ",
					released=" . db_param() . ",
					date_order=" . db_param() . ",
					obsolete=" . db_param() . "
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_version_name, $c_description, $c_released, $c_date_order, $c_obsolete, $c_version_id ) );

	if( $c_version_name != $c_old_version_name ) {
		$t_project_list = array( $c_project_id );
		if ( config_get( 'subprojects_inherit_versions' ) ) {
			$t_project_list = array_merge( $t_project_list, project_hierarchy_get_all_subprojects( $c_project_id, true ) );
		}
		$t_project_list = implode( ',', $t_project_list );

		$query = 'UPDATE ' . $t_bug_table . ' SET version=' . db_param() .
				 " WHERE ( project_id IN ( $t_project_list ) ) AND ( version=" . db_param() . ')';
		db_query_bound( $query, Array( $c_version_name, $c_old_version_name ) );

		$query = "UPDATE $t_bug_table
					  SET fixed_in_version=" . db_param() . "
					  WHERE ( project_id IN ( $t_project_list ) ) AND ( fixed_in_version=" . db_param() . ')';
		db_query_bound( $query, Array( $c_version_name, $c_old_version_name ) );

		$query = "UPDATE $t_bug_table
					  SET target_version=" . db_param() . "
					  WHERE ( project_id IN ( $t_project_list ) ) AND ( target_version=" . db_param() . ')';
		db_query_bound( $query, Array( $c_version_name, $c_old_version_name ) );

		$query = "UPDATE $t_history_table
			SET old_value=".db_param()."
			WHERE field_name IN ('version','fixed_in_version','target_version')
				AND old_value=".db_param()."
				AND bug_id IN (SELECT id FROM $t_bug_table WHERE project_id IN ( $t_project_list ))";
		db_query_bound( $query, Array( $c_version_name, $c_old_version_name ) );

		$query = "UPDATE $t_history_table
			SET new_value=".db_param()."
			WHERE field_name IN ('version','fixed_in_version','target_version')
				AND new_value=".db_param()."
				AND bug_id IN (SELECT id FROM $t_bug_table WHERE project_id IN ( $t_project_list ))";
		db_query_bound( $query, Array( $c_version_name, $c_old_version_name ) );

		/**
		 * @todo We should consider using ids instead of names for foreign keys.  The main advantage of using the names are:
		 *		- for history the version history entries will still be valid even if the version is deleted in the future. --  we can ban deleting referenced versions.
		 *		- when an issue is copied or moved from one project to another, we can keep the last version with the issue even if it doesn't exist in the new project.  Also previous history entries remain valid.
		 * @todo We probably need to update the saved filters too?
		 */
	}

	// db_query errors on failure so:
	return true;
}

/**
 * Remove a version from the project
 * @param int $p_version_id
 * @param string $p_new_version
 * @return true
 */
function version_remove( $p_version_id, $p_new_version = '' ) {
	$c_version_id = db_prepare_int( $p_version_id );

	version_ensure_exists( $p_version_id );

	$t_old_version = version_get_field( $p_version_id, 'version' );
	$t_project_id = version_get_field( $p_version_id, 'project_id' );
	$c_project_id = db_prepare_int( $t_project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "DELETE FROM $t_project_version_table
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_version_id ) );

	$t_project_list = array( $c_project_id );
	if ( config_get( 'subprojects_inherit_versions' ) ) {
		$t_project_list = array_merge( $t_project_list, project_hierarchy_get_all_subprojects( $c_project_id, true ) );
	}
	$t_project_list = implode( ',', $t_project_list );

	$query = "UPDATE $t_bug_table
				  SET version=" . db_param() . "
				  WHERE project_id IN ( $t_project_list ) AND version=" . db_param();
	db_query_bound( $query, Array( $p_new_version, $t_old_version ) );

	$query = "UPDATE $t_bug_table
				  SET fixed_in_version=" . db_param() . "
				  WHERE ( project_id IN ( $t_project_list ) ) AND ( fixed_in_version=" . db_param() . ')';
	db_query_bound( $query, Array( $p_new_version, $t_old_version ) );

	$query = "UPDATE $t_bug_table
				  SET target_version=" . db_param() . "
				  WHERE ( project_id IN ( $t_project_list ) ) AND ( target_version=" . db_param() . ')';
	db_query_bound( $query, array( $p_new_version, $t_old_version ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Remove all versions associated with a project
 * @param int $p_project_id
 * @return true
 */
function version_remove_all( $p_project_id ) {
	$c_project_id = db_prepare_int( $p_project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	# remove all references to versions from verison, fixed in version and target version.
	$query = "UPDATE $t_bug_table
				  SET version='', fixed_in_version='', target_version=''
				  WHERE project_id=" . db_param();
	db_query_bound( $query, array( $c_project_id ) );

	# remove the actual versions associated with the project.
	$query = "DELETE FROM $t_project_version_table
				  WHERE project_id=" . db_param();
	db_query_bound( $query, array( $c_project_id ) );

	# db_query errors on failure so:
	return true;
}

$g_cache_versions_project = null;

/**
 * Cache version information for an array of project id's
 * @param array $p_project_id_array
 * @return null
 */
function version_cache_array_rows( $p_project_id_array ) {
	global $g_cache_versions, $g_cache_versions_project;

	$c_project_id_array = array();

	foreach( $p_project_id_array as $t_project_id ) {
		if( !isset( $g_cache_versions_project[(int) $t_project_id] ) ) {
			$c_project_id_array[] = (int) $t_project_id;
			$g_cache_versions_project[(int) $t_project_id] = array();
		}
	}

	if( empty( $c_project_id_array ) ) {
		return;
	}

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$query = "SELECT *
				  FROM $t_project_version_table
				  WHERE project_id IN (" . implode( ',', $c_project_id_array ) . ')
				  ORDER BY date_order DESC';
	$result = db_query_bound( $query );

	$rows = array();
	while( $row = db_fetch_array( $result ) ) {
		$g_cache_versions[(int) $row['id']] = $row;

		$rows[ (int)$row[ 'project_id' ] ][] = $row['id'];
	}

	foreach( $rows as $t_project_id => $t_row ) {
		$g_cache_versions_project[ (int)$t_project_id ] = $t_row;
	}
	return;
}

/**
 * Return all versions for the specified project
 * @param int $p_project_id
 * @param int $p_released
 * @param bool $p_obsolete
 * @return array Array of version rows (in array format)
 */
function version_get_all_rows( $p_project_id, $p_released = null, $p_obsolete = false, $p_inherit = null ) {
	global $g_cache_versions, $g_cache_versions_project;

	if ( $p_inherit === null ) {
		$t_inherit = ( ON == config_get( 'subprojects_inherit_versions' ) );
	} else {
		$t_inherit = $p_inherit;
	}

	if( $t_inherit ) {
		$t_project_ids = project_hierarchy_inheritance( $p_project_id );
	} else {
		$t_project_ids [] = $p_project_id;
	}

	$t_is_cached = true;
	foreach( $t_project_ids as $t_project_id ) {
		if( !isset( $g_cache_versions_project[$t_project_id] ) ) {
			$t_is_cached = false;
			break;
		}
	}
	if( $t_is_cached ) {
		$t_versions = array();
		foreach( $t_project_ids as $t_project_id ) {
			if( !empty( $g_cache_versions_project[$t_project_id]) ) {
				foreach( $g_cache_versions_project[$t_project_id] as $t_id ) {
					$t_versions[] = version_cache_row( $t_id );
				}
			}
		}
		return $t_versions;
	}

	$c_project_id = db_prepare_int( $p_project_id );
	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$t_param_count = 0;

	$t_project_where = version_get_project_where_clause( $p_project_id, $p_inherit );

	$query = "SELECT *
				  FROM $t_project_version_table
				  WHERE $t_project_where";

	$query_params = array();

	if( $p_released !== null ) {
		$c_released = db_prepare_int( $p_released );
		$query .= " AND released = " . db_param( $t_param_count++ );
		$query_params[] = $c_released;
	}

	if( $p_obsolete !== null ) {
		$c_obsolete = db_prepare_bool( $p_obsolete );
		$query .= " AND obsolete = " . db_param( $t_param_count++ );
		$query_params[] = $c_obsolete;
	}

	$query .= " ORDER BY date_order DESC";

	$result = db_query_bound( $query, $query_params );
	$count = db_num_rows( $result );
	$rows = array();
	for( $i = 0;$i < $count;$i++ ) {
		$row = db_fetch_array( $result );
		$g_cache_versions[(int) $row['id']] = $row;

		$rows[] = $row;
	}
	return $rows;
}

/**
 * Return all versions for the specified project, including subprojects
 * @param int $p_project_id
 * @param int $p_released
 * @param bool $p_obsolete
 * @return array
 */
function version_get_all_rows_with_subs( $p_project_id, $p_released = null, $p_obsolete = false ) {
	$t_project_where = helper_project_specific_where( $p_project_id );

	$t_param_count = 0;
	$t_query_params = array();

	if( $p_released === null ) {
		$t_released_where = '';
	} else {
		$c_released = db_prepare_int( $p_released );
		$t_released_where = "AND ( released = " . db_param( $t_param_count++ ) . " )";
		$t_query_params[] = $c_released;
	}

	if( $p_obsolete === null ) {
		$t_obsolete_where = '';
	} else {
		$c_obsolete = db_prepare_bool( $p_obsolete );
		$t_obsolete_where = "AND ( obsolete = " . db_param( $t_param_count++ ) . " )";
		$t_query_params[] = $c_obsolete;
	}

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$query = "SELECT *
				  FROM $t_project_version_table
				  WHERE $t_project_where $t_released_where $t_obsolete_where
				  ORDER BY date_order DESC";
	$result = db_query_bound( $query, $t_query_params );
	$count = db_num_rows( $result );
	$rows = array();
	for( $i = 0;$i < $count;$i++ ) {
		$row = db_fetch_array( $result );
		$rows[] = $row;
	}
	return $rows;
}

/**
 * Get the version_id, given the project_id and $p_version_id
 * returns false if not found, otherwise returns the id.
 * @param string $p_version
 * @param int $p_project_id
 * @param mixed $p_inherit true to look for version in parent projects, false not to, null to use default configuration.
 * @return int
 */
function version_get_id( $p_version, $p_project_id = null, $p_inherit = null ) {
	global $g_cache_versions;

	if( $p_project_id === null ) {
		$c_project_id = helper_get_current_project();
	} else {
		$c_project_id = db_prepare_int( $p_project_id );
	}

	foreach( $g_cache_versions as $t_version ) {
		if(( $t_version['version'] === $p_version ) && ( $t_version['project_id'] == $c_project_id ) ) {
			return $t_version['id'];
		}
	}

	$t_project_where = version_get_project_where_clause( $c_project_id, $p_inherit );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$query = "SELECT id FROM $t_project_version_table
					WHERE " . $t_project_where . " AND
						version=" . db_param();

	$result = db_query_bound( $query, Array( $p_version ) );

	if( 0 == db_num_rows( $result ) ) {
		return false;
	} else {
		return db_result( $result );
	}
}

/**
 * Get the specified field name for the specified version id.
 * triggers an error if version not found, otherwise returns the field value.
 * @param int $p_version_id
 * @param string $p_field_name
 * @return string
 */
function version_get_field( $p_version_id, $p_field_name ) {
	$row = version_cache_row( $p_version_id );

	if( isset( $row[$p_field_name] ) ) {
		return $row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * Gets the full name of a version.  This may include the project name as a prefix (e.g. '[MantisBT] 1.2.0')
 *
 * @param int $p_version_id  The version id.
 * @param bool $p_show_project  Whether to include the project or not, null means include the project if different from current.
 * @param int $p_current_project_id  The current project id or null to use the cookie.
 * @return string The full name of the version.
 */
function version_full_name( $p_version_id, $p_show_project = null, $p_current_project_id = null ) {
	if ( 0 == $p_version_id ) {
		# No Version
		return '';
	} else {
		$t_row = version_cache_row( $p_version_id );
		$t_project_id = $t_row['project_id'];

		$t_current_project_id = is_null( $p_current_project_id ) ? helper_get_current_project() : $p_current_project_id;

		if ( $p_show_project === null ) {
			$t_show_project = $t_project_id != $t_current_project_id;
		} else {
			$t_show_project = $p_show_project;
		}

		if ( $t_show_project && $t_project_id != $t_current_project_id ) {
			return '[' . project_get_name( $t_project_id ) . '] ' . $t_row['version'];
		}

		return $t_row['version'];
	}
}

/**
 * get information about a version given its id
 * @param int $p_version_id
 * @return VersionData
 */
function version_get( $p_version_id ) {
	static $t_vars;

	$row = version_cache_row( $p_version_id );

	if ($t_vars == null ) {
		$t_reflection = new ReflectionClass('VersionData');
		$t_vars = $t_reflection->getDefaultProperties();
	}

	$t_version_data = new VersionData;
	$t_row_keys = array_keys( $row );

	# Check each variable in the class
	foreach( $t_vars as $var => $val ) {
		# If we got a field from the DB with the same name
		if( in_array( $var, $t_row_keys, true ) ) {
			# Store that value in the object
			$t_version_data->$var = $row[$var];
		}
	}

	return $t_version_data;
}

/**
 * Return a copy of the version structure with all the instvars prepared for db insertion
 * @param VersionData $p_version_info
 * @return VersionData
 */
function version_prepare_db( $p_version_info ) {
	$p_version_info->id = db_prepare_int( $p_version_info->id );
	$p_version_info->project_id = db_prepare_int( $p_version_info->project_id );
	$p_version_info->released = db_prepare_int( $p_version_info->released );

	return $p_version_info;
}

/**
 * Checks whether the product version should be shown
 * (i.e. report, update, view, print).
 * @param integer $p_project_id  The project id.
 * @return bool true: show, false: otherwise.
 */
function version_should_show_product_version( $p_project_id ) {
	return ( ON == config_get( 'show_product_version', /* default */ null, /* user_id */ null, $p_project_id ) )
		|| ( ( AUTO == config_get( 'show_product_version', /* default */ null, /* user_id */ null, $p_project_id ) )
				&& ( count( version_get_all_rows( $p_project_id ) ) > 0 ) );
}

/**
 * Gets the where clause to use for retrieving versions.
 *
 * @param integer $p_project_id  The project id to use.
 * @param bool    $p_inherit  Include versions from parent projects? true: yes, false: no, null: use default configuration.
 * @return string The where clause not including WHERE.
 */
function version_get_project_where_clause( $p_project_id, $p_inherit ) {
	if ( $p_project_id == ALL_PROJECTS ) {
		$t_inherit = false;
	} else {
		if ( $p_inherit === null ) {
			$t_inherit = ( ON == config_get( 'subprojects_inherit_versions' ) );
		} else {
			$t_inherit = $p_inherit;
		}
	}

	$c_project_id = db_prepare_int( $p_project_id );

	if ( $t_inherit ) {
		$t_project_ids = project_hierarchy_inheritance( $p_project_id );

		$t_project_where = ' project_id IN ( ' . implode( ', ', $t_project_ids ) . ' ) ';
	} else {
		$t_project_where = ' project_id=' . $c_project_id . ' ';
	}

	return $t_project_where;
}

