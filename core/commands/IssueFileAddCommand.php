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
 * Command data
 * - In files, `error` and `size` are used only by web UI.
 * - Reporter is optional, it is defaulted to logged in user.
 *
 * Sample:
 * {
 *   "query": {
 *     "issue_id": 12345
 *   },
 *   "payload": {
 *     "reporter": {
 *       "name": "vboctor"
 *     },
 *     "files": [
 *       {
 *         "name": "filename.ext",
 *         "type": "application/...",
 *         "tmp_name": "/tmp/php/phpRELws8",
 *         "error": 0,
 *         "size": 114
 *       }
 *     ]
 *   } 
 * }
 */

/**
 * A command that adds an issue attachment.
 */
class IssueFileAddCommand extends Command {
	/**
	 * The issue to add the note to.
	 *
	 * @var BugData
	 */
	private $issue = null;

	/**
	 * @var integer
	 */
	private $user_id;

	/**
	 * The files to attach with the note.
	 */
	private $files = array();

	/**
	 * The reporter id for the note.
	 */
	private $reporterId = 0;

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
		$t_issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );

		$this->issue = bug_get( $t_issue_id, true );
		if( bug_is_readonly( $t_issue_id ) ) {
			throw new ClientException(
				sprintf( "Issue '%d' is read-only.", $t_issue_id ),
				ERROR_BUG_READ_ONLY_ACTION_DENIED,
				array( $t_issue_id ) );
		}

		$this->parseFiles();

		$t_files_included = !empty( $this->files );

		if( !$t_files_included ) {
			throw new ClientException(
				'Files not provided',
				ERROR_INVALID_FIELD_VALUE,
				array( 'files' ) );
		}

		$this->user_id = auth_get_current_user_id();

		# Parse reporter id or default it.
		$t_reporter = $this->payload( 'reporter' );
		if( $t_reporter !== null ) {
			$this->reporterId = user_get_id_by_user_info(
				$t_reporter, /* throw_if_id_doesnt_exist */ true );
		} else {
			$this->reporterId = $this->user_id;
		}

		if( $this->reporterId != $this->user_id ) {
			# Make sure that active user has access level required to specify a different reporter.
			# This feature is only available in the API and not Web UI.
			$t_specify_reporter_access_level = config_get( 'webservice_specify_reporter_on_add_access_level_threshold' );
			if( !access_has_bug_level( $t_specify_reporter_access_level, $t_issue_id ) ) {
				throw new ClientException( 'Access denied to override reporter', ERROR_ACCESS_DENIED );
			}
		}

		# Can reporter attach files
		if( !file_allow_bug_upload( $this->issue->id, $this->reporterId ) ) {
			throw new ClientException( 'access denied for uploading files', ERROR_ACCESS_DENIED );
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		if( $this->issue->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			global $g_project_override;
			$g_project_override = $this->issue->project_id;
		}

		# Handle the file upload
		file_attach_files( $this->issue->id, $this->files );

		return array();
	}

	/**
	 * Parse files from payload.
	 * @return void
	 */
	private function parseFiles() {
		$this->files = $this->payload( 'files', array() );
		if( !is_array( $this->files ) ) {
			$this->files = array();
		}

		$t_files_required_fields = array( 'name', 'tmp_name' );
		foreach( $this->files as $t_file ) {
			foreach( $t_files_required_fields as $t_field ) {
				if( !isset( $t_file[$t_field] ) ) {
					throw new ClientException(
						sprintf( "File field '%s' is missing.", $t_field ),
						ERROR_EMPTY_FIELD,
						array( $t_field ) );
				}
			}
		}
	}
}

