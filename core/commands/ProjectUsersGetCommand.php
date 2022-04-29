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

use Mantis\Exceptions\ClientException;

/**
 * A command to get the users within a project with the specified access level.
 */
class ProjectUsersGetCommand extends Command {
	/**
	 * The project id
	 */
	private $project_id;

	/**
	 * The minimum access level, users with access level greater or equal to this access level
	 * will be returned.
	 */
	private $access_level;

	/**
	 * The page number (starts with 1)
	 */
	private $page;

	/**
	 * The number of users to return per page.
	 */
	private $page_size;

	/**
	 * Constructor
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
		$this->project_id = $this->query( 'id' );
		$this->access_level = $this->option( 'access_level' );
		$this->page = $this->query( 'page', 1 );
		$this->page_size = $this->query( 'page_size', 50 );

		if( $this->project_id <= ALL_PROJECTS ) {
			throw new ClientException(
				sprintf( "Must specify a specific project id.", $this->project_id ),
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}

		if( !project_exists( $this->project_id ) ) {
			throw new ClientException(
				sprintf( "Project '%d' not found", $this->project_id ),
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}

		if( $this->page < 1 ) {
			$this->page = 1;
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		global $g_project_override;
		$g_project_override = $this->project_id;

		$t_users = project_get_all_user_rows(
			$this->project_id,
			$this->access_level,
			/* include_global_users */ true );

		$t_display = array();
		$t_sort = array();

		foreach( $t_users as $t_user ) {
			$t_user_name = user_get_name_from_row( $t_user );
			$t_display[] = string_attribute( $t_user_name );
			$t_sort[] = user_get_name_for_sorting_from_row( $t_user );
		}

		array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );

		unset( $t_display );
		unset( $t_sort );

		$t_skip = ( $this->page - 1 ) * $this->page_size;
		$t_taken = 0;
		$t_result = array();
		$t_user_ids = array();

		for( $i = $t_skip; $i < count( $t_users ); $i++ ) {
			$t_user_ids[] = (int)$t_users[$i]['id'];
			$t_taken++;

			if( $this->page_size != 0 && $t_taken == $this->page_size ) {
				break;
			}
		}

		$t_result = mci_account_get_array_by_ids( $t_user_ids );
		return $t_result;
	}
}
