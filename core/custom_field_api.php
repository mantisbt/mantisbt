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
 * Custom Field API
 *
 * @package CoreAPI
 * @subpackage CustomFieldAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

$g_custom_field_types[CUSTOM_FIELD_TYPE_STRING] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_TEXTAREA] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_NUMERIC] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_FLOAT] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_ENUM] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_EMAIL] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_CHECKBOX] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_LIST] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_MULTILIST] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_DATE] = 'standard';

foreach( $g_custom_field_types as $t_type ) {
	require_once( config_get_global( 'core_path' ) . 'cfdefs/cfdef_' . $t_type . '.php' );
}
unset( $t_type );

/**
 * Return true whether to display custom field
 * @param integer $p_type    Custom field type.
 * @param string  $p_display When to display.
 * @return boolean
 */
function custom_field_allow_manage_display( $p_type, $p_display ) {
	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_type]['#display_' . $p_display] ) ) {
		return $g_custom_field_type_definition[$p_type]['#display_' . $p_display];
	}
	return false;
}

$g_cache_custom_field = array();
$g_cache_cf_list = null;
$g_cache_cf_linked = array();
$g_cache_name_to_id_map = array();

# Values are indexed by [ bug_id, field_id ]
# a non existant value will have a cached value of null
$g_cache_cf_bug_values = array();

/**
 * Cache a custom field row if necessary and return the cached copy
 * If the second parameter is true (default), trigger an error
 * if the field can't be found.  If the second parameter is
 * false, return false if the field can't be found.
 * @param integer $p_field_id       Integer representing custom field id.
 * @param boolean $p_trigger_errors Indicates whether to trigger an error if the field is not found.
 * @return array array representing custom field
 * @access public
 */
function custom_field_cache_row( $p_field_id, $p_trigger_errors = true ) {
	global $g_cache_custom_field;

	$c_field_id = (int)$p_field_id;
	if( !isset( $g_cache_custom_field[$c_field_id] ) ) {
		custom_field_cache_array_rows( array( $c_field_id ) );
	}

	# the cached index exist, even when not found
	$t_cf_row = $g_cache_custom_field[$c_field_id];
	if( !$t_cf_row ) {
		if( $p_trigger_errors ) {
			error_parameters( 'Custom ' . $p_field_id );
			trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}
	return $t_cf_row;
}

/**
 * Cache custom fields contained within an array of field id's
 * @param array $p_cf_id_array Array of custom field id's.
 * @return void
 * @access public
 */
function custom_field_cache_array_rows( array $p_cf_id_array ) {
	global $g_cache_custom_field, $g_cache_name_to_id_map;
	$c_cf_id_array = array();

	foreach( $p_cf_id_array as $t_cf_id ) {
		$c_id = (int)$t_cf_id;
		if( !isset( $g_cache_custom_field[$c_id] ) ) {
			$c_cf_id_array[$c_id] = $c_id;
		}
	}

	if( empty( $c_cf_id_array ) ) {
		return;
	}

	db_param_push();
	$t_params = array();
	$t_in_caluse_dbparams = array();
	foreach( $c_cf_id_array as $t_id) {
		$t_in_caluse_dbparams[] = db_param();
		$t_params[] = $t_id;
	}
	$t_query = 'SELECT * FROM {custom_field} WHERE id IN (' . implode( ',', $t_in_caluse_dbparams ) . ')';
	$t_result = db_query( $t_query, $t_params );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_id = (int)$t_row['id'];
		$g_cache_custom_field[$c_id] = $t_row;
		$g_cache_name_to_id_map[$t_row['name']] = $c_id;
		unset( $c_cf_id_array[$c_id] );
	}
	# set the remaining ids as not found
	foreach( $c_cf_id_array as $t_id) {
		$g_cache_custom_field[$t_id] = false;
	}
	return;
}

/**
 * Load in cache values of custom fields, for given bugs and field ids.
 * When a value for a given bug and field does not exist, fill the cached value as null
 * @global array $g_cache_cf_bug_values
 * @param array $p_bug_id_array
 * @param array $p_field_id_array
 * @return void
 */
function custom_field_cache_values( array $p_bug_id_array, array $p_field_id_array ) {
	global $g_cache_cf_bug_values;

	if( empty( $p_field_id_array ) ) {
		return;
	}

	# clean fields ids
	$t_fields_to_search = array();
	$f_cf_defs = array();
	foreach( $p_field_id_array as $t_field_id ) {
		$c_field_id = (int)$t_field_id;
		$t_fields_to_search[$c_field_id] = $c_field_id;
		$f_cf_defs[$c_field_id] = custom_field_get_definition( $c_field_id );
	}

	# get bugs to fetch
	$t_bugs_to_search = array();
	foreach( $p_bug_id_array as $t_bug_id ) {
		$c_bug_id = (int)$t_bug_id;
		if( !isset( $g_cache_cf_bug_values[$c_bug_id] ) ) {
			$t_bugs_to_search[] = $c_bug_id;
		} else {
			$t_diff = array_diff( $t_fields_to_search, array_keys( $g_cache_cf_bug_values[$c_bug_id] ) );
			if( !empty( $t_diff ) ) {
				$t_bugs_to_search[] = $c_bug_id;
			}
		}
	}
	if( empty( $t_bugs_to_search ) ) {
		return;
	}

	db_param_push();
	$t_params= array();
	$t_query = 'SELECT B.id AS bug_id, CF.id AS field_id, CFS.value, CFS.text FROM {bug} B'
			. ' LEFT OUTER JOIN {custom_field} CF ON 1 = 1'
			. ' LEFT OUTER JOIN {custom_field_string} CFS ON ( B.id = CFS.bug_id AND CF.id = CFS.field_id )';
	$t_bug_in_params = array();
	foreach( $t_bugs_to_search as $t_bug_id ) {
		$t_bug_in_params[] = db_param();
		$t_params[] = $t_bug_id;
	}
	$t_query .= ' WHERE B.id IN (' . implode( ',', $t_bug_in_params ) . ')';
	$t_field_in_params = array();
	foreach( $t_fields_to_search as $t_field_id ) {
		$t_field_in_params[] = db_param();
		$t_params[] = $t_field_id;
	}
	$t_query .= ' AND CF.id IN (' . implode( ',', $t_field_in_params ) . ')';

	$t_result = db_query( $t_query, $t_params );

	# By having the left outer joins, non existant values are fetched as nulls,
	# and can be stored in cache to mark them as not-found
	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_bug_id = (int)$t_row['bug_id'];
		# create a bug index if necessary
		if( !isset( $g_cache_cf_bug_values[$c_bug_id] ) ) {
			$g_cache_cf_bug_values[$c_bug_id] = array();
		}
		$c_field_id = (int)$t_row['field_id'];
		$t_value_column = ( $f_cf_defs[$c_field_id]['type'] == CUSTOM_FIELD_TYPE_TEXTAREA ? 'text' : 'value' );
		$t_value = $t_row[$t_value_column];
		if( null !== $t_value ) {
			$t_value = custom_field_database_to_value( $t_value, $f_cf_defs[$c_field_id]['type'] );
		}
		# non-existant will be stored as null
		$g_cache_cf_bug_values[$c_bug_id][$c_field_id] = $t_value;
	}
}

