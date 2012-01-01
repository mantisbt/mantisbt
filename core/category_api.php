<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package CoreAPI
 * @subpackage CategoryAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Category data cache (to prevent excessive db queries)
$g_category_cache = array();

/**
 * Check whether the category exists in the project
 * @param int $p_category_id category id
 * @return bool Return true if the category exists, false otherwise
 * @access public
 */
function category_exists( $p_category_id ) {
	global $g_category_cache;
	if( isset( $g_category_cache[(int) $p_category_id] ) ) {
		return true;
	}

	$c_category_id = db_prepare_int( $p_category_id );

	$t_category_table = db_get_table( 'mantis_category_table' );

	$query = "SELECT COUNT(*) FROM $t_category_table
					WHERE id=" . db_param();
	$count = db_result( db_query_bound( $query, array( $c_category_id ) ) );

	if( 0 < $count ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check whether the category exists in the project
 * Trigger an error if it does not
 * @param int $p_category_id category id
 * @access public
 */
 function category_ensure_exists( $p_category_id ) {
	if( !category_exists( $p_category_id ) ) {
		trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
	}
}

/**
 * Check whether the category is unique within a project
 * @param int $p_project_id project id
 * @param string $p_name project name
 * @return bool Returns true if the category is unique, false otherwise
 * @access public
 */
 function category_is_unique( $p_project_id, $p_name ) {
	$c_project_id = db_prepare_int( $p_project_id );

	$t_category_table = db_get_table( 'mantis_category_table' );

	$query = "SELECT COUNT(*) FROM $t_category_table
					WHERE project_id=" . db_param() . " AND " . db_helper_like( 'name' );
	$count = db_result( db_query_bound( $query, array( $c_project_id, $p_name ) ) );

	if( 0 < $count ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check whether the category is unique within a project
 * Trigger an error if it is not
 * @param int $p_project_id Project id
 * @param string $p_name Category Name
 * @return null
 * @access public
 */
 function category_ensure_unique( $p_project_id, $p_name ) {
	if( !category_is_unique( $p_project_id, $p_name ) ) {
		trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
	}
}

/**
 * Add a new category to the project
 * @param int $p_project_id Project id
 * @param string $p_name Category Name
 * @return int Category ID
 * @access public
 */
 function category_add( $p_project_id, $p_name ) {
	$c_project_id = db_prepare_int( $p_project_id );

	if( is_blank( $p_name ) ) {
		error_parameters( lang_get( 'category' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	category_ensure_unique( $p_project_id, $p_name );

	$t_category_table = db_get_table( 'mantis_category_table' );

	$query = "INSERT INTO $t_category_table
					( project_id, name )
				  VALUES
					( " . db_param() . ', ' . db_param() . ' )';
	db_query_bound( $query, array( $c_project_id, $p_name ) );

	# db_query errors on failure so:
	return db_insert_id( $t_category_table );
}

/**
 * Update the name and user associated with the category
 * @param int $p_category_id Category id
 * @param string $p_name Category Name
 * @param int $p_assigned_to User ID that category is assigned to
 * @return bool
 * @access public
 */
 function category_update( $p_category_id, $p_name, $p_assigned_to ) {
	if( is_blank( $p_name ) ) {
		error_parameters( lang_get( 'category' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_old_category = category_get_row( $p_category_id );

	$c_category_id = db_prepare_int( $p_category_id );
	$c_assigned_to = db_prepare_int( $p_assigned_to );

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "UPDATE $t_category_table
				  SET name=" . db_param() . ',
					user_id=' . db_param() . '
				  WHERE id=' . db_param();
	db_query_bound( $query, array( $p_name, $c_assigned_to, $c_category_id ) );

	# Add bug history entries if we update the category's name
	if( $t_old_category['name'] != $p_name ) {
		$query = "SELECT id FROM $t_bug_table WHERE category_id=" . db_param();
		$t_result = db_query_bound( $query, array( $c_category_id ) );

		while( $t_bug_row = db_fetch_array( $t_result ) ) {
			history_log_event_direct( $t_bug_row['id'], 'category', $t_old_category['name'], $p_name );
		}
	}

	# db_query errors on failure so:
	return true;
}

/**
 * Remove a category from the project
 * @param int $p_category_id Category id
 * @param int $p_new_category_id new category id (to replace existing category)
 * @return bool
 * @access public
 */
 function category_remove( $p_category_id, $p_new_category_id = 0 ) {
	$t_category_row = category_get_row( $p_category_id );

	$c_category_id = db_prepare_int( $p_category_id );
	$c_new_category_id = db_prepare_int( $p_new_category_id );

	category_ensure_exists( $p_category_id );
	if( 0 != $p_new_category_id ) {
		category_ensure_exists( $p_new_category_id );
	}

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "DELETE FROM $t_category_table
				  WHERE id=" . db_param();
	db_query_bound( $query, array( $c_category_id ) );

	# update bug history entries
	$query = "SELECT id FROM $t_bug_table WHERE category_id=" . db_param();
	$t_result = db_query_bound( $query, array( $c_category_id ) );

	while( $t_bug_row = db_fetch_array( $t_result ) ) {
		history_log_event_direct( $t_bug_row['id'], 'category', $t_category_row['name'], category_full_name( $p_new_category_id, false ) );
	}

	# update bug data
	$query = "UPDATE $t_bug_table
				  SET category_id=" . db_param() . "
				  WHERE category_id=" . db_param();
	db_query_bound( $query, array( $c_new_category_id, $c_category_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Remove all categories associated with a project
 * @param int $p_project_id Project ID
 * @param int $p_new_category_id new category id (to replace existing category)
 * @return bool
 * @access public
 */
 function category_remove_all( $p_project_id, $p_new_category_id = 0 ) {

	project_ensure_exists( $p_project_id );
	if( 0 != $p_new_category_id ) {
		category_ensure_exists( $p_new_category_id );
	}

	# cache category names
	category_get_all_rows( $p_project_id );

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	# get a list of affected categories
	$t_query = "SELECT id FROM $t_category_table WHERE project_id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_project_id ) );

	$t_category_ids = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_category_ids[] = $t_row['id'];
	}

	# Handle projects with no categories
	if( count( $t_category_ids ) < 1 ) {
		return true;
	}

	$t_category_ids = join( ',', $t_category_ids );

	# update bug history entries
	$t_query = "SELECT id, category_id FROM $t_bug_table WHERE category_id IN ( $t_category_ids )";
	$t_result = db_query_bound( $t_query );

	while( $t_bug_row = db_fetch_array( $t_result ) ) {
		history_log_event_direct( $t_bug_row['id'], 'category', category_full_name( $t_bug_row['category_id'], false ), category_full_name( $p_new_category_id, false ) );
	}

	# update bug data
	$t_query = "UPDATE $t_bug_table SET category_id=" . db_param() . " WHERE category_id IN ( $t_category_ids )";
	db_query_bound( $t_query, array( $p_new_category_id ) );

	# delete categories
	$t_query = "DELETE FROM $t_category_table WHERE project_id=" . db_param();
	db_query_bound( $t_query, array( $p_project_id ) );

	return true;
}

/**
 * Return the definition row for the category
 * @param int $p_category_id Category id
 * @return array array containing category details
 * @access public
 */
 function category_get_row( $p_category_id ) {
	global $g_category_cache;
	if( isset( $g_category_cache[$p_category_id] ) ) {
		return $g_category_cache[$p_category_id];
	}

	$c_category_id = db_prepare_int( $p_category_id );

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );

	$query = "SELECT * FROM $t_category_table
				WHERE id=" . db_param();
	$result = db_query_bound( $query, array( $c_category_id ) );
	$count = db_num_rows( $result );
	if( 0 == $count ) {
		trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
	}

	$row = db_fetch_array( $result );
	$g_category_cache[$p_category_id] = $row;
	return $row;
}

/**
 * Sort categories based on what project they're in.
 * Call beforehand with a single parameter to set a 'preferred' project.
 * @param array $p_category1 array containing category details
 * @param array $p_category2 array containing category details
 * @return int integer representing sort order
 * @access public
 */
 function category_sort_rows_by_project( $p_category1, $p_category2 = null ) {
	static $p_project_id = null;
	if( is_null( $p_category2 ) ) {

		# Set a target project
		$p_project_id = $p_category1;
		return;
	}

	if( !is_null( $p_project_id ) ) {
		if( $p_category1['project_id'] == $p_project_id && $p_category2['project_id'] != $p_project_id ) {
			return -1;
		}
		if( $p_category1['project_id'] != $p_project_id && $p_category2['project_id'] == $p_project_id ) {
			return 1;
		}
	}

	$t_proj_cmp = strcasecmp( $p_category1['project_name'], $p_category2['project_name'] );
	if( $t_proj_cmp != 0 ) {
		return $t_proj_cmp;
	}

	return strcasecmp( $p_category1['name'], $p_category2['name'] );
}

$g_cache_category_project = null;

function category_cache_array_rows_by_project( $p_project_id_array ) {
	global $g_category_cache, $g_cache_category_project;

	$c_project_id_array = array();

	foreach( $p_project_id_array as $t_project_id ) {
		if( !isset( $g_cache_category_project[(int) $t_project_id] ) ) {
			$c_project_id_array[] = (int) $t_project_id;
			$g_cache_category_project[(int) $t_project_id] = array();
		}
	}

	if( empty( $c_project_id_array ) ) {
		return;
	}

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );

	$query = "SELECT c.*, p.name AS project_name FROM $t_category_table AS c
				LEFT JOIN $t_project_table AS p
					ON c.project_id=p.id
				WHERE project_id IN ( " . implode( ', ', $c_project_id_array ) . " )
				ORDER BY c.name ";
	$result = db_query_bound( $query );

	$rows = array();
	while( $row = db_fetch_array( $result ) ) {
		$g_category_cache[(int) $row['id']] = $row;

		$rows[ (int)$row[ 'project_id' ] ][] = $row['id'];
	}

	foreach( $rows as $t_project_id => $t_row ) {
		$g_cache_category_project[ (int)$t_project_id ] = $t_row;
	}
	return;
}

/**
 *	Get a distinct array of categories accessible to the current user for
 *	the specified projects.  If no project is specified, use the current project.
 *	If the current project is ALL_PROJECTS get all categories for all accessible projects.
 *	For all cases, get global categories and subproject categories according to configured inheritance settings.
 *	@param mixed $p_project_id A specific project or null
 *	@return array A unique array of category names
 */
function category_get_filter_list( $p_project_id = null ) {
	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	if( $t_project_id == ALL_PROJECTS ) {
		$t_project_ids = current_user_get_accessible_projects();
	} else {
		$t_project_ids = array( $t_project_id );
	}

	$t_subproject_ids = array();
	foreach( $t_project_ids as $t_project_id ) {
		$t_subproject_ids = array_merge( $t_subproject_ids, current_user_get_all_accessible_subprojects( $t_project_id ) );
	}

	$t_project_ids = array_merge( $t_project_ids, $t_subproject_ids );

	$t_categories = array();
	foreach( $t_project_ids AS $t_id ) {
		$t_categories = array_merge( $t_categories, category_get_all_rows( $t_id ) );
	}

	$t_unique = array();
	foreach( $t_categories AS $t_category ) {
		if( !in_array( $t_category['name'], $t_unique ) ) {
			$t_unique[] = $t_category['name'];
		}
	}

	return $t_unique;
}

/**
 * Return all categories for the specified project id.
 * Obeys project hierarchies and such.
 * @param int $p_project_id Project id
 * @param bool $p_inherit indicates whether to inherit categories from parent projects, or null to use configuration default.
 * @param bool $p_sort_by_project
 * @return array array of categories
 * @access public
 */
 function category_get_all_rows( $p_project_id, $p_inherit = null, $p_sort_by_project = false ) {
	global $g_category_cache, $g_cache_category_project;

	if( isset( $g_cache_category_project[ (int)$p_project_id ] ) ) {
		if( !empty( $g_cache_category_project[ (int)$p_project_id ]) ) {
			foreach( $g_cache_category_project[ (int)$p_project_id ] as $t_id ) {
				$t_categories[] = category_get_row( $t_id );
			}

			if( $p_sort_by_project ) {
				category_sort_rows_by_project( $p_project_id );
				usort( $t_categories, 'category_sort_rows_by_project' );
				category_sort_rows_by_project( null );
			}
			return $t_categories;
		} else {
			return array();
		}
	}

	$c_project_id = db_prepare_int( $p_project_id );

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );

	if ( $c_project_id == ALL_PROJECTS ) {
		$t_inherit = false;
	} else {
		if ( $p_inherit === null ) {
			$t_inherit = config_get( 'subprojects_inherit_categories' );
		} else {
			$t_inherit = $p_inherit;
		}
	}

	if ( $t_inherit ) {
		$t_project_ids = project_hierarchy_inheritance( $p_project_id );
		$t_project_where = ' project_id IN ( ' . implode( ', ', $t_project_ids ) . ' ) ';
	} else {
		$t_project_where = ' project_id=' . $p_project_id . ' ';
	}

	$query = "SELECT c.*, p.name AS project_name FROM $t_category_table AS c
				LEFT JOIN $t_project_table AS p
					ON c.project_id=p.id
				WHERE $t_project_where
				ORDER BY c.name ";
	$result = db_query_bound( $query );
	$count = db_num_rows( $result );
	$rows = array();
	for( $i = 0;$i < $count;$i++ ) {
		$row = db_fetch_array( $result );

		$rows[] = $row;
		$g_category_cache[(int) $row['id']] = $row;
	}

	if( $p_sort_by_project ) {
		category_sort_rows_by_project( $p_project_id );
		usort( $rows, 'category_sort_rows_by_project' );
		category_sort_rows_by_project( null );
	}

	return $rows;
}

/**
 *
 * @param array $p_cat_id_array array of category id's
 * @return null
 * @access public
 */
function category_cache_array_rows( $p_cat_id_array ) {
	global $g_category_cache;
	$c_cat_id_array = array();

	foreach( $p_cat_id_array as $t_cat_id ) {
		if( !isset( $g_category_cache[(int) $t_cat_id] ) ) {
			$c_cat_id_array[] = (int) $t_cat_id;
		}
	}

	if( empty( $c_cat_id_array ) ) {
		return;
	}

	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );

	$query = "SELECT c.*, p.name AS project_name FROM $t_category_table AS c
				LEFT JOIN $t_project_table AS p
					ON c.project_id=p.id
				WHERE c.id IN (" . implode( ',', $c_cat_id_array ) . ')';
	$result = db_query_bound( $query );

	while( $row = db_fetch_array( $result ) ) {
		$g_category_cache[(int) $row['id']] = $row;
	}
	return;
}

/**
 * Given a category id and a field name, this function returns the field value.
 * An error will be triggered for a non-existent category id or category id = 0.
 * @param int $p_category_id category id
 * @param string $p_name field name
 * @return string field value
 * @access public
 */
function category_get_field( $p_category_id, $p_field_name ) {
	$t_row = category_get_row( $p_category_id );
	return $t_row[$p_field_name];
}

/**
 * Given a category id, this function returns the category name.
 * An error will be triggered for a non-existent category id or category id = 0.
 * @param int $p_category_id category id
 * @return string category name
 * @access public
 */
 function category_get_name( $p_category_id ) {
	return category_get_field( $p_category_id, 'name' );
}

/**
 * Given a category name and project, this function returns the category id.
 * An error will be triggered if the specified project does not have a
 * category with that name.
 * @param string $p_category_name category name
 * @param int $p_project_id project id
 * @param bool $p_trigger_errors trigger error on failure
 * @return bool
 * @access public
 */
 function category_get_id_by_name( $p_category_name, $p_project_id, $p_trigger_errors = true ) {
	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_project_name = project_get_name( $p_project_id );

	$t_query = "SELECT id FROM $t_category_table
				WHERE name=" . db_param() . " AND project_id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_category_name, (int) $p_project_id ) );
	$t_count = db_num_rows( $t_result );
	if( 1 > $t_count ) {
		if( $p_trigger_errors ) {
			error_parameters( $p_category_name, $t_project_name );
			trigger_error( ERROR_CATEGORY_NOT_FOUND_FOR_PROJECT, ERROR );
		} else {
			return false;
		}
	}

	return db_result( $t_result );
}

/**
 * Retrieves category name (including project name if required)
 * @param string $p_category_id category id
 * @param bool $p_show_project show project details
 * @param int $p_project_id current project id override
 * @return string category full name
 * @access public
 */
function category_full_name( $p_category_id, $p_show_project = true, $p_current_project = null ) {
	if( 0 == $p_category_id ) {
		# No Category
		return lang_get( 'no_category' );
	} else {
		$t_row = category_get_row( $p_category_id );
		$t_project_id = $t_row['project_id'];

		$t_current_project = is_null( $p_current_project ) ? helper_get_current_project() : $p_current_project;

		if( $p_show_project && $t_project_id != $t_current_project ) {
			return '[' . project_get_name( $t_project_id ) . '] ' . $t_row['name'];
		}

		return $t_row['name'];
	}
}
