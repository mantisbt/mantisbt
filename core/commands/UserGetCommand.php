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
	 * Fields to show if no `select` query parameter is specified.
	 * 
	 * @var array
	 */
	private $select;

	# TODO: add support for enabled flag
	# TODO: add supoprt for protected flag

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
		$t_user_id = $this->query( 'user_id', null );
		if( !is_null( $t_user_id ) && $t_user_id <= 0 ) {
			throw new ClientException(
				"Invalid user id '$t_user_id'",
				ERROR_INVALID_FIELD_VALUE,
				array( 'id' ) );
		}

		$this->target_user_id = null;

		$t_username = $this->query( 'username' );
		if( !is_null( $t_username ) && !is_blank( $t_username ) ) {
			$t_user_id = user_get_id_by_name( $t_username );
			if( $t_user_id === false ) {
				throw new ClientException(
					'User not found',
					ERROR_USER_BY_NAME_NOT_FOUND,
					array( $t_username ) );
			}

			$this->target_user_id = $t_user_id;
		}
		
		if ( is_null( $this->target_user_id ) ) {
			$t_user_id = $this->query( 'user_id' );
			if( !is_null( $t_user_id ) && !is_blank( $t_user_id ) ) {
				if( !user_exists( $t_user_id ) ) {
					throw new ClientException(
						'User not found',
						ERROR_USER_BY_ID_NOT_FOUND,
						array( $t_user_id ) );	
				}

				$this->target_user_id = (int)$t_user_id;
			} else {
				throw new ClientException(
					'User not specified',
					ERROR_INVALID_FIELD_VALUE,
					array( 'user_id' ) );
			}
		}

		$t_same_user = $t_current_user_id == $this->target_user_id;

		# Ensure user has access level to retrieve user information
		if( !$t_same_user && !access_has_global_level( config_get_global( 'manage_user_threshold' ) ) ) {
			throw new ClientException( 'Access denied to get other users', ERROR_ACCESS_DENIED );
		}

		if( !user_exists( $this->target_user_id ) ) {
			throw new ClientException(
				'User not found',
				ERROR_USER_BY_ID_NOT_FOUND,
				array( $this->target_user_id ) );
		}

		$t_select = $this->query( 'select', null );
		if( !is_null( $t_select ) ) {
			$t_select = array_map( 'trim', $t_select );
			$t_select = array_map( 'strtolower', $t_select );
			$t_select = array_unique( $t_select );

			if( empty( $t_select ) ) {
				$this->select = null;
			} else {
				$this->select = $t_select;
			}
		}

		# if select field is not specified or empty, then use defaults
		if( is_null( $this->select ) ) {
			$this->select = UserGetCommand::getDefaultFields();
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		$t_result = mci_user_get( $this->target_user_id, $this->select );

		if( $this->option( 'return_as_users', true ) ) {
			$t_result = array( 'users' => array( $t_result ) );
		}

		return $t_result;
	}

	/**
	 * Get the default fields to select for the response.
	 * 
	 * NOTE: removing fields from this list will break backward compatibility.
	 * 
	 * @return array List of fields to select.
	 */
	public static function getDefaultFields() {
		return array(
			'id',
			'name',
			'real_name',
			'email',
			'access_level',
			'language',
			'timezone',
			'created_at'
		);
	}
}

