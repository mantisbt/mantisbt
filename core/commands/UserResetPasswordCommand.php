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
 * A command that resets the password of the specified user account.
 *
 * Sample:
 * {
 *   "query": {
 *	  "id": 1234
 *   }
 * }
 */
class UserResetPasswordCommand extends Command {
	/**
	 * Constants for execute() method's return value.
	 */
	const RESULT_RESET = 'reset';
	const RESULT_UNLOCK = 'unlock';

	/**
	 * @var integer The id of the user to delete.
	 */
	private $user_id_reset;

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
	 * @throws ClientException
	 */
	function validate() {
		# Ensure user has the required access level to reset passwords
		if( !access_has_global_level( config_get_global( 'manage_user_threshold' ) ) ) {
			throw new ClientException( 'Access denied to reset user password', ERROR_ACCESS_DENIED );
		}

		$this->user_id_reset = (int)$this->query( 'id', null );

		# Make sure the account exists
		$t_user = user_get_row( $this->user_id_reset );
		if( $t_user === false ) {
			throw new ClientException( 'Invalid user id', ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
		}

		# Mantis can't reset protected accounts' passwords, but if the
		# account is locked, we allow the operation as Unlock
		if( auth_can_set_password( $this->user_id_reset )
			&& user_is_protected( $this->user_id_reset )
			&& user_is_login_request_allowed( $this->user_id_reset )
		) {
			throw new ClientException(
				'Password reset not allowed for protected accounts',
				ERROR_PROTECTED_ACCOUNT
			);
		}

		# Ensure that the account to be reset is of equal or lower access than
		# the current user.
		if( !access_has_global_level( $t_user['access_level'] ) ) {
			throw new ClientException( 'Access denied to reset user password with higher access level', ERROR_ACCESS_DENIED );
		}

		# Check that we are not resetting the last administrator account
		$t_admin_threshold = config_get_global( 'admin_site_threshold' );
		if( user_is_administrator( $this->user_id_reset ) &&
			user_count_level( $t_admin_threshold, /* enabled */ true ) <= 1 ) {
			throw new ClientException(
				'Resetting last administrator not allowed',
				ERROR_USER_CHANGE_LAST_ADMIN );
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 * @throws ClientException
	 */
	protected function process() {
		# If the password can be changed, reset it
		if( auth_can_set_password( $this->user_id_reset )
			&& user_reset_password( $this->user_id_reset )
		) {
			return array( 'action' => self::RESULT_RESET );
		}

		# Password can't be changed, unlock the account
		# the account (i.e. reset failed login count)
		user_reset_failed_login_count_to_zero( $this->user_id_reset );
		return array( 'action' =>  self::RESULT_UNLOCK );
	}
}
