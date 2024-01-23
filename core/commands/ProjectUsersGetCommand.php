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

require_api( 'authentication_api.php' );
require_api( 'user_pref_api.php' );

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_account_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_enum_api.php' );

/**
 * Sample:
 * {
 *   "query": {
 *     'id'        => 1,
 *     'page'      => 1,
 *     'page_size' => 50,
 *     'access_level' => 1,
 *     'include_access_levels' => 1
 *   }
 * }
 */

/**
 * A command to get the users within a project with the specified access level
 * or higher.
 */
class ProjectUsersGetCommand extends Command {
	/**
	 * The project id
	 *
	 * @var integer
	 */
	private $project_id;

	/**
	 * The minimum access level, users with access level greater or equal to this access level
	 * will be returned.
	 *
	 * @var integer
	 */
	private $access_level;

	/**
	 * The page number (starts with 1)
	 *
	 * @var integer
	 */
	private $page;

	/**
	 * The number of users to return per page.
	 *
	 * @var integer
	 */
	private $page_size;

	/**
	 * Include effective access level of users on the project?
	 *
	 * @var integer
	 */
	private $include_access_levels;

	/**
	 * The authenticated user id
	 *
	 * @var integer
	 */
	private $user_id;

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
		$this->user_id = auth_get_current_user_id();
		$this->project_id = (int)$this->query( 'id' );
		$this->access_level = (int)$this->query( 'access_level' );
		$this->page = (int)$this->query( 'page', 1 );
		$this->page_size = (int)$this->query( 'page_size', 50 );
		$this->include_access_levels = (int)$this->query( 'include_access_levels', true );

		if( !project_exists( $this->project_id ) ) {
			throw new ClientException(
				sprintf( "Project '%d' not found", $this->project_id ),
				ERROR_PROJECT_NOT_FOUND,
				array( $this->project_id ) );
		}

		# If user doesn't have access to project, return project doesn't exist
		if( !access_has_project_level( VIEWER, $this->project_id, $this->user_id ) ) {
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
	 * @return array Command response
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
			$t_display[] = $t_user_name;
			$t_sort[] = user_get_name_for_sorting_from_row( $t_user );
		}

		array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );

		unset( $t_display );
		unset( $t_sort );

		$t_skip = ( $this->page - 1 ) * $this->page_size;
		$t_taken = 0;
		$t_users_result = array();

		$t_lang = mci_get_user_lang( auth_get_current_user_id() );

		for( $i = $t_skip; $i < count( $t_users ); $i++ ) {
			$t_user_id = (int)$t_users[$i]['id'];
			$t_user = mci_account_get_array_by_id( $t_user_id );

			if( $this->include_access_levels ) {
				$t_access_level = (int)$t_users[$i]['access_level'];
				$t_user['access_level'] =
					mci_enum_get_array_by_id( $t_access_level, 'access_levels', $t_lang );	
			}

			$t_users_result[] = $t_user;
			$t_taken++;

			if( $this->page_size != 0 && $t_taken == $this->page_size ) {
				break;
			}
		}

		$t_result = array( 'users' => $t_users_result );
		return $t_result;
	}
}
