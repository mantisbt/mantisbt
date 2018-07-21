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

        bugnote_ensure_exists( $this->id );

        $this->issueId = bugnote_get_field( $this->id, 'bug_id' );
        $t_specified_issue_id = $this->query( 'issue_id' );
        if( $t_specified_issue_id !== null && $t_specified_issue_id != $this->issueId ) {
            throw new ClientException( "Issue note doesn't belong to issue", ERROR_INVALID_FIELD_VALUE, array( 'id' ) );
        }

        $t_project_id = bug_get_field( $this->issueId, 'project_id' );
		if( $t_project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
            global $g_project_override;
            $g_project_override = $t_project_id;
        }

        $t_reporter_id = bugnote_get_field( $this->id, 'reporter_id' );
        $t_user_id = auth_get_current_user_id();

        # mirrors check from bugnote_delete.php
        if( $t_user_id == $t_reporter_id ) {
            $t_threshold_config_name =  'bugnote_user_delete_threshold';
        } else {
            $t_threshold_config_name =  'delete_bugnote_threshold';
        }

        if( !access_has_bugnote_level( config_get( $t_threshold_config_name ), $this->id ) ) {
            throw new ClientException( 'Access denied', ERROR_ACCESS_DENIED );
        }

        if( bug_is_readonly( $this->issueId ) ) {
			throw new ClientException(
				sprintf( "Issue '%d' is read-only.", $this->issueId ),
				ERROR_BUG_READ_ONLY_ACTION_DENIED,
				array( $this->issueId ) );
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

