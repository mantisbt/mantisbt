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
require_api( 'version_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that adds a project version.
 */
class VersionDeleteCommand extends Command {
	/**
	 * The project id
	 * @var integer
	 */
	private $project_id;

	/**
	 * The version id
	 * @var integer
	 */
	private $version_id;

	/**
	 * $p_data['query'] is expected to contain:
	 * - project_id (integer)
	 * - version_id (integer)
	 *
	 * $p_data['payload'] is expected to contain:
	 * - name (string)
	 * - description (string)
	 * - released (bool)
	 * - obsolete (bool)
	 * - timestamp (timestamp) - e.g. 2018-05-21T15:00:00-08:00
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
		$this->project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );

		if( !project_exists( $this->project_id ) ) {
			throw new ClientException(
				"Project '$this->project_id' not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}

		$t_access_level = config_get(
			'manage_project_threshold',
			/* default */ null,
			/* user */ null,
			$this->project_id );
		if( !access_has_project_level( $t_access_level, $this->project_id ) ) {
			throw new ClientException( 'Access denied to delete version', ERROR_ACCESS_DENIED );
		}

		# Make sure that version belongs to the project
		# No need to check if version exists, version_get_field() will take care of it
		$this->version_id = helper_parse_id( $this->query( 'version_id' ), 'version_id' );
		$t_project_id_from_version = version_get_field( $this->version_id, 'project_id' );
		if( $t_project_id_from_version !== $this->project_id ) {
			throw new ClientException(
				"Version with id '$this->version_id' not found",
				ERROR_VERSION_NOT_FOUND,
				array( $this->version_id ) );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		global $g_project_override;

		$t_prev_project_id = $g_project_override;
		$g_project_override = $this->project_id;

		# The remove API triggers the EVENT_VERSION_DELETE event
		# will leave it there so that it is triggered when a project
		# is deleted, and it deletes all versions.
		version_remove( $this->version_id, '' );

		$g_project_override = $t_prev_project_id;
		return [];
	}
}

