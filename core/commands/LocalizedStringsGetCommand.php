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
 * A command that gets a set of localized strings in user's language.
 *
 * This command can retrieve one or more localized strings that are
 * specified via query parameter. If a requested localized string
 * doesn't exist, it will be silently ignored.
 * 
 * The string query parameter can be a string or array of strings.
 */
class LocalizedStringsGetCommand extends Command {
	/**
	 * Constructor
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_strings = $this->query( 'string' );
		if( !is_array( $t_strings ) ) {
			$t_strings = array( $t_strings );
		}

		$t_current_language = lang_get_current();
		$t_localized_strings = array();

		foreach( $t_strings as $t_string ) {
			if( !lang_exists( $t_string, $t_current_language ) ) {
				continue;
			}

			$t_localized_strings[] = array( 'name' => $t_string, 'localized' => lang_get( $t_string ) );
		}

		return array(
			'strings' => $t_localized_strings,
			'language' => $t_current_language,
		);
	}
}

