<?php
# MantisBT - A PHP based bugtracking system

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
 * Version API
 *
 * @package CoreAPI
 * @subpackage VersionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'date_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * Version Data Structure Definition
 */
class VersionData {
	/**
	 * Version id
	 */
	protected $id = 0;

	/**
	 * Project ID
	 */
	protected $project_id = 0;

	/**
	 * Version name
	 */
	protected $version = '';

	/**
	 * Version Description
	 */
	protected $description = '';

	/**
	 * Version Release Status e.g. VERSION_FUTURE
	 */
	protected $released = VERSION_FUTURE;

	/**
	 * Obsolete
	 */
	protected $obsolete = 0;

	/**
	 * Date Order
	 */
	protected $date_order = 1;

	/**
	 * VersionData constructor.
	 * Initialize the object with default values, or with data from a
	 * project_version table row.
	 * @param array|null $p_row
	 */
	public function __construct( array $p_row = null ) {
		if( $p_row !== null ) {
			$this->set_from_db_row( $p_row );
		}
	}

	/**
	 * Overloaded function
	 * @param string         $p_name  A valid property name.
	 * @param integer|string $p_value The property value to set.
	 * @return void
	 * @private
	 */
	public function __set( $p_name, $p_value ) {
		$t_value = $p_value;

		switch( $p_name ) {
			case 'date_order':
				if( !is_numeric( $p_value ) ) {
					if( $p_value == '' ) {
						$t_value = date_get_null();
					} else {
						$t_value = strtotime( $p_value );
						if( $t_value === false ) {
							throw new ClientException(
								"Invalid date format '$p_value'",
								ERROR_INVALID_DATE_FORMAT,
								array( $p_value ) );
						}
					}
				}
		}

		$this->$p_name = $t_value;
	}

	/**
	 * Overloaded function
	 * @param string $p_name A valid property name.
	 * @return integer|string
	 * @private
	 */
	public function __get( $p_name ) {
		return $this->{$p_name};
	}

	/**
	 * Initialize the object with data from a database row.
	 * @param array $p_row
	 */
	public function set_from_db_row( array $p_row ) {
		static $s_vars;

		if( $s_vars == null ) {
			$t_reflection = new ReflectionClass( $this );
			$s_vars = $t_reflection->getDefaultProperties();
		}

		# Check each variable in the class
		foreach( $s_vars as $t_var => $t_val ) {
			# If we got a field from the DB with the same name
			if( array_key_exists( $t_var, $p_row ) ) {
				# Store that value in the object
				$this->$t_var = $p_row[$t_var];
			}
		}
	}
}

/**
 * Array indexed by version id.
 * Each item is a version row, as retrieved from project_version table.
 */
$g_cache_versions = array();

/**
 * Array indexed by project_id.
 * Each item is an array of version ids that are linked to that project.
 * Note that this does not include versions inherited from parent projects.
 */
$g_cache_versions_project  = array();

/**
 * Cache a version row if necessary and return the cached copy
 * If the second parameter is true (default), trigger an error
 * if the version can't be found.  If the second parameter is
 * false, return false if the version can't be found.
 * @param integer $p_version_id     A version identifier to look up.
 * @param boolean $p_trigger_errors Whether to generate errors if not found.
 * @return array
 */
function version_cache_row( $p_version_id, $p_trigger_errors = true ) {
	global $g_cache_versions;

	$c_version_id = (int)$p_version_id;

	if( isset( $g_cache_versions[$c_version_id] ) ) {
		return $g_cache_versions[$c_version_id];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {project_version} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $c_version_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		$g_cache_versions[$c_version_id] = false;

		if( $p_trigger_errors ) {
			throw new ClientException(
				"Version with id $p_version_id not found",
				ERROR_VERSION_NOT_FOUND,
				array( $p_version_id ) );
		}

		return false;
	}

	$g_cache_versions[$c_version_id] = $t_row;

	return $t_row;
}

/**
 * Cache version information for an array of project id's
 * @param array $p_project_id_array An array of project identifiers.
 * @return void
 */
