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
 * A base class for notifications.
 */
class IssueNoteAddedNotification extends Notification {
	/**
	 * Command constructor taking in all required data to execute the command.
	 *
	 * @param string $p_notification_type The notification type
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data, NOTIFICATION_TYPE_ISSUE_NOTE_ADDED );
	}
}
