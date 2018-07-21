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
 * Workflow API
 *
 * @package CoreAPI
 * @subpackage WorkflowAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

require_api( 'config_api.php' );

/**
 * Determine if there is a transition possible between two workflow states. The
 * direction of the transition is factored into this check.
 * @param integer $p_from_status_id Source status ID.
 * @param integer $p_to_status_id   Destination status ID.
 * @return boolean Whether a transition exists in the specified direction
 */
function workflow_transition_edge_exists( $p_from_status_id, $p_to_status_id ) {
	if( $p_from_status_id == $p_to_status_id ) {
		return false;
	}

	$t_project_workflow = workflow_parse( config_get( 'status_enum_workflow' ) );

	return isset( $t_project_workflow['exit'][$p_from_status_id][$p_to_status_id] );
}

/**
 * Parse a workflow into a graph-like array of workflow transitions.
 * @param array $p_enum_workflow The workflow enumeration to parse.
 * @return array The parsed workflow graph.
 */
function workflow_parse( array $p_enum_workflow ) {
	$t_status_arr = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );

	# If workflow is not set, defaults to array(), which means that all transitions are valid
	if( !is_array( $p_enum_workflow ) ) {
		$p_enum_workflow = array();
	}

	# If any status row is missing, it defaults to all transitions
	foreach( $t_status_arr as $t_status => $t_label ) {
		if( !isset( $p_enum_workflow[$t_status] ) ) {
			$t_temp_workflow = array();
			foreach ( $t_status_arr as $t_next => $t_next_label ) {
				if( $t_status != $t_next ) {
					$t_temp_workflow[] = $t_next . ':' . $t_next_label;
				}
			}
			$p_enum_workflow[$t_status] = implode( ',', $t_temp_workflow );
		}
	}

	$t_entry = array();
	$t_exit = array();

	# prepopulate new bug state (bugs go from nothing to here)
	$t_submit_status_array = config_get( 'bug_submit_status' );
	$t_new_label = MantisEnum::getLabel( lang_get( 'status_enum_string' ), config_get( 'bug_submit_status' ) );
	if( is_array( $t_submit_status_array ) ) {
		# @@@ (thraxisp) this is not implemented in bug_api.php
		foreach ( $t_submit_status_array as $t_access => $t_status ) {
			$t_entry[$t_status][0] = $t_new_label;
			$t_exit[0][$t_status] = $t_new_label;
		}
	} else {
		$t_status = $t_submit_status_array;
		$t_entry[$t_status][0] = $t_new_label;
		$t_exit[0][$t_status] = $t_new_label;
	}

	# add user defined arcs
	$t_default = array();
	foreach ( $t_status_arr as $t_status => $t_status_label ) {
		$t_exit[$t_status] = array();
		if( isset( $p_enum_workflow[$t_status] ) ) {
			$t_next_arr = MantisEnum::getAssocArrayIndexedByValues( $p_enum_workflow[$t_status] );
			foreach ( $t_next_arr as $t_next => $t_next_label ) {
				if( !isset( $t_default[$t_status] ) ) {
					$t_default[$t_status] = $t_next;
				}
				$t_exit[$t_status][$t_next] = '';
				$t_entry[$t_next][$t_status] = '';
			}
		}
		if( !isset( $t_entry[$t_status] ) ) {
			$t_entry[$t_status] = array();
		}
	}
	return array( 'entry' => $t_entry, 'exit' => $t_exit, 'default' => $t_default );
}
