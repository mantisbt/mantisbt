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
	 * @param integer $p_user_id     A user identifier.
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
	 * @param string $p_action_icon Icon name for Font Awesome
	 * @return string
	 */
	public function html_start( $p_action_icon = 'fa-check' ) {
		$t_avatar = Avatar::get( $this->user_id, 40 );
		$t_html = '<div class="profile-activity clearfix">';

		if( !empty( $t_avatar ) ) {
			$t_html .= prepare_avatar( $t_avatar, 'profile-activity', 40 );
		} else {
			$t_html .= icon_get( $p_action_icon, 'pull-left thumbicon btn-primary no-hover' );
		}
		return $t_html;
	}

	/**
	 * Returns html string representing the ending block of a timeline entry
	 * @return string
	 */
	public function html_end() {
		$t_html = '<div class="time">'
			. icon_get( 'fa-clock-o', 'ace-icon bigger-110' )
			. ' ' . $this->format_timestamp( $this->timestamp )
			. '</div>';
		$t_html .= '</div>';
		return $t_html;
	}
}
