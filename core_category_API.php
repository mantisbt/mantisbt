<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Category API
	###########################################################################
	# --------------------
	# checks to see if the category is a duplicate
	# we do it this way because each different project can have the same category names
	function is_duplicate_category( $p_project_id, $p_category ) {
		global $g_mantis_project_category_table;
		$p_project_id = (integer)$p_project_id;
		$p_category = addslashes($p_category);

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_category_table
				WHERE project_id='$p_project_id' AND
					category='$p_category'";
		$result = db_query( $query );
		$category_count =  db_result( $result, 0, 0 );
		if ( $category_count > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	function category_add( $p_project_id, $p_category ) {
		global $g_mantis_project_category_table;
		$p_project_id = (integer)$p_project_id;
		$p_category = addslashes($p_category);

		$query = "INSERT
				INTO $g_mantis_project_category_table
				( project_id, category )
				VALUES
				( '$p_project_id', '$p_category' )";
		return db_query( $query );
	}
	# --------------------
	function category_update( $p_project_id, $p_category, $p_orig_category ) {
		global $g_mantis_project_category_table;
		$p_project_id = (integer)$p_project_id;
		$p_category = addslashes($p_category);
		$p_orig_category = addslashes($p_orig_category);

		$query = "UPDATE $g_mantis_project_category_table
				SET category='$p_category'
				WHERE category='$p_orig_category' AND
					  project_id='$p_project_id'";
		return db_query( $query );
	}
	# --------------------
	function category_delete( $p_project_id, $p_category ) {
		global $g_mantis_project_category_table;
		$p_project_id = (integer)$p_project_id;
		$p_category = addslashes($p_category);

		$query = "DELETE
				FROM $g_mantis_project_category_table
				WHERE project_id='$p_project_id' AND
					  category='$p_category'";
		return db_query( $query );
	}
	# --------------------
	# return all categories for the specified project id
	function category_get_all( $p_project_id ) {
		global $g_mantis_project_category_table;
		$p_project_id = (integer)$p_project_id;

		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE project_id='$p_project_id'
				ORDER BY category";
		return db_query( $query );
	}
	# --------------------
?>