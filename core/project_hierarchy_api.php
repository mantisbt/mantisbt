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
 * Project Hierarchy API
 *
 * @package CoreAPI
 * @subpackage ProjectHierarchyAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses database_api.php
 */

require_api( 'constant_inc.php' );
require_api( 'database_api.php' );

$g_cache_project_hierarchy = null;
$g_cache_project_inheritance = null;
$g_cache_show_disabled = null;

/**
 * Add project to project hierarchy
 * @param integer $p_child_id       Child project identifier.
 * @param integer $p_parent_id      Parent project identifier.
 * @param boolean $p_inherit_parent Whether or not the child project inherits from the parent project.
 * @return void
 */
function project_hierarchy_add( $p_child_id, $p_parent_id, $p_inherit_parent = true ) {
	if( in_array( $p_parent_id, project_hierarchy_get_all_subprojects( $p_child_id ) ) ) {
		trigger_error( ERROR_PROJECT_RECURSIVE_HIERARCHY, ERROR );
	}

	db_param_push();
	$t_query = 'INSERT INTO {project_hierarchy}
		                ( child_id, parent_id, inherit_parent )
						VALUES
						( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array( $p_child_id, $p_parent_id, $p_inherit_parent ) );
}

/**
 * Update project hierarchy
 * @param integer $p_child_id       Child project identifier.
 * @param integer $p_parent_id      Parent project identifier.
 * @param boolean $p_inherit_parent Whether or not the child project inherits from the parent project.
 * @return void
 */
function project_hierarchy_update( $p_child_id, $p_parent_id, $p_inherit_parent = true ) {
	db_param_push();
	$t_query = 'UPDATE {project_hierarchy}
					SET inherit_parent=' . db_param() . '
					WHERE child_id=' . db_param() . '
						AND parent_id=' . db_param();
	db_query( $t_query, array( $p_inherit_parent, $p_child_id, $p_parent_id ) );
}

/**
 * Remove project from project hierarchy
 * @param integer $p_child_id  Child project identifier.
 * @param integer $p_parent_id Parent project identifier.
 * @return void
 */
function project_hierarchy_remove( $p_child_id, $p_parent_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {project_hierarchy} WHERE child_id = ' . db_param() . '
						AND parent_id = ' . db_param();

	db_query( $t_query, array( $p_child_id, $p_parent_id ) );
}

/**
 * Remove any project hierarchy entries relating to project_id
 * @param integer $p_project_id Project identifier.
 * @return void
 */
function project_hierarchy_remove_all( $p_project_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {project_hierarchy} WHERE child_id = ' . db_param() . '
						  OR parent_id = ' . db_param();

	db_query( $t_query, array( $p_project_id, $p_project_id ) );
}

/**
 * Returns true if project is at top of hierarchy
 * @deprecated This function is no longer used, but refactored in case it's called by external code.
 *
 * @param integer $p_project_id    Project identifier.
 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
 * @return boolean
 */
function project_hierarchy_is_toplevel( $p_project_id, $p_show_disabled = false ) {
	$t_graph = project_hierarchy_graph( $p_show_disabled );
	$t_reachable = $t_graph->get_reachable_projects( $p_project_id, ProjectGraph::PARENTS );
	# reachable projects include the project itself. In order to not have parents,
	# this array must contain more than one project.
	return ( count( $t_reachable ) <= 1 );
}

/**
 * Returns the id of the project's parent (ALL_PROJECTS if top-level or not found).
 * If there are more than one parent, the first one found is returned.
 * @deprecated This function is no longer used, but refactored in case it's called by external code.
 *
 * @param integer $p_project_id    Project Identifier.
 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
 * @return integer
 */
function project_hierarchy_get_parent( $p_project_id, $p_show_disabled = false ) {
	$t_graph = project_hierarchy_graph( $p_show_disabled );
	$t_node = $t_graph->get_node( $p_project_id );
	if( $t_node && !empty( $t_node['parents'] ) ) {
		return reset( $t_node['parents'] );
	} else {
		return ALL_PROJECTS;
	}
}

/**
 * Returns true if the child project inherits categories from the parent.
 * @param integer $p_child_id      Child project identifier.
 * @param integer $p_parent_id     Parent project identifier.
 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
 * @return boolean
 */
function project_hierarchy_inherit_parent( $p_child_id, $p_parent_id, $p_show_disabled = false ) {
	$t_graph = project_hierarchy_graph( $p_show_disabled );
	$t_node = $t_graph->get_node( (int)$p_child_id );
	if( $t_node ) {
		return isset( $t_node['inherit_categories'][(int)$p_parent_id] );
	}
	return false;
}

/**
 * Generate an array of project's the given project inherits from,
 * including the original project in the result.
 * @param integer $p_project_id    Project identifier.
 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
 * @return array
 */
