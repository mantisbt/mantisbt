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

use Mantis\Exceptions\ClientException;

/**
 * A command that deletes a project.
 */
class ProjectDeleteCommand extends Command {
	/**
	 * @var integer id of project to delete
	 */
	private $id;

	/**
	 * Constructor
	 *
	 * $p_data['query'] is expected to contain:
	 * - id (integer)
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
	 * @throws ClientException
	 */
	function validate() {
		$t_user_id = auth_get_current_user_id();
		if( !access_has_project_level( config_get( 'delete_project_threshold', null, $t_user_id, $this->id ) ) ) {
			throw new ClientException(
				'Access denied to delete project',
				ERROR_ACCESS_DENIED );
		}

		$this->id = helper_parse_id( $this->query( 'id' ), 'project_id' );
		if( !project_exists( $this->id ) ) {
			throw new ClientException(
				"Project '$this->id' not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $this->id ) );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		global $g_project_override;
		$g_project_override = $this->id;

		event_signal( 'EVENT_MANAGE_PROJECT_DELETE', array( $this->id ) );

		project_delete( $this->id );
		return [];
	}
}
