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

/**
 * Base that uses the old style wiki definitions from config_inc.php
 */
abstract class MantisCoreWikiPlugin extends MantisWikiPlugin {
	function config() {
		return array(
			'root_namespace' => config_get_global( 'wiki_root_namespace' ),
			'engine_url' => config_get_global( 'wiki_engine_url' ),
		);
	}
}