/**
 * Clear the custom field values cache (or just the given bug id if specified)
 * @global array $g_cache_cf_bug_values
 * @param integer $p_bug_id	Bug id
 * @return void
 */
function custom_field_clear_cache_values( $p_bug_id = null ) {
	global $g_cache_cf_bug_values;

	if( null === $p_bug_id ) {
		$g_cache_cf_bug_values = array();
	} else {
		if( isset( $g_cache_cf_bug_values[(int)$p_bug_id] ) ) {
			unset( $g_cache_cf_bug_values[(int)$p_bug_id] );
		}
	}
}

/**
 * Clear the custom field cache (or just the given id if specified)
 * @param integer $p_field_id Custom field id.
 * @return void
 * @access public
 */
function custom_field_clear_cache( $p_field_id = null ) {
	global $g_cache_custom_field, $g_cached_custom_field_lists;

	$g_cached_custom_field_lists = null;

	if( null === $p_field_id ) {
		$g_cache_custom_field = array();
	} else {
		if( isset( $g_cache_custom_field[$p_field_id] ) ) {
			unset( $g_cache_custom_field[$p_field_id] );
		}
	}
}

/**
 * Check to see whether the field is included in the given project
 * return true if the field is included, false otherwise
 * @param integer $p_field_id   Custom field id.
 * @param integer $p_project_id Project id.
 * @return boolean
 * @access public
 */
