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
 * ProjectGraph class.
 * @copyright Copyright 2019 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses project_api.php
 */

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'project_api.php' );


/**
 * A class that builds a graph representation of all projects and subprojects, providing
 * simple tools to inspect and display the relations and inheritance between projects.
 *
 * The graph can be a complete graph for all existing projects, or can be limited to a
 * subset of projects (see constructor options). This subgraph can be based on user visibility,
 * enabled/disbaled projects, an access threshold, or an explicit set of project ids.
 * When restrictions are applied to the inclusion of projects, the graph is first built
 * with all projects and then reduced by removing those project nodes that don't meet the
 * contraints, while relinking the adjacent nodes to mantain the relative hierarchy.
 * For example: having projects: A, with B as child, and C as B's child, after removing B
 * C will become A's child.
 *
 * Graph nodes contain the relations to adjacent nodes in separate propertioes for node
 * parents, children, and inheritance (from parents). This relations can be used to
 * inspect a project children, parents or inheritance parents independently.
 *
 * The main tool to inspect the hierarchy is the traversal function, visiting the graph nodes
 * in any direction (parents, children, inheritance). The result is a tree traversal with
 * useful info about the order and depth of each visited node.
 * The traversal options offers a wide range of configuration to serve multiple purposes:
 * Inspect full hierarchy, immediate descendats, reachable nodes, inherited projects...
 */
class ProjectGraph {

	/**
	 * Constants defining a sorting method to be used in sort()/"sort" option
	 */
	const SORT_NAME_ASC = 1;
	const SORT_NAME_DESC = 2;

	/**
	 * These constants represent each type of relation a node can have to other nodes.
	 * They are defined as bitmask to be able to combine them.
	 * For example in sort()/"sort_target" option as a combination:
	 *  "PARENTS | CHILDREN" to sort both groups of relations
	 */
	const ALL = ~0;
	const PARENTS             = 0b001;
	const CHILDREN            = 0b010;
	const INHERIT_CATEGORIES  = 0b100;

	/**
	 * Default options for the graph.
	 * These can be individually overriden with the constructor parameter.
	 * @var array
	 */
	protected $default_options = array(
		'for_user' => ALL_USERS,
		'show_disabled' => false,
		'filter_threshold' => null,
		'sort' => null,
		'sort_target' => self::CHILDREN,
		'cache' => true,
		'limit_projects' => null
		);

	/**
	 * Default options for the traversal.
	 * These can be individually overriden with the function parameter.
	 * @var array
	 */
	protected $default_traverse_options = array(
		'max_depth' => -1,
		'start_node' => ALL_PROJECTS,
		'direction' => self::CHILDREN,
		'max_leaf' => -1,
		'duplicates' => true,
		'include_all_projects' => true
		);

	/**
	 * Actual option array used in the graph.
	 * @var array
	 */
	protected $options;

	/**
	 * Storage of the raw data retrieved from hierarchy table
	 * @var array
	 */
	protected $hierarchy_data = null;

	/**
	 * Storage for the graph data. Each item, indexed by project_id, represent
	 * a node as an array record containing:
	 *		'project_id' => integer, project id of the current node.
	 *		'parents' => array of parent project ids
	 *		'children' => array of children project ids
	 *		'inherit_categories' => array of project ids from which this node
	 *                              inherits categories.
	 * @var array
	 */
	protected $graph_data = null;

	/**
	 * Storage for cached data that can be reused between object instances
	 * @var array
	 */
	protected static $cached = array();

	/**
	 * An array of project ids that have been visited by the latest traverse() execution
	 * @var array
	 */
	public $traverse_visited_ids = null;

	/**
	 * A boolean flag that indicates if a cycle was detected in the latest traverse() execution
	 * @var boolean
	 */
	public $traverse_cycle_detected = null;

