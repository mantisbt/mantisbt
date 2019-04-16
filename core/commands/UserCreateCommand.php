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
 * A command that creates a user account.
 *
 * Sample:
 * {
 *   "payload": {
 *     "name": "vboctor",
 *     "password": "p@ssw0rd",
 *     "real_name": "Victor Boctor",
 *     "email": "vboctor@example.com",
 *     "access_level": { "name" => "developer" },
 *     "enabled": true,
 *     "protected": false
 *   }
 * }
 */
class UserCreateCommand extends Command {
	/**
	 * @var string user name
	 */
	private $username;

	/**
	 * @var string user real name
	 */
	private $realname;

	/**
	 * @var string user email address
	 */
	private $email;

	/**
	 * @var string user password
	 */
	private $password;

	/**
	 * @var boolean user can edit their information.  Usually, true for shared accounts.
	 */
	private $protected;

	/**
	 * @var boolean user account enabled for login.
	 */
	private $enabled;

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
		# Ensure user has access level to create users
		if( !access_has_global_level( config_get_global( 'manage_user_threshold' ) ) ) {
			throw new ClientException( 'Access denied to create users', ERROR_ACCESS_DENIED );
		}

		# Access Level
		$this->access_level = access_parse_array(
			$this->payload(
				'access_level',
				array( 'id' => config_get_global( 'default_new_account_access_level' ) ) ) );

		# Don't allow the creation of accounts with access levels higher than that of
		# the user creating the account.
		if( !access_has_global_level( $this->access_level ) ) {
			throw new ClientException(
				'Access denied to create users with higher access level',
				ERROR_ACCESS_DENIED );
		}

		# Username and Real Name
		$this->username = trim( $this->payload( 'name', $this->payload( 'username', '' ) ) );
		$this->realname = string_normalize( $this->payload( 'real_name', '' ) );

		# Protected and Enabled Flags
		$this->protected = $this->payload( 'protected', false );
		$this->enabled = $this->payload( 'enabled', true );

		# Email
		$this->email = trim( $this->payload( 'email', '' ) );

		# Password
		$this->password = $this->payload( 'password', '' );
		if( ( ON == config_get( 'send_reset_password' ) ) &&
		    ( ON == config_get( 'enable_email_notification' ) ) ) {
			# Check code will be sent to the user directly via email. Dummy password set to random
			# Create random password
			$this->password = auth_generate_random_password();
		} else {
			$this->password = $this->payload( 'password', auth_generate_random_password() );
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		# Need to send the user creation mail in the tracker language, not in the creating admin's language
		# Park the current language name until the user has been created
		lang_push( config_get_global( 'default_language' ) );

		# create the user
		$t_admin_name = user_get_name( auth_get_current_user_id() );
		user_create(
			$this->username,
			$this->password,
			$this->email,
			$this->access_level,
			$this->protected,
			$this->enabled,
			$this->realname,
			$t_admin_name );

		# set language back to user language
		lang_pop();

		return array( 'id' => user_get_id_by_name( $this->username ) );
	}
}

