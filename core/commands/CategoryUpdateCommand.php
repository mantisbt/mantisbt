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
 * A command that updates a project category.
 */
class CategoryUpdateCommand extends Command {
	/** @var int */
	private $project_id;
	/** @var int */
	private $category_id;
	/** @var array */
	private $old_category;
	/** @var int */
	private $handler_id;

	/**
	 * Validate the project, category, and update authorization.
	 *
	 * @return void
	 * @throws ClientException If the project or category is unavailable, or
	 *                         access is denied.
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
			throw new ClientException( 'Access denied to update categories', ERROR_ACCESS_DENIED );
		}

		$this->category_id = helper_parse_id( $this->query( 'category_id' ), 'category_id' );
		if( !category_exists( $this->category_id ) || (int)category_get_field( $this->category_id, 'project_id' ) !== $this->project_id ) {
			throw new ClientException( "Category '$this->category_id' not found", ERROR_CATEGORY_NOT_FOUND, [ $this->category_id ] );
		}

		$this->old_category = category_get_row( $this->category_id );
		$t_name = trim( (string)$this->payload( 'name', $this->old_category['name'] ) );
		if( is_blank( $t_name ) ) {
			throw new ClientException( 'Category name can\'t be empty', ERROR_EMPTY_FIELD, [ 'name' ] );
		}

		if( strcasecmp( $t_name, $this->old_category['name'] ) !== 0 && !category_is_unique( $this->project_id, $t_name ) ) {
			throw new ClientException( 'Category name is not unique', ERROR_CATEGORY_DUPLICATE, [ 'name' ] );
		}

		if( array_key_exists( 'handler', $this->data['payload'] ) ) {
			$t_handler = $this->payload( 'handler' );
			$this->handler_id = $t_handler === null ? NO_USER : mci_get_user_id( $t_handler, null );
			if( (int)$this->handler_id !== (int)$this->old_category['user_id'] ) {
				category_validate_assigned_to( $this->handler_id, $this->project_id );
			}
		} else {
			$this->handler_id = (int)$this->old_category['user_id'];
		}
	}

	/**
	 * Apply the category update and return its API representation.
	 *
	 * @return array The updated category response.
	 */
	protected function process() {
		$t_name = trim( (string)$this->payload( 'name', $this->old_category['name'] ) );
		$t_enabled = $this->payload( 'enabled' );
		if( $t_enabled === null ) {
			$t_enabled = category_is_enabled( $this->category_id );
		}

		$t_status = category_enabled_to_status( $t_enabled );
		category_update( $this->category_id, $t_name, $this->handler_id, $t_status );

		category_cache_flush( $this->project_id );

		$t_data = [ 'query' => [ 'project_id' => $this->project_id, 'category_id' => $this->category_id ] ];
		$t_command = new CategoryGetCommand( $t_data );
		$t_result = $t_command->execute();
		return [ 'category' => $t_result['categories'][0] ];
	}
}
