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
 * MantisBT Generic Invalid Plugin class
 *
 * The purpose of this class is to handle invalid plugins. It is used as a base
 * for other, specialized invalid plugin classes, e.g.
 * @see InvalidIncompleteDefinitionPlugin
 * @see MissingPlugin
 * @see MissingClassPlugin
 *
 * For Plugin API internal use only.
 */
class InvalidPlugin extends MantisPlugin {
	/**
	 * The reference, invalid Plugin.
	 *
	 * This is used for plugins that are considered invalid even though they
	 * can be loaded, so we can query the reference plugin's properties.
	 *
	 * @var MantisPlugin $ref_plugin
	 */
	public $ref_plugin;

	/**
	 * Flag indicating whether the plugin can be removed from manage plugins page.
	 * @var bool $removable True if it can be removed,
	 *                      False if manual intervention is required.
	 */
	public $removable = true;

	function register() {
		$this->name = $this->basename;
		$this->description = lang_get( 'plugin_invalid_description' );

		$this->status = self::STATUS_INVALID;
	}

	/**
	 * Initialize the invalid plugin.
	 * @see MantisPlugin::getInvalidPlugin()
	 *
	 * @param MantisPlugin $p_plugin Reference, invalid plugin
	 */
	public function setInvalidPlugin( MantisPlugin $p_plugin ) {
		$this->ref_plugin = $p_plugin;
	}
}
