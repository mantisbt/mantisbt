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
require_api( 'api_token_api.php' );

use Mantis\Exceptions\ClientException;

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_account_api.php' );

/**
 * A command that creates a user API token.
 */
class UserTokenCreateCommand extends Command {
	/**
	 * @var integer The user id.
	 */
	private $user_id;

	/**
	 * @var string The token name.
	 */
	private $name;

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
		// acting user id
		$t_current_user_id = auth_get_current_user_id();

		// user is to create token for
		$this->user_id = $this->query( 'user_id' );

		// any user can create a token for themselves, but further checks are needed to
		// create tokens for other users.
		if( $t_current_user_id != $this->user_id ) {
			// User has access level to impersonate other users
			if( !access_has_global_level( config_get_global( 'impersonate_user_threshold' ) ) ) {
				throw new ClientException(
					"User doesn't have access level to impersonate other users.",
					ERROR_ACCESS_DENIED
				);
			}

			// Make sure $this->user_id
			if( !user_exists( $this->user_id ) ) {
				throw new ClientException(
					"User doesn't exist",
					ERROR_USER_BY_ID_NOT_FOUND,
					array( $this->user_id )
				);
			}

			// User is not creating an access token for a user with higher access level
			$t_current_user_access_level = access_get_global_level( $t_current_user_id );
			$t_target_user_access_level = access_get_global_level( $this->user_id );
			if( $t_current_user_access_level < $t_target_user_access_level ) {
				throw new ClientException(
					"User can't create tokens for other users with higher access level.",
					ERROR_ACCESS_DENIED
				);
			}
		}

		// Check if it possible to create tokens for target user - e.g. user is not protected.
		if( !api_token_can_create( $this->user_id )) {
			throw new ClientException(
				'Create API tokens not allowed for target user',
				ERROR_ACCESS_DENIED
			);
		}

		// get or generate token name
		$this->name = $this->payload( 'name', '' );
		if( is_blank( $this->name ) ) {
			$t_date_format = config_get( 'complete_date_format' );
			$t_current_user_name = user_get_field( $t_current_user_id, 'username' );
			$t_count = 1;
			do {
				$this->name = sprintf(
					"%s created on %s",
					$t_current_user_name,
					date( $t_date_format ) );
				
				if( $t_count > 1 ) {
					$this->name .= ' ' . $t_count;
				}

				$t_count++;
			} while ( !api_token_name_is_unique( $this->name, $this->user_id ) );
		}

		// make sure token name is unique
		if( !api_token_name_is_unique( $this->name, $this->user_id ) ) {
			throw new ClientException(
				'Token name is not unique',
				ERROR_INVALID_FIELD_VALUE,
				array( $this->name )
			);
		}
	}

	// process the command
	function process() {
		$t_token_result = api_token_create( $this->name, $this->user_id, /* return_id */ true );

		$t_result = array(
			'id' => $t_token_result['id'],
			'name' => $this->name,
			'token' => $t_token_result['token'],
			'user' => mci_account_get_array_by_id( $this->user_id )
		);

		return $t_result;
	}
}
