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

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );

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
	 */
	function validate() {
		$t_current_user_id = auth_get_current_user_id();

		// verify that user has appropriate access level to set configuration via database
		$t_access_level_required = config_get_global( 'set_configuration_threshold' );
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
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
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

		$t_set_of_configs = $this->payload( 'configs' );
		foreach( $t_set_of_configs as $t_config ) {
			if( !isset( $t_config['option'] ) || is_blank( $t_config['option']) ) {
				throw new ClientException(
					'Config option not provided',
					ERROR_EMPTY_FIELD,
					array( 'option' ) );
			};

			$t_name = $t_config['option'];

			# Silently ignore unknown configs - similar to get configs. This may be useful for
			# compatibility with different MantisBT versions.
			if( !config_is_set( $t_name ) ) {
				continue;
			}

			# make sure that configuration option specified is a valid one.
			$t_not_found_value = '***CONFIG OPTION NOT FOUND***';
			if( config_get( $t_name, $t_not_found_value ) === $t_not_found_value ) {
				continue;
			}

			# these are config options that are stored in the database, but can't be deleted
			# or modified. For example, database_version (schema version).
			if( !config_can_delete( $t_name ) ) {
				continue;
			}

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

			if( ConfigsSetCommand::config_is_enum( $t_name ) &&
			    is_array( $t_config['value'] ) ) {
				$t_config['value'] = ConfigsSetCommand::array_to_enum_string( $t_name, $t_config['value'] );
			}

			$this->options[] = $t_config;
		}

		# This mode is only for web UI and it will always have a single config option
		if( MANAGE_CONFIG_ACTION_EDIT === $this->option( 'edit_action', MANAGE_CONFIG_ACTION_CREATE ) ) {
			$t_original_option = $this->option( 'original_option', '' );
			$t_original_user_id = (int)$this->option( 'original_user_id', '' );
			$t_original_project_id = (int)$this->option( 'original_project_id', '' );

			if( count( $this->options ) != 1 ||
				is_blank( $t_original_option ) ||
				is_blank( $t_original_user_id ) ||
				is_blank( $t_original_project_id ) ) {
				throw new ClientException(
					'Invalid parameters for edit action',
					ERROR_INVALID_FIELD_VALUE,
					array( 'edit_action' ) );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		# The edit case is internal only to web UI and it will always have a single config option
		if( MANAGE_CONFIG_ACTION_EDIT === $this->option( 'edit_action', MANAGE_CONFIG_ACTION_CREATE ) ) {
			$t_original_option = $this->option( 'original_option' );
			$t_original_user_id = (int)$this->option( 'original_user_id' );
			$t_original_project_id = (int)$this->option( 'original_project_id' );

			$t_option = $this->options[0];

			# EDIT action doesn't keep original if key values are different.
			if ( $t_original_option !== $t_option['option']
					|| $t_original_user_id !== $this->user_id
					|| $t_original_project_id !== $this->project_id ) {
				config_delete( $t_original_option, $t_original_user_id, $t_original_project_id );
			}
		}

		foreach( $this->options as $t_option ) {			
			if( is_null( $t_option['value'] ) ) {
				config_delete( $t_option['option'], $this->user_id, $this->project_id );
			} else {
				config_set( $t_option['option'], $t_option['value'], $this->user_id, $this->project_id );
			}
		}

		return [];
	}

	/**
	 * Checks if the specific config option is an enum.
	 *
	 * @param string $p_option The option name.
	 * @return bool true enum, false otherwise.
	 */
	private static function config_is_enum( $p_option ) {
		return stripos( $p_option, '_enum_string' ) !== false;
	}

	/**
	 * Convert an enum array into an enum string representation.
	 * 
	 * Input: 
	 * - array of enum entries. Each enum entry has an id and name.
	 * - Note that label (localized name) is not settable and hence not expected.
	 */
	private static function array_to_enum_string( $p_enum_name, $p_enum_array ) {
		$t_enum_string = '';

		foreach( $p_enum_array as $t_entry ) {
			if( !isset( $t_entry['id'] ) || !isset( $t_entry['name'] ) ) {
				throw new ClientException(
					sprintf( "Enum '%s' missing 'id' or 'name' field for an entry", $p_enum_name ),
					ERROR_INVALID_FIELD_VALUE,
					array( $p_enum_name )
				);
			}

			if( !is_numeric( $t_entry['id'] ) ) {
				throw new ClientException(
					sprintf( "Enum '%s' has 'id' that is not numeric", $p_enum_name ),
					ERROR_INVALID_FIELD_VALUE,
					array( $p_enum_name )
				);
			}

			if( isset( $t_entry['label'] ) ) {
				throw new ClientException(
					sprintf( "Enum '%s' has 'label' property which is not supported", $p_enum_name ),
					ERROR_INVALID_FIELD_VALUE,
					array( $p_enum_name )
				);
			}

			if( !preg_match('/^[a-zA-Z0-9_-]+$/', $t_entry['name'] ) ) {
				throw new ClientException(
					sprintf( "Enum '%s' has invalid enum entry name '%s'.", $p_enum_name, $t_entry['name'] ),
					ERROR_INVALID_FIELD_VALUE,
					array( $p_enum_name )
				);
			}

			if( !empty( $t_enum_string ) ) {
				$t_enum_string .= ',';
			}

			$t_enum_string .= (int)$t_entry['id'] . ':' . $t_entry['name'];
		}

		return $t_enum_string;
	}
}
