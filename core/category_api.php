<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: category_api.php,v 1.14.22.1 2007-10-13 22:35:15 giallu Exp $
	# --------------------------------------------------------

	### Category API ###

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check whether the category exists in the project
	# Return true if the category exists, false otherwise
	function category_exists( $p_project_id, $p_category ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_category		= db_prepare_string( $p_category );

		$t_project_category_table = config_get( 'mantis_project_category_table' );

		$query = "SELECT COUNT(*)
				  FROM $t_project_category_table
				  WHERE project_id='$c_project_id' AND
						category='$c_category'";
		$result = db_query( $query );
		$category_count =  db_result( $result );

		if ( 0 < $category_count ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check whether the category exists in the project
	# Trigger an error if it does not
	function category_ensure_exists( $p_project_id, $p_category ) {
		if ( !category_exists( $p_project_id, $p_category ) ) {
			trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# Check whether the category is unique within a project
	# Returns true if the category is unique, false otherwise
	function category_is_unique( $p_project_id, $p_category ) {
		return !category_exists( $p_project_id, $p_category );
	}

	# --------------------
	# Check whether the category is unique within a project
	# Trigger an error if it is not
	function category_ensure_unique( $p_project_id, $p_category ) {
		if ( !category_is_unique( $p_project_id, $p_category ) ) {
			trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
		}
	}


	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Add a new category to the project
	function category_add( $p_project_id, $p_category ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_category		= db_prepare_string( $p_category );

		category_ensure_unique( $p_project_id, $p_category );

		$t_project_category_table = config_get( 'mantis_project_category_table' );

		$query = "INSERT INTO $t_project_category_table
					( project_id, category )
				  VALUES
					( '$c_project_id', '$c_category' )";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Update the name and user associated with the category
	function category_update( $p_project_id, $p_category, $p_new_category, $p_assigned_to ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_category		= db_prepare_string( $p_category );
		$c_new_category	= db_prepare_string( $p_new_category );
		$c_assigned_to	= db_prepare_int( $p_assigned_to );

		category_ensure_exists( $p_project_id, $p_category );

		$t_project_category_table	= config_get( 'mantis_project_category_table' );
		$t_bug_table				= config_get( 'mantis_bug_table' );

		$query = "UPDATE $t_project_category_table
				  SET category='$c_new_category',
				  	  user_id=$c_assigned_to
				  WHERE category='$c_category' AND
						project_id='$c_project_id'";
		db_query( $query );

		if ( $p_category != $p_new_category ) {
			$query = "UPDATE $t_bug_table
					  SET category='$c_new_category'
					  WHERE category='$c_category' AND
					  		project_id='$c_project_id'";
			db_query( $query );
		}

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Remove a category from the project
	function category_remove( $p_project_id, $p_category, $p_new_category='' ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_category		= db_prepare_string( $p_category );
		$c_new_category	= db_prepare_string( $p_new_category );

		category_ensure_exists( $p_project_id, $p_category );
		if ( !is_blank( $p_new_category ) ) {
			category_ensure_exists( $p_project_id, $p_new_category );
		}

		$t_project_category_table	= config_get( 'mantis_project_category_table' );
		$t_bug_table				= config_get( 'mantis_bug_table' );

		$query = "DELETE FROM $t_project_category_table
				  WHERE project_id='$c_project_id' AND
						category='$c_category'";
		db_query( $query );

		$query = "UPDATE $t_bug_table
				  SET category='$c_new_category'
				  WHERE category='$c_category' AND
				  		project_id='$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Remove all categories associated with a project
	function category_remove_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		project_ensure_exists( $p_project_id );

		$t_project_category_table	= config_get( 'mantis_project_category_table' );
		$t_bug_table				= config_get( 'mantis_bug_table' );

		$query = "DELETE FROM $t_project_category_table
				  WHERE project_id='$c_project_id'";
		db_query( $query );

		$query = "UPDATE $t_bug_table
				  SET category=''
				  WHERE project_id='$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return the definition row for the category
	function category_get_row( $p_project_id, $p_category ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_category		= db_prepare_string( $p_category );

		$t_project_category_table = config_get( 'mantis_project_category_table' );

		$query = "SELECT category, user_id
				FROM $t_project_category_table
				WHERE project_id='$c_project_id' AND
					category='$c_category'";
		$result = db_query( $query );
		$count = db_num_rows( $result );
		if ( 0 == $count ) {
			trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
		}

		return db_fetch_array( $result );
	}

	# --------------------
	# Return all categories for the specified project id
	function category_get_all_rows( $p_project_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_project_category_table = config_get( 'mantis_project_category_table' );

		$query = "SELECT category, user_id
				FROM $t_project_category_table
				WHERE project_id='$c_project_id'
				ORDER BY category";
		$result = db_query( $query );
		$count = db_num_rows( $result );
		$rows = array();
		for ( $i = 0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );

			$rows[] = $row;
		}

		return $rows;
	}

	# --------------------
	# Returns the number of defined categories for the specified project id.
	function category_get_count( $p_project_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$t_project_category_table = config_get( 'mantis_project_category_table' );

		$t_query = "SELECT count(*)
				FROM $t_project_category_table
				WHERE project_id='$c_project_id'";
		$t_result = db_query( $t_query );
		return db_result( $t_result );
	}
?>