	/**
	 * Constructor for the graph.
	 *
	 * @param array $p_options    An array of options, as ( option => value ). Available options are:
	 *
	 *    for_user: integer       A user id to check visibility of projects.
	 *                            ALL_USERS to not apply access checks.
	 *
	 *    show_disabled: boolean  Whether to include disabled projects
	 *
	 *    filter_threshold: null|integer|array|string
	 *                            An access threshold to evaluate for each project, and remove nodes
	 *                            that don't match (needs to have a user_id specified).
	 *                            This can be an integer/array access level threshold, or a string
	 *                            meaning a configuration name that will be evaluated with config_get().
	 *
	 *     sort: null|integer|Callable  Whether to sort the related projects at each node. Can be either:
	 *                            - null, meaning no sorting will be made which is faster if not needed.
	 *                            - An integer, meaning use one of predefined sorting methods (see class
	 *                              constants) for example: ProjectGraph::SORT_NAME_ASC
	 *                            - A Callable instance, for custom comparing function.
	 *
	 *     sort_target: integer   What relation(s) will be sorted. See class constants, being a bitmask
	 *                            that can be logically combined to sort one or several relation types.
	 *                             For example: "PARENTS | CHILDREN"
	 *
	 *     cache: boolean		  Whether this instance uses and updates the cached data. This can be
	 *                            turned off in scenarios where a lot of different graphs are needed
	 *                            (eg, for different users) that won't be reused, saving memory usage.
	 *
	 *     limit_projects: null|array  An array of project ids to prune the graph around them.
	 *                            By providing a list of projects here, the graph will be reduced to
	 *                            contain only those specified projects. The reduction is done based
	 *                            on the initial hierarchy, and will delete nodes not contained in this
	 *                            list. The deletion of a node will perform a relinking of adjacent relations.
	 *                            Note that the special node for ALL_PROJECT will still exist as the root
	 *                            of the graph, even if it was not explicitly contained in the list.
	 */
	public function __construct( array $p_options = array() ) {
		$this->options = $p_options + $this->default_options;

		# The raw hierarchy data and the full graph can be cached, with the only variance
		# being the user id due to visibility checks. Later steps for filter, sorting and
		# traversal are performed at later steps.
		# So we can cache the data for reuse in new instances.
		$t_user_id = $this->options['for_user'];
		$t_use_cache = $this->options['cache'] && null === $this->options['limit_projects'];
		if( $t_use_cache && isset( self::$cached[$t_user_id] ) ) {
			$this->hierarchy_data = self::$cached[$t_user_id]['hierarchy_data'];
			$this->graph_data = self::$cached[$t_user_id]['graph_data'];
		} else {
			$this->query_data();
			$this->build_full_graph();
			if( $t_use_cache ) {
				self::$cached[$t_user_id]['hierarchy_data'] = $this->hierarchy_data;
				self::$cached[$t_user_id]['graph_data'] = $this->graph_data;
			}
		}

		$this->filter();

		if( null !== $this->options['sort'] ) {
			$this->sort(  $this->options['sort'], $this->options['sort_target'] );
		}
	}

	/**
	 * Internal step for the construction of the graph.
	 * Queries the hierarchy table and prepares the raw inheritance data.
	 * 
	 * @global array $g_cache_project  This is the global project rows cache from project_api.
	 *                                 It's used directly to improve performance calling for related
	 *                                 information for projects. We ensure to call project_cache_all()
	 *                                 to have this cache ready.
	 * @return void
	 */
	protected function query_data() {
		global $g_cache_project;
		$t_limit_projects = is_array( $this->options['limit_projects'] );

		$t_query = new DbQuery();
		$t_sql = 'SELECT p.id, COALESCE (ph.parent_id, 0) AS parent_id, ph.inherit_parent  FROM {project} p'
				. ' LEFT JOIN {project_hierarchy} ph ON p.id = ph.child_id';
		if( $t_limit_projects ) {
			# Add ALL_PROJECTS to the delimited set of prjects, if not already there.
			$t_delimited_projects = array_merge( $this->options['limit_projects'], [ALL_PROJECTS] );
			$t_sql .= ' WHERE ' . $t_query->sql_in( 'p.id', $t_delimited_projects );
		}
		$t_query->sql( $t_sql );

		$this->hierarchy_data = array();
		while( $t_row = $t_query->fetch() ) {
			$c_id = (int)$t_row['id'];
			$c_parent_id = (int)$t_row['parent_id'];
			$this->hierarchy_data[$c_id]['parents'][$c_parent_id] = $c_parent_id;
			if( 1 == $t_row['inherit_parent'] ) {
				$this->hierarchy_data[$c_id]['inherit_categories'][$c_parent_id] = $c_parent_id;
			}
		}

		project_cache_all();

		$t_user_id = $this->options['for_user'];

		# To determine visibility of projects, as fast as possible:
		# - If we don't specify a user, there is no restriction for visibility
		# - If we specify a user, and the project is private:
		#   - if the user have private projects privilege, all projects are visible
		#   - otherwise, we'll need to check if the user is assigned to each project.
		$t_check_visible = $t_user_id != ALL_USERS
				&& !access_has_global_level( config_get( 'private_project_threshold', null, ALL_USERS, ALL_PROJECTS ), $this->options['for_user'] );

		foreach( $this->hierarchy_data as $t_project_id => &$t_data ) {
			# For project fields, use project rows cache. Those are filled becasue
			# we have called project_cache_all()
			$t_data['enabled'] = $g_cache_project[$t_project_id]['enabled'];

			# If the projects are delimited, remove references to parents outside of the delimited set.
			if( $t_limit_projects ) {
				$t_data['parents'] = array_intersect( $t_data['parents'], $t_delimited_projects );
				# if no parents are left, place as child of ALL_PROJECTS
				if( empty( $t_data['parents'] ) ) {
					$t_data['parents'][] = ALL_PROJECTS;
				}
			}

			# initialize the catgory inheritance array, if it's empty
			if( !isset( $t_data['inherit_categories'] ) ) {
				$t_data['inherit_categories'] = array();
			}
			# if the project inherits global categories, add ALL_PROJECTS to category inheritance
			if( 1 == $g_cache_project[$t_project_id]['inherit_global'] ) {
				$t_data['inherit_categories'][ALL_PROJECTS] = ALL_PROJECTS;
			}

			# access_get_local_level() relies in cache, and returns false if the user is not
			# assigned to that project. We don't care of the actual returned access level.
			if( $t_check_visible && $g_cache_project[$t_project_id]['view_state'] != VS_PUBLIC ) {
				$t_data['visible'] = false !== access_get_local_level( $t_user_id, $t_project_id );
			} else {
				$t_data['visible'] = true;
			}
		}
		unset( $t_data ); # clean up reference variable
	}

