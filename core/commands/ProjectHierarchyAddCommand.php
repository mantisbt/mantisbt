<?php
# MantisBT - A PHP based bugtracking system
#
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

require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that adds a project to the project hierarchy (subproject).
 */
class ProjectHierarchyAddCommand extends Command {
	/**
	 * @var integer
	 */
	private $project_id;

	/**
	 * @var integer
	 */
	private $subproject_id;

	/**
	 * Constructor
	 *
	 * $p_data['query'] is expected to contain:
	 * - project_id (integer)
	 *
	 * $p_data['payload'] is expected to contain:
	 * - project (array)
	 * - inherit_parent (boolean)
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
		if ( config_get( 'subprojects_enabled' ) == OFF ) {
			throw new ClientException(
				'Project hierarchy (subprojects) is disabled',
				ERROR_PROJECT_HIERARCHY_DISABLED );
		}

		$this->project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );
		if( !project_exists( $this->project_id ) ) {
			throw new ClientException(
				"Project '$this->project_id' not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}

		$this->subproject_id = mci_get_project_id( $this->payload( 'project' ), false );
		if ( !isset( $this->subproject_id ) ) {
			$t_subproject = $this->payload( 'project' );
			$t_subproject_name = isset( $t_subproject['name'] )	? $t_subproject['name'] : '';
			throw new ClientException(
				"Project '$t_subproject_name' not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $t_subproject_name ) );
		} else if ( !project_exists( $this->subproject_id ) ) {
			throw new ClientException(
				"Project '$this->subproject_id' not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $this->subproject_id ) );
		}

		if( $this->project_id == $this->subproject_id ) {
			throw new ClientException(
				"Project can't be subproject of itself",
				ERROR_PROJECT_RECURSIVE_HIERARCHY );
		}

		if( !access_has_project_level( config_get( 'manage_project_threshold' ), $this->project_id ) ||
			!access_has_project_level( config_get( 'manage_project_threshold' ), $this->subproject_id ) ) {
			throw new ClientException(
				'Access denied to add subprojects',
				ERROR_ACCESS_DENIED );
		}

		if( in_array( $this->subproject_id, project_hierarchy_get_subprojects( $this->project_id, true ) ) ) {
			throw new ClientException(
				"Project '$this->subproject_id' is already a subproject of '$this->project_id'",
				ERROR_PROJECT_SUBPROJECT_DUPLICATE,
				array( $this->subproject_id, $this->project_id ) );
		}

		if( in_array( $this->project_id, project_hierarchy_get_all_subprojects( $this->subproject_id, true ) ) ) {
			throw new ClientException(
				"Project can't be a descendant subproject of itself",
				ERROR_PROJECT_RECURSIVE_HIERARCHY );
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		if( $this->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			global $g_project_override;
			$g_project_override = $this->project_id;
		}

		project_hierarchy_add( $this->subproject_id, $this->project_id,
			$this->payload( 'inherit_parent', true ) );

		return array();
	}
}
