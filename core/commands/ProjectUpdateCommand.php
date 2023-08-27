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
 * A command that updates a project.
 */
class ProjectUpdateCommand extends Command {
	/**
	 * Project Id
	 * @var integer
	 */
	private $id;

	/**
	 * Project Name
	 * @var string
	 */
	private $name;

	/**
	 * Project Description
	 * 
	 * @var string
	 */
	private $description;

	/**
	 * Project View State
	 * 
	 * @var int
	 */
	private $view_state;

	/*
	 * Project Inherit Global Categories
	 * 
	 * @var int
	 */
	private $inherit_global;

	/**
	 * Project Status
	 * 
	 * @var int
	 */
	private $status;

	/**
	 * Project Enabled
	 * 
	 * @var int
	 */
	private $enabled;

	/**
	 * Project File Path
	 * 
	 * @var string
	 */
	private $file_path;

	/**
	 * Constructor
	 *
	 * $p_data['query'] is expected to contain:
	 * - id (integer)
	 *
	 * $p_data['payload'] is expected to a subset of the following fields:
	 * - id (integer)
	 * - name (string)
	 * - description (string)
	 * - view_state (int)
	 * - inherit_global (int)
	 * - status (int)
	 * - enabled (int)
	 * - file_path (string)
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the inputs and access level.
	 *
	 * @throws ClientException
	 */
	protected function validate() {
		$this->id = (int)$this->query( 'id' );
		if( $this->id == ALL_PROJECTS || $this->id < 1 ) {
			throw new ClientException(
				'Project id is invalid',
				ERROR_INVALID_FIELD_VALUE,
				array( 'id' ) );
		}

		$t_project = $this->data['payload'];
		if( isset( $t_project['id'] ) && (int)$t_project['id'] != $this->id ) {
			throw new ClientException(
				'Project id in payload does not match id in query',
				ERROR_INVALID_FIELD_VALUE,
				array( 'id' ) );
		}

		if( !project_exists( $this->id ) ) {
			throw new ClientException(
				'Project not found',
				ERROR_PROJECT_NOT_FOUND,
				array( $this->id ) );
		}

		global $g_project_override;
		$g_project_override = $this->id;

		$t_user_id = auth_get_current_user_id();
		if( !access_has_project_level( config_get( 'manage_project_threshold', null, $t_user_id, $this->id ) ) ) {
			throw new ClientException(
				'Access denied to update project',
				ERROR_ACCESS_DENIED );
		}

		$this->name = $this->payload( 'name', project_get_field( $this->id, 'name' ) );
		if( is_blank( $this->name ) ) {
			throw new ClientException(
				'Project name cannot be blank',
				ERROR_EMPTY_FIELD,
				array( 'name' ) );
		}

		$this->description = $this->payload( 'description', project_get_field( $this->id, 'description' ) );
		$this->inherit_global = (int)$this->payload( 'inherit_global', project_get_field( $this->id, 'inherit_global' ) );
		$this->enabled = (int)$this->payload( 'enabled', project_get_field( $this->id, 'enabled' ) );
		$this->file_path = $this->payload( 'file_path', project_get_field( $this->id, 'file_path' ) );

		$this->view_state = $this->payload( 'view_state', array( 'id' => project_get_field( $this->id, 'view_state' ) ) );
		$this->view_state = mci_get_project_view_state_id( $this->view_state );

		$this->status = $this->payload( 'status', array( 'id' => project_get_field( $this->id, 'status' ) ) );
		$this->status = mci_get_project_status_id( $this->status );

		# check to make sure a modified project doesn't already exist
		if( $this->name != project_get_name( $this->id ) ) {
			if( !project_is_name_unique( $this->name ) ) {
				throw new ClientException(
					'Project name already exists',
					ERROR_PROJECT_NAME_NOT_UNIQUE,
					array( $this->name ) );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array
	 */
	protected function process() {
		project_update(
			$this->id,
			$this->name,
			$this->description,
			$this->status,
			$this->view_state,
			$this->file_path,
			$this->enabled,
			$this->inherit_global
		);

		project_clear_cache( $this->id );

		event_signal( 'EVENT_MANAGE_PROJECT_UPDATE', array( $this->id ) );

		$t_result = array();
		if( $this->option('return_project', false ) ) {
			$t_user_id = auth_get_current_user_id();

			$t_lang = mci_get_user_lang( $t_user_id );
			$t_result['project'] = mci_project_get( $this->id, $t_lang, /* detail */ true );
		} else {
			$t_result['id'] = $this->id;
		}

		return $t_result;
	}
}
