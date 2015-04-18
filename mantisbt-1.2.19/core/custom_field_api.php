<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage CustomFieldAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires bug_api
 */
require_once( 'bug_api.php' );
/**
 * requires helper_api
 */
require_once( 'helper_api.php' );
/**
 * requires date_api
 */
require_once( 'date_api.php' );

# ## Custom Fields API ###
# *******************************************
#	TODO
#	- add an object to store field data like BugData and UserPrefs ?
#	- add caching functions like user, bug, etc
#	- make existing api functions use caching functions
#	- add functions to return individual db columns for a field definition
# *******************************************

$g_custom_field_types[CUSTOM_FIELD_TYPE_STRING] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_NUMERIC] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_FLOAT] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_ENUM] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_EMAIL] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_CHECKBOX] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_LIST] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_MULTILIST] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_DATE] = 'standard';

foreach( $g_custom_field_types as $type ) {
	require_once( 'cfdefs' . DIRECTORY_SEPARATOR . 'cfdef_' . $type . '.php' );
}
unset( $type );

function custom_field_allow_manage_display( $p_type, $p_display ) {
	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_type]['#display_' . $p_display] ) ) {
		return $g_custom_field_type_definition[$p_type]['#display_' . $p_display];
	}
	return false;
}


# ########################################
# SECURITY NOTE: cache globals are initialized here to prevent them
#   being spoofed if register_globals is turned on

$g_cache_custom_field = array();
$g_cache_cf_list = NULL;
$g_cache_cf_linked = array();
$g_cache_name_to_id_map = array();

/**
 * Cache a custom field row if necessary and return the cached copy
 * If the second parameter is true (default), trigger an error
 * if the field can't be found.  If the second parameter is
 * false, return false if the field can't be found.
 * @param int $p_field_id integer representing custom field id
 * @param bool $p_trigger_errors indicates whether to trigger an error if the field is not found
 * @return array array representing custom field
 * @access public
 */
