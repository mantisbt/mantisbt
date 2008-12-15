<?php
# Mantis - a php based bugtracking system
# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package CoreAPI
 * @subpackage VersionAPI
 */

# =========================================
# Version Data Structure Definition
# ===================================
class VersionData {
	var $id = 0;
	var $project_id = 0;
	var $version = '';
	var $description = '';
	var $released = VERSION_FUTURE;
	var $date_order = '';
	var $obsolete = 0;
}

# ===================================
# Boolean queries and ensures
# ===================================

$g_cache_versions = array();

# --------------------
# Cache a version row if necessary and return the cached copy
#  If the second parameter is true (default), trigger an error
#  if the version can't be found.  If the second parameter is
#  false, return false if the version can't be found.
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
	$row['date_order'] = db_unixtimestamp( $row['date_order'] );
	$g_cache_versions[$c_version_id] = $row;

	return $row;
}

# --------------------
# Check whether the version exists
# $p_project_id : null will use the current project, otherwise the specified project
# Returns true if the version exists, false otherwise
function version_exists( $p_version_id ) {
	return version_cache_row( $p_version_id, false ) !== false;
}

# --------------------
# Check whether the version name is unique
# Returns true if the name is unique, false otherwise
function version_is_unique( $p_version, $p_project_id = null ) {
	return version_get_id( $p_version, $p_project_id ) === false;
}

# --------------------
# Check whether the version exists
# Trigger an error if it does not
function version_ensure_exists( $p_version_id ) {
	if( !version_exists( $p_version_id ) ) {
		error_parameters( $p_version_id );
		trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
	}
}

# --------------------
# Check whether the version is unique within a project
# Trigger an error if it is not
function version_ensure_unique( $p_version, $p_project_id = null ) {
	if( !version_is_unique( $p_version, $p_project_id ) ) {
		trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
	}
}

# ===================================
# Creation / Deletion / Updating
# ===================================
# --------------------
# Add a version to the project
function version_add( $p_project_id, $p_version, $p_released = VERSION_FUTURE, $p_description = '', $p_date_order = null, $p_obsolete = false ) {
	$c_project_id = db_prepare_int( $p_project_id );
	$c_released = db_prepare_int( $p_released );
	$c_obsolete = db_prepare_bool( $p_obsolete );

	if( null === $p_date_order ) {
		$c_date_order = db_now();
	} else {
		$c_date_order = db_timestamp( $p_date_order );
	}

	version_ensure_unique( $p_version, $p_project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$query = "INSERT INTO $t_project_version_table
					( project_id, version, date_order, description, released, obsolete )
				  VALUES
					(" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query_bound( $query, Array( $c_project_id, $p_version, $c_date_order, $p_description, $c_released, $c_obsolete ) );

	# db_query errors on failure so:
	return true;
}

# --------------------
# Update the definition of a version
function version_update( $p_version_info ) {
	version_ensure_exists( $p_version_info->id );

	$t_old_version_name = version_get_field( $p_version_info->id, 'version' );

	# check for duplicates
	if(( strtolower( $t_old_version_name ) != strtolower( $p_version_info->version ) ) && !version_is_unique( $p_version_info->version, $p_version_info->project_id ) ) {
		trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
	}

	$c_version_id = db_prepare_int( $p_version_info->id );
	$c_version_name = $p_version_info->version;
	$c_old_version_name = $t_old_version_name;
	$c_description = $p_version_info->description;
	$c_released = db_prepare_int( $p_version_info->released );
	$c_obsolete = db_prepare_bool( $p_version_info->obsolete );
	$c_date_order = db_timestamp( $p_version_info->date_order );
	$c_project_id = db_prepare_int( $p_version_info->project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "UPDATE $t_project_version_table
				  SET version=" . db_param() . ",
					description=" . db_param() . ",
					released=" . db_param() . ",
					date_order=" . db_param() . ",
					obsolete=" . db_param() . "
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_version_name, $c_description, $c_released, $c_date_order, $c_obsolete, $c_version_id ) );

	if( $c_version_name != $c_old_version_name ) {
		$query = "UPDATE $t_bug_table
					  SET version=" . db_param() . '
					  WHERE ( project_id=' . db_param() . ') AND ( version=' . db_param() . ')';
		db_query_bound( $query, Array( $c_version_name, $c_project_id, $c_old_version_name ) );

		$query = "UPDATE $t_bug_table
					  SET fixed_in_version=" . db_param() . '
					  WHERE ( project_id=' . db_param() . ') AND ( fixed_in_version=' . db_param() . ')';
		db_query_bound( $query, Array( $c_version_name, $c_project_id, $c_old_version_name ) );

		$query = "UPDATE $t_bug_table
					  SET target_version=" . db_param() . '
					  WHERE ( project_id=' . db_param() . ') AND ( target_version=' . db_param() . ')';
		db_query_bound( $query, Array( $c_version_name, $c_project_id, $c_old_version_name ) );

		/**
		 * @todo We should consider using ids instead of names for foreign keys.  The main advantage of using the names are:
		 *		- for history the version history entries will still be valid even if the version is deleted in the future. --  we can ban deleting referenced versions.
		 *		- when an issue is copied or moved from one project to another, we can keep the last version with the issue even if it doesn't exist in the new project.  Also previous history entries remain valid.
		 * @todo We should update the history for version, fixed_in_version, and target_version.
		 * @todo We probably need to update the saved filters too?
		 */
	}

	// db_query errors on failure so:
	return true;
}

# --------------------
# Remove a version from the project
function version_remove( $p_version_id, $p_new_version = '' ) {
	$c_version_id = db_prepare_int( $p_version_id );
	$c_new_version = db_prepare_string( $p_new_version );

	version_ensure_exists( $p_version_id );

	$t_old_version = version_get_field( $p_version_id, 'version' );
	$t_project_id = version_get_field( $p_version_id, 'project_id' );
	$c_project_id = db_prepare_int( $t_project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "DELETE FROM $t_project_version_table
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_version_id ) );

	$query = "UPDATE $t_bug_table
				  SET version=" . db_param() . "
				  WHERE project_id=" . db_param() . " AND version=" . db_param();
	db_query_bound( $query, Array( $c_new_version, $c_project_id, $t_old_version ) );

	$query = "UPDATE $t_bug_table
				  SET fixed_in_version=" . db_param() . '
				  WHERE ( project_id=' . db_param() . ' ) AND ( fixed_in_version=' . db_param() . ')';
	db_query_bound( $query, Array( $c_new_version, $c_project_id, $t_old_version ) );

	# db_query errors on failure so:
	return true;
}

