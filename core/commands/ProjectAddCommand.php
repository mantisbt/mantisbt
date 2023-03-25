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

require_api( 'project_api.php' );

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_enum_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that adds a project.
 */
class ProjectAddCommand extends Command {
	/**
	 * Project Name
	 * @var string
	 */
	private $name;

	/**
	 * Project Description
	 * @var string
	 */
	private $description;

	/**
	 * Project View State
	 * @var int
	 */
	private $view_state;

	/**
	 * Project Inherit Global Categories
	 * @var int
	 */
	private $inherit_global;

	/**
	 * Project Status
	 * @var int
	 */
	private $status;

	/**
	 * Project Enabled
	 * @var int
	 */
	private $enabled;

	/**
	 * Project File Path
	 * @var string
	 */
	private $file_path;

	/**
	 * Constructor
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the inputs and user access level.
	 *
	 * @return void
	 */
	function validate() {
		if( !access_has_global_level( config_get( 'create_project_threshold' ) ) ) {
			throw new ClientException(
				'Access denied to create projects',
				ERROR_ACCESS_DENIED
			);
		}

		if( is_blank( $this->payload( 'name' ) ) ) {
			throw new ClientException(
				'Project name cannot be empty',
				ERROR_EMPTY_FIELD,
				array( 'name' )
			);
		}

		$t_project = $this->data['payload'];

		$this->name = $this->payload( 'name' );
		$this->description = $this->payload( 'description', '' );
		$this->inherit_global = $this->payload( 'inherit_global', true );
		$this->file_path = $this->payload( 'file_path', '' );
		$this->view_state = isset( $t_project['view_state'] ) ?  mci_get_project_view_state_id( $t_project['view_state'] ) : config_get( 'default_project_view_status' );
		$this->status = isset( $t_project['status'] ) ? mci_get_project_status_id( $t_project['status'] ) : 10 /* development */;
		$this->enabled = $this->payload( 'enabled', true );

		if( !project_is_name_unique( $this->name ) ) {
			throw new ClientException(
				'Project name is not unique',
				ERROR_PROJECT_NAME_NOT_UNIQUE,
				array( 'name' )
			);
		}

		$t_enum_values = MantisEnum::getValues( config_get( 'project_status_enum_string' ) );
		if( !in_array( $this->status, $t_enum_values ) ) {
			throw new ClientException(
				'Invalid project status',
				ERROR_INVALID_FIELD_VALUE,
				array( 'status' )
			);
		}

		if( !is_bool( $this->inherit_global ) ) {
			throw new ClientException(
				'Invalid project inherit global',
				ERROR_INVALID_FIELD_VALUE,
				array( 'inherit_global' )
			);
		}

		if( !is_bool( $this->enabled ) ) {
			throw new ClientException(
				'Invalid project enabled',
				ERROR_INVALID_FIELD_VALUE,
				array( 'enabled' )
			);
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array the command result
	 */
	function process() {
		$t_project_id = project_create(
			$this->name,
			$this->description,
			$this->status,
			$this->view_state,
			$this->file_path,
			$this->enabled,
			$this->inherit_global
		);

		# If user doesn't have management access to project, then user is not an ADMINISTRATOR.
		# grant user the higher of their global access level and the access level required to manage the project.
		$t_user_id = auth_get_current_user_id();
		$t_manage_project_access_level = config_get( 'manage_project_threshold', null, $t_user_id, $t_project_id );
		if( !access_has_project_level( $t_manage_project_access_level, $t_project_id, $t_user_id ) ) {
			$t_access_level = access_get_global_level( $t_user_id );
			if( $t_access_level < $t_manage_project_access_level ) {
				$t_access_level = $t_manage_project_access_level;
			}

			project_add_user( $t_project_id, $t_user_id, $t_access_level );
		}

		global $g_project_override;
		$g_project_override = $t_project_id;

		event_signal( 'EVENT_MANAGE_PROJECT_CREATE', array( $t_project_id ) );

		$t_result = array();
		if( $this->option('return_project', false ) ) {
			$t_lang = mci_get_user_lang( $t_user_id );
			$t_result['project'] = mci_project_get( $t_project_id, $t_lang, /* detail */ true );
		} else {
			$t_result['id'] = $t_project_id;
		}

		return $t_result;
	}
}
