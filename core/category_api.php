<?php
# MantisBT - A PHP based bugtracking system

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
 * Category API
 *
 * @package CoreAPI
 * @subpackage CategoryAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'utility_api.php' );

# Category data cache (to prevent excessive db queries)
$g_category_cache = array();

/**
 * Check whether the category exists in the project
 * @param integer $p_category_id A Category identifier.
 * @return boolean Return true if the category exists, false otherwise
 * @access public
 */
function category_exists( $p_category_id ) {
	$t_category_row = category_get_row( $p_category_id, /* error_if_not_exists */ false );
	return $t_category_row !== false;
}

/**
 * Check whether the category exists in the project
 * Trigger an error if it does not
 * @param integer $p_category_id A Category identifier.
 * @return void
 * @access public
 */
function category_ensure_exists( $p_category_id ) {
	if( !category_exists( $p_category_id ) ) {
		trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
	}
}

/**
 * Check whether the category is unique within a project
 * @param integer $p_project_id A project identifier.
 * @param string  $p_name       Project name.
 * @return boolean Returns true if the category is unique, false otherwise
 * @access public
 */
function category_is_unique( $p_project_id, $p_name ) {
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {category}
					WHERE project_id=' . db_param() . ' AND ' . db_helper_like( 'name' );
	$t_count = db_result( db_query( $t_query, array( $p_project_id, $p_name ) ) );

	if( 0 < $t_count ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check whether the category is unique within a project
 * Trigger an error if it is not
 * @param integer $p_project_id Project identifier.
 * @param string  $p_name       Category Name.
 * @return void
 * @access public
 */
function category_ensure_unique( $p_project_id, $p_name ) {
	if( !category_is_unique( $p_project_id, $p_name ) ) {
		trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
	}
}

/**
 * Checks whether the category can be deleted.
 * It is not allowed to delete a category if it is defined as 'default for moves'
 * @see $g_default_category_for_moves
 * @param integer $p_category_id Category identifier.
 * @return boolean True if category can be deleted, false otherwise
 * @access public
 */
function category_can_remove( $p_category_id ) {
	$t_default_category_id = config_get( 'default_category_for_moves', null, ALL_USERS, ALL_PROJECTS );

	return $p_category_id != $t_default_category_id
		&& !config_is_defined( 'default_category_for_moves', $p_category_id );
}

/**
 * Trigger an error if the category cannot be deleted.
 * @param integer $p_category_id Category identifier.
 * @return void
 * @access public
 */
function category_ensure_can_remove( $p_category_id ) {
	if( !category_can_remove( $p_category_id ) ) {
		trigger_error( ERROR_CATEGORY_CANNOT_DELETE_DEFAULT, ERROR );
	}
}

/**
 * Add a new category to the project
 * @param integer $p_project_id Project identifier.
 * @param string  $p_name       Category Name.
 * @return integer Category ID
 * @access public
 */
function category_add( $p_project_id, $p_name ) {
	if( is_blank( $p_name ) ) {
		error_parameters( lang_get( 'category' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	category_ensure_unique( $p_project_id, $p_name );

	db_param_push();
	$t_query = 'INSERT INTO {category} ( project_id, name )
				  VALUES ( ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array( $p_project_id, $p_name ) );

	# db_query() errors on failure so:
	return db_insert_id( db_get_table( 'category' ) );
}

/**
 * Update the name and user associated with the category
 * @param integer $p_category_id Category identifier.
 * @param string  $p_name        Category Name.
 * @param integer $p_assigned_to User ID that category is assigned to.
 * @return void
 * @access public
 */
function category_update( $p_category_id, $p_name, $p_assigned_to ) {
	if( is_blank( $p_name ) ) {
		error_parameters( lang_get( 'category' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_old_category = category_get_row( $p_category_id );

	db_param_push();
	$t_query = 'UPDATE {category} SET name=' . db_param() . ', user_id=' . db_param() . '
				  WHERE id=' . db_param();
	db_query( $t_query, array( $p_name, $p_assigned_to, $p_category_id ) );

	# Add bug history entries if we update the category's name
	if( $t_old_category['name'] != $p_name ) {
		db_param_push();
		$t_query = 'SELECT id FROM {bug} WHERE category_id=' . db_param();
		$t_result = db_query( $t_query, array( $p_category_id ) );

		while( $t_bug_row = db_fetch_array( $t_result ) ) {
			history_log_event_direct( $t_bug_row['id'], 'category', $t_old_category['name'], $p_name );
		}
	}
}

/**
 * Remove a category from the project
 * @param integer $p_category_id     Category identifier.
 * @param integer $p_new_category_id New category id (to replace existing category).
 * @return void
 * @access public
 */
function category_remove( $p_category_id, $p_new_category_id = 0 ) {
	$t_category_row = category_get_row( $p_category_id );

	category_ensure_exists( $p_category_id );
	category_ensure_can_remove( $p_category_id );
	if( 0 != $p_new_category_id ) {
		category_ensure_exists( $p_new_category_id );
	}

	db_param_push();
	$t_query = 'DELETE FROM {category} WHERE id=' . db_param();
	db_query( $t_query, array( $p_category_id ) );

	# update bug history entries
	db_param_push();
	$t_query = 'SELECT id FROM {bug} WHERE category_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_category_id ) );

	while( $t_bug_row = db_fetch_array( $t_result ) ) {
		history_log_event_direct( $t_bug_row['id'], 'category', $t_category_row['name'], category_full_name( $p_new_category_id, false ) );
	}

	# update bug data
	db_param_push();
	$t_query = 'UPDATE {bug} SET category_id=' . db_param() . ' WHERE category_id=' . db_param();
	db_query( $t_query, array( $p_new_category_id, $p_category_id ) );
}

/**
 * Remove all categories associated with a project.
 * This will skip processing of categories that can't be deleted.
 * @param integer $p_project_id      A Project identifier.
 * @param integer $p_new_category_id New category id (to replace existing category).
 * @return boolean
 * @access public
 */
function category_remove_all( $p_project_id, $p_new_category_id = 0 ) {
	project_ensure_exists( $p_project_id );
	if( 0 != $p_new_category_id ) {
		category_ensure_exists( $p_new_category_id );
	}

	# cache category names
	category_get_all_rows( $p_project_id );

	# get a list of affected categories
	db_param_push();
	$t_query = 'SELECT id FROM {category} WHERE project_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_project_id ) );

	$t_category_ids = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		# Don't add category to the list if it can't be deleted
		if( !category_can_remove( $t_row['id'] ) ) {
			continue;
		}
		$t_category_ids[] = $t_row['id'];
	}

	# Handle projects with no categories
	if( count( $t_category_ids ) < 1 ) {
		return true;
	}

	$t_category_ids = join( ',', $t_category_ids );

	# update bug history entries
	$t_query = 'SELECT id, category_id FROM {bug} WHERE category_id IN ( ' . $t_category_ids . ' )';
	$t_result = db_query( $t_query );

	while( $t_bug_row = db_fetch_array( $t_result ) ) {
		history_log_event_direct( $t_bug_row['id'], 'category', category_full_name( $t_bug_row['category_id'], false ), category_full_name( $p_new_category_id, false ) );
	}

	# update bug data
	db_param_push();
	$t_query = 'UPDATE {bug} SET category_id=' . db_param() . ' WHERE category_id IN ( ' . $t_category_ids . ' )';
	db_query( $t_query, array( $p_new_category_id ) );

	# delete categories
	db_param_push();
	$t_query = 'DELETE FROM {category} WHERE project_id=' . db_param();
	db_query( $t_query, array( $p_project_id ) );

	return true;
}

/**
 * Return the definition row for the category
 * @param integer $p_category_id Category identifier.
 * @param boolean $p_error_if_not_exists true: error if not exists, otherwise return false.
 * @return array An array containing category details.
 * @access public
 */
function category_get_row( $p_category_id, $p_error_if_not_exists = true ) {
	global $g_category_cache;

	$p_category_id = (int)$p_category_id;

	if( isset( $g_category_cache[$p_category_id] ) ) {
		return $g_category_cache[$p_category_id];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {category} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_category_id ) );
	$t_row = db_fetch_array( $t_result );
	if( !$t_row ) {
		if( $p_error_if_not_exists ) {
			trigger_error( ERROR_CATEGORY_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$g_category_cache[$p_category_id] = $t_row;
	return $t_row;
}

/**
 * Sort categories based on what project they're in.
 * Call beforehand with a single parameter to set a 'preferred' project.
 * @param int|array $p_category1 Id of preferred project or array containing category details.
 * @param array $p_category2 Array containing category details.
 * @return integer|null An integer representing sort order.
 * @access public
 */
function category_sort_rows_by_project( $p_category1, array $p_category2 = null ) {
	static $s_project_id = null;
	if( is_null( $p_category2 ) ) {
		# Set a target project
		$s_project_id = $p_category1;
		return null;
	}

	if( !is_null( $s_project_id ) ) {
		if( $p_category1['project_id'] == $s_project_id && $p_category2['project_id'] != $s_project_id ) {
			return -1;
		}
		if( $p_category1['project_id'] != $s_project_id && $p_category2['project_id'] == $s_project_id ) {
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

/**
 * Cache categories from multiple projects
 * @param array $p_project_id_array Array of project identifiers.
 * @return void
 */
function category_cache_array_rows_by_project( array $p_project_id_array ) {
	global $g_category_cache, $g_cache_category_project;

	$c_project_id_array = array();

	foreach( $p_project_id_array as $t_project_id ) {
		if( !isset( $g_cache_category_project[(int)$t_project_id] ) ) {
			$c_project_id_array[] = (int)$t_project_id;
			$g_cache_category_project[(int)$t_project_id] = array();
		}
	}

	if( empty( $c_project_id_array ) ) {
		return;
	}

	$t_query = 'SELECT c.*, p.name AS project_name FROM {category} c
				LEFT JOIN {project} p
					ON c.project_id=p.id
				WHERE project_id IN ( ' . implode( ', ', $c_project_id_array ) . ' )
				ORDER BY c.name ';
	$t_result = db_query( $t_query );

	$t_rows = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$g_category_cache[(int)$t_row['id']] = $t_row;

		$t_rows[(int)$t_row['project_id']][] = $t_row['id'];
	}

	foreach( $t_rows as $t_project_id => $t_row ) {
		$g_cache_category_project[(int)$t_project_id] = $t_row;
	}
	return;
}

/**
 *	Get a distinct array of categories accessible to the current user for
 *	the specified projects.  If no project is specified, use the current project.
 *	If the current project is ALL_PROJECTS get all categories for all accessible projects.
 *	For all cases, get global categories and subproject categories according to configured inheritance settings.
 *	@param integer|null $p_project_id A specific project or null.
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
	foreach( $t_project_ids as $t_id ) {
		$t_categories = array_merge( $t_categories, category_get_all_rows( $t_id ) );
	}

	$t_unique = array();
	foreach( $t_categories as $t_category ) {
		if( !in_array( $t_category['name'], $t_unique ) ) {
			$t_unique[] = $t_category['name'];
		}
	}

	return $t_unique;
}

/**
 * Return all categories for the specified project id.
 * Obeys project hierarchies and such.
 * @param integer $p_project_id      A Project identifier.
 * @param boolean $p_inherit         Indicates whether to inherit categories from parent projects, or null to use configuration default.
 * @param boolean $p_sort_by_project Whether to sort by project.
 * @return array array of categories
 * @access public
 */
function category_get_all_rows( $p_project_id, $p_inherit = null, $p_sort_by_project = false ) {
	global $g_category_cache, $g_cache_category_project;

	if( isset( $g_cache_category_project[(int)$p_project_id] ) ) {
		if( !empty( $g_cache_category_project[(int)$p_project_id]) ) {
			foreach( $g_cache_category_project[(int)$p_project_id] as $t_id ) {
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

	$c_project_id = (int)$p_project_id;

	if( $c_project_id == ALL_PROJECTS ) {
		$t_inherit = false;
	} else {
		if( $p_inherit === null ) {
			$t_inherit = config_get( 'subprojects_inherit_categories' );
		} else {
			$t_inherit = $p_inherit;
		}
	}

	if( $t_inherit ) {
		$t_project_ids = project_hierarchy_inheritance( $p_project_id );
		$t_project_where = ' project_id IN ( ' . implode( ', ', $t_project_ids ) . ' ) ';
	} else {
		$t_project_where = ' project_id=' . $p_project_id . ' ';
	}

	$t_query = 'SELECT c.*, p.name AS project_name FROM {category} c
				LEFT JOIN {project} p
					ON c.project_id=p.id
				WHERE ' . $t_project_where . ' ORDER BY c.name';
	$t_result = db_query( $t_query );
	$t_rows = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_rows[] = $t_row;
		$g_category_cache[(int)$t_row['id']] = $t_row;
	}

	if( $p_sort_by_project ) {
		category_sort_rows_by_project( $p_project_id );
		usort( $t_rows, 'category_sort_rows_by_project' );
		category_sort_rows_by_project( null );
	}

	return $t_rows;
}

/**
 * Cache an set of category ids
 * @param array $p_cat_id_array Array of category identifiers.
 * @return void
 * @access public
 */
function category_cache_array_rows( array $p_cat_id_array ) {
	global $g_category_cache;
	$c_cat_id_array = array();

	foreach( $p_cat_id_array as $t_cat_id ) {
		if( !isset( $g_category_cache[(int)$t_cat_id] ) ) {
			$c_cat_id_array[] = (int)$t_cat_id;
		}
	}

	if( empty( $c_cat_id_array ) ) {
		return;
	}

	$t_query = 'SELECT c.*, p.name AS project_name FROM {category} c
				LEFT JOIN {project} p
					ON c.project_id=p.id
				WHERE c.id IN (' . implode( ',', $c_cat_id_array ) . ')';
	$t_result = db_query( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$g_category_cache[(int)$t_row['id']] = $t_row;
	}
	return;
}

/**
 * Given a category id and a field name, this function returns the field value.
 * An error will be triggered for a non-existent category id or category id = 0.
 * @param integer $p_category_id A category identifier.
 * @param string  $p_field_name  Field name.
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
 * @param integer $p_category_id A category identifier.
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
 * @param string  $p_category_name  Category name to retrieve.
 * @param integer $p_project_id     A project identifier.
 * @param boolean $p_trigger_errors Whether to trigger error on failure.
 * @return boolean
 * @access public
 */
function category_get_id_by_name( $p_category_name, $p_project_id, $p_trigger_errors = true ) {
	$t_project_name = project_get_name( $p_project_id );

	db_param_push();
	$t_query = 'SELECT id FROM {category} WHERE name=' . db_param() . ' AND project_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_category_name, (int)$p_project_id ) );
	$t_id = db_result( $t_result );
	if( $t_id === false ) {
		if( $p_trigger_errors ) {
			error_parameters( $p_category_name, $t_project_name );
			trigger_error( ERROR_CATEGORY_NOT_FOUND_FOR_PROJECT, ERROR );
		} else {
			return false;
		}
	}

	return $t_id;
}

/**
 * Retrieves category name (including project name if required)
 * @param string  $p_category_id     Category identifier.
 * @param boolean $p_show_project    Show project details.
 * @param integer $p_current_project Current project id override.
 * @return string category full name
 * @access public
 */
function category_full_name( $p_category_id, $p_show_project = true, $p_current_project = null ) {
	if( 0 == $p_category_id ) {
		# No Category
		return lang_get( 'no_category' );
	} else if( !category_exists( $p_category_id ) ) {
		return '@' . $p_category_id . '@';
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

/**
 * Check category can be deleted 
 * @param string $p_category_id Category identifier.
 * @return boolean Return true if the category valid for delete, otherwise false
 * @access public
 */
function category_can_delete( $p_category_id ) {
	db_param_push();
	$t_query = 'SELECT COUNT(id) FROM {bug} WHERE category_id=' . db_param();
	$t_bug_count = db_result( db_query( $t_query, array( $p_category_id ) ) );
	return $t_bug_count == 0;
}

/**
 * Ensure category can be deleted, otherwise raise an error.
 * @param string $p_category_id Category identifier.
 * @return void
 * @access public
 */
function category_ensure_can_delete( $p_category_id ) {
	if( !category_can_delete( $p_category_id ) ) {
		$t_category_name = category_get_name( $p_category_id );
		error_parameters( $t_category_name );
		trigger_error( ERROR_CATEGORY_CANNOT_DELETE_HAS_ISSUES, ERROR );
	}
}