function custom_field_cache_row( $p_field_id, $p_trigger_errors = true ) {
	global $g_cache_custom_field, $g_cache_name_to_id_map;

	$c_field_id = db_prepare_int( $p_field_id );

	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );

	if( isset( $g_cache_custom_field[$c_field_id] ) ) {
		return $g_cache_custom_field[$c_field_id];
	}

	$query = "SELECT *
				  FROM $t_custom_field_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_field_id ) );

	if( 0 == db_num_rows( $result ) ) {
		if( $p_trigger_errors ) {
			error_parameters( 'Custom ' . $p_field_id );
			trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$row = db_fetch_array( $result );

	$g_cache_custom_field[$c_field_id] = $row;
	$g_cache_name_to_id_map[$row['name']] = $c_field_id;

	return $row;
}

/**
 * Cache custom fields contained within an array of field id's
 * @param array $p_cf_id_array array of custom field id's
 * @return null
 * @access public
 */
function custom_field_cache_array_rows( $p_cf_id_array ) {
	global $g_cache_custom_field;
	$c_cf_id_array = array();

	foreach( $p_cf_id_array as $t_cf_id ) {
		if( !isset( $g_cache_custom_field[(int) $t_cf_id] ) ) {
			$c_cf_id_array[] = (int) $t_cf_id;
		}
	}

	if( empty( $c_cf_id_array ) ) {
		return;
	}

	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );

	$query = "SELECT *
				  FROM $t_custom_field_table
				  WHERE id IN (" . implode( ',', $c_cf_id_array ) . ')';
	$result = db_query_bound( $query );

	while( $row = db_fetch_array( $result ) ) {
		$g_cache_custom_field[(int) $row['id']] = $row;
	}
	return;
}

/**
 * Clear the custom field cache (or just the given id if specified)
 * @param int $p_field_id custom field id
 * @return bool
 * @access public
 */
function custom_field_clear_cache( $p_field_id = null ) {
	global $g_cache_custom_field, $g_cached_custom_field_lists;

	$g_cached_custom_field_lists = null;

	if( null === $p_field_id ) {
		$g_cache_custom_field = array();
	} else {
		$c_field_id = db_prepare_int( $p_field_id );
		unset( $g_cache_custom_field[$c_field_id] );
	}

	return true;
}

/**
 * Check to see whether the field is included in the given project
 * return true if the field is included, false otherwise
 * @param int $p_field_id custom field id
 * @param int $p_project_id project id
 * @return bool
 * @access public
 */
function custom_field_is_linked( $p_field_id, $p_project_id ) {
	global $g_cache_cf_linked;

	$c_project_id = db_prepare_int( $p_project_id );
	$c_field_id = db_prepare_int( $p_field_id );

	if( isset( $g_cache_cf_linked[$c_project_id] ) ) {
		if( in_array( $c_field_id, $g_cache_cf_linked[$p_project_id] ) ) {
			return true;
		}
		return false;
	}

	# figure out if this bug_id/field_id combination exists
	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "SELECT COUNT(*)
				FROM $t_custom_field_project_table
				WHERE field_id=" . db_param() . " AND
					  project_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_field_id, $c_project_id ) );
	$count = db_result( $result );

	if( $count > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check to see whether the field id is defined
 * return true if the field is defined, false otherwise
 * @param int $p_field_id custom field id
 * @return bool
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
 * @param int $p_field_id custom field id
 * @return int custom field type
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
 * @param int $p_field_id custom field id
 * @return bool
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
 * @param string $p_name custom field name
 * @param int $p_custom_field_id custom field id
 * @return bool
 * @access public
 */
function custom_field_is_name_unique( $p_name, $p_custom_field_id = null ) {
	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );
	$query = "SELECT COUNT(*)
				  FROM $t_custom_field_table
				  WHERE name=" . db_param();
	if( $p_custom_field_id !== null ) {
		$c_id = db_prepare_int( $p_custom_field_id );
		$query .= ' AND (id <> ' . db_param() . ')';
	}
	$result = db_query_bound( $query, ( ($p_custom_field_id !== null) ? Array( $p_name, $c_id ) : Array( $p_name ) ) );
	$count = db_result( $result );

	if( $count > 0 ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check to see whether the name is unique
 * return true if the name has not been used, error otherwise
 * @param string $p_name Custom field name
 * @return bool
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
 * @param int $p_field_id custom field id
 * @param int $p_bug_id bug id
 * @param int $p_user_id user id
 * @return bool
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
 * @param int $p_field_id custom field id
 * @param int $p_project_id bug id
 * @param int $p_user_id user id
 * @return bool
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
 * @param int $p_field_id custom field id
 * @param int $p_project_id bug id
 * @param int $p_user_id user id
 * @return bool
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
 * @param int $p_field_id custom field id
 * @param int $p_bug_id bug id
 * @param int $p_user_id user id
 * @return bool
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
 * @param string $p_name custom field name
 * @return int custom field id
 * @access public
 */
function custom_field_create( $p_name ) {
	if( string_contains_scripting_chars( $p_name ) ) {
		error_parameters( lang_get( 'custom_field_name' ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_PROPERTY, ERROR );
	}

	$c_name = trim( $p_name );

	if( is_blank( $c_name ) ) {
		error_parameters( 'name' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	custom_field_ensure_name_unique( $c_name );

	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );
	$query = "INSERT INTO $t_custom_field_table
					( name, possible_values )
				  VALUES
					( " . db_param() . ',' . db_param() . ')';

	db_query_bound( $query, Array( $c_name, '' ) );

	return db_insert_id( $t_custom_field_table );
}

/**
 * Update the field definition
 * return true on success, false on failure
 * @param int $p_field_id custom field id
 * @param array custom field definition
 * @return bool
 * @access public
 */
function custom_field_update( $p_field_id, $p_def_array ) {
	if( string_contains_scripting_chars( $p_def_array['name'] ) ) {
		error_parameters( lang_get( 'custom_field_name' ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_PROPERTY, ERROR );
	}

	if( is_blank( $p_def_array['name'] ) ) {
		error_parameters( 'name' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if( $p_def_array['access_level_rw'] < $p_def_array['access_level_r'] ) {
		error_parameters(
			lang_get( 'custom_field_access_level_r' ) . ', ' .
			lang_get( 'custom_field_access_level_rw' )
		);
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_PROPERTY, ERROR );
	}

	if (   $p_def_array['length_min'] < 0
		|| ( $p_def_array['length_max'] != 0 && $p_def_array['length_min'] > $p_def_array['length_max'] )
	) {
		error_parameters( lang_get( 'custom_field_length_min' ) . ', ' . lang_get( 'custom_field_length_max' ));
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_PROPERTY, ERROR );
	}

	if( !custom_field_is_name_unique( $p_def_array['name'], $p_field_id ) ) {
		trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
	}

	# Build fields update statement
	$t_update = '';
	foreach( $p_def_array as $field => $value ) {
		$t_update .= "$field = " . db_param() . ', ';
		$t_params[] = is_bool( $value ) ? db_prepare_bool( $value ) : $value;
	}

	# If there are fields to update, execute SQL
	if( $t_update !== '' ) {
		$t_mantis_custom_field_table = db_get_table( 'mantis_custom_field_table' );

		$t_query = "
			UPDATE $t_mantis_custom_field_table
			SET " . rtrim( $t_update, ', ' ) . "
			WHERE id = " . db_param();
		$t_params[] = $p_field_id;
		db_query_bound( $t_query, $t_params );

		custom_field_clear_cache( $p_field_id );

		# db_query errors on failure so:
		return true;
	}

	return false;
}

/**
 * Add a custom field to a project
 * return true on success, false on failure or if already added
 * @param int $p_field_id custom field id
 * @param int $p_project_id project id
 * @return bool
 * @access public
 */
function custom_field_link( $p_field_id, $p_project_id ) {
	$c_field_id = db_prepare_int( $p_field_id );
	$c_project_id = db_prepare_int( $p_project_id );

	custom_field_ensure_exists( $p_field_id );
	project_ensure_exists( $p_project_id );

	if( custom_field_is_linked( $p_field_id, $p_project_id ) ) {
		return false;
	}

	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "INSERT INTO $t_custom_field_project_table
					( field_id, project_id )
				  VALUES
					( " . db_param() . ', ' . db_param() . ')';
	db_query_bound( $query, Array( $c_field_id, $c_project_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Remove a custom field from a project
 * return true on success, false on failure
 *
 * The values for the custom fields are not deleted.  This is to allow for the
 * case where a bug is moved to another project that has the field, or the
 * field is linked again to the project.
 * @param int $p_field_id custom field id
 * @param int $p_project_id project id
 * @return bool
 * @access public
 */
function custom_field_unlink( $p_field_id, $p_project_id ) {
	$c_field_id = db_prepare_int( $p_field_id );
	$c_project_id = db_prepare_int( $p_project_id );

	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "DELETE FROM $t_custom_field_project_table
				  WHERE field_id = " . db_param() . " AND
				  		project_id = " . db_param();
	db_query_bound( $query, Array( $c_field_id, $c_project_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Delete the field definition and all associated values and project associations
 * return true on success, false on failure
 * @param int $p_field_id custom field id
 * @return bool
 * @access public
 */
function custom_field_destroy( $p_field_id ) {
	$c_field_id = db_prepare_int( $p_field_id );

	# delete all values
	$t_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
	$query = "DELETE FROM $t_custom_field_string_table
				  WHERE field_id=" . db_param();
	db_query_bound( $query, Array( $c_field_id ) );

	# delete all project associations
	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "DELETE FROM $t_custom_field_project_table
				  WHERE field_id=" . db_param();
	db_query_bound( $query, Array( $c_field_id ) );

	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );

	# delete the definition
	$query = "DELETE FROM $t_custom_field_table
				  WHERE id=" .  db_param();
	db_query_bound( $query, Array( $c_field_id ) );

	custom_field_clear_cache( $p_field_id );

	# db_query errors on failure so:
	return true;
}

/**
 * Delete all associations of custom fields to the specified project
 * return true on success, false on failure
 *
 * To be called from within project_delete().
 * @param int $p_project_id project id
 * @return bool
 * @access public
 */
function custom_field_unlink_all( $p_project_id ) {
	$c_project_id = db_prepare_int( $p_project_id );

	# delete all project associations
	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "DELETE FROM $t_custom_field_project_table
				  WHERE project_id=" . db_param();
	db_query_bound( $query, Array( $c_project_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Delete all custom values associated with the specified bug.
 * return true on success, false on failure
 *
 * To be called from bug_delete().
 * @param int $p_bug_id bug id
 * @return bool
 * @access public
 */
function custom_field_delete_all_values( $p_bug_id ) {
	$c_bug_id = db_prepare_int( $p_bug_id );

	$t_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
	$query = "DELETE FROM $t_custom_field_string_table
				  WHERE bug_id=" . db_param();
	db_query_bound( $query, Array( $c_bug_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Get the id of the custom field with the specified name.
 * false is returned if no custom field found with the specified name.
 * @param string $p_field_name custom field name
 * @param int $p_truncated_length
 * @return bool|int false or custom field id
 * @access public
 */
function custom_field_get_id_from_name( $p_field_name, $p_truncated_length = null ) {
	global $g_cache_name_to_id_map;

	if ( is_blank( $p_field_name ) ) {
		return false;
	}

	if ( isset( $g_cache_name_to_id_map[$p_field_name] ) ) {
		return $g_cache_name_to_id_map[$p_field_name];
	}

	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );

	if(( null === $p_truncated_length ) || ( utf8_strlen( $p_field_name ) != $p_truncated_length ) ) {
		$query = "SELECT id FROM $t_custom_field_table WHERE name = " . db_param();
		$c_field_name = $p_field_name;
	} else {
		# This is to handle the case where we potentially only have a
		# truncated part of the custom field name.  This happens when we
		# are getting the field from the history logs (as the history's
		# field_name column used to be 32 while custom field name is 64).
		# This is needed to handle legacy database entries, as any
		# history record created after 1.1.0a4 has the correct field
		# size (see #8002)
		$query = "SELECT id FROM $t_custom_field_table WHERE name LIKE " . db_param();
		$c_field_name = $p_field_name . '%';
	}

	$t_result = db_query_bound( $query, array( $c_field_name ) );

	if( db_num_rows( $t_result ) == 0 ) {
		$g_cache_name_to_id_map[$p_field_name] = false;
		return false;
	}

	$row = db_fetch_array( $t_result );
	$g_cache_name_to_id_map[$p_field_name] = $row['id'];

	return $row['id'];
}

/**
 * Return an array of ids of custom fields bound to the specified project
 *
 * The ids will be sorted based on the sequence number associated with the binding
 * @param int $p_project_id project id
 * @return array
 * @access public
 */
function custom_field_get_linked_ids( $p_project_id = ALL_PROJECTS ) {
	global $g_cache_cf_linked, $g_cache_custom_field;

	if( !isset( $g_cache_cf_linked[$p_project_id] ) ) {

		$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );
		$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );

		if( ALL_PROJECTS == $p_project_id ) {
			$t_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
			$t_project_table = db_get_table( 'mantis_project_table' );
			$t_user_table = db_get_table( 'mantis_user_table' );
			$t_user_id = auth_get_current_user_id();

			# Select only the ids of custom fields in projects the user has access to
			#  - all custom fields in public projects,
			#  - those in private projects where the user is listed
			#  - in private projects where the user is implicitly listed
			$t_query = "
				SELECT DISTINCT cft.id
				FROM $t_custom_field_table cft
					JOIN $t_custom_field_project_table cfpt ON cfpt.field_id = cft.id
					JOIN $t_project_table pt
						ON pt.id = cfpt.project_id AND pt.enabled = " . db_prepare_bool( true ) . "
					LEFT JOIN $t_project_user_list_table pult
						ON pult.project_id = cfpt.project_id AND pult.user_id = " . db_param() . "
					, $t_user_table ut
				WHERE ut.id = " . db_param() . "
					AND (  pt.view_state = " . VS_PUBLIC . "
						OR pult.user_id = ut.id
						";
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
					$t_access_clause = rtrim( $t_access_clause, ',') . ')';
				}
			} else {
				$t_access_clause = '>=' . db_param();
				$t_params[] = $t_private_access;
			}
			$t_query .= "OR ( pult.user_id IS NULL AND ut.access_level $t_access_clause ) )";
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
					$t_project_clause = rtrim( $t_project_clause, ',') . ')';
				}
			} else {
				$t_project_clause = '= ' . db_param();
				$t_params[] = $p_project_id;
			}
			$t_query = "
				SELECT cft.id
				FROM $t_custom_field_table cft
					JOIN $t_custom_field_project_table cfpt ON cfpt.field_id = cft.id
				WHERE cfpt.project_id $t_project_clause
				ORDER BY sequence ASC, name ASC";
		}

		$result = db_query_bound( $t_query, $t_params );
		$t_row_count = db_num_rows( $result );
		$t_ids = array();

		for( $i = 0;$i < $t_row_count;$i++ ) {
			$row = db_fetch_array( $result );
			array_push( $t_ids, $row['id'] );
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

	if( $g_cache_cf_list === NULL ) {
		$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );
		$query = "SELECT *
				  FROM $t_custom_field_table
				  ORDER BY name ASC";
		$result = db_query_bound( $query );
		$t_row_count = db_num_rows( $result );
		$t_ids = array();

		for( $i = 0;$i < $t_row_count;$i++ ) {
			$row = db_fetch_array( $result );

			$g_cache_custom_field[(int) $row['id']] = $row;

			array_push( $t_ids, $row['id'] );
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
 * @param int $p_field_id custom field id
 * @return array
 * @access public
 */
function custom_field_get_project_ids( $p_field_id ) {
	$c_field_id = db_prepare_int( $p_field_id );

	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "SELECT project_id
				  FROM $t_custom_field_project_table
				  WHERE field_id = " . db_param();
	$result = db_query_bound( $query, Array( $c_field_id ) );

	$t_row_count = db_num_rows( $result );
	$t_ids = array();

	for( $i = 0;$i < $t_row_count;$i++ ) {
		$row = db_fetch_array( $result );

		array_push( $t_ids, $row['project_id'] );
	}

	return $t_ids;
}

/**
 * Return a field definition row for the field or error if the field does not exist
 * @param int $p_field_id custom field id
 * @return array custom field definition
 * @access public
 */
function custom_field_get_definition( $p_field_id ) {
	return custom_field_cache_row( $p_field_id );
}

/**
 * Return a single database field from a custom field definition row for the field
 * if the database field does not exist, display a warning and return ''
 * @param int $p_field_id custom field id
 * @param int $p_field_name custom field name
 * @return string
 * @access public
 */
function custom_field_get_field( $p_field_id, $p_field_name ) {
	$row = custom_field_get_definition( $p_field_id );

	if( isset( $row[$p_field_name] ) ) {
		return $row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * Return custom field name including localized name (if available)
 *
 * @param string $p_name Custom field's name
 * @return string CustomFieldName [(LocalizedName)]
 * @access public
 */
function custom_field_get_display_name( $p_name ) {
	$t_local_name = lang_get_defaulted( $p_name );
	if( $t_local_name != $p_name ) {
		$p_name .= " ($t_local_name)";
	}

	return string_display( $p_name );
}

/**
 * Get the value of a custom field for the given bug
 * @todo return values are unclear... should we error when access is denied
 * and provide an api to check whether it will be?
 * @param int $p_field_id custom field id
 * @param int $p_bug_id bug id
 * @return mixed: value is defined, null: no value is defined, false: read access is denied
 * @access public
 */
function custom_field_get_value( $p_field_id, $p_bug_id ) {
	$c_field_id = db_prepare_int( $p_field_id );
	$c_bug_id = db_prepare_int( $p_bug_id );

	$row = custom_field_cache_row( $p_field_id );

	$t_access_level_r = $row['access_level_r'];
	$t_default_value = $row['default_value'];

	if( !custom_field_has_read_access( $p_field_id, $p_bug_id, auth_get_current_user_id() ) ) {
		return false;
	}

	$t_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
	$query = "SELECT value
				  FROM $t_custom_field_string_table
				  WHERE bug_id=" . db_param() . " AND
				  		field_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bug_id, $c_field_id ) );

	if( db_num_rows( $result ) > 0 ) {
		return custom_field_database_to_value( db_result( $result ), $row['type'] );
	} else {
		return null;
	}
}

/**
 * Gets the custom fields array for the given bug readable by specified level.
 * Array keys are custom field names. Array is sorted by custom field sequence number;
 * Array items are arrays with the next keys:
 * 'type', 'value', 'access_level_r'
 * @param int $p_bug_id bug id
 * @param int $p_user_access_level Access level
 * @return  array
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
 * @param int $p_bug_id bug id
 * @return  array
 * @access public
 */
function custom_field_get_all_linked_fields( $p_bug_id ) {
	global $g_cached_custom_field_lists;

	if( !is_array( $g_cached_custom_field_lists ) ) {
		$g_cached_custom_field_lists = array();
	}

	# is the list in cache ?
	if( !array_key_exists( $p_bug_id, $g_cached_custom_field_lists ) ) {
		$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
		$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );
		$t_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );

		$query = "
			SELECT f.name, f.type, f.access_level_r, f.default_value, f.type, s.value
			FROM $t_custom_field_project_table p
				INNER JOIN $t_custom_field_table f ON f.id = p.field_id
				LEFT JOIN $t_custom_field_string_table s
					ON s.field_id = p.field_id AND s.bug_id = " . db_param() . "
			WHERE p.project_id = " . db_param() . "
			ORDER BY p.sequence ASC, f.name ASC";

		$t_params = array(
			(int)$p_bug_id,
			bug_get_field( $p_bug_id, 'project_id' )
		);

		$result = db_query_bound( $query, $t_params );
		$t_row_count = db_num_rows( $result );

		$t_custom_fields = array();

		for( $i = 0;$i < $t_row_count;++$i ) {
			$row = db_fetch_array( $result );

			if( is_null( $row['value'] ) ) {
				$t_value = $row['default_value'];
			} else {
				$t_value = custom_field_database_to_value( $row['value'], $row['type'] );
			}

			$t_custom_fields[$row['name']] = array(
				'type' => $row['type'],
				'value' => $t_value,
				'access_level_r' => $row['access_level_r'],
			);
		}

		$g_cached_custom_field_lists[$p_bug_id] = $t_custom_fields;
	}

	return $g_cached_custom_field_lists[$p_bug_id];
}

/**
 * Gets the sequence number for the specified custom field for the specified
 * project.  Returns false in case of error.
 * @param int $p_field_id custom field id
 * @param int $p_project_id project id
 * @return int|bool
 * @access public
 */
function custom_field_get_sequence( $p_field_id, $p_project_id ) {
	$c_field_id = db_prepare_int( $p_field_id );
	$c_project_id = db_prepare_int( $p_project_id );

	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );
	$query = "SELECT sequence
				  FROM $t_custom_field_project_table
				  WHERE field_id=" . db_param() . " AND
						project_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_field_id, $c_project_id ), 1 );

	if( 0 == db_num_rows( $result ) ) {
		return false;
	}

	$t_row = db_fetch_array( $result );

	return $t_row['sequence'];
}

/**
 * Allows the validation of a custom field value without setting it
 * or needing a bug to exist.
 * @param int $p_field_id custom field id
 * @param string $p_value custom field value
 * @return bool
 * @access public
 */
function custom_field_validate( $p_field_id, $p_value ) {
	$c_field_id = db_prepare_int( $p_field_id );

	custom_field_ensure_exists( $p_field_id );

	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );
	$query = "SELECT name, type, possible_values, valid_regexp,
				  		 access_level_rw, length_min, length_max, default_value
				  FROM $t_custom_field_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_field_id ) );
	$row = db_fetch_array( $result );

	$t_name = $row['name'];
	$t_type = $row['type'];
	$t_possible_values = $row['possible_values'];
	$t_valid_regexp = $row['valid_regexp'];
	$t_length_min = $row['length_min'];
	$t_length_max = $row['length_max'];
	$t_default_value = $row['default_value'];

	$t_valid = true;
	$t_length = utf8_strlen( $p_value );
	switch ($t_type) {
		case CUSTOM_FIELD_TYPE_STRING:
			# Empty fields are valid
			if( $t_length == 0 ) {
				break;
			}
			# Regular expression string validation
			if( !is_blank( $t_valid_regexp ) ) {
				$t_valid &= preg_match( "/$t_valid_regexp/", $p_value );
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
			# gpc_get_cf for date returns the value from strftime
			# Either false (php >= 5.1) or -1 (php < 5.1) for failure
			$t_valid &= ( $p_value == null ) || ( ( $p_value !== false ) && ( $p_value > 0 ) );
			break;
		case CUSTOM_FIELD_TYPE_CHECKBOX:
		case CUSTOM_FIELD_TYPE_MULTILIST:
			# Checkbox fields can hold a null value (when no checkboxes are ticked)
			if ( $p_value === '' ) {
				break;
			}

			# If checkbox field value is not null then we need to validate it... (note: no "break" statement here!)
			$t_values = explode( '|', $p_value );
			$t_possible_values = custom_field_prepare_possible_values( $row['possible_values'] );
			$t_possible_values = explode( '|', $t_possible_values );
			$t_invalid_values = array_diff( $t_values, $t_possible_values );
			$t_valid &= ( count( $t_invalid_values ) == 0 );
			break;
		case CUSTOM_FIELD_TYPE_LIST:
		case CUSTOM_FIELD_TYPE_ENUM:
		case CUSTOM_FIELD_TYPE_RADIO:
			# List fields can be empty (when they are not shown on the form or shown with no default values and never clicked)
			if ( is_blank( $p_value ) ) {
				break;
			}

			# If list field value is not empty then we need to validate it... (note: no "break" statement here!)
			$t_possible_values = custom_field_prepare_possible_values( $row['possible_values'] );
			$t_values_arr = explode( '|', $t_possible_values );
			$t_valid &= in_array( $p_value, $t_values_arr );
			break;
		case CUSTOM_FIELD_TYPE_EMAIL:
			if ( $p_value !== '' ) {
				$t_valid &= email_is_valid( $p_value );
			}
			break;
		default:
			break;
	}
	return (bool)$t_valid;
}

/**
 * $p_possible_values: possible values to be pre-processed.  If it has enum values,
 * it will be left as is.  If it has a method, it will be replaced by the list.
 * @param string $p_possible_values
 * @return string|array
 * @access public
 */
function custom_field_prepare_possible_values( $p_possible_values ) {
	$t_possible_values = $p_possible_values;

	if( !is_blank( $t_possible_values ) && ( $t_possible_values[0] == '=' ) ) {
		$t_possible_values = helper_call_custom_function( 'enum_' . utf8_substr( $t_possible_values, 1 ), array() );
	}

	return $t_possible_values;
}

/**
 * Get All Possible Values for a Field.
 * @param array $p_field_def custom field definition
 * @param int $p_project_id project id
 * @return bool|array
 * @access public
 */
function custom_field_distinct_values( $p_field_def, $p_project_id = ALL_PROJECTS ) {
	global $g_custom_field_type_definition;
	$t_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
	$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	$t_return_arr = array();

	# If an enumeration type, we get all possible values, not just used values
	if( isset( $g_custom_field_type_definition[$p_field_def['type']]['#function_return_distinct_values'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_field_def['type']]['#function_return_distinct_values'], $p_field_def );
	} else {
		$t_from = "$t_custom_field_string_table cfst";
		$t_where1 = 'cfst.field_id = ' . db_param();
		$t_params[] = $p_field_def['id'];

		if( ALL_PROJECTS != $p_project_id ) {
			$t_from .= " JOIN $t_mantis_bug_table bt ON bt.id = cfst.bug_id";
			$t_where2 = 'AND bt.project_id = ' . db_param();
			$t_params[] = $p_project_id;
		} else {
			$t_where2 = '';
		}
		$t_query = "
			SELECT DISTINCT cfst.value
			FROM $t_from
			WHERE $t_where1 $t_where2
			ORDER BY cfst.value";

		$t_result = db_query_bound( $t_query, $t_params );
		$t_row_count = db_num_rows( $t_result );
		if( 0 == $t_row_count ) {
			return false;
		}

		for( $i = 0;$i < $t_row_count;$i++ ) {
			$row = db_fetch_array( $t_result );
			if( !is_blank( trim( $row['value'] ) ) ) {
				array_push( $t_return_arr, $row['value'] );
			}
		}
	}
	return $t_return_arr;
}

/**
 * Convert the value to save it into the database, depending of the type
 * return value for database
 * @param mixed $p_value
 * @param int $p_type
 * @return mixed
 * @access public
 */
function custom_field_value_to_database( $p_value, $p_type ) {
	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_type]['#function_value_to_database'] ) ) {
		return call_user_func( $g_custom_field_type_definition[$p_type]['#function_value_to_database'], $p_value );
	}
	return $p_value;
}

/**
 * Convert the database-value to value, depending of the type
 * return value for further operation
 * @param mixed $p_value
 * @param int $p_type
 * @return mixed
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
 * @param mixed $p_value
 * @param int $p_type
 * @return mixed
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
 * @param int $p_field_id custom field id
 * @param int $p_bug_id bug id
 * @param mixed $p_value
 * @param boolean $p_log create history logs for new values
 * @return bool
 * @access public
 */
function custom_field_set_value( $p_field_id, $p_bug_id, $p_value, $p_log_insert=true ) {
	$c_field_id = db_prepare_int( $p_field_id );
	$c_bug_id = db_prepare_int( $p_bug_id );

	custom_field_ensure_exists( $p_field_id );

	if ( !custom_field_validate( $p_field_id, $p_value ) )
		return false;

	$t_name = custom_field_get_field( $p_field_id, 'name' );
	$t_type = custom_field_get_field( $p_field_id, 'type' );
	$t_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );

	# Determine whether an existing value needs to be updated or a new value inserted
	$query = "SELECT value
				  FROM $t_custom_field_string_table
				  WHERE field_id=" . db_param() . " AND
				  		bug_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_field_id, $c_bug_id ) );

	if( db_num_rows( $result ) > 0 ) {
		$query = "UPDATE $t_custom_field_string_table
					  SET value=" . db_param() . "
					  WHERE field_id=" . db_param() . " AND
					  		bug_id=" . db_param();
		db_query_bound( $query, Array( custom_field_value_to_database( $p_value, $t_type ), $c_field_id, $c_bug_id ) );

		$row = db_fetch_array( $result );
		history_log_event_direct( $c_bug_id, $t_name, custom_field_database_to_value( $row['value'], $t_type ), $p_value );
	} else {
		$query = "INSERT INTO $t_custom_field_string_table
						( field_id, bug_id, value )
					  VALUES
						( " . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
		db_query_bound( $query, Array( $c_field_id, $c_bug_id, custom_field_value_to_database( $p_value, $t_type ) ) );
		# Don't log history events for new bug reports or on other special occasions
		if ( $p_log_insert ) {
			history_log_event_direct( $c_bug_id, $t_name, '', $p_value );
		}
	}

	custom_field_clear_cache( $p_field_id );

	# db_query errors on failure so:
	return true;
}

/**
 * Sets the sequence number for the specified custom field for the specified
 * project.
 * @param int $p_field_id custom field id
 * @param int $p_project_id project id
 * @param int $p_sequence
 * @return bool
 * @access public
 */
function custom_field_set_sequence( $p_field_id, $p_project_id, $p_sequence ) {
	$c_field_id = db_prepare_int( $p_field_id );
	$c_project_id = db_prepare_int( $p_project_id );
	$c_sequence = db_prepare_int( $p_sequence );

	$t_custom_field_project_table = db_get_table( 'mantis_custom_field_project_table' );

	$query = "UPDATE $t_custom_field_project_table
				  SET sequence=" . db_param() . "
				  WHERE field_id=" . db_param() . " AND
				  		project_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_sequence, $c_field_id, $c_project_id ) );

	custom_field_clear_cache( $p_field_id );

	return true;
}

/**
 * Print an input field
 * $p_field_def contains the definition of the custom field (including it's field id
 * $p_bug_id    contains the bug where this field belongs to. If it's left
 * away, it'll default to 0 and thus belongs to a new (i.e. non-existant) bug
 * NOTE: This probably belongs in the print_api.php
 * @param array $p_field_def custom field definition
 * @param int $p_bug_id bug id
 * @access public
 */
function print_custom_field_input( $p_field_def, $p_bug_id = null ) {
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
	} else {
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_DEFINITION, ERROR );
	}
}

/**
 * Prepare a string containing a custom field value for display
 * @todo This probably belongs in the string_api.php
 * @param array  $p_def contains the definition of the custom field
 * @param int $p_field_id contains the id of the field
 * @param int $p_bug_id contains the bug id to display the custom field value for
 * @return string
 * @access public
 */
function string_custom_field_value( $p_def, $p_field_id, $p_bug_id ) {
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
 * @param array  $p_def contains the definition of the custom field
 * @param int $p_field_id contains the id of the field
 * @param int $p_bug_id contains the bug id to display the custom field value for
 * @return null
 * @access public
 */
function print_custom_field_value( $p_def, $p_field_id, $p_bug_id ) {
	echo string_custom_field_value( $p_def, $p_field_id, $p_bug_id );
}

/**
 * Prepare a string containing a custom field value for email
 * NOTE: This probably belongs in the string_api.php
 * @param string $p_value value of custom field
 * @param int $p_type	type of custom field
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
