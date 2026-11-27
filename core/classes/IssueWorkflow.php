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
 * A single class that handles the workflow for an issue.
 *
 * Goals:
 * - Have one place to handles business logic relating to a workflow
 *   rather than it being spread around the code in bug_* and access_*
 *   functions.
 * - Make it easy to write test cases for such logic.
 * - Make it easier to allow customizing workflows in the future via
 *   configs or plugins.
 *
 * Few issues to note:
 * - Even though the class starts off with an issue, it doesn't use
 *   such issue id beyond the constructor to extract information deemed
 *   relevant to the workflow. This is to avoid using bug level access
 *   check APIs which has complexities in it that really belong to workflow
 *   and not access checks. It is expected that some of these APIs will be
 *   revised to use the workflow when applicable.
 * - The workflow keeps a minimum access level required for transitions
 *   which is bumped up for private issues.
 * - The access level for an operation is the highest of the relevant config
 *   and the minimum access level.
 * - Features that impact visibility of an issue and are not transitions
 *   are expected to be handled earlier in the flow and hence are not
 *   factored here. For example, limited view of an issue to reporters or
 *   handlers.
 *
 * TODO:
 * - Add ability to get valid next statuses from current status rather
 *   requiring the caller to iterate on each status and check if it is valid
 *   for current user.
 * - Considering adding the resolutions aspect of the workflow
 *   - What is the new resolution on actions like reopen and close.
 *   - What is a valid status + resolution combination for validations in
 *     API/UI
 *   - What is the default resolution for a new status?
 * - Add unit tests.
 * - Use from the rest of the code.
 */
class IssueWorkflow {
	/**
	 * The reporter of the issue
	 *
	 * @var int
	 */
	private $reporter_id;

	/**
	 * The project id of the issue
	 *
	 * @var int
	 */

	private $project_id = ALL_PROJECTS;

	/**
	 * The current status of the issue.
	 *
	 * @var int
	 */
	private $status;

	/**
	 * The logged in user id.
	 */
	private $user_id = 0;

	/**
	 * The workflow for the project.
	 *
	 * @var array
	 */
	private $status_enum_workflow = array();

	/**
	 * Minimum access level to do any transitions. This may be increased if the issue
	 * is private for example.
	 *
	 * @var int
	 */
	private $min_access_level;

	/**
	 * The user's access level on the project.
	 *
	 * @var int
	 */
	private $user_access_level;

	/**
	 * reporter can close. Allow reporters to close the bugs they reported, after
	 * they are marked resolved.
	 *
	 * @var integer
	 */
	private $allow_reporter_close = OFF;

	/**
	 * reporter can reopen. Allow reporters to reopen the bugs they reported, after
	 * they are marked resolved.
	 *
	 * @var integer
	 */
	private $allow_reporter_reopen = ON;

	/**
	 * Constructor
	 */
	public function __construct( $p_issue_id ) {
		$this->user_id = auth_get_current_user_id();

		$t_issue = bug_get( $p_issue_id, false );
		$this->reporter_id = $t_issue->reporter_id;
		$this->status = $t_issue->status;
		$this->project_id = $t_issue->project_id;

		# Set project dependent fields
		$this->status_enum_workflow = $this->project_config( 'status_enum_workflow' );
		$this->user_access_level = access_get_project_level( $this->project_id, $this->user_id );
		if( $t_issue->view_state == VS_PUBLIC ) {
			$this->min_access_level = $this->project_config( 'private_bug_threshold' );
		} else {
			$this->min_access_level = ANYBODY;
		}

		# Set user + project dependent fields
		$this->user_access_level = access_get_project_level( $this->project_id, $this->user_id );
		if( $this->user_id == $this->reporter_id ) {
			$this->allow_reporter_close = $this->project_config( 'allow_reporter_close' );
			$this->allow_reporter_reopen = $this->project_config( 'allow_reporter_reopen' );
		}
	}

	public function is_closed( $p_status = null ) {
		$t_status = $p_status ?? $this->status;
		$t_closed_status_threshold = $this->project_config( 'bug_closed_status_threshold' );
		return $t_status >= $t_closed_status_threshold;
	}

	public function is_resolved( $p_status = null ) {
		$t_status = $p_status ?? $this->status;
		$t_resolved_status_threshold = $this->project_config( 'bug_resolved_status_threshold' );
		return $t_status >= $t_resolved_status_threshold;
	}

	public function is_open( $p_status = null ) {
		$t_status = $p_status ?? $this->status;
		return !$this->is_resolved( $t_status ) && !$this->is_closed( $t_status );
	}

	public function is_reported_by_user() {
		return $this->user_id == $this->reporter_id;
	}

	public function is_readonly() {
		//
		// Based on bug_is_readonly( $p_bug_id )
		//

		# If the bug is not in a readonly status, then it is not readonly
		$t_readonly_status_threshold = $this->project_config( 'bug_readonly_status_threshold' );
		if( $this->status < $t_readonly_status_threshold ) {
			return false;
		}

		# It is readonly, now check if user can update readonly issues
		$t_update_readonly_threshold = $this->project_config( 'update_readonly_bug_threshold' );
		if( $this->has_access_level( $t_update_readonly_threshold ) ) {
			return false;
		}

		return true;
	}