function project_hierarchy_inheritance( $p_project_id, $p_show_disabled = false ) {
	static $cache = array();

	if( !isset( $cache[$p_project_id][$p_show_disabled] ) ) {
		$t_graph = project_hierarchy_graph( $p_show_disabled );
		$t_reachable = $t_graph->get_reachable_projects( (int)$p_project_id, ProjectGraph::INHERIT_CATEGORIES );

		# special case: if list is empty, it's becasue current project is disabled and
		# $p_show_disabled is false. Add current project to not break existing code
		if( empty( $t_reachable ) ) {
			$t_reachable[] = (int)$p_project_id;
		}
		$cache[$p_project_id][$p_show_disabled] = $t_reachable;
	}

	return $cache[$p_project_id][$p_show_disabled];
}

/**
 * Returns all projects that are immediate subprojects, this is, only one depth level
 * below the specified project id.
 * The project id provided as parameter won't appear in the result.
 *
 * This function uses the actual project hierarchy and does not account for user visibility.
 *
 * @param integer $p_project_id    Project id to start the search of subprojects.
 * @param boolean $p_show_disabled Whether or not to return disabled.
 * @return array    Array of project ids, or empty array if none found.
 */
function project_hierarchy_get_subprojects( $p_project_id, $p_show_disabled = false ) {
	$t_graph = project_hierarchy_graph( $p_show_disabled );
	$t_traverse_options = array(
		'max_depth' => 1,
		'start_node' => $p_project_id
		);
	$t_graph->traverse( $t_traverse_options );
	$t_children = $t_graph->traverse_visited_ids;

	# the current root project is not expected in the result, remove it
	return array_diff( $t_children, array( $p_project_id ) );
}

/**
 * Returns all projects that are subprojects, reachable at any depth, from the
 * specified project id.
 * The project id provided as parameter won't appear in the result.
 *
 * This function uses the actual project hierarchy and does not account for user visibility.
 *
 * @param integer $p_project_id    Project id to start the search of subprojects.
 * @param boolean $p_show_disabled Whether or not to return disabled.
 * @return array    Array of project ids, or empty array if none found.
 */
function project_hierarchy_get_all_subprojects( $p_project_id, $p_show_disabled = false ) {
	$t_graph = project_hierarchy_graph( $p_show_disabled );
	$t_reachable = $t_graph->get_reachable_projects( $p_project_id );

	# the current root project is not expected in the result, remove it
	return array_diff( $t_reachable, array( $p_project_id ) );
}

/**
 * Creates a projects graph object with the accesible projects for a user.
 *
 * @param null|integer $p_user_id   A user id to evaluate project access, or null to use current
 * @param boolean $p_show_disabled  Whether to include disabled projects
 * @return \ProjectGraph	ProjectGraph object for the visible projects hierarchy
 */
function project_hierarchy_graph_visible_projects( $p_user_id = null, $p_show_disabled = false ) {
	static $s_cache_visible_projects = array();

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	if( !isset( $s_cache_visible_projects[$p_user_id][$p_show_disabled] ) ) {
		$t_project_graph = new ProjectGraph( array(
			'for_user' => $p_user_id,
			'show_disabled' => $p_show_disabled,
			'sort' => ProjectGraph::SORT_NAME_ASC,
			'sort_target' => ProjectGraph::CHILDREN
			) );
		$s_cache_visible_projects[$p_user_id][$p_show_disabled] = $t_project_graph;
	}
	return $s_cache_visible_projects[$p_user_id][$p_show_disabled];
}

/**
 * Creates a projects graph object with all existing projects
 *
 * This function uses the actual project hierarchy and does not account for user visibility.
 *
 * @param boolean $p_show_disabled    Whether to include disabled projects
 * @return \ProjectGraph   ProjectGraph object for the projects hierarchy
 */
function project_hierarchy_graph( $p_show_disabled = true ) {
	static $s_cache_graph = array();

	if( !isset( $s_cache_graph[$p_show_disabled] ) ) {
		$s_cache_graph[$p_show_disabled] =
			new ProjectGraph( array(
				'for_user' => ALL_USERS,
				'show_disabled' => $p_show_disabled,
				'sort' => null,
				) );
	}

	return $s_cache_graph[$p_show_disabled];
}

/**
 * Returns a list representation of the hierarchical tree of projects accessible to a user.
 * Each item is an array with useful info, in the order of a tree traversal.
 * The root node is by default ALL_PROJECTS, but a specific project id can be used as
 * root node to retrieve a partial tree.
 *
 * @param null|integer $p_user_id   A user id to evaluate project access, or null to use current
 * @param integer $p_project_id     A project_id to be used as root of the tree.
 * @param boolean $p_show_disabled  Whether to include disabled projects
 * @see \ProjectGraph         Refer to ProjectGraph::travese() for a detailed description of
 *                            the list representation.
 * @return array	Array of items, each item is an array with associative info about the projects.
 */
function project_hierarchy_list_visible_projects( $p_user_id = null, $p_project_id = ALL_PROJECTS, $p_show_disabled = false ) {
	$t_graph = project_hierarchy_graph_visible_projects( $p_user_id, $p_show_disabled );
	return $t_graph->traverse( ['start_node' => $p_project_id] );
}