	/**
	 * Internal step for the construction of the graph.
	 * Builds the graph representation based on previously read raw hierarchy data.
	 *
	 * @return void
	 */
	protected function build_full_graph() {
		$this->graph_data = array();

		# create a node for ALL_PROJETS
		$this->graph_data[ALL_PROJECTS] = array(
			'project_id' => ALL_PROJECTS,
			'parents' => array(),
			'children' => array(),
			'inherit_categories' => array()
			);

		# create a node for each project. We know the parents for each project
		# even those which have no parents (we have previously filled with ALL_PROJECT)
		foreach( $this->hierarchy_data as $t_id => $t_data ){
			$this->graph_data[$t_id] = array(
			'project_id' => $t_id,
			'parents' => $t_data['parents'],
			'children' => array(),
			'inherit_categories' => $t_data['inherit_categories']
			);
		}

		# now fill the info for children
		foreach( $this->graph_data as $t_id => $t_node ) {
			# for each project, take its parents, go to those nodes, and update
			# them with a link to children
			foreach( $t_node['parents'] as $t_parent_id ) {
				$this->graph_data[$t_parent_id]['children'][$t_id] = $t_id;
			}
		}
	}

	/**
	 * Internal step for the construction of the graph.
	 * Based on the selected criteria for the graph, we will prune nodes.
	 * When removing a node P, the adjacent nodes will have its relations relinked
	 * For example: P's children will become children to P's parents
	 *
	 * @return void
	 */
	protected function filter() {
		# prepare which conditions will be checked for filtering
		$t_filter_visible = $this->options['for_user'] != ALL_USERS;
		$t_filter_threshold = $t_filter_visible && null !== $this->options['filter_threshold'];
		$t_filter_disabled = $this->options['show_disabled'] != true;
		$t_needs_filtering = $t_filter_visible || $t_filter_threshold || $t_filter_disabled;
		if( !$t_needs_filtering ) {
			return;
		}

		# prepare some helper variables
		$t_threshold_str = is_string( $this->options['filter_threshold'] ) ? $this->options['filter_threshold'] : null;
		$t_threshold_value = $this->options['filter_threshold'];
		$t_user_id = $this->options['for_user'];

		foreach( $this->graph_data as $t_project_id => $t_node ) {
			$t_remove = false;
			if( $t_project_id == ALL_PROJECTS ) {
				continue;
			}
			if( $t_filter_visible ) {
				$t_remove = !$this->hierarchy_data[$t_project_id]['visible'];
			}
			if( !$t_remove  && $t_filter_disabled ) {
				$t_remove = !$this->hierarchy_data[$t_project_id]['enabled'];
			}
			if( !$t_remove && $t_filter_threshold ) {
				$t_threshold = $t_threshold_str ?
						config_get( $t_threshold_str, null, $t_user_id, $t_project_id )
						: $t_threshold_value;
				$t_remove = !access_has_project_level( $t_threshold, $t_project_id, $t_user_id );
			}
			if( $t_remove ) {
				$this->graph_drop_node_and_relink( $t_project_id );
			}
		}
	}

