<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bugnote_api.php,v 1.7 2002-08-29 02:56:23 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Bugnote API
	###########################################################################

	# --------------------
	# updates the last_modified field
	function bugnote_date_update( $p_bugnote_id ) {
		global $g_mantis_bugnote_table;

		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		$query ="UPDATE $g_mantis_bugnote_table ".
				"SET last_modified=NOW() ".
				"WHERE id='$c_bugnote_id'";
		$result = db_query( $query );

		# db_query() errors if there was a problem so:
		return true;
	}
	# --------------------
	# returns true if the bugnote exists, false otherwise
	function bugnote_exists( $p_bugnote_id ) {
		global $g_mantis_bugnote_table;

		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_bugnote_table ".
				"WHERE id='$c_bugnote_id'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return false;
		} else {
			return true;
		}
	}
	# --------------------
	# check to see if bugnote exists
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function bugnote_ensure_exists( $p_bugnote_id ) {
		if ( ! bugnote_exists( $p_bugnote_id ) ) {
			trigger_error( ERROR_BUGNOTE_NOT_FOUND, ERROR );
		}
	}
	# --------------------
	# add a bugnote to a bug
	function bugnote_add ( $p_bug_id, $p_bugnote_text, $p_private=false )
	{
		global $g_mantis_bugnote_text_table, $g_mantis_bugnote_table;

		$c_bug_id = db_prepare_int( $p_bug_id );
		$c_bugnote_text = db_prepare_string( $p_bugnote_text );
		$c_private = db_prepare_bool( $p_private );

		# insert bugnote text
		$query = "INSERT
				INTO $g_mantis_bugnote_text_table
				( id, note )
				VALUES
				( null, '$c_bugnote_text' )";
		$result = db_query( $query );

		if ( $result ) {
			# retrieve bugnote text id number
			$t_bugnote_text_id = db_insert_id();

			# Check for private bugnotes.
			if ( $c_private && access_level_check_greater_or_equal( $g_private_bugnote_threshold ) ) {
				$t_view_state = PRIVATE;
			} else {
				$t_view_state = PUBLIC;
			}

			# get user information
			$u_id = current_user_get_field( 'id' );

			# insert bugnote info
			$query = "INSERT
					INTO $g_mantis_bugnote_table
					( id, bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
					VALUES
					( null, '$c_bug_id', '$u_id','$t_bugnote_text_id', '$t_view_state', NOW(), NOW() )";
			$result = db_query( $query );

			if ( $result ) {
				# update bug last updated
				$result = bug_date_update( $p_bug_id );

				if ( $result ) {
					# get bugnote id
					$t_bugnote_id = str_pad( db_insert_id(), '0', 7, STR_PAD_LEFT );

					# log new bug
					history_log_event_special( $p_bug_id, BUGNOTE_ADDED , $t_bugnote_id );

					return true;
				}
			}
		}

		return false;
	}
	# --------------------
	# Delete a bugnote
	function bugnote_delete( $p_bugnote_id ) {
		global $g_mantis_bugnote_table, $g_mantis_bugnote_text_table;

		$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		# grab the bugnote text id
		$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

		# Remove the bugnote
		$query = "DELETE
				FROM $g_mantis_bugnote_table
				WHERE id='$c_bugnote_id'";
		$result = db_query( $query );
		
		if ( $result ) {
			# Remove the bugnote text
			$query = "DELETE
					FROM $g_mantis_bugnote_text_table
					WHERE id='$t_bugnote_text_id'";
			$result = db_query( $query );

			if ( $result ) {
				# log deletion of bug
				$t_bugnote_id = str_pad( $c_bugnote_id, '0', 7, STR_PAD_LEFT );
				history_log_event_special( $t_bug_id, BUGNOTE_DELETED, $t_bugnote_id );
				return true;
			}
		}

		return false;
	}
	# --------------------
	# Update a bugnote text
	function bugnote_update_text( $p_bugnote_id, $p_bugnote_text ) {
		global $g_mantis_bugnote_text_table;

		$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
		$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

		$c_bugnote_text		= db_prepare_string( $p_bugnote_text );

		$query = "UPDATE $g_mantis_bugnote_text_table
				SET note='$c_bugnote_text'
				WHERE id='$t_bugnote_text_id'";
		$result = db_query( $query );

		if ( $result ) {
			# updated the last_updated date
			$result = bugnote_date_update( $p_bugnote_id );

			if ( $result ) {
				# log new bugnote
				history_log_event_special( $t_bug_id, BUGNOTE_UPDATED, $p_bugnote_id );

				return true;
			}
		}

		return false;
	}
	# --------------------
	# Updated the view state of a bugnote
	function bugnote_update_view_state( $p_bugnote_id, $p_private ) {
		global $g_mantis_bugnote_table;

		$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );

		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		if ( $p_private ) {
			$t_view_state = PRIVATE;
		} else {
			$t_view_state = PUBLIC;
		}

		# update view_state
		$query = "UPDATE $g_mantis_bugnote_table
					SET view_state='$t_view_state'
					WHERE id='$c_bugnote_id'";
		$result = db_query( $query );

		if ( $result ) {
			history_log_event_special( $t_bug_id, BUGNOTE_STATE_CHANGED, $t_view_state, $c_bugnote_id );
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# Returns the text associated with the bugnote
	function bugnote_get_text( $p_bugnote_id ) {
		global $g_mantis_bugnote_text_table;

		$c_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

		# grab the bugnote text
		$query = "SELECT note
				FROM $g_mantis_bugnote_text_table
				WHERE id='$c_bugnote_text_id'";
		$result = db_query( $query );

		return db_result( $result );
	}
	# --------------------
	# Returns a field for the given bugnote
	function bugnote_get_field( $p_bugnote_id, $p_field_name ) {
		global $g_mantis_bugnote_table;

		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		$query ="SELECT $p_field_name ".
				"FROM $g_mantis_bugnote_table ".
				"WHERE id ='$c_bugnote_id' ".
				"LIMIT 1";
		$result = db_query( $query );

		return db_result( $result );
	}

?>
