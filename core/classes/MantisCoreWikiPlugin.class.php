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
 * Mantis Core Wiki Plugins
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * requires MantisWikiPlugin.class
 */
require_once( 'MantisWikiPlugin.class.php' );

/**
 * Base that uses the old style wiki definitions from config_inc.php
 * @package MantisBT
 * @subpackage classes
 */
abstract class MantisCoreWikiPlugin extends MantisWikiPlugin {
	function config() {
		return array(
			'root_namespace' => config_get_global( 'wiki_root_namespace' ),
			'engine_url' => config_get_global( 'wiki_engine_url' ),
		);
	}
}

/**
 * Basic Dokuwiki support with old-style wiki integration.
 * @package MantisBT
 * @subpackage classes 
 */
class MantisCoreDokuwikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'MantisBT Dokuwiki Integration';
		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);
	}

	function base_url( $p_project_id=null ) {
		$t_base = plugin_config_get( 'engine_url' ) . 'doku.php?id=';

		$t_namespace = plugin_config_get( 'root_namespace' );
		if ( !is_blank( $t_namespace ) ) {
			$t_base .= urlencode( $t_namespace ) . ':';
		}

		if ( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( project_get_name( $p_project_id ) ) . ':';
		}
		return $t_base;
	}

	function link_bug( $p_event, $p_bug_id ) {
		return $this->base_url( bug_get_field( $p_bug_id, 'project_id' ) ) .  'issue:' . (int)$p_bug_id;
	}

	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id ) . 'start';
	}
}

/**
 * Basic MediaWiki support with old-style wiki integration.
 * @package MantisBT
 * @subpackage classes 
 */
class MantisCoreMediaWikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'MantisBT MediaWiki Integration';
		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);
	}

	function base_url( $p_project_id=null ) {
		$t_base = plugin_config_get( 'engine_url' ) . 'index.php/';
		if ( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( project_get_name( $p_project_id ) ) . ':';
		} else {
			$t_base .= urlencode( plugin_config_get( 'root_namespace' ) );
		}
		return $t_base;
	}

	function link_bug( $p_event, $p_bug_id ) {
		return $this->base_url( bug_get_field( $p_bug_id, 'project_id' ) ) . (int)$p_bug_id;
	}

	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id ) . 'Main_Page';
	}
}

/**
 * Basic Twiki support with old-style wiki integration.
 * @package MantisBT
 * @subpackage classes 
 */
class MantisCoreTwikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'MantisBT Twiki Integration';
		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);
	}

	function base_url( $p_project_id=null ) {
		$t_base = plugin_config_get( 'engine_url' );

		$t_namespace = plugin_config_get( 'root_namespace' );
		if ( !is_blank( $t_namespace ) ) {
			$t_base .= urlencode( $t_namespace ) . '/';
		}

		if ( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( project_get_name( $p_project_id ) ) . '/';
		}
		return $t_base;
	}

	function link_bug( $p_event, $p_bug_id ) {
		return $this->base_url( bug_get_field( $p_bug_id, 'project_id' ) ) . 'IssueNumber' . (int)$p_bug_id;
	}

	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id );
	}
}

/**
 * Basic WikkaWiki support with old-style wiki integration.
 * @package MantisBT
 * @subpackage classes 
 */
class MantisCoreWikkaWikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'MantisBT WikkaWiki Integration';
		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);
	}

	function base_url( $p_project_id=null ) {
		$t_base = plugin_config_get( 'engine_url' ) . 'wikka.php?wakka=';

		$t_namespace = ucfirst( plugin_config_get( 'root_namespace' ) );
		if ( !is_blank( $t_namespace ) ) {
			$t_base .= urlencode( $t_namespace );
		}

		if ( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( project_get_name( $p_project_id ) );
		}
		return $t_base;
	}

	function link_bug( $p_event, $p_bug_id ) {
		return $this->base_url( bug_get_field( $p_bug_id, 'project_id' ) ) . 'Issue' . (int)$p_bug_id;
	}

	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id ) . 'Start';
	}
}

/**
 * Basic Xwiki support with old-style wiki integration.
 * @package MantisBT
 * @subpackage classes 
 */
class MantisCoreXwikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'MantisBT Xwiki Integration';
		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);
	}

	function base_url( $p_project_id=null ) {
		$t_base = plugin_config_get( 'engine_url' );
		if ( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( project_get_name( $p_project_id ) ) . '/';
		} else {
			$t_base .= urlencode( plugin_config_get( 'root_namespace' ) );
		}
		return $t_base;
	}

	function link_bug( $p_event, $p_bug_id ) {
		return $this->base_url( bug_get_field( $p_bug_id, 'project_id' ) ) .  (int)$p_bug_id;
	}

	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id ) . 'Main_Page';
	}
}
