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
abstract class Notification
{
	protected $type;

	/**
	 * This is the data for the notification.
	 * @var array The input data for the command.
	 */
	protected $data;

	/**
	 * Command constructor taking in all required data to execute the command.
	 *
	 * @param array $p_data The command data.
	 * @param string $p_type The notification type
	 */
	function __construct( array $p_data, $p_type ) {
		$this->data = $p_data;
		$this->type = $p_type;
	}

	public function getType() {
		return $this->type;
	}

	public function getData() {
		return $this->data;
	}

	/**
	 * Gets the value of a payload field or its default.
	 *
	 * @param string $p_name The field name.
	 * @param mixed  $p_default The default value.
	 *
	 * @return mixed The payload field value or its default.
	 */
	public function payload( $p_name, $p_default = null ) {
		if( isset( $this->data[$p_name] ) ) {
			return $this->data[$p_name];
		}

		return $p_default;
	}
}
