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
	 * The project id
	 * 
	 * @var integer
	 */
	protected $project_id;

	/**
	 * @var int|null $timestamp
	 */
	protected $timestamp;

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
	 * @throws ClientException
	 */
	function validate() {
		$this->project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );

		if( !project_exists( $this->project_id ) ) {
			throw new ClientException(
				"Project $this->project_id not found",
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}

		if( !access_has_project_level( config_get( 'manage_project_threshold' ), $this->project_id ) ) {
			throw new ClientException( 'Access denied to add versions', ERROR_ACCESS_DENIED );
		}

		$t_name = $this->payload( 'name', '' );
		$t_name = trim( $t_name );
		if( !version_is_valid_name( $t_name ) ) {
			throw new ClientException(
				'Invalid version name',
				ERROR_INVALID_FIELD_VALUE,
				array( 'name' ) );
		}

		$t_timestamp = $this->payload( 'timestamp', '' );
		$this->timestamp = is_blank( $t_timestamp ) ? null : strtotime( $t_timestamp );
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

		$t_name = trim( $this->payload( 'name' ) );
		$t_description = trim( $this->payload( 'description', '' ) );
		$t_released = $this->payload( 'released', false );
		$t_obsolete = $this->payload( 'obsolete', false );

		$t_version_id = version_add(
			$this->project_id,
			$t_name,
			$t_released ? VERSION_RELEASED : VERSION_FUTURE,
			$t_description,
			$this->timestamp,
			$t_obsolete );

		version_cache_clear_row( $t_version_id );
		$t_version = version_get( $t_version_id );
		$t_result = array( 'version' => VersionGetCommand::VersionToArray( $t_version ) );

		$g_project_override = $t_prev_project_id;

		return $t_result;
	}
}
