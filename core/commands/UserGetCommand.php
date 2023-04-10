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
require_api( 'config_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that gets user information. If id is not specified, it will get the current user.
 * To get other users' information, the user must have manage_user_threshold access level.
 *
 * Sample:
 * {
 *   "query": {
 *      "id": 1234
 *   },
 *   "options": {
 * 	    "include_in_user_element": true
 *   }
 * }
 */
class UserGetCommand extends Command {
	/**
	 * @var integer The id of the user to get.
	 */
	private $target_user_id;

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
		$t_current_user_id = auth_get_current_user_id();
		$this->target_user_id = (int)$this->query( 'id', $t_current_user_id );
		if( $this->target_user_id <= 0 ) {
			throw new ClientException( 'Invalid user id', ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
		}

		$t_same_user = $t_current_user_id == $this->target_user_id;

		# Ensure user has access level to retrieve user information
		if( !$t_same_user && !access_has_global_level( config_get_global( 'manage_user_threshold' ) ) ) {
			throw new ClientException( 'Access denied to get other users', ERROR_ACCESS_DENIED );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		$t_result = mci_user_get( $this->target_user_id );

		if( $this->option( 'include_in_user_element', true ) ) {
			$t_result = array( 'user' => $t_result );
		}

		return $t_result;
	}
}