	/**
	 * Internal helper function to perform removal of a node from the graph,
	 * and manage relinking of relations in adjacent nodes
	 *
	 * @param integer $p_project_id  Project id of the target node
	 */
	protected function graph_drop_node_and_relink( $p_project_id ) {
		# This node will be removed from the graph, and adjacent nodes will be relinked
		$t_node = $this->graph_data[$p_project_id];

		foreach( $t_node['children'] as $t_child_id ) {
			# remove this node reference as parent in its children
			unset( $this->graph_data[$t_child_id]['parents'][$p_project_id] );
			# merge this node's parents into each child's parents
			$this->graph_data[$t_child_id]['parents'] += $t_node['parents'];
			# merge category inheritance
			$this->graph_data[$t_child_id]['inherit_categories'] += $t_node['inherit_categories'];
		}

		foreach( $t_node['parents'] as $t_parent_id ) {
			# remove this node reference as child in its parents
			unset( $this->graph_data[$t_parent_id]['children'][$p_project_id] );
			# merge this node's children into each parent's children
			$this->graph_data[$t_parent_id]['children'] += $t_node['children'];
		}

		# remove this node
		unset( $this->graph_data[$p_project_id] );
	}

	/**
	 * A versatile traversal algorithm to get a tree representation of the graph.
	 * Performs a preorder depth first tree traversal of the graph, discarding cycles
	 * that may occur at each branch.
	 * Returns an array of items, each one is an associative array with useful info of
	 * each visited node, arranged in the order of the traversal.
	 *
	 * Treversal direction can follow any of the relation types: children, parents
	 * or inheritance.
	 *
	 * Note : A special node for ALL_PROJECTS exists in the graph, that allows to traverse
	 * the full tree of projects in a consistent way, as it will allways be the root node
	 * for all 1-st level projects.
	 *
	 * After this function execution, the following class properties will be populated
	 * with relevant information:
	 * - traverse_visited_ids: Array with the list of visited nodes, in no particular order.
	 * - traverse_cycle_detected: True if a cycle occurs. False otherwise.
	 *
	 * The returnsed array is a set of elements, each one being an associative array:
	 *   'id' => The project id.
	 *   'depth' => The depth level in the traversal, being 0 the starting project node.
	 *   'path' => An array of project ids spanning the path from the root node to the
	 *             current project in this record, including both.
	 *
	 * @param $p_traverse_options   An array of options, as (key => value) pairs.
	 *                              Available options are:
	 *
	 *    max_depth: integer     Sets a limit for the depth of the tree. If set, when visiting
	 *                           the N-th level in the tree, the travelsal is forced to backtrack
	 *                           and won't go further. Use "-1" for no limitation.
	 *
	 *    start_node: null|integer  Specify a project id for the node to be used as root for the tree.
	 *                           For a top-down full tree spanning children, ALL_PROJECTS will
	 *                           allways be a root node which is parent to all 1-st level projects.
	 *
	 *    direction: integer     Use class constants to specify which relations are to be followed
	 *                           by the traversal. This allow to build a top-down tree spannign
	 *                           children, a bottom-up path to parents or inheritable parents (using
	 *                           $p_start_node to set the starting point)
	 *
	 *    max_leaf: integer      Set a limit for the maximum leaf count for visited projects. Once this
	 *                           limit is reached, the traversal stops. Use "-1" for no limitation.
	 *
	 *    duplicates: boolean    If true, projects are allowed to appear duplicated at different places
	 *                           in the tree, due to being a child of multiple parents.
	 *                           If false, the projects appear only once in the tree, and succesive
	 *                           visits to the node are discarded.
	 *
	 *    include_all_projects: boolean   Whether to include ALL_PROJECTS in the traversal result.
	 *                           If false, the special project ALL_PROJECTS won't appear as a reachable
	 *                           or visited node, and any link pointing to it won't be followed.
	 *
	 * @return array   An array of records representing relevant info for each visited project,
	 *                 sorted in the order of the traversal.
	 */
	public function traverse( array $p_traverse_options = array() ) {
		$t_params = $p_traverse_options + $this->default_traverse_options;
		# reset stats, in case of early return
		$this->traverse_visited_ids = array();
		$this->traverse_cycle_detected = null;

		if( !isset( $this->graph_data[$t_params['start_node']] ) ) {
			return array();
		}
		$t_result = array();
		$t_stack = array();

		# prepare the key for the array of adjacent nodes, based on the requested direction
		switch( $t_params['direction'] ) {
			case self::INHERIT_CATEGORIES:
				$t_key_links = 'inherit_categories';
				break;
			case self::PARENTS:
				$t_key_links = 'parents';
				break;
			case self::CHILDREN:
			default:
				$t_key_links = 'children';
		}

		# Initialize the stack with the starting root node
		$t_node = $this->graph_data[$t_params['start_node']];
		$t_stack[] = array( 'node' => $t_node, 'level' => 0, 'path' => array() );

		$t_leaf_count = 0;
		$t_visited = array();
		$t_cycle_detected = false;
		while( $t_item = array_pop( $t_stack ) ) {
			# if we have reached a maximum leaf count, then stop
			if( $t_params['max_leaf'] >= 0 && $t_leaf_count >= $t_params['max_leaf'] ) {
				break;
			}

			$t_node = $t_item['node'];
			$t_path = $t_item['path'];
			$t_level = $t_item['level'];

			# if the node is already part of the path to this node
			# discard it to avoyd a cycle
			$t_cycle = in_array( $t_node['project_id'], $t_path );
			if( $t_cycle ) {
				$t_cycle_detected = true;
				continue;
			}

			if( $t_params['include_all_projects'] || ALL_PROJECTS != $t_node['project_id'] ) {
				$t_path[] = $t_node['project_id'];
				# add current node to the result list
				$t_result[] = array(
					'id' => $t_node['project_id'],
					'level' => $t_level,
					'path' => $t_path
				);
				$t_visited[$t_node['project_id']] = $t_node['project_id'];
			}

			# check for max depth if specified
			if( $t_params['max_depth'] >= 0 && $t_level >= $t_params['max_depth'] ) {
				# backtrack after deepest allowed node counts as a leaf node
				$t_leaf_count++;
				continue;
			}

			$t_items_added = 0;
			if( !empty( $t_node[$t_key_links] ) ) {
				# push linked nodes into the stack
				# the array is looped backwards to mantain the ordering (LIFO)
				for( end( $t_node[$t_key_links] ); false !== $t_linked_id = current( $t_node[$t_key_links] ); prev( $t_node[$t_key_links] ) ) {
					if( !$t_params['include_all_projects'] && ALL_PROJECTS == $t_linked_id ) {
						continue;
					}
					if( !$t_params['duplicates'] && isset( $t_visited[$t_linked_id] ) ) {
						continue;
					}
					if( isset( $this->graph_data[$t_linked_id] ) ) {
						$t_stack[] = array(
							'node' => $this->graph_data[$t_linked_id],
							'level' => $t_level+1,
							'path' => $t_path
						);
						$t_items_added++;
					}
				}
			}
			if( $t_items_added == 0 ) {
				# no more links to follow means it's a leaf node
				$t_leaf_count++;
			}
		}
		# save stats
		$this->traverse_visited_ids = $t_visited;
		$this->traverse_cycle_detected = $t_cycle_detected;

		return $t_result;
	}

