<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: user_pref_api.php,v 1.1 2002-10-18 23:04:30 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# User Preferences API
	###########################################################################

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_user_pref = array();

	# --------------------
	# Cache a user preferences row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the preferences can't be found.  If the second parameter is
	#  false, return false if the preferences can't be found.
	#
	# @@@ needs extension for per-project prefs
	function user_pref_cache_row( $p_user_id, $p_trigger_errors=true) {
		global $g_cache_user_pref;

		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		if ( isset ( $g_cache_user_pref[$c_user_id] ) ) {
			return $g_cache_user_pref[$c_user_id];
		}

		$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_user_pref[$c_user_id] = $row;

		return $row;
	}

	# --------------------
	# Clear the user preferences cache (or just the given id if specified)
	#
	# @@@ needs extension for per-project prefs
	function user_pref_clear_cache( $p_user_id = null ) {
		global $g_cache_user_pref;

		if ( null === $p_user_id ) {
			$g_cache_user_pref = array();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
			unset( $g_cache_user_pref[$c_user_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# return true if the user has prefs assigned for the given project,
	#  false otherwise
	function user_has_prefs( $p_user_id, $p_project_id = 0 ) {
		$c_project_id = db_prepare_int( $p_project_id );
		$c_user_id = db_prepare_int( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

	    $query = "SELECT COUNT(*)
	    		  FROM $t_user_pref_table
	    		  WHERE user_id='$c_user_id' AND project_id='$c_project_id'";
	    $result = db_query($query);
		$t_count =  db_result( $result );
		if ( $t_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# create a set of default preferences for the project
	function user_create_prefs( $p_user_id, $p_project_id = 0 ) {
		$c_user_id 		= db_prepare_int( $p_user_id );
		$c_project_id 	= db_prepare_int( $p_project_id );

		$t_user_pref_table 	= config_get( 'mantis_user_pref_table' );

		$t_default_advanced_report			= config_get( 'default_advanced_report');
		$t_default_advanced_view			= config_get( 'default_advanced_view');
		$t_default_advanced_update			= config_get( 'default_advanced_update');
		$t_default_refresh_delay			= config_get( 'default_refresh_delay');
		$t_default_redirect_delay			= config_get( 'default_redirect_delay');
		$t_default_email_on_new				= config_get( 'default_email_on_new');
		$t_default_email_on_assigned		= config_get( 'default_email_on_assigned');
		$t_default_email_on_feedback		= config_get( 'default_email_on_feedback');
		$t_default_email_on_resolved		= config_get( 'default_email_on_resolved');
		$t_default_email_on_closed			= config_get( 'default_email_on_closed');
		$t_default_email_on_reopened		= config_get( 'default_email_on_reopened');
		$t_default_email_on_bugnote			= config_get( 'default_email_on_bugnote');
		$t_default_email_on_status			= config_get( 'default_email_on_status');
		$t_default_email_on_priority		= config_get( 'default_email_on_priority');
		$t_default_language					= config_get( 'default_language');

	    $query = "INSERT
				  INTO $t_user_pref_table
				    (id, user_id, project_id,
				    advanced_report, advanced_view, advanced_update,
				    refresh_delay, redirect_delay,
				    email_on_new, email_on_assigned,
				    email_on_feedback, email_on_resolved,
				    email_on_closed, email_on_reopened,
				    email_on_bugnote, email_on_status,
				    email_on_priority, language)
				  VALUES
				    (null, '$c_user_id', '$c_project_id',
				    '$t_default_advanced_report', '$t_default_advanced_view', '$t_default_advanced_update',
				    '$t_default_refresh_delay', '$t_default_redirect_delay',
				    '$t_default_email_on_new', '$t_default_email_on_assigned',
				    '$t_default_email_on_feedback', '$t_default_email_on_resolved',
				    '$t_default_email_on_closed', '$t_default_email_on_reopened',
				    '$t_default_email_on_bugnote', '$t_default_email_on_status',
				    '$t_default_email_on_priority', '$t_default_language')";
		db_query($query);

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# delete a preferencess row
	# returns true when the prefs wer successfully deleted
	function user_delete_prefs( $p_user_id, $p_project_id = 0 ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "DELETE
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id'
				    AND project_id='$c_project_id'";
		db_query( $query );

		user_pref_clear_cache( $p_user_id );

		# db_query() errors on failure so:
		return true;
	}

	#===================================
	# Data Access
	#===================================

	# --------------------
	# return the user's preferences
	function user_get_pref_row( $p_user_id ) {
		return user_pref_cache_row( $p_user_id );
	}

	# --------------------
	# Return the specified preference field for the user id
	# If the preference can't be found try to return a defined default
	# If that fails, trigger a WARNING and return ''
	# @@@ needs extension for project-specific preferences
	function user_get_pref( $p_user_id, $p_field_name ) {
		$row = user_pref_cache_row( $p_user_id, false );

		if ( false !== $row && isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			# new accounts should have had prefs created for them but older accoutns
			#  might not yet

			# try to get a default value
			$t_default = config_get( 'default_' . $p_field_name, null );

			if ( null !== $t_default ) {
				return $t_default;
			}
		}

		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Set a user preference
	function user_set_pref( $p_user_id, $p_pref_name, $p_pref_value ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_pref_name	= db_prepare_string( $p_pref_name );
		$c_pref_value	= db_prepare_string( $p_pref_value );

		# in case the user doesn't have a prefs table yet (an old account perhaps?)
		#  create a default one now
		if ( ! user_has_prefs( $p_user_id ) ) {
			user_create_prefs( $p_user_id );
		}

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "UPDATE $t_user_pref_table
				  SET $c_pref_name='$c_pref_value'
				  WHERE user_id='$c_user_id'";

		db_query( $query );

		user_pref_clear_cache( $p_user_id );

		#db_query() errors on failure so:
		return true;
	}
?>
