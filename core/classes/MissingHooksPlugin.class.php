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
 * MantisBT Invalid Incomplete Definition Plugin
 * @copyright  Copyright 2024  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://mantisbt.org
 * @package    MantisBT
 * @subpackage classes
 */

namespace Mantis\classes;
use InvalidPlugin;
use MantisPlugin;

/**
 * MantisBT Missing Hooks Invalid Plugin class
 *
 * The purpose of this class is to handle installed Plugins that are hooking
 * undeclared Events.
 *
 * For Plugin API internal use only.
 */
class MissingHooksPlugin extends InvalidPlugin
{

	function register() {
		$this->name = $this->basename;
		$this->description = lang_get( 'plugin_missing_event' );
		$this->status = self::STATUS_MISSING_EVENTS;
	}

	public function setInvalidPlugin( MantisPlugin $p_plugin ) {
		parent::setInvalidPlugin( $p_plugin );

		if( $p_plugin->version ) {
			$this->name .= ' ' . $p_plugin->version;
		}

		$t_events = array_keys( $p_plugin->hooks() );
		$t_missing = array_filter( $t_events,
			function( $p_event ) {
				return !event_is_declared( $p_event );
			}
		);

		$this->status_message = sprintf(
			lang_get( 'plugin_missing_event_status_message' ),
			implode( ', ', $t_missing )
		);
	}

}