	/**
	 * Internal step for the construction of the graph.
	 * Sort each node's relations array contents. By having the relation links ordered,
	 * the traveral result will mantain this order in the tree result.
	 *
	 * @global array $g_cache_project  Project rows cache from project_api
	 * @param integer $p_sort_method   See class constants
	 * @param integer $p_target        See class constants. Bitmask typed.
	 * @return void
	 */
	protected function sort( $p_sort_method = self::SORT_NAME_ASC, $p_target = self::ALL ) {
		global $g_cache_project;

		if( null === $p_sort_method ) {
			return;
		}
		# prepare the compare function
		switch( $p_sort_method ) {
			case self::SORT_NAME_ASC:
				$fn_cmp = function ( $a, $b ) use( $g_cache_project ) {
					return strnatcasecmp( $g_cache_project[$a]['name'], $g_cache_project[$b]['name'] );
				};
				break;
			case self::SORT_NAME_DESC:
				$fn_cmp = function ( $a, $b ) use( $g_cache_project ) {
					return strnatcasecmp( $g_cache_project[$b]['name'], $g_cache_project[$a]['name'] );
				};
				break;

			default:
				if( is_callable( $p_sort_method ) ) {
					$fn_cmp = $p_sort_method;
				} else {
					return;
				}
		}

		# iterate the node list and sort each node's members
		foreach( $this->graph_data as $t_project_id => &$t_node ) {
			if( self::PARENTS & $p_target ) {
				uasort( $t_node['parents'], $fn_cmp );
			}
			if( self::CHILDREN & $p_target ) {
				uasort( $t_node['children'], $fn_cmp );
			}
			if( self::INHERIT_CATEGORIES & $p_target ) {
				uasort( $t_node['inherit_categories'], $fn_cmp );
			}
		}
		unset( $t_node );
	}

