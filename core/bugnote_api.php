<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_api.php,v 1.20 2004-01-11 07:16:09 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'email_api.php' );
	require_once( $t_core_dir . 'history_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	
	###########################################################################
	# Bugnote API
	###########################################################################

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check if a bugnote with the given ID exists
	#
	# return true if the bugnote exists, false otherwise
	function bugnote_exists( $p_bugnote_id ) {
		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		$query ="SELECT COUNT(*) ".
				"FROM $t_bugnote_table ".
				"WHERE id='$c_bugnote_id'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Check if a bugnote with the given ID exists
	# 
	# return true if the bugnote exists, raise an error if not
	function bugnote_ensure_exists( $p_bugnote_id ) {
		if ( ! bugnote_exists( $p_bugnote_id ) ) {
			trigger_error( ERROR_BUGNOTE_NOT_FOUND, ERROR );
		}
	}
	
	# --------------------
	# Check if the given user is the reporter of the bugnote
	# return true if the user is the reporter, false otherwise
	function bugnote_is_user_reporter( $p_bugnote_id, $p_user_id ) {
		if ( bugnote_get_field( $p_bugnote_id, 'reporter_id' ) == $p_user_id ) {
			return true;
		} else {
			return false;
		}
	}	

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Add a bugnote to a bug
	#
	# return the ID of the new bugnote
	function bugnote_add ( $p_bug_id, $p_bugnote_text, $p_private=false)
	{
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_bugnote_text	= db_prepare_string( $p_bugnote_text );
		$c_private		= db_prepare_bool( $p_private );

		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );

		# insert bugnote text
		$query = "INSERT
				INTO $t_bugnote_text_table
				( id, note )
				VALUES
				( null, '$c_bugnote_text' )";
		db_query( $query );

		# retrieve bugnote text id number
		$t_bugnote_text_id = db_insert_id();

		# Check for private bugnotes.
		if ( $p_private && access_has_project_level( config_get( 'private_bugnote_threshold' ) ) ) {
			$t_view_state = VS_PRIVATE;
		} else {
			$t_view_state = VS_PUBLIC;
		}

		# get user information
		$t_user_id = auth_get_current_user_id();

		# insert bugnote info
		$query = "INSERT
				INTO $t_bugnote_table
				( id, bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
				VALUES
				( null, '$c_bug_id', '$t_user_id','$t_bugnote_text_id', '$t_view_state', NOW(), NOW() )";
		db_query( $query );

		# get bugnote id
		$t_bugnote_id = db_insert_id();

		# update bug last updated
		bug_update_date( $p_bug_id );

		# log new bug
		history_log_event_special( $p_bug_id, BUGNOTE_ADDED, bugnote_format_id( $t_bugnote_id ) );

		return $t_bugnote_id;
	}

	# --------------------
	# Delete a bugnote
	function bugnote_delete( $p_bugnote_id ) {
		$c_bugnote_id	= db_prepare_int( $p_bugnote_id );

		$t_bug_id		= bugnote_get_field( $p_bugnote_id, 'bug_id' );
		$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );

		# Remove the bugnote
		$query = "DELETE
				FROM $t_bugnote_table
				WHERE id='$c_bugnote_id'";
		db_query( $query );
		
		# Remove the bugnote text
		$query = "DELETE
				FROM $t_bugnote_text_table
				WHERE id='$t_bugnote_text_id'";
		db_query( $query );

		# log deletion of bug
		history_log_event_special( $t_bug_id, BUGNOTE_DELETED, bugnote_format_id( $p_bugnote_id ) );

		return true;
	}

	# --------------------
	# delete all bugnotes associated with the given bug
	function bugnote_delete_all( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );

		# Delete the bugnote text items
		$query = "SELECT bugnote_text_id
				FROM $t_bugnote_table
				WHERE bug_id='$c_bug_id'";
		$result = db_query($query);

		$bugnote_count = db_num_rows( $result );

		for ( $i = 0 ; $i < $bugnote_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_bugnote_text_id = $row['bugnote_text_id'];

			# Delete the corresponding bugnote texts
			$query = "DELETE
					FROM $t_bugnote_text_table
					WHERE id='$t_bugnote_text_id'";
			db_query( $query );
		}

		# Delete the corresponding bugnotes
		$query = "DELETE
				FROM $t_bugnote_table
				WHERE bug_id='$c_bug_id'";
		$result = db_query($query);

		# db_query() errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Get the text associated with the bugnote
	function bugnote_get_text( $p_bugnote_id ) {
		$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

		$t_bugnote_text_table = config_get( 'mantis_bugnote_text_table' );

		# grab the bugnote text
		$query = "SELECT note
				FROM $t_bugnote_text_table
				WHERE id='$t_bugnote_text_id'";
		$result = db_query( $query );

		return db_result( $result );
	}

	# --------------------
	# Get a field for the given bugnote
	function bugnote_get_field( $p_bugnote_id, $p_field_name ) {
		$c_bugnote_id = db_prepare_int( $p_bugnote_id );
		$c_field_name = db_prepare_string( $p_field_name );

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		$query ="SELECT $c_field_name ".
				"FROM $t_bugnote_table ".
				"WHERE id='$c_bugnote_id' ".
				"LIMIT 1";
		$result = db_query( $query );

		return db_result( $result );
	}

	
	#===================================
	# Data Modification
	#===================================
	
	# --------------------
	# Update the last_modified field of the bugnote
	function bugnote_date_update( $p_bugnote_id ) {
		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		$query = "UPDATE $t_bugnote_table
				  SET last_modified=NOW()
				  WHERE id='$c_bugnote_id'";
		db_query( $query );

		# db_query() errors if there was a problem so:
		return true;
	}

	# --------------------
	# Set the bugnote text
	function bugnote_set_text( $p_bugnote_id, $p_bugnote_text ) {
		$c_bugnote_text	= db_prepare_string( $p_bugnote_text );

		$t_bug_id			= bugnote_get_field( $p_bugnote_id, 'bug_id' );
		$t_bugnote_text_id	= bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

		$t_bugnote_text_table = config_get( 'mantis_bugnote_text_table' );

		$query = "UPDATE $t_bugnote_text_table
				SET note='$c_bugnote_text'
				WHERE id='$t_bugnote_text_id'";
		db_query( $query );

		# updated the last_updated date
		bugnote_date_update( $p_bugnote_id );

		# log new bugnote
		history_log_event_special( $t_bug_id, BUGNOTE_UPDATED, bugnote_format_id( $p_bugnote_id ) );

		return true;
	}

	# --------------------
	# Set the view state of the bugnote
	function bugnote_set_view_state( $p_bugnote_id, $p_private ) {
		$c_bugnote_id = db_prepare_int( $p_bugnote_id );

		$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );

		if ( $p_private ) {
			$t_view_state = VS_PRIVATE;
		} else {
			$t_view_state = VS_PUBLIC;
		}

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		# update view_state
		$query = "UPDATE $t_bugnote_table
					SET view_state='$t_view_state'
					WHERE id='$c_bugnote_id'";
		db_query( $query );

		history_log_event_special( $t_bug_id, BUGNOTE_STATE_CHANGED, bugnote_format_id( $t_view_state ), $p_bugnote_id );

		return true;
	}


	#===================================
	# Other
	#===================================

	# --------------------
	# Pad the bugnote id with the appropriate number of zeros for printing
	function bugnote_format_id( $p_bugnote_id ) {
		$t_padding = config_get( 'display_bugnote_padding' );
		return( str_pad( $p_bugnote_id, $t_padding, '0', STR_PAD_LEFT ) );
	}
?>
