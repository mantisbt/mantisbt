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
 * A command that adds a category to a project.
 */
class CategoryAddCommand extends Command {
	/** @var int */
	private $project_id;
	/** @var string */
	private $name;
	/** @var int */
	private $handler_id;

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
			throw new ClientException( "Project '$this->project_id' not found", ERROR_PROJECT_NOT_FOUND, [ $this->project_id ] );
		}
		helper_set_current_project( $this->project_id );

		$t_manage_project_threshold = config_get( 'manage_project_threshold' );
		if( !access_has_project_level( $t_manage_project_threshold, $this->project_id ) ) {
			throw new ClientException( 'Access denied to add categories', ERROR_ACCESS_DENIED );
		}

		$this->name = trim( (string)$this->payload( 'name', '' ) );
		if( is_blank( $this->name ) ) {
			throw new ClientException( 'Category name can\'t be empty', ERROR_EMPTY_FIELD, [ 'name' ] );
		}

		if( !category_is_unique( $this->project_id, $this->name ) ) {
			throw new ClientException( 'Category name is not unique', ERROR_CATEGORY_DUPLICATE, [ 'name' ] );
		}

		$t_handler = $this->payload( 'handler' );
		$this->handler_id = $t_handler === null ? NO_USER : mci_get_user_id( $t_handler, null );
		category_validate_assigned_to( $this->handler_id, $this->project_id );
	}

	/**
	 * Create the category and return its API representation.
	 *
	 * @return array The created category response.
	 */
	protected function process() {
		$t_id = category_add( $this->project_id, $this->name );
		category_cache_flush( $this->project_id );
		$t_enabled = (bool)$this->payload( 'enabled', true );

		if( $this->handler_id !== NO_USER || !$t_enabled ) {
			category_update( $t_id, $this->name, $this->handler_id, category_enabled_to_status( $t_enabled ) );
		}

		$t_data = [ 'query' => [ 'project_id' => $this->project_id, 'category_id' => $t_id ] ];
		$t_command = new CategoryGetCommand( $t_data );
		$t_result = $t_command->execute();

		return [ 'category' => $t_result['categories'][0] ];
	}
}
