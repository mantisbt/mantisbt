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
	# The old category name is excluded from the search for duplicate since a category
	# can re-take its name.  It is also useful when changing the case of a category name.
	# For example, "category" -> "Category".
	function is_duplicate_category( $p_project_id, $p_category , $p_old_category = '' ) {
		global $g_mantis_project_category_table;

		$c_project_id	= (integer)$p_project_id;
		$c_category		= addslashes($p_category);

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_category_table
				WHERE project_id='$c_project_id' AND
				category='$c_category'";

		if (strlen($p_old_category) != 0) {
			$c_old_category = addslashes($p_old_category);
			$query = $query . " AND category <> '$c_old_category'";
		}

		$result = db_query( $query );
		$category_count =  db_result( $result, 0, 0 );

		return ( $category_count > 0 );
	}
	# --------------------
	function category_add( $p_project_id, $p_category ) {
		global $g_mantis_project_category_table;

		$c_project_id	= (integer)$p_project_id;
		$c_category		= addslashes($p_category);

		$query = "INSERT
				INTO $g_mantis_project_category_table
				( project_id, category )
				VALUES
				( '$c_project_id', '$c_category' )";
		return db_query( $query );
	}
	# --------------------
	function category_update( $p_project_id, $p_category, $p_orig_category, $p_assigned_to ) {
		global $g_mantis_project_category_table;

		$c_project_id		= (integer)$p_project_id;
		$c_category			= addslashes($p_category);
		$c_orig_category	= addslashes($p_orig_category);
		$c_assigned_to		= (integer)$p_assigned_to;

		$query = "UPDATE $g_mantis_project_category_table
				SET category='$c_category', user_id=$c_assigned_to
				WHERE category='$c_orig_category' AND
					  project_id='$c_project_id'";
		return db_query( $query );
	}
	# --------------------
	function category_delete( $p_project_id, $p_category ) {
		global $g_mantis_project_category_table;

		$c_project_id	= (integer)$p_project_id;
		$c_category		= addslashes($p_category);

		$query = "DELETE
				FROM $g_mantis_project_category_table
				WHERE project_id='$c_project_id' AND
					  category='$c_category'";
		return db_query( $query );
	}
	# --------------------
	# return all categories for the specified project id
	function category_get_all( $p_project_id ) {
		global $g_mantis_project_category_table;

		$c_project_id = (integer)$p_project_id;

		$query = "SELECT category, user_id
				FROM $g_mantis_project_category_table
				WHERE project_id='$c_project_id'
				ORDER BY category";
		return db_query( $query );
	}
	# --------------------
?>