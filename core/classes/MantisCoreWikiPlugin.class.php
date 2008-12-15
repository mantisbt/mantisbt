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

require_once( 'MantisWikiPlugin.class.php' );

/**
 * Basic Dokuwiki support with old-style wiki integration.
 * @package MantisBT
 * @subpackage classes 
 */
class MantisCoreDokuwikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'Mantis Dokuwiki Integration';
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
 */
class MantisCoreMediaWikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'Mantis MediaWiki Integration';
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
 */
class MantisCoreTwikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'Mantis Twiki Integration';
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
 */
class MantisCoreWikkaWikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'Mantis WikkaWiki Integration';
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
 */
class MantisCoreXwikiPlugin extends MantisCoreWikiPlugin {

	function register() {
		$this->name = 'Mantis Xwiki Integration';
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
