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

/**
 * A command that sets config options.
 * 
 * Only config options that can be overriden in the database can be set via this command.
 */
class ConfigsSetCommand extends Command {
	/**
	 * Array of option names
	 */
	private $options;

	/**
	 * The project id to retrieve options for, or ALL_PROJECTS.
	 */
	private $project_id;

	/**
	 * The user id to retrieve options for, or ALL_USERS
	 */
	private $user_id;

	private $configs;

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

		// verify that user has administrator access
		$t_access_level_required = config_get_global( 'admin_site_threshold' );
		if( !access_has_global_level( $t_access_level_required, $t_current_user_id ) ) {
			throw new ClientException(
				"User doesn't have access to set configs.",
				ERROR_ACCESS_DENIED );
		}

		$t_payload = $this->data['payload'];

		// parse user id from payload, if not provided default to ALL_USERS
		if( isset( $t_payload['user'] ) ) {
			$this->user_id = mci_get_user_id( $t_payload['user'] );
		} else {
			$this->user_id = ALL_USERS;
		}

		// parse project id from payload, if not provided default to ALL_PROJECTS
		if( isset( $t_payload['project'] ) )  {
			$this->project_id = mci_get_project_id( $t_payload['project'] );
		} else {
			$this->project_id = ALL_PROJECTS;
		}

		if( $this->project_id != ALL_PROJECTS && !project_exists( $this->project_id ) ) {
			throw new ClientException(
				"Project doesn't exist",
				ERROR_PROJECT_NOT_FOUND );
		}

		# This check is redundant if command is limited to administrator, but it is
		# better to have it as a safe guard is non-administrators can change their own
		# settings later.
		if( $this->project_id != ALL_PROJECTS &&
			$this->user_id != ALL_USERS &&
			!access_has_project_level( VIEWER, $this->project_id, $this->user_id ) ) {
			throw new ClientException(
				"User doesn't have access to specified project.",
				ERROR_ACCESS_DENIED );
		}

		global $g_global_settings;
		$t_set_of_configs = $this->payload( 'configs' );
		foreach( $t_set_of_configs as $t_config ) {
			if( !isset( $t_config['name'] ) || is_blank( $t_config['name']) ) {
				throw new ClientException(
					'Config name not provided',
					ERROR_EMPTY_FIELD,
					array( 'name' ) );
			};

			$t_name = $t_config['name'];

			# Silently ignore unknown configs - similar to get configs. This may be useful for
			# compatibility with different MantisBT versions.
			if( !config_is_set( $t_name ) ) {
				continue;
			}

			# these are config options that are stored in the database, but can't be deleted
			# or modified. For example, database_version (schema version).
			if( !config_can_delete( $t_name ) ) {
				continue;
			}

			# It is not allowed to set configs that are not public.
			/*
			global $g_public_config_names
			if( !in_array( $t_name, $g_public_config_names ) ) {
				throw new ClientException(
					sprintf( "Config '%s' is not public", $t_name ),
					ERROR_INVALID_FIELD_VALUE,
					array( $t_name ) );
			}
			*/

			# It is not allowed to set configs that are global and don't support db overrides
			if( !config_can_set_in_database( $t_name ) ) {
				throw new ClientException(
					sprintf( "Config '%s' is global and cannot be set", $t_name ),
					ERROR_INVALID_FIELD_VALUE,
					array( $t_name ) );
			}

			if( !isset( $t_config['value'] ) ) {
				$t_config['value'] = null;
			}

			# TODO: handle advanced configs like enums that may be set as string or array

			$this->configs[] = $t_config;
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns void
	 */
	protected function process() {
		foreach( $this->configs as $t_config ) {
			if( is_null( $t_config['value'] ) ) {
				config_delete( $t_config['name'], $this->user_id, $this->project_id );
			} else {
				config_set( $t_config['name'], $t_config['value'], $this->user_id, $this->project_id );
			}
		}
	}
}
