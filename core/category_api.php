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
	# $Id$
	# --------------------------------------------------------

	### Category API ###

	# Category data cache (to prevent excessive db queries)
	$g_category_cache = array();

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check whether the category exists in the project
	# Return true if the category exists, false otherwise
	function category_exists( $p_category_id ) {
		global $g_category_cache;
		if ( isset( $g_category_cache[$p_category_id] ) ) {
			return true;
		}

		$c_category_id	= db_prepare_int( $p_category_id );

		$t_category_table = db_get_table( 'mantis_category_table' );

		$query = "SELECT COUNT(*) FROM $t_category_table
					WHERE id=" . db_param(0);
		$count = db_result( db_query_bound( $query, array( $c_category_id ) ) );

		if ( 0 < $count ) {
				return true;
		} else {
				return false;
		}
	}

	# --------------------
	# Check whether the category exists in the project
	# Trigger an error if it does not
	function category_ensure_exists( $p_category_id ) {
		if ( !category_exists( $p_category_id ) ) {
			trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# Check whether the category is unique within a project
	# Returns true if the category is unique, false otherwise
	function category_is_unique( $p_project_id, $p_name ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_category_table = db_get_table( 'mantis_category_table' );

		$query = "SELECT COUNT(*) FROM $t_category_table
					WHERE project_id=" . db_param(0) . " AND " . db_helper_like( 'name', 1 );
		$count = db_result( db_query_bound( $query, array( $c_project_id, $p_name ) ) );

		if ( 0 < $count ) {
				return false;
		} else {
				return true;
		}
	}

	# --------------------
	# Check whether the category is unique within a project
	# Trigger an error if it is not
	function category_ensure_unique( $p_project_id, $p_name ) {
		if ( !category_is_unique( $p_project_id, $p_name ) ) {
			trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
		}
	}


	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Add a new category to the project
	function category_add( $p_project_id, $p_name ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		category_ensure_unique( $p_project_id, $p_name );

		$t_category_table = db_get_table( 'mantis_category_table' );

		$query = "INSERT INTO $t_category_table
					( project_id, name )
				  VALUES
					( " . db_param(0) . ', ' . db_param(1) . ' )';
		db_query_bound( $query, array( $c_project_id, $p_name ) );

		# db_query errors on failure so:
		return db_insert_id( $t_category_table );
	}

	# --------------------
	# Update the name and user associated with the category
	function category_update( $p_category_id, $p_name, $p_assigned_to ) {
		$t_old_category = category_get_row( $p_category_id );

		$c_category_id	= db_prepare_int( $p_category_id );
		$c_name			= db_prepare_string( $p_name );
		$c_assigned_to	= db_prepare_int( $p_assigned_to );

		$t_category_table	= db_get_table( 'mantis_category_table' );
		$t_bug_table		= db_get_table( 'mantis_bug_table' );

		$query = "UPDATE $t_category_table
				  SET name=" . db_param(0) . ',
					user_id=' . db_param(1) . '
				  WHERE id=' . db_param(2);
		db_query_bound( $query, array( $c_name, $c_assigned_to, $c_category_id ) );

		# Add bug history entries if we update the category's name
		if ( $t_old_category['name'] != $c_name ) {
			$query = "SELECT id FROM $t_bug_table WHERE category_id=" . db_param(0);
			$t_result = db_query_bound( $query, array( $c_category_id ) );

			while ( $t_bug_row = db_fetch_array( $t_result ) ) {
				history_log_event_direct( $t_bug_row['id'], 'category', $t_old_category['name'], $c_name );
			}
		}

		# db_query errors on failure so:
		return true;
	}

	# --------------------
	# Remove a category from the project
	function category_remove( $p_category_id, $p_new_category_id = 0 ) {
		$t_category_row = category_get_row( $p_category_id );

		$c_category_id	= db_prepare_int( $p_category_id );
		$c_new_category_id	= db_prepare_int( $p_new_category_id );

		category_ensure_exists( $p_category_id );
		if ( 0 != $p_new_category_id ) {
			category_ensure_exists( $p_new_category_id );
		}

		$t_category_table	= db_get_table( 'mantis_category_table' );
		$t_bug_table		= db_get_table( 'mantis_bug_table' );

		$query = "DELETE FROM $t_category_table
				  WHERE id=" . db_param(0);
		db_query_bound( $query, array( $c_category_id ) );

		# update bug history entries
		$query = "SELECT id FROM $t_bug_table WHERE category_id=" . db_param(0);
		$t_result = db_query_bound( $query, array( $c_category_id ) );

		while ( $t_bug_row = db_fetch_array( $t_result ) ) {
			var_dump( $t_bug_row );
			history_log_event_direct( $t_bug_row['id'], 'category', $t_category_row['name'], category_full_name( $p_new_category_id, false ) );
		}

		# update bug data
		$query = "UPDATE $t_bug_table
				  SET category_id=" . db_param(0) . "
				  WHERE category_id=" . db_param(1);
		db_query_bound( $query, array( $c_new_category_id, $c_category_id ) );

		# db_query errors on failure so:
		return true;
	}

	# --------------------
	# Remove all categories associated with a project
	function category_remove_all( $p_project_id, $p_new_category_id = 0 ) {
		$c_project_id = db_prepare_int( $p_project_id );
		$c_new_category_id = db_prepare_int( $p_new_category_id );

		project_ensure_exists( $p_project_id );
		if ( 0 != $p_new_category_id ) {
			category_ensure_exists( $p_new_category_id );
		}

		$t_category_table	= db_get_table( 'mantis_category_table' );
		$t_bug_table		= db_get_table( 'mantis_bug_table' );

		$query = "DELETE FROM $t_category_table
				  WHERE project_id=" . db_param(0);
		db_query_bound( $query, array( $c_project_id ) );

		# cache category names
		category_get_all_rows();

		# update bug history entries
		$query = "SELECT id, category_id FROM $t_bug_table WHERE project_id=" . db_param(0);
		$t_result = db_query_bound( $query, array( $c_project_id ) );

		while ( $t_bug_row = db_fetch_array( $t_result ) ) {
			var_dump( $t_bug_row );
			history_log_event_direct( $t_bug_row['id'], 'category', category_full_name( $t_bug_row['category_id'], false ), category_full_name( $p_new_category_id, false ) );
		}

		# update bug data
		$query = "UPDATE $t_bug_table
				  SET category=" . db_param(0) . '
				  WHERE project_id=' . db_param(1);
		db_query_bound( $query, array( $c_new_category_id, $c_project_id ) );

		# db_query errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return the definition row for the category
	function category_get_row( $p_category_id ) {
		global $g_category_cache;
		if ( isset( $g_category_cache[$p_category_id] ) ) {
			return $g_category_cache[$p_category_id];
		}

		$c_category_id	= db_prepare_int( $p_category_id );

		$t_category_table = db_get_table( 'mantis_category_table' );
		$t_project_table = db_get_table( 'mantis_project_table' );

		$query = "SELECT * FROM $t_category_table
				WHERE id=" . db_param(0);
		$result = db_query_bound( $query, array( $c_category_id ) );
		$count = db_num_rows( $result );
		if ( 0 == $count ) {
			trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
		}

		$row = db_fetch_array( $result );
		$g_category_cache[$p_category_id] = $row;
		return $row;
	}

	# --------------------
	# Return all categories for the specified project id
	function category_get_all_rows( $p_project_id ) {
		global $g_category_cache;

		$c_project_id	= db_prepare_int( $p_project_id );

		$t_category_table = db_get_table( 'mantis_category_table' );
		$t_project_table = db_get_table( 'mantis_project_table' );

		$t_project_where = helper_project_specific_where( $c_project_id );

		$query = "SELECT c.*, p.name AS project_name FROM $t_category_table AS c
				JOIN $t_project_table AS p
					ON c.project_id=p.id
				WHERE $t_project_where
				ORDER BY c.name ";
		$result = db_query_bound( $query );
		$count = db_num_rows( $result );
		$rows = array();
		for ( $i = 0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );

			$rows[] = $row;
			$g_category_cache[$row['id']] = $row;
		}

		return $rows;
	}

	# --------------------
	# Given a category id and a field name, this function returns the field value.
	# An error will be triggered for a non-existent category id or category id = 0.
	function category_get_field( $p_category_id, $p_field_name ) {
		$t_row = category_get_row( $p_category_id );
		return $t_row[$p_field_name];
	}

	# --------------------
	# Given a category id, this function returns the category name.
	# An error will be triggered for a non-existent category id or category id = 0.
	function category_get_name( $p_category_id ) {
		return category_get_field( $p_category_id, 'name' );
	}

	# Helpers

	function category_full_name( $p_category_id, $p_show_project=true ) {
		if ( 0 == $p_category_id ) { # No Category
			return lang_get( 'no_category' );
		} else {
			$t_row = category_get_row( $p_category_id );
			$t_project_id = $t_row['project_id'];

			if ( $p_show_project && ALL_PROJECTS != $t_project_id ) {
				return '[' . project_get_name( $t_project_id ) . '] ' . $t_row['name'];
			}

			return $t_row['name'];
		}
	}
