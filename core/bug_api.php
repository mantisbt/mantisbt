<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_api.php,v 1.11 2002-09-16 06:10:25 jfitzell Exp $
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
	function bug_create() {
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
		$t_base = bug_cache_row( $p_user_id );
		$t_text = bug_text_cache_row( $p_user_id );

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
	function bug_resolve( $p_bug_id, $p_resolution, $p_bugnote_text='' ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		bug_set_field( $p_bug_id, 'status', RESOLVED );
		bug_set_field( $p_bug_id, 'resolution', (int)$p_resolution );

		# Add bugnote if supplied
		if ( $p_bugnote_text != '' ) {
			bugnote_add( $p_bug_id, $p_bugnote_text );
		}

		email_resolved( $p_bug_id );

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
?>
