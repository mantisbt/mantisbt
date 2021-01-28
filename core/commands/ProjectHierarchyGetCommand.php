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

require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'authentication_api.php' );
require_api( 'user_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that gets project subprojects from the project hierarchy.
 */
class ProjectHierarchyGetCommand extends Command {

	/**
	 * @var integer
	 */
	private $project_id;

	/**
	 * @var boolean
	 */
	private $recursive;

	/**
	 * @var boolean
	 */
	private $include_disabled;

	/**
	 * return an array of user accessible subprojects of a project (recurse if requested)
	 * @param integer $p_project_id	A valid project identifier.
	 * @param boolean $p_recursive Include only childs or all descendents.
	 * @param boolean $p_include_disabled Include disabled projects in the resulting array.
	 * @param array $p_subprojects The current array of subprojects.
	 * @return array
	 */
	private function get_subprojects( $p_user_id, $p_project_id, $p_recursive, $p_include_disabled, $p_subprojects  = array() ) {
		$t_subprojects = $p_subprojects;
		$t_subproject_ids = user_get_accessible_subprojects(
			$p_user_id, $p_project_id, $p_include_disabled );
		foreach( $t_subproject_ids as $t_subproject_id ) {
			$t_subprojects[] = array(
				'id' => $t_subproject_id,
				'name' => project_get_name( $t_subproject_id ),
				'enabled' => project_enabled( $t_subproject_id ),
				'parent' => array( 
					'id' => $p_project_id,
					'name' => project_get_name( $p_project_id )
				),
				'inherit_parent' => project_hierarchy_inherit_parent(
					 $t_subproject_id, $p_project_id, $p_include_disabled )
			);

			if( true === $p_recursive ) {
				$t_subprojects += $this->get_subprojects(
					$p_user_id, $t_subproject_id, $p_recursive, $p_include_disabled, $t_subprojects );
			}
		}

		return $t_subprojects;
	}

	/**
	 * Constructor
	 *
	 * $p_data['query'] is expected to contain:
	 * - project_id (integer)
	 * - recursive (boolean)
	 * - include_disabled (boolean)
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

		$this->recursive = $this->query( 'recursive' );
		if( !isset( $this->recursive ) ) {
			throw new ClientException(
				"'recursive' not provided",
				ERROR_EMPTY_FIELD,
				array( 'recursive' ) );
		} else if( !is_bool( $this->recursive ) ) {
			throw new ClientException(
				"Invalid value for 'recursive', not bool",
				ERROR_INVALID_FIELD_VALUE,
				array( 'recursive' ) );
		}

		$this->include_disabled = $this->query( 'include_disabled' );
		if( !isset( $this->include_disabled ) ) {
			throw new ClientException(
				"'include_disabled' not provided",
				ERROR_EMPTY_FIELD,
				array( 'include_disabled' ) );
		} else if( !is_bool( $this->include_disabled ) ) {
			throw new ClientException(
				"Invalid value for 'include_disabled', not bool",
				ERROR_INVALID_FIELD_VALUE,
				array( 'include_disabled' ) );
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

		return $this->get_subprojects(
			auth_get_current_user_id(), $this->project_id, $this->recursive, $this->include_disabled );
	}
}

