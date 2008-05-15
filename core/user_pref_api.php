<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: user_pref_api.php,v 1.32.14.1 2007-10-13 22:35:47 giallu Exp $
	# --------------------------------------------------------

	### User Preferences API ###
	$g_default_mapping = array(
		'default_profile' => 'default_profile',
		'default_project' => 'default_project',
		'advanced_report' => 'default_advanced_report',
		'advanced_view' => 'default_advanced_view',
		'advanced_update' => 'default_advanced_update',
		'refresh_delay' => 'default_refresh_delay',
		'redirect_delay' => 'default_redirect_delay',
		'bugnote_order' => 'default_bugnote_order',
		'email_on_new' => 'default_email_on_new',
		'email_on_assigned' => 'default_email_on_assigned',
		'email_on_feedback' => 'default_email_on_feedback',
		'email_on_resolved' => 'default_email_on_resolved',
		'email_on_closed' => 'default_email_on_closed',
		'email_on_reopened' => 'default_email_on_reopened',
		'email_on_bugnote' => 'default_email_on_bugnote',
		'email_on_status' => 'default_email_on_status',
		'email_on_priority' => 'default_email_on_priority',
		'email_on_new_min_severity' => 'default_email_on_new_minimum_severity',
		'email_on_assigned_min_severity' => 'default_email_on_assigned_minimum_severity',
		'email_on_feedback_min_severity' => 'default_email_on_feedback_minimum_severity',
		'email_on_resolved_min_severity' => 'default_email_on_resolved_minimum_severity',
		'email_on_closed_min_severity' => 'default_email_on_closed_minimum_severity',
		'email_on_reopened_min_severity' => 'default_email_on_reopened_minimum_severity',
		'email_on_bugnote_min_severity' => 'default_email_on_bugnote_minimum_severity',
		'email_on_status_min_severity' => 'default_email_on_status_minimum_severity',
		'email_on_priority_min_severity' => 'default_email_on_priority_minimum_severity',
		'email_bugnote_limit' => 'default_email_bugnote_limit',
		'language' => 'default_language'
		);

	#===================================
	# Preference Structure Definition
	#===================================
	class UserPreferences {
		var $default_profile = NULL;
		var $default_project = NULL;
		var $advanced_report = NULL;
		var $advanced_view = NULL;
		var $advanced_update = NULL;
		var $refresh_delay = NULL;
		var $redirect_delay = NULL;
		var $bugnote_order = NULL;
		var $email_on_new = NULL;
		var $email_on_assigned = NULL;
		var $email_on_feedback = NULL;
		var $email_on_resolved = NULL;
		var $email_on_closed = NULL;
		var $email_on_reopened = NULL;
		var $email_on_bugnote = NULL;
		var $email_on_status = NULL;
		var $email_on_priority = NULL;
		var $email_on_new_min_severity = NULL;
		var $email_on_assigned_min_severity = NULL;
		var $email_on_feedback_min_severity = NULL;
		var $email_on_resolved_min_severity = NULL;
		var $email_on_closed_min_severity = NULL;
		var $email_on_reopened_min_severity = NULL;
		var $email_on_bugnote_min_severity = NULL;
		var $email_on_status_min_severity = NULL;
		var $email_on_priority_min_severity = NULL;
		var $email_bugnote_limit = NULL;
		var $language = NULL;

		function UserPreferences() {
			$this->default_profile                   	= 0;
			$this->default_project              	 	= ALL_PROJECTS;
		}

		function Get( $t_string ) {
			global $g_default_mapping;
			if( is_null( $this->{$t_string} ) ) {
				$this->{$t_string} = config_get( $g_default_mapping[$t_string] );
			}
			return $this->{$t_string} ;
 		}
	}

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on

	$g_cache_user_pref = array();

	# --------------------
	# Cache a user preferences row if necessary and return the cached copy
	#  If the third parameter is true (default), trigger an error
	#  if the preferences can't be found.  If the second parameter is
	#  false, return false if the preferences can't be found.
	function user_pref_cache_row( $p_user_id, $p_project_id = ALL_PROJECTS, $p_trigger_errors = true) {
		global $g_cache_user_pref;

		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		if ( isset ( $g_cache_user_pref[$c_user_id][$c_project_id] ) ) {
			return $g_cache_user_pref[$c_user_id][$c_project_id];
		}

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id' AND project_id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
			} else {
				$g_cache_user_pref[$c_user_id][$c_project_id] = false;
				return false;
			}
		}

		$row = db_fetch_array( $result );

		if ( !isset( $g_cache_user_pref[$c_user_id] ) ) {
			$g_cache_user_pref[$c_user_id] = array();
		}

		$g_cache_user_pref[$c_user_id][$c_project_id] = $row;

		return $row;
	}

	# --------------------
	# Clear the user preferences cache (or just the given id if specified)
	function user_pref_clear_cache( $p_user_id = null, $p_project_id = null ) {
		global $g_cache_user_pref;

		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		if ( null === $p_user_id ) {
			$g_cache_user_pref = array();
		} else if ( null === $p_project_id ) {
			unset( $g_cache_user_pref[$c_user_id] );
		} else {
			unset( $g_cache_user_pref[$c_user_id][$c_project_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# return true if the user has prefs assigned for the given project,
	#  false otherwise
	#
	# Trying to get the row shouldn't be any slower in the DB than getting COUNT(*)
	#  and the transfer time is negligable.  So we try to cache the row - it could save
	#  us another query later.
	function user_pref_exists( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		if ( false === user_pref_cache_row( $p_user_id, $p_project_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# perform an insert of a preference object into the DB
	#
	# Also see the higher level user_pref_set() and user_pref_set_default()
	function user_pref_insert( $p_user_id, $p_project_id, $p_prefs ) {
		$c_user_id 		= db_prepare_int( $p_user_id );
		$c_project_id 	= db_prepare_int( $p_project_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table 	= config_get( 'mantis_user_pref_table' );

		$t_vars		= get_object_vars( $p_prefs );
		$t_values	= array();

		foreach ( $t_vars as $var => $val ) {
			array_push( $t_values, '\'' . db_prepare_string( $p_prefs->Get( $var ) ) . '\'' );
		}

		$t_vars_string		= implode( ', ', array_keys( $t_vars ) );
		$t_values_string	= implode( ', ', $t_values );

	    $query = "INSERT INTO $t_user_pref_table
				    (user_id, project_id, $t_vars_string)
				  VALUES
				    ('$c_user_id', '$c_project_id', $t_values_string)";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# perform an update of a preference object into the DB
	#
	# Also see the higher level user_pref_set() and user_pref_set_default()
	function user_pref_update( $p_user_id, $p_project_id, $p_prefs ) {
		$c_user_id 		= db_prepare_int( $p_user_id );
		$c_project_id 	= db_prepare_int( $p_project_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table	= config_get( 'mantis_user_pref_table' );
		$t_vars				= get_object_vars( $p_prefs );

		$t_pairs = array();

		foreach ( $t_vars as $var => $val ) {
			if( is_bool( $p_prefs->$var ) ) {
				array_push( $t_pairs, "$var = " . db_prepare_bool( $p_prefs->Get( $var ) ) );
			} else if( is_int( $p_prefs->$var ) ) {
				array_push( $t_pairs, "$var = " . db_prepare_int( $p_prefs->Get( $var ) ) );
			} else {
				array_push( $t_pairs, "$var = '" . db_prepare_string( $p_prefs->Get( $var ) ) . '\'' );
			}
		}

		$t_pairs_string = implode( ', ', $t_pairs );

	    $query = "UPDATE $t_user_pref_table
				  SET $t_pairs_string
				  WHERE user_id=$c_user_id AND project_id=$c_project_id";
		db_query( $query );

		user_pref_clear_cache( $p_user_id, $p_project_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# delete a preferencess row
	# returns true if the prefs were successfully deleted
	function user_pref_delete( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "DELETE FROM $t_user_pref_table
				  WHERE user_id='$c_user_id' AND
				  		project_id='$c_project_id'";
		db_query( $query );

		user_pref_clear_cache( $p_user_id, $p_project_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# delete all preferences for a user in all projects
	# returns true if the prefs were successfully deleted
	#
	# It is far more efficient to delete them all in one query than to
	#  call user_pref_delete() for each one and the code is short so that's
	#  what we do
	function user_pref_delete_all( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "DELETE FROM $t_user_pref_table
				  WHERE user_id='$c_user_id'";
		db_query( $query );

		user_pref_clear_cache( $p_user_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# delete all preferences for a project for all users (part of deleting the project)
	# returns true if the prefs were successfully deleted
	#
	# It is far more efficient to delete them all in one query than to
	#  call user_pref_delete() for each one and the code is short so that's
	#  what we do
	function user_pref_delete_project( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "DELETE FROM $t_user_pref_table
				  WHERE project_id='$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# return the user's preferences
	# @@@ (this should be a private interface as it doesn't have the benefit of applying
	#  global defaults before returning values.
	function user_pref_get_row( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		return user_pref_cache_row( $p_user_id, $p_project_id );
	}

	# --------------------
	# return the user's preferences in a UserPreferences object
	function user_pref_get( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		global $g_default_mapping;

		$t_prefs = new UserPreferences;

		$row = user_pref_cache_row( $p_user_id, $p_project_id, false );

		# If the user has no preferences for the given project
		if ( false === $row ) {
			if ( ALL_PROJECTS != $p_project_id ) {
				# Try to get the prefs for ALL_PROJECTS (the defaults)
				$row = user_pref_cache_row( $p_user_id, ALL_PROJECTS, false );
			}

			# If $row is still false (the user doesn't have default preferences)
			if ( false === $row ) {
				# We use an empty array
				$row = array();
			}
		}

		$t_row_keys	= array_keys( $row );
		$t_vars		= get_object_vars( $t_prefs );

		# Check each variable in the class
		foreach ( $t_vars as $var => $val ) {
			# If we got a field from the DB with the same name
			if ( in_array( $var, $t_row_keys, true ) ) {
				# Store that value in the object
				$t_prefs->$var = $row[$var];
			} else {
				$t_prefs->$var = $t_prefs->Get( $var );
			}
		}

		return $t_prefs;
	}

	# --------------------
	# Return the specified preference field for the user id
	# If the preference can't be found try to return a defined default
	# If that fails, trigger a WARNING and return ''
	function user_pref_get_pref( $p_user_id, $p_pref_name, $p_project_id = ALL_PROJECTS ) {
		$t_prefs = user_pref_get( $p_user_id, $p_project_id );

		$t_vars = get_object_vars( $t_prefs );

		if ( in_array( $p_pref_name, array_keys( $t_vars ), true ) ) {
			return $t_prefs->Get( $p_pref_name );
		} else {
			error_parameters( $p_pref_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# returns user language
	function user_pref_get_language( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_prefs = user_pref_get( $p_user_id, $p_project_id );
		
		// ensure the language is a valid one
		$t_lang = $t_prefs->language;
		if ( ! lang_language_exists( $t_lang ) ) {
			$t_lang = false;
		}
		return $t_lang;
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Set a user preference
	#
	# By getting the prefs for the project first we deal fairly well with defaults.
	#  If there are currently no prefs for that project, the ALL_PROJECTS prefs will
	#  be returned so we end up storing a new set of prefs for the given project
	#  based on the prefs for ALL_PROJECTS.  If there isn't even an entry for
	#  ALL_PROJECTS, we'd get returned a default UserPreferences object to modify.
	function user_pref_set_pref( $p_user_id, $p_pref_name, $p_pref_value, $p_project_id = ALL_PROJECTS ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_pref_name	= db_prepare_string( $p_pref_name );
		$c_pref_value	= db_prepare_string( $p_pref_value );
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_prefs = user_pref_get( $p_user_id, $p_project_id );

		$t_prefs->$p_pref_name = $p_pref_value;

		user_pref_set( $p_user_id, $t_prefs, $p_project_id );

		return true;
	}

	# --------------------
	# set the user's preferences for the project from the given preferences object
	#  Do the work by calling user_pref_update() or user_pref_insert() as appropriate
	function user_pref_set( $p_user_id, $p_prefs, $p_project_id = ALL_PROJECTS ) {
		if ( user_pref_exists( $p_user_id, $p_project_id ) ) {
			return user_pref_update( $p_user_id, $p_project_id, $p_prefs );
		} else {
			return user_pref_insert( $p_user_id, $p_project_id, $p_prefs );
		}
	}

	# --------------------
	# create a set of default preferences for the project
	function user_pref_set_default( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		# get a default preferences object
		$t_prefs = new UserPreferences();

		return user_pref_set( $p_user_id, $t_prefs, $p_project_id );
	}
?>
