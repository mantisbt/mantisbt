<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: custom_field_api.php,v 1.24 2004-02-24 23:51:38 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'bug_api.php' );

	###########################################################################
	# Custom Fields API
	###########################################################################

#*******************************************
#	TODO
#	- add an object to store field data like BugData and UserPrefs ?
#	- add caching functions like user, bug, etc
#	- make existing api functions use caching functions
#	- add functions to return individual db columns for a field definition
#*******************************************

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_custom_field = array();
	
	# Cache a custom field row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the field can't be found.  If the second parameter is
	#  false, return false if the field can't be found.
	function custom_field_cache_row( $p_field_id, $p_trigger_errors=true ) {
		global $g_cache_custom_field;

		$c_field_id = db_prepare_int( $p_field_id );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );

		if ( isset ( $g_cache_custom_field[$c_field_id] ) ) {
			return $g_cache_custom_field[$c_field_id];
		}

		$query = "SELECT *
				  FROM $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_custom_field[$c_field_id] = $row;

		return $row;
	}

	# --------------------
	# Clear the custom field cache (or just the given id if specified)
	function custom_field_clear_cache( $p_field_id = null ) {
		global $g_cache_custom_field;

		if ( null === $p_field_id ) {
			$g_cache_custom_field = array();
		} else {
			$c_field_id = db_prepare_int( $p_field_id );
			unset( $g_cache_custom_field[$c_field_id] );
		}

		return true;
	}


	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check to see whether the field is included in the given project
	#  return true if the field is included, false otherwise
	#
	function custom_field_is_linked( $p_field_id, $p_project_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_field_id		= db_prepare_int( $p_field_id );

		# figure out if this bug_id/field_id combination exists
		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "SELECT COUNT(*) FROM
				  $t_custom_field_project_table
				  WHERE field_id='$c_field_id'
					AND project_id='$c_project_id'";
		$result = db_query( $query );

		$count = db_result( $result );

		if ( $count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check to see whether the field id is defined
	#  return true if the field is defined, false otherwise
	function custom_field_exists( $p_field_id ) {
		if ( false == custom_field_cache_row( $p_field_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Check to see whether the field id is defined
	#  return true if the field is defined, error otherwise
	function custom_field_ensure_exists( $p_field_id ) {
		if ( custom_field_exists( $p_field_id ) ) {
			return true;
		} else {
			trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# Check to see whether the name is unique
	#  return false if a field with the name already exists, true otherwise
	#  if an id is specified, then the corresponding record is excluded from the
	#  uniqueness test.
	function custom_field_is_name_unique( $p_name, $p_custom_field_id = null ) {
		$c_name		= db_prepare_string( $p_name );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT COUNT(*) FROM
				  $t_custom_field_table
				  WHERE name='$c_name'";
		if ( $p_custom_field_id !== null ) {
			$c_id = db_prepare_int( $p_custom_field_id );
			$query .= " AND (id <> $c_id)";
		}
		$result = db_query( $query );

		$count = db_result( $result );

		if ( $count > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Check to see whether the name is unique
	#  return true if the name has not been used, error otherwise
	function custom_field_ensure_name_unique( $p_name ) {
		if ( custom_field_is_name_unique( $p_name ) ) {
			return true;
		} else {
			trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
		}
	}

	# --------------------
	# Return true if the user can read the value of the field for the given bug,
	#  false otherwise.
	function custom_field_has_read_access( $p_field_id, $p_bug_id, $p_user_id = null ) {
		custom_field_ensure_exists( $p_field_id );

		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$t_access_level_r = custom_field_get_field( $p_field_id, 'access_level_r' );

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		if ( user_get_access_level( $p_user_id, $t_project_id ) >= $t_access_level_r ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Return true if the user can modify the value of the field for the given project,
	#  false otherwise.
	function custom_field_has_write_access_to_project( $p_field_id, $p_project_id, $p_user_id = null ) {
		custom_field_ensure_exists( $p_field_id );

		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$t_access_level_rw = custom_field_get_field( $p_field_id, 'access_level_rw' );

		if ( user_get_access_level( $p_user_id, $p_project_id ) >= $t_access_level_rw ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Return true if the user can modify the value of the field for the given bug,
	#  false otherwise.
	function custom_field_has_write_access( $p_field_id, $p_bug_id, $p_user_id = null ) {
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
		return ( custom_field_has_write_access_to_project( $p_field_id, $t_project_id, $p_user_id ) );
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# create a new custom field with the name $p_name
	# the definition are the default values and can be changes later
	# return the ID of the new definition
	function custom_field_create( $p_name ) {
		$c_name = db_prepare_string( trim( $p_name ) );

		if ( is_blank( $p_name ) ) {
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		custom_field_ensure_name_unique( $p_name );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "INSERT INTO
				  $t_custom_field_table
					( name )
				  VALUES
					( '$c_name' )";

		db_query( $query );

		return db_insert_id();
	}

	# --------------------
	# Update the field definition
	#  return true on success, false on failure
	function custom_field_update( $p_field_id, $p_def_array ) {
		$c_field_id			= db_prepare_int( $p_field_id );
		$c_name				= db_prepare_string( trim( $p_def_array['name'] ) );
		$c_type				= db_prepare_int(    $p_def_array['type']            );
		$c_possible_values	= db_prepare_string( $p_def_array['possible_values'] );
		$c_default_value	= db_prepare_string( $p_def_array['default_value']   );
		$c_valid_regexp		= db_prepare_string( $p_def_array['valid_regexp']      );
		$c_access_level_r	= db_prepare_int(    $p_def_array['access_level_r']  );
		$c_access_level_rw	= db_prepare_int(    $p_def_array['access_level_rw'] );
		$c_length_min		= db_prepare_int(    $p_def_array['length_min']      );
		$c_length_max		= db_prepare_int(    $p_def_array['length_max']      );
		$c_advanced			= db_prepare_bool(   $p_def_array['advanced']        );

		if (	( is_blank( $c_name ) ) || 
			( $c_access_level_rw < $c_access_level_r ) ||
			( $c_length_min < 0 ) ||
			( ( $c_length_max != 0 ) && ( $c_length_min > $c_length_max ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_DEFINITION, ERROR );
		}

		if ( !custom_field_is_name_unique( $c_name, $c_field_id ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
		}

		$query = "UPDATE " .
				 config_get( 'mantis_custom_field_table' ).
				 " SET ";
		$t_update_something = false;

		if( array_key_exists( 'name', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "name='$c_name'";
		}
		if( array_key_exists( 'type', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "type='$c_type'";
		}
		if( array_key_exists( 'possible_values', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "possible_values='$c_possible_values'";
		}
		if( array_key_exists( 'default_value', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "default_value='$c_default_value'";
		}
		if( array_key_exists( 'valid_regexp', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "valid_regexp='$c_valid_regexp'";
		}
		if( array_key_exists( 'access_level_r', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "access_level_r='$c_access_level_r'";
		}
		if( array_key_exists( 'access_level_rw', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "access_level_rw='$c_access_level_rw'";
		}
		if( array_key_exists( 'length_min', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "length_min='$c_length_min'";
		}
		if( array_key_exists( 'length_max', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "length_max='$c_length_max'";
		}
		if( array_key_exists( 'advanced', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "advanced='$c_advanced'";
		}

		$query .= " WHERE id='$c_field_id'";

		if( $t_update_something ) {
			db_query( $query );
			custom_field_clear_cache( $p_field_id );
		} else {
			return false;   # there is nothing to update...
		}

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Add a custom field to a project
	#  return true on success, false on failure or if already added
	function custom_field_link( $p_field_id, $p_project_id ) {
		$c_field_id		= db_prepare_int( $p_field_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		custom_field_ensure_exists( $p_field_id );
		project_ensure_exists( $p_project_id );

		if ( custom_field_is_linked( $p_field_id, $p_project_id ) ) {
			return false;
		}

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "INSERT INTO $t_custom_field_project_table
					( field_id, project_id )
				  VALUES
					( '$c_field_id', '$c_project_id' )";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Remove a custom field from a project
	#  return true on success, false on failure
	#
	# The values for the custom fields are not deleted.  This is to allow for the 
	# case where a bug is moved to another project that has the field, or the
	# field is linked again to the project.
	function custom_field_unlink( $p_field_id, $p_project_id ) {
		$c_field_id		= db_prepare_int( $p_field_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "DELETE FROM $t_custom_field_project_table
				  WHERE field_id = '$c_field_id'
					AND project_id = '$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Delete the field definition and all associated values and project
	#  associations
	#  return true on success, false on failure
	function custom_field_destroy( $p_field_id ) {
		$c_field_id = db_prepare_int( $p_field_id );

		# delete all values
		$t_custom_field_string_table = config_get( 'mantis_custom_field_string_table' );
		$query = "DELETE FROM
				  $t_custom_field_string_table
				  WHERE field_id='$c_field_id'";
		db_query( $query );

		# delete all project associations
		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "DELETE FROM
				  $t_custom_field_project_table
				  WHERE field_id='$c_field_id'";
		db_query( $query );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		# delete the definition
		$query = "DELETE FROM
				  $t_custom_field_table
				  WHERE id='$c_field_id'";
		db_query( $query );

		custom_field_clear_cache( $p_field_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Delete all associations of custom fields to the specified project
	#  return true on success, false on failure
	#
	# To be called from within project_delete().
	function custom_field_unlink_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		# delete all project associations
		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "DELETE FROM
				  $t_custom_field_project_table
				  WHERE project_id='$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Delete all custom values associated with the specified bug.
	#  return true on success, false on failure
	#
	# To be called from bug_delete().
	function custom_field_delete_all_values( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_custom_field_string_table = config_get( 'mantis_custom_field_string_table' );
		$query = "DELETE FROM $t_custom_field_string_table
				  WHERE bug_id='$c_bug_id'";
		db_query( $query );

		#db_query() errors on failure so:
		return true;
	}

	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return an array all custom field ids
	function custom_field_get_ids() {
		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT id FROM
				  $t_custom_field_table
				  ORDER BY name ASC";
		$result = db_query( $query );

		$t_row_count = db_num_rows( $result );
		$t_ids = array();

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );

			array_push( $t_ids, $row['id'] );
		}

		return $t_ids;
	}

	# --------------------
	# Return an array of ids of custom fields bound to the specified project
	#
	# The ids will be sorted based on the sequence number associated with the binding
	function custom_field_get_linked_ids( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT field_id FROM
				  $t_custom_field_project_table p, $t_custom_field_table f
				  WHERE p.project_id='$c_project_id'
					AND p.field_id = f.id
				  ORDER BY p.sequence ASC, f.name ASC";
		$result = db_query( $query );

		$t_row_count = db_num_rows( $result );
		$t_ids = array();

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );

			array_push( $t_ids, $row['field_id'] );
		}

		return $t_ids;
	}

	# --------------------
	# Return an array of ids of projects related to the specified custom field
	# (the array may be empty)
	function custom_field_get_project_ids( $p_field_id ) {
		$c_field_id = db_prepare_int( $p_field_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "SELECT project_id FROM
					  $t_custom_field_project_table
				  WHERE field_id = '$c_field_id'";
		$result = db_query( $query );

		$t_row_count = db_num_rows( $result );
		$t_ids = array();

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );

			array_push( $t_ids, $row['project_id'] );
		}

		return $t_ids;
	}

	# --------------------
	# Return a field definition row for the field or error if the field does
	#  not exist
	function custom_field_get_definition( $p_field_id ) {
		return custom_field_cache_row( $p_field_id );
	}

	# --------------------
	# Return a single database field from a custom field definition row
	#  for the field
	# if the database field does not exist, display a warning and return ''
	function custom_field_get_field( $p_field_id, $p_field_name ) {
		$row = custom_field_get_definition( $p_field_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# Get the value of a custom field for the given bug
	#
	# @@@ return values are unclear... should we error when access is denied
	#   and provide an api to check whether it will be?
	function custom_field_get_value( $p_field_id, $p_bug_id ) {
		$c_field_id = db_prepare_int( $p_field_id );
		$c_bug_id   = db_prepare_int( $p_bug_id );

		custom_field_ensure_exists( $p_field_id );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT access_level_r, default_value FROM
				  $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );

		$t_access_level_r	= $row['access_level_r'];
		$t_default_value	= $row['default_value'];

		if( !custom_field_has_read_access( $p_field_id, $p_bug_id, auth_get_current_user_id() ) ) {
			return false;
		}

		$t_custom_field_string_table = config_get( 'mantis_custom_field_string_table' );
		$query = "SELECT value FROM
				  $t_custom_field_string_table
				  WHERE bug_id='$c_bug_id'
					AND field_id='$c_field_id'";
		$result = db_query( $query );

		if( db_num_rows( $result ) > 0 ) {
			return db_result( $result );
		} else {
			return $t_default_value;
		}
	}

	# --------------------
	# Gets the sequence number for the specified custom field for the specified
	# project.  Returns false in case of error.
	function custom_field_get_sequence( $p_field_id, $p_project_id ) {
		$c_field_id = db_prepare_int( $p_field_id );
		$c_project_id = db_prepare_int( $p_project_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "SELECT sequence FROM
				  $t_custom_field_project_table
				  WHERE field_id='$c_field_id' AND
					project_id='$c_project_id'
					LIMIT 1";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			return false;
		}

		$t_row = db_fetch_array( $result );

		return $t_row['sequence'];
	}

	# --------------------
	# Allows the validation of a custom field value without setting it
	# or needing a bug to exist.
	function custom_field_validate( $p_field_id, $p_value ) {
		$c_field_id	= db_prepare_int( $p_field_id );
		$c_value	= db_prepare_string( $p_value );

		custom_field_ensure_exists( $p_field_id );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT name, type, possible_values, valid_regexp,
				  access_level_rw, length_min, length_max, default_value
				  FROM $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );

		$t_name			= $row['name'];
		$t_type			= $row['type'];
		$t_possible_values	= $row['possible_values'];
		$t_valid_regexp		= $row['valid_regexp'];
		$t_length_min		= $row['length_min'];
		$t_length_max		= $row['length_max'];
		$t_default_value	= $row['default_value'];

		# check for valid value
		if ( !is_blank( $t_valid_regexp ) ) {
			if ( !ereg( $t_valid_regexp, $p_value ) ) {
				return false;
			}
		}

		if ( strlen( $p_value ) < $t_length_min ) {
			return false;
		}

		if ( ( 0 != $t_length_max ) && ( strlen( $p_value ) > $t_length_max ) ) {
			return false;
		}

		return true;
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Set the value of a custom field for a given bug
	#  return true on success, false on failure
	function custom_field_set_value( $p_field_id, $p_bug_id, $p_value ) {
		$c_field_id	= db_prepare_int( $p_field_id );
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_value	= db_prepare_string( $p_value );

		custom_field_ensure_exists( $p_field_id );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT name, type, possible_values, valid_regexp,
				  access_level_rw, length_min, length_max, default_value
				  FROM $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );

		$t_name				= $row['name'];
		$t_type				= $row['type'];
		$t_possible_values	= $row['possible_values'];
		$t_valid_regexp		= $row['valid_regexp'];
		$t_access_level_rw	= $row['access_level_rw'];
		$t_length_min		= $row['length_min'];
		$t_length_max		= $row['length_max'];
		$t_default_value	= $row['default_value'];

		# check for valid value
		if ( !is_blank( $t_valid_regexp ) ) {
			if ( !ereg( $t_valid_regexp, $p_value ) ) {
				return false;
			}
		}

		if ( strlen( $p_value ) < $t_length_min ) {
			return false;
		}

		if ( ( 0 != $t_length_max ) && ( strlen( $p_value ) > $t_length_max ) ) {
			return false;
		}

		if( !custom_field_has_write_access( $p_field_id, $p_bug_id, auth_get_current_user_id() ) ) {
			return false;
		}

		$t_custom_field_string_table = config_get( 'mantis_custom_field_string_table' );

		# do I need to update or insert this value?
		$query = "SELECT value
				  FROM $t_custom_field_string_table
				  WHERE field_id='$c_field_id'
					AND bug_id='$c_bug_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			$query = "UPDATE $t_custom_field_string_table
					  SET value='$c_value'
					  WHERE field_id='$c_field_id'
						AND bug_id='$c_bug_id'";
			db_query( $query );

			$row = db_fetch_array( $result );
			history_log_event_direct( $c_bug_id, $t_name, $row['value'], $p_value);
		} else {
			# Always store the value, even if it's the dafault value
			# This is important, as the definitions might change but the
			#  values stored with a bug must not change
			$query = "INSERT INTO $t_custom_field_string_table
						( field_id, bug_id, value )
					  VALUES
						( '$c_field_id', '$c_bug_id', '$c_value' )";
			db_query( $query );
			history_log_event_direct( $c_bug_id, $t_name, '', $p_value);
		}

		custom_field_clear_cache( $p_field_id );

		#db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Sets the sequence number for the specified custom field for the specified
	# project.
	function custom_field_set_sequence( $p_field_id, $p_project_id, $p_sequence ) {
		$c_field_id = db_prepare_int( $p_field_id );
		$c_project_id = db_prepare_int( $p_project_id );
		$c_sequence = db_prepare_int( $p_sequence );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );

		$query = "UPDATE $t_custom_field_project_table
				  SET sequence='$c_sequence'
				  WHERE field_id='$c_field_id'
					AND project_id='$c_project_id'";
		$result = db_query( $query );

		custom_field_clear_cache( $p_field_id );

		return true;
	}

	#===================================
	# Output
	#===================================

	# --------------------
	# Print an input field
	# $p_field_def contains the definition of the custom field (including it's
	#              field id
	# $p_bug_id    contains the bug where this field belongs to. If it's left
	#              away, it'll default to 0 and thus belongs to a new (i.e.
	#              non-existant) bug
	# NOTE: This probably belongs in the print_api.php
	function print_custom_field_input( $p_field_def, $p_bug_id = null ) {
		$t_id = $p_field_def['id'];

		if( null == $p_bug_id ) {
			$t_custom_field_value = $p_field_def['default_value'];
		} else {
			$t_custom_field_value = custom_field_get_value( $t_id, $p_bug_id );
		}

		$t_custom_field_value = string_attribute( $t_custom_field_value );

		switch ($p_field_def['type']) {
		case CUSTOM_FIELD_TYPE_ENUM:
			echo "<select name=\"custom_field_$t_id\">";
			$t_values = explode('|', $p_field_def['possible_values']);
			foreach( $t_values as $t_option ) {
				if( $t_custom_field_value == $t_option ) {
					echo "<option value=\"$t_option\" selected>$t_option</option>";
				} else {
					echo "<option value=\"$t_option\">$t_option</option>";
				}
			}
			echo '</select>';
			break;
		case CUSTOM_FIELD_TYPE_NUMERIC:
		case CUSTOM_FIELD_TYPE_FLOAT:
		case CUSTOM_FIELD_TYPE_EMAIL:
		default:
		case CUSTOM_FIELD_TYPE_STRING:
			echo "<input type=\"text\" name=\"custom_field_$t_id\" size=\"80\"";
			if( 0 < $p_field_def['length_max'] ) {
				echo ' maxlength="' . $p_field_def['length_max'] . '"';
			} else {
				echo ' maxlength="255"';
			}
			echo " value=\"$t_custom_field_value\"></input>";
		}
	}

?>