	/**
	 * This method checks if user can transition status of the issue to the given status.
	 *
	 * @param string $p_status The status to transition to.
	 */
	public function can_transition_to( $p_status ) {
		# If it is not a valid transition, then no.
		if( !$this->is_valid_status_transition( $p_status ) ) {
			return false;
		}

		# if issue is getting re-opened, check can_reopen()
		$t_reopen_status = $this->project_config( 'bug_reopen_status' );
		if( $this->is_resolved() && ( $p_status == $t_reopen_status ) ) {
			return $this->can_reopen();
		}

		# if issue is getting closed, check can_close()
		if( $this->is_resolved() && $this->is_closed( $p_status ) ) {
			return $this->can_close();
		}

		# Check that the user has access level to make such transition
		$t_status_threshold = access_get_status_threshold( $p_status, $this->project_id );
		return $this->has_access_level( $t_status_threshold );
	}

	/**
	 * Check that user can close the issue from its current state.
	 *
	 * This is exposed as its own method vs. relying on can_transition_to()
	 * in order to allow API / UI to know if closing an issue is a valid action
	 * without getting into what that exactly means in terms of from/to statuses.
	 *
	 * @return bool True if user can close the issue, otherwise false.
	 */
	public function can_close() {
		//
		// Based on access_can_close_bug( $t_existing_bug, $t_current_user_id );
		//

		# Can't close a bug that's already closed
		if( $this->is_closed() ) {
			return false;
		}

		# If allow_reporter_close is enabled, then reporters can close their own bugs
		# if they are in resolved status
		if( $this->allow_reporter_close
			&& $this->is_reported_by_user()
			&& $this->is_resolved()
		) {
			return true;
		}

		# Get the status for closed
		$t_closed_status = $this->project_config( 'bug_closed_status_threshold' );

		# Check that user has access level to transition to such status.
		$t_closed_status_threshold = access_get_status_threshold( $t_closed_status, $this->project_id );
		return $this->has_access_level( $t_closed_status_threshold );
	}

	/**
	 * Check that user can re-open the issue from its current state.
	 *
	 * This is exposed as its own method vs. relying on can_transition_to()
	 * in order to allow API / UI to know if re-opening an issue is a valid action
	 * without getting into what that exactly means in terms of from/to statuses.
	 *
	 * @return bool True if user can re-open the issue, otherwise false.
	 */
	public function can_reopen() {
		//
		// Based on access_can_reopen_bug( $t_existing_bug, $t_current_user_id )
		//

		# Can't reopen a bug that's not resolved
		if( !$this->is_resolved() ) {
			return false;
		}

		# Reopen status must be reachable by workflow
		$t_reopen_status = $this->project_config( 'bug_reopen_status' );
		if( !$this->is_valid_status_transition( $t_reopen_status ) ) {
			return false;
		}

		# If allow_reporter_reopen is enabled, then reporters can always reopen
		# their own bugs as long as their access level is reporter or above
		if( $this->allow_reporter_reopen
			&& $this->is_reported_by_user()
			&& $this->has_access_level( $this->project_config( 'report_bug_threshold' ) )
		) {
			return true;
		}

		# Other users's access level must allow them to reopen bugs
		$t_reopen_bug_threshold = $this->project_config( 'reopen_bug_threshold' );
		if( $this->has_access_level( $t_reopen_bug_threshold ) ) {
			# User must be allowed to change status to reopen status
			$t_reopen_status_threshold = access_get_status_threshold( $t_reopen_status, $this->project_id );
			return $this->has_access_level( $t_reopen_status_threshold );
		}

		return false;
	}

	private function is_valid_status_transition( $p_wanted_status ) {
		#
		# Based on bug_check_workflow() in core/bug_api.php
		#

		if( count( $this->status_enum_workflow ) < 1 ) {
			# workflow not defined, use default enum
			return true;
		}

		if( $this->status == $p_wanted_status ) {
			# no change in state, allow the transition
			return true;
		}

		# There should always be a possible next status, if not defined, then allow all.
		if( !isset( $t_status_enum_workflow[$this->status] ) ) {
			return true;
		}

		# workflow defined - find allowed states
		$t_allowed_states = $this->status_enum_workflow[$this->status];

		return MantisEnum::hasValue( $t_allowed_states, $p_wanted_status );
	}

	/**
	 * Get a config option in context of the project.
	 *
	 * @return mixed The config value.
	 */
	private function project_config( $p_config ) {
		return config_get( $p_config, null, NO_USER, $this->project_id );
	}

	/**
	 * Check that user has the minimim access level + the access level
	 * specified
	 *
	 * @return int|array The access level.
	 */
	private function has_access_level( $p_access_level ) {
		# can't just check the max of the two since access levels can configured
		# can be an specific level or an array of levels.
		return
			access_compare_level( $this->user_access_level, $p_access_level ) &&
			access_compare_level( $this->user_access_level, $this->min_access_level );
	}
}
