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

namespace Mantis\Notifications;

/**
 * Email for issue note added notification.
 */
class IssueNoteAddedEmail extends Email {
	private $note;

	/**
	 * Command constructor taking in all required data to execute the command.
	 *
	 * @param string $p_notification_type The notification type
	 * @param array $p_data The command data.
	 */
	function __construct( $p_notification, $p_target_user_id ) {
		parent::__construct( $p_notification, $p_target_user_id );

		$t_issue_note_id = $this->payload( 'issue_note_id' );
		$this->note = bugnote_get( $p_bugnote_id );

	}

	function generateSubject() {
		return email_build_subject( $this->note->bug_id );
	}

	function generateTextBody() {
		$t_user_id = $this->target_user_id;
		$t_bugnote = $this->note;

		$t_user_email = user_get_email( $t_user_id );
		if( is_blank( $t_user_email ) ) {
			# TODO: log that user skipped because email is empty
			return null;
		}

		$t_project_id = bug_get_field( $t_bugnote->bug_id, 'project_id' );
		$t_separator = config_get( 'email_separator2' );
		$t_time_tracking_access_threshold = config_get( 'time_tracking_view_threshold' );
		$t_view_attachments_threshold = config_get( 'view_attachments_threshold' );
		$t_message_id = 'email_notification_title_for_action_bugnote_submitted';
		$t_verbose = config_get( 'email_notifications_verbose', /* default */ null, $t_user_id, $t_project_id ) == ON;

		log_event( LOG_EMAIL_VERBOSE, 'Issue = #%d, Note = ~%d, Type = %s, Msg = \'%s\', User = @U%d, Email = \'%s\'.',
			$t_bugnote->bug_id, $t_bugnote->id, 'bugnote', $t_message_id, $t_user_id, $t_user_email );

		$t_message = lang_get( 'email_notification_title_for_action_bugnote_submitted' ) . "\n\n";

		$t_show_time_tracking = access_has_bug_level( $t_time_tracking_access_threshold, $t_bugnote->bug_id, $t_user_id );
		$t_formatted_note = email_format_bugnote( $t_bugnote, $t_project_id, $t_show_time_tracking, $t_separator );
		$t_message .= trim( $t_formatted_note ) . "\n";
		$t_message .= $t_separator . "\n";

		# Files attached
		$t_files = $this->notification->payload( 'files', array() );
		if( count( $t_files ) > 0 &&
			access_has_bug_level( $t_view_attachments_threshold, $t_bugnote->bug_id, $t_user_id ) ) {
			$t_message .= lang_get( 'bugnote_attached_files' ) . "\n";

			foreach( $t_files as $t_file ) {
				$t_message .= '- ' . $t_file['name'] . ' (' . number_format( $t_file['size'] ) .
					' ' . lang_get( 'bytes' ) . ")\n";
			}

			$t_message .= $t_separator . "\n";
		}

		$t_contents = $t_message . "\n";

		log_event( LOG_EMAIL_VERBOSE, 'queued bugnote email for note ~' . $p_bugnote_id .
			' issue #' . $t_bugnote->bug_id . ' by U' . $t_user_id );

		return $t_contents;
	}

	protected function generateHtmlEmail() {
		return null;
	}
}
