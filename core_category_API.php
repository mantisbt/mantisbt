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
	# return all categories for the specified project id
	function category_get_all( $p_project_id ) {
		global $g_mantis_project_category_table;

		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE project_id='$p_project_id'
				ORDER BY category";
		return db_query( $query );
	}
	# --------------------
	function category_add( $p_project_id, $p_category ) {
		global $g_mantis_project_category_table;

		# insert category
		$query = "INSERT
				INTO $g_mantis_project_category_table
				( project_id, category )
				VALUES
				( '$p_project_id', '$p_category' )";
		return db_query( $query );
	}
	# --------------------
?>