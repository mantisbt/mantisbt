<?php
# Mantis - a php based bugtracking system

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


/**
 * Project Hierarchy API
 * @package CoreAPI
 * @subpackage ProjectHierarchyAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$t_core_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

function project_hierarchy_add( $p_child_id, $p_parent_id, $p_inherit_parent = true ) {
	if( in_array( $p_parent_id, project_hierarchy_get_all_subprojects( $p_child_id ) ) ) {
		trigger_error( ERROR_PROJECT_RECURSIVE_HIERARCHY, ERROR );
	}

	$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );

	$c_child_id = db_prepare_int( $p_child_id );
	$c_parent_id = db_prepare_int( $p_parent_id );
	$c_inherit_parent = db_prepare_bool( $p_inherit_parent );

	$query = "INSERT INTO $t_project_hierarchy_table
		                ( child_id, parent_id, inherit_parent )
						VALUES
						( " . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';

	db_query_bound( $query, Array( $c_child_id, $c_parent_id, $c_inherit_parent ) );
}

function project_hierarchy_update( $p_child_id, $p_parent_id, $p_inherit_parent = true ) {
	$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );

	$c_child_id = db_prepare_int( $p_child_id );
	$c_parent_id = db_prepare_int( $p_parent_id );
	$c_inherit_parent = db_prepare_bool( $p_inherit_parent );

	$query = "UPDATE $t_project_hierarchy_table
					SET inherit_parent=" . db_param() . '
					WHERE child_id=' . db_param() . '
						AND parent_id=' . db_param();
	db_query_bound( $query, Array( $c_inherit_parent, $c_child_id, $c_parent_id ) );
}

function project_hierarchy_remove( $p_child_id, $p_parent_id ) {
	$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );

	$c_child_id = db_prepare_int( $p_child_id );
	$c_parent_id = db_prepare_int( $p_parent_id );

	$query = "DELETE FROM $t_project_hierarchy_table
		                WHERE child_id = " . db_param() . "
						AND parent_id = " . db_param();

	db_query_bound( $query, Array( $c_child_id, $c_parent_id ) );
}

function project_hierarchy_remove_all( $p_project_id ) {
	$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );

	$c_project_id = db_prepare_int( $p_project_id );

	$query = "DELETE FROM $t_project_hierarchy_table
		                WHERE child_id = " . db_param() . "
						  OR parent_id = " . db_param();

	db_query_bound( $query, Array( $c_project_id, $c_project_id ) );
}

function project_hierarchy_is_toplevel( $p_project_id ) {
	global $g_cache_project_hierarchy;

	if( null === $g_cache_project_hierarchy ) {
		project_hierarchy_cache();
	}

	if( isset( $g_cache_project_hierarchy[ALL_PROJECTS] ) ) {
		return in_array( $p_project_id, $g_cache_project_hierarchy[ALL_PROJECTS] );
	} else {
		return false;
	}
}

$g_cache_project_hierarchy = null;
$g_cache_project_inheritance = null;

function project_hierarchy_cache( $p_show_disabled = false ) {
	global $g_cache_project_hierarchy, $g_cache_project_inheritance;
	global $g_cache_show_disabled;

	if( !is_null( $g_cache_project_hierarchy ) && ( $g_cache_show_disabled == $p_show_disabled ) ) {
		return;
	}
	$g_cache_show_disabled = $p_show_disabled;

	$t_project_table = db_get_table( 'mantis_project_table' );
	$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );
	$t_enabled_clause = $p_show_disabled ? '1=1' : 'p.enabled = ' . db_param();

	$query = "SELECT DISTINCT p.id, ph.parent_id, p.name, p.inherit_global, ph.inherit_parent
				  FROM $t_project_table p
				  LEFT JOIN $t_project_hierarchy_table ph
				    ON ph.child_id = p.id
				  WHERE $t_enabled_clause
				  ORDER BY p.name";

	$result = db_query_bound( $query, ( $p_show_disabled ? null : Array( true ) ) );
	$row_count = db_num_rows( $result );

	$g_cache_project_hierarchy = array();
	$g_cache_project_inheritance = array();

	for( $i = 0;$i < $row_count;$i++ ) {
		$row = db_fetch_array( $result );

		if( null === $row['parent_id'] ) {
			$row['parent_id'] = ALL_PROJECTS;
		}

		if( isset( $g_cache_project_hierarchy[$row['parent_id']] ) ) {
			$g_cache_project_hierarchy[$row['parent_id']][] = $row['id'];
		} else {
			$g_cache_project_hierarchy[$row['parent_id']] = array(
				$row['id'],
			);
		}

		if( !isset( $g_cache_project_inheritance[$row['id']] ) ) {
			$g_cache_project_inheritance[$row['id']] = array();
		}

		if( $row['inherit_global'] && !isset( $g_cache_project_inheritance[$row['id']][ALL_PROJECTS] ) ) {
			$g_cache_project_inheritance[$row['id']][] = ALL_PROJECTS;
		}
		if( $row['inherit_parent'] && !isset( $g_cache_project_inheritance[$row['id']][$row['parent_id']] ) ) {
			$g_cache_project_inheritance[$row['id']][] = (int) $row['parent_id'];
		}
	}
}

# Returns true if the child project inherits categories from the parent.
function project_hierarchy_inherit_parent( $p_child_id, $p_parent_id ) {
	global $g_cache_project_inheritance;

	return in_array( $p_parent_id, $g_cache_project_inheritance[$p_child_id] );
}

# Generate an array of project's the given project inherits from,
# including the original project in the result.
function project_hierarchy_inheritance( $p_project_id ) {
	global $g_cache_project_inheritance;

	$t_project_ids = array(
		(int) $p_project_id,
	);
	$t_lookup_ids = array(
		(int) $p_project_id,
	);

	while( count( $t_lookup_ids ) > 0 ) {
		$t_project_id = array_shift( $t_lookup_ids );

		if( !isset( $g_cache_project_inheritance[$t_project_id] ) ) {
			continue;
		}

		foreach( $g_cache_project_inheritance[$t_project_id] as $t_parent_id ) {
			if( !in_array( $t_parent_id, $t_project_ids ) ) {
				$t_project_ids[] = $t_parent_id;

				if( !in_array( $t_lookup_ids, $t_project_ids ) ) {
					$t_lookup_ids[] = $t_parent_id;
				}
			}
		}
	}

	return $t_project_ids;
}

function project_hierarchy_get_subprojects( $p_project_id, $p_show_disabled = false ) {
	global $g_cache_project_hierarchy;

	if(( null === $g_cache_project_hierarchy ) || ( $p_show_disabled ) ) {
		project_hierarchy_cache( $p_show_disabled );
	}

	if( isset( $g_cache_project_hierarchy[$p_project_id] ) ) {
		return $g_cache_project_hierarchy[$p_project_id];
	} else {
		return array();
	}
}

function project_hierarchy_get_all_subprojects( $p_project_id ) {
	$t_todo = project_hierarchy_get_subprojects( $p_project_id );
	$t_subprojects = Array();

	while( $t_todo ) {
		$t_elem = array_shift( $t_todo );
		if( !in_array( $t_elem, $t_subprojects ) ) {
			array_push( $t_subprojects, $t_elem );
			$t_todo = array_merge( $t_todo, project_hierarchy_get_subprojects( $t_elem ) );
		}
	}

	return $t_subprojects;
}
