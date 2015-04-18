<?php
# MantisBT - a php based bugtracking system

# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 * Mantis Wiki Plugins
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Base class that implements the skeleton for a wiki plugin.
 * @package MantisBT
 * @subpackage classes
 */
abstract class MantisWikiPlugin extends MantisPlugin {

	function hooks() {
		return array(
			'EVENT_WIKI_INIT' => 'wiki_init',
			'EVENT_WIKI_LINK_BUG' => 'link_bug',
			'EVENT_WIKI_LINK_PROJECT' => 'link_project',
		);
	}

	function wiki_init() {
		return true;
	}

	abstract function link_bug( $p_event, $p_bug_id );
	abstract function link_project( $p_event, $p_project_id );
}