function version_cache_array_rows( array $p_project_ids ) {
	global $g_cache_versions_project, $g_cache_versions;

	$t_ids_to_fetch = array();
	foreach( $p_project_ids as $t_id ) {
		$c_id = (int)$t_id;
		if( !isset( $g_cache_versions_project[$c_id] ) ) {
			$t_ids_to_fetch[$c_id] = $c_id;
		}
	}
	if( empty( $t_ids_to_fetch ) ) {
		return;
	}

	$t_query = new DbQuery();
	$t_sql = 'SELECT * FROM {project_version} WHERE ' . $t_query->sql_in( 'project_id', $t_ids_to_fetch );
	$t_query->sql( $t_sql );
	while( $t_row = $t_query->fetch() ) {
		$c_project_id = (int)$t_row['project_id'];
		$c_version_id = (int)$t_row['id'];
		$g_cache_versions[$c_version_id] = $t_row;
		if( !isset( $g_cache_versions_project[$c_project_id] ) ) {
			$g_cache_versions_project[$c_project_id] = array();
		}
		$g_cache_versions_project[$c_project_id][] = $c_version_id;
		if( isset( $t_ids_to_fetch[$c_project_id] ) ) {
			unset( $t_ids_to_fetch[$c_project_id] );
		}
	}
	foreach( $t_ids_to_fetch as $t_id_not_found ) {
		$g_cache_versions_project[$t_id_not_found] = false;
	}
}

/**
 * Check whether the version exists
 * $p_project_id : null will use the current project, otherwise the specified project
 * Returns true if the version exists, false otherwise
 * @param integer $p_version_id A version identifier.
 * @return boolean
 */
function version_exists( $p_version_id ) {
	return version_cache_row( $p_version_id, false ) !== false;
}

/**
 * Check whether the version name is unique
 * Returns true if the name is unique, false otherwise
 * @param string  $p_version    A version string to check.
 * @param integer $p_project_id A valid Project identifier.
 * @return boolean
 */
function version_is_unique( $p_version, $p_project_id = null ) {
	return version_get_id( $p_version, $p_project_id ) === false;
}

/**
 * Check whether the version exists
 * Trigger an error if it does not
 * @param integer $p_version_id A version identifier.
 * @return void
 */
function version_ensure_exists( $p_version_id ) {
	if( !version_exists( $p_version_id ) ) {
		throw new ClientException(
			"Version with id '$p_version_id' not found",
			ERROR_VERSION_NOT_FOUND,
			array( $p_version_id ) );
	}
}

/**
 * Check whether the version is unique within a project
 * Trigger an error if it is not
 * @param string  $p_version    A version string.
 * @param integer $p_project_id A valid Project identifier.
 * @return void
 */
function version_ensure_unique( $p_version, $p_project_id = null ) {
	if( !version_is_unique( $p_version, $p_project_id ) ) {
		throw new ClientException(
			"Version '$p_version' already exists",
			ERROR_VERSION_DUPLICATE,
			array( $p_version ) );
	}
}

/**
 * Add a version to the project
 * @param integer $p_project_id  A valid project id.
 * @param string  $p_version     Name of a version to add.
 * @param integer $p_released    Release status of the version.
 * @param string  $p_description Description of the version.
 * @param integer $p_date_order  Date Order.
 * @param boolean $p_obsolete    Obsolete status of the version.
 * @return integer
 */
