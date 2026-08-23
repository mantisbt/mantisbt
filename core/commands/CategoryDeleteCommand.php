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

use Mantis\Exceptions\ClientException;

/**
 * A command that deletes a project category.
 */
class CategoryDeleteCommand extends Command {
	/** @var int */
	private $project_id;
	/** @var int */
	private $category_id;

	/**
	 * Validate the project, category, permissions, and deletion constraints.
	 *
	 * @return void
	 * @throws ClientException If the project or category is unavailable, access
	 *                         is denied, or the category cannot be deleted.
	 */
	public function validate() {
		$this->project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );
		if( !project_exists( $this->project_id ) ) {
			throw new ClientException( "Project '$this->project_id' not found", ERROR_PROJECT_NOT_FOUND, array( $this->project_id ) );
		}
		helper_set_current_project( $this->project_id );

		$t_manage_project_threshold = config_get( 'manage_project_threshold' );
		if( !access_has_project_level( $t_manage_project_threshold, $this->project_id ) ) {
			throw new ClientException( 'Access denied to delete categories', ERROR_ACCESS_DENIED );
		}

		$this->category_id = $this->query( 'category_id' );
		if( $this->category_id === null ) {
			$this->category_id = category_get_id_by_name( $this->query( 'category_name' ), $this->project_id );
		}

		$this->category_id = helper_parse_id( $this->category_id, 'category_id' );
		if( !category_exists( $this->category_id ) || (int)category_get_field( $this->category_id, 'project_id' ) !== $this->project_id ) {
			throw new ClientException( "Category '$this->category_id' not found", ERROR_CATEGORY_NOT_FOUND, array( $this->category_id ) );
		}

		category_ensure_can_remove( $this->category_id );
		if( !$this->option( 'allow_reassign', false ) ) {
			category_ensure_can_delete( $this->category_id );
		}
	}

	/**
	 * Delete the category, optionally reassigning linked issues.
	 *
	 * @return array An empty response.
	 */
	public function process() {
		category_remove( $this->category_id, $this->option( 'new_category_id', 0 ) );
		category_cache_flush( $this->project_id );
		return array();
	}
}
