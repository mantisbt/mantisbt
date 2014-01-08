<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage WikiAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Returns whether wiki functionality is enabled
 * @return bool indicating whether wiki is enabled
 * @access public
 */
function wiki_enabled() {
	return( config_get_global( 'wiki_enable' ) == ON );
}

/**
 *
 * @return null
 * @access public
 */
function wiki_init() {
	if( wiki_enabled() ) {

		# handle legacy style wiki integration
		require_once( config_get_global( 'class_path' ) . 'MantisCoreWikiPlugin.class.php' );
		switch( config_get_global( 'wiki_engine' ) ) {
			case 'dokuwiki':
				plugin_child( 'MantisCoreDokuwiki' );
				break;
			case 'mediawiki':
				plugin_child( 'MantisCoreMediaWiki' );
				break;
			case 'twiki':
				plugin_child( 'MantisCoreTwiki' );
				break;
			case 'WikkaWiki':
				plugin_child( 'MantisCoreWikkaWiki' );
				break;
			case 'xwiki':
				plugin_child( 'MantisCoreXwiki' );
				break;
		}

		if( is_null( event_signal( 'EVENT_WIKI_INIT' ) ) ) {
			config_set_global( 'wiki_enable', OFF );
		}
	}
}

/**
 *
 * @param int $p_bug_id Bug ID
 * @return string url
 * @access public
 */
function wiki_link_bug( $p_bug_id ) {
	return event_signal( 'EVENT_WIKI_LINK_BUG', $p_bug_id );
}

/**
 *
 * @param int $p_project_id
 * @return string url
 * @access public
 */
function wiki_link_project( $p_project_id ) {
	return event_signal( 'EVENT_WIKI_LINK_PROJECT', $p_project_id );
}

