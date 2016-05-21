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
 * Billing API
 *
 * @package CoreAPI
 * @subpackage BillingAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 */

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );

/**
 * Ensure that the specified user has billing reporting access to the specified project.
 *
 * @param integer $p_project_id The project id or null for current project.
 * @param integer $p_user_id The user id or null for logged in user.
 */
function billing_ensure_reporting_access( $p_project_id = null, $p_user_id = null ) {
	if( config_get( 'time_tracking_enabled' ) == OFF ) {
		trigger_error( ERROR_ACCESS_DENIED, ERROR );
	}

	access_ensure_project_level( config_get( 'time_tracking_reporting_threshold' ), $p_project_id, $p_user_id );
}

/**
 * Gets the billing information for the specified project during the specified date range.
 * 
 * @param integer $p_project_id    A project identifier or ALL_PROJECTS.
 * @param string  $p_from          Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string  $p_to            Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param integer $p_cost_per_hour Cost per hour.
 * @return array array of bugnotes
 * @access public
 */
function billing_get_for_project( $p_project_id, $p_from, $p_to, $p_cost_per_hour ) {
	$t_params = array();
	$c_to = strtotime( $p_to ) + SECONDS_PER_DAY - 1;
	$c_from = strtotime( $p_from );

	if( $c_to === false || $c_from === false ) {
		error_parameters( array( $p_from, $p_to ) );
		trigger_error( ERROR_GENERIC, ERROR );
	}

	db_param_push();

	if( ALL_PROJECTS != $p_project_id ) {
		access_ensure_project_level( config_get( 'view_bug_threshold' ), $p_project_id );

		$t_project_where = ' AND b.project_id = ' . db_param() . ' AND bn.bug_id = b.id ';
		$t_params[] = $p_project_id;
	} else {
		$t_project_ids = user_get_all_accessible_projects();
		$t_project_where = ' AND b.project_id in (' . implode( ', ', $t_project_ids ). ')';
	}

	if( !is_blank( $c_from ) ) {
		$t_from_where = ' AND bn.date_submitted >= ' . db_param();
		$t_params[] = $c_from;
	} else {
		$t_from_where = '';
	}

	if( !is_blank( $c_to ) ) {
		$t_to_where = ' AND bn.date_submitted <= ' . db_param();
		$t_params[] = $c_to;
	} else {
		$t_to_where = '';
	}

	$t_results = array();

	$t_query = 'SELECT bn.id id, bn.time_tracking minutes, bn.date_submitted as date_submitted, bnt.note note,
			u.realname realname, b.project_id project_id, c.name bug_category, b.summary bug_summary, bn.bug_id bug_id, bn.reporter_id reporter_id
			FROM {user} u, {bugnote} bn, {bug} b, {bugnote_text} bnt, {category} c
			WHERE u.id = bn.reporter_id AND bn.time_tracking != 0 AND bn.bug_id = b.id AND bnt.id = bn.bugnote_text_id AND c.id=b.category_id
			' . $t_project_where . $t_from_where . $t_to_where . '
			ORDER BY bn.id';
	$t_result = db_query( $t_query, $t_params );

	$t_cost_per_min = $p_cost_per_hour / 60.0;

	$t_access_level_required = config_get( 'time_tracking_view_threshold');

	while( $t_row = db_fetch_array( $t_result ) ) {
		if ( !access_has_bugnote_level( $t_access_level_required, $t_row['id'] ) ) {
			continue;
		}

		$t_total_cost = $t_cost_per_min * $t_row['minutes'];
		$t_row['cost'] = $t_total_cost;
		$t_results[] = $t_row;
	}

	$t_billing_rows = billing_rows_to_array( $t_results );
	return $t_billing_rows;
}

/**
 * Gets the billing summary for the specified project and the date range.
 *
 * @param integer $p_project_id    A project identifier or ALL_PROJECTS.
 * @param string  $p_from          Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string  $p_to            Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param integer $p_cost_per_hour Cost per hour.
 * @return array The contains billing data grouped by issues, users, and total information.
 * @access public
 */
