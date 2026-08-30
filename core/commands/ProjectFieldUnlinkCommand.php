<?php
# MantisBT - A PHP based bugtracking system
#
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

use Mantis\Exceptions\ClientException;

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'helper_api.php' );

/**
 * Unlinks a custom field from a project.
 */
class ProjectFieldUnlinkCommand extends Command {
	/** @var int */
	private $project_id;

	/** @var int */
	private $field_id;

	/**
	 * Validate the data.
	 */
	function validate() {
		$this->project_id = helper_parse_id( $this->query( 'project_id' ), 'project_id' );
		$this->field_id = helper_parse_id( $this->query( 'field_id' ), 'field_id' );

		$t_user_id = auth_get_current_user_id();
		$t_manage_project_threshold = config_get( 'manage_project_threshold', null, $t_user_id, $this->project_id );
		$t_custom_field_link_threshold = config_get( 'custom_field_link_threshold', null, $t_user_id, $this->project_id );
		if( !access_has_project_level( $t_manage_project_threshold, $this->project_id, $t_user_id ) ||
			!access_has_project_level( $t_custom_field_link_threshold, $this->project_id, $t_user_id ) ) {
			throw new ClientException( 'Access denied to unlink custom field', ERROR_ACCESS_DENIED );
		}

		project_ensure_exists( $this->project_id );
		custom_field_ensure_exists( $this->field_id );
	}

	/**
	 * Process the command.
	 *
	 * @return array
	 */
	function process() {
		custom_field_unlink( $this->field_id, $this->project_id );
		return [];
	}
}
