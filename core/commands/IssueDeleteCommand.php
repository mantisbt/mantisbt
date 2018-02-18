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

require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that deletes an issue.
 */
class IssueDeleteCommand extends Command {
    /**
     * @var integer issue note id to delete
     */
    private $id;

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
		$this->id = $this->query( 'id' );

		if( (int)$this->id < 1 ) {
			throw new ClientException( "'id' must be >= 1", ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
		}

		if( !bug_exists( $this->id ) ) {
			throw new ClientException(
				"Issue '" . $this->id . "' does not exist.",
				ERROR_BUG_NOT_FOUND,
				$this->id
			);
		}

		global $g_project_override;
		$g_project_override = bug_get_field( $this->id, 'project_id' );

		if( !access_has_bug_level( config_get( 'delete_bug_threshold' ), $this->id ) ) {
			throw new ClientException(
				sprintf( 'Access denied to delete issue %d', $this->id ),
				ERROR_ACCESS_DENIED
			);
		}
	}

 	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		log_event( LOG_WEBSERVICE, "deleting issue '" . $this->id . "'" );
		bug_delete( $this->id );
	}
}

