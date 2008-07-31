<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
     *  @package CoreAPI
     *  @subpackage WikiAPI
     */

 
function wiki_enabled() {
	return ( config_get_global( 'wiki_enable' ) == ON );
}

function wiki_ensure_enabled() {
	if ( !wiki_enabled() ) {
		access_denied();
	}
}

function wiki_init() {
	if ( wiki_enabled() ) {

		# handle legacy style wiki integration
		require_once( config_get_global( 'class_path' ) . 'MantisCoreWikiPlugin.class.php' );
		switch (config_get_global( 'wiki_engine' )) {
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

		if ( is_null( event_signal( 'EVENT_WIKI_INIT' ) ) ) {
			config_set_global( 'wiki_enable', OFF );
		}
	}
}

function wiki_link_bug( $p_bug_id ) {
	return event_signal( 'EVENT_WIKI_LINK_BUG', $p_bug_id );
}

function wiki_link_project( $p_project_id ) {
	return event_signal( 'EVENT_WIKI_LINK_PROJECT', $p_project_id );
}

wiki_init();