	/**
	 * Utility function to get all projects that are reachable from some initial
	 * project, without explicit information of ordering or relative hierarchy,
	 * by following the relations specfied by the "direction" parameter.
	 * This is a wrapper for a simplified graph traversal.
	 *
	 * Note that the initial node is included in the reachable nodes.
	 * However, the special id ALL_PROJECTS wont't be considered if its the initial
	 * node in the search. But it can appear in the result if it's a visited node,
	 * for example: a final node following parents relations.
	 *
	 * @param integer $p_start_project  A project id to start the traversal
	 * @param integer $p_direction      The relations to follow in the traversal. See class constants.
	 * @return array    Array of reachable projects, in no specific order.
	 */
	public function get_reachable_projects( $p_start_project = ALL_PROJECTS, $p_direction = self::CHILDREN ) {
		# special case: all reachable projects under ALL_PROJECTS, following
		# children relations, are effectively all the projects contained in the graph
		if( ALL_PROJECTS == $p_start_project && self::CHILDREN == $p_direction ) {
			# ALL_PROJECTS, is always node [0]
			# if present, discard it to not have it included
			return array_diff( array_keys( $this->graph_data ), [ALL_PROJECTS] );
		}

		$t_traverse_options = array(
			'start_node' => $p_start_project,
			'max_depth' => -1,
			'direction' => $p_direction,
			'duplicates' => false,
			'include_all_projects' => true
			);
		$this->traverse( $t_traverse_options );
		return $this->traverse_visited_ids;
	}

	/**
	 * Returns the internal graph data
	 * @return array
	 */
	public function get_graph_data() {
		return $this->graph_data;
	}

	/**
	 * Returns the node data associated to a project id,
	 * or null if the project does not exists in teh graph.
	 * @param integer $p_project_id  A valid project id
	 * @return null|array
	 */
	public function get_node( $p_project_id ) {
		if( !isset( $this->graph_data[$p_project_id] ) ) {
			return null;
		}
		return $this->graph_data[$p_project_id];
	}

	/**
	 * Helper function to build a path from the graph root to the specified project.
	 * Performs a traversal starting at the specified project, following parents
	 * relations. In the case that a project have multiple parents, only one of them
	 * will be followed, the first one based on the initial graph sorting parameters.
	 *
	 * The result will be an array of project ids, from top to bottom ending in the
	 * specified project. This array will contain ALL_PROJECTS as starting point to
	 * be consistent with the graph representation.
	 *
	 * Returns null if the specified project does not exist in the graph.
	 *
	 * @param integer $p_project_id  A project id to get the path for.
	 * @return null|array   Array of projects ids representing the path.
	 */
	public function get_project_trace( $p_project_id ) {
		if( ALL_PROJECTS == $p_project_id ) {
			return array( ALL_PROJECTS );
		}
		if( !isset( $this->graph_data[$p_project_id] ) ) {
			return null;
		}

		# traverse the graph: without depth limit, starting at current project,
		#  following parents relations, with a limit of 1 leaf, no duplicates
		$t_traverse_options = array(
			'depth' => -1,
			'start_node' => $p_project_id,
			'direction' => ProjectGraph::PARENTS,
			'max_leaf' => 1,
			'duplicates' => false,
			'include_all_projects' => false
			);
		$t_list = $this->traverse( $t_traverse_options );

		# an empty list means the input is not an accesible project
		if( empty( $t_list ) ) {
			return null;
		}
		# The last node in our list is the 1st level parent.
		$t_top_parent = array_pop( $t_list );

		# The calculated path is the reverse of the requested parent-to-child path
		return array_reverse( $t_top_parent['path'] );
	}
}