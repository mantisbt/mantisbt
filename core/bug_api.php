<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_api.php,v 1.16 2002-10-27 22:53:04 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Bug API
	###########################################################################

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_bug = array();
	$g_cache_bug_text = array();
	
	# Cache a bug row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the bug can't be found.  If the second parameter is
	#  false, return false if the bug can't be found.
	function bug_cache_row( $p_bug_id, $p_trigger_errors=true) {
		global $g_cache_bug;

		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( isset ( $g_cache_bug[$c_bug_id] ) ) {
			return $g_cache_bug[$c_bug_id];
		}

		$query = "SELECT *
				  FROM $t_bug_table
				  WHERE id='$c_bug_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_bug[$c_bug_id] = $row;

		return $row;
	}
	# --------------------
	# Pads the bug id with the appropriate number of zeros.
	function bug_format_id( $p_bug_id ) {
		return( str_pad( $p_bug_id, 7, '0', STR_PAD_LEFT ) );
	}
	# --------------------
	# Clear the bug cache (or just the given id if specified)
	function bug_clear_cache( $p_bug_id = null ) {
		global $g_cache_bug;

		if ( null === $p_bug_id ) {
			$g_cache_bug = array();
		} else {
			$c_bug_id = db_prepare_int( $p_bug_id );
			unset( $g_cache_bug[$c_bug_id] );
		}

		return true;
	}
	# Cache a bug text row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the bug text can't be found.  If the second parameter is
	#  false, return false if the bug text can't be found.
	function bug_text_cache_row( $p_bug_id, $p_trigger_errors=true) {
		global $g_cache_bug_text;

		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_bug_text_table = config_get( 'mantis_bug_text_table' );

		if ( isset ( $g_cache_bug_text[$c_bug_id] ) ) {
			return $g_cache_bug_text[$c_bug_id];
		}

		$query = "SELECT bt.*
				  FROM $t_bug_text_table bt, $t_bug_table b
				  WHERE b.id='$c_bug_id'
				    AND b.bug_text_id = bt.id";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_bug_text[$c_bug_id] = $row;

		return $row;
	}
	# --------------------
	# Clear the bug text cache (or just the given id if specified)
	function bug_text_clear_cache( $p_bug_id = null ) {
		global $g_cache_bug_text;

		if ( null === $p_bug_id ) {
			$g_cache_bug_text = array();
		} else {
			$c_bug_id = db_prepare_int( $p_bug_id );
			unset( $g_cache_bug_text[$c_bug_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# check to see if bug exists by id
	# return true if it does, false otherwise
	function bug_exists( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_table = config_get( 'mantis_bug_table' );

		$query = "SELECT COUNT(*)
				  FROM $t_bug_table
				  WHERE id='$c_bug_id'";
		$result = db_query( $query );

		if ( db_result( $result ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if bug exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function bug_ensure_exists( $p_bug_id ) {
		if ( ! bug_exists( $p_bug_id ) ) {
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Create a new bug and return the bug id
	#
	# @@@ pass in a bug object instead of all these params ??
	function bug_create( $p_project_id,
				$p_reporter_id, $p_handler_id,
				$p_priority,
				$p_severity, $p_reproducibility,
				$p_category,
				$p_os, $p_os_build,
				$p_platform, $p_version,
				$p_build, 
				$p_profile_id, $p_summary, $p_view_state,
				$p_description, $p_steps_to_reproduce, $p_additional_info ) {

		$c_project_id			= db_prepare_int( $p_project_id );
		$c_reporter_id			= db_prepare_int( $p_reporter_id );
		$c_handler_id			= db_prepare_int( $p_handler_id );
		$c_priority				= db_prepare_int( $p_priority );
		$c_severity				= db_prepare_int( $p_severity );
		$c_reproducibility		= db_prepare_int( $p_reproducibility );
		$c_category				= db_prepare_string( $p_category );
		$c_os					= db_prepare_string( $p_os );
		$c_os_build				= db_prepare_string( $p_os_build );
		$c_platform				= db_prepare_string( $p_platform );
		$c_version				= db_prepare_string( $p_version );
		$c_build				= db_prepare_string( $p_build );
		$c_profile_id			= db_prepare_int( $p_profile_id );
		$c_summary				= db_prepare_string( $p_summary );
		$c_view_state			= db_prepare_int( $p_view_state );
		$c_description			= db_prepare_string( $p_description );
		$c_steps_to_reproduce	= db_prepare_string( $p_steps_to_reproduce );
		$c_additional_info		= db_prepare_string( $p_additional_info );

		$t_bug_text_table = config_get( 'mantis_bug_text_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_project_category_table = config_get( 'mantis_project_category_table' );

		# Insert text information
		$query = "INSERT
				  INTO $t_bug_text_table
				    ( id, description, steps_to_reproduce, additional_information )
				  VALUES
				    ( null, '$c_description', '$c_steps_to_reproduce',
				      '$c_additional_info' )";
		db_query( $query );

		# Get the id of the text information we just inserted
		# NOTE: this is guarranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_text_id = db_insert_id();

		# check to see if we want to assign this right off
		$t_status = NEW_;

		# if not assigned, check if it should auto-assigned.
		if ( 0 == $c_handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$query = "SELECT user_id
					  FROM $t_project_category_table
					  WHERE project_id='$c_project_id' AND category='$c_category'";

			$result = db_query( $query );

			if ( db_num_rows( $result ) > 0 ) {
				$c_handler_id = $p_handler_id = db_result( $result );
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if ( ( $c_handler_id != 0 ) && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
			$t_status = ASSIGNED;
		}

		# Insert the rest of the data
		$t_resolution = OPEN;

		$query = "INSERT
				  INTO $t_bug_table
				    ( id, project_id,
				      reporter_id, handler_id,
				      duplicate_id, priority,
				      severity, reproducibility,
				      status, resolution,
				      projection, category,
				      date_submitted, last_updated,
				      eta, bug_text_id,
				      os, os_build,
				      platform, version,
				      build, votes,
				      profile_id, summary, view_state )
				  VALUES
				    ( null, '$c_project_id',
				      '$c_reporter_id', '$c_handler_id',
				      '0', '$c_priority',
				      '$c_severity', '$c_reproducibility',
				      '$t_status', '$t_resolution',
				      10, '$c_category',
				      NOW(), NOW(),
				      10, '$t_text_id',
				      '$c_os', '$c_os_build',
				      '$c_platform', '$c_version',
				      '$c_build', 1,
				      '$c_profile_id', '$c_summary', '$c_view_state' )";
		db_query( $query );

		$t_bug_id = db_insert_id();

		# log new bug
		history_log_event_special( $t_bug_id, NEW_BUG );

		email_new_bug( $t_bug_id );

		return $t_bug_id;
	}
	# --------------------
	# allows bug deletion :
	# delete the bug, bugtext, bugnote, and bugtexts selected
	# used in bug_delete.php & mass treatments
	function bug_delete( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_bug_text_table = config_get( 'mantis_bug_text_table' );

		email_bug_deleted( $p_bug_id );

		# Delete bugnotes
		bugnote_delete_all( $p_bug_id );

		# Delete files
		file_delete_attachments( $p_bug_id );

		# Delete the bug history
		history_delete( $p_bug_id );

		# Delete the bugnote text
		$t_bug_text_id = bug_get_field( $p_bug_id, 'bug_text_id' );

		$query = "DELETE
				  FROM $t_bug_text_table
				  WHERE id='$t_bug_text_id'";
		db_query( $query );

		# Delete the bug entry
		$query = "DELETE
				  FROM $t_bug_table
				  WHERE id='$c_bug_id'";
		db_query( $query );

		bug_clear_cache( $p_bug_id );
		bug_text_clear_cache( $p_bug_id );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Delete all bugs associated with a project
	function bug_delete_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_bug_table = config_get( 'mantis_bug_table' );

		$query = "SELECT id
				  FROM $t_bug_table
				  WHERE project_id='$c_project_id'";
		$result = db_query( $query );

		$bug_count = db_num_rows( $result );

		for ( $i=0 ; $i < $bug_count ; $i++ ) {	
			$row = db_fetch_array( $result );

			bug_delete( $row['id'] );
		}

		# @@@ should we check the return value of each bug_delete() and 
		#  return false if any of them return false? Presumable bug_delete()
		#  will eventually trigger an error on failure so it won't matter...

		return true;
	}
	# --------------------
	function bug_update() {
	}

	#===================================
	# Data Access
	#===================================

	# Returns the extended record of the specified bug, this includes
	# the bug text fields
	# @@@ include reporter name and handler name, the problem is that
	#      handler can be 0, in this case no corresponding name will be
	#      found.  Use equivalent of (+) in Oracle.
	function bug_get_extended_row( $p_bug_id ) {
		$t_base = bug_cache_row( $p_bug_id );
		$t_text = bug_text_cache_row( $p_bug_id );

		# merge $t_text first so that the 'id' key has the bug id not the bug text id
		return array_merge( $t_text, $t_base );
	}
	# --------------------
	# Returns the record of the specified bug
	function bug_get_row( $p_bug_id ) {
		return bug_cache_row( $p_bug_id );
	}
	# --------------------
	# return the specified field of the given bug
	#  if the field does not exist, display a warning and return ''
	function bug_get_field( $p_bug_id, $p_field_name ) {
		$row = bug_get_row( $p_bug_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	# --------------------
	# return the specified text field of the given bug
	#  if the field does not exist, display a warning and return ''
	function bug_get_text_field( $p_bug_id, $p_field_name ) {
		$row = bug_text_cache_row( $p_bug_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	# --------------------
	# Returns the number of bugnotes for the given bug_id
	function bug_get_bugnote_count( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		if ( !access_level_check_greater_or_equal( config_get( 'private_bugnote_threshold' ), $t_project_id ) ) {
			$t_restriction = 'AND view_state=' . PUBLIC;
		} else {
			$t_restriction = '';
		}

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		$query = "SELECT COUNT(*)
				  FROM $t_bugnote_table
				  WHERE bug_id ='$c_bug_id' $t_restriction";
		$result = db_query( $query );

		return db_result( $result );
	}
	# --------------------
	# return the timestamp for the most recent time at which a bugnote
	#  associated wiht the bug was modified
	function bug_get_newest_bugnote_timestamp( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		$query = "SELECT UNIX_TIMESTAMP(last_modified) as last_modified
				  FROM $t_bugnote_table
				  WHERE bug_id='$c_bug_id'
				  ORDER BY last_modified DESC
				  LIMIT 1";
		$result = db_query( $query );
		
		return db_result( $result );
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# set the value of a bug field
	function bug_set_field( $p_bug_id, $p_field_name, $p_status ) {
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_field_name	= db_prepare_string( $p_field_name );
		$c_status		= db_prepare_string( $p_status ); #generic, unknown type

		$h_status = bug_get_field( $p_bug_id, $p_field_name );

		# return if status is already set
		if ( $c_status == $h_status ) {
			return true;
		}

		$t_bug_table = config_get ( 'mantis_bug_table' );

		# Update fields
		$query = "UPDATE $t_bug_table
				  SET $c_field_name='$c_status'
				  WHERE id='$c_bug_id'";
		db_query( $query );

		# updated the last_updated date
		bug_update_date( $p_bug_id );

		# log changes
		history_log_event_direct( $p_bug_id, $p_field_name, $h_status, $p_status );

		bug_clear_cache( $p_bug_id );

		return true;
	}
	# --------------------
	# assign the bug to the given user
	function bug_assign( $p_bug_id, $p_user_id, $p_bugnote_text='' ) {
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_user_id	= db_prepare_int( $p_user_id );

		# extract current information into history variables
		$h_status		= bug_get_field ( $p_bug_id, 'status' );
		$h_handler_id	= bug_get_field ( $p_bug_id, 'handler_id' );

		if ( ON == config_get( 'auto_set_status_to_assigned' ) ) {
			$t_ass_val = ASSIGNED;
		} else {
			$t_ass_val = $h_status;
		}
	
		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( ( $t_ass_val != $h_status ) || ( $p_user_id != $h_handler_id ) ) {

			# get user id
			$query = "UPDATE $t_bug_table
					  SET handler_id='$c_user_id', status='$t_ass_val'
					  WHERE id='$c_bug_id'";
			db_query( $query );

			# log changes
			history_log_event_direct( $c_bug_id, 'status', $h_status, $t_ass_val );
			history_log_event_direct( $c_bug_id, 'handler_id', $h_handler_id, $p_user_id );

			# Add bugnote if supplied
			if ( $p_bugnote_text != '' ) {
				bugnote_add( $p_bug_id, $p_bugnote_text );
			}

			# updated the last_updated date
			bug_update_date( $p_bug_id );

			# send assigned to email
			email_assign( $p_bug_id );

			bug_clear_cache( $p_bug_id );
		}

		return true;
	}
	# --------------------
	# close the given bug
	function bug_close( $p_bug_id, $p_bugnote_text='' ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		bug_set_field( $p_bug_id, 'status', CLOSED );

		# Add bugnote if supplied
		if ( $p_bugnote_text != '' ) {
			bugnote_add( $p_bug_id, $p_bugnote_text );
		}

		email_close( $p_bug_id );

		return true;
	}
	# --------------------
	# resolve the given bug
	function bug_resolve( $p_bug_id, $p_resolution, $p_bugnote_text = '', $p_duplicate_id = null, $p_handler_id = null ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		bug_set_field( $p_bug_id, 'status', RESOLVED );
		bug_set_field( $p_bug_id, 'resolution', (int)$p_resolution );

		if ( null !== $p_duplicate_id ) {
			bug_set_field( $p_bug_id, 'duplicate_id', (int)$p_duplicate_id );
		}

		if ( null == $p_handler_id ) {
			$p_handler_id = auth_get_current_user_id();
		}
		bug_set_field( $p_bug_id, 'handler_id', $p_handler_id );

		# Add bugnote if supplied
		if ( '' != $p_bugnote_text ) {
			bugnote_add( $p_bug_id, $p_bugnote_text );
		}

		email_resolved( $p_bug_id );

		return true;
	}
	# --------------------
	# reopen the given bug
	function bug_reopen( $p_bug_id, $p_bugnote_text='' ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		bug_set_field( $p_bug_id, 'status', FEEDBACK );
		bug_set_field( $p_bug_id, 'resolution', REOPENED );

		# Add bugnote if supplied
		if ( '' != $p_bugnote_text ) {
			bugnote_add( $p_bug_id, $p_bugnote_text );
		}

		email_reopen( $p_bug_id );

		return true;
	}
	# --------------------
	# updates the last_updated field
	function bug_update_date( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_table = config_get( 'mantis_bug_table' );

		$query = "UPDATE $t_bug_table
				  SET last_updated=NOW()
				  WHERE id='$c_bug_id'";
		db_query( $query );

		return true;
	}

	# --------------------
	# 
	function bug_monitor( $p_bug_id, $p_user_id ) {
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_user_id	= db_prepare_int( $p_user_id );

		# Make sure we aren't already monitoring this bug
		if ( user_is_monitoring_bug( $p_user_id, $p_bug_id ) ) {
			return true;
		}

		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );

		# Insert monitoring record
		$query ="INSERT ".
				"INTO $t_bug_monitor_table ".
				"( user_id, bug_id ) ".
				"VALUES ".
				"( '$c_user_id', '$c_bug_id' )";
		db_query($query);

		# log new monitoring action
		history_log_event_special( $p_bug_id, BUG_MONITOR, $c_user_id );	
		
		return true;
	}

	# --------------------
	# 
	function bug_unmonitor( $p_bug_id, $p_user_id ) {
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_user_id	= db_prepare_int( $p_user_id );

		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );

		# Delete monitoring record
		$query ="DELETE ".
				"FROM $t_bug_monitor_table ".
				"WHERE user_id = '$c_user_id' AND bug_id = '$c_bug_id'";
		db_query($query);

		# log new un-monitor action
		history_log_event_special( $p_bug_id, BUG_UNMONITOR, $p_user_id );

		return true;
	}
?>
