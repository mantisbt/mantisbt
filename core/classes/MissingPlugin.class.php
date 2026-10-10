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
 * MantisBT Missing Plugin
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * MantisBT Missing Plugin class
 *
 * The purpose of this class is to handle the error scenario when a plugin has
 * been installed, and its source code later removed from the plugin_path dir.
 *
 * For Plugin API internal use only.
 */
final class MissingPlugin extends InvalidPlugin {
	function register() {
		$this->name = $this->basename;
		$this->description = lang_get( 'plugin_missing_description' );

		$this->status = self::STATUS_MISSING_PLUGIN;
		$this->status_message = lang_get( 'plugin_missing_status_message' );
	}
}
