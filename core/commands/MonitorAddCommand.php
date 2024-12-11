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

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that adds user to monitor an issue.
 */
class MonitorAddCommand extends Command {
	/**
	 * @var integer
	 */
	private $projectId;

	/**
	 * @var array array of user ids to add to monitor list of the issue
	 */
	private $userIdsToAdd;

	/**
	 * $p_data['payload'] is expected to contain:
	 * - issue_id
	 * - users (array of users) each user as
	 *   - an array having a key value for id or name or real_name or name_or_realname.
	 *     id takes first priority, name second, real_name third, name_or_realname fourth.
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {		
		$t_issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );

		$this->projectId = bug_get_field( $t_issue_id, 'project_id' );
		$t_logged_in_user = auth_get_current_user_id();

		$t_users = $this->payload( 'users', array( array( 'id' => $t_logged_in_user ) ) );
		if( !is_array( $t_users ) ) {
			throw new ClientException( 'Invalid users array', ERROR_INVALID_FIELD_VALUE, array( 'users' ) );
		}

		# Normalize user objects
		$t_user_ids = array();
		foreach( $t_users as $t_user ) {
			$t_user_ids[] = user_get_id_by_user_info( $t_user );
		}

		$this->userIdsToAdd = array();
		foreach( $t_user_ids as $t_user_id ) {
			user_ensure_exists( $t_user_id );

			if( user_is_anonymous( $t_user_id ) ) {
				throw new ClientException( "anonymous account can't monitor issues", ERROR_PROTECTED_ACCOUNT );
			}

			if( $t_logged_in_user != $t_user_id ) {
				$t_access_level = config_get(
					'monitor_add_others_bug_threshold',
					/* default */ null,
					/* user */ null,
					$this->projectId );

				if( !access_has_bug_level( $t_access_level, $t_issue_id, $t_logged_in_user ) ) {
					throw new ClientException( 'access denied', ERROR_ACCESS_DENIED );
				}

			}

			$t_access_level = config_get(
				'monitor_bug_threshold',
				/* default */ null,
				/* user */ null,
				$this->projectId );

			if( !access_has_project_level( $t_access_level, $this->projectId, $t_user_id ) ) {
				throw new ClientException( 'access denied', ERROR_MONITOR_ACCESS_TOO_LOW );
			}

			$this->userIdsToAdd[] = $t_user_id;
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		if( $this->projectId != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			global $g_project_override;
			$g_project_override = $this->projectId;
		}

		foreach( $this->userIdsToAdd as $t_user_id ) {
			bug_monitor( $this->query( 'issue_id' ), $t_user_id );
		}

		return array();
	}
}

