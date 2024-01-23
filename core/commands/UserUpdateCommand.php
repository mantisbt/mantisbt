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

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_account_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );

/**
 * A command that updates a user account.
 *
 * Note that only fields to be updated need to be provided. If extra fields
 * are provided and it matches current state, they will be ignored.
 *
 * Sample:
 * {
 *   "query": {
 *     "user_id": 1
 *   },
 *   "payload": {
 *     "user": {
 *       "name": "vboctor",
 *       "real_name": "Victor Boctor",
 *       "email": "vboctor@example.com",
 *       "access_level": { "name" => "developer" },
 *       "enabled": true,
 *       "protected": false
 *     },
 *     "notify_user": false
 *   }
 * }
 */
class UserUpdateCommand extends Command {
	/**
	 * @var integer user id
	 */
	private $user_id;

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
	 * @var integer access level
	 */
	private $access_level;

	/**
	 * @var boolean user can edit their information.  Usually, true for shared accounts.
	 */
	private $protected;

	/**
	 * @var boolean user account enabled for login.
	 */
	private $enabled;

	/**
	 * @var boolean notify user of account changes via email.
	 */
	private $notify_user;

	/**
	 * @var array old user information
	 */
	private $old_user;

	/**
	 * @var array new user information
	 */
	private $new_user;

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
		# Get id of target user
		$this->user_id = helper_parse_id( $this->query( 'user_id' ), 'user_id' );

		# Get info about logged in user which can be different than target user
		$t_actor_is_admin = current_user_is_administrator();
		$t_actor_can_manage_users = access_has_global_level( config_get( 'manage_user_threshold' ) );
		$t_actor_user_id = auth_get_current_user_id();

		# Ensure user exists
		if( !user_exists( $this->user_id ) ) {
			throw new ClientException( 'User does not exist', ERROR_USER_NOT_FOUND );
		}

		# User Data
		$t_user = $this->payload( 'user' );
		if( is_null( $t_user ) ) {
			throw new ClientException( 'Missing user data', ERROR_EMPTY_FIELD, array( 'user' ) );
		}

		# Protected
		$t_old_protected = user_is_protected( $this->user_id );
		$t_new_protected = isset( $t_user['protected'] ) ? (bool)$t_user['protected'] : null;
		if( !is_null( $t_new_protected ) && $t_old_protected != $t_new_protected ) {
			$this->protected = $t_new_protected ? true : false;
		}

		# LDAP
		$t_ldap = ( LDAP == config_get_global( 'login_method' ) );

		# Username
		if( isset( $t_user['name'] ) ) {
			$t_user['username'] = $t_user['name'];
		}

		$t_old_username = user_get_username( $this->user_id );
		$t_new_username = isset( $t_user['username' ] ) ? trim( $t_user['username']): null;

		if( !is_null( $t_new_username ) && $t_new_username !== $t_old_username ) {
			user_ensure_name_unique( $t_new_username, $this->user_id );
			user_ensure_name_valid( $t_new_username );

			$this->username = $t_new_username;
		}

		# Real Name
		$t_old_realname = user_get_realname( $this->user_id );
		$t_new_realname = isset( $t_user['real_name'] ) ? $t_user['real_name'] : null;

		if( !is_null( $t_new_realname ) ) {
			$t_new_realname = string_normalize( $t_new_realname );
		}

		# ... if realname should be set by LDAP, then fetch it.
		if( $t_ldap && config_get_global( 'use_ldap_realname' ) ) {
			$t_username = !is_null( $t_new_username ) ?: $t_old_username;
			$t_realname = ldap_realname_from_username( $t_username );
			if( !is_null( $t_realname ) && $t_realname !== $t_new_username ) {
				$t_new_realname = $t_realname;
			}
		}

		if( !is_null( $t_new_realname ) && $t_old_realname !== $t_new_realname ) {
			$this->realname = $t_new_realname;
		}

		# Email
		$t_old_email = user_get_field( $this->user_id, 'email' );
		$t_new_email = isset( $t_user['email'] ) ? trim( $t_user['email'] ) : null;

		# ... if email should be set by LDAP, then fetch it.
		if( $t_ldap && config_get_global( 'use_ldap_email' ) ) {
			$t_email = ldap_email( $this->user_id );
			if( !is_null( $t_email ) && $t_email !== $t_old_email ) {
				$t_new_email = $t_email;
			}
		}

		if( !is_null( $t_new_email ) && $t_new_email !== $t_old_email ) {
			email_ensure_valid( $t_new_email );
			email_ensure_not_disposable( $t_new_email );
			user_ensure_email_unique( $t_new_email, $this->user_id );

			$this->email = $t_new_email;
		}

		# Access Level
		$t_old_access_level = user_get_access_level( $this->user_id );
		$t_new_access_level = isset( $t_user['access_level'] ) ? $t_user['access_level'] : null;
		if( !is_null( $t_new_access_level ) ) {
			$t_new_access_level = access_parse_array( $t_new_access_level );
		}

