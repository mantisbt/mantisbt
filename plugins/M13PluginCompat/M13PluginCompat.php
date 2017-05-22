<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

class M13PluginCompatPlugin extends MantisPlugin {

	function register() {
		$this->name = 'Mantis 1.3 plugin pages comptaibility';
		$this->description = 'Provides compatibility for plugin pages using v1.3 layouts';
		$this->version = MANTIS_VERSION;
		$this->requires = array( 'MantisCore' => '2.0.0' );
		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	function hooks() {
		return array( 'EVENT_LAYOUT_RESOURCES' => 'resources' );
	}

	function resources( $p_event ) {
		return '<link rel="stylesheet" type="text/css" href="'. plugin_file( 'm13_compat.css' ) .'"/>';
	}

	function init() {
		plugin_require_api( 'core/html_compat_api.php' );
	}
}