function custom_field_is_linked( $p_field_id, $p_project_id ) {
	global $g_cache_cf_linked;

	if( isset( $g_cache_cf_linked[$p_project_id] ) ) {
		if( in_array( $p_field_id, $g_cache_cf_linked[$p_project_id] ) ) {
			return true;
		}
		return false;
	}

	# figure out if this bug_id/field_id combination exists
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {custom_field_project}
				WHERE field_id=' . db_param() . ' AND project_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_field_id, $p_project_id ) );
	$t_count = db_result( $t_result );

	if( $t_count > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check to see whether the field id is defined
 * return true if the field is defined, false otherwise
 * @param integer $p_field_id Custom field id.
 * @return boolean
 * @access public
 */
function custom_field_exists( $p_field_id ) {
	if( false == custom_field_cache_row( $p_field_id, false ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Return the type of a custom field if it exists.
 * @param integer $p_field_id Custom field id.
 * @return integer custom field type
 * @access public
 */
function custom_field_type( $p_field_id ) {
	$t_field = custom_field_cache_row( $p_field_id, false );
	if( $t_field == false ) {
		return - 1;
	} else {
		return $t_field['type'];
	}
}

/**
 * Check to see whether the field id is defined
 * return true if the field is defined, error otherwise
 * @param integer $p_field_id Custom field id.
 * @return boolean
 * @access public
 */
function custom_field_ensure_exists( $p_field_id ) {
	if( custom_field_exists( $p_field_id ) ) {
		return true;
	} else {
		error_parameters( 'Custom ' . $p_field_id );
		trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
	}
}

/**
 * Check to see whether the name is unique
 * return false if a field with the name already exists, true otherwise
 * if an id is specified, then the corresponding record is excluded from the
 * uniqueness test.
 * @param string  $p_name            Custom field name.
 * @param integer $p_custom_field_id Custom field identifier.
 * @return boolean
 * @access public
 */
function custom_field_is_name_unique( $p_name, $p_custom_field_id = null ) {
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {custom_field} WHERE name=' . db_param();
	if( $p_custom_field_id !== null ) {
		$t_query .= ' AND (id <> ' . db_param() . ')';
	}
	$t_result = db_query( $t_query, ( ($p_custom_field_id !== null) ? array( $p_name, $p_custom_field_id ) : array( $p_name ) ) );
	$t_count = db_result( $t_result );

	if( $t_count > 0 ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check to see whether the name is unique
 * return true if the name has not been used, error otherwise
 * @param string $p_name Custom field name.
 * @return boolean
 * @access public
 */
function custom_field_ensure_name_unique( $p_name ) {
	if( custom_field_is_name_unique( $p_name ) ) {
		return true;
	} else {
		trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
	}
}

/**
 * Return true if the user can read the value of the field for the given bug,
 * false otherwise.
 * @param integer $p_field_id Custom field identifier.
 * @param integer $p_bug_id   A bug identifier.
 * @param integer $p_user_id  User id.
 * @return boolean
 * @access public
 */
function custom_field_has_read_access( $p_field_id, $p_bug_id, $p_user_id = null ) {
	custom_field_ensure_exists( $p_field_id );

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_access_level_r = custom_field_get_field( $p_field_id, 'access_level_r' );

	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

	return access_has_project_level( $t_access_level_r, $t_project_id, $p_user_id );
}

/**
 * Return true if the user can read the value of the field for the given project,
 * false otherwise.
 * @param integer $p_field_id   Custom field identifier.
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user identifier.
 * @return boolean
 * @access public
 */
function custom_field_has_read_access_by_project_id( $p_field_id, $p_project_id, $p_user_id = null ) {
	custom_field_ensure_exists( $p_field_id );

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_access_level_r = custom_field_get_field( $p_field_id, 'access_level_r' );

	return access_has_project_level( $t_access_level_r, $p_project_id, $p_user_id );
}

/**
 * Return true if the user can modify the value of the field for the given project,
 * false otherwise.
 * @param integer $p_field_id   Custom field identifier.
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user identifier.
 * @return boolean
 * @access public
 */
function custom_field_has_write_access_to_project( $p_field_id, $p_project_id, $p_user_id = null ) {
	custom_field_ensure_exists( $p_field_id );

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_access_level_rw = custom_field_get_field( $p_field_id, 'access_level_rw' );

	return access_has_project_level( $t_access_level_rw, $p_project_id, $p_user_id );
}

/**
 * Return true if the user can modify the value of the field for the given bug,
 * false otherwise.
 * @param integer $p_field_id Custom field identifier.
 * @param integer $p_bug_id   A bug identifier.
 * @param integer $p_user_id  A user identifier.
 * @return boolean
 * @access public
 */
function custom_field_has_write_access( $p_field_id, $p_bug_id, $p_user_id = null ) {
	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	return( custom_field_has_write_access_to_project( $p_field_id, $t_project_id, $p_user_id ) );
}

/**
 * create a new custom field with the name $p_name
 * the definition are the default values and can be changes later
 * return the ID of the new definition
 * @param string $p_name Custom field name.
 * @return integer custom field id
 * @access public
 */
function custom_field_create( $p_name ) {
	$c_name = trim( $p_name );

	if( is_blank( $c_name ) ) {
		error_parameters( 'name' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	custom_field_ensure_name_unique( $c_name );

	db_param_push();
	$t_query = 'INSERT INTO {custom_field} ( name, possible_values )
				  VALUES ( ' . db_param() . ',' . db_param() . ')';
	db_query( $t_query, array( $c_name, '' ) );

	return db_insert_id( db_get_table( 'custom_field' ) );
}

/**
 * Update the field definition
 * return true on success, false on failure
 * @param integer $p_field_id  Custom field identifier.
 * @param array   $p_def_array Custom field definition.
 * @return boolean
 * @access public
 */
function custom_field_update( $p_field_id, array $p_def_array ) {
	if( is_blank( $p_def_array['name'] ) ) {
		error_parameters( 'name' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if( $p_def_array['access_level_rw'] < $p_def_array['access_level_r'] ) {
		error_parameters(
			lang_get( 'custom_field_access_level_r' ) . ', ' .
			lang_get( 'custom_field_access_level_rw' ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_PROPERTY, ERROR );
	}

	if( $p_def_array['length_min'] < 0
		|| ( $p_def_array['length_max'] != 0 && $p_def_array['length_min'] > $p_def_array['length_max'] )
	) {
		error_parameters( lang_get( 'custom_field_length_min' ) . ', ' . lang_get( 'custom_field_length_max' ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_PROPERTY, ERROR );
	}

	if( !custom_field_is_name_unique( $p_def_array['name'], $p_field_id ) ) {
		trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
	}

	db_param_push();

	# Build fields update statement
	$t_update = '';
	foreach( $p_def_array as $t_field => $t_value ) {
		switch( $t_field ) {
			case 'name':
			case 'possible_values':
			case 'default_value':
			case 'valid_regexp':
				# Possible values doesn't apply to textarea fields
				if( $p_def_array['type'] == CUSTOM_FIELD_TYPE_TEXTAREA && $t_field == 'possible_values' ) {
					$t_value = '';
				}

				$t_update .= $t_field . '=' . db_param() . ', ';
				$t_params[] = (string)$t_value;
				break;
			case 'type':
			case 'access_level_r':
			case 'access_level_rw':
			case 'length_min':
			case 'length_max':
				$t_update .= $t_field  . '=' . db_param() . ', ';
				$t_params[] = (int)$t_value;
				break;
			case 'filter_by':
			case 'display_report':
			case 'display_update':
			case 'display_resolved':
			case 'display_closed':
			case 'require_report':
			case 'require_update':
			case 'require_resolved':
			case 'require_closed':
				$t_update .= $t_field . '=' . db_param() . ', ';
				$t_params[] = (bool)$t_value;
				break;
		}
	}

	# If there are fields to update, execute SQL
	if( $t_update !== '' ) {
		$t_query = 'UPDATE {custom_field} SET ' . rtrim( $t_update, ', ' ) . ' WHERE id = ' . db_param();
		$t_params[] = $p_field_id;
		db_query( $t_query, $t_params );

		custom_field_clear_cache( $p_field_id );

		return true;
	}

	# Reset the parameter count manually since the query was not executed
	db_param_pop();

	return false;
}

/**
 * Add a custom field to a project
 * return true on success, false on failure or if already added
 * @param integer $p_field_id   Custom field identifier.
 * @param integer $p_project_id Project identifier.
 * @return boolean
 * @access public
 */
function custom_field_link( $p_field_id, $p_project_id ) {
	custom_field_ensure_exists( $p_field_id );
	project_ensure_exists( $p_project_id );

	if( custom_field_is_linked( $p_field_id, $p_project_id ) ) {
		return false;
	}

	db_param_push();
	$t_query = 'INSERT INTO {custom_field_project} ( field_id, project_id )
				  VALUES ( ' . db_param() . ', ' . db_param() . ')';
	db_query( $t_query, array( $p_field_id, $p_project_id ) );

	return true;
}

/**
 * Remove a custom field from a project
 * return true on success, false on failure
 *
 * The values for the custom fields are not deleted.  This is to allow for the
 * case where a bug is moved to another project that has the field, or the
 * field is linked again to the project.
 * @param integer $p_field_id   Custom field identifier.
 * @param integer $p_project_id Project identifier.
 * @return void
 * @access public
 */
function custom_field_unlink( $p_field_id, $p_project_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {custom_field_project}
				  WHERE field_id = ' . db_param() . ' AND project_id = ' . db_param();
	db_query( $t_query, array( $p_field_id, $p_project_id ) );
}

/**
 * Delete the field definition and all associated values and project associations
 * return true on success, false on failure
 * @param integer $p_field_id Custom field identifier.
 * @return void
 * @access public
 */
function custom_field_destroy( $p_field_id ) {
	# delete all values
	db_param_push();
	$t_query = 'DELETE FROM {custom_field_string} WHERE field_id=' . db_param();
	db_query( $t_query, array( $p_field_id ) );

	# delete all project associations
	db_param_push();
	$t_query = 'DELETE FROM {custom_field_project} WHERE field_id=' . db_param();
	db_query( $t_query, array( $p_field_id ) );

	# delete the definition
	db_param_push();
	$t_query = 'DELETE FROM {custom_field} WHERE id=' .  db_param();
	db_query( $t_query, array( $p_field_id ) );

	custom_field_clear_cache( $p_field_id );
	custom_field_clear_cache_values();
}

/**
 * Delete all associations of custom fields to the specified project
 * return true on success, false on failure
 *
 * To be called from within project_delete().
 * @param integer $p_project_id A project identifier.
 * @return void
 * @access public
 */
function custom_field_unlink_all( $p_project_id ) {
	# delete all project associations
	db_param_push();
	$t_query = 'DELETE FROM {custom_field_project} WHERE project_id=' . db_param();
	db_query( $t_query, array( $p_project_id ) );
}

/**
 * Delete all custom values associated with the specified bug.
 *
 * To be called from bug_delete().
 * @param integer $p_bug_id A bug identifier.
 * @return void
 * @access public
 */
function custom_field_delete_all_values( $p_bug_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {custom_field_string} WHERE bug_id=' . db_param();
	db_query( $t_query, array( $p_bug_id ) );
	custom_field_clear_cache_values( $p_bug_id );
}

/**
 * Get the id of the custom field with the specified name.
 * false is returned if no custom field found with the specified name.
 * @param string $p_field_name Custom field name.
 * @return boolean|integer false or custom field id
 * @access public
 */
function custom_field_get_id_from_name( $p_field_name ) {
	global $g_cache_name_to_id_map;

	if( is_blank( $p_field_name ) ) {
		return false;
	}

	if( isset( $g_cache_name_to_id_map[$p_field_name] ) ) {
		return $g_cache_name_to_id_map[$p_field_name];
	}

	db_param_push();
	$t_query = 'SELECT id FROM {custom_field} WHERE name=' . db_param();
	$t_result = db_query( $t_query, array( $p_field_name ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		$g_cache_name_to_id_map[$p_field_name] = false;
		return false;
	}

	$g_cache_name_to_id_map[$p_field_name] = $t_row['id'];

	return $t_row['id'];
}

/**
 * Return an array of ids of custom fields bound to the specified project
 *
 * The ids will be sorted based on the sequence number associated with the binding
 * @param integer $p_project_id A project identifier.
 * @return array
 * @access public
 */
function custom_field_get_linked_ids( $p_project_id = ALL_PROJECTS ) {
	global $g_cache_cf_linked;

	if( !isset( $g_cache_cf_linked[$p_project_id] ) ) {
		db_param_push();

		if( ALL_PROJECTS == $p_project_id ) {
			$t_user_id = auth_get_current_user_id();

			# Select only the ids of custom fields in projects the user has access to
			#  - all custom fields in public projects,
			#  - those in private projects where the user is listed
			#  - in private projects where the user is implicitly listed
			$t_query = 'SELECT DISTINCT cft.id
				FROM {custom_field} cft
					JOIN {custom_field_project} cfpt ON cfpt.field_id = cft.id
					JOIN {project} pt
						ON pt.id = cfpt.project_id AND pt.enabled = ' . db_prepare_bool( true ) . '
					LEFT JOIN {project_user_list} pult
						ON pult.project_id = cfpt.project_id AND pult.user_id = ' . db_param() . '
					, {user} ut
				WHERE ut.id = ' . db_param() . '
					AND (  pt.view_state = ' . VS_PUBLIC . '
						OR pult.user_id = ut.id
						';
			$t_params = array( $t_user_id, $t_user_id );

			# Add private access clause and related parameter
			$t_private_access = config_get( 'private_project_threshold' );
			if( is_array( $t_private_access ) ) {
				if( 1 == count( $t_private_access ) ) {
					$t_access_clause = '= ' . db_param();
					$t_params[] = array_shift( $t_private_access );
				} else {
					$t_access_clause = 'IN (';
					foreach( $t_private_access as $t_elem ) {
						$t_access_clause .= db_param() . ',';
						$t_params[] = $t_elem;
					}
					$t_access_clause = rtrim( $t_access_clause, ',' ) . ')';
				}
			} else {
				$t_access_clause = '>=' . db_param();
				$t_params[] = $t_private_access;
			}
			$t_query .= 'OR ( pult.user_id IS NULL AND ut.access_level ' . $t_access_clause . ' ) )';
		} else {
			if( is_array( $p_project_id ) ) {
				if( 1 == count( $p_project_id ) ) {
					$t_project_clause = '= ' . db_param();
					$t_params[] = array_shift( $p_project_id );
				} else {
					$t_project_clause = 'IN (';
					foreach( $p_project_id as $t_project ) {
						$t_project_clause .= db_param() . ',';
						$t_params[] = $t_project;
					}
					$t_project_clause = rtrim( $t_project_clause, ',' ) . ')';
				}
			} else {
				$t_project_clause = '= ' . db_param();
				$t_params[] = $p_project_id;
			}
			$t_query = 'SELECT cft.id
				FROM {custom_field} cft
					JOIN {custom_field_project} cfpt ON cfpt.field_id = cft.id
				WHERE cfpt.project_id ' . $t_project_clause . '
				ORDER BY sequence ASC, name ASC';
		}

		$t_result = db_query( $t_query, $t_params );
		$t_ids = array();

		while( $t_row = db_fetch_array( $t_result ) ) {
			array_push( $t_ids, $t_row['id'] );
		}
		custom_field_cache_array_rows( $t_ids );

		$g_cache_cf_linked[$p_project_id] = $t_ids;
	} else {
		$t_ids = $g_cache_cf_linked[$p_project_id];
	}
	return $t_ids;
}

/**
 * Return an array all custom field ids sorted by name
 * @return array
 * @access public
 */
function custom_field_get_ids() {
	global $g_cache_cf_list, $g_cache_custom_field;

	if( $g_cache_cf_list === null ) {
		$t_query = 'SELECT * FROM {custom_field} ORDER BY name ASC';
		$t_result = db_query( $t_query );
		$t_ids = array();

		while( $t_row = db_fetch_array( $t_result ) ) {
			$g_cache_custom_field[(int)$t_row['id']] = $t_row;

			array_push( $t_ids, $t_row['id'] );
		}
		$g_cache_cf_list = $t_ids;
	} else {
		$t_ids = $g_cache_cf_list;
	}
	return $t_ids;
}

/**
 * Return an array of ids of projects related to the specified custom field
 * (the array may be empty)
 * @param integer $p_field_id Custom field identifier.
 * @return array
 * @access public
 */
function custom_field_get_project_ids( $p_field_id ) {
	db_param_push();
	$t_query = 'SELECT project_id FROM {custom_field_project} WHERE field_id = ' . db_param();
	$t_result = db_query( $t_query, array( $p_field_id ) );

	$t_ids = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		array_push( $t_ids, $t_row['project_id'] );
	}

	return $t_ids;
}

/**
 * Return a field definition row for the field or error if the field does not exist
 * @param integer $p_field_id Custom field identifier.
 * @return array custom field definition
 * @access public
 */
function custom_field_get_definition( $p_field_id ) {
	return custom_field_cache_row( $p_field_id );
}

/**
 * Return a single database field from a custom field definition row for the field
 * if the database field does not exist, display a warning and return ''
 * @param integer $p_field_id   Custom field identifier.
 * @param integer $p_field_name Custom field name.
 * @return string
 * @access public
 */
function custom_field_get_field( $p_field_id, $p_field_name ) {
	$t_row = custom_field_get_definition( $p_field_id );

	if( isset( $t_row[$p_field_name] ) ) {
		return $t_row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * Return custom field name including localized name (if available)
 *
 * @param string $p_name Custom field's name.
 * @return string CustomFieldName [(LocalizedName)]
 * @access public
 */
function custom_field_get_display_name( $p_name ) {
	$t_local_name = lang_get_defaulted( $p_name );
	if( $t_local_name != $p_name ) {
		$p_name .= ' (' . $t_local_name . ')';
	}

	return string_display( $p_name );
}

/**
 * Get the value of a custom field for the given bug
 * @todo return values are unclear... should we error when access is denied
 * and provide an api to check whether it will be?
 * @param integer $p_field_id Custom field id.
 * @param integer $p_bug_id   A bug identifier.
 * @return mixed: value is defined, null: no value is defined, false: read access is denied
 * @access public
 */
function custom_field_get_value( $p_field_id, $p_bug_id ) {
	global $g_cache_cf_bug_values;
	$c_bug_id = (int)$p_bug_id;
	$c_field_id = (int)$p_field_id;

	$t_row = custom_field_cache_row( $c_field_id );
	$t_access_level_r = $t_row['access_level_r'];

	# first check permissions
	if( !custom_field_has_read_access( $c_field_id, $c_bug_id, auth_get_current_user_id() ) ) {
		return false;
	}

	# A null value means a cached non existant value. It must be checked with care.
	if( !isset( $g_cache_cf_bug_values[$c_bug_id] )
			|| !array_key_exists( $c_field_id, $g_cache_cf_bug_values[$c_bug_id] ) ) {
		custom_field_cache_values( array( $c_bug_id ), array( $c_field_id ) );
	}

	return $g_cache_cf_bug_values[$c_bug_id][$c_field_id];
}

/**
 * Gets the custom fields array for the given bug readable by specified level.
 * Array keys are custom field names. Array is sorted by custom field sequence number;
 * Array items are arrays with the next keys:
 * 'type', 'value', 'access_level_r'
 * @param integer $p_bug_id            A bug identifier.
 * @param integer $p_user_access_level Access level.
 * @return array
 * @access public
 */
function custom_field_get_linked_fields( $p_bug_id, $p_user_access_level ) {
	$t_custom_fields = custom_field_get_all_linked_fields( $p_bug_id );

	# removing restricted fields
	foreach( $t_custom_fields as $t_custom_field_name => $t_custom_field_data ) {
		if( $p_user_access_level < $t_custom_field_data['access_level_r'] ) {
			unset( $t_custom_fields[$t_custom_field_name] );
		}
	}
	return $t_custom_fields;
}

/**
 * Gets the custom fields array for the given bug. Array keys are custom field names.
 * Array is sorted by custom field sequence number; Array items are arrays with the next keys:
 * 'type', 'value', 'access_level_r'
 * @param integer $p_bug_id A bug identifier.
 * @return array
 * @access public
 */
function custom_field_get_all_linked_fields( $p_bug_id ) {
	global $g_cached_custom_field_lists;

	if( !is_array( $g_cached_custom_field_lists ) ) {
		$g_cached_custom_field_lists = array();
	}

	# is the list in cache ?
	if( !array_key_exists( $p_bug_id, $g_cached_custom_field_lists ) ) {
		$c_project_id = (int)( bug_get_field( $p_bug_id, 'project_id' ) );

		db_param_push();
		$t_query = 'SELECT f.name, f.type, f.access_level_r, f.default_value, s.value
			FROM {custom_field_project} p
				INNER JOIN {custom_field} f ON f.id = p.field_id
				LEFT JOIN {custom_field_string} s
					ON s.field_id = p.field_id AND s.bug_id = ' . db_param() . '
			WHERE p.project_id = ' . db_param() . '
			ORDER BY p.sequence ASC, f.name ASC';
		$t_result = db_query( $t_query, array( $p_bug_id, $c_project_id) );

		$t_custom_fields = array();

		while( $t_row = db_fetch_array( $t_result ) ) {

			if( is_null( $t_row['value'] ) ) {
				$t_value = $t_row['default_value'];
			} else {
				$t_value = custom_field_database_to_value( $t_row['value'], $t_row['type'] );
			}

			$t_custom_fields[$t_row['name']] = array(
				'type' => $t_row['type'],
				'value' => $t_value,
				'access_level_r' => $t_row['access_level_r'],
			);
		}

		$g_cached_custom_field_lists[$p_bug_id] = $t_custom_fields;
	}

	return $g_cached_custom_field_lists[$p_bug_id];
}

/**
 * Gets the sequence number for the specified custom field for the specified
 * project.  Returns false in case of error.
 * @param integer $p_field_id   A custom field identifier.
 * @param integer $p_project_id A project identifier.
 * @return integer|boolean
 * @access public
 */
function custom_field_get_sequence( $p_field_id, $p_project_id ) {
	$p_field_id = (int)$p_field_id;
	$p_project_id = (int)$p_project_id;

	db_param_push();
	$t_query = 'SELECT sequence
				  FROM {custom_field_project}
				  WHERE field_id=' . db_param() . ' AND
						project_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_field_id, $p_project_id ), 1 );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		return false;
	}

	return $t_row['sequence'];
}

/**
 * Allows the validation of a custom field value without setting it
 * or needing a bug to exist.
 * @param integer $p_field_id Custom field identifier.
 * @param string  $p_value    Custom field value.
 * @return boolean
 * @access public
 */
function custom_field_validate( $p_field_id, $p_value ) {
	$t_row = custom_field_get_definition( $p_field_id );

	$t_name = $t_row['name'];
	$t_type = $t_row['type'];
	$t_possible_values = $t_row['possible_values'];
	$t_valid_regexp = $t_row['valid_regexp'];
	$t_length_min = $t_row['length_min'];
	$t_length_max = $t_row['length_max'];
	$t_default_value = $t_row['default_value'];

	$t_valid = true;
	$t_length = utf8_strlen( $p_value );
	switch( $t_type ) {
		case CUSTOM_FIELD_TYPE_STRING:
		case CUSTOM_FIELD_TYPE_TEXTAREA:
			# Empty fields are valid
			if( $t_length == 0 ) {
				break;
			}
			# Regular expression string validation
			if( !is_blank( $t_valid_regexp ) ) {
				$t_valid &= preg_match( '/' . $t_valid_regexp . '/', $p_value );
			}
			# Check the length of the string
			$t_valid &= ( 0 == $t_length_min ) || ( $t_length >= $t_length_min );
			$t_valid &= ( 0 == $t_length_max ) || ( $t_length <= $t_length_max );
			break;
		case CUSTOM_FIELD_TYPE_NUMERIC:
			# Empty fields are valid
			if( $t_length == 0 ) {
				break;
			}
			$t_valid &= is_numeric( $p_value );

			# Check the length of the number
			$t_valid &= ( 0 == $t_length_min ) || ( $t_length >= $t_length_min );
			$t_valid &= ( 0 == $t_length_max ) || ( $t_length <= $t_length_max );

			break;
		case CUSTOM_FIELD_TYPE_FLOAT:
			# Empty fields are valid
			if( $t_length == 0 ) {
				break;
			}
			# Allow both integer and float numbers
			$t_valid &= is_numeric( $p_value ) || is_float( $p_value );

			# Check the length of the number
			$t_valid &= ( 0 == $t_length_min ) || ( $t_length >= $t_length_min );
			$t_valid &= ( 0 == $t_length_max ) || ( $t_length <= $t_length_max );

			break;
		case CUSTOM_FIELD_TYPE_DATE:
			# gpc_get_cf for date returns the value from strtotime
			# For 32 bit systems, supported range will be 13 Dec 1901 20:45:54 UTC to 19 Jan 2038 03:14:07 UTC
			$t_valid &= $p_value !== false;
			break;
		case CUSTOM_FIELD_TYPE_CHECKBOX:
		case CUSTOM_FIELD_TYPE_MULTILIST:
			# Checkbox fields can hold a null value (when no checkboxes are ticked)
			if( $p_value === '' ) {
				break;
			}
			# If checkbox field value is not null then we need to validate it
			$t_values = explode( '|', $p_value );
			$t_possible_values = custom_field_prepare_possible_values( $t_row['possible_values'] );
			$t_possible_values = explode( '|', $t_possible_values );
			$t_invalid_values = array_diff( $t_values, $t_possible_values );
			$t_valid &= ( count( $t_invalid_values ) == 0 );
			break;
		case CUSTOM_FIELD_TYPE_ENUM:
		case CUSTOM_FIELD_TYPE_LIST:
		case CUSTOM_FIELD_TYPE_RADIO:
			# List fields can be empty (when they are not shown on the
			# form, or shown with no default values and never clicked)
			if( is_blank( $p_value ) ) {
				break;
			}

			# If list field value is not empty then we need to validate it
			$t_possible_values = custom_field_prepare_possible_values( $t_row['possible_values'] );
			$t_values_arr = explode( '|', $t_possible_values );
			$t_valid &= in_array( $p_value, $t_values_arr );
			break;
		case CUSTOM_FIELD_TYPE_EMAIL:
			if( $p_value !== '' ) {
				$t_valid &= email_is_valid( $p_value );
			}
			break;
		default:
			break;
	}
	return (bool)$t_valid;
}

/**
 * $p_possible_values: possible values to be pre-processed.  If it has enumeration values,
 * it will be left as is.  If it has a method, it will be replaced by the list.
 * @param string $p_possible_values Possible values for custom field.
 * @return string|array
 * @access public
 */
function custom_field_prepare_possible_values( $p_possible_values ) {
	if( !is_blank( $p_possible_values ) && ( $p_possible_values[0] == '=' ) ) {
		return helper_call_custom_function( 'enum_' . utf8_substr( $p_possible_values, 1 ), array() );
	}

	return $p_possible_values;
}

/**
 * Get All Possible Values for a Field.
 * @param array   $p_field_def  Custom field definition.
 * @param integer $p_project_id Project identifier.
 * @return boolean|array
 * @access public
 */
function custom_field_distinct_values( array $p_field_def, $p_project_id = ALL_PROJECTS ) {
	global $g_custom_field_type_definition;
	$t_return_arr = array();

	# If an enumeration type, we get all possible values, not just used values
	if( isset( $g_custom_field_type_definition[$p_field_def['type']]['#function_return_distinct_values'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_field_def['type']]['#function_return_distinct_values'], $p_field_def );
	} else {
		db_param_push();

		$t_from = '{custom_field_string} cfst';
		$t_where1 = 'cfst.field_id = ' . db_param();
		$t_params[] = $p_field_def['id'];

		if( ALL_PROJECTS != $p_project_id ) {
			$t_from .= ' JOIN {bug} bt ON bt.id = cfst.bug_id';
			$t_where2 = 'AND bt.project_id = ' . db_param();
			$t_params[] = $p_project_id;
		} else {
			$t_where2 = '';
		}
		$t_query = 'SELECT DISTINCT cfst.value
			FROM ' . $t_from . '
			WHERE ' . $t_where1 . $t_where2 . '
			ORDER BY cfst.value';
		$t_result = db_query( $t_query, $t_params );
		$t_row_count = 0;

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_row_count++;
			if( !is_blank( trim( $t_row['value'] ) ) ) {
				array_push( $t_return_arr, $t_row['value'] );
			}
		}

		if( 0 == $t_row_count ) {
			return false;
		}
	}
	return $t_return_arr;
}

/**
 * Convert the value to save it into the database, depending of the type
 * return value for database
 * @param boolean|integer|string $p_value Custom field value.
 * @param integer                $p_type  Custom field type.
 * @return boolean|integer|string
 * @access public
 */
function custom_field_value_to_database( $p_value, $p_type ) {
	global $g_custom_field_type_definition;

	$t_value = $p_value;

	if( isset( $g_custom_field_type_definition[$p_type]['#function_value_to_database'] ) ) {
		$t_value = call_user_func( $g_custom_field_type_definition[$p_type]['#function_value_to_database'], $p_value );
	}

	return $t_value === null ? '' : $t_value;
}

/**
 * Convert the database-value to value, depending of the type
 * return value for further operation
 * @param boolean|integer|string $p_value Custom field value.
 * @param integer                $p_type  Custom field type.
 * @return boolean|integer|string
 * @access public
 */
function custom_field_database_to_value( $p_value, $p_type ) {
	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_type]['#function_database_to_value'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_type]['#function_database_to_value'], $p_value );
	}
	return $p_value;
}

/**
 * Convert the default-value to value depending on the type.  For example, in case of date, this
 * would translate 'tomorrow' to tomorrow's date.
 * @param boolean|integer|string $p_value Custom field default value.
 * @param integer                $p_type  Custom field type.
 * @return boolean|integer|string
 * @access public
 */
function custom_field_default_to_value( $p_value, $p_type ) {
	global $g_custom_field_type_definition;

	if( isset( $g_custom_field_type_definition[$p_type]['#function_default_to_value'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_type]['#function_default_to_value'], $p_value );
	}

	return $p_value;
}

/**
 * Set the value of a custom field for a given bug
 * return true on success, false on failure
 * @param integer $p_field_id   Custom field identifier.
 * @param integer $p_bug_id     A bug identifier.
 * @param mixed   $p_value      New custom field value.
 * @param boolean $p_log_insert Create history logs for new values.
 * @return boolean
 * @access public
 */
function custom_field_set_value( $p_field_id, $p_bug_id, $p_value, $p_log_insert = true ) {
	custom_field_ensure_exists( $p_field_id );

	if( !custom_field_validate( $p_field_id, $p_value ) ) {
		return false;
	}

	$t_name = custom_field_get_field( $p_field_id, 'name' );
	$t_type = custom_field_get_field( $p_field_id, 'type' );

	$t_value_field = ( $t_type == CUSTOM_FIELD_TYPE_TEXTAREA ) ? 'text' : 'value';
	$t_value = custom_field_value_to_database( $p_value, $t_type );

	# Determine whether an existing value needs to be updated or a new value inserted
	db_param_push();
	$t_query = 'SELECT ' . $t_value_field . '
				  FROM {custom_field_string}
				  WHERE field_id=' . db_param() . ' AND
				  		bug_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_field_id, $p_bug_id ) );

	if( $t_row = db_fetch_array( $t_result ) ) {
		db_param_push();
		$t_query = 'UPDATE {custom_field_string}
					  SET ' . $t_value_field . '=' . db_param() . '
					  WHERE field_id=' . db_param() . ' AND
					  		bug_id=' . db_param();
		$t_params = array(
			$t_value,
			(int)$p_field_id,
			(int)$p_bug_id,
		);
		db_query( $t_query, $t_params );

		history_log_event_direct( $p_bug_id, $t_name, custom_field_database_to_value( $t_row[$t_value_field], $t_type ), $t_value );
	} else {
		db_param_push();
		$t_query = 'INSERT INTO {custom_field_string}
						( field_id, bug_id, ' . $t_value_field . ' )
					  VALUES
						( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
		$t_params = array(
			(int)$p_field_id,
			(int)$p_bug_id,
			$t_value,
		);
		db_query( $t_query, $t_params );
		# Don't log history events for new bug reports or on other special occasions
		if( $p_log_insert ) {
			history_log_event_direct( $p_bug_id, $t_name, '', $t_value );
		}
	}

	custom_field_clear_cache_values( $p_bug_id );

	# db_query() errors on failure so:
	return true;
}

/**
 * Sets the sequence number for the specified custom field for the specified
 * project.
 * @param integer $p_field_id   A custom field identifier.
 * @param integer $p_project_id A Project identifier.
 * @param integer $p_sequence   Sequence order.
 * @return boolean
 * @access public
 */
function custom_field_set_sequence( $p_field_id, $p_project_id, $p_sequence ) {
	db_param_push();
	$t_query = 'UPDATE {custom_field_project}
				  SET sequence=' . db_param() . '
				  WHERE field_id=' . db_param() . ' AND
				  		project_id=' . db_param();
	db_query( $t_query, array( $p_sequence, $p_field_id, $p_project_id ) );

	custom_field_clear_cache( $p_field_id );

	return true;
}

/**
 * Print an input field
 * $p_field_def contains the definition of the custom field (including it's field id
 * $p_bug_id    contains the bug where this field belongs to. If it's left
 * away, it'll default to 0 and thus belongs to a new (i.e. non-existant) bug
 * NOTE: This probably belongs in the print_api.php
 * @param array   $p_field_def Custom field definition.
 * @param integer $p_bug_id    A bug identifier.
 * @return void
 * @access public
 */
function print_custom_field_input( array $p_field_def, $p_bug_id = null ) {
	if( null === $p_bug_id ) {
		$t_custom_field_value = custom_field_default_to_value( $p_field_def['default_value'], $p_field_def['type'] );
	} else {
		$t_custom_field_value = custom_field_get_value( $p_field_def['id'], $p_bug_id );
		# If the custom field value is undefined and the field cannot hold a null value, use the default value instead
		if( $t_custom_field_value === null &&
			( $p_field_def['type'] == CUSTOM_FIELD_TYPE_ENUM ||
				$p_field_def['type'] == CUSTOM_FIELD_TYPE_LIST ||
				$p_field_def['type'] == CUSTOM_FIELD_TYPE_MULTILIST ||
				$p_field_def['type'] == CUSTOM_FIELD_TYPE_RADIO ) ) {
			$t_custom_field_value = custom_field_default_to_value( $p_field_def['default_value'], $p_field_def['type'] );
		}
	}

	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_field_def['type']]['#function_print_input'] ) ) {
		call_user_func( $g_custom_field_type_definition[$p_field_def['type']]['#function_print_input'], $p_field_def, $t_custom_field_value );
		print_hidden_input( custom_field_presence_field_name( $p_field_def['id'] ), '1' );
	} else {
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_DEFINITION, ERROR );
	}
}

/**
 * Constructs the name of a field used as a flag to indicate that a custom field is present on the form
 * @param integer $p_custom_field_id  The custom field id to create the field name for.
 * @return string The field name.
 */
function custom_field_presence_field_name( $p_custom_field_id ) {
	return 'custom_field_' . (int)$p_custom_field_id . '_presence';
}

/**
 * Checks the presence of a custom field on the form.
 * @param integer $p_custom_field_id  The custom field id to check.
 * @return bool true when field is on form, false otherwise.
 */
function custom_field_is_present( $p_custom_field_id ) {
	return gpc_isset( custom_field_presence_field_name( $p_custom_field_id ) );
}

/**
 * Prepare a string containing a custom field value for display
 * @param array   $p_def      Contains the definition of the custom field.
 * @param integer $p_field_id Contains the id of the field.
 * @param integer $p_bug_id   Contains the bug id to display the custom field value for.
 * @return string
 * @access public
 */
function string_custom_field_value( array $p_def, $p_field_id, $p_bug_id ) {
	$t_custom_field_value = custom_field_get_value( $p_field_id, $p_bug_id );
	if( $t_custom_field_value === null ) {
		return '';
	}
	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_def['type']]['#function_string_value'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_def['type']]['#function_string_value'], $t_custom_field_value );
	}
	return string_display_links( $t_custom_field_value );
}

/**
 * Print a custom field value for display
 * NOTE: This probably belongs in the print_api.php
 * @param array   $p_def      Contains the definition of the custom field.
 * @param integer $p_field_id Contains the id of the field.
 * @param integer $p_bug_id   Contains the bug id to display the custom field value for.
 * @return void
 * @access public
 */
function print_custom_field_value( array $p_def, $p_field_id, $p_bug_id ) {
	echo string_custom_field_value( $p_def, $p_field_id, $p_bug_id );
}

/**
 * Prepare a string containing a custom field value for email
 * NOTE: This probably belongs in the string_api.php
 * @param string  $p_value Value of custom field.
 * @param integer $p_type  Type of custom field.
 * @return string value ready for sending via email
 * @access public
 */
function string_custom_field_value_for_email( $p_value, $p_type ) {
	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_type]['#function_string_value_for_email'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_type]['#function_string_value_for_email'], $p_value );
	}
	return $p_value;
}