function billing_get_summaries( $p_project_id, $p_from, $p_to, $p_cost_per_hour ) {
	$t_billing_notes = billing_get_for_project( $p_project_id, $p_from, $p_to, $p_cost_per_hour );

	$t_issues = array();
	$t_users = array();

	foreach ( $t_billing_notes as $t_note ) {
		extract( $t_note, EXTR_PREFIX_ALL, 'v' );

		$t_username = user_get_name( $v_reporter_id );

		# Create users in collection of users if not already exists
		if( !isset( $t_users[$t_username] ) ) {
			$t_users[$t_username] = array();
			$t_users[$t_username]['minutes'] = 0;
			$t_users[$t_username]['cost'] = 0;
		}

		# Update user total minutes
		$t_users[$t_username]['minutes'] += $v_minutes;

		# Create issue if it doesn't exist yet.
		if( !isset( $t_issues[$v_bug_id] ) ) {
			$t_issues[$v_bug_id]['issue_id'] = $v_bug_id;
			$t_issues[$v_bug_id]['project_id'] = $v_project_id;
			$t_issues[$v_bug_id]['project_name'] = $v_project_name;
			$t_issues[$v_bug_id]['summary'] = $v_bug_summary;
			$t_issues[$v_bug_id]['users'] = array();
			$t_issues[$v_bug_id]['minutes'] = 0;
			$t_issues[$v_bug_id]['cost'] = 0;
		}

		# Create user within issue if they don't exist yet
		if( !isset( $t_issues[$v_bug_id]['users'][$t_username] ) ) {
			$t_issues[$v_bug_id]['users'][$t_username] = array();
			$t_issues[$v_bug_id]['users'][$t_username]['minutes'] = 0;
			$t_issues[$v_bug_id]['users'][$t_username]['cost'] = 0;
		}

		# Update total minutes for user within the issue
		$t_issues[$v_bug_id]['users'][$t_username]['minutes'] += $v_minutes;

		# Update total minutes for issue
		$t_issues[$v_bug_id]['minutes'] += $v_minutes;
	}

	$t_total = array(
		'minutes' => 0,
		'cost' => 0,
	);

	# Calculate costs and update total cost/minutes across all issues
	foreach( $t_issues as $t_issue_id => $t_issue_info ) {
		# Calculate cost for issue
		$t_cost = $t_issue_info['minutes'] * $p_cost_per_hour / 60;
		$t_issues[$t_issue_id]['cost'] = $t_cost;
		$t_issues[$t_issue_id]['duration'] = db_minutes_to_hhmm( $t_issue_info['minutes'] );

		# Add issue cost and minutes to totals
		$t_total['cost'] += $t_cost;
		$t_total['minutes'] += $t_issue_info['minutes'];

		# Calculate cost per user per issue
		foreach( $t_issue_info['users'] as $t_username => $t_user_info ) {
			$t_issues[$t_issue_id]['users'][$t_username]['cost'] =
				$t_user_info['minutes'] * $p_cost_per_hour / 60;
		}

		ksort( $t_issues[$t_issue_id]['users'] );
	}

	# Calculate total cost per user across all issues
	foreach( $t_users as $t_username => $t_info ) {
		$t_users[$t_username]['cost'] = $t_info['minutes'] * $p_cost_per_hour / 60;
	}

	ksort( $t_users );
	ksort( $t_issues );

	return array(
		'issues' => $t_issues,
		'users' => $t_users,
		'total' => $t_total );
}

/**
 * Converts an array of bugnotes to array of billing rows
 *
 * @param array $p_bugnotes  Array of bugnotes
 * @return array             Array of billing rows
 * @access private
 */
function billing_rows_to_array( $p_bugnotes ) {
	$t_billing_rows = array();

	foreach( $p_bugnotes as $t_note ) {
		$t_row = array();
		$t_row['id'] = $t_note['id'];
		$t_row['minutes'] = $t_note['minutes'];
		$t_row['duration'] = db_minutes_to_hhmm( $t_note['minutes'] );
		$t_row['note'] = $t_note['note'];
		$t_row['reporter_id'] = $t_note['reporter_id'];
		$t_row['reporter_username'] = user_get_name( $t_note['reporter_id'] );
		$t_row['reporter_realname'] = user_get_realname( $t_note['reporter_id'] );
		$t_row['date_submitted'] = $t_note['date_submitted'];

		if ( is_blank( $t_row['reporter_realname'] ) ) {
			$t_row['reporter_realname'] = $t_row['reporter_username'];
		}

		$t_row['bug_id'] = $t_note['bug_id'];
		$t_row['project_id'] = $t_note['project_id'];
		$t_row['project_name'] = project_get_name( $t_note['project_id'] );
		$t_row['bug_summary'] = $t_note['bug_summary'];
		$t_row['bug_category'] = $t_note['bug_category'];
		$t_row['cost'] = $t_note['cost'];

		$t_billing_rows[] = $t_row;
	}

	return $t_billing_rows;
}
