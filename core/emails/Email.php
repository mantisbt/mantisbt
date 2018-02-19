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

namespace Mantis\Emails;

/**
 * A base class for for email notifications.  An Email instance corresponds to a
 * single email.  Hence, a single notification may trigger creation of N Email instances
 * and hence N emails.
 */
abstract class Email {
	protected $notification;
	protected $target_user_id;

	/**
	 * Command constructor taking in all required data to execute the command.
	 *
	 * @param array $p_notification The notification that triggered the email.
	 * @param int $p_target_user_id The target user id for the notification.
	 */
	function __construct( $p_notification, $p_target_user_id ) {
		$this->notification = $p_notification;
		$this->target_user_id = $p_target_user_id;
	}

	/**
	 * @return array Array with key as header names and values as header values
	 */
	protected function generatedHeaders() {
		return array();
	}

	/**
	 * @returns string subject for email.
	 */
	abstract protected function generateSubject();

	/**
	 * @returns string The text version of the email or null.
	 */
	abstract protected function generateTextBody();

	/**
	 * @returns string The html version of the email or null.
	 */
	abstract protected function generateHtmlBody();

	public function getEmailData() {
		if( OFF == config_get( 'enable_email_notification' ) ) {
			return null;
		}

		$t_email = user_get_email( $this->target_user_id );
		if( is_blank( $t_email ) ) {
			return null;
		}

		$t_headers = $this->generateHeaders();
		if( !is_array( $t_headers ) ) {
			$t_headers = array();
		}

		$t_metadata = array(
			'headers' => $t_headers,
			'charset' => 'utf-8',
		);

		lang_push( user_pref_get_language( $this->target_user_id ) );

		$t_email_data = new EmailData;
		$t_email_data->email = $t_email;
		$t_email_data->subject = trim( $this->generateSubject() );
		$t_email_data->body = trim( $this->generateTextBody() );
		$t_email_data->metadata = $t_metadata;

		lang_pop();

		return $t_email_data;
	}
}
