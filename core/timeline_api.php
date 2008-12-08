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
 * Timeline API
 * Handles management of roadmap and changelog data.
 *
 * @package CoreAPI
 * @subpackage TimelineAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Get a list of bug ids for a specific project and version.
 * @param int Project ID
 * @param string Version name
 * @param int Timeline type
 * @return array Bug IDs
 */
function timeline_get_bug_ids( $p_project_id, $p_version, $p_type = null ) {
	if( is_null( $p_type ) ) {
		$p_type = TIMELINE_TARGETTED| TIMELINE_FIXED;
	}

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$t_query = "SELECT DISTINCT( id ) FROM $t_bug_table
		WHERE project_id=" . db_param();
	$t_query_where = array();
	$t_query_params = array(
		$p_project_id,
	);

	if( $p_type&TIMELINE_TARGETTED ) {
		$t_query_where[] = 'target_version=' . db_param();
		$t_query_params[] = $p_version;
	}
	if( $p_type&TIMELINE_FIXED ) {
		$t_query_where[] = 'fixed_in_version=' . db_param();
		$t_query_params[] = $p_version;
	}

	$t_query .= ' AND ( ' . join( ' OR ', $t_query_where ) . ' ) ORDER BY id ASC';
	$t_result = db_query_bound( $t_query, $t_query_params );

	$t_bug_ids = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_bug_ids[] = $t_row['id'];
	}

	return $t_bug_ids;
}

/**
 * Given a list of bug ids, return a hierarchical array of bug ids
 * translating relationships between bugs into a tree structure.
 * @param array Bug IDs
 * @return array Hierarchy of bug IDs
 */
function timeline_get_hierarchy( $p_bug_ids ) {
	if( !is_array( $p_bug_ids ) || count( $p_bug_ids ) < 1 ) {
		return array();
	}

	$t_bugs = array();
	foreach( $p_bug_ids as $t_bug_id ) {
		$t_bugs[$t_bug_id] = array();
	}

	$t_relationship_table = db_get_table( 'mantis_bug_relationship_table' );
	$t_bug_ids_joined = join( ',', $p_bug_ids );

	$t_params = array();
	$t_query = "SELECT * FROM $t_relationship_table
		WHERE source_bug_id IN ( $t_bug_ids_joined )
			AND destination_bug_id IN ( $t_bug_ids_joined )";

	$t_result = db_query_bound( $t_query, $t_params );

	$t_rows = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_source_id = $t_row['source_bug_id'];
		$t_dest_id = $t_row['destination_bug_id'];

		switch( $t_row['relationship_type'] ) {
			case BUG_DEPENDANT:
				$t_bugs[$t_source_id][$t_dest_id] = array();
				break;

			case BUG_DUPLICATE:
				$t_bugs[$t_dest_id][$t_source_id] = array();
				break;

			case BUG_RELATED:
			default:
		}
	}

	return timeline_generate_hierarchy( $t_bugs );
}

/**
 * Recursively generate a full tree structure hierarchy from a
 * single depth tree of parent/child elements.
 * @param array Single-depth base tree
 * @param array Local tree branch
 * @param array Bug IDs already seen in this tree branch
 * @return array Updated local branch or full tree
 */
function timeline_generate_hierarchy( $p_all_bugs, $p_local_bugs = null, $p_seen_ids = null ) {
	if( is_null( $p_seen_ids ) ) {
		$p_seen_ids = array();
	}

	# Outer pass
	# Only care about calling recursively on the children
	if( is_null( $p_local_bugs ) ) {
		$p_new_bugs = array();

		foreach( $p_all_bugs as $t_bug_id => $t_children ) {
			if( count( $t_children ) > 0 ) {
				$p_new_bugs[$t_bug_id] = timeline_generate_hierarchy( $p_all_bugs, $t_children, array( $t_bug_id ) );
			} else {
				$p_new_bugs[$t_bug_id] = array();
			}
		}

		return $p_new_bugs;

		# Inner passes
	} else {
		foreach( $p_local_bugs as $t_bug_id => $t_children ) {
			if( !in_array( $t_bug_id, $p_seen_ids ) ) {
				$t_seen_ids = $p_seen_ids;
				$t_seen_ids[] = $t_bug_id;

				$p_local_bugs[$t_bug_id] = timeline_generate_hierarchy( $p_all_bugs, $p_all_bugs[$t_bug_id], $t_seen_ids );
			} else {
				return array();
			}
		}

		return $p_local_bugs;
	}
}
