<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# version API
	###########################################################################
	# --------------------
	# checks to see if the version is a duplicate
	# we do it this way because each different project can have the same category names
	function is_duplicate_version( $p_project_id, $p_version, $p_date_order='0' ) {
		global $g_mantis_project_version_table;
		$c_project_id = (integer)$p_project_id;
		$c_version = addslashes($p_version);
		$c_date_order = addslashes($p_date_order);

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_version_table
				WHERE project_id='$c_project_id' AND
					version='$c_version' AND
					date_order='$c_date_order'";
		$result = db_query( $query );
		$version_count =  db_result( $result, 0, 0 );
		if ( $version_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	function version_add( $p_project_id, $p_version ) {
		global $g_mantis_project_version_table;
		$c_project_id = (integer)$p_project_id;
		$c_version = addslashes($p_version);

		$query = "INSERT
				INTO $g_mantis_project_version_table
				( project_id, version, date_order )
				VALUES
				( '$c_project_id', '$c_version', NOW() )";
		return db_query( $query );
	}
	# --------------------
	function version_update( $p_project_id, $p_version, $p_date_order, $p_orig_version ) {
		global $g_mantis_project_version_table;
		$c_project_id = (integer)$p_project_id;
		$c_version = addslashes($p_version);
		$c_date_order = addslashes($p_date_order);
		$c_orig_version = addslashes($p_orig_version);

		$query = "UPDATE $g_mantis_project_version_table
				SET version='$c_version',
					date_order='$c_date_order'
				WHERE version='$c_orig_version'
					  AND project_id='$c_project_id'";
		return db_query( $query );
	}
	# --------------------
	function version_delete( $p_project_id, $p_version ) {
		global $g_mantis_project_version_table;
		$c_project_id = (integer)$p_project_id;
		$c_version = addslashes($p_version);

		$query = "DELETE
				FROM $g_mantis_project_version_table
				WHERE project_id='$c_project_id' AND
					  version='$c_version'";
		return db_query( $query );
	}
	# --------------------
	# return all categories for the specified project id
	function version_get_all( $p_project_id ) {
		global $g_mantis_project_version_table;
		$c_project_id = (integer)$p_project_id;

		$query = "SELECT version, date_order
				FROM $g_mantis_project_version_table
				WHERE project_id='$c_project_id'
				ORDER BY date_order DESC";
		return db_query( $query );
	}
	# --------------------
?>