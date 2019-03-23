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

use Mantis\Exceptions\ClientException;

/**
 * A command that adds a project version.
 */
class VersionAddCommand extends Command {
	/**
	 * $p_data['query'] is expected to contain:
	 * - project_id (integer)
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
		$t_project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );

		if( !access_has_project_level( config_get( 'manage_project_threshold' ), $t_project_id ) ) {
			throw new ClientException( 'Access denied to add versions', ERROR_ACCESS_DENIED );
		}

		$t_name = $this->payload( 'name' );
		if( is_blank( $t_name ) ) {
			throw new ClientException( 'Invalid version name', ERROR_EMPTY_FIELD, array( 'name' ) );
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );
		if( $t_project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			global $g_project_override;
			$g_project_override = $t_project_id;
		}

		$t_name = trim( $this->payload( 'name' ) );
		$t_description = trim( $this->payload( 'description', '' ) );
		$t_released = $this->payload( 'released', false );
		$t_obsolete = $this->payload( 'obsolete', false );

		$t_timestamp = $this->payload( 'timestamp', '' );
		if( is_blank( $t_timestamp ) ) {
			$t_timestamp = null;
		} else {
			$t_timestamp = strtotime( $t_timestamp );
		}

		$t_version_id = version_add(
			$t_project_id,
			$t_name,
			$t_released ? VERSION_RELEASED : VERSION_FUTURE,
			$t_description,
			$t_timestamp,
			$t_obsolete );

		return array( 'id' => $t_version_id );
	}
}

