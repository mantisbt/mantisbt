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
 * MantisBT Invalid Plugin
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * MantisBT Invalid Plugin class
 *
 * The purpose of this class is to handle incomplete plugin definitions, i.e.
 * undefined 'name' or 'version' properties.
 *
 * For Plugin API internal use only.
 */
class InvalidPlugin extends MantisPlugin {
	function register() {
		$this->name = $this->basename;
		$this->description = lang_get( 'plugin_invalid_description' );
	}

	public function set( MantisPlugin $p_plugin ) {
		$t_missing = array();

		if( $p_plugin->name ) {
			$this->name .= " ($p_plugin->name)";
		} else {
			$t_missing[] = 'name';
		}

		if( !$p_plugin->version ) {
			$t_missing[] = 'version';
		}

		if( !empty( $t_missing ) ) {
			$this->description .= '<br>'
				. sprintf(
					lang_get( 'plugin_invalid_description_details' ),
					implode( ', ', $t_missing )
				);
		}
	}
}