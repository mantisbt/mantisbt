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

use Mantis\Exceptions\ClientException;

/**
 * A command that updates a project in the project hierarchy (subproject).
 */
class ProjectHierarchyUpdateCommand extends Command {
	/**
	 * @var integer
	 */
	private $project_id;

	/**
	 * @var integer
	 */
	private $subproject_id;

	/**
	 * @var boolean
	 */
	private $inherit_parent;

	/**
	 * Constructor
	 *
	 * $p_data['query'] is expected to contain:
	 * - project_id (integer)
	 * - subproject_id (integer)
	 *
	 * $p_data['payload'] is expected to contain:
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
		if ( config_get_global( 'subprojects_enabled' ) == OFF ) {
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

		$this->subproject_id = helper_parse_id( $this->query( 'subproject_id' ), 'subproject_id' );
		if( !project_exists( $this->subproject_id ) ) {
			throw new ClientException(
				"Project '$this->subproject_id' not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $this->subproject_id ) );
		}

		if( !access_has_project_level( config_get( 'manage_project_threshold' ), $this->project_id ) ||
			!access_has_project_level( config_get( 'manage_project_threshold' ), $this->subproject_id ) ) {
			throw new ClientException(
				'Access denied to delete subprojects',
				ERROR_ACCESS_DENIED );
		}

		if( !in_array( $this->subproject_id, project_hierarchy_get_subprojects( $this->project_id, true ) ) ) {
			throw new ClientException(
				"Project '$this->subproject_id' is not a subproject of '$this->project_id'",
				ERROR_PROJECT_SUBPROJECT_NOT_FOUND,
				array( $this->subproject_id, $this->project_id ) );
		}

		$this->inherit_parent = $this->payload( 'inherit_parent' );
		if( !isset( $this->inherit_parent ) ) {
			throw new ClientException( 'inherit_parent must be supplied',
				ERROR_EMPTY_FIELD,
				array( 'inherit_parent' ) );
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

		project_hierarchy_update( $this->subproject_id, $this->project_id, $this->inherit_parent );

		return array();
	}
}
