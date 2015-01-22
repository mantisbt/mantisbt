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
	protected $tie_breaker;

	/**
	 * @param integer $p_timestamp   Timestamp representing the time the event occurred.
	 * @param integer $p_user_id     An user identifier.
	 * @param boolean $p_tie_breaker A value to sort events by if timestamp matches (generally issue identifier).
	 */
	public function __construct( $p_timestamp, $p_user_id, $p_tie_breaker ) {
		$this->timestamp = $p_timestamp;
		$this->user_id = $p_user_id;
		$this->tie_breaker = $p_tie_breaker;
	}

	/**
	 * Comparision function for ordering of timeline events.
	 * We compare first by timestamp, then by the tie_breaker field.
	 * @param TimelineEvent $p_other An instance of TimelineEvent to compare against.
	 * @return integer
	 */
	public function compare( TimelineEvent $p_other ) {
		if( $this->timestamp < $p_other->timestamp ) {
			return -1;
		}

		if( $this->timestamp > $p_other->timestamp ) {
			return 1;
		}

		if( $this->tie_breaker < $p_other->tie_breaker ) {
			return -1;
		}

		if( $this->tie_breaker > $p_other->tie_breaker ) {
			return 1;
		}

		return 0;
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
		$t_avatar = user_get_avatar( $this->user_id, 32 );

		# Avatar div
		if( !empty( $t_avatar ) ) {
			$t_class = 'avatar';
			$t_src = $t_avatar[0];
		}
		else {
			$t_class = 'no-avatar';
			$t_src = 'images/notice.gif';
		}

		return sprintf(
			'<div class="entry"><div class="%s"><img class="%s" src="%s" /></div><div class="timestamp">%s</div>',
			$t_class,
			$t_class,
			$t_src,
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
