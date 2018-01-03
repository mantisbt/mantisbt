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

/** SAMPLE
{
  "query": {
    "issue_id": 12345
  },
  "payload": {
	"text": "This is a test issue note",
	"view_state": {
		"name": "private"
	},
	"reporter": {
		"name": "vboctor"
	},
	"time_tracking": {
		"duration": "00:45",
	},
	"files": [
		{
          "name": "filename.ext",
          "type": "application/...",
          "tmp_name": "/tmp/php/phpRELws8",
          "error": 0,
		  "size": 114
		}
	]
  }
}
*/

/**
 * A command that adds user to monitor an issue.
 */
class IssueNoteAddCommand extends Command {
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
	 * Time tracking duration associated with the note.
	 *
	 * @var string
	 */
	private $time_tracking = '0:00';

	/**
	 * The files to attach with the note.
	 */
	private $files = array();

	/**
	 * Private note?
	 *
	 * @var boolean
	 */
	private $private = false;

	/**
	 * The note text to add.
	 */
	private $text = '';

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
		# Validate issue note type
		switch( $this->payload( 'type', 'note' ) ) {
			case 'note':
			case 'timelog':
				break;
			case 'reminder':  # isn't supported by this command.
			default:
				throw new ClientException(
					sprintf( "Invalid value '%s' for 'type'.", $t_type ),
					ERROR_INVALID_FIELD_VALUE,
					array( 'type' )
				);
		}

		$t_issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );

		$this->issue = bug_get( $t_issue_id, true );
		if( bug_is_readonly( $t_issue_id ) ) {
			throw new ClientException(
				sprintf( 'Issue %d is read-only.', $t_issue_id ),
				ERROR_BUG_READ_ONLY_ACTION_DENIED,
				array( $t_issue_id ) );
		}

		if( !access_has_bug_level( config_get( 'add_bugnote_threshold' ), $t_issue_id ) ) {
			throw new ClientException( 'access denied', ERROR_ACCESS_DENIED );
		}

		$this->parseViewState();

		if( $this->private ) {
			if( !access_has_bug_level( config_get( 'set_view_status_threshold' ), $t_issue_id ) ) {
				throw new ClientException( 'access denied', ERROR_ACCESS_DENIED );
			}
		}

		if( $this->option( 'allowLocalPathForFiles', false ) ) {
			$this->files = $this->payload( 'files' );
			if( !is_array( $this->files ) ) {
				$this->files = array();
			}
	
			# The UI hides the attach controls when the note is marked as private to avoid disclosure of
			# attachments.  Attaching files to private notes can be re-enabled as proper support for protecting
			# private attachments is implemented.
			if( $this->private && count( $this->files ) > 0 ) {
				# TODO: trigger exception, private attachments not supported.
				$this->files = array();
			}
		}

		if( count( $this->files ) > 0 ) {
			if( !file_allow_bug_upload( $this->issue->id ) ) {
				throw new ClientException( 'access denied for uploading files', ERROR_ACCESS_DENIED );
			}
		}

		$t_time_tracking = $this->payload( 'time_tracking' );
		if( is_array( $t_time_tracking ) && isset( $t_time_tracking['duration'] ) ) {
			$this->time_tracking = $t_time_tracking['duration'];
		}

		# Parse to duration to validate it.
		$t_time_tracking_mins = helper_duration_to_minutes( $this->time_tracking, 'time tracking duration' );

		$this->text = trim( $this->payload( 'text', '' ) );
		if( empty( $this->text ) &&
		    $t_time_tracking_mins == 0 && 
		    count( $this->files ) == 0 ) {
			throw new ClientException( 'Issue note not specified.', ERROR_EMPTY_FIELD, array( lang_get( 'bugnote' ) ) );
		}

		$this->user_id = auth_get_current_user_id();

		# Parse reporter id or default it.
		$t_reporter = $this->payload( 'reporter' );
		if( $t_reporter !== null ) {
			$t_reporter_id = user_get_id_by_user_info( $t_reporter );
		} else {
			$t_reporter_id = $this->user_id;
		}

		if( $t_reporter_id != $this->user_id ) {
			# Make sure that active user has access level required to specify a different reporter.
			# This feature is only available in the API and not Web UI.
			$t_specify_reporter_access_level = config_get( 'webservice_specify_reporter_on_add_access_level_threshold' );
			if( !access_has_bug_level( $t_specify_reporter_access_level, $t_issue_id ) ) {
				throw new ClientException( 'Access denied to override reporter', ERROR_ACCESS_DENIED );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns integer The id of the added note.
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
		if( count( $this->files > 0 ) ) {
			$t_file_infos = file_attach_files( $this->issue->id, $this->files );
		} else {
			$t_file_infos = array();
		}

		# We always set the note time to BUGNOTE, and the API will overwrite it with TIME_TRACKING
		# if time tracking is not 0 and the time tracking feature is enabled.
		$t_note_id = bugnote_add(
			$this->issue->id,
			$this->text,
			$this->time_tracking,
			$this->private,
			BUGNOTE,
			/* attr */ '',
			/* user_id */ null,
			/* send_email */ false );
		if( !$t_note_id ) {
			throw new ClientException( "Unable to add note", ERROR_GENERIC );
		}

		# Process the mentions in the added note
		$t_user_ids_that_got_mention_notifications = bugnote_process_mentions( $this->issue->id, $t_note_id, $this->payload( 'text' ) );

		# Send email explicitly from here to have file support, this will move into the API once we have
		# proper bugnote files support in db schema and object model.
		email_bugnote_add( $t_note_id, $t_file_infos, /* user_exclude_ids */ $t_user_ids_that_got_mention_notifications );

		# Handle the reassign on feedback feature. Note that this feature generally
		# won't work very well with custom workflows as it makes a lot of assumptions
		# that may not be true. It assumes you don't have any statuses in the workflow
		# between 'bug_submit_status' and 'bug_feedback_status'. It assumes you only
		# have one feedback, assigned and submitted status.
		if( config_get( 'reassign_on_feedback' ) &&
			$this->issue->status === config_get( 'bug_feedback_status' ) &&
			$this->issue->handler_id !== $this->user_id &&
			$this->issue->reporter_id === $this->user_id ) {
			if( $this->issue->handler_id !== NO_USER ) {
				bug_set_field( $this->issue->id, 'status', config_get( 'bug_assigned_status' ) );
			} else {
				bug_set_field( $this->issue->id, 'status', config_get( 'bug_submit_status' ) );
			}
		}

		return array( 'id' => $t_note_id );
	}

	/**
	 * Parse view state for note.
	 */
	private function parseViewState() {
		$t_view_state = $this->payload( 'view_state' );
		if( $t_view_state === null ) {
			$t_view_state = config_get( 'default_bugnote_view_status' );
		} else {
			$t_view_state = helper_parse_view_state( $t_view_state );
		}

		$this->private = $t_view_state == VS_PRIVATE;
	}
}