		if( !is_null( $t_new_access_level ) && $t_old_access_level !== $t_new_access_level ) {
			$this->access_level = $t_new_access_level;
		}

		# Enabled
		$t_old_enabled = user_is_enabled( $this->user_id );
		$t_new_enabled = isset( $t_user['enabled'] ) ? $t_user['enabled'] : null;
		if( !is_null( $t_new_enabled ) && $t_old_enabled !== $t_new_enabled ) {
			$this->enabled = $t_new_enabled ? true : false;
		}

		# Notify User
		$this->notify_user = $this->payload( 'notify_user', false );

		# Authorization
		if( $t_actor_user_id === $this->user_id && !$t_actor_is_admin ) {
			if( $this->access_level !== null ) {
				throw new ClientException( 'Access denied to update own access level', ERROR_ACCESS_DENIED );
			}

			if( $this->protected !== null ) {
				throw new ClientException( 'Access denied to update own protected status', ERROR_ACCESS_DENIED );
			}

			if( $this->enabled !== null ) {
				throw new ClientException( 'Access denied to update own enabled status', ERROR_ACCESS_DENIED );
			}
		} else {
			if( !$t_actor_can_manage_users ) {
				throw new ClientException( 'Access denied to update users', ERROR_ACCESS_DENIED );
			}
		}

		# Don't allow updating accounts to access levels that are higher than
		# the actor's access level.
		if( !access_has_global_level( $t_old_access_level ) ||
		    ( !is_null( $t_new_access_level ) && !access_has_global_level( $t_new_access_level ) ) ) {
			throw new ClientException(
				'Access denied to update users that have higher access level',
				ERROR_ACCESS_DENIED );
		}

		# check that we are not downgrading the last administrator
		if( user_is_administrator( $this->user_id ) ) {
			$t_admin_threshold = config_get_global( 'admin_site_threshold' );
			$t_admin_count = user_count_level( $t_admin_threshold, /* enabled */ true );

			if( $t_admin_count <= 1 ) {
				if( ( !is_null( $this->enabled ) && !$this->enabled ) ||
				    ( !is_null( $this->access_level ) && $this->access_level < $t_admin_threshold ) ) {
						throw new ClientException(
							'Disabling or reducing access level of last admin not allowed.',
							ERROR_USER_CHANGE_LAST_ADMIN );
				}
			}
		}

		$this->old_user = array(
			'id' => $this->user_id,
			'username' => $t_old_username,
			'real_name' => $t_old_realname,
			'email' => $t_old_email,
			'access_level' => $t_old_access_level,
			'enabled' => $t_old_enabled,
			'protected' => $t_old_protected
		);

		$this->new_user = array(
			'id' => $this->user_id,
			'username' => $t_new_username ?: $t_old_username,
			'real_name' => !is_null( $t_new_realname ) ? $t_new_realname : $t_old_realname,
			'email' => $t_new_email ?: $t_old_email,
			'access_level' => $t_new_access_level ?: $t_old_access_level,
			'enabled' => !is_null( $t_new_enabled ) ? $t_new_enabled : $t_old_enabled,
			'protected' => !is_null( $t_new_protected ) ? $t_new_protected : $t_old_protected
		);
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		$this->update_user( $this->new_user );

		# Project specific access rights override global levels, hence, for users who are changed
		# to be administrators, we have to remove project specific rights.
		if( !is_null( $this->access_level ) ) {
			$t_admin_threshold = config_get_global( 'admin_site_threshold' );
			if( ( $this->access_level >= $t_admin_threshold ) && !user_is_administrator( $this->user_id ) ) {
				user_delete_project_specific_access_levels( $this->user_id );
			}
		}

		if( $this->notify_user ) {
			email_user_changed( $this->user_id, $this->old_user, $this->new_user );
		}

		event_signal( 'EVENT_MANAGE_USER_UPDATE', array( $this->user_id ) );

		user_clear_cache( $this->user_id );
		$t_select = array( 'id', 'name', 'real_name', 'email', 'access_level', 'enabled', 'protected' );
		$t_user = mci_user_get( $this->user_id, $t_select );
		return array( 'user' => $t_user );
	}

	/**
	 * Update user in database.
	 *
	 * There was no function to update user before. The functionality was done
	 * directly in the action page. Given that the database update is done here
	 * directly to discourage direct usage of the function without validation,
	 * authorization, triggering of events, etc.
	 *
	 * @param array $p_user User data
	 * @return void
	 */
	private function update_user( $p_user ) {
		db_param_push();

		$t_query = 'UPDATE {user}
			SET username=' . db_param() . ', email=' . db_param() . ',
				access_level=' . db_param() . ', enabled=' . db_param() . ',
				protected=' . db_param() . ', realname=' . db_param() . '
			WHERE id=' . db_param();

		$t_query_params = array(
			$p_user['username'],
			$p_user['email'],
			$p_user['access_level'],
			$p_user['enabled'],
			$p_user['protected'],
			$p_user['real_name'],
			$p_user['id'] );

		db_query( $t_query, $t_query_params );
	}
}

