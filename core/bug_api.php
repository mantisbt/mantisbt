<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_api.php,v 1.2 2002-08-25 08:14:59 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Bug API
	###########################################################################

	# --------------------
	function bug_add() {
	}
	# --------------------
	function bug_update() {
	}
	# --------------------
	# allows bug deletion :
	# delete the bug, bugtext, bugnote, and bugtexts selected
	# used in bug_delete.php & mass treatments
	function bug_delete( $p_id, $p_bug_text_id ) {
		global $g_mantis_bug_file_table, $g_mantis_bug_table, $g_mantis_bug_text_table,
			   $g_mantis_bugnote_table, $g_mantis_bugnote_text_table, $g_mantis_bug_history_table,
			   $g_file_upload_method ;

		email_bug_deleted( $p_id );

		$c_id			= (integer)$p_id;
		$c_bug_text_id	= (integer)$p_bug_text_id;
		
		$retval = true;

		# Delete the bug entry
		$query = "DELETE
				FROM $g_mantis_bug_table
				WHERE id='$c_id'";
		$result = db_query( $query );
		$retval = $retval && $result;

		# Delete the corresponding bug text
		$query = "DELETE
				FROM $g_mantis_bug_text_table
				WHERE id='$c_bug_text_id'";
		$result = db_query( $query );
		$retval = $retval && $result;

		# Delete the bugnote text items
		$query = "SELECT bugnote_text_id
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$c_id'";
		$result = db_query($query);
		$retval = $retval && $result;
		$bugnote_count = db_num_rows( $result );
		for ($i=0;$i<$bugnote_count;$i++){
			$row = db_fetch_array( $result );
			$t_bugnote_text_id = $row['bugnote_text_id'];

			# Delete the corresponding bugnote texts
			$query = "DELETE
					FROM $g_mantis_bugnote_text_table
					WHERE id='$t_bugnote_text_id'";
			$result = db_query( $query );
			$retval = $retval && $result;
		}

		# Delete the corresponding bugnotes
		$query = "DELETE
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$c_id'";
		$result = db_query($query);
		$retval = $retval && $result;

		if ( ( DISK == $g_file_upload_method ) || ( FTP == $g_file_upload_method ) ) {
			# Delete files from disk
			$query = "SELECT diskfile, filename
				FROM $g_mantis_bug_file_table
				WHERE bug_id='$c_id'";
			$result = db_query( $query );
			$retval = $retval && $result;
			$file_count = db_num_rows( $result );

			# there may be more than one file
			for ($i=0;$i<$file_count;$i++){
				$row = db_fetch_array( $result );

				file_delete_local ( $row['diskfile'] );

				if ( FTP == $g_file_upload_method ) {
					$ftp = file_ftp_connect();
					file_ftp_delete ( $ftp, $row['filename'] );
					file_ftp_disconnect( $ftp );
				}
			}
		}

		# Delete the corresponding files
		$query = "DELETE
			FROM $g_mantis_bug_file_table
			WHERE bug_id='$c_id'";
		$result = db_query($query);
		$retval = $retval && $result;

		# Delete the bug history
		$query = "DELETE
			FROM $g_mantis_bug_history_table
			WHERE bug_id='$c_id'";
		$result = db_query($query);
		$retval = $retval && $result;

		return ($retval);
	}
	# --------------------
	# This function assigns the bug to the current user
	function bug_assign( $p_bug_id ) {
		global $g_mantis_bug_table, $g_auto_set_status_to_assigned;

		# extract current information into history variables
		$result = get_bug_row ( $p_bug_id );
		if ( 0 == db_num_rows( $result ) ) {
			# speed is not an issue in this case, so re-use code
			check_bug_exists( $p_bug_id );
		}

		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'h' );

		if ( ON == $g_auto_set_status_to_assigned ) {
			$t_ass_val = ASSIGNED;
		} else {
			$t_ass_val = $h_status;
		}

		$t_handler_id = get_current_user_field( 'id' );

		if ( ( $t_ass_val != $h_status ) || ( $t_handler_id != $h_handler_id ) ) {
			$c_id = (integer)$p_bug_id;

			# get user id
			$query ="UPDATE $g_mantis_bug_table ".
					"SET handler_id='$t_handler_id', status='$t_ass_val' ".
					"WHERE id='$c_id'";
			$result = db_query( $query );

			# updated the last_updated date
			$result = bug_date_update( $p_bug_id );

			# log changes
			history_log_event_direct( $c_id, 'status', $h_status, $t_ass_val, $t_handler_id );
			history_log_event_direct( $c_id, 'handler_id', $h_handler_id, $t_handler_id, $t_handler_id );

			# send assigned to email
			email_assign( $p_bug_id );
		}
		return true;
	}
	# --------------------
	function bug_close( $p_bug_id, $p_bugnote_text  ) {
		global $g_mantis_bug_table;

		#clean variables
		$c_id = (integer)$p_bug_id;

		$t_handler_id	= get_current_user_field( 'id' );

		$h_status	= get_bug_field( $p_bug_id, 'status' );

		# Update fields
		$t_status_val = CLOSED;
		$query ="UPDATE $g_mantis_bug_table ".
				"SET status='$t_status_val' ".
				"WHERE id='$c_id'";
		$result = db_query( $query );

		# log changes
		history_log_event( $p_bug_id, 'status', $h_status );

		$p_bugnote_text = trim( $p_bugnote_text );
		# check for blank bugnote
		if ( !empty( $p_bugnote_text ) ) {
			# insert bugnote text
			#@@@ jf - need to add string_prepare_textarea() call or something once that is resolved
			$result = bugnote_add( $p_bug_id, $p_bugnote_text );

			email_close( $p_bug_id );
		}
		return true;
	}
	# --------------------
	function bug_get_field() {
	}
	# --------------------
	# Returns the record of the specified bug
	function get_bug_row( $p_bug_id ) {
		global $g_mantis_bug_table;

		$c_bug_id = (integer)$p_bug_id;

		$query ="SELECT * ".
				"FROM $g_mantis_bug_table ".
				"WHERE id='$c_bug_id' ".
				"LIMIT 1";
		return db_query( $query );
	}
	# --------------------
	# updates the last_updated field
	function bug_date_update( $p_bug_id ) {
		global $g_mantis_bug_table;

		$c_bug_id = (integer)$p_bug_id;

		$query ="UPDATE $g_mantis_bug_table ".
				"SET last_updated=NOW() ".
				"WHERE id='$c_bug_id'";
		return db_query( $query );
	}
	# --------------------
	# Returns the extended record of the specified bug, this includes
	# the bug text fields
	# @@@ include reporter name and handler name, the problem is that
	#      handler can be 0, in this case no corresponding name will be
	#      found.  Use equivalent of (+) in Oracle.
	function get_bug_row_ex( $p_bug_id ) {
		global $g_mantis_bug_table, $g_mantis_bug_text_table;

		$c_bug_id = (integer)$p_bug_id;

		$query ="SELECT b.*, bt.*, b.id as id ".
				"FROM $g_mantis_bug_table b, $g_mantis_bug_text_table bt ".
				"WHERE b.id='$c_bug_id' AND b.bug_text_id = bt.id ".
				"LIMIT 1";
		return db_query( $query );
	}
	# --------------------
	# Returns the specified field value of the specified bug
	function get_bug_field( $p_bug_id, $p_field_name ) {
		global $g_mantis_bug_table;

		$c_bug_id = (integer)$p_bug_id;

		$query ="SELECT $p_field_name ".
				"FROM $g_mantis_bug_table ".
				"WHERE id='$c_bug_id' ".
				"LIMIT 1";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# Returns the specified field value of the specified bug text
	function get_bug_text_field( $p_bug_id, $p_field_name ) {
		global $g_mantis_bug_text_table;

		$t_bug_text_id = get_bug_field( $p_bug_id, 'bug_text_id' );

		$query ="SELECT $p_field_name ".
				"FROM $g_mantis_bug_text_table ".
				"WHERE id='$t_bug_text_id' ".
				"LIMIT 1";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
?>