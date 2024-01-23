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

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );

/**
 * A command that gets project versions.
 */
class VersionGetCommand extends Command {
	/**
	 * The project id to get the version(s) for.
	 * 
	 * @var integer
	 */
	private $project_id;

	/**
	 * The version id to get or null for all versions in the project.
	 *
	 * @var integer|null
	 */
	private $version_id;

	/**
	 * $p_data['query'] is expected to contain:
	 * - project_id (integer)
	 * - version_id (integer) - optional
	 * - obsolete (integer) - optional - 1 to include obsolete versions, 0 to exclude them. default 0.
	 * - released (integer) - optional - default null
	 * - inherit (integer) - optional - 1 to include inherited versions, 0 to exclude them. default 0.
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

		if( !access_has_project_level( VIEWER, $this->project_id ) ) {
			throw new ClientException( 'Access denied to get versions', ERROR_ACCESS_DENIED );
		}

		$this->version_id = $this->query( 'version_id' );

		if( !is_null( $this->version_id ) ) {
			if( is_blank( $this->version_id ) ) {
				throw new ClientException( 'Invalid version name', ERROR_EMPTY_FIELD, array( 'id' ) );
			}
			
			if( !version_exists( $this->version_id ) ) {
				throw new ClientException( 'Version not found', ERROR_VERSION_NOT_FOUND, array( $this->version_id ) );
			}
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

		if( is_null( $this->version_id ) ) {
			$t_versions = version_get_all_rows(
				$this->project_id,
				/* released */ $this->query( 'released' ),
				/* obsolete */ $this->query( 'obsolete', false ),
				/* inherit */ $this->query( 'inherit', false ) );

			$t_versions = array_map( 'VersionGetCommand::VersionRowToArray', $t_versions );
		} else {
			$t_version = version_get( $this->version_id );

			if( $t_version->project_id == $this->project_id ) {
				$t_version = VersionGetCommand::VersionToArray( $t_version );
				$t_versions = array( $t_version );
			} else {
				$t_versions = array();
			}
		}

		$g_project_override = $t_prev_project_id;

		$t_result = array(
			'versions' => $t_versions
		);

		return $t_result;
	}

	/**
	 * Convert a version row to an array.
	 *
	 * @param array $p_row The version row.
	 * @return array The version array.
	 */
	public static function VersionRowToArray( $p_row ) {
		$t_version = array();
		$t_version['id'] = (int)$p_row['id'];
		$t_version['name'] = $p_row['version'];

		if( !is_blank( $p_row['description'] ) ) {
			$t_version['description'] = $p_row['description'];
		}

		$t_version['released'] = (bool)$p_row['released'];
		$t_version['obsolete'] = (bool)$p_row['obsolete'];
		$t_version['timestamp'] = ApiObjectFactory::datetime( $p_row['date_order'] );

		return $t_version;
	}

	/**
	 * Convert a version object to an array.
	 * 
	 * @param VersionData $p_version The version object.
	 * @return array The version array.
	 */
	public static function VersionToArray( $p_version ) {
		$t_version = array();

		$t_version['id'] = (int)$p_version->id;
		$t_version['name'] = $p_version->version;

		if( !is_blank( $p_version->description ) ) {
			$t_version['description'] = $p_version->description;
		}

		$t_version['released'] =(bool)$p_version->released;
		$t_version['obsolete'] = (bool)$p_version->obsolete;
		ApiObjectFactory::$soap = false;
		$t_version['timestamp'] = ApiObjectFactory::datetime( $p_version->date_order );

		return $t_version;
	}
}
