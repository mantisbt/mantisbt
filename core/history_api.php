<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: history_api.php,v 1.3 2002-08-29 02:56:23 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# History API
	###########################################################################

	# --------------------
	# log the changes (old / new value are supplied to reduce db access)
	# events should be logged *after* the modification
	function history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, $p_new_value, $p_user_id = 0 ) {
		global $g_mantis_bug_history_table;

		# Only log events that change the value
		if ( $p_new_value != $p_old_value ) {
			$c_field_name	= string_prepare_text( $p_field_name );
			$c_old_value	= string_prepare_text( $p_old_value );
			$c_new_value	= string_prepare_text( $p_new_value );
			$c_bug_id		= (integer)$p_bug_id;
			$c_user_id 		= (integer)$p_user_id;
			if ( 0 == $c_user_id ) {
				$c_user_id	= current_user_get_field( 'id' );
			};
			
			$query = "INSERT INTO $g_mantis_bug_history_table
					( user_id, bug_id, date_modified, field_name, old_value, new_value )
					VALUES
					( '$c_user_id', '$c_bug_id', NOW(), '$c_field_name', '$c_old_value', '$c_new_value' )";
			$result = db_query( $query );
		}
	}
	# --------------------
	# log the changes
	# events should be logged *after* the modification
	function history_log_event( $p_bug_id, $p_field_name, $p_old_value ) {
		history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, get_bug_field( $p_bug_id, $p_field_name ) );
	}
	# --------------------
	# log the changes
	# events should be logged *after* the modification
	# These are special case logs (new bug, deleted bugnote, etc.)
	function history_log_event_special( $p_bug_id, $p_type, $p_optional='',  $p_optional2='' ) {
		global $g_mantis_bug_history_table;

		$c_bug_id		= (integer)$p_bug_id;
		$c_type			= (integer)$p_type;
		$c_optional		= string_prepare_text( $p_optional );
		$c_optional2	= string_prepare_text( $p_optional2 );
		$t_user_id		= current_user_get_field( 'id' );
		
		$query = "INSERT INTO $g_mantis_bug_history_table
				( user_id, bug_id, date_modified, type, old_value, new_value )
				VALUES
				( '$t_user_id', '$c_bug_id', NOW(), '$c_type', '$c_optional', '$c_optional2' )";
		$result = db_query( $query );
	}
	# --------------------
	# return all bug history for a given bug id ordered by date
	function history_get_events( $p_bug_id ) {
		global $g_mantis_bug_history_table, $g_mantis_user_table;
		
		$c_bug_id 		= (integer)$p_bug_id;

		$query = "SELECT b.*, u.username
				FROM $g_bug_history_table b
				LEFT JOIN $g_mantis_user_table u
				ON b.user_id=u.id
				WHERE bug_id='$c_bug_id'
				ORDER BY date_modified DESC";
		$result = db_query( $query );
	}
	# --------------------
?>