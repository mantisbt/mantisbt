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

	public function __construct( $p_timestamp, $p_user_id, $p_tie_breaker ) {
		$this->timestamp = $p_timestamp;
		$this->user_id = $p_user_id;
		$this->tie_breaker = $p_tie_breaker;
	}

	public function skip() {
		return false;
	}

	public function compare( $p_other ) {
		if ( $this->timestamp < $p_other->timestamp ) {
			return -1;
		}

		if ( $this->timestamp > $p_other->timestamp ) {
			return 1;
		}

		if ( $this->tie_breaker < $p_other->tie_breaker ) {
			return -1;
		}

		if ( $this->tie_breaker > $p_other->tie_breaker ) {
			return 1;
		}

		return 0;
	}

	public function html() {
	}

	public function format_timestamp( $p_timestamp ) {
		$t_normal_date_format = config_get( 'normal_date_format' );
		return date( $t_normal_date_format, $p_timestamp );
	}

	public function html_start() {
		$t_avatar = user_get_avatar( $this->user_id, 32 );

		if ( $t_avatar !== false ) {
			$t_html = '<div class="entry">';
		} else {
			$t_html = '<div class="entry-no-avatar">';
		}

		if ( $t_avatar !== false ) {
			$t_avatar_url = $t_avatar[0];
			$t_html .= '<img class="avatar" src="' . $t_avatar_url . '"/>';
		} else {
			$t_html .= '<img class="avatar" />';
		}

		$t_html .= '<div class="timestamp">' .  $this->format_timestamp( $this->timestamp ) . '</div>';

		return $t_html;
	}

	public function html_end() {
		return '</div>';
	}
}