# --------------------
# Remove all versions associated with a project
function version_remove_all( $p_project_id ) {
	$c_project_id = db_prepare_int( $p_project_id );

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "DELETE FROM $t_project_version_table
	  			  WHERE project_id=" . db_param();

	db_query_bound( $query, Array( $c_project_id ) );

	$query = "UPDATE $t_bug_table
				  SET version=''
				  WHERE project_id=" . db_param();
	db_query_bound( $query, Array( $c_project_id ) );

	$query = "UPDATE $t_bug_table
				  SET fixed_in_version=''
				  WHERE project_id=" . db_param();
	db_query_bound( $query, Array( $c_project_id ) );

	# db_query errors on failure so:
	return true;
}

# ===================================
# Data Access
# ===================================
# --------------------
# Return all versions for the specified project
function version_get_all_rows( $p_project_id, $p_released = null, $p_obsolete = false ) {
	global $g_cache_versions;

	$c_project_id = db_prepare_int( $p_project_id );
	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$t_param_count = 0;

	$query = "SELECT *
				  FROM $t_project_version_table
				  WHERE project_id=" . db_param( $t_param_count++ );
	$query_params = array(
		$c_project_id,
	);

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
		$row['date_order'] = db_unixtimestamp( $row['date_order'] );
		$g_cache_versions[(int) $row['id']] = $row;

		$rows[] = $row;
	}
	return $rows;
}

# --------------------
# Return all versions for the specified project, including subprojects
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
		$row['date_order'] = db_unixtimestamp( $row['date_order'] );
		$rows[] = $row;
	}
	return $rows;
}

# --------------------
# Get the version_id, given the project_id and $p_version_id
# returns false if not found, otherwise returns the id.
function version_get_id( $p_version, $p_project_id = null ) {
	global $g_cache_versions;
	if( $p_project_id === null ) {
		$c_project_id = helper_get_current_project();
	} else {
		$c_project_id = db_prepare_int( $p_project_id );
	}

	foreach( $g_cache_versions as $t_version ) {
		if(( $t_version['version'] == $p_version ) && ( $t_version['project_id'] == $c_project_id ) ) {
			return $t_version['id'];
		}
	}

	$t_project_version_table = db_get_table( 'mantis_project_version_table' );

	$query = "SELECT id
					FROM $t_project_version_table
					WHERE project_id=" . db_param() . " AND
						version=" . db_param();

	$result = db_query_bound( $query, Array( $c_project_id, $p_version ) );

	if( 0 == db_num_rows( $result ) ) {
		return false;
	} else {
		return db_result( $result );
	}
}

# --------------------
# Get the specified field name for the specified version id.
# triggers an error if version not found, otherwise returns the field value.
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

# --------------------
# get information about a version given its id
function version_get( $p_version_id ) {
	$row = version_cache_row( $p_version_id );

	$t_version_data = new VersionData;
	$t_row_keys = array_keys( $row );
	$t_vars = get_object_vars( $t_version_data );

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

# --------------------
# Return a copy of the version structure with all the instvars prepared for db insertion
function version_prepare_db( $p_version_info ) {
	$p_version_info->id = db_prepare_int( $p_version_info->id );
	$p_version_info->project_id = db_prepare_int( $p_version_info->project_id );
	$p_version_info->released = db_prepare_int( $p_version_info->released );

	return $p_version_info;
}
