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

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that deletes an issue note.
 */
class IssueNoteDeleteCommand extends Command {
    /**
     * @var integer issue note id to delete
     */
    private $id;

    /**
     * @var integer issue id
     */
    private $issueId;

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

        if( (integer)$this->id < 1 ) {
            throw new ClientException( "'id' must be >= 1", ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
        }

    }

 	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
        bugnote_delete( $this->id );
        return array(
            'id' => $this->id,
            'issue_id' => $this->issueId
        );
	}
}

