<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: custom_field_api.php,v 1.5 2002-12-05 07:23:30 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Custom Fields API
	###########################################################################

#*******************************************
#	NOTE
#
#	This API is in progress and will likely not be completed for 0.18
#
#	TODO
#	- add an object to store field data like BugData and UserPrefs ?
#	- add caching functions like user, bug, etc
#	- make existing api functions use caching functions
#	- add functions to return individual db fields of a field definition
#
#	DB SCHEMA
#	The following is the current proposed DB schema to go with this API
/*
CREATE TABLE mantis_custom_field_table (
  id int(3) NOT NULL auto_increment,
  name varchar(64) NOT NULL default '',
  type int(2) NOT NULL default '0',
  possible_values varchar(255) NOT NULL default '',
  default_value varchar(255) NOT NULL default '',
  valid_regexp varchar(255) NOT NULL default '',
  access_level_r int(2) NOT NULL default '0',
  access_level_rw int(2) NOT NULL default '0',
  length_min int(3) NOT NULL default '0',
  length_max int(3) NOT NULL default '0',
  advanced int(1) NOT NULL default '0',
  sequence int(2) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY name (name)
) TYPE=MyISAM COMMENT='Field definitions';

CREATE TABLE mantis_custom_field_string_table (
  field_id int(3) NOT NULL,
  bug_id int(7) NOT NULL,
  value varchar(255) NOT NULL default '',
  PRIMARY KEY (field_id,bug_id)
) TYPE=MyISAM COMMENT='Field values of type string';

CREATE TABLE mantis_custom_field_project_table (
  field_id int(3) NOT NULL,
  project_id int(7) unsigned NOT NULL,
  PRIMARY KEY (field_id,project_id)
) TYPE=MyISAM COMMENT='Definitions of which fields are available in each project';
*/
#*******************************************

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check to see whether the field is included in the given project
	#  return true if the field is included, false otherwise
	function custom_field_in_project( $p_field_id, $p_project_id ) {
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
		$c_field_id		= db_prepare_int( $p_field_id );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT COUNT(*) FROM
				  $t_custom_field_table
				  WHERE id='$c_field_id'";
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
	#  return true if the field is defined, error otherwise
	function custom_field_ensure_exists( $p_field_id )
	{
		if ( custom_field_exists( $p_field_id ) ) {
			return true;
		} else {
			trigger_error( ERROR_CUSTOM_FIELD_DOES_NOT_EXIST, ERROR );
		}
	}

	# --------------------
	# Check to see whether the name is unique
	#  return false if a field with the name already exists, true otherwise
	function custom_field_is_name_unique( $p_name ) {
		$c_name		= db_prepare_string( $p_name );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT COUNT(*) FROM
				  $t_custom_field_table
				  WHERE name='$c_name'";
		$result = db_query( $query );

		$count = db_result( $result );

		if ( $count > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Return true if the user can read the value of the field for the given bug,
	#  false otherwise.
	function custom_field_has_read_access( $p_field_id, $p_bug_id, $p_user_id ) {
		custom_field_ensure_exists( $p_field_id );

		#@@@ get the read access level for the field - we need a function to get fields

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		if ( user_get_access_level( $p_user_id, $t_project_id ) >= $t_access_level_r ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Return true if the user can modify the value of the field for the given bug,
	#  false otherwise.
	function custom_field_has_write_access( $p_field_id, $p_bug_id, $p_user_id ) {
		custom_field_ensure_exists( $p_field_id );

		#@@@ get the read access level for the field - we need a function to get fields

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		if ( user_get_access_level( $p_user_id, $t_project_id ) >= $t_access_level_rw ) {
			return true;
		} else {
			return false;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# create a new custom field with the name $p_name
	# the definition are the default values and can be changes later
	# return the ID of the new definition
	function custom_field_create( $p_name ) {
		$c_name = db_prepare_string( $p_name );

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
	# bind a custom field with the id $p_field_id to the
	# project $p_project_id
	# return true on success
	# return false if an error occures (e.g. non existing id)
	function custom_field_bind( $p_field_id, $p_project_id ) {
		custom_field_ensure_exists( $p_field_id );
		project_ensure_exists( $p_project_id );

		if( custom_field_in_project( $p_field_id, $p_project_id ) ) {
			return false;
		}
		$c_field_id   = db_prepare_int( $p_field_id );
		$c_project_id = db_prepare_int( $p_project_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$query = "INSERT INTO
				  $t_custom_field_project_table
					( field_id, project_id )
				  VALUES
					( '$c_field_id', '$c_project_id' )";

		db_query( $query );
		# db_qery returns false if there's a problem

		return true;
	}

	# --------------------
	# Update the field definition
	#  return true on success, false on failure
	function custom_field_update( $p_field_id, $p_def_array ) {
		$c_field_id			= db_prepare_int( $p_field_id );
		$c_name			= db_prepare_string( $p_def_array['name']         );
		$c_type				= db_prepare_int(    $p_def_array['type']            );
		$c_possible_values	= db_prepare_string( $p_def_array['possible_values'] );
		$c_default_value	= db_prepare_string( $p_def_array['default_value']   );
		$c_valid_regexp		= db_prepare_string( $p_def_array['valid_regexp']      );
		$c_access_level_r	= db_prepare_int(    $p_def_array['access_level_r']  );
		$c_access_level_rw	= db_prepare_int(    $p_def_array['access_level_rw'] );
		$c_length_min		= db_prepare_int(    $p_def_array['length_min']      );
		$c_length_max		= db_prepare_int(    $p_def_array['length_max']      );
		$c_advanced			= db_prepare_int(    $p_def_array['advanced']        );
		$c_sequence			= db_prepare_int(    $p_def_array['sequence']        );

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
		if( array_key_exists( 'sequence', $p_def_array ) ) {
			if ( !$t_update_something ) {
				$t_update_something = true;
			} else {
				$query .= ', ';
			}
			$query .= "sequence='$c_sequence'";
		}

		$query .= " WHERE id='$c_field_id'";

		if( $t_update_something ) {
			db_query( $query );
		} else {
			return false;   # there is nothing to update...
		}

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Add a custom field to a project
	#  return true on success, false on failure
	function custom_field_add( $p_field_id, $p_project_id ) {
		$c_field_id		= db_prepare_int( $p_field_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		if ( custom_field_in_project( $p_field_id, $p_project_id ) ) {
			return true;
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
	function custom_field_remove( $p_field_id, $p_project_id ) {
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
	function custom_field_delete( $p_field_id ) {
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

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Delete all associations of custom fields to the specified project
	#  return true on success, false on failure
	#
	# To be called from within project_delete().
	function custom_field_delete_associations( $p_project_id ) {
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
	# Return an array of ids of custom fields related to the specified project
	# if the project_id parameter is empty (or 0) return all existing custom
	# field ids  (the array may be empty)
	function custom_field_get_ids( $p_project_id = 0 ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_custom_field_project_table = config_get( 'mantis_custom_field_project_table' );
		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		if( 0 == $p_project_id ) {
			$query = "SELECT id as field_id FROM
					  $t_custom_field_table";
		} else {
			$query = "SELECT field_id FROM
					  $t_custom_field_project_table p, $t_custom_field_table f
					  WHERE p.project_id='$c_project_id'
						AND p.field_id = f.id
					  ORDER BY f.sequence ASC";
		}
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
		$c_field_id = db_prepare_int( $p_field_id );

		$t_custom_field_table = config_get( 'mantis_custom_field_table' );
		$query = "SELECT * FROM
				  $t_custom_field_table
				  WHERE id='$c_field_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) < 1 ) {
			trigger_error( ERROR_CUSTOM_FIELD_NOT_FOUND, ERROR );
		}

		return db_fetch_array( $result );
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

		if( !custom_field_has_read_access( $p_field_id, $p_bug_id, auth_get_current_user_id() ) ) {
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
			history_log_event_direct( $c_bug_id, $t_name, $row['value'], $c_value);
		} else {
			# Always store the value, even if it's the dafault value
			# This is important, as the definitions might change but the
			#  values stored with a bug must not change
			$query = "INSERT INTO $t_custom_field_string_table
						( field_id, bug_id, value )
					  VALUES
						( '$c_field_id', '$c_bug_id', '$c_value' )";
			db_query( $query );
		}

		#db_query() errors on failure so:
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
	function print_custom_field_input( $p_field_def, $p_bug_id = 0) {
		$t_id = $p_field_def['id'];

		if( 0 == $p_bug_id ) {
			$t_custom_field_value = $p_field_def['default_value'];
		} else {
			$t_custom_field_value = custom_field_get_value( $t_id, $p_bug_id );
		}
		switch ($p_field_def['type']) {
		case CUSTOM_FIELD_TYPE_ENUM:
			echo "<select name=\"f_custom_field_$t_id\">";
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
			echo "<input type=\"text\" name=\"f_custom_field_$t_id\" size=\"80\"";
			if( 0 < $p_field_def['length_max'] ) {
				echo ' maxlength="' . $p_field_def['length_max'] . '"';
			} else {
				echo ' maxlength="255"';
			}
			echo " value=\"$t_custom_field_value\"></input>";
		}
	}

?>