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

require_api( 'email_queue_api.php' );
require_once( __DIR__ . '/EmailMessage.class.php' );

/**
 * The base class for email sender implementations.
 *
 * Note that the same instance will be used to send multiple emails
 * within the same request.
 */
abstract class EmailSender {
	/**
	 * Send an email
	 *
	 * @param EmailMessage $p_email_data The email to send
	 * @return bool true if the email was sent successfully, false otherwise
	 */
	abstract public function send( EmailMessage $p_message ) : bool;
}
