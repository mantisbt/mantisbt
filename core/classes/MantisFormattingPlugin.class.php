<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Abstract class for any plugin that's modifying textual output.
 * @package MantisBT
 * @subpackage classes
 */
abstract class MantisFormattingPlugin extends MantisPlugin {

	/**
	 * Event hook declaration.
	 */
	function hooks() {
		return array(
			'EVENT_DISPLAY_TEXT'		=> 'text',			# Text String Display
			'EVENT_DISPLAY_FORMATTED'	=> 'formatted',		# Formatted String Display
			'EVENT_DISPLAY_RSS'			=> 'rss',			# RSS String Display
			'EVENT_DISPLAY_EMAIL'		=> 'email',			# Email String Display
		);
	}

	/**
	 * Plain text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @param boolean Multiline text
	 * @return multi Array with formatted text and multiline paramater
	 */
	function text( $p_event, $p_string, $p_multiline = true ) {
		return $p_string;
	}

	/**
	 * Formatted text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @param boolean Multiline text
	 * @return multi Array with formatted text and multiline paramater
	 */
	function formatted( $p_event, $p_string, $p_multiline = true ) {
		return $p_string;
	}

	/**
	 * RSS text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @return string Formatted text
	 */
	function rss( $p_event, $p_string ) {
		return $p_string;
	}

	/**
	 * Email text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @return string Formatted text
	 */
	function email( $p_event, $p_string ) {
		return $p_string;
	}
}
