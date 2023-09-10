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
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that remove user's access to a project. If user id 0 is specified, then all users will be
 * removed from project.
 *
 * Sample:
 * {
 *   "payload": {
 *     "project": { "name": "My Project" },    // can also be { "id : 1 }
 *     "user": { "name": "administrator" },    // can also be { "id": 1 }
 *   }
 * }
 */
class ProjectUsersDeleteCommand extends Command {
	/**
	 * @var integer The project id
	 */
	private $project_id;

	/**
	 * @var integer The user id
	 */
	private $user_id;

	/**
	 * @var integer The logged in user id.
	 */
	private $actor_id;

	/**
	 * Constructor
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
		$t_project = $this->payload( 'project' );
		if( is_null( $t_project ) ) {
			throw new ClientException( 'User not specified', ERROR_EMPTY_FIELD, array( 'project' ) );
		}

		$this->project_id = mci_get_project_id( $t_project );
		if( $this->project_id < 1 ) {
			throw new ClientException( 'Invalid Project', ERROR_INVALID_FIELD_VALUE, array( 'project' ) );
		}

		$t_user = $this->payload( 'user' );
		if( is_null( $t_user ) ) {
			throw new ClientException( 'User not specified', ERROR_EMPTY_FIELD, array( 'user' ) );
		}

		$this->user_id = mci_get_user_id( $t_user, /* default */ null, /* allow all users */ true );
		if( is_null( $this->user_id ) ) {
			throw new ClientException( 'Invalid User', ERROR_INVALID_FIELD_VALUE, array( 'user' ) );
		}

		# ALL_USERS is a valid case for deleting all users on a project
		if( $this->user_id != ALL_USERS ) {
			user_ensure_exists( $this->user_id );
		}

		project_ensure_exists( $this->project_id );

		$this->actor_id = auth_get_current_user_id();

		# We should check both since we are in the project section and an
		# admin might raise the first threshold and not realize they need
		# to raise the second
		$t_access_check = access_has_project_level(
			config_get( 'manage_project_threshold', /* default */ null, $this->actor_id, $this->project_id ),
			$this->project_id );

		$t_access_check = $t_access_check &&
			access_has_project_level(
				config_get( 'project_user_threshold', /* default */ null, $this->actor_id, $this->project_id ),
				$this->project_id );

		# Don't allow removal of users from the project who have a higher access level than the current user
		$t_access_check = $t_access_check &&
			access_has_project_level(
				access_get_project_level( $this->project_id, $this->actor_id ), $this->project_id );

		if( !$t_access_check ) {
			throw new ClientException( "Access Denied", ERROR_ACCESS_DENIED );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		if( $this->user_id === ALL_USERS ) {
			project_remove_all_users(
				$this->project_id,
				access_get_project_level( $this->project_id, $this->actor_id ) );
		} else {
			project_remove_user( $this->project_id, $this->user_id );
		}

		return [];
	}
}

