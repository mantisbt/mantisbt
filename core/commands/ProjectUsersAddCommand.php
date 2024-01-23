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

use Mantis\Exceptions\ClientException;

require_api( 'authentication_api.php' );
require_api( 'user_pref_api.php' );

require_once( dirname( __FILE__, 3 ) . '/api/soap/mc_account_api.php' );
require_once( dirname( __FILE__, 3 ) . '/api/soap/mc_api.php' );
require_once( dirname( __FILE__, 3 ) . '/api/soap/mc_enum_api.php' );

/**
 * Sample:
 * {
 *   "payload": {
 *     "project": { "name": "My Project" },    // can also be { "id : 1 }
 *     "user": { "name": "administrator" },    // can also be { "id": 1 }
 *     "access_level": { "name": "developer" } // can also be { "id": 25 }
 *   }
 * }
 */

/**
 * A command to add a user to a project or update their access to a project.
 */
class ProjectUsersAddCommand extends Command {
	/**
	 * @var integer The project id
	 */
	private $project_id;

	/**
	 * @var integer The user id
	 */
	private $user_id;

	/**
	 * The minimum access level, users with access level greater or equal to this access level
	 * will be returned.
	 *
	 * @var integer
	 */
	private $access_level;

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
	 *
	 * @return void
	 * @throws ClientException
	 */
	function validate() {
		$t_project = $this->payload( 'project' );
		if( is_null( $t_project ) ) {
			throw new ClientException( 'Project not specified', ERROR_EMPTY_FIELD, array( 'project' ) );
		}

		$this->project_id = mci_get_project_id( $t_project );
		if( $this->project_id < 1 ) {
			throw new ClientException( 'Invalid Project', ERROR_INVALID_FIELD_VALUE, array( 'project' ) );
		}

		$t_user = $this->payload( 'user' );
		if( is_null( $t_user ) ) {
			throw new ClientException( 'User not specified', ERROR_EMPTY_FIELD, array( 'user' ) );
		}

		$this->user_id = mci_get_user_id( $t_user );
		if( $this->user_id < 1 ) {
			throw new ClientException( 'Invalid User', ERROR_INVALID_FIELD_VALUE, array( 'user' ) );
		}

		$t_access_level = $this->payload( 'access_level' );
		if( is_null( $t_access_level ) ) {
			throw new ClientException( 'Access level not specified', ERROR_EMPTY_FIELD, array( 'access_level' ) );
		}

		$this->access_level = access_parse_array( $t_access_level );

		user_ensure_exists( $this->user_id );
		project_ensure_exists( $this->project_id );

		$t_actor_id = auth_get_current_user_id();

		# We should check both since we are in the project section and an
		# admin might raise the first threshold and not realize they need
		# to raise the second
		$t_access_check = access_has_project_level(
			config_get( 'manage_project_threshold', /* default */ null, $t_actor_id, $this->project_id ),
			$this->project_id );

		$t_access_check = $t_access_check &&
			access_has_project_level(
				config_get( 'project_user_threshold', /* default */ null, $t_actor_id, $this->project_id ),
				$this->project_id );

		if( !$t_access_check ) {
			throw new ClientException( "Access Denied", ERROR_ACCESS_DENIED );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return void
	 */
	protected function process() {
		# This is an upsert, it will work for adding a user or modifying their access level.
		project_add_user( $this->project_id, $this->user_id, $this->access_level );
	}
}
