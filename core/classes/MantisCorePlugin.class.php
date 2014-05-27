<?php
# MantisBT - A PHP based bugtracking system

# Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * MantisBT Core Plugin
 * Used to give other plugins a permanent core plugin to 'require' for compatibility.
 * Can/should not be used as a base class.
 * @package MantisBT
 * @subpackage classes
 */
final class MantisCorePlugin extends MantisPlugin {
	/**
	 * Plugin registration
	 */
	function register() {
		$this->name = 'MantisBT Core';
		$this->description = 'Core plugin API for the Mantis Bug Tracker.';

		$this->version = MANTIS_VERSION;

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}
}