function version_add( $p_project_id, $p_version, $p_released = VERSION_FUTURE, $p_description = '', $p_date_order = null, $p_obsolete = false ) {
	$c_project_id = (int)$p_project_id ;
	$c_released = (bool)$p_released;
	$c_obsolete = (bool)$p_obsolete;

	if( null === $p_date_order ) {
		$c_date_order = db_now();
	} else {
		$c_date_order = $p_date_order;
	}

	version_ensure_unique( $p_version, $p_project_id );

	db_param_push();
	$t_query = 'INSERT INTO {project_version}
					( project_id, version, date_order, description, released, obsolete )
				  VALUES
					(' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array( $c_project_id, $p_version, $c_date_order, $p_description, $c_released, $c_obsolete ) );

	$t_version_id = db_insert_id( db_get_table( 'project_version' ) );

	event_signal( 'EVENT_MANAGE_VERSION_CREATE', array( $t_version_id ) );

	return $t_version_id;
}

/**
 * Update the definition of a version
 * @param VersionData $p_version_info A version structure to update.
 * @return void
 */
function version_update( VersionData $p_version_info ) {
	version_ensure_exists( $p_version_info->id );

	$t_old_version_name = version_get_field( $p_version_info->id, 'version' );

	# check for duplicates
	if( ( mb_strtolower( $t_old_version_name ) != mb_strtolower( $p_version_info->version ) ) && !version_is_unique( $p_version_info->version, $p_version_info->project_id ) ) {
		$t_version = $p_version_info->version;
		throw new ClientException(
			"Version '$t_version' already exists",
			ERROR_VERSION_DUPLICATE,
			array( $t_version ) );
	}

	$c_version_id = (int)$p_version_info->id;
	$c_version_name = $p_version_info->version;
	$c_old_version_name = $t_old_version_name;
	$c_description = $p_version_info->description;
	$c_released = (bool)$p_version_info->released;
	$c_obsolete = (bool)$p_version_info->obsolete;
	$c_date_order = $p_version_info->date_order;
	$c_project_id = (int)$p_version_info->project_id;

	db_param_push();
	$t_query = 'UPDATE {project_version}
				  SET version=' . db_param() . ',
					description=' . db_param() . ',
					released=' . db_param() . ',
					date_order=' . db_param() . ',
					obsolete=' . db_param() . '
				  WHERE id=' . db_param();
	db_query( $t_query, array( $c_version_name, $c_description, $c_released, $c_date_order, $c_obsolete, $c_version_id ) );

	if( $c_version_name != $c_old_version_name ) {
		$t_project_list = array( $c_project_id );
		if( config_get( 'subprojects_inherit_versions', null, ALL_USERS, ALL_PROJECTS ) ) {
			$t_project_list = array_merge( $t_project_list, project_hierarchy_get_all_subprojects( $c_project_id, true ) );
		}
		$t_project_list = implode( ',', $t_project_list );

		db_param_push();
		$t_query = 'UPDATE {bug} SET version=' . db_param() .
				 ' WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( version=' . db_param() . ')';
		db_query( $t_query, array( $c_version_name, $c_old_version_name ) );

		db_param_push();
		$t_query = 'UPDATE {bug} SET fixed_in_version=' . db_param() . '
					  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( fixed_in_version=' . db_param() . ')';
		db_query( $t_query, array( $c_version_name, $c_old_version_name ) );

		db_param_push();
		$t_query = 'UPDATE {bug} SET target_version=' . db_param() . '
					  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( target_version=' . db_param() . ')';
		db_query( $t_query, array( $c_version_name, $c_old_version_name ) );

		db_param_push();
		$t_query = 'UPDATE {bug_history}
			SET old_value='.db_param().'
			WHERE field_name IN (\'version\',\'fixed_in_version\',\'target_version\')
				AND old_value='.db_param().'
				AND bug_id IN (SELECT id FROM {bug} WHERE project_id IN ( ' . $t_project_list . ' ))';
		db_query( $t_query, array( $c_version_name, $c_old_version_name ) );

		db_param_push();
		$t_query = 'UPDATE {bug_history}
			SET new_value='.db_param().'
			WHERE field_name IN (\'version\',\'fixed_in_version\',\'target_version\')
				AND new_value='.db_param().'
				AND bug_id IN (SELECT id FROM {bug} WHERE project_id IN ( ' . $t_project_list . ' ))';
		db_query( $t_query, array( $c_version_name, $c_old_version_name ) );

		# @todo We should consider using ids instead of names for foreign keys.  The main advantage of using the names are:
		#		- for history the version history entries will still be valid even if the version is deleted in the future. --  we can ban deleting referenced versions.
		# 		- when an issue is copied or moved from one project to another, we can keep the last version with the issue even if it doesn't exist in the new project.  Also previous history entries remain valid.
		# @todo We probably need to update the saved filters too?
	}
}

/**
 * Remove a version from the project
 * @param integer $p_version_id  A valid version identifier.
 * @param string  $p_new_version A version string to update issues using the old version with.
 * @return void
 */
function version_remove( $p_version_id, $p_new_version = '' ) {
	version_ensure_exists( $p_version_id );

	event_signal( 'EVENT_MANAGE_VERSION_DELETE', array( $p_version_id, $p_new_version ) );

	$t_old_version = version_get_field( $p_version_id, 'version' );
	$t_project_id = version_get_field( $p_version_id, 'project_id' );

	db_param_push();
	$t_query = 'DELETE FROM {project_version} WHERE id=' . db_param();
	db_query( $t_query, array( (int)$p_version_id ) );

	$t_project_list = array( $t_project_id );
	if( config_get( 'subprojects_inherit_versions', null, ALL_USERS, ALL_PROJECTS ) ) {
		$t_project_list = array_merge( $t_project_list, project_hierarchy_get_all_subprojects( $t_project_id, true ) );
	}
	$t_project_list = implode( ',', $t_project_list );

	db_param_push();
	$t_query = 'UPDATE {bug} SET version=' . db_param() . '
				  WHERE project_id IN ( ' . $t_project_list . ' ) AND version=' . db_param();
	db_query( $t_query, array( $p_new_version, $t_old_version ) );

	db_param_push();
	$t_query = 'UPDATE {bug} SET fixed_in_version=' . db_param() . '
				  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( fixed_in_version=' . db_param() . ')';
	db_query( $t_query, array( $p_new_version, $t_old_version ) );

	db_param_push();
	$t_query = 'UPDATE {bug} SET target_version=' . db_param() . '
				  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( target_version=' . db_param() . ')';
	db_query( $t_query, array( $p_new_version, $t_old_version ) );
}

/**
 * Remove all versions associated with a project
 * @param integer $p_project_id A project identifier.
 * @return boolean
 */
function version_remove_all( $p_project_id ) {
	$c_project_id = (int)$p_project_id;

	# remove all references to versions from version, fixed in version and target version.
	db_param_push();
	$t_query = 'UPDATE {bug}
				  SET version=\'\', fixed_in_version=\'\', target_version=\'\'
				  WHERE project_id=' . db_param();
	db_query( $t_query, array( $c_project_id ) );

	# remove the actual versions associated with the project.
	db_param_push();
	$t_query = 'DELETE FROM {project_version} WHERE project_id=' . db_param();
	db_query( $t_query, array( $c_project_id ) );

	return true;
}

/**
 * Return all versions for the specified project or projects list
 * Returned versions are ordered by reverse 'date_order'
 * @param integer|array $p_project_ids  A valid project id, or array of ids
 * @param boolean $p_released   Whether to show only released, unreleased, or both.
 *                  For this parameter, use constants defined as:
 *                  VERSION_ALL (null): returns any
 *                  VERSION_FUTURE (false): returns only unreleased versions
 *                  VERSION_RELEASED (true): returns only released versions
 * @param boolean $p_obsolete   Whether to include obsolete versions.
 * @param boolean $p_inherit    True to include versions from parent projects,
 *                              false not to, or null to use configuration
 *                              setting ($g_subprojects_inherit_versions).
 * @return array Array of version rows (in array format)
 */
function version_get_all_rows( $p_project_ids, $p_released = null, $p_obsolete = false, $p_inherit = null ) {
	global $g_cache_versions, $g_cache_versions_project;
	if( $p_inherit === null && ON == config_get( 'subprojects_inherit_versions', null, ALL_USERS, ALL_PROJECTS ) ) {
		$t_inherit = true;
	} else {
		$t_inherit = (bool)$p_inherit;
	}
	$t_project_ids = is_array( $p_project_ids ) ? $p_project_ids : array( $p_project_ids );

	if( $t_inherit ) {
		# add all parents for the requested projects
		$t_project_list = array();
		foreach( $t_project_ids as $t_id ) {
			if( in_array( $t_id, $t_project_list ) ) {
				# if it's already in the list, it appeared as parent of other project,
				# thus its own parents were added too.
				continue;
			}
			$t_project_list = array_merge( $t_project_list, project_hierarchy_inheritance( $t_id ) );
		}
		$t_project_list = array_unique( $t_project_list );
	} else {
		$t_project_list = $t_project_ids;
	}

	version_cache_array_rows( $t_project_list );
	$t_versions = array();
	foreach( $t_project_list as $t_project_id ) {
		if( !empty( $g_cache_versions_project[$t_project_id]) ) {
			foreach( $g_cache_versions_project[$t_project_id] as $t_id ) {
				$t_version_row = version_cache_row( $t_id );
				if( $p_obsolete === false && (int)$t_version_row['obsolete'] == 1 ) {
					continue;
				}
				if( $p_released !== null ) {
					$c_ver_released = (int)$t_version_row['released'] == 1;
					if( $p_released && !$c_ver_released || !$p_released && $c_ver_released ) {
						continue;
					}
				}

				$t_versions[] = $t_version_row;
				$t_order[] = (int)$t_version_row['date_order'];
			}
		}
	}
	if( !empty( $t_versions ) ) {
		# @TODO this function should not be responsible for sorting, let the
		# caller sort as needed
		# Included for backward compatibilty with calls that expect this.
		array_multisort( $t_order, SORT_DESC, SORT_REGULAR, $t_versions );
	}
	return $t_versions;
}

/**
 * Get the version_id, given the project_id and $p_version_id
 * returns false if not found, otherwise returns the id.
 * @param string  $p_version    A version string to look up.
 * @param integer $p_project_id A valid project identifier.
 * @param boolean $p_inherit    True to include versions from parent projects,
 *                              false not to, or null to use configuration
 *                              setting ($g_subprojects_inherit_versions).
 * @return integer
 */
function version_get_id( $p_version, $p_project_id = null, $p_inherit = null ) {
	global $g_cache_versions;

	if( $p_project_id === null ) {
		$c_project_id = helper_get_current_project();
	} else {
		$c_project_id = (int)$p_project_id;
	}

	$t_versions = version_get_all_rows( $c_project_id, VERSION_ALL, true /* incl. obsolete */ ,  $p_inherit );
	foreach( $t_versions as $t_version ) {
		if( $t_version['version'] === $p_version ) {
			return $t_version['id'];
		}
	}
	return false;
}

/**
 * Get the specified field name for the specified version id.
 * triggers an error if version not found, otherwise returns the field value.
 * @param integer $p_version_id A valid version identifier.
 * @param string  $p_field_name A valid field name to lookup.
 * @return string
 */
function version_get_field( $p_version_id, $p_field_name ) {
	$t_row = version_cache_row( $p_version_id );

	if( isset( $t_row[$p_field_name] ) ) {
		switch( $p_field_name ) {
			case 'project_id':
				return (int)$t_row[$p_field_name];
			default:
				return $t_row[$p_field_name];
		}
	} else {
		throw new ClientException(
			"Field '$p_field_name' not found",
			ERROR_DB_FIELD_NOT_FOUND,
			array( $p_field_name ) );
	}
}

/**
 * Gets the full name of a version.  This may include the project name as a prefix (e.g. '[MantisBT] 2.0.0')
 *
 * @param integer $p_version_id         The version id.
 * @param boolean $p_show_project       Whether to include the project name or not,
 *                                      null means include the project if different from current.
 * @param integer $p_current_project_id The current project id or null to use the cookie.
 * @return string The full name of the version.
 */
function version_full_name( $p_version_id, $p_show_project = null, $p_current_project_id = null ) {
	if( 0 == $p_version_id ) {
		# No Version
		return '';
	} else {
		$t_row = version_cache_row( $p_version_id );
		$t_project_id = $t_row['project_id'];

		$t_current_project_id = is_null( $p_current_project_id ) ? helper_get_current_project() : $p_current_project_id;

		if( $p_show_project === null ) {
			$t_show_project = $t_project_id != $t_current_project_id;
		} else {
			$t_show_project = $p_show_project;
		}

		if( $t_show_project ) {
			return '[' . project_get_name( $t_project_id ) . '] ' . $t_row['version'];
		}

		return $t_row['version'];
	}
}

/**
 * get information about a version given its id
 * @param integer $p_version_id A valid version identifier.
 * @return VersionData
 */
function version_get( $p_version_id ) {
	$t_row = version_cache_row( $p_version_id );

	return new VersionData( $t_row );
}

/**
 * Checks whether the product version should be shown
 * (i.e. report, update, view, print).
 * @param integer|array $p_project_ids  A valid project id or array of ids
 * @return boolean true: show, false: otherwise.
 */
function version_should_show_product_version( $p_project_ids ) {
	$t_project_ids = is_array( $p_project_ids ) ? $p_project_ids : array( $p_project_ids );

	$t_check_projects = array();
	foreach( $t_project_ids as $t_id ) {
		$t_option = config_get( 'show_product_version', null, null, $t_id );
		if( ON == $t_option ) {
			# if at least one of the projects have the option enabled is enough
			# condition to return true
			return true;
		}
		if( AUTO == $t_option ) {
			# if option is AUTO, save this project for later check it there are
			# any actual versions.
			$t_check_projects[] = $t_id;
		}
		# if option is not ON or AUTO, ignore this project
	}

	if( !empty( $t_check_projects ) ) {
		return count( version_get_all_rows( $t_check_projects ) ) > 0;
	}

	return false;
}
