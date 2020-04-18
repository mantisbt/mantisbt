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
 * A command that gets a set of config options.
 *
 * The following query string parameters are supported:
 * - option/option[]: can be a string or an array
 * - project_id: an optional parameter for project id to get configs for. Default ALL_PROJECTS (0).
 * - user_id: an optional parameter for user id to get configs for.  Default current user.
 *   can be 0 for ALL_USERS.
 *
 * The response will include a config key that is an array of requested configs.  Configs
 * that are not public or are not defined will be filtered out, and request will still succeed.
 * This is to make it easier to request configs that maybe defined in some versions of MantisBT
 * but not others.
 *
 * Note that only users with ADMINISTRATOR access can fetch configuration for other users.
 */
class ConfigsGetCommand extends Command {
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
	 */
	function validate() {
		$this->options = $this->query( 'option' );
		if( !is_array( $this->options ) ) {
			$this->options = array( $this->options );
		}

		$this->project_id = $this->query( 'project_id' );
		if( is_null( $this->project_id ) ) {
			$this->project_id = ALL_PROJECTS;
		}
	
		if( $this->project_id != ALL_PROJECTS && !project_exists( $this->project_id ) ) {
			throw new ClientException(
				sprintf( "Project '%d' not found", $this->project_id ),
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}
	
		$this->user_id = $this->query( 'user_id' );
		if( is_null( $this->user_id ) ) {
			$this->user_id = auth_get_current_user_id();
		} else {
			if( $this->user_id != ALL_USERS &&
			$this->user_id != auth_get_current_user_id() &&
			!current_user_is_administrator() ) {
				throw new ClientException(
					'Admin access required to get configs for other users',
					ERROR_ACCESS_DENIED );
			}
	
			if( $this->user_id != ALL_USERS && !user_exists( $this->user_id ) ) {
				throw new ClientException(
					sprintf( "User '%d' not found.", $this->user_id ),
					ERROR_USER_BY_ID_NOT_FOUND,
					array( $this->user_id ) );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_configs = array();

		foreach( $this->options as $t_option ) {
			# Filter out undefined configs rather than error, they may be valid in some MantisBT versions but not
			# others.
			if( !config_is_set( $t_option ) ) {
				continue;
			}
	
			# Filter out private configs, since they can be private in some configs but public in others.
			if( config_is_private( $t_option ) ) {
				continue;
			}
	
			$t_value = config_get( $t_option, /* default */ null, $this->user_id, $this->project_id );
			if( ConfigsGetCommand::config_is_enum( $t_option ) ) {
				$t_value = ConfigsGetCommand::config_get_enum_as_array( $t_option, $t_value );
			}
	
			$t_config_pair = array(
				'option' => $t_option,
				'value' => $t_value
			);
	
			$t_configs[] = $t_config_pair;
		}
	
		# wrap all configs into a configs attribute to allow adding other information if needed in the future
		# that belongs outside the configs response.
		return array( 'configs' => $t_configs );
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
	 * Gets the enum config option as an array with the id as the key and the value
	 * as an array with name and label (localized name) keys.
	 *
	 * @param string $p_enum_name The enum config option name.
	 * @param string $p_enum_string_value The enum config option value.
	 * @return array The enum array.
	 */
	private static function config_get_enum_as_array( $p_enum_name, $p_enum_string_value ) {
		$t_enum_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string_value );
		$t_localized_enum_string = lang_get( $p_enum_name );

		$t_enum_array = array();

		foreach( $t_enum_assoc_array as $t_id => $t_name ) {
			$t_label = MantisEnum::getLocalizedLabel( $p_enum_string_value, $t_localized_enum_string, $t_id );
			$t_enum_entry = array( 'id' => $t_id, 'name' => $t_name, 'label' => $t_label );
			$t_enum_array[] = $t_enum_entry;
		}

		return $t_enum_array;
	}
}

