<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: custom_field_api.php,v 1.57 2005-07-19 18:28:50 thraxisp Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'helper_api.php' );
	require_once( $t_core_dir . 'date_api.php' );

	### Custom Fields API ###

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
				error_parameters( 'Custom ' . $p_field_id );
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
		global $g_cache_custom_field, $g_cached_custom_field_lists;

		$g_cached_custom_field_lists = null;

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
		$query = "SELECT COUNT(*)
				FROM $t_custom_field_project_table
				WHERE field_id='$c_field_id' AND
					  project_id='$c_project_id'";
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
	# Return the type of a custom field if it exists.
	function custom_field_type( $p_field_id ) {
		$t_field = custom_field_cache_row( $p_field_id, false ) ;
		if ( $t_field == false ) {
			return -1 ;
		} else {
			return $t_field[ 'type' ] ;
		}
	}

	# --------------------
	# Check to see whether the field id is defined
	#  return true if the field is defined, error otherwise
	function custom_field_ensure_exists( $p_field_id ) {
		if ( custom_field_exists( $p_field_id ) ) {
			return true;
		} else {
			error_parameters( 'Custom ' . $p_field_id );
			trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# Check to see whether the name is unique
	#  return false if a field with the name already exists, true otherwise
	#  if an id is specified, then the corresponding record is excluded from the
	#  uniqueness test.
	function custom_field_is_name_unique( $p_name, $p_custom_field_id = null ) {
		$c_name	= db_prepare_string( $p_name );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT COUNT(*)
				  FROM $t_custom_field_table
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

        return access_has_project_level( $t_access_level_r, $t_project_id, $p_user_id );
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

        return access_has_project_level( $t_access_level_rw, $p_project_id, $p_user_id );
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
		$query = "INSERT INTO $t_custom_field_table
					( name )
				  VALUES
					( '$c_name' )";

		db_query( $query );

		return db_insert_id( $t_custom_field_table );
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
		$c_valid_regexp		= db_prepare_string( $p_def_array['valid_regexp']    );
		$c_access_level_r	= db_prepare_int(    $p_def_array['access_level_r']  );
		$c_access_level_rw	= db_prepare_int(    $p_def_array['access_level_rw'] );
		$c_length_min		= db_prepare_int(    $p_def_array['length_min']      );
		$c_length_max		= db_prepare_int(    $p_def_array['length_max']      );
		$c_advanced			= db_prepare_bool(   $p_def_array['advanced']        );
		$c_display_report	= db_prepare_bool( 	 $p_def_array['display_report'] );
		$c_display_update	= db_prepare_bool( 	 $p_def_array['display_update'] );
		$c_display_resolved	= db_prepare_bool( 	 $p_def_array['display_resolved'] );
		$c_display_closed	= db_prepare_bool( 	 $p_def_array['display_closed']   );
		$c_require_report	= db_prepare_bool( 	 $p_def_array['require_report']  );
		$c_require_update	= db_prepare_bool( 	 $p_def_array['require_update']  );
		$c_require_resolved = db_prepare_bool( 	 $p_def_array['require_resolved'] );
		$c_require_closed	= db_prepare_bool( 	 $p_def_array['require_closed']   );

		if (( is_blank( $c_name ) ) ||
			( $c_access_level_rw < $c_access_level_r ) ||
			( $c_length_min < 0 ) ||
			( ( $c_length_max != 0 ) && ( $c_length_min > $c_length_max ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_DEFINITION, ERROR );
		}

		if ( $c_advanced == true && ( $c_require_report == true || $c_require_update ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_DEFINITION, ERROR );
		}


		if ( !custom_field_is_name_unique( $c_name, $c_field_id ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
		}

		$t_update_something = false;
		$t_mantis_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "UPDATE $t_mantis_custom_field_table
				  SET ";
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
		if( array_key_exists( 'display_report', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "display_report='$c_display_report'";
		}
		if( array_key_exists( 'display_update', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "display_update='$c_display_update'";
		}
		if( array_key_exists( 'display_resolved', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "display_resolved='$c_display_resolved'";
		}
		if( array_key_exists( 'display_closed', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "display_closed='$c_display_closed'";
		}
		if( array_key_exists( 'require_report', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "require_report='$c_require_report'";
		}
		if( array_key_exists( 'require_update', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "require_update='$c_require_update'";
		}
		if( array_key_exists( 'require_resolved', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "require_resolved='$c_require_resolved'";
		}
		if( array_key_exists( 'require_closed', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "require_closed='$c_require_closed'";
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
				  WHERE field_id = '$c_field_id' AND
				  		project_id = '$c_project_id'";
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
		$query = "DELETE FROM $t_custom_field_string_table
				  WHERE field_id='$c_field_id'";
		db_query( $query );

		# delete all project associations
		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "DELETE FROM $t_custom_field_project_table
				  WHERE field_id='$c_field_id'";
		db_query( $query );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		# delete the definition
		$query = "DELETE FROM $t_custom_field_table
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
		$query = "DELETE FROM $t_custom_field_project_table
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
	# Get the id of the custom field with the specified name.
	# false is returned if no custom field found with the specified name.
	function custom_field_get_id_from_name( $p_field_name ) {
		$t_custom_field_table = config_get( 'mantis_custom_field_table' );

		$c_field_name = db_prepare_string( $p_field_name );

		$query = "SELECT id FROM $t_custom_field_table WHERE name = '$c_field_name'";
		$t_result = db_query( $query, 1 );

		if ( db_num_rows( $t_result ) == 0 ) {
			return false;
		}

		$row = db_fetch_array( $t_result );

		return $row['id'];
	}

	# --------------------
	# Return an array of ids of custom fields bound to the specified project
	#
	# The ids will be sorted based on the sequence number associated with the binding
	function custom_field_get_linked_ids( $p_project_id = ALL_PROJECTS ) {
		$t_custom_field_table			= config_get( 'mantis_custom_field_table' );
		$t_custom_field_project_table	= config_get( 'mantis_custom_field_project_table' );

		if ( ALL_PROJECTS == $p_project_id ) {
            $t_project_user_list_table = config_get( 'mantis_project_user_list_table' );
            $t_project_table = config_get( 'mantis_project_table' );
            $t_user_table = config_get( 'mantis_user_table' );
            $t_user_id = auth_get_current_user_id();
            $t_pub = VS_PUBLIC;
            $t_priv = VS_PRIVATE;
            
            $t_private_access = config_get( 'private_project_threshold' );
            if ( is_array( $t_private_access ) ) {
                if ( 1 == count( $t_private_access ) ) {
				    $t_access_clause = "= " . array_shift( $t_private_access ) . " ";
                } else {
                    $t_access_clause = "IN (" . implode( ',', $t_private_access ) . ")";
                }
            } else {
                $t_access_clause = ">= $t_private_access ";
            }			

            
            # select only the ids that the user has some access to 
            #  e.g., all fields in public projects, or private projects where the user is listed
            #    or private projects where the user is implicitly listed
            $query = "SELECT cft.id as id, cft.name as name
                FROM $t_custom_field_table as cft, $t_user_table ut, $t_project_table pt, $t_custom_field_project_table cfpt
                    LEFT JOIN $t_project_user_list_table pult 
                        on cfpt.project_id = pult.project_id and pult.user_id = $t_user_id
                WHERE cft.id = cfpt.field_id AND cfpt.project_id = pt.id AND ut.id = $t_user_id AND 
                    ( pt.view_state = $t_pub OR 
                    ( pt.view_state = $t_priv and pult.user_id = $t_user_id ) OR 
                    ( pult.user_id is null and ut.access_level $t_access_clause ) )
                GROUP BY cft.id, cft.name
                ORDER BY cft.name ASC";
		} else {
            if ( is_array( $p_project_id ) ) {
                if ( 1 == count( $p_project_id ) ) {
				    $t_project_clause = "= " . array_shift( $p_project_id ) . " ";
                } else {
                    $t_project_clause = "IN (" . implode( ',', $p_project_id ) . ")";
                }
            } else {
                $t_project_clause = "= $p_project_id ";
            }			
			$query = "SELECT cft.id, cft.name, cfpt.sequence
					  FROM $t_custom_field_table cft, $t_custom_field_project_table cfpt
					  WHERE cfpt.project_id $t_project_clause AND
							cft.id = cfpt.field_id
					  ORDER BY sequence ASC, name ASC";
		}
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
	# Return an array all custom field ids sorted by name
	function custom_field_get_ids( ) {
		$t_custom_field_table			= config_get( 'mantis_custom_field_table' );
		$query = "SELECT id, name
				  FROM $t_custom_field_table
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
	# Return an array of ids of projects related to the specified custom field
	# (the array may be empty)
	function custom_field_get_project_ids( $p_field_id ) {
		$c_field_id = db_prepare_int( $p_field_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "SELECT project_id
				  FROM $t_custom_field_project_table
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
			error_parameters( $p_field_name );
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
		$query = "SELECT access_level_r, default_value, type
				  FROM $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );

		$t_access_level_r	= $row['access_level_r'];
		$t_default_value	= $row['default_value'];

		if( !custom_field_has_read_access( $p_field_id, $p_bug_id, auth_get_current_user_id() ) ) {
			return false;
		}

		$t_custom_field_string_table = config_get( 'mantis_custom_field_string_table' );
		$query = "SELECT value
				  FROM $t_custom_field_string_table
				  WHERE bug_id='$c_bug_id' AND
				  		field_id='$c_field_id'";
		$result = db_query( $query );

		if( db_num_rows( $result ) > 0 ) {
			return custom_field_database_to_value( db_result( $result ) , $row['type'] );
		} else {
			return $t_default_value;
		}
	}

	# --------------------
	# Gets the custom fields array for the given bug readable by specified level.
	# Array keys are custom field names. Array is sorted by custom field sequence number;
	# Array items are arrays with the next keys:
	# 'type', 'value', 'access_level_r'
	function custom_field_get_linked_fields( $p_bug_id, $p_user_access_level ) {
		$t_custom_fields = custom_field_get_all_linked_fields( $p_bug_id );

		# removing restricted fields
		foreach ( $t_custom_fields as $t_custom_field_name => $t_custom_field_data ) {
			if ( $p_user_access_level < $t_custom_field_data['access_level_r'] ) {
				unset( $t_custom_fields[$t_custom_field_name] );
			}
		}
		return $t_custom_fields;
	}

	# --------------------
	# Gets the custom fields array for the given bug. Array keys are custom field names.
	# Array is sorted by custom field sequence number; Array items are arrays with the next keys:
	# 'type', 'value', 'access_level_r'
	function custom_field_get_all_linked_fields( $p_bug_id ) {
		global $g_cached_custom_field_lists;

		if ( !is_array( $g_cached_custom_field_lists ) ) {
			$g_cached_custom_field_lists = array();
		}

		# is the list in cache ?
		if( !array_key_exists( $p_bug_id, $g_cached_custom_field_lists ) ) {
			$c_bug_id     = db_prepare_int( $p_bug_id );
			$c_project_id = db_prepare_int( bug_get_field( $p_bug_id, 'project_id' ) );

			$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
			$t_custom_field_table         = config_get( 'mantis_custom_field_table' );
			$t_custom_field_string_table  = config_get( 'mantis_custom_field_string_table' );

			$query = "SELECT f.name, f.type, f.access_level_r, f.default_value, f.type, s.value
					FROM $t_custom_field_project_table p INNER JOIN $t_custom_field_table f
						ON p.field_id = f.id
					LEFT JOIN $t_custom_field_string_table s
						ON  p.field_id=s.field_id AND s.bug_id='$c_bug_id'
					WHERE   p.project_id = '$c_project_id'
					ORDER BY p.sequence ASC, f.name ASC";

			$result = db_query( $query );

			$t_row_count = db_num_rows( $result );

			$t_custom_fields = array();

			for ( $i=0 ; $i < $t_row_count ; ++$i ) {
				$row = db_fetch_array( $result );

				if( is_null( $row['value'] ) ) {
					$t_value = $row['default_value'];
				} else {
					$t_value = custom_field_database_to_value( $row['value'], $row['type'] );
				}

				$t_custom_fields[$row['name']] = array( 'type'  => $row['type'],
														  'value' => $t_value,
														  'access_level_r' => $row['access_level_r'] );
			}

			$g_cached_custom_field_lists[$p_bug_id] = $t_custom_fields;
		}

		return $g_cached_custom_field_lists[$p_bug_id];
	}


	# --------------------
	# Gets the sequence number for the specified custom field for the specified
	# project.  Returns false in case of error.
	function custom_field_get_sequence( $p_field_id, $p_project_id ) {
		$c_field_id = db_prepare_int( $p_field_id );
		$c_project_id = db_prepare_int( $p_project_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "SELECT sequence
				  FROM $t_custom_field_project_table
				  WHERE field_id='$c_field_id' AND
						project_id='$c_project_id'";
		$result = db_query( $query, 1 );

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

		$t_name				= $row['name'];
		$t_type				= $row['type'];
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

	# --------------------
	# $p_possible_values: possible values to be pre-processed.  If it has enum values,
	#                     it will be left as is.  If it has a method, it will be replaced
	#                     by the list.
	function custom_field_prepare_possible_values( $p_possible_values ) {
		$t_possible_values = $p_possible_values;

		if ( !is_blank( $t_possible_values ) && ( $t_possible_values[0] == '=' ) ) {
			$t_possible_values = helper_call_custom_function( 'enum_' . substr( $t_possible_values, 1 ), array() );
		}

		return $t_possible_values;
	}

	# --------------------
	# Get All Possible Values for a Field.
	function custom_field_distinct_values( $p_field_id, $p_project_id = ALL_PROJECTS ) {
		$c_field_id						= db_prepare_int( $p_field_id );
		$c_project_id					= db_prepare_int( $p_project_id );
		$t_custom_field_string_table	= config_get( 'mantis_custom_field_string_table' );
		$t_custom_field_table			= config_get( 'mantis_custom_field_table' );
		$t_mantis_bug_table				= config_get( 'mantis_bug_table' );
		$t_return_arr					= array();

		$query = "SELECT type, possible_values
				  FROM $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );

		$t_row_count = db_num_rows( $result );
		if ( 0 == $t_row_count ) {
			return false;
		}
		$row = db_fetch_array( $result );

		# If an enumeration type, we get all possible values, not just used values
		if ( CUSTOM_FIELD_TYPE_ENUM == $row['type'] ||
			 CUSTOM_FIELD_TYPE_CHECKBOX == $row['type'] ||
			 CUSTOM_FIELD_TYPE_LIST == $row['type'] ||
			 CUSTOM_FIELD_TYPE_MULTILIST == $row['type']
			) {
			$t_possible_values = custom_field_prepare_possible_values( $row['possible_values'] );

			$t_values_arr = explode( '|', $t_possible_values );

			foreach( $t_values_arr as $t_option ) {
				array_push( $t_return_arr, $t_option );
			}
		} else {
			$t_where = '';
			$t_from = $t_custom_field_string_table;
			if ( ALL_PROJECTS != $p_project_id ) {
				$t_where = " AND $t_mantis_bug_table.id = $t_custom_field_string_table.bug_id AND
							$t_mantis_bug_table.project_id = '$p_project_id'";
				$t_from = $t_from . ", $t_mantis_bug_table";
			}
			$query2 = "SELECT $t_custom_field_string_table.value FROM $t_from
						WHERE $t_custom_field_string_table.field_id='$c_field_id' $t_where
						GROUP BY $t_custom_field_string_table.value";
			$result2 = db_query( $query2 );
			$t_row_count = db_num_rows( $result2 );
			if ( 0 == $t_row_count ) {
				return false;
			}

			for ( $i = 0; $i < $t_row_count; $i++ ) {
				$row = db_fetch_array( $result2 );
				if( !is_blank( trim( $row['value'] ) ) ) {
					array_push( $t_return_arr, $row['value'] );
				}
			}
		}
		return $t_return_arr;
	}

	#===================================
	# Data Modification
	#===================================

 	# --------------------
	# Convert the value to save it into the database, depending of the type
	# return value for database
	function custom_field_value_to_database( $p_value, $p_type ) {
		switch ($p_type) {
		case CUSTOM_FIELD_TYPE_MULTILIST:
		case CUSTOM_FIELD_TYPE_CHECKBOX:
			if ( '' == $p_value ) {
				$result = '';
			} else {
				$result = '|' . $p_value . '|';
			}
			break;
		default:
			$result = $p_value;
		}
		return $result;
	}

	# --------------------
	# Convert the database-value to value, depending of the type
	# return value for further operation
	function custom_field_database_to_value( $p_value, $p_type ) {
		switch ($p_type) {
		case CUSTOM_FIELD_TYPE_MULTILIST:
		case CUSTOM_FIELD_TYPE_CHECKBOX:
			$result = str_replace( '||', '', '|' . $p_value . '|' );
			break;
		default:
			$result = $p_value;
		}
		return $result;
	}

	# --------------------
	# Set the value of a custom field for a given bug
	#  return true on success, false on failure
	function custom_field_set_value( $p_field_id, $p_bug_id, $p_value ) {
		$c_field_id	= db_prepare_int( $p_field_id );
		$c_bug_id	= db_prepare_int( $p_bug_id );

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

		$c_value	= db_prepare_string( custom_field_value_to_database( $p_value, $t_type ) );

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
				  WHERE field_id='$c_field_id' AND
				  		bug_id='$c_bug_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			$query = "UPDATE $t_custom_field_string_table
					  SET value='$c_value'
					  WHERE field_id='$c_field_id' AND
					  		bug_id='$c_bug_id'";
			db_query( $query );

			$row = db_fetch_array( $result );
			history_log_event_direct( $c_bug_id, $t_name, custom_field_database_to_value( $row['value'], $t_type ), $p_value );
		} else {
			# Always store the value, even if it's the dafault value
			# This is important, as the definitions might change but the
			#  values stored with a bug must not change
			$query = "INSERT INTO $t_custom_field_string_table
						( field_id, bug_id, value )
					  VALUES
						( '$c_field_id', '$c_bug_id', '$c_value' )";
			db_query( $query );
			history_log_event_direct( $c_bug_id, $t_name, '', $p_value );
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
				  WHERE field_id='$c_field_id' AND
				  		project_id='$c_project_id'";
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

		if( null === $p_bug_id ) {
			$t_custom_field_value = $p_field_def['default_value'];
		} else {
			$t_custom_field_value = custom_field_get_value( $t_id, $p_bug_id );
		}

		$t_custom_field_value = string_attribute( $t_custom_field_value );

		switch ($p_field_def['type']) {
		case CUSTOM_FIELD_TYPE_ENUM:
		case CUSTOM_FIELD_TYPE_LIST:
		case CUSTOM_FIELD_TYPE_MULTILIST:
 			$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );
			$t_list_size = $t_possible_values_count = count( $t_values );

			if ( $t_possible_values_count > 5 ) {
				$t_list_size = 5;
			}

			if ( $p_field_def['type'] == CUSTOM_FIELD_TYPE_ENUM ) {
				$t_list_size = 0;	# for enums the size is 0
			}

			if ( $p_field_def['type'] == CUSTOM_FIELD_TYPE_MULTILIST ) {
				echo '<select name="custom_field_' . $t_id . '[]" size="' . $t_list_size . '" multiple>';
			} else {
				echo '<select name="custom_field_' . $t_id . '" size="' . $t_list_size . '">';
			}

			$t_selected_values = explode( '|', $t_custom_field_value );
 			foreach( $t_values as $t_option ) {
				if( in_array( $t_option, $t_selected_values ) ) {
 					echo '<option value="' . $t_option . '" selected> ' . $t_option . '</option>';
 				} else {
 					echo '<option value="' . $t_option . '">' . $t_option . '</option>';
 				}
 			}
 			echo '</select>';
			break;
		case CUSTOM_FIELD_TYPE_CHECKBOX:
			$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );
			$t_checked_values = explode( '|', $t_custom_field_value );
			foreach( $t_values as $t_option ) {
				echo '<input type="checkbox" name="custom_field_' . $t_id . '[]"';
				if( in_array( $t_option, $t_checked_values ) ) {
					echo ' value="' . $t_option . '" checked>&nbsp;' . $t_option . '&nbsp;&nbsp;';
				} else {
					echo ' value="' . $t_option . '">&nbsp;' . $t_option . '&nbsp;&nbsp;';
				}
			}
 			break;
		case CUSTOM_FIELD_TYPE_NUMERIC:
		case CUSTOM_FIELD_TYPE_FLOAT:
		case CUSTOM_FIELD_TYPE_EMAIL:
		case CUSTOM_FIELD_TYPE_STRING:
			echo '<input type="text" name="custom_field_' . $t_id . '" size="80"';
			if( 0 < $p_field_def['length_max'] ) {
				echo ' maxlength="' . $p_field_def['length_max'] . '"';
			} else {
				echo ' maxlength="255"';
			}
			echo ' value="' . $t_custom_field_value .'"></input>';
			break ;

		case CUSTOM_FIELD_TYPE_DATE:
			print_date_selection_set("custom_field_" . $t_id, config_get('short_date_format'), $t_custom_field_value, false, true) ;
			break ;
		}
	}

	# --------------------
	# Prepare a string containing a custom field value for display
	# $p_def 		contains the definition of the custom field
	# $p_field_id 	contains the id of the field
	# $p_bug_id		contains the bug id to display the custom field value for
	# NOTE: This probably belongs in the string_api.php
	function string_custom_field_value( $p_def, $p_field_id, $p_bug_id ) {
		$t_custom_field_value = custom_field_get_value( $p_field_id, $p_bug_id );
		switch( $p_def['type'] ) {
			case CUSTOM_FIELD_TYPE_EMAIL:
				return "<a href=\"mailto:$t_custom_field_value\">$t_custom_field_value</a>";
				break;
			case CUSTOM_FIELD_TYPE_ENUM:
			case CUSTOM_FIELD_TYPE_LIST:
			case CUSTOM_FIELD_TYPE_MULTILIST:
			case CUSTOM_FIELD_TYPE_CHECKBOX:
				return str_replace( '|', ', ', $t_custom_field_value );
				break;
			case CUSTOM_FIELD_TYPE_DATE:
				if ($t_custom_field_value != null) {
					return date( config_get( 'short_date_format'), $t_custom_field_value) ;
				}
				break ;
			default:
				return string_display_links( $t_custom_field_value );
		}
	}

	# --------------------
	# Print a custom field value for display
	# $p_def 		contains the definition of the custom field
	# $p_field_id 	contains the id of the field
	# $p_bug_id		contains the bug id to display the custom field value for
	# NOTE: This probably belongs in the print_api.php
	function print_custom_field_value( $p_def, $p_field_id, $p_bug_id ) {
		echo string_custom_field_value( $p_def, $p_field_id, $p_bug_id );
	}

	# --------------------
	# Prepare a string containing a custom field value for email
	# $p_value		value of custom field
	# $p_type		type of custom field
	# NOTE: This probably belongs in the string_api.php
	function string_custom_field_value_for_email( $p_value, $p_type ) {
		switch( $p_type ) {
			case CUSTOM_FIELD_TYPE_EMAIL:
				return 'mailto:'.$p_value;
				break;
			case CUSTOM_FIELD_TYPE_ENUM:
			case CUSTOM_FIELD_TYPE_LIST:
			case CUSTOM_FIELD_TYPE_MULTILIST:
			case CUSTOM_FIELD_TYPE_CHECKBOX:
				return str_replace( '|', ', ', $p_value );
				break;
			case CUSTOM_FIELD_TYPE_DATE:
				if ($p_value != null) {
					return date( config_get( 'short_date_format' ), $p_value) ;
				}
				break ;
			default:
				return $p_value;
		}
		return $p_value;
	}

?>
