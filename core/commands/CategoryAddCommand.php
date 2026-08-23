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
 * A command that adds a category to a project.
 */
class CategoryAddCommand extends Command {
	/** @var int */
	private $project_id;
	/** @var string */
	private $name;

	/**
	 * Validate the project and category name.
	 *
	 * @return void
	 * @throws ClientException If the project is unavailable, access is denied,
	 *                         or the category name is invalid.
	 */
	function validate() {
		$t_project_id = $this->query( 'project_id' );
		$this->project_id = $t_project_id == ALL_PROJECTS ? ALL_PROJECTS : helper_parse_id( $t_project_id, 'project_id' );

		if( $this->project_id != ALL_PROJECTS && !project_exists( $this->project_id ) ) {
			throw new ClientException( "Project '$this->project_id' not found", ERROR_PROJECT_NOT_FOUND, array( $this->project_id ) );
		}
		helper_set_current_project( $this->project_id );

		$t_manage_project_threshold = config_get( 'manage_project_threshold' );
		if( !access_has_project_level( $t_manage_project_threshold, $this->project_id ) ) {
			throw new ClientException( 'Access denied to add categories', ERROR_ACCESS_DENIED );
		}

		$this->name = trim( (string)$this->payload( 'name', '' ) );
		if( is_blank( $this->name ) ) {
			throw new ClientException( 'Category name can\'t be empty', ERROR_EMPTY_FIELD, array( 'name' ) );
		}

		if( !category_is_unique( $this->project_id, $this->name ) ) {
			throw new ClientException( 'Category name is not unique', ERROR_CATEGORY_DUPLICATE, array( 'name' ) );
		}

		category_validate_assigned_to( $this->payload( 'assigned_to', NO_USER ), $this->project_id, true );
	}

	/**
	 * Create the category and return its API representation.
	 *
	 * @return array The created category response.
	 */
	protected function process() {
		$t_id = category_add( $this->project_id, $this->name );
		category_cache_flush( $this->project_id );
		$t_assigned_to = (int)$this->payload( 'assigned_to', NO_USER );
		$t_status = $this->payload( 'enabled' ) === null
			? CATEGORY_STATUS_ENABLED
			: ( $this->payload( 'enabled' ) ? CATEGORY_STATUS_ENABLED : CATEGORY_STATUS_DISABLED );

		if( $t_assigned_to !== NO_USER || $t_status !== null ) {
			category_update( $t_id, $this->name, $t_assigned_to, $t_status === null ? CATEGORY_STATUS_ENABLED : (int)$t_status );
		}

		$t_result = new CategoryGetCommand( array( 'query' => array( 'project_id' => $this->project_id, 'category_id' => $t_id ) ) );
		return array( 'category' => $t_result->execute()['categories'][0] );
	}
}
