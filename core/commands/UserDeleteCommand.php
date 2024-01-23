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
 * A command that deletes a user account.
 *
 * Sample:
 * {
 *   "query": {
 *      "id": 1234
 *   }
 * }
 */
class UserDeleteCommand extends Command {
	/**
	 * @var integer The id of the user to delete.
	 */
	private $user_id_to_delete;

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
		$this->user_id_to_delete = (int)$this->query( 'id', null );
		if( $this->user_id_to_delete <= 0 ) {
			throw new ClientException( 'Invalid user id', ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
		}

		user_ensure_exists( $this->user_id_to_delete );

		if( $this->user_id_to_delete == auth_get_current_user_id() ) {
			throw new ClientException( 'Deleting own account not allowed', ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
		}

		# Ensure user has access level to delete users
		if( !access_has_global_level( config_get_global( 'manage_user_threshold' ) ) ) {
			throw new ClientException( 'Access denied to delete users', ERROR_ACCESS_DENIED );
		}

		$t_user = user_get_row( $this->user_id_to_delete );

		# Ensure that the account to be deleted is of equal or lower access to the
		# current user.
		if( !access_has_global_level( $t_user['access_level'] ) ) {
			throw new ClientException( 'Access denied to deleted users with higher access level', ERROR_ACCESS_DENIED );
		}

		# Check that we are not deleting the last administrator account
		$t_admin_threshold = config_get_global( 'admin_site_threshold' );
		if( user_is_administrator( $this->user_id_to_delete ) &&
			user_count_level( $t_admin_threshold, /* enabled */ true ) <= 1 ) {
			throw new ClientException(
				'Deleting last administrator not allowed',
				ERROR_USER_CHANGE_LAST_ADMIN );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		user_delete( $this->user_id_to_delete );
		return array();
	}
}

