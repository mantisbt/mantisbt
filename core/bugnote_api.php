<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bugnote_api.php,v 1.2 2002-08-25 08:14:59 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Bugnote API
	###########################################################################

	# --------------------
	# updates the last_modified field
	function bugnote_date_update( $p_bugnote_id ) {
		global $g_mantis_bugnote_table;

		$c_bugnote_id = (integer)$p_bugnote_id;

		$query ="UPDATE $g_mantis_bugnote_table ".
				"SET last_modified=NOW() ".
				"WHERE id='$c_bugnote_id'";
		return db_query( $query );
	}
	# --------------------
	# check to see if bugnote exists
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function check_bugnote_exists( $p_bugnote_id ) {
		global $g_mantis_bugnote_table;

		$c_bugnote_id = (integer)$p_bugnote_id;

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_bugnote_table ".
				"WHERE id='$c_bugnote_id'";
		$result = db_query( $query );
		if ( 0 == db_result( $result, 0, 0 ) ) {
			print_header_redirect( 'main_page.php' );
		}
	}
	# --------------------
	# add a bugnote to a bug
	function bugnote_add ( $p_bug_id, $p_bugnote_text, $p_private=false )
	{
		global $g_mantis_bugnote_text_table, $g_mantis_bugnote_table;

		$c_bug_id = (integer)$p_bug_id;
		$c_bugnote_text = addslashes( $p_bugnote_text );
		$c_private = (bool)$p_private;

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
			$u_id = get_current_user_field( 'id' );

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
					$t_bugnote_id = str_pd( db_insert_id(), '0', 7, STR_PAD_LEFT );

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

		$t_bug_id = get_bugnote_field( $p_bugnote_id, 'bug_id' );
		$c_bugnote_id = (integer)$p_bugnote_id;

		# grab the bugnote text id
		$t_bugnote_text_id = get_bugnote_field( $p_bugnote_id, 'bugnote_text_id' );

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
				$t_bugnote_id = str_pd( $c_bugnote_id, '0', 7, STR_PAD_LEFT );
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

		$t_bug_id = get_bugnote_field( $p_bugnote_id, 'bug_id' );
		$t_bugnote_text_id = get_bugnote_field( $p_bugnote_id, 'bugnote_text_id' );

		$c_bugnote_text		= addslashes( $p_bugnote_text );

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

		$t_bug_id = get_bugnote_field( $p_bugnote_id, 'bug_id' );

		$c_bugnote_id = (integer)$p_bugnote_id;

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
	function get_bugnote_text( $p_bugnote_id ) {
		global $g_mantis_bugnote_text_table;

		$c_bugnote_text_id = get_bugnote_field( $p_bugnote_id, 'bugnote_text_id' );

		# grab the bugnote text
		$query = "SELECT note
				FROM $g_mantis_bugnote_text_table
				WHERE id='$c_bugnote_text_id'";
		$result = db_query( $query );

		if ( ! $result ) {
			return false;
		}

		$f_bugnote_text = db_result( $result, 0, 0 );
		
		return $f_bugnote_text;
	}
	# --------------------
	# Returns the number of bugnotes for the given bug_id
	function get_bugnote_count( $p_bug_id ) {
		global $g_mantis_bugnote_table, $g_private_bugnote_threshold;

		$c_bug_id = (integer)$p_bug_id;

		if ( !access_level_check_greater_or_equal( $g_private_bugnote_threshold ) ) {
			$t_restriction = 'AND view_state=' . PUBLIC;
		} else {
			$t_restriction = '';
		}

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_bugnote_table ".
				"WHERE bug_id ='$c_bug_id' $t_restriction";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# Returns a field for the given bugnote
	function get_bugnote_field( $p_bugnote_id, $p_field_name ) {
		global $g_mantis_bugnote_table;

		$c_bugnote_id = (integer)$p_bugnote_id;

		$query ="SELECT $p_field_name ".
				"FROM $g_mantis_bugnote_table ".
				"WHERE id ='$c_bugnote_id' ".
				"LIMIT 1";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}

?>
