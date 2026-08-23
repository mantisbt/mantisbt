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

require_api( 'category_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );

global $g_absolute_path;
require_once( $g_absolute_path . 'api/soap/mc_core.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that gets the categories belonging to a project.
 */
class CategoryGetCommand extends Command {
	/** @var int */
	private $project_id;
	/** @var int|null */
	private $category_id;

	/**
	 * Validate project visibility and the optional category identifier.
	 *
	 * @return void
	 * @throws ClientException If the project or category is unavailable, or
	 *                         access is denied.
	 */
	function validate() {
		$t_project_id = $this->query( 'project_id' );
		$this->project_id = $t_project_id == ALL_PROJECTS ? ALL_PROJECTS : helper_parse_id( $t_project_id, 'project_id' );
		if( $this->project_id != ALL_PROJECTS && !project_exists( $this->project_id ) ) {
			throw new ClientException( "Project '$this->project_id' not found", ERROR_PROJECT_NOT_FOUND, array( $this->project_id ) );
		}
		helper_set_current_project( $this->project_id );

		if( !access_has_project_level( VIEWER, $this->project_id ) ) {
			throw new ClientException( 'Access denied to get categories', ERROR_ACCESS_DENIED );
		}

		$this->category_id = $this->query( 'category_id' );
		if( $this->category_id !== null ) {
			$this->category_id = helper_parse_id( $this->category_id, 'category_id' );
			if( !category_exists_in_project( $this->category_id, $this->project_id ) ) {
				throw new ClientException( "Category '$this->category_id' not found", ERROR_CATEGORY_NOT_FOUND, array( $this->category_id ) );
			}
		}
	}

	/**
	 * Retrieve and map the project's categories.
	 *
	 * @return array The category collection response.
	 */
	protected function process() {
		$t_rows = category_get_all_rows( $this->project_id );
		if( $this->category_id !== null ) {
			$t_rows = array_filter( $t_rows, function( $p_row ) {
				return (int)$p_row['id'] === $this->category_id;
			} );
		}

		return array( 'categories' => array_map( 'CategoryGetCommand::CategoryRowToArray', array_values( $t_rows ) ) );
	}

	/**
	 * Convert a database category row to the public API representation.
	 *
	 * @param array $p_row Category database row.
	 * @return array Category representation.
	 */
	public static function CategoryRowToArray( $p_row ) {
		$t_category = array(
			'id' => (int)$p_row['id'],
			'name' => $p_row['name'],
			'project' => array( 'id' => (int)$p_row['project_id'], 'name' => $p_row['project_name'] ),
			'enabled' => (int)$p_row['status'] === CATEGORY_STATUS_ENABLED,
		);
		$t_manage_project_threshold = config_get(
			'manage_project_threshold',
			null,
			null,
			$p_row['project_id']
		);
		if( (int)$p_row['user_id'] !== NO_USER && access_has_project_level( $t_manage_project_threshold, $p_row['project_id'] ) ) {
			$t_category['default_handler'] = mci_account_get_array_by_id( (int)$p_row['user_id'] );
		}

		return $t_category;
	}
}
