<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

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
	function bug_delete() {
	}
	# --------------------
	function bug_get_field() {
	}
	# --------------------
	# Returns the record of the specified bug
	function get_bug_row( $p_bug_id ) {
		global $g_mantis_bug_table;

		$c_bug_id = (integer)$p_bug_id;

		# get info
		$query ="SELECT * ".
				"FROM $g_mantis_bug_table ".
				"WHERE id='$c_bug_id' ".
				"LIMIT 1";

		return db_query( $query );
	}
	# --------------------
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

		# get info
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

		# get info
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

		# get info
		$query ="SELECT $p_field_name ".
				"FROM $g_mantis_bug_text_table ".
				"WHERE id='$t_bug_text_id' ".
				"LIMIT 1";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# --------------------
	# --------------------
	# --------------------
	# --------------------
	# --------------------
	# --------------------
	# --------------------
	# --------------------
?>