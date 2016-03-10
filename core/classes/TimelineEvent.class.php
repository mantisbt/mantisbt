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

/**
 * Mantis Column Handling
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Base class for timeline events.
 *
 * @package MantisBT
 * @subpackage classes
 */
class TimelineEvent {
	protected $timestamp;
	protected $user_id;

	/**
	 * @param integer $p_timestamp   Timestamp representing the time the event occurred.
	 * @param integer $p_user_id     An user identifier.
	 */
	public function __construct( $p_timestamp, $p_user_id ) {
		$this->timestamp = $p_timestamp;
		$this->user_id = $p_user_id;
	}

	/**
	 * Whether to skip this timeline event.
	 * This normally implements access checks for the event.
	 * @return boolean
	 */
	public function skip() {
		return false;
	}

	/**
	 * Returns html string to display
	 * @return string
	 */
	public function html() {
		return '';
	}

	/**
	 * Formats a timestamp in the timeline for display.
	 * @param integer $p_timestamp Integer representing timestamp to format.
	 * @return string
	 */
	public function format_timestamp( $p_timestamp ) {
		$t_normal_date_format = config_get( 'normal_date_format' );
		return date( $t_normal_date_format, $p_timestamp );
	}

	/**
	 * Returns html string representing the beginning block of a timeline entry
	 * @return string
	 */
	public function html_start() {
		$t_avatar = Avatar::get( $this->user_id, 32 );
		if( $t_avatar === null ) {
			return sprintf(
				'<div class="entry"><div class="timestamp">%s</div>', $this->format_timestamp( $this->timestamp ) );
		}

		return sprintf(
			'<div class="entry"><div class="avatar"><a href="%s"><img class="avatar" src="%s" alt="%s" width="32" height="32" /></a></div><div class="timestamp">%s</div>',
			$t_avatar->link,
			$t_avatar->image,
			$t_avatar->text,
			$this->format_timestamp( $this->timestamp )
		);
	}

	/**
	 * Returns html string representing the ending block of a timeline entry
	 * @return string
	 */
	public function html_end() {
		return '</div>';
	